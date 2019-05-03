<?php /* vim: set expandtab : */
/**
 * The statement class belonging to the AMysql_Abstract class, where mysql
 * queries are built and handled.
 * Most methods here are chainable, and many common AMysql_Abstract methods
 * return a new instance of this class.
 * A simple use example:
 * 
 * $amysql = new AMysql($conn);
 * try { 
 *     $stmt = $amysql->query('SELECT * FROM cms_content');
 *     while ($row = $stmt->fetchAssoc()) {
 *         ...
 *     }
 * }
 * catch (AMysql_Exception $e) {
 *     echo $e->getDetails();
 * }  
 *
 * Visit https://github.com/amcsi/amysql
 * @author      Szerémi Attila
 * @license     MIT License; http://www.opensource.org/licenses/mit-license.php
 **/
class AMysql_Statement implements IteratorAggregate, Countable
{
    public $amysql;
    public $error;
    public $errno;
    public $result;
    public $results = array ();
    public $query;
    public $affectedRows;
    public $throwExceptions;
    public $lastException = null;
    public $insertId;

    /**
     * Whether the time all the queries take should be recorded.
     *
     * @deprecated Queries are now always being profiled
     *
     * @var boolean
     */
    public $profileQueries;

    public $link;
    public $isMysqli;

    protected $_fetchMode;
    protected $_fetchModeExtraArgs = array ();

    public $beforeSql = '';
    public $prepared = '';
    public $binds = array();

    // whether this statement has been executed yet.
    protected $_executed = false;

    protected $_replacements;

    /**
     * The time in took in seconds with microseconds to perform the query.
     * It is automatically filled only when $profileQueries is set to true.
     * 
     * @var float
     */
    public $queryTime;

    const CODE_QUERY_NOT_SUCCESSFUL = 120000;

    /**
     * __construct
     * 
     * @param AMysql_Abstract $amysql
     * @access public
     */
    public function __construct(AMysql_Abstract $amysql)
    {
        $amysql->lastStatement = $this;
        $this->amysql = $amysql;
        $this->isMysqli = $amysql->isMysqli;
        $this->throwExceptions = $this->amysql->throwExceptions;
        $this->setFetchMode($amysql->getFetchMode());
    }

    /**
     * Fetches the mysql link. If it is not set, try to take it
     * from the amysql object. Of still not set, try to connect and
     * take it.
     * 
     * @access public
     * @return resource|Mysqli $link        The mysql resource or Mysqli object
     */
    public function getLink()
    {
        if (!$this->link) {
            $amysql = $this->amysql;
            $link = $amysql->link;
            if (!$link) {
                $amysql->connect();
                $link = $amysql->link;
            }
            $this->link = $link;
        }
        return $this->link;
    }

    /**
     * Checks whether mysqli is being used (as opposed to mysql)
     * 
     * @access public
     * @return boolean
     */
    public function isMysqli()
    {
        if (!isset($this->isMysqli)) {
            $link = $this->getLink();
            $this->isMysqli = $link instanceof Mysqli;
        }
        return $this->isMysqli;
    }

    /**
     * Fetches the iterator for iterating through the results of the statement
     * with the set fetch mode (default is assoc).
     * This method is automatically called due to this class being an
     * IteratorAggregate, so all you need to do is foreach your $statement
     * object.
     * 
     * @access public
     * @return AMysql_Iterator
     */
    public function getIterator()
    {
        return new AMysql_Iterator($this);
    }

    /**
     * Sets the fetch mode for fetch() and fetchAll()
     * Any extra parameters are passed on to the handler for that fetch type.
     * For example you can pass the class name and parameters for fetchObject
     * here.
     * 
     * @param mixed $fetchMode      The fetch mode. Use the AMysql_Abstract
     *                              constants.
     * @access public
     * @return AMysql_Statement (chainable)
     */
    public function setFetchMode($fetchMode/* [, extras [, extras...]]*/)
    {
        static $fetchModes = array (
            AMysql_Abstract::FETCH_ASSOC, AMysql_Abstract::FETCH_OBJECT,
            AMysql_Abstract::FETCH_ARRAY, AMysql_Abstract::FETCH_ROW
        );
        $args = func_get_args();
        $extraArgs = array_slice($args, 1);
        if (in_array($fetchMode, $fetchModes)) {
            $this->_fetchMode = $fetchMode;
            $this->_fetchModeExtraArgs = $extraArgs;
        } else {
            throw new Exception("Unknown fetch mode: `$fetchMode`");
        }
        return $this;
    }

