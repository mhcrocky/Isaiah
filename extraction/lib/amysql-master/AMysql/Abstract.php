<?php /* vim: set expandtab : */
/**
 * Mysql abstraction which only uses mysql_* functions, but can use
 * Mysqli also.
 *
 * For information on binding placeholders, @see AMysql_Statement::execute()
 *
 * @todo Maybe remove automatic dot detection for identifier escaping.
 * @todo Allow fetch methods to auto execute the statement, or throw an
 *       exception if the query hasn't been executed yet.
 * @todo Delay the call of some mysql queries to execute right before a
 *       proper one. e.g. calling 'SET NAMES utf8' every time after setting
 *       the connection params won't give you much use for lazy connection.
 *
 * Visit https://github.com/amcsi/amysql
 * @author      Szerémi Attila
 * @license     MIT License; http://www.opensource.org/licenses/mit-license.php
 **/
abstract class AMysql_Abstract
{

    public $insertId; // last insert id
    public $lastStatement; // last AMysql_Statement
    public $link = null; // mysql link
    public $isMysqli; // mysql or mysqli
    public $error; // last error message
    public $errno; // last error number
    public $result; // last mysql result
    public $query; // last used query string
    public $affectedRows; // last affected rows count
    /**
     * What value to reset autocommit to after a rollback or commit when
     * using Mysqli.
     * 
     * @var boolean
     * @access public
     */
    public $defaultAutoCommit = true;

    /**
     * Contains the number of total affected rows by the last multiple statement deploying
     * method, such as updateMultipleByData and updateMultipleByKey
     * 
     * @var int
     */
    public $multipleAffectedRows;

    /**
     * Whether to throw AMysql_Exceptions on mysql errors. If false, trigger_error is called instead.
     * It is recommended to leave this on TRUE.
     * 
     * @var boolean
     * @access public
     */
    public $throwExceptions = true;

    /**
     * Whether AMysql_Exceptions should trigger errors by default on construction.
     * This is for legacy behavior (before v1.1.0). It is recommended to keep
     * is at FALSE.
     * 
     * @var int
     * @access public
     */
    public $triggerErrorOnException = false;

    /**
     * Whether backtraces should be added to each array for getQueriesData(). If true,
     * backtraces will be found under the 'backtrace' key.
     *
     * @var boolean
     */
    public $includeBacktrace = false;

    /**
     * Amount of seconds needed to pass by to automatically call mysql_ping before
     * any query. This helps prevent "2006: Server has gone away" errors that may
     * be caused by mysql queries being performed after other long, blocking requests.
     * 
     * Mysql pinging isn't guaranteed to automatically reconnect if the connection
     * actually gets lost, so it is recommended to set the connection details with
     * setConnDetails even if you are using a preexisting mysql(i) resource.
     *
     * Can be set in the connection details array or $this->setAutoPingSeconds()
     * 
     * @var int|FALSE
     * @access protected
     */
    protected $autoPingSeconds = false;

    /**
     * This is set automatically by the script. Do not change this value manually!
     * 
     * @var mixed
     * @access public
     */
    public $autoPing;

    /**
     * If a "2006: Server has gone away" error would occur, attempt to reconnect
     * once, resending the same query before giving up.
     * 
     * Mysql pinging isn't guaranteed to automatically reconnect if the connection
     * actually gets lost, so it is recommended to set the connection details with
     * setConnDetails even if you are using a preexisting mysql(i) resource.
     *
     * Can be set in the config with the "autoReconnect" key, or by using the
     * $this->setAutoReconnect() method.
     * 
     * @var false
     * @access protected
     */
    protected $autoReconnect = false;

    /**
     * Last time a query has been executed or a mysql connection has been made.
     * No need to modify externally.
     * 
     * @var int
     * @access public
     */
    public $lastPingTime;

    /**
     * Let AMysql_Statement::bindParam() and AMysql_Statement::bindValue()
     * use indexes starting from 1 instead of 0 in case of unnamed placeholders.
     * The same way PDO does it. The factory default is false.
     **/
    public $pdoIndexedBinding = false;
    /**
     * The fetch mode. Can be changed here or set at runtime with
     * setFetchMode.
     * 
     * @var string
     * @access protected
     */
    protected $_fetchMode = self::FETCH_ASSOC;

    protected $connDetails = array();
    protected $profiler;
    protected $inTransaction = false;

    protected $isAnsi = false;
    protected $identifierQuoteChar = '`';

    /**
     * Whether Mysqli is considered able to use.
     * Do not change; it is automatically set. 
     * To force a specific mysql driver to be used, use
     * $this->setConnDetails()
     * @var boolean
     */
    public static $useMysqli;

    const FETCH_ASSOC	= 'assoc';
    const FETCH_OBJECT	= 'object';
    const FETCH_ARRAY	= 'array';
    const FETCH_ROW	    = 'row';

