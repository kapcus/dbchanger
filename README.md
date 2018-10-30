DbChanger
=================
Toolkit for advanced database change (dbChange) management.

* Deploy and install centrally
* Multiple environments with multiple schemas & users
* Each dbChange can have multiple sql files with arbitrary placeholders
* Simple environment configuration
* Each dbChange can define multiple other dbChanges that are required 

Currently supported databases: 
* `ORACLE` 

Architecture
=================
- [Database schema](misc/erm/datamodel.png)

* `Environment` can have multiple `Users` assigned to multiple `Groups` and also can have multiple `Placeholders` defined  
* `DbChange` consists of fragments.
* `Fragment` is bunch of sql queries defined in one sql file - sql content is more like template
* Each DbChange can be installed on environment, this is represented as Installation. 
* `Installation` can be successful, cancelled, but always one active at the same time
Each installation consists of Installation fragments.
`Installation fragment` is modified sql content of particular fragment, 
crafted for particular environment and user. Its identificator starts by letter F, e.g. `F10`

 
DbChange `12345` has its own [directory](misc/sampledata/12345).
It consists of 10 fragments. Therefore 10 files:
* [01_central.sql](misc/sampledata/12345/01_central.sql)
* [02_central.sql](misc/sampledata/12345/02_central.sql)
* ...
* [10_region.sql](misc/sampledata/12345/10_region.sql)

Each file name follow mask `XXXXXX_YYYYYYY_ZZZZZZZ.sql` where
* `XXXXXX` is optional and can serve as a file prefix, in our example it is completly empty (e.g. dbchange_)
* `YYYYYY` is numeric fragment index, must be numeric and incremental, identified with letter I (e.g. I5)
* `ZZZZZZ` is group name  

File [_requirements.txt](misc/sampledata/12345/_requirements.txt) can contain list of 
required dbChanges that must be installed before this dbChange can be installed.
One dbChange code per line. Empty or missing file means dbChange does not require any dbChange to be installed. 

Each fragment content must be atomic from transactional perspective. 
E.g. Oracle does not support rolling back of DDL statements therefore all these queries must be in its own separate file.

Groups can be defined in `config.local.neon` file for each environment different. E.g.
* `central: [MAINUSER]` - means fragment sql content with group `central` will be executed for MAINUSER
* `region: [SLAVEUSER1, SLAVEUSER2]` means fragment sql content with group `region` will be executed for SLAVEUSER1 and also for user SLAVEUSER2

User can define arbitrary number of groups and assigned arbitrary number of users into each group.

In fragment sql content `placeholders` can be used. Processing engine simple replace placeholder with
defined value in config file which is to be replaced. 

Group name can be also used as a placeholder. As a result,
line of sql code will be inserted for each user assigned into the group and placeholder value will 
be replaced with user name.

Following notation is used: 
* `I1` = `Installation` with id 1
* `F2` = `Installation fragment` with id 2
* `X3` = `Fragment index` with id 3
* `L4` = `Installation log` with id 4


Available Installation statuses:
* `N` = `New` - initial status which means installation can start
* `P` = `Pending` - status signaling that at least one pending installation fragment exists 
* `I` = `Installed` - all installation fragments were successfully installed
* `C` = `Cancelled` - all installation fragments were cancelled

Available Installation fragment statuses:
* `N` = `New` - initial status which means installation fragment is ready for installation
* `P` = `Pending` - status signaling that installation of this installation fragment already started but something wrong happened
* `I` = `Installed` - installation fragment has been successfully installed
* `S` = `Skipped` - installation fragment has been skipped
* `C` = `Cancelled` - installation fragment has been cancelled

Installation
---------
1] Install DbChanger with all necessary dependencies with
```
composer require kapcus/dbchanger
```

2] run sql scripts located in [build](build) folder. This will install DbChanger internal database tables.
(you need to be able to create table, trigger, sequence, see [grants.sql](misc/grants.sql))

3] Move [config.local.neon.example](misc/config.local.neon.example) into `dbchanger/config.local.neon` and setup dbchanger.database section (this is where database for central DbChanger logic will be running).

4] run this to verify if DbChanger is properly installed and configured
```
php bin/console.php dbchanger:check
``` 

Usage
---------

1] Define your environments in `dbchanger/config.local.neon`.

========================================