    /**
     * Executes a prepared statement, optionally accepting binds for replacing
     * placeholders.
     * 
     * @param mixed $binds	(Optional) The binds for the placeholders. This
     *				library supports names and unnames placeholders.
     *				To use unnamed placeholders, use question marks
     *				(?) as placeholders. A bind's key should be the
     *				index of the question mark. If $binds is not
     *				an array, it is casted to one.
     *				To use named placeholders, the placeholders
     *				must start with a non-alphanumeric, non-128+
     *				character. If the key starts with an
     *				alphanumeric or 128+ character, the placeholder
     *				that is searched to be replaced will be the
     *				key prepended by a colon (:). Here are examples
     *				for what keys will replace what placeholders
     *				for the value:
     *
     *				:key: => :key:
     *				:key => :key
     *				key => :key
     *				key: => :key:
     *				!key => !key
     *				élet => :élet
     *
     *				All values are escaped and are automatically
     *				surrounded by apostrophes if needed. Do NOT
     *				add apostrophes around the string values as
     *				encapsulating for a mysql string.
     * @see AMysql_Abstract::escape()
     *
     * @return AMysql_Statement (chainable)
     */
    public function execute($binds = array ())
    {
        if (1 <= func_num_args()) {
            $this->binds = is_array($binds) ? $binds : array ($binds);
        }
        $sql = $this->getSql();
        if ($this->amysql->autoPing) {
            $this->amysql->autoPing();
        }
        try {
            $result = $this->_query($sql);
        } catch (AMysql_Exception $e) {
            /**
             * If the error is "2006 mysql server has gone away" and
             * autoReconnect is set, try to reconnect and execute the
             * query again before giving up.
             */
            if (AMysql_Exception::CODE_SERVER_GONE_AWAY == $e->getCode()) {
                $this->link = null; // clear the db link cache.

                if ($this->amysql->getAutoReconnect()) {
                    try {
                        $this->amysql->pingReconnect();
                        $result = $this->_query($sql);
                        return $this;
                    } catch (AMysql_Exception $e2) {
                        // failed to reconnect
                    }
                }
            }
            $this->handleException($e);
        }
        return $this;
    }

    /**
     * The sql string built up by the different preparing methods (prepare,
     * select, insert etc.) is returned, having the placeholders being
     * replaced by their binded values. You can debug what the final SQL
     * string would be by calling this method.
     * Call this method to see what exact SQL string was sent or is to be
     * sent to the mysql server.
     *
     * @param string $prepared	(Optional) Use this prepared string
     *					        instead of the one set.
     * @return string           The final SQL string.
     **/
    public function getSql($prepared = null)
    {
        if (!$prepared) {
            $prepared = $this->prepared;
        }
        return $this->beforeSql . $this->quoteInto($prepared, $this->binds);
    }

    /**
     * Like {@link AMysql_Statement::getSql()}, but you must give the prepared 
     * statement, the binds, and {@link AMysql_Statement::beforeSql} is ignored.
     *
     * @see AMysql_Statement::getSql()
     * 
     * @param string $prepared 
     * @param mixed $binds		$binds are automatically type casted
     *					        to an array.
     * @return string
     */
    public function quoteInto($prepared, $binds)
    {
        $sql = $prepared;
        if (!is_array($binds)) {
            $binds = is_array($binds) ? $binds : array ($binds);
        }
        if (!$binds) {
            return $sql;
        }
        if (array_key_exists(0, $binds)) {
            $parts = explode('?', $sql);
            $sql = '';
            if (count($parts) - 1 == count($binds)) {
                foreach ($binds as &$bind) {
                    $sql .= array_shift($parts);
                    $sql .= $this->amysql->escape($bind);
                };
                $sql .= array_shift($parts);
            } elseif (count($parts) - 1 < count($binds)) {
                $msg = "More binds than question marks!\n";
                $msg .= "Prepared query: `$prepared`\n";
                $msg .= sprintf("Binds: %s\n", print_r($binds, true));
                throw new RuntimeException($msg);
            } else {
                $msg = "Fewer binds than question marks!\n";
                $msg .= "Prepared query: `$prepared`\n";
                $msg .= sprintf("Binds: %s\n", print_r($binds, true));
                throw new RuntimeException($msg);
            }
        } else {
            $keysQuoted = array ();
            $replacements = array ();
            foreach ($binds as $key => &$bind) {
                if (127 < ord($key[0]) || preg_match('/^\w$/', $key[0])) {
                    $key = ':' . $key;
                }
                $keyQuoted = preg_quote($key, '/');
                $keysQuoted[] = $keyQuoted;
                $replacements[$key] = $this->amysql->escape($bind);
            }
            $keysOr = join('|', $keysQuoted);
            # Anything that is one of the keys followed by a non-word ascii
            # character. This prevents :someKeyWithLongerName from being
            # treated as :someKey, if both those keys actually existed.
            $pattern =
                "/($keysOr)(?![\w\x80-\xff])/m";
            $this->_replacements = $replacements;

            $sql = preg_replace_callback(
                $pattern,
                array($this, '_replaceCallback'),
                $sql
            );
        }
        return $sql;
    }