    /**
     * @constructor
     * @param resource|mysqli|array|string $resOrArrayOrHost    (Optional) Either a valid mysql or mysqli connection
     *					            resource/object, or a connection details array
     *					            as according to setConnDetails() (doesn't auto connect),
     *					            or parameters you would normally pass to the mysql_connect()
     *					            function. (auto connects)
     *					            (NOTE that this last construction method is
     *					            discouraged and may be deprecated and removed in later versions)
     *					            (Also note that I mention the arguments mysql_connect(), but only
     *					            the arguments. This library will still connect to mysqli if it
     *					            is available)
     *
     * @see $this->setConnDetails()
     * @see $this->setConnDetails()
     *
     **/
    public function __construct(
        $resOrArrayOrHost = null,
        $username = null,
        $password = null,
        $newLink = false,
        $clientFlags = 0
    ) {
        if (!class_exists('AMysql_Statement')) {
            // Assume no autoloader, and load everything manually
            $dir = dirname(realpath(__FILE__));
            require_once $dir . '/Exception.php';
            require_once $dir . '/Expr.php';
            require_once $dir . '/Statement.php';
            require_once $dir . '/Iterator.php';
            require_once $dir . '/Select.php';
            require_once $dir . '/Profiler.php';
        }

        $this->setAutoPingSeconds($this->autoPingSeconds);

        // Use mysqli by default if available and PHP is at least of version 5.3.0 (required).
        // Can be overridden by connection details.
        self::$useMysqli = class_exists('Mysqli', false) && function_exists('mysqli_stmt_get_result');

        if (is_resource($resOrArrayOrHost) &&
            0 === strpos(get_resource_type($resOrArrayOrHost), 'mysql link')
        ) {
            $this->link = $resOrArrayOrHost;
            $this->isMysqli = false;
        } elseif ($resOrArrayOrHost instanceof Mysqli) {
            $this->link = $resOrArrayOrHost;
            $this->isMysqli = true;
        } elseif (is_array($resOrArrayOrHost)) {
            $this->setConnDetails($resOrArrayOrHost);
        } elseif (is_string($resOrArrayOrHost)) {
            $this->oldSetConnDetails($resOrArrayOrHost, $username, $password, $newLink, $clientFlags);
            $this->connect();
        }
    }

    /**
     * Sets the connection details
     * 
     * @param array $connDetails        An array of details:
     *                                  host - hostname or ip
     *                                  username - username
     *                                  password - password
     *                                  db - db to auto connect to
     *                                  port - port
     *                                  driver - force 'mysql' or 'mysqli'
     *                                  socket - socket
     *                                  defaultAutoCommit - @see $this->defaultAutoCommit
     *                                  autoPingSeconds - @see $this->autoPingSeconds
     *                                  autoReconnect - @see $this->autoReconnect
     *
     *
     * @access public
     * @return AMysql_Abstract (chainable)
     */
    public function setConnDetails(array $cd)
    {
        $defaults = array (
            'socket' => ini_get('mysqli.default_socket'),
            'db' => null,
            'newLink' => false,
            'clientFlags' => 0,
        );
        $this->connDetails = array_merge($defaults, $cd);
        if (array_key_exists('autoPingSeconds', $cd)) {
            $this->setAutoPingSeconds($cd['autoPingSeconds']);
        }
        if (array_key_exists('autoReconnect', $cd)) {
            $this->setAutoReconnect($cd['autoReconnect']);
        }
        if (array_key_exists('defaultAutoCommit', $cd)) {
            $this->setDefaultAutoCommit($cd['defaultAutoCommit']);
        }
        return $this;
    }

    /**
     * @see mysql_connect() 
     * 
     * @param string $host (optional)
     * @param string $username (optional)
     * @param string $password (optional)
     * @param boolean $newLink (optional)
     * @param int $clientFlags (optional)
     * @access public
     * @return AMysql_Abstract (chainable)
     */
    public function oldSetConnDetails(
        $host = null,
        $username = null,
        $password = null,
        $newLink = false,
        $clientFlags = 0
    ) {
        $port = null;
        $cd = array ();
        if ($host && false !== strpos($host, ':')) {
            list ($host, $port) = explode(':', $host, 2);
        }
        $cd['host'] = $host;
        $cd['port'] = $port;
        $cd['username'] = $username;
        $cd['password'] = $password;
        $cd['newLink'] = $newLink;
        $cd['clientFlags'] = $clientFlags;
        $this->setConnDetails($cd);
        return $this;
    }

    /**
     * Connects to the database with the configured settings.
     * Sets $this->link and $this->isMysqli
     * 
     * @access public
     * @return AMysql_Abstract (chainable)
     */
    public function connect()
    {
        $this->error = null;
        $this->errno = null;

        $cd = $this->connDetails;
        if (!$cd) {
            throw new LogicException("No connection details set. Could not connect.");
        }
        if (isset($cd['driver'])) {
            switch ($cd['driver']) {
                case 'mysqli':
                    $isMysqli = true;
                    break;
                case 'mysql':
                    $isMysqli = false;
                    break;
                default:
                    throw new LogicException ("Unknown driver: `$cd[driver]`");
                    break;
            }
        } else {
            $isMysqli = self::$useMysqli;
        }
        $this->isMysqli = $isMysqli;
        $newLink = !empty($cd['newLink']);
        $res = null;
        if (isset($cd['host'], $cd['username'], $cd['password'])) {
            if ($isMysqli) {
                $port = isset($cd['port']) ? $cd['port'] : ini_get('mysqli.default_port');
                $res = mysqli_connect(
                    $cd['host'],
                    $cd['username'],
                    $cd['password'],
                    $cd['db'],
                    $port,
                    $cd['socket']
                );
            } else {
                $host = isset($cd['port']) ? "$cd[host]:$cd[port]" : $cd['host'];
                $res = mysql_connect(
                    $host,
                    $cd['username'],
                    $cd['password'],
                    $newLink,
                    $cd['clientFlags']
                );
            }
        }
        if ($res) {
            $this->lastPingTime = time(); // otherwise can cause infinite recursion.
            $this->link = $res;

            if (!$isMysqli && !empty($cd['db'])) {
                $this->selectDb($cd['db']);
            }
        } else {
            if ($this->isMysqli) {
                $this->handleError(
                    mysqli_connect_error(),
                    mysqli_connect_errno(),
                    '(connection to mysql)'
                );
            } else {
                $this->handleError(
                    mysql_error(),
                    mysql_errno(),
                    '(connection to mysql)'
                );
            }
        }
        return $this;
    }

