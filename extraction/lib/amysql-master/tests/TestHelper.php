<?php
/**
 * It is recommended that while testing AMysql, you set this in your
 * MySQL's my.ini file and restart mysqld afterwards:
 * innodb_flush_log_at_trx_commit=2
 *
 * It makes all the tests run a lot faster.
 **/
ob_start();
define('BASE_PATH', realpath(dirname(__FILE__) . '/../'));
define('APPLICATION_PATH', BASE_PATH);
define('APPLICATION_ENV', 'testing');

set_include_path(
	'.'
    . PATH_SEPARATOR . BASE_PATH
    . PATH_SEPARATOR . get_include_path()
);

/**
 * Make sure you have a mysql user and database preprepared for the tests
 **/
$conf = array();
if (file_exists($filename = dirname(__FILE__) . '/config/conf.dist.php')) {
    include $filename;
}
if (file_exists($filename = dirname(__FILE__) . '/config/conf.php')) {
    include $filename;
}

$sqlDriver = $conf['amysqlTestDriver'];
if (!$sqlDriver) {
    $sqlDriver = getenv('AMYSQL_DRIVER');
}
if (!$sqlDriver) {
    $sqlDriver = 'mysqli';
}

define('AMYSQL_TEST_HOST', $conf['amysqlTestHost']);
define('AMYSQL_TEST_PORT', $conf['amysqlTestPort']);
define('AMYSQL_TEST_USER', $conf['amysqlTestUser']);
define('AMYSQL_TEST_PASS', $conf['amysqlTestPass']);
define('AMYSQL_TEST_DB', $conf['amysqlTestDb']);
define('SQL_DRIVER', $sqlDriver);

if (class_exists('PHPUnit_Framework_TestCase')) {
    require_once dirname(__FILE__) . '/AMysql_TestCase.php';
}

require_once APPLICATION_PATH . '/AMysql.php';