    /**
     * Replaced a named placeholder with its replacement escaped via
     * {@link AMysql_Abstract::escape()} 
     * 
     * @param array $match
     * @access protected
     * @return string           The replacement
     */
    protected function _replaceCallback($match)
    {
        $key = $match[0];
        $replacement = array_key_exists($key, $this->_replacements) ?
            $this->_replacements[$key] :
            $key;
        return $replacement;
    }

    /**
     * Prepares an SQL string for binding and execution. Use of this
     * method is not recommended externally. Use the AMysql class's
     * prepare method instead which returns a new AMysql_Statement instance.
     * 
     * @param string $sql           The SQL string to prepare.
     * @return AMysql_Statement (chainable)
     */
    public function prepare($sql)
    {
        $this->beforeSql = '';
        $this->prepared = $sql;
        $this->binds = array();
        return $this;
    }

    /**
     * Prepares a statement and executes it with the given binds.
     *
     * @see AMysql_Statement::prepare()
     * @see AMysql_Statement::execute()
     * 
     * @param string $sql       The SQL string to prepare.
     * @param array $binds      @see $this->execute()
     * @access public
     * @return AMysql_Statement (chainable)
     */
    public function query($sql, $binds = array ())
    {
        $this->prepare($sql);
        $result = $this->execute($binds);
        return $this;
    }

    /**
     * Performs the actual query on the database with the given SQL string,
     * making no further modifications on it. 
     * 
     * @param string $sql       The SQL string.
     * @access protected
     * @throws AMysql_Exception on error
     * @return AMysql_Statement (chainable)
     */
    protected function _query($sql)
    {
        $link = $this->getLink();
        $isMysqli = $this->isMysqli();
        $this->query = $sql;
        $success = false;
        $stmt = null;
        try {
            set_error_handler(array($this, 'errorHandlerCallback'), E_WARNING);
            if ($this->_executed) {
                throw new LogicException(
                    "This statement has already been executed.\nQuery: $sql"
                );
            }
            if ($isMysqli) {
                $startTime = microtime(true);

                $stmt = $link->prepare($sql);
                if ($stmt) {
                    $success = $stmt->execute();
                }

                $duration = microtime(true) - $startTime;
                $this->queryTime = $duration;
            } else {
                $startTime = microtime(true);

                $result = mysql_query($sql, $link);

                $duration = microtime(true) - $startTime;
                $this->queryTime = $duration;
            }
            if ($isMysqli) {
                $result = $stmt ? $stmt->get_result() : false;
                if (!$result && $success) {
                    /**
                     * In mysqli, result_metadata will return a falsy value
                     * even for successful SELECT queries, so for compatibility
                     * let's set the result to true if it isn't an object
                     * (is false), but the query was successful.
                     */
                    $result = true;
                }
            }
            $this->amysql->addQuery($sql, $this->queryTime);
            if (false !== $result) {
                if ($isMysqli) {
                    $this->affectedRows = $stmt->affected_rows;
                    $this->insertId = $stmt->insert_id;
                } else {
                    $this->affectedRows = mysql_affected_rows($link);
                    $this->insertId = mysql_insert_id($link);
                }
                $this->result = $result;
                $this->results[] = $result;
                $this->amysql->affectedRows = $this->affectedRows;
                $this->amysql->insertId = $this->insertId;
                $this->_executed = true;
            } else {
                throw new RuntimeException(
                    "Query was not successful.",
                    self::CODE_QUERY_NOT_SUCCESSFUL
                );
            }
            restore_error_handler();
        } catch (Exception $e) {
            restore_error_handler();
            if ($e instanceof ErrorException) {
                $this->reportErrorException($e);
            } elseif ($e instanceof RuntimeException) {
                if (self::CODE_QUERY_NOT_SUCCESSFUL == $e->getCode()) {
                    // continue on reporting the error
                } else {
                    // unexpected RuntimeException should be thrown.
                    throw $e;
                }
            } else {
                // throw the unexpected exception
                throw $e;
            }
            $error = $isMysqli ? $link->error : mysql_error($link);
            $errno = $isMysqli ? $link->errno : mysql_errno($link);
            throw new AMysql_Exception($error, $errno, $this->query, $e);
        }
        return $this;
    }

    /**
     * Frees the mysql result resource.
     *
     * @return AMysql_Statement (chainable)
     **/
    public function freeResults()
    {
        foreach ($this->results as $result) {
            if (is_resource($result)) {
                $this->isMysqli() ? $result->free() : mysql_free_result($result);
            }
        }
        return $this;
    }