    /**
     * Closes the connection and unsets its link.
     * 
     * @access public
     * @return AMysql_Abstract (chainable)
     */
    public function close()
    {
        if ($this->link instanceof Mysqli) {
            $this->link->close();
        }
        else if (is_resource($this->link)) {
            mysql_close($this->link);
        }
        $this->link = null;
        return $this;
    }

    /**
     * Returns the DB connection link. Connects to the DB if not connected.
     * Does not check if the connection has since been broken.
     * 
     * @access public
     * @return resource|mysqli            Connection resource or object
     */
    public function autoConnect()
    {
        if (!$this->link) {
            $this->connect();
        }
        return $this->link;
    }

    /**
     * Reconnects to the database with the last saved connection details. 
     * 
     * @access public
     * @return void
     */
    public function forceReconnect()
    {
        $oldConnDetails = (array) $this->connDetails;
        $this->connDetails['newLink'] = true;
        $this->connect();
        $this->connDetails = $oldConnDetails;
    }

    /**
     * Sets the amount of seconds needed to pass - since last communicating
     * with the database - before communicating again, to ping the
     * database server beforehand and also reconnect if the connection has
     * been since lost.
     * 
     * @param int|FALSE $autoPingSeconds        The amount of seconds, or
     *                                          FALSE if it should be disabled
     * @access public
     * @return AMysql_Statement (chainable)
     */
    public function setAutoPingSeconds($autoPingSeconds)
    {
        $this->autoPingSeconds = $autoPingSeconds;
        $this->autoPing = is_numeric($this->autoPingSeconds);
        return $this;
    }

    /**
     * @see $this->autoReconnect 
     * 
     * @param boolean
     * @access public
     * @return AMysql_Abstract (chainable)
     */
    public function setAutoReconnect($autoReconnect)
    {
        $this->autoReconnect = $autoReconnect == true;
        return $this;
    }

    /**
     * getAutoReconnect
     * 
     * @access public
     * @return boolean
     */
    public function getAutoReconnect()
    {
        return $this->autoReconnect;
    }

    /**
     * @see $this->defaultAutoCommit
     * 
     * @param boolean
     * @access public
     * @return AMysql_Abstract (chainable)
     */
    public function setDefaultAutoCommit($defaultAutoCommit)
    {
        $this->defaultAutoCommit = $defaultAutoCommit == true;
        return $this;
    }

    /**
     * Returns the default fetch mode. See the FETCH_ class constants.
     * 
     * @access public
     * @return string
     */
    public function getFetchMode()
    {
        return $this->_fetchMode;
    }

    /**
     * Selects the given database.
     * 
     * @param string $db 
     * @return AMysql_Abstract (chainable)
     */
    public function selectDb($db)
    {
        $this->connDetails['db'] = $db; // for reconnecting later
        $isMysqli = $this->isMysqli;
        $link = $this->autoPingConnect();
        $result = $isMysqli ? $link->select_db($db) : mysql_select_db($db, $link);
        if (!$result) {
            $error = $isMysqli ? $link->error : mysql_error($link);
            $errno = $isMysqli ? $link->errno : mysql_errno($link);
            $this->handleError($error, $errno, 'USE ' . $db);
        }
        return $this;
    }

    /**
     * Takes care of setting everything to utf-8
     * 
     * @access public
     * @return AMysql_Abstract (chainable)
     */
    public function setUtf8()
    {
        return $this->setCharset('utf8')->setNames('utf8');
    }

    /**
     * Changes the character set of the connection.
     * 
     * @param string $charset Example: utf8
     * @return AMysql_Abstract (chainable)
     */
    public function setCharset($charset)
    {
        $this->autoConnect();
        $isMysqli = $this->isMysqli;
        if ($isMysqli) {
            $result = $this->link->set_charset($charset);
        } else {
            if (!function_exists('mysql_set_charset')) {
                function mysql_set_charset($charset, $link = null)
                {
                    return mysql_query("SET CHARACTER SET '$charset'", $link);
                }
            }
            $result = mysql_set_charset($charset, $this->link);
        }
        if (!$result) {
            $error = $isMysqli ? $this->link->error : mysql_error($this->link);
            $errno = $isMysqli ? $this->link->errno : mysql_errno($this->link);
            $this->handleError($error, $errno, "(setting charset)");
        }
        return $this;
    }

    /**
     * Performs SET NAMES <charset> to change the character set. It may be
     * enough to use $this->setCharset().
     * 
     * @param string $names Example: utf8
     * @return AMysql_Abstract (chainable)
     */
    public function setNames($names)
    {
        $stmt = $this->query("SET NAMES $names");
        return $this;
    }

    /**
     * Sets whether ANSI mode is on. Does not change the database's mode,
     * you just have to call this method when you know ANSI mode is being
     * used in the mysql connection, so that escaping is done according
     * to the correct mode.
     * ANSI mode is off my default on most mysql installations.
     * 
     * @param boolean $isAnsi 
     * @access public
     * @return AMysql_Abstract (chainable)
     */
    public function setAnsi($isAnsi)
    {
        $this->isAnsi = $isAnsi;
        $this->identifierQuoteChar = $isAnsi ? '"' : '`';
        return $this;
    }

    /**
     * Returns whether ANSI mode is being used by this instance (not the database)
     *
     * @return boolean
     **/
    public function isAnsi()
    {
        return $this->isAnsi;
    }

