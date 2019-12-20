<?php

namespace App\AdminModule\Presenters;

use App\Model\ConfereeManager;
use App\Model\TalkManager;
use App\Orm\Conferee;
use App\Orm\Program;
use App\Orm\Talk;
use DateInterval;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nextras\Orm\Collection\ICollection;
use Ublaboo\DataGrid\DataGrid;

class ConferencePresenter extends BasePresenter
{
    /**
     * @var ConfereeManager
     */
    private $confereeManager;
    /**
     * @var TalkManager
     */
    private $talkManager;
    private $icon_prefix;


    /**
     * ConferencePreseneter constructor.
     * @param ConfereeManager $confereeManager
     * @param TalkManager $talkManager
     */
    public function __construct(ConfereeManager $confereeManager, TalkManager $talkManager)
    {
        $this->confereeManager = $confereeManager;
        $this->talkManager = $talkManager;
    }


    /**
     * @param $name
     */
    public function createComponentConfereeDatagrid($name)
    {
        $grid = new DataGrid($this, $name);

        $grid->setDataSource($this->confereeManager->findAll());

        $grid->addColumnText('name', 'Jméno');
        $grid->addColumnText('email', 'E-mail');
    }


    /**
     * @throws \Nette\Application\AbortException
     * @secured
     */
    public function handleExportConfereeCsv($msExcel = false)
    {
        $delimiter = $msExcel ? ';' : ',';

        $allConferee = $this->confereeManager->findAll();

        ob_start();
        $df = fopen("php://output", 'w');
        fputcsv(
            $df,
            [
                "E-mail",
                "Jméno",
                "Přihlášen",
                "Newsletter",
                "Přednášející",
                "Registrace",
                "Souhlas získán",
                "Bio",
                "Firma",
                "Adresa"
            ],
            $delimiter,
            '"'
        );

        /** @var Conferee $conferee */
        foreach ($allConferee as $conferee) {
            try {
                $extended = Json::decode($conferee->extended, Json::FORCE_ARRAY);
            } catch (JsonException $e) {
                $extended = [];
            }
            @fputcsv($df, [
                $conferee->email,
                $conferee->name,
                'Ano',
                $conferee->allowMail ? 'Ano' : 'Ne',
                count($conferee->talk) ? 'Ano' : 'Ne',
                $conferee->created->format(\DateTime::ATOM),
                $conferee->consens ? $conferee->consens->format(\DateTime::ATOM) : null,
                $conferee->bio,
                isset($extended['company']) ? $extended['company'] : null,
                isset($extended['address']) ? $extended['address'] : null,
            ], $delimiter, '"');
        }

        fclose($df);
        $csv = ob_get_clean();

        if ($msExcel) {
            $csv = iconv("UTF-8", "WINDOWS-1250", $csv);
        }

        $fileDatePostfix = gmdate("Ymd.his");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");

        header("Content-Type: application/octet-stream");
        header("Content-Length: " . strlen($csv));

        header("Content-Disposition: attachment;filename=users-$fileDatePostfix.csv");
        echo $csv;

        $this->terminate();
    }


    /**
     * @param $name
     * @throws \App\Model\InvalidEnumeratorSetException
     * @throws \Nette\Utils\JsonException
     * @throws \Ublaboo\DataGrid\Exception\DataGridColumnStatusException
     * @throws \Ublaboo\DataGrid\Exception\DataGridException
     */
    public function createComponentTalksDatagrid($name)
    {
        $categories = $this->talkManager->getCategories();
        $rooms = $this->talkManager->getRooms();

        $grid = new DataGrid($this, $name);
        $this->icon_prefix = DataGrid::$iconPrefix;
        $this->icon_prefix = 'glyphicon glyphicon-';

        $grid->setDataSource($this->talkManager->findAll());

        $grid->addColumnLink('title', 'Název', ':Conference:talkDetail', 'title', ['id']);
        $grid->addColumnText('speaker', 'Jméno', 'conferee.name');


        $grid->addColumnText('category', 'Kategorie')
            ->setReplacement($categories);

        $onStatusChange = function ($id, $status) use ($grid) {
            /** @var Talk $talk */
            $talk = $this->talkManager->getById($id);
            $talk->setValue('enabled', $status);
            $this->talkManager->save($talk);

            if ($this->isAjax()) {
                $grid->redrawItem($id);
            }
        };

        $grid->addColumnStatus('enabled', 'Aktivní')
            ->addOption(1, 'Aktivní')
            ->endOption()
            ->addOption(0, 'Zrušená')
            ->setClass('btn-danger')
            ->endOption()
            ->onChange[] = $onStatusChange;

        $grid->addAction('edit', '', 'talkEdit')
            ->setIcon('pencil')
            ->setTitle('Upravit');
    }


