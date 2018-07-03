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