    /**
     * Does a simple identifier escape. It should be fail proof for that literal identifier.
     *
     * @param string $identifier    The identifier
     * @param string $qc            The quote character. Default: `
     *
     * @return string               The escaped identifier.
     **/
    public static function escapeIdentifierSimple($identifier, $qc = '`')
    {
        return $qc . addcslashes($identifier, "$qc\\") . $qc;
    }

    /**
     * Escapes an identifier. If there's a dot in it, it is split
     * into two identifiers, each escaped, and joined with a dot.
     *
     * @param string $identifier    The identifier
     * @param string $qc            The quote character. Default: `
     *
     * @return string               The escaped identifier.
     **/
    protected static function _escapeIdentifier($identifier, $qc)
    {
        $exploded = explode('.', $identifier);
        $count = count($exploded);
        $identifier = $exploded[$count-1] == '*' ?
            '*' :
            self::escapeIdentifierSimple($exploded[$count-1], $qc);
        if (1 < $count) {
            $identifier = "$qc$exploded[0]$qc.$identifier";
        }
        $ret = $identifier;
        return $ret;
    }

    /**
     * Escapes an identifier, such as a column or table name.
     * Includes functionality for making an AS syntax.
     *
     * @deprecated Do not rely on this static method. Its public visibility or name may be changed
     *  in the future. Use the non-static escapeTable() method instead.
     *
     * @param string $identifierName    The identifier name. If it has a dot in
     *                                  it, it'll automatically split the
     *                                  identifier name into the
     *                                  `tableName`.`columnName` syntax.
     *
     * @param string $as                (Optional) adds an AS syntax, but only
     *                                  if it's a string. The value is the alias
     *                                  the identifier should have for the query.
     *
     * @param $qc                       The quote character. Default: `
     *
     * @todo Possibly change the functionality to remove the automatic dot 
     * detection,
     * 	and ask for an array instead?
     *
     * e.g.
     *  echo $amysql->escapeIdentifier('table.order', 'ot');
     *  // `table`.`order` AS ot
     **/
    public static function escapeIdentifier($identifierName, $as = null, $qc = '`')
    {
        $asString = '';
        $escapeIdentifierName = true;
        if ($as and !is_numeric($as)) {
            $asString = ' AS ' . $as;
        } elseif (is_string($identifierName) and (false !==
            strpos($identifierName, ' AS '))
        ) {
            $exploded = explode(' AS ', $identifierName);
            $identifierName = $exploded[0];
            $asString = ' AS ' . $exploded[1];
        }
        if ($identifierName instanceof AMysql_Expr) {
            $ret = $identifierName->__toString() . $asString;
        } else {
            $ret = self::_escapeIdentifier($identifierName, $qc) . $asString;
        }
        return $ret;
    }

    /**
     * Escapes a table name.
     *
     * @param string $identifierName    The identifier name. If it has a dot in
     *                                  it, it'll automatically split the
     *                                  identifier name into the
     *                                  `tableName`.`columnName` syntax.
     *
     * @param string $as                (Optional) adds an AS syntax, but only
     *                                  if it's a string. The value is the alias
     *                                  the identifier should have for the query.
     *
     * @return string
     */
    public function escapeTable($tableName, $as = null)
    {
        return self::escapeIdentifier($tableName, $as, $this->identifierQuoteChar);
    }

    /**
     * Escapes a column name.
     *
     * @param string $identifierName    The identifier name. If it has a dot in
     *                                  it, it'll automatically split the
     *                                  identifier name into the
     *                                  `tableName`.`columnName` syntax.
     *
     * @param string $as                (Optional) adds an AS syntax, but only
     *                                  if it's a string. The value is the alias
     *                                  the identifier should have for the query.
     *
     * @return string
     */
    public function escapeColumn($columnName, $as = null)
    {
        return $this->escapeTable($columnName, $as, $this->identifierQuoteChar);
    }

    /**
     * Returns whether a MySQL TRANSACTION is in progress,
     * based off method calls.
     * 
     * @access public
     * @return boolean
     */
    public function inTransaction()
    {
        return $this->inTransaction;
    }

    /**
     * Performs a mysql ROLLBACK.
     *
     * @todo checks
     * @return mixed        Do not rely on this return value; it may change in
     *                      the future.
     **/
    public function startTransaction()
    {
        $link = $this->autoConnect();
        if ($link instanceof Mysqli) {
            $link->autocommit(false);
            $ret = true;
        } else {
            $ret = $this->query('START TRANSACTION');
        }
        $this->inTransaction = true;
        return $ret;
    }

    /**
     * Performs a mysql COMMIT.
     *
     * @todo checks
     * @return mixed        Do not rely on this return value; it may change in
     *                      the future.
     **/
    public function commit()
    {
        $link = $this->autoConnect();
        $ret = $this->query('COMMIT');
        if ($link instanceof Mysqli) {
            $link->autocommit($this->defaultAutoCommit);
        }

        $this->inTransaction = false;
        return $ret;
    }

    /**
     * Performs a mysql ROLLBACK.
     *
     * @todo checks
     * @return mixed        Do not rely on this return value; it may change in
     *                      the future.
     **/
    public function rollback()
    {
        $link = $this->autoConnect();
        $ret = $this->query('ROLLBACK');
        if ($link instanceof Mysqli) {
            $link->autocommit($this->defaultAutoCommit);
        }

        $this->inTransaction = false;
        return $ret;
    }

    /**
     * Executes a query by an sql string and binds.
     *
     * @param string $sql The SQL string.
     * @param mixed $binds The binds or a single bind.
     *
     * @return AMysql_Statement
     **/
    public function query($sql, $binds = array ())
    {
        $stmt = new AMysql_Statement($this);
        $result = $stmt->query($sql, $binds);
        return $stmt;
    }

