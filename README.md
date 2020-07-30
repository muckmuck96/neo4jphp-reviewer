# neo4jphp-reviewer
Authors: elauser, muckmuck96
- Zuerst sollten alle Dependencies installiert werden: `composer install`
- Außerdem sollte z.B. der [Neo4j Desktop Client/Server](https://neo4j.com/download/) heruntergeladen werden und eine Datenbank mit der Version 3.5.19 erstellt werden
- Nun muss die Datenbank im Neo4j Browser geöffnet werden und mittels `:play movies`, einmal auf weiter und dann auf den Cypher Code klicken und ausführen, die Datenbank mit Beispieldaten gefüllt.
- Der Datenbank muss dann mittels `:server user add` ein Benutzer mit username: `example4j`, password: `example4j` und roles `admin` erstellt werden
- Dann kann der Server gestartet werden: `php -S localhost:8080 index.php`
