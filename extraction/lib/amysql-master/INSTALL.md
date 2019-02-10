Installation
=

Composer
-----
Install composer in your project:

```shell
curl -s https://getcomposer.org/installer | php
```

Create a composer.json file in your project root:

```json
{
    "require": {
        "amcsi/amysql": "1.*"
    }
}
```

Install via composer:

```shell
php composer.phar install
```

Add this line to your application’s index.php file (PHP 5.3+):

```php
<?php
require 'vendor/autoload.php';
```

Or just include AMysql.php if your PHP version is 5.1.*:

```php
<?php
require 'vendor/amcsi/amysql/AMysql.php';
```

Manual
-----

Copy files:

    AMysql.php
    AMysql/

To /path/to/libs/ (Zend Framework style) or /path/to/libs/AMysql/ (subprojects separated)

Then include AMysql.php or have AMysql.php be autoloadable.