    /**
     * Returns all the results with each row in the format of that specified
     * by the fetch mode.
     * 
     * @see AMysql_Statement::setFetchMode()
     *
     * @return array
     **/
    public function fetchAll()
    {
        $result = $this->result;
        $ret = array ();
        if (AMysql_Abstract::FETCH_ASSOC == $this->_fetchMode) {
            $methodName = 'fetchAssoc';
        } elseif (AMysql_Abstract::FETCH_OBJECT == $this->_fetchMode) {
            $methodName = 'fetchObject';
        } elseif (AMysql_Abstract::FETCH_ARRAY == $this->_fetchMode) {
            $methodName = 'fetchArray';
        } elseif (AMysql_Abstract::FETCH_ROW == $this->_fetchMode) {
            $methodName = 'fetchRow';
        } else {
            throw new Exception("Unknown fetch mode: `$this->_fetchMode`");
        }
        $ret = array();
        $numRows = $this->numRows();
        if (0 === $numRows) {
            return array ();
        } elseif (false === $numRows) {
            return false;
        }
        $extraArgs = $this->_fetchModeExtraArgs;
        $method = array ($this, $methodName);
        $result instanceof Mysqli_Result ?
            $result->data_seek(0) :
            mysql_data_seek($result, 0);
        while (
            ($row = call_user_func_array($method, $extraArgs)) && isset($row)
        ) {
            $ret[] = $row;
        }
        return $ret;
    }

    /**
     * Returns one row in the format specified by the fetch mode.
     *
     * @see AMysql_Statement::setFetchMode()
     * 
     * @return mixed            Usually array. Can also be FALSE if there are
     *                          no more rows. If AMysql_Abstract::FETCH_OBJECT
     *                          is the fetch mode, then an object would be
     *                          returned.
     */
    public function fetch()
    {
        if ('assoc' == $this->_fetchMode) {
            return $this->fetchAssoc();
        } elseif ('object' == $this->_fetchMode) {
            $extraArgs = $this->_fetchModeExtraArgs;
            $method = array ($this, 'fetchObject');
            return call_user_func_array($method, $extraArgs);
        } elseif ('row' == $this->_fetchMode) {
            return $this->fetchRow();
        } elseif (AMysql_Abstract::FETCH_ARRAY == $this->_fetchMode) {
            return $this->fetchArray();
        } else {
            throw new RuntimeException("Unknown fetch mode: `$this->_fetchMode`");
        }
    }

    /**
     * Fetches one row with column names as the keys.
     * 
     * @return array|FALSE
     */
    public function fetchAssoc()
    {
        $result = $this->result;
        return $this->isMysqli() ? $result->fetch_assoc() : mysql_fetch_assoc($result);
    }

    /**
     * Fetches all rows and returns them as an array of associative arrays. The
     * outer array is numerically indexed by default, but can be indexed by
     * a field value.
     * 
     * @param integer|string|boolean $keyColumn	(Optional) If a string, the cell
     *					of the given field will be the key for
     *					its row, so the result will not be an array
     *					numerically indexed from 0 in order. This
     *					value can also be an integer, specifying
     *					the index of the field with the key.
     * @access public
     * @return <Associative result array>[]
     */
    public function fetchAllAssoc($keyColumn = false)
    {
        $result = $this->result;
        $ret = array();
        $numRows = $this->numRows();
        if (0 === $numRows) {
            return array ();
        } elseif (false === $numRows) {
            return false;
        }
        $result instanceof Mysqli_Result ? $result->data_seek(0) : mysql_data_seek($result, 0);
        $keyColumnGiven = is_string($keyColumn) || is_int($keyColumn);
        if (!$keyColumnGiven) {
            while (false !== ($row = $this->fetchAssoc()) && isset($row)) {
                $ret[] = $row;
            }
        } else {
            $row = $this->fetchAssoc();
            /**
             * Since we are using associative keys here, if we gave the key as an
             * int, we have to find out the associative version of the key.
             **/
            if (is_int($keyColumn)) {
                $cnt = count($keyColumn);
                reset($row);
                for ($i = 0; $i < $cnt; $i++) {
                    next($row);
                }
                $keyColumn = key($row);
                reset($row);
            }
            $ret[$row[$keyColumn]] = $row;
            while ($row = $this->fetchAssoc()) {
                $ret[$row[$keyColumn]] = $row;
            }
        }
        return $ret;
    }

    /**
     * Fetches the next row with column names as numeric indices.
     * Returns FALSE if there are no more rows.
     * 
     * @return array|FALSE
     */
    public function fetchRow()
    {
        $result = $this->result;
        return $this->isMysqli() ?
            $result->fetch_row() :
            mysql_fetch_row($result);
    }

    /**
     * Alias of {@link AMysql_Statement::fetchRow()}
     *
     * @see AMysql_Statement::fetchRow()
     * 
     * @return array|FALSE
     */
    public function fetchNum()
    {
        return $this->fetchRow();
    }