2] Initialize the DbChange with
``` 
php bin/console.php dbchanger:init
```

This command will load environment data specified in configuration file into internal
DbChanger database. Now, DbChanger is aligned with your configuration and ready to serve.

========================================
 
3] Register dbChange (e.g. 12345) with
```
php bin/console.php dbchanger:register 12345

php bin/console.php dbchanger:register 12345 -d

php bin/console.php dbchanger:register 12345 -o
```

This command will load sql content of dbChange files into internal DbChanger database.
Now, dbChange is ready to be installed on selected environment.

In case `-d` is specified (debug), all dependant DbChanges specified in file `_requirements.txt` are ignored. This is useful during development when
developer needs to test one particular DbChanges and dependencies are not important.

In case `-o` is specified (overwrite), existing DbChange (if any) is overwritten - this is possible only if there is no pending installation of this DbChange.

Source dbChange content is searched in `inputDirectory` specified in [config.neon](config/config.neon)

========================================

4] Install dbChange (e.g. 12345) with
```
php bin/console.php dbchanger:install DEV 12345

php bin/console.php dbchanger:install DEV 12345 -s

php bin/console.php dbchanger:install DEV 12345 -f
``` 

This command will establish the connection with environment under specified user.
Once connected, it will execute sql queries for selected dbChange, one by one.
Installation is taking fragments one by one and tries to install them confirming this
operation with appropriate installation fragment status.
Once whole dbChange installation is successfully installed, proper installation status is set.

In case `-s` is specified (stop), installation will stop at the very beginning (useful in case you need to skip the first fragment)

In case `-f` is specified (force), the constraint which ensure that all required DbChanges are up-to-date 
(i.e. latest version is installed) will be ignored

All executed queries are logged into `logDirectory` specified in [config.neon](config/config.neon) 

========================================

Other functionality
---------
DbChanger can display status of installations and all installation fragments are listed with particular attributes
(e.g. display status of installation for dbChange 12345 on DEV environment)
```
php bin/console.php dbchanger:status DEV 12345
```

Also all registered version of particular DbChange are listed.

========================================

In case installation fails or group is to be installed manually, manual interaction is expected.
During installation, DbChanger will recognize this state and will report it.
Once manually executed or fixed, it is necessary to tell DbChanger that issue
has been fixed. Following commands can change status of dbChange fragment(s) 
(e.g. fragment F3 to status INSTALLED, fragments F3, F4 and F5 to status CANCELLED, whole installation to status NEW) 
```
php bin/console.php dbchanger:mark F3 I
php bin/console.php dbchanger:mark F3-F5 C
php bin/console.php dbchanger:mark I1 N
```

========================================

Command `log` can be used for displaying installation log history for given installed fragment
```
php bin/console.php dbchanger:log F5
```

========================================

Command `display` can be useful for displaying sql content of installed fragment or installation log entry.
```
php bin/console.php dbchanger:display F5
php bin/console.php dbchanger:display L10
```

========================================

It can be also useful to dump dbChange or individual fragment content into the file.
E.g. in case of manual dbChange when sql can be executed by separate process only.
Following command will generate final sql content for environment DEV, dbChange 12345
and fragment with index 7 whole content. Output folder can be specified in 
configuration file.  
```
php bin/console.php dbchanger:generate DEV 12345

php bin/console.php dbchanger:generate DEV 12345 X7

php bin/console.php dbchanger:generate DEV 12345 X7 -d
```

In case `-d` is specified, output is dumped into standard output.

Source dbChange content is searched in `inputDirectory` specified in [config.neon](config/config.neon)
Generated content is stored in `outputDirectory` specified in [config.neon](config/config.neon)

Special types of sql fragments
---------
* It is possible to specify different delimiter (e.g. in case of Oracle create procedure/trigger),
just add comment `-- DELIMITER`, see [example](misc/sampledata/12346)

* It is possible to multiply only part of sql query when group placeholder is to be replaced.
For this purpose, see `/*START*/`, `/*END*/`, `/*GLUE_START` and `GLUE_END*/` usage in
[example](misc/sampledata/12347)

TODO
---------
* add comments into source code
* Check command - check all connections, check all sequences

* Reinit command - when environment, group, user is changed/added/removed, reflect this change
* Rollback command - add support for inverse dbChange and implement dbChange roll back
* List command - list all dbchange which can be registered