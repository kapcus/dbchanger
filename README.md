DbChanger
=================
Toolkit for advanced database change management.

* Deploy and install centrally
* Multiple environments with multiple schemas & users
* Each dbchange can have multiple sql files with arbitrary placeholders
* Simple environment configuration

Currently supported databases: 
* `ORACLE` 

Architecture
=================
- [Database schema](https://github.com/kapcus/dbchanger/tree/master/misc/erm/datamodel.png)

Dbchange consists of fragments. Fragment is bunch of sql queries defined in one sql file.
E.g. Dbchange `12345` has its own directory `dbchanger/misc/sampledata/12345`.
It consists of 10 fragments. Therefore 10 files:
* 01_central.sql
* 02_central.sql
* ...
* 10_region.sql

Each file name follow mask XXXXXX_YYYYYYY_ZZZZZZZ.sql where
* XXXXXX is optional and can serve as a file prefix, in our example it is completly empty (e.g. dbchange_)
* YYYYYY is numeric index, must be numeric and incremental
* ZZZZZZ is group name  

Each fragment content must be atomic from transactional perspective. 
E.g. Oracle does not support rolling back of DDL statements therefore all these queries must be in its own separate file.

Groups can be defined in `config.local.neon` file for each environment different. E.g.
* `central: [MAINUSER]` - means fragment sql content with group `central` will be executed for MAINUSER
* `region: [SLAVEUSER1, SLAVEUSER2]` means fragment sql content with group `region` will be executed for SLAVEUSER1 and also for user SLAVEUSER2

User can define arbitraty number of groups and assigned arbitraty number of users into each group.

In fragment sql content `placeholders` can be used. Processing engine simple replace placeholder with
defined value in config file which is to be replaced. 

Group name can be also used as a placeholder (currently only in one-line statements). As a result,
line of sql code will be inserted for each user assigned into the group and placeholder value will 
be replaced with user name.

Installation
---------
1] Install dbchanger with all necessary dependencies with
```
composer require kapcus/dbchanger
```

2] run `dbchanger/misc/create_master_sql.sql` in your database instance where central dbchanger logic will run.
(you need to be able to create table, trigger, sequence, see `dbchanger/misc/grants.sql`)

3] Move `dbchanger/misc/config.local.neon.example` into `dbchanger/config.local.neon` and setup dbchanger.database section (this is where database for central dbchanger logic will be running).

4] run this to verify if dbchanger is properly installed and configured
```
php bin/console.php dbchanger:check
``` 

Usage
---------

1] Define your environments in `dbchanger/config.local.neon`.

========================================

2] Initialize the environment (e.g. DEV) with
``` 
php bin/console.php dbchanger:init DEV
```

This command will load environment data specified in configuration file into internal
dbchanger database. Now, environment is ready for dbchange deployments.

========================================
 
3] Register dbchange (e.g. 12345) with
```
php bin/console.php dbchanger:register 12345
```

This command will load sql content of dbchange files into internal dbchanger database.
Now, dbchange is ready to be installed on selected environment.

========================================

4] Install dbchange (e.g. 12345) with
```
php bin/console.php dbchanger:install DEV 12345
``` 

This command will establish the connection with environment under specified user.
Once connected, it will execute sql queries for selected dbchange.
Once each whole dbchange fragment is successfully installed, 

========================================

Other functionality
---------

In case installation fails or group is to be installed manually, manual interaction is expected.
During installation, DbChanger will recognize this state and will report it.
Once manually executed or fixed, it is necessary to tell DbChanger that it
has been done. Following command can change status of dbchange fragment 
(e.g. fragment DEV-12345-5-FSIDEVL to status INSTALLED) 
```
php bin/console.php dbchanger:mark DEV-12345-5-FSIDEVL I
```

Available fragment states:
* T = To be installed - initial status which means fragment is ready for installation
* P = Pending - status signaling that this fragment was already installed but installation has not finished
* I = Installed - fragment has been successfully installed
* R = Rolled back - fragment has been successfully rolled back
* C = Cancelled - fragment and whole dbchange has been cancelled

========================================

It can be also useful to dump dbchange or individual fragment content into the file.
E.g. in case of manual dbchange when sql can be executed by separate process only.
Following command will generate final sql content for environment DEV, dbchange 12345
and fragment with index 7 whole content. Output folder can be specified in 
configuration file. 
```
php bin/console.php dbchanger:generate DEV 12345 7
```