    /**
     * Executes a query, and returns the first found row's first column's value.
     * Throws a warning if no rows were found.
     *
     * @param string $sql   The SQL string.
     * @param mixed $binds  The binds or a single bind.
     *
     * @return string|int
     **/
    public function getOne($sql, $binds = array ())
    {
        $stmt = new AMysql_Statement($this);
        $stmt->query($sql, $binds);
        return $stmt->result(0, 0);
    }

    /**
     * Like $this->getOne(), except returns a null when no result is found,
     * without throwing an error.
     *
     * @param string $sql   The SQL string.
     * @param mixed $binds  The binds or a single bind.
     *
     * @see AMysql_Abstract::getOne()
     *
     * @return string|int|NULL
     **/
    public function getOneNull($sql, $binds = array ())
    {
        $stmt = new AMysql_Statement($this);
        $stmt->query($sql, $binds);
        return $stmt->resultNull(0, 0);
    }

    /**
     * Like $this->getOne(), but casts the result to an int. No exception is
     * thrown when there is no result.
     *
     * @param string $sql The SQL string.
     * @param mixed $binds The binds or a single bind.
     *
     * @see AMysql_Abstract::getOne()
     *
     * @return int
     **/
    public function getOneInt($sql, $binds = array ())
    {
        $stmt = new AMysql_Statement($this);
        $stmt->query($sql, $binds);
        return $stmt->resultInt(0, 0);
    }

    /**
     * Prepares a mysql statement. It is to be executed.
     *
     * @param string $sql               The unbound SQL string.
     *
     * @return AMysql_Statement         A new statement instance.
     **/
    public function prepare($sql)
    {
        $stmt = new AMysql_Statement($this);
        $stmt->prepare($sql);
        return $stmt;
    }

    /**
     * Returns a new instance of AMysql_Select.
     * By passing parameters, you can now have $select->column() invoked
     * straight away (e.g. $amysql->select('*')->...)
     *
     * @param mixed $columns            (Optional)
     *
     * @see AMysql_Select::column()
     *
     * @return AMysql_Select            A new instance of this class
     **/
    public function select($columns = null)
    {
        $select = new AMysql_Select($this);
        if ($columns) {
            $select->column($columns);
        }
        return $select;
    }

    /**
     * Creates a new AMysql_Statement instance and returns it.
     *
     * @return AMysql_Statement
     **/
    public function newStatement()
    {
        return new AMysql_Statement($this);
    }

    /**
     * Performs an instant UPDATE returning the statement.
     *
     * @param string $tableName 	The table name.
     * @param array $data 		    The array of data changes. A
     *					            one-dimensional array
     * 					            with keys as column names and values
     *					            as their values.
     * @param string $where		    An SQL substring of the WHERE clause.
     * @param mixed $binds		    (Optional) The binds or a single bind for the WHERE clause.
     *
     * @return AMysql_Statement
     **/
    public function upd($tableName, array $data, $where, $binds = array())
    {
        $stmt = new AMysql_Statement($this);
        $stmt->update($tableName, $data, $where);
        $stmt->execute($binds);
        return $stmt;
    }

    /**
     * Performs an instant UPDATE returning its success.
     *
     * @param string $tableName 	The table name.
     * @param array $data 		    The array of data changes. A
     *					            one-dimensional array
     * 					            with keys as column names and values
     *					            as their values.
     * @param string $where		    An SQL substring of the WHERE clause.
     * @param mixed $binds		    (Optional) The binds or a single bind for the WHERE clause.
     *
     * @return boolean              Whether the update was successful.
     **/
    public function update($tableName, array $data, $where, $binds = array())
    {
        return $this->upd($tableName, $data, $where, $binds)->result;
    }

    /**
     * Updates multiple rows.
     * The number of total affected rows can be found in
     * $this->multipleAffectedRows.
     *
     * @param string $tableName 	The table name.
     * @param array[] $data 	    The array of data changes. An array of
     *                              an array of column-value pairs to update
     *                              for that row.
     *					            One of the column keys should be the one
     *					            with the value to search for for updating
     *					            (typically the primary key)
     *					            e.g.
     *					            [
     *					                [
     *					                    'id' => 1,
     *					                    'col1' => 'newStringValue',
     *					                    'col2' => 'newStringValue2'
     *					                ],
     *					                [
     *					                    'id' => 2,
     *					                    'col1' => 'anotherNewStringValue',
     *					                    'col2' => 'anotherNewStringValue2'
     *					                ],
     *					                ...
     *					            ]
     * @param string $column		(Options) the name of the column to search
     *                              for, and key to use among the data.
     *					            The default is 'id'.
     * @return boolean              Whether there was an equal amount of
     *                              successful updates (whether a row was
     *                              affected or not) as the size of the
     *                              inputted data array.
     **/
    public function updateMultipleByData(
        $tableName,
        array $data,
        $column = 'id'
    ) {
        $successesNeeded = count($data);
        $where = self::escapeIdentifier($column) . " = ?";
        $affectedRows = 0;
        foreach ($data as $row) {
            $by = $row[$column];
            unset($row[$column]);
            $stmt = new AMysql_Statement($this);
            $stmt->update($tableName, $row, $where)->execute(array ($by));
            $affectedRows += $stmt->affectedRows;
            if ($stmt->result) {
                $successesNeeded--;
            }
        }
        $this->multipleAffectedRows = $affectedRows;
        return 0 === $successesNeeded;
    }

