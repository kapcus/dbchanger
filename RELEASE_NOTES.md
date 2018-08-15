0.5.4 - 2018/08/15
=================
* Added command `display` for displaying content of installed fragment or installation log entry.
```
php bin/console.php dbchanger:display F5
php bin/console.php dbchanger:display L10
```

* Added command `log` for displaying installation log history for given installed fragment.
```
php bin/console.php dbchanger:log F5
```

* Extended command `mark` to support fragment range and also marking of installation.
```
php bin/console.php dbchanger:mark F5 I
php bin/console.php dbchanger:mark F5-F10 I
php bin/console.php dbchanger:mark I2 I
```

* Changed logic of command `status` - from now, it will display detail table for all existing installations 
(until now, it was displaying the details for the latest installation only).

* New Installation statuses: `(N)ew, (P)ending, (I)nstalled, (C)ancelled`
* New Installation fragment statuses: `(N)ew, (P)ending, (I)nstalled, (S)kipped, (C)ancelled`

* New notation introduced: 

```
I1 = installation with id 1
F2 = installation fragment with id 2
X3 = fragment index with id 3
L4 = installation log with id 4
```


0.5.3 - 2018/07/03
=================
* Added overwrite mode for command `register` which will overwrite existing DbChanges with the same code (providing no pending installation is detected)
```
php bin/console.php dbchanger:register 12345
php bin/console.php dbchanger:register 12345 -o
```


0.5.2 - 2018/05/29
=================

* Added debug mode for command `register` which will ignore all dependancies in `_requirements.txt`
```
php bin/console.php dbchanger:register 12345 -d
```
* Command `install` will report in case of success, e.g. `OK - DbChange installed successfully.`