    /**
     * Fetches the next row with column names and their indexes as keys.
     * 
     * @return array|FALSE
     */
    public function fetchArray()
    {
        $result = $this->result;
        $isMysqli = $this->isMysqli();
        return $isMysqli ?
            $result->fetch_array(MYSQLI_BOTH) :
            mysql_fetch_array($result, MYSQL_BOTH);
    }

    /**
     * Returns the result of the given row and field. A warning is issued
     * if the result on the given row and column does not exist.
     * 
     * @param int $row		        (Optional) The row number.
     * @param int|string $field	    (Optional) The field number or name.
     * @return string|int|FALSE
     */
    public function result($row = 0, $field = 0)
    {
        $result = $this->result;
        if ($this->isMysqli()) {
            if ($result->num_rows <= $row) {
                // mysql_result compatibility, sort of...
                trigger_error("Unable to jump to row $row", E_WARNING);
                return false;
            }
            /**
             * @todo optimize
             **/
            $result->data_seek($row);
            $array = $result->fetch_array(MYSQLI_BOTH);
            if (!array_key_exists($field, $array)) {
                // mysql_result compatibility, sort of...
                trigger_error("Unable to access field `$field` of row $row", E_WARNING);
                return false;
            }
            $ret = $array[$field];
            $result->data_seek(0);
            return $ret;
        }
        return mysql_result($result, $row, $field);
    }

    /**
     * Returns the result of the given row and field, or the given value
     * if the row doesn't exist
     * 
     * 
     * @param mixed $default	The value to return if the field is not found.
     * @param int $row		    (Optional) The row number.
     * @param int $field	    (Optional) The field.
     * @return mixed
     */
    public function resultDefault($default, $row = 0, $field = 0)
    {
        $result = $this->result;
        return $row < $this->numRows() ? $this->result($row, $field) :
            $default;
    }

    /**
     * Returns the result of the given row and field, or null if the
     * row doesn't exist
     * 
     * 
     * @param int $row		(Optional) The row number.
     * @param int $field	(Optional) The field.
     * @return mixed
     */

    public function resultNull($row = 0, $field = 0)
    {
        return $this->resultDefault(null, $row, $field);
    }

    /**
     * Returns the result of the given row and field as an integer.
     * 0, if that result doesn't exist.
     * 
     * @param int $row		(Optional) The row number.
     * @param int $field	(Optional) The field.
     * @return int
     */
    public function resultInt($row = 0, $field = 0)
    {
        return (int) $this->resultNull($row, $field);
    }

    /**
     * Returns an array of scalar values, where the keys are the values
     * of the key column specified, and the values are the values of the
     * value column specified.
     * 
     * @param mixed $keyColumn	    (Optional) column number or string for
     *				                the keys.
     *				                Default: 0.
     * @param mixed $valueColumn    (Optional) column number or string for
     *				                the values.
     *				                Default: 1.
     * @access public
     * @return array
     */
    public function fetchPairs($keyColumn = 0, $valueColumn = 1)
    {
        $ret = array ();
        while ($row = $this->fetchArray()) {
            $key = $row[$keyColumn];
            $ret[$key] = $row[$valueColumn];
        }
        return $ret;
    }

    /**
     * Alias of {@link AMysql_Statement::fetchPairs()}
     * 
     */
    public function pairUp($keyColumn = 0, $valueColumn = 1)
    {
        return $this->fetchPairs($keyColumn, $valueColumn);
    }

    /**
     * Returns all values of a specified column as an array.
     * 
     * @param mixed $column	    (Optional) column number or string for
     *				            the values.
     *				            Default: 0.
     * @access public
     * @return array
     */
    public function fetchAllColumn($column = 0)
    {
        $ret = array ();
        $numRows = $this->numRows();
        if (!$numRows) {
            return $ret;
        }
        $result = $this->result;
        $this->isMysqli() ? $result->data_seek(0) : mysql_data_seek($result, 0);
        while ($row = $this->fetchArray()) {
            $ret[] = $row[$column];
        }
        return $ret;
    }

