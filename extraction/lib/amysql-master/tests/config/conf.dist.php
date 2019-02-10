<?php
// Overrides go in conf.php
$conf['amysqlTestHost'] = 'localhost';
$conf['amysqlTestPort'] = 3306;
$conf['amysqlTestUser'] = 'travis';
$conf['amysqlTestPass'] = '';
$conf['amysqlTestDb'] = 'amysql';
// change this to test different drivers (in conf.php)
// if NULL, looks at the AMYSQL_DRIVER instead, but defaults to mysqli
$conf['amysqlTestDriver'] = null;
