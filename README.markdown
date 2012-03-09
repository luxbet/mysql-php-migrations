This project was created to address the problem of maintaining database changes within an actively developed PHP/MySQL project. Frequently, multiple developers need to be able to quickly update their local sandboxes with the latest DB schema changes and also add their own without creating file conflicts in repositories such as CVS and Subversion.

## MySQL-PHP-Migrations includes the following features:

* command-line only utility
* migration files are stored using timestamps to reduce repository conflicts
* ability to list, migrate up, migrate down, run a specific migration, and migrate to latest database version
* each migration is wrapped in a transaction to ease error recovery
* migration information stored in database table to support interleaving (insertion of migrations between migrations that have already been run)
* in-line help
* support for PDO with MySQL driver or mysqli
* can store an initial DB schema which allows entire database structure to be erased and rebuilt from an initial schema and existing migrations

## New changes by luxbet:

* --dry-run / -p support: try out without any database changes
* migration files can be grouped by put under separate folders under /db
  (when using ./migrate.php add, it is still put under /db directly, but you can move them around later)
* an SQL log file (log.sql) for all the SQL from migration files of last run
* fix bugs and a little nicer CLI prompt
* You can redefine ``MPM_PATH`` and ``MPM_CONFIG_FILE_PATH`` so you can share one installation across multiple projects

```php
#!/usr/bin/env php
<?php

define('MPM_PATH', '/usr/local/src/mysql-php-migration');

# the config file is ./db_migration/db_migration_config.php
define('MPM_CONFIG_FILE_PATH', './db_migration');

require(MPM_PATH . '/migrate.php');

```

* rename migration config file name from ``db_config.php`` to ``db_migration_config.php``
* add a ``info`` field to migration file, which will be shown in the SQL log file and list command
* "list" command: show migration info and "highlight" current migration so you don't need to run "status" command separately

## Known Issues:

* We may break some PDO support when adding --dry-run :( We use mysqli only