    /**
     * Fetches all rows and returns them as an array of columns containing an 
     * array of values.  Works simalarly to fetchAllAssoc(), but with the 
     * resulting array transposed.
     *
     *  e.g.
     *  [
     *      'id' => ['1', '2'],
     *      'val' => ['val1', 'val2']
     *  ]
     *
     * @param string|int $keyColumn         When building an array of arrays
     *                                      (list of values for that column)
     *                                      if this value is given, the indexes
     *                                      of the inner array will be equal to
     *                                      the value of the column in the row
     *                                      equivalent of data. Typically you
     *                                      want to choose the primary key.
     *                                      e.g. if $keyColumn is 'id', the
     *                                      example changes to:
     *                                      [
     *                                          'id' => ['1' => '1', '2' => '2'],
     *                                          'val' => [
     *                                              '1' => 'val1',
     *                                              '2' => 'val2'
     *                                          ]
     *                                      ]
     *
     * @access public
     * @return array
     */
    public function fetchAllColumns($keyColumn = false)
    {
        $ret = array ();
        $numRows = $this->numRows();
        $keyColumnGiven = is_string($keyColumn) || is_int($keyColumn);
        if (!$numRows) {
            // ok
        } elseif (!$keyColumnGiven) {
            /**
             * If $keyColumn isn't given i.e. the resulting array indexes
             * don't matter, let's build the returning array here to
             * dodge unnecessary overhead.
             **/
            $result = $this->result;
            $result instanceof Mysqli_Result ?
                $result->data_seek(0) :
                mysql_data_seek($result, 0);
            $firstRow = $this->fetchAssoc();
            foreach ($firstRow as $colName => $val) {
                $ret[$colName] = array ($val);
            }
            while ($row = $this->fetchAssoc()) {
                foreach ($row as $colName => $val) {
                    $ret[$colName][] = $val;
                }
            }
            return $ret;
        } else {
            /**
             * Otherwise if $keyColumn is given, we have no other choice but to use
             * $this->fetchAllAssoc($keyColumn) and transpose it.
             **/
            $ret = AMysql_Abstract::transpose(
                $this->fetchAllAssoc($keyColumn)
            );
        }
        return $ret;
    }

    /**
     * Fetches the next row as an object.
     *
     * @param string $className         (Optional) The class to use. Default is stdClass.
     * @param array $params             (Optional) The params to pass to the object.
     * 
     * @return object
     */
    public function fetchObject(
        $className = 'stdClass',
        array $params = array()
    ) {
        $result = $this->result;
        $isMysqli = $this->isMysqli();
        if ($params) {
            return $isMysqli ?
                $result->fetch_object($className, $params) :
                mysql_fetch_object($result, $className, $params)
                ;
        } else {
            return $isMysqli ?
                $result->fetch_object($className) :
                mysql_fetch_object($result, $className)
                ;
        }
    }

    /**
     * Returns the number of affected rows.
     * 
     * @return int
     */
    public function affectedRows()
    {
        return $this->affectedRows;
    }

    /**
     * Returns the number of rows selected, or FALSE on failure.
     * 
     * @access public
     * @return int|FALSE
     */
    public function numRows()
    {
        if ($this->isMysqli()) {
            return $this->result instanceof Mysqli_Result ? $this->result->num_rows : false;
        }
        return mysql_num_rows($this->result);
    }

    /**
     * Throws an AMysqlException for the last mysql error.
     * For internal use (if even used).
     * 
     * @throws AMysql_Exception
     */
    public function throwException()
    {
        throw new AMysql_Exception($this->error, $this->errno, $this->query);
    }

    /**
     * @deprecated
     */
    public function escapeIdentifierSimple($columnName)
    {
        return AMysql_Abstract::escapeIdentifierSimple($columnName);
    }

    /**
     * Escapes an identifier and puts it between identifier quotes.
     *
     * @param string $identifier    The identifier
     * @param string $qc            The quote character. Default: `
     *
     * @return string               The escaped identifier.
     **/
    public function escapeIdentifier($columnName, $as = null)
    {
        return $this->amysql->escapeIdentifier($columnName, $as);
    }

    /**
     * Appends a string to the prepared string.
     *
     * @param string $sql           The string to append.
     * @return AMysql_Statement (chainable)
     **/
    public function appendPrepare($sql)
    {
        $this->prepared .= $sql;
        return $this;
    }

    /**
     * Binds a value to the sql string.
     *
     * @param mixed $key	    If an integer, then the given index
     *				            question mark will be replaced.
     *				            If a string, then then, if it starts
     *				            with an alphanumberic or 128+ ascii
     *				            character, then a colon plus the string
     *				            given will be replaced, otherwise the
     *				            given string literally will be replaced.
     *				            Example: if the string is
     *				            foo
     *				            then :foo will be replaced.
     *				            if the string is
     *				            !foo
     *				            then !foo will be replaced
     *				            if the string is
     *				            :foo:
     *				            then :foo: will be replaced.
     *				            Note: don't worry about keys that have a
     *				            common beginning. If foo and fool are set,
     *				            :fool will not be replaced with the value
     *				            given for foo.
     *
     * @param mixed $val	    Bind this value for replacing the mark
     *				            defined by $key. The value is escaped
     *				            depeding on its type, apostrophes included,
     *				            so do not add apostrophes in your
     *				            prepared sqls.
     *
     * @return AMysql_Statement (chainable)
     **/
    public function bindValue($key, $val)
    {
        if (is_numeric($key) && $this->amysql->pdoIndexedBinding) {
            $key--;
        }
        $this->binds[$key] = $val;
        return $this;
    }

