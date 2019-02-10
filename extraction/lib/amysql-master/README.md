AMysql
======
[![Build Status](https://travis-ci.org/amcsi/amysql.png?branch=next)](https://travis-ci.org/amcsi/amysql)

A MySQL wrapper guaranteed to work on any PHP 5.2+ or HHVM configuration with the mysqli extension or `mysql_*` functions available.
Also contains many tools to help build queries, manage them, profile them.


### Is AMysql for you?

* Feel like Zend_Db is way too bulky, but on the other hand does not give you useful tools for common problems?
* Afraid of using PDO or Mysqli as it might not be available on the production server?
* Do not like the boring task of assembling INSERTs and UPDATEs?

Then AMysql is the library for your project!

### What AMysql tries to be

* A library that allows you to work with mysql with any PHP configuration without requiring any extensions.
* A practical library that provides great tools for common situations in MySQL. This includes tools not available in any MySQL API (extension level or PHP level), or the combination of tools that would otherwise only be available separately in several different APIs, but never together.

### What AMysql is not

* **It's not an abstraction library.** Although I might add PostgreSQL support, the main focus is on MySQL, as abstraction takes focus away from being able to make good tools specifically for a type of database. Also, it would be too much work for me alone to look into how all the other databases function.
* **It's not an ORM.** There is support for fetching data into new instances of classes, but the focus is on ease of building queries.

Requirements
============

PHP 5.1.0+ is required, although this has only been tested on 5.2 and higher, and either the MySQLi extension or the mysql_* functions must be available.

Installation
============

Available on packagist.

See [INSTALL](INSTALL.md) file.

Usage
=====

Typically you want to make one instance of AMysql per db connection. AMysql lazy connects by default.

Apigen docs are available for this project: http://amcsi.github.io/amysql/apigen/

#### Instantiating AMysql

When instantiating AMysql, you can pass in either a mysql link resource or connection details as an array.

```php
<?php
$this->_amysql = new AMysql;
$this->_amysql->setConnDetails(array (
    'host' => 'localhost',
    'username' => 'user',
    'password' => 'pass',
    'db'        => 'db',
));
```

or

```php
<?php
$this->_amysql = new AMysql(array (
    'host' => 'localhost',
    'username' => 'user',
    'password' => 'pass',
    'db'        => 'db',
));
```

or

```php
<?php
$conn = mysql_connect($host, $user, $pass);
$amysql = new AMysql($conn);
$amysql->selectDb($db);
```
    
The full connection details array supports:

* `host` Host to connect to
* `username` Mysql user username
* `password` Mysql user password
* `db` The database to select
* `port`
* `clientFlags` mysql_* only. For the `mysql_connect()` `$client_flags` argument
* `driver` `mysql` or `mysqli`. Prefer a specific driver. By default when making a new connection, AMysql tries to use `MySQLi` if it is available, otherwise it falls back to the `mysql_*` functions
* `socket` - socket
* `defaultAutoCommit` Specify here whether autocommit is on. When using mysqli and autocommit is off on the MySQL server, this should be set to false.
* `autoPingSeconds` Each time a query is executed, it is checked if the given amount of seconds has passed since the last query, and if so, pings the server and reconnects if the connection has been lost. It is recommended that this is set to 20 seconds and prevents the script from dying from a 2006 error. Off (NULL) by default.
* `autoReconnect` If this is TRUE and an executed query fails with a 2006 mysql error, try to reconnect with the connection details and attempt to reexecute the query once before giving up. It is recommended that you set this to TRUE. WARNING: there might be edge cases related to writing data and the max packets setting which may result in 2006 mysql errors even if though the INSERT/UPDATE want through, resulting in a double write. I don't quite understand this, but it's never happened to me before. If you want to be extra careful, don't set this to TRUE, and only rely on `autoPingSeconds` instead.

```php
<?php
$data = array (
    'name' => 'adam',
    'description' => 'blah'
);
$amysql->insert('tablename', $data);
```

#### Inserting mysql expressions

Within prepared statement you can purposefully bind literal strings that will not be escaped or enclosed by quotes. Use it with caution.

To use it, see the example below:

```php
<?php
$date = $amysql->expr('CURRENT_TIMESTAMP');
// or new AMysql_Expr($amysql, 'CURRENT_TIMESTAMP');
$data = array (
    'name' => 'adam',
    'description' => 'blah',
    'date' => $date
);
$insertId = $amysql->insert('tablename', $data);
// INSERT INTO `tablename` (`name`, `description`, `date`) VALUES ('adam', 'blah', CURRENT_TIMESTAMP);
```

AMysql_Expr also supports a few predefined special expressions not only consisting of literal values, such as for making an `IN` list or for doing proper escaping for a `LIKE` expression. For more information, check out the [AMysql/Expr.php file](AMysql/Expr.php).

Example for an `IN` list:

```php
<?php
$materialsIn = $amysql->expr(
    AMysql_Expr::COLUMN_IN,
    'material',
    array('wood', 'metal', 'paper', 'plastic')
);
$sql = "SELECT * FROM tableName WHERE :materialsIn";

$amysql->query($sql, array('materialsIn' => $materialsIn));
// SELECT * FROM tableName WHERE `material` IN ('wood', 'metal', 'paper', 'plastic')
```

Example for escaping LIKE. Note the automatic escaping handling with the = sign ( http://stackoverflow.com/a/3683868/1381550 ). Do not worry about actual percent and underscore lines, as they will properly be escaped and will be treated as literal characters. The default LIKE pattern is wrapping your search string between two (%) signs for a "contains" match. This is represented internally with a `sprintf()` pattern of `%%%s%s` meaning (literal %), (the string), (literal %). You can change this in the 3rd parameter of `AMysql_Expr::__construct`

```php
<?php
$descriptionLike = $amysql->expr(
    AMysql_Expr::ESCAPE_LIKE,
    'part'
));

$sql = "SELECT * FROM articles WHERE description LIKE :descriptionLike";
$amysql->query($sql, array('descriptionLike' => $descriptionLike));
// SELECT * FROM articles WHERE description LIKE '%part%' ESCAPE '='



// "begins with" example

$descriptionLike = $amysql->expr(
    AMysql_Expr::ESCAPE_LIKE,
    '100% success',
    '%%%s', // begins with
));

$sql = "SELECT * FROM articles WHERE description LIKE :descriptionLike";
$amysql->query($sql, array('descriptionLike' => $descriptionLike));
// SELECT * FROM articles WHERE description LIKE '%100=% success' ESCAPE '='
// note the automatic escaping of the (%) sign
```

#### Inserting multiple rows of data

```php
<?php
$data = array (
    array (
        'name' => 'adam',
        'description' => 'blah'
    ),
    array (
        'name' => 'bob',
        'description' => 'blahblah'
    )
);
$id = $amysql->insert('tablename', $data);
$affectedRows = $amysql->lastStatement->affectedRows();

// or you can achieve the same result by having the column names be the outer indexes.

$data = array (
    'name' => array (
        'adam', 'bob'
    ),
    'description' => array (
        'blah', 'blahblah'
    )
);
$id = $amysql->insert('tablename', $data);
$affectedRows = $amysql->lastStatement->affectedRows();
```

#### Updating a single row

```php
<?php
/**
 * Update the name to bob and the description to blahmodified for all rows
 * with an id of 2.
 */
$data = array (
    'name' => 'bob',
    'description' => 'blahmodified'
);
$success = $amysql->update('tablename', $data, 'id = ?', array('2'));
$affectedRows = $amysql->lastStatement->affectedRows();
```

#### Updating multiple rows

You can update multiple rows with the same `insert()` method as for single rows if you pass a multidimensional array. It can be an array or rows, or an array of columns with an array of values.

```php
<?php
/**
 * Update the name to bob and the description to blahmodified for all rows
 * with an id of 2, and update the name to carmen and the description to
 * anothermodified for all rows with an id of 3.
 */
$data = array (
    array (
        'id'            => 2
        'name'          => 'bob',
        'description'   => 'blahmodified'
    ),
    array (
        'id'            => 3
        'name'          => 'carmen',
        'description'   => 'anothermodified'
    )
);
$success = $amysql->updateMultipleByData('tablename', $data, 'id');
$affectedRows = $amysql->multipleAffectedRows;
```

#### Deleting rows

```php
<?php
$where = 'id = ?';
$success = $amysql->delete('tablename', $where, array ('2'));
$affectedRows = $amysql->lastStatement->affectedRows();
```

#### Queries throw AMysql_Exceptions by default.

```php
<?php
try {
    $amysql->query("SELECTbad-syntaxError-!#");
}
catch (AMysql_Exception $e) {
    trigger_error($e, E_USER_WARNING); // the exception converted to string
    // contains the mysql error code, the error message, and the query string used.
    // $e->getCode() contains the mysql error code
}
```

#### AMysql_Exceptions can be checked for several constraint related rejections in a fairly simple and not too error prone way.

```php
<?php
try {
    $data = array('passwordHash' => $passwordHash, 'email' => 'myemail@email.com');
    $amysql->insert('tableName', $data);
} catch (AMysql_Exception $e) {
    if (AMysql_Exception::CODE_DUPLICATE_ENTRY === $e->getCode()) {
        // $e->getProps(0) === 'myemail@email.com'
        if ('emailUniqueIndexName' === $e->getProps(1)) {
            $formError = 'A user with this email has already registered';
        } else {
            // code shouldn't get to here
            $formError = 'An unknown error has occured';
            trigger_error($e, E_USER_WARNING);
        }
    } else {
        $formError = 'An unknown error has occured';
        trigger_error($e, E_USER_WARNING);
    }
}
```

#### Selecting (without AMysql_Select)

```php
<?php
// with named parameters (do not use apostrophes)
$binds = array (
    'id' => 1,
    'state' => 'active'
);
$stmt = $amysql->query("SELECT * FROM tablename WHERE id = :id AND state = :state", $binds);
$results = $stmt->fetchAllAssoc();

// with unnamed parameters (never use apostrophes when binding values)
$binds = array (1, 'active');
$stmt = $amysql->query("SELECT * FROM tablename WHERE id = ? AND state = ?", $binds);
$results = $stmt->fetchAllAssoc();
$numRows = $stmt->numRows();
```
    
Note that if there is only 1 question mark in the prepared string, you may also pass the `$binds` as a scalar value and it will be treated as if it were the within an array. If you are expecting a possible `null` value to be bound, do not use the scalar method though (always use an array in that case).

P.S. this is also true for every method that expects a `$binds` array.

#### Preparing select first, executing later (without AMysql_Select)
```php
<?php
$binds = array (
    'id' => 1,
    'state' => 'active'
);
$stmt = $amysql->prepare("SELECT * FROM tablename WHERE id = :id AND state = :state");
$stmt->execute($binds);
$results = $stmt->fetchAllAssoc();
```

####And now with a new AMysql_Select class to help assemble a SELECT SQL string:

```php
<?php
$select = $mysql->select();
$select 
    ->option('DISTINCT')
    ->option('SQL_CALC_FOUND_ROWS')
    ->from(array ('table1', 't2alias' => 'table2'))
    ->from(array ('t3alias' => 'table3'), array ('t3_col1' => 'col1', 't3_col2' => 'col2'))
    ->column('t2alias.*')
    ->column (array ('t1_col1' => 'table1.col1'))
    ->columnLiteral('table7, table8, CURRENT_TIMESTAMP AS ctimestamp')
    ->join(
        '',
        array ('t4alias' => 'table4'),
        't4alias.t1_id = table1.id',
        array ('t4lol', 't4lol2aliased' => 't4lol2')
    )
    ->join('left', array ('table5'), 't2alias.colx = table5.coly', array (), true)
    ->join('cross', array ('table6'), 't3alias.colx = table6.coly', array ())
    ->groupBy('t2alias.col1')
    ->groupBy('t2alias.col2', true, true)
    ->groupBy('t2alias.col3', true)
    ->having('1 = 1')
    ->having('2 = 2')
    ->orderBy('t3alias.col1')
    ->orderBy('t3alias.col2', true, true)
    ->orderBy('t3alias.col3', true)
    ->where('3 = :aBind')
    ->where("'yes' = :anotherBind")
    ->limit(100)
    ->offset(200)
;
$select->execute(array ('aBind' => 3, 'anotherBind' => 'yes'));
/*
SELECT DISTINCT `t3alias`.`col1` AS `t3_col1`, `t3alias`.`col2` AS `t3_col2`,
    `t2alias`.*, `table1`.`col1` AS `t1_col1`, `t4alias`.`t4lol`,
    `t4alias`.`t4lol2` AS `t4lol2aliased`,
    table7, table8, CURRENT_TIMESTAMP AS ctimestamp'
    FROM `table1`, `table2` AS `t2alias`, `table3` AS `t3alias`
    LEFT JOIN `table5` ON (t2alias.colx = table5.coly)
    JOIN `table4` AS `t4alias` ON (t4alias.t1_id = table1.id)
    CROSS JOIN `table6` ON (t3alias.colx = table6.coly)
    WHERE 3 = 3 AND 'yes' = 'yes'
    GROUP BY `t2alias`.`col2` DESC, `t2alias`.`col1`, `t2alias`.`col3` DESC
    HAVING 1 = 1 AND 2 = 2
    ORDER BY `t3alias`.`col2` DESC, `t3alias`.`col1`, `t3alias`.`col3` DESC
    LIMIT 100
    OFFSET 200
 */
$foundRows = $amysql->foundRows(); // resolves "SELECT FOUND_ROWS()" for "SQL_CALC_FOUND_ROWS"
```

Read the commented [AMysql_Select](AMysql/Select.php) file for more details.

A documentation on binding parameters can be found in the comments for [AMysql_Statement](AMysql/Statement.php)::execute(). Be sure to check it out.

#### Profiling

A simple example without having to make your own HTML template:

```php
$profiler = $amysql->getProfiler();

// ... execute a bunch of MySQL queries

$view->profiler = $profiler; // add it to your view.
$view->render('layout.phtml');

// layout.phtml:

<html>
    <head>
    </head>
    <body>
        <?php include 'content.phtml' ?>

        <?php if ('dev' == APP_ENV): ?>
        <?php echo $this->profiler->getAsHtml() ?>
        <?php endif ?>
    </body>
</html>

```

Alternatively, check out [AMysql_Profiler](AMysql/Profiler.php) for more options.

#### Other methods

Many other useful methods are available as well. Check out the source files and read the documentation for the methods.

Apigen docs are available for this project: http://amcsi.github.io/amysql/apigen/

## License

MIT

## Contribution

If anything doesn't work the way you expect it to, please report in as an issue on github.

If you can provide pull requests to fixes, that would be even better. Work from the `master` or `develop` branch, whichever is the latest.
