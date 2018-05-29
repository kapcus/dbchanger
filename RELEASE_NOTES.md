0.5.2
=================

* Added debug mode for command `register` which will ignore all dependancies in `_requirements.txt`
```
php bin/console.php dbchanger:register 12345 -d
```
* Command `install` will report in case of success, e.g. `OK - DbChange installed successfully.`