    /**
     * The same as $this->bindValue(), except that $val is binded by
     * reference, meaning its value is extracted on execute.
     *
     * @see AMysql_Statement::bindValue()
     * @param mixed $key	    @see AMysql_Statement::bindValue()
     * @param mixed &$val	    Like $val in
     *                          {@link AMysql_Statement::bindValue()}, but by
     *                          reference.
     *
     * @return AMysql_Statement (chainable)
     */
    public function bindParam($key, &$val)
    {
        if (is_numeric($key) && $this->amysql->pdoIndexedBinding) {
            $key--;
        }
        $this->binds[$key] =& $val;
        return $this;
    }

    /**
     * Sets the binds binding question marks or named binds to values.
     * 
     * @param array $binds      The binds.
     * @access public
     * @return AMysql_Statement (chainable)
     */
    public function setBinds(array $binds)
    {
        $this->binds = $binds;
        return $this;
    }

    /**
     * Merges an array of binds with the ones already set.
     * Only use for named parameters!
     * 
     * @param array $binds      The binds.
     * @access public
     * @return AMysql_Statement (chainable)
     */
    public function addBinds(array $binds)
    {
        $this->binds = array_merge($this->binds, $binds);
        return $this;
    }

    /**
     * Builds a columns list with values as a string. Can be an
     * array of column-value pairs (2D for one row), can be an
     * array of array of key-value pairs (3D for multiple rows),
     * or can be an array with column names as the keys, each value
     * being an array of values (3D for multiple rows).
     *
     * e.g. (`col1`, `col2`) VALUES ('col1val1', 'col1val2'),
     *  ('col2val1', 'col2val2')
     *
     * @param array $data @see $this->insertReplace()
     * @return string
     */
    public function buildColumnsValues(array $data)
    {
        $i = 0;
        if (empty($data[0])) {
            // keys are column names
            foreach ($data as $columnName => $values) {
                $cols[] = $this->amysql->escapeColumn($columnName);
                if (!is_array($values)) {
                    // single piece of data here.
                    $values = array($values);
                }
                foreach ($values as $key => $value) {
                    if (!isset($vals[$key])) {
                        $vals[$key] = array ();
                    }
                    $vals[$key][] = $this->amysql->escape($value);
                }
            }
        } else {
            // keys are indexes

            // the column names should be found in the first index's keys
            $akeys = array_keys($data[0]);
            $cols = array ();
            foreach ($akeys as $col) {
                $cols[] = $this->amysql->escapeColumn($col);
            }

            foreach ($data as $row) {
                $vals[$i] = array ();
                $row2 = array();
                foreach ($akeys as $key) {
                    $row2[$key] = null;
                }
                foreach ($row as $columnName => $value) {
                    $row2[$columnName] = $this->amysql->escape($value);

                }
                $vals[$i] = $row2;
                $i++;
            }
        }
        $columnsString = join(', ', $cols);
        $rowValueStrings = array();
        foreach ($vals as $rowValues) {
            $rowValueStrings[] = join(', ', $rowValues);
        }
        $valuesString = join('), (', $rowValueStrings);
        $columnsValuesString = "($columnsString) VALUES ($valuesString)";
        return $columnsValuesString;
    }

    /**
     * Puts together a string that is to be placed after a SET statement.
     * i.e. column1 = 'value', int_col = 3
     *
     * @param array $data Keys are column names, values are the values unescaped
     * @return string
     */
    public function buildSet(array $data)
    {
        $sets = array ();
        foreach ($data as $columnName => $value) {
            $columnName = $this->amysql->escapeColumn($columnName);
            $sets[] = "$columnName = " . $this->amysql->escape($value);
        }
        $setsString = join(', ', $sets);
        return $setsString;
    }

    /**
     * Prepares a mysql UPDATE unexecuted. By execution, have the placeholders
     * of the WHERE statement binded.
     * It is rather recommended to use AMysql_Abstract::update() instead, which
     * lets you also bind the values in one call and it returns the success
     * of the query.
     *
     * @param string $tableName 	The table name.
     * @param array $data 		    The array of data changes. A
     *					            one-dimensional array
     * 					            with keys as column names and values
     *					            as their values.
     * @param string $where		    An SQL substring of the WHERE clause.
     *
     * @return AMysql_Statement (chainable)
     **/
    public function update($tableName, array $data, $where)
    {
        if (!$data) {
            return false;
        }
        $setsString = $this->buildSet($data);

        /**
         * This must be solved by beforeSql, otherwise bind substrings
         * could cause problems within the SET string.
         **/
        $tableSafe = $this->amysql->escapeTable($tableName);
        $beforeSql = "UPDATE $tableSafe SET $setsString WHERE ";
        $this->prepare($where);
        $this->beforeSql = $beforeSql;

        return $this;
    }

