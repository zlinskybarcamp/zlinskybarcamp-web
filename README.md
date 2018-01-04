Web Zlínského barcampu
======================

Web Zlínského barcampu (https://www.zlinskybarcamp.cz/)


Instalace na localhost
----------------------

Po stažení repozitáře naisntalujte závislosti:

    composer install

Vytvořte soubor `app/config/config.local.neon` (není verzován v Gitu). Může být i prázdný.

Pro úpravy JavaScriptových souborů a nebo stylů je potřeba nainstalovat závislosti pro generátor:

    npm install
    
Po úpravě souborů v `assets/` zavolejte `grunt` a vygenerují se soubory:
- `www/js/main.js`
- `www/js/admin.js`
- `www/css/main.css`

které obsahují veškeré scripty a styly stránek (kromě admin sekce, ta využívá css z oficiálních CDN). Tyto soubory jsou
součástí repozitáře, takže je lze rovnou použít. 
 

Spuštění webového serveru
-------------------------
Spusťte Docker 

    docker-composer up -d

Na stránce `http://localhost:8000` by se měl objevit aktuální web.


Požadavky
---------

PHP 5.6 nebo vyšší, Mysql 5 nebo vyšší, Git, Unzip. 


License
-------
- Web: The MIT License (MIT)
- Nette: New BSD License or GPL 2.0 or 3.0