    /**
     * Updates multiple rows. The values for the column to search for is the
     * key of each row.
     * The number of total affected rows can be found in
     * $this->multipleAffectedRows.
     *
     * @param string $tableName 	The table name.
     * @param array[] $data 	    The array of data changes. An array
     *                              indexed by the value of the column to
     *                              apply the update to (typically the primary)
     *                              key containing
     *                              an array of column-value pairs to update
     *                              for that row.
     *					            e.g.
     *					            [
     *					                1 => [
     *					                    'col1' => 'newStringValue',
     *					                    'col2' => 'newStringValue2'
     *					                ],
     *					                2 => [
     *					                    'id' => 2,
     *					                    'col1' => 'anotherNewStringValue',
     *					                    'col2' => 'anotherNewStringValue2'
     *					                ],
     *					                ...
     *					            ]
     * @param string $column		(Options) the name of the column and
     *					            key to search for. The default is
     *					            'id'.
     * @param boolean $updateSameColumn	(Optional) If the column being searched
     *					                for is within the a data row,
     *					                if this is false, that key should
     *					                be removed before updating the data.
     *					                This is the default.
     *
     * @return boolean
     **/
    public function updateMultipleByKey(
        $tableName,
        array $data,
        $column = 'id',
        $updateSameColumn = false
    ) {
        $successesNeeded = count($data);
        $where = $this->escapeColumn($column) . " = ?";
        $affectedRows = 0;
        foreach ($data as $by => $row) {
            if (!$updateSameColumn) {
                unset($row[$column]);
            }
            $stmt = new AMysql_Statement($this);
            $stmt->update($tableName, $row, $where)->execute(array ($by));
            $affectedRows += $stmt->affectedRows;
            if ($stmt->result) {
                $successesNeeded--;
            }
        }
        $this->multipleAffectedRows = $affectedRows;
        return 0 === $successesNeeded;
    }

