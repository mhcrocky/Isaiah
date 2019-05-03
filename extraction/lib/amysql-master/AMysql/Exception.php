<?php /* vim: set expandtab : */
/**
 * MySQL exception class
 *
 * A list of error codes can be found below. Anything in ($#), where # is a number which
 * represents the index the substring is captured in in the array $this->getParams()
 * returns.
 *
 *  CODE_DUPLICATE_ENTRY (1062):
 *      Duplicate entry '($0)' for key '($1)'
 *  CODE_PARENT_FOREIGN_KEY_CONSTRAINT_FAILS (1451):
 *      Cannot delete or update a parent row: a foreign key constraint fails ($0)
 *  CODE_CHILD_FOREIGN_KEY_CONSTRAINT_FAILS (1452):
 *      Cannot add or update a child row: a foreign key constraint fails (%0)
 *  CODE_SERVER_GONE_AWAY (2006):
 *      Mysql server has gone away
 *
 * List of official MySQL error codes can be found on these links:
 * @link http://dev.mysql.com/doc/refman/5.7/en/error-messages-server.html
 * @link http://dev.mysql.com/doc/refman/5.7/en/error-messages-client.html
 *
 * Visit https://github.com/amcsi/amysql
 * @author      Szerémi Attila 
 * @license     MIT License; http://www.opensource.org/licenses/mit-license.php
 **/ 
class AMysql_Exception extends RuntimeException
{

    /**
     * @var string The query string mysql had a problem with (if there is one).
     **/         
    public $query;
    protected $errorTriggered = 0;
    protected $params;

    const CODE_DUPLICATE_ENTRY = 1062; 
    const CODE_PARENT_FOREIGN_KEY_CONSTRAINT_FAILS = 1451;
    const CODE_CHILD_FOREIGN_KEY_CONSTRAINT_FAILS = 1452;
    const CODE_SERVER_GONE_AWAY = 2006;

    /**
     * __construct 
     * 
     * @param string $msg                   The mysql error message or custom
     *                                      message
     * @param int $errno                    The mysql error code or custom
     *                                      error code
     * @param string $query                 The mysql query string used or a
     *                                      message hinting the action taken
     * @param Exception $previous           The previous exception
     * @access public
     */
    public function __construct($msg, $errno, $query, $previous = null)
    {
        $this->query = $query;
        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
            parent::__construct($msg, $errno, $previous);
        } else {
            parent::__construct($msg, $errno);
        }
    }

    public function getDetails()
    {
        return $this->__toString();
    }

    public function getLogMessage()
    {
        return $this->__toString();
    }

    /**
     * Parses the mysql error message for variables based on the error code.
     * Only a subset of error codes/messages are supported.
     * It is recommended that you always check the exception's error code before
     * calling this method.
     *
     * For requests for including support for specific error codes/messages
     * in the codebase, please visit @link https://github.com/amcsi/amysql/issues
     * 
     * @param int $index            (Optional) Returns the $index element of the returning
     *                              array instead
     * @access public
     * @return array|string         An array of the parsed variables.
     *                              Empty array if the error code's message doesn't have
     *                              variables, or if the error code/message is not supported.
     *                              If $index was set, a string will be returned if found,
     *                              otherwise NULL is returned and an E_NOTICE is raised.
     */
    public function getParams($index = null)
    {
        if (!isset($this->params)) {
            switch ($this->getCode()) {
                case self::CODE_DUPLICATE_ENTRY:
                    $pattern = "@Duplicate entry '(.*)' for key '(.*)'@";
                    $message = $this->getMessage();
                    preg_match($pattern, $message, $params);
                    array_shift($params);
                    break;
                case self::CODE_PARENT_FOREIGN_KEY_CONSTRAINT_FAILS:
                    /**
                     * @todo Grab more specific parameters here.
                     * Would anyone help me find a guaranteed-to-work regex solution?
                     * I couldn't find any reliable documentation on the exact format
                     * of this among the MySQL docs.
                     **/
                    $pattern = "@Cannot delete or update a parent row: a foreign key constraint fails \((.*)\)$@";
                    $message = $this->getMessage();
                    preg_match($pattern, $message, $params);
                    array_shift($params);
                    break;
                case self::CODE_CHILD_FOREIGN_KEY_CONSTRAINT_FAILS:
                    $pattern = "@Cannot add or update a child row: a foreign key constraint fails \((.*)\)$@";
                    /*
                    $pattern = '@Cannot add or update a child row: ' .
                        'a foreign key constraint fails \((.*), CONSTRAINT ' .
                        '[`"]?(.*?)[`"]? FOREIGN KEY \([`"](.*)[`"]\) REFERENCES '
                        .'[`"]?(.*?)[`"]? \([`"]?(.*?)[`"]?\)(?: (.*))?\)$@';
                     */
                    $message = $this->getMessage();
                    preg_match($pattern, $message, $params);
                    array_shift($params);
                    break;
                default:
                    $params = array();
                    break;
            }
            $this->params = $params;
        }
        $ret = $this->params;
        if (isset($index)) {
            $ret = $ret[$index];
        }
        return $ret;
    }

    /**
     * Performs a trigger_error on this exception.
     * Does nothing if this method has already been called on this object.
     * 
     * @access public
     * @return void
     */
    public function triggerErrorOnce()
    {
        if (!$this->errorTriggered) {
            trigger_error($this, E_USER_WARNING);
            $this->errorTriggered++;
        }
    }

    /**
     * The details of the exception described in a string.
     *
     * @todo Include the previous exception here.
     * 
     * @access public
     * @return void
     */
    public function __toString()
    {
        return "AMysqlException: `$this->message`\n" .
            "Error code `$this->code` in $this->file:$this->line\n" .
            "Query: $this->query\n";
    }
}
?>