    /**
     * @param $id
     * @throws \Nette\Application\BadRequestException
     * @throws \Nette\Utils\JsonException
     */
    public function renderTalkEdit($id)
    {
        /** @var Talk $talk */
        $talk = $this->talkManager->getById($id);

        if (!$talk) {
            $this->error('přednáška nenalezena');
        }

        $this->template->talk = $talk;
        $this->template->extended = Json::decode($talk->extended, Json::FORCE_ARRAY);

        /** @var Form $form */
        $form = $this['talkForm'];

        $values = $talk->toArray();

        $form->setDefaults($values);
    }


    /**
     * @return Form
     * @throws \App\Model\InvalidEnumeratorSetException
     * @throws \Nette\Utils\JsonException
     */
    public function createComponentTalkForm()
    {
        $form = new Form();

        $form->addHidden('id');

        $form->addText('title', 'Název');
        $form->addTextArea('description', 'Popis');
//        $form->addTextArea('purpose', 'Pro koho je určena');
        $form->addSelect('category', 'Kategorie', $this->talkManager->getCategories());
        $form->addText('company', 'Firma');

        $form->addSubmit('submit', 'Odeslat')->setOption('primary', true);

        $form->addProtection();

        $form->onSuccess[] = [$this, 'onTalkFormSuccess'];

        return $form;
    }


    /**
     * @param Form $form
     * @param ArrayHash $values
     * @throws \Nette\Application\BadRequestException
     * @throws \Nette\Application\AbortException
     * @throws \Exception
     */
    public function onTalkFormSuccess(Form $form, $values)
    {
        $id = $values->id;

        /** @var Talk $talk */
        $talk = $this->talkManager->getById($id);

        if (!$talk) {
            $this->error('Přednáška nenalezena');
        }

        foreach ($values as $key => $value) {
            if (in_array($key, ['id'])) {
                continue;
            }

            if ($value === '') {
                $value = null;
            }
            $talk->setValue($key, $value);
        }

        $this->talkManager->save($talk);

        $this->flashMessage('Uloženo', 'success');
        $this->redirect('talks');
    }


    public function getProgramTypes()
    {
        return [
            'talk' => 'Přednáška',
            'coffee' => 'Coffee break',
            'lunch' => 'Přestávka na oběd',
            'custom' => 'Vlastní blok',
        ];
    }


    /**
     * @param $name
     * @throws JsonException
     * @throws \App\Model\InvalidEnumeratorSetException
     * @throws \Ublaboo\DataGrid\Exception\DataGridException
     */
    public function createComponentProgramDatagrid($name)
    {
        $rooms = $this->talkManager->getRooms();
        $program = $this->talkManager->findAllProgram()
            ->orderBy('room', ICollection::ASC)
            ->orderBy('time', ICollection::ASC);

        $grid = new DataGrid($this, $name);
        DataGrid::$iconPrefix = 'glyphicon glyphicon-';

        $grid->setDataSource($program);

        $grid->addColumnText('type', 'Typ')
            ->setReplacement($this->getProgramTypes());

        $grid->addColumnText('title', 'Název')->setRenderer(function ($row) {
            /** @var Program $row */
            if (empty($row->title) && isset($row->talk)) {
                return $row->talk->title;
            } else {
                return $row->title;
            }
        });

        $grid->addColumnText('speaker', 'Přednášející')->setRenderer(function ($row) {
            /** @var Program $row */
            if (empty($row->speaker) && isset($row->talk)) {
                return $row->talk->conferee->name;
            } else {
                return $row->speaker;
            }
        });

        $grid->addColumnText('room', 'Místnost')
            ->setReplacement($rooms);

        $grid->addColumnText('time', 'Čas')->setRenderer(function ($row) {
            /** @var Program $row */
            if (is_null($row->time)) {
                return null;
            } else {
                return $row->time->format('%H:%I:%S');
            }
        });

        $grid->addColumnText('duration', 'Délka [minuty]');

        $grid->addAction('edit', '', 'programEdit')
            ->setIcon('pencil')
            ->setTitle('Upravit');
    }


