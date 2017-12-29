Web Zlínského barcampu
======================

Web Zlínského barcampu (https://www.zlinskybarcamp.cz/)


Instalace na localhost
----------------------

Po stažení repozitáře naisntalujte závislosti:

    composer install

Vytvořte soubor `app/config/config.local.neon` (není verzován v Gitu). Může být i prázdný.

Spuštění webového serveru
-------------------------
Spusťe Docker 

    docker-composer up -d

Na stránce `http://localhost:8000` by se měl objevit aktuální web.


Požadavky
---------

PHP 5.6 nebo vyšší, Mysql 5 nebo vyšší, Git, Unzip. 


License
-------
- Web: The MIT License (MIT)
- Nette: New BSD License or GPL 2.0 or 3.0
- Adminer: Apache License 2.0 or GPL 2