    /**
     * Prepares a mysql INSERT or REPLACE unexecuted. After this, you should just
     * call $this->execute().
     * It is rather recommended to use AMysql_Abstract::insert() instead, which
     * returns the last inserted id already.
     *
     * @param string $type          "$type INTO..." (INSERT, INSERT IGNORE, REPLACE) etc.
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
     * @return AMysql_Statement (chainable)
     **/
    public function insertReplace($type, $tableName, array $data)
    {
        $cols = array ();
        $vals = array();
        if (!$data) {
            return false;
        }
        $tableSafe = $this->amysql->escapeTable($tableName);
        $columnsValues = $this->buildColumnsValues($data);
        $sql = "$type INTO $tableSafe $columnsValues";
        $this->prepare($sql);
        return $this;
    }

    /**
     * Performs an INSERT.
     *
     * @see $this->insertReplace
     * 
     * @param string $tableName 
     * @param array $data 
     * @access public
     * @return AMysql_Statement (chainable)
     */
    public function insert($tableName, array $data)
    {
        return $this->insertReplace('INSERT', $tableName, $data);
    }

    /**
     * Performs a REPLACE.
     *
     * @see $this->insertReplace
     * 
     * @param string $tableName 
     * @param array $data 
     * @access public
     * @return AMysql_Statement (chainable)
     */
    public function replace($tableName, array $data)
    {
        return $this->insertReplace('REPLACE', $tableName, $data);
    }

    /**
     * Prepares a mysql DELETE unexecuted. By execution, have the placeholders
     * of the WHERE statement binded.
     * It is rather recommended to use AMysql_Abstract::delete() instead, which
     * lets you also bind the values in one call and it returns the success
     * of the query.
     *
     * @param string $tableName 	The table name.
     * @param string $where		    An SQL substring of the WHERE clause.
     *
     * @see AMysql_Abstract::delete()
     *
     * @return AMysql_Statement (chainable)
     **/
    public function delete($tableName, $where)
    {
        $tableSafe = $this->amysql->escapeTable($tableName);
        $sql = "DELETE FROM $tableSafe";
        if ($where) {
            $sql .= ' WHERE ' . $where;
        }
        $this->prepare($sql);
        return $this;
    }

    /**
     * Returns the last insert id
     *
     * @return integer|FALSE
     **/
    public function insertId()
    {
        return $this->insertId;
    }

    /**
     * Free the results.
     **/
    public function __destruct()
    {
        $this->freeResults();
    }

    public function __set($name, $value)
    {
        switch($name) {
            case 'fetchMode':
                $this->setFetchMode($value);
                break;
            default:
                throw new OutOfBoundsException(
                    "Invalid member: `$name` (target value was `$value`)"
                );
        }
    }

    /**
     * Returns the number of rows in the result. 
     * 
     * @throws LogicException   If the query was not a read query.
     * @access public
     * @return int
     */
    public function count()
    {
        if (!is_resource($this->result) && !is_object($this->result)) {
            $msg = "No SELECT result. ".
                "Last query: " . $this->query;
            throw new LogicException($msg);
        }
        $count = $this->numRows();
        return $count;
    }

    /**
     * Handle an ErrorException thrown with the help of
     * $this->errorHandlerCallback. For now, just suppress the
     * error.
     * For internal use.
     * 
     * @param ErrorException $ex 
     * @access public
     * @return void
     */
    public function reportErrorException(ErrorException $ex)
    {
    }

    /**
     * Changes warnings into exceptions. 
     * For internal use.
     * Mainly for handling warnings generated by mysql functions/methods.
     * 
     * @param int $errno 
     * @param string $errstr 
     * @param string $errfile 
     * @param int $errline 
     * @param array $errcontext 
     * @access public
     * @throws ErrorException
     * @return void
     */
    public function errorHandlerCallback(
        $errno,
        $errstr,
        $errfile = null,
        $errline = null,
        $errcontext = null
    ) {
        throw new ErrorException($errstr, $errno, 1, $errfile, $errline);
    }

    /**
     * handleError 
     * 
     * @param string $msg 
     * @param int $code 
     * @param string $query 
     * @access protected
     * @throws AMysql_Exception
     * @return void
     */
    protected function handleError($msg, $code, $query)
    {
        $this->error = $msg;
        $this->errno = $code;
        return $this->amysql->handleError($msg, $code, $query);
    }

    /**
     * handleException
     * 
     * @param AMysql_Exception 
     * @access protected
     * @throws AMysql_Exception
     * @return void
     */
    protected function handleException(AMysql_Exception $ex)
    {
        return $this->amysql->handleException($ex);
    }
}
