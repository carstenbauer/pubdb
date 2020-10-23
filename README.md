Simple publication management system by Carsten Bauer (bauer@thp.uni-koeln.de)

# Configuration

You need to create two files, "config/config.php" and "config/dbconfig.php".

Dummy config/config.php file:
-----------------------------
```
<?php

const SIMPLEPIE_CACHE_LOCATION = "C:/xampp/htdocs/simplepie-1.5/cache";
const SIMPLEPIE_AUTOLOADER_LOCATION = "C:/xampp/htdocs/simplepie-1.5";

const INSERTPASSWORD = "allyourbasearebelongtous";

const BIBUTILS_BIN_FOLDER = "C:/xampp/htdocs/publications/tools/parser/bibutilsbinaries";

const PROJECT_ROOT = "C:/xampp/htdocs/publications/";

?>
```

Dummy config/dbconfig.php file:
-------------------------------

```
<?php

// Database credentials
const SERVERNAME = "host/name/of/mysql/server"; # e.g. localhost or mysql2.rrz.uni-koeln.de
const USERNAME = "carsten";
const PASSWORD = "mypassword";
const DBNAME = "crc183";


?>
```

Dummy database:
-------------------------------

See [example_db.sql](https://github.com/crstnbr/pubdb/blob/master/example_db.sql).

# External dependencies

We use Simplepie for arxiv parsing. Just download it somewhere (http://simplepie.org/downloads/?download) and specify paths to autoloader (it's in the root directory of simplepie) and a new folder "cache", that you'll have to create somewhere, in config/config.php.

Note that, probably, the Simplepie's cache folder (see above) and the folder "tmp" need to be writable.