    /**
     *
     * @throws JsonException
     */
    public function getMergedTalks()
    {
        $talks = $this->talkManager->findAll();

        $merged = $this->getProgramTypes();

        unset($merged['talk']);

        /** @var Talk $talk */
        foreach ($talks as $talk) {
            $id = $talk->id;

            $requestedDuration = null;
            $extended = Json::decode($talk->extended, Json::FORCE_ARRAY);
            $duration = isset($extended['requested_duration']) ? intval($extended['requested_duration']) : null;
            if ($duration) {
                $requestedDuration = " (požadováno $duration minut)";
            }

            $merged['talk|' . $id] = "Přednáška: " . $talk->title . $requestedDuration;
        }

        return $merged;
    }


    public function renderProgramEdit($id = null)
    {
        if ($id === null) {
            return;
        }
        /** @var Program $program */
        $program = $this->talkManager->getProgramById($id);

        if (!$program) {
            $this->error('Program nenalezena');
        }

        /** @var Form $form */
        $form = $this['programForm'];

        $values = $program->toArray();
        $values['time'] = is_null($values['time']) ? '' : $values['time']->format('%H:%I:%S');
        $values['type'] = $program->talk ? $program->type . '|' . $program->talk->id : $program->type;

        $form->setDefaults($values);
    }


    /**
     * @return Form
     * @throws JsonException
     * @throws \App\Model\InvalidEnumeratorSetException
     */
    public function createComponentProgramForm()
    {
        $durations = $this->talkManager->getDurations();
        $durations += $this->talkManager->getDurationChoice();
        $durations = array_filter($durations, function ($item) {
            return intval($item);
        }, ARRAY_FILTER_USE_KEY);

        $form = new Form();

        $form->addGroup();

        $form->addHidden('id');

        $form->addSelect('type', 'Type', [null => '== Vyberte =='] + $this->getMergedTalks())
            ->setRequired(true);
        $form->addRadioList('room', 'Místnost', $this->talkManager->getRooms())->setRequired(true);
        $form->addText('time', 'Čas konání')->setType('time')->setRequired(true);
        $form->addRadioList('duration', 'Délka v minutách', $durations);

        $form->addSubmit('submit', 'Uložit')->setOption('primary', true);

        $form->addGroup('Vlastní název přednášky v programu');

        $form->addText('title', 'Název')
            ->setOption('description', 'Volitelné. Zadejte jen pokud je potřeba v programu název přednášky přepsat');
        $form->addTextArea('speaker', 'Přednášející')
            ->setOption('description', 'Volitelné. Zadejte jen pokud je potřeba v programu název speakera přepsat');

        $form->addGroup();

        $form->addSubmit('submit_rename', 'Uložit a přejmenovat')->setOption('primary', true);

        $form->addProtection();

        $form->onSuccess[] = [$this, 'onProgramFormSuccess'];

        return $form;
    }


    /**
     * @param Form $form
     * @param $values
     * @throws \Exception
     * @throws \Nette\Application\AbortException
     */
    public function onProgramFormSuccess(Form $form, $values)
    {
        $id = $values->id;

        /** @var Program $program */
        $program = $this->talkManager->getProgramById($id);

        if (!$program) {
            $program = new Program;
        }

        foreach ($values as $key => $value) {
            if (in_array($key, ['id'])) {
                continue;
            }

            if ($key === 'time') {
                if (preg_match('#^(-?)(\d+):(\d+)#', $value, $m)) {
                    $value = new DateInterval("PT{$m[2]}H{$m[3]}M");
                } else {
                    $values = null;
                }
            }

            if ($key === 'duration') {
                if (!(empty($value) || in_array($value, [10, 20, 30, 40, 50, 60, 90]))) {
                    $form['duration']->addError('Délka přednášky musí být jeden z časů: 10, 20, 30, 40, 50, 60, nebo 90 minut');
                    return;
                }
            }

            if ($key === 'type') {
                list($type, $talkId) = array_pad(explode('|', $value, 2), 2, null);
                $program->type = $type;
                if ($talkId) {
                    $program->talk = $this->talkManager->getById($talkId);
                } else {
                    $program->talk = null;
                }
                continue;
            }

            if ($value === '') {
                $value = null;
            }
            $program->setValue($key, $value);
        }

        $this->talkManager->saveProgram($program);

        $this->flashMessage('Uloženo', 'success');
        $this->redirect('program');
    }
}