    /**
     * Performs an instant INSERT, returning the statement.
     *
     * @param string $tableName 	The table name.
     * @param array $data		    A one or two-dimensional array.
     * 					            1D:
     * 					            an associative array of keys as column names and values
     * 					            as their values. This inserts one row.
     * 					            2D numeric:
     * 					            A numeric array where each value is an associative array
     * 					            with column-value pairs. Each outer, numeric value represents
     * 					            a row of data.
     * 					            2D associative:
     * 					            An associative array where the keys are the columns, the
     * 					            values are numerical arrays, where each value represents the
     * 					            value for the new row of that key.
     *
     * @return AMysql_Statement
     **/
    public function ins($tableName, array $data)
    {
        $stmt = new AMysql_Statement($this);
        $stmt->insert($tableName, $data);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Performs an instant INSERT, but tries to return the last insert
     * id straight away.
     * 
     * @param string $tableName 	The table name.
     * @param array $data		    A one or two-dimensional array.
     * 					            1D:
     * 					            an associative array of keys as column names and values
     * 					            as their values. This inserts one row.
     * 					            2D numeric:
     * 					            A numeric array where each value is an associative array
     * 					            with column-value pairs. Each outer, numeric value represents
     * 					            a row of data.
     * 					            2D associative:
     * 					            An associative array where the keys are the columns, the
     * 					            values are numerical arrays, where each value represents the
     * 					            value for the new row of that key.
     * @access public
     *
     * @return int|boolean          The mysql_insert_id(), if the query
     *                              succeeded and there exists a primary key.
     *                              Otherwise the boolean of whether the insert
     *                              was successful.
     */
    public function insert($tableName, array $data)
    {
        $stmt = $this->ins($tableName, $data);
        $success = $stmt->result;
        if ($success) {
            return $stmt->insertId ? $stmt->insertId : true;
        } else {
            return false;
        }
    }

    /**
     * Performs an instant REPLACE, returning the statement.
     *
     * @param string $tableName 	The table name.
     * @param array $data		    A one or two-dimensional array.
     * 					            1D:
     * 					            an associative array of keys as column names and values
     * 					            as their values. This inserts one row.
     * 					            2D numeric:
     * 					            A numeric array where each value is an associative array
     * 					            with column-value pairs. Each outer, numeric value represents
     * 					            a row of data.
     * 					            2D associative:
     * 					            An associative array where the keys are the columns, the
     * 					            values are numerical arrays, where each value represents the
     * 					            value for the new row of that key.
     *
     * @return AMysql_Statement
     **/
    public function rep($tableName, array $data)
    {
        $stmt = new AMysql_Statement($this);
        $stmt->replace($tableName, $data);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Performs an instant REPLACE, returning its success.
     *
     * @param string $tableName 	The table name.
     * @param array $data		    A one or two-dimensional array.
     * 					            1D:
     * 					            an associative array of keys as column 
     * 					            names and values
     * 					            as their values. This inserts one row.
     * 					            2D numeric:
     * 					            A numeric array where each value is an 
     * 					            associative array
     * 					            with column-value pairs. Each outer, 
     * 					            numeric value represents
     * 					            a row of data.
     * 					            2D associative:
     * 					            An associative array where the keys are the 
     * 					            columns, the
     * 					            values are numerical arrays, where each 
     * 					            value represents the
     * 					            value for the new row of that key.
     *
     * @return boolean              Success.
     **/
    public function replace($tableName, array $data)
    {
        $success = $this->rep($tableName, $data)->result;
        return $success;
    }

    /**
     * Performs an INSERT or an UPDATE; if the $value parameter is not falsy, 
     * an UPDATE is performed with the given column name and value, otherwise 
     * an insert. It is recommended that this is used for tables with a primary 
     * key, and use the primary key as the column to look at. Also, this would 
     * keep the return value consistent.
     * 
     * @param mixed $tableName		The table name to INSERT or UPDATE to
     * @param mixed $data		    The data to change
     * @param mixed $columnName		The column to search by. It should be
     *					            a primary key.
     * @param mixed $value		    (Optional) The value to look for in
     *					            case you want
     *					            to UPDATE. Keep this at null, 0,
     *					            or anything else falsy for INSERT.
     *
     * @return int			        If the $value is not falsy, it returns
     *					            $value after UPDATING. Otherwise the
     *					            mysql_insert_id() of the newly
     *					            INSERTED row.
     */
    public function save($tableName, $data, $columnName, $value = null)
    {
        if ($value) {
            $where = AMysql_Abstract::escapeIdentifier($columnName) . ' = ?';
            $this->update($tableName, $data, $where, array ($value));
            return $value;
        } else {
            $id = $this->insert($tableName, $data);
            return $id;
        }
    }

    /**
     * Performs an instant DELETE, returning the statement.
     *
     * @param string $tableName 	The table name.
     * @param string $where		    An SQL substring of the WHERE clause.
     * @param mixed $binds		    (Optional) The binds or a single bind for 
     *                              the WHERE clause.
     *
     * @return AMysql_Statement
     **/
    public function del($tableName, $where, $binds = array ())
    {
        $stmt = new AMysql_Statement($this);
        $stmt->delete($tableName, $where);
        $stmt->execute($binds);
        return $stmt;
    }

    /**
     * Performs an instant DELETE, returning whether it succeeded.
     *
     * @param string $tableName 	The table name.
     * @param string $where		    An SQL substring of the WHERE clause.
     * @param mixed $binds		    (Optional) The binds or a single bind for 
     *                              the WHERE clause.
     *
     * @return resource|FALSE       The mysql resource if the delete was 
     *                              successful, otherwise false.
     **/
    public function delete($tableName, $where, $binds = array ())
    {
        return $this->del($tableName, $where, $binds)->result;
    }

    /**
     * If the last mysql query was a SELECT with the SQL_CALC_FOUND_ROWS
     * options, this returns the number of found rows from that last
     * query with LIMIT and OFFSET ignored.
     * 
     * @access public
     * @return int              Number of found rows.
     */
    public function foundRows()
    {
        $stmt = new AMysql_Statement($this);
        $sql = 'SELECT FOUND_ROWS()';
        $stmt->query($sql);
        return $stmt->resultInt();
    }

    /**
     * Returns an AMysql_Expr for using in prepared statements as values.
     * See the AMysql_Expr class for details.
     * To bind a literal value without apostrophes, here is an example of
     * how you can execute a prepared statement with the help of placeholders:
     *	$amysql->prepare('SELECT ? AS time')->execute(array (
     *	    $amysql->expr('CURRENT_TIMESTAMP')
     *	))
     *
     * @see AMysql_Expr
     * @param ...$variadic      see AMysql_Expr
     *
     * @return AMysql_Expr
     **/
    public function expr(/* args */)
    {
        $args = func_get_args();
        $expr = new AMysql_Expr($this);
        call_user_func_array(array ($expr, 'set'), $args);
        return $expr;
    }

    /**
     * Escapes LIKE. The after the LIKE <string> syntax, you must place
     * an ESCAPE statement with '=' or whatever you pass here as
     * $escapeStr
     * It is not recommended to call this static method externally;
     * please use {@link AMysql_Expr} instead.
     *
     * @param string $s                 The string to LIKE escape
     * @param string $escapeChar        (Optional) The escape character
     **/
    public static function escapeLike($s, $escapeStr = '=')
    {
        return str_replace(
            array($escapeStr, '_', '%'),
            array($escapeStr.$escapeStr, $escapeStr.'_', $escapeStr.'%'),
            $s
        );
    }

    /**
     * Escapes a value. The method depends on the passed value's type, but 
     * unless the passed type is an AMysql_Expr, the safety is almost 
     * guaranteed. Do not put apostrophes around bind marks!  Those are handled 
     * by this escaping method.
     *
     * @param mixed         The value to escape
     *
     * @return string|int               The value safe to put into a query,
     *                                  including surrounding apostrophes if
     *                                  the value is string-like, not including
     *                                  apostrophes if it's an int or an
     *                                  {@link AMysql_Expr} or
     *                                  {@link AMysql_Select}
     *                                  (for selectception)
     *
     **/
    public function escape($value)
    {
        $res = $this->autoConnect();
        $isValidLink =
            $res instanceof Mysqli ||
            0 === strpos(get_resource_type($res), 'mysql link');
        if (!$isValidLink) {
            throw new RuntimeException('Resource is not a mysql resource.', 0);
        }
        $isMysqli = $this->isMysqli;
        // If it's an int, place it there literally
        if (is_int($value)) {
            return $value;
        }
        // If it's a NULL, use the literal string, NULL
        if (null === $value) {
            return 'NULL';
        }
        // Literal TRUE or FALSE in case of a boolean
        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }
        // for selectception ;)
        if ($value instanceof AMysql_Statement) {
            return $value->getSql();
        }
        // In case of an AMysql_Expr, use its __toString() methods return value.
        if ($value instanceof AMysql_Expr) {
            return $value->__toString();
        }
        // In the case of a string or anything else, let's escape it and
        // put it between apostrophes.
        return
            "'" .
                ($isMysqli ?
                    $this->link->real_escape_string($value) :
                    mysql_real_escape_string($value, $res))
                .
            "'"
        ;
    }

    /**
     * Transposes a 2 dimensional array.
     * Every inner array must contain the same keys as the other inner arrays,
     * otherwise unexpected results may occur.
     *
     * Example:
     *   $input = array (
     *       3 => array (
     *           'col1' => 'bla',
     *           'col2' => 'yo'
     *       ),
     *       9 => array (
     *           'col1' => 'ney',
     *           'col2' => 'lol'
     *       )
     *   );
     *   $output = $amysql->transpose($input);
     *
     *   $output: array (
     *       'col1' => array (
     *           3 => 'bla',
     *           9 => 'ney'
     *       ),
     *       'col2' => array (
     *           3 => 'yo',
     *           9 => 'lol'
     *       )
     *   );
     *
     * @param array $array      The 2 dimensional array to transpose
     * @return array
     */
    public static function transpose(array $array)
    {
        $ret = array ();
        if (!$array) {
            return $ret;
        }
        foreach ($array as $key1 => $arraySub) {
            if (!$ret) {
                foreach ($arraySub as $key2 => $value) {
                    $ret[$key2] = array ($key1 => $value);
                }
            } else {
                foreach ($arraySub as $key2 => $value) {
                    $ret[$key2][$key1] = $value;
                }
            }
        }
        return $ret;
    }

    /**
     * Adds a query and a profile for it to the list of queries.
     * Used by AMysql_Statement. Do not call externally!
     * 
     * @param string $query         The SQL query.
     * @param float $queryTime      The time the query took.
     * @access public
     * @return AMysql_Abstract (chainable)
     */
    public function addQuery($query, $queryTime)
    {
        $this->getProfiler()->addQuery($query, $queryTime);
        return $this;
    }

    /**
     * Pings the mysql server to see if it's still alive;
     * attempts to reconnect otherwise.
     * 
     * @param boolean $handleError      (Optional) If TRUE, on failure, this will 
     *                                  be handled as any other AMysql error. 
     *                                  Defaults to FALSE (no errors or 
     *                                  exceptions).
     * @access public
     * @return boolean                  TRUE if the connection is still there 
     *                                  or reconnection was successful.
     *                                  FALSE if reconnection wasn't successful.
     */
    public function pingReconnect()
    {
        $isMysqli = $this->isMysqli;
        if ($isMysqli) {
            $success = @mysqli_ping($this->link);
        } else {
            $success = @mysql_ping($this->link);
        }
        if (!$success) {
            try {
                $this->forceReconnect();
                if (!$this->error) {
                    $success = true;
                }
            } catch (Exception $e) {
                // do nothing
            }
        }
        return $success;
    }

    /**
     * Pings if the set amount of auto ping time has passed.
     * 
     * @access public
     * @return AMysql_Abstract (chainable)
     */
    public function autoPing()
    {
        if ($this->autoPing) {
            if (!$this->link) {
                $this->connect();
            } elseif (
                !$this->lastPingTime ||
                $this->autoPingSeconds <= (time() - $this->lastPingTime)
            ) {
                $this->pingReconnect();
                $this->lastPingTime = time();
            }
        }
        return $this;
    }

    /**
     * Auto connects and auto pings (if on), returning the mysql link.
     * 
     * @access public
     * @return Mysqli|resource
     */
    public function autoPingConnect()
    {
        $link = $this->autoConnect();
        $this->autoPing();
        return $link;
    }

    /**
     * Gets the list of SQL queries performed so far by AMysql_Statement
     * objects connected by this object.
     * 
     * @access public
     * @return array
     */
    public function getQueries()
    {
        return $this->getProfiler()->getQueries();
    }

    /**
     * Returns an arrays of profiled query data. Each value is an array that 
     * consists of:
     *  - query - The SQL query performed
     *  - time - The amount of seconds the query took (float)
     *
     * If profileQueries wss off at any query, its time value will be null.
     * 
     * @return array[]
     */
    public function getQueriesData()
    {
        return $this->getProfiler()->getQueriesData();
    }

    /**
     * Returns the profiler object. Pass it to your templates
     * to retrieve the profiler results.
     * 
     * @access public
     * @return AMysql_Profiler
     */
    public function getProfiler()
    {
        if (!$this->profiler) {
            $this->profiler = new AMysql_Profiler($this);
        }
        return $this->profiler;
    }

    /**
     * Force using a new profiler.
     * 
     * @access public
     * @return AMysql_Abstract (chainable)
     */
    public function useNewProfiler()
    {
        $this->profiler = new AMysql_Profiler($this);
        return $this;
    }

    /**
     * For internal use.
     * 
     * @param mixed $msg 
     * @param mixed $code 
     * @param mixed $query 
     * @access public
     * @throws AMysql_Exception
     * @return void
     */
    public function handleError($msg, $code, $query)
    {
        $this->error = $msg;
        $this->errno = $code;
        $ex = new AMysql_Exception($msg, $code, $query);
        if ($this->triggerErrorOnException) {
            $ex->triggerErrorOnce();
        }
        if ($this->throwExceptions) {
            throw $ex;
        }
        $ex->triggerErrorOnce();
    }

    /**
     * For internal use. 
     * 
     * @param mixed $msg 
     * @param mixed $code 
     * @param mixed $query 
     * @access public
     * @throws AMysql_Exception
     * @return void
     */
    public function handleException(AMysql_Exception $ex)
    {
        $this->error = $ex->getMessage();
        $this->errno = $ex->getCode();
        if ($this->triggerErrorOnException) {
            $ex->triggerErrorOnce();
        }
        if ($this->throwExceptions) {
            throw $ex;
        }
        $ex->triggerErrorOnce();
    }

    public function __get($name)
    {
        switch ($name) {
            case 'totalTime':
                $profiler = $this->getProfiler();
                return $profiler->totalTime;
        }
        trigger_error("Undefined property: `$name`");
    }
}
