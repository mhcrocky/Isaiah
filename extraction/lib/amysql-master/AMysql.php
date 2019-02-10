<?php
/**
 * Mysql abstraction which only uses mysql_* functions
 * @author Szerémi Attila
 *   
 **/
$dir = dirname(realpath(__FILE__));
require_once $dir . '/AMysql/Abstract.php';

class AMysql extends AMysql_Abstract 
{
}
?>
