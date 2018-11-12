<?php
/**
 * This class is so that you could access the query profile data within
 * your view containing the information on the latest queries without
 * having to pass the entire AMysql object onto the view.
 *
 * The problem with simply calling $amysql->getQueriesData() is that you
 * may be using a framework that doesn't allow you to set a hook for after
 * resolving the controllers, but before rendering a view. With this class,
 * you can just fetch it by $amysql->getProfiler(), assign it to view, and
 * access the latest profiler data with $profiler['queriesData'] and
 * $profiler['totalTime'].
 *
 * Enable the profiler by setting the AMysql object's profileQueries
 * proterty to true.
 * 
 * Visit https://github.com/amcsi/amysql
 * @author      Szerémi Attila
 * @license     MIT License; http://www.opensource.org/licenses/mit-license.php
 */
class AMysql_Profiler implements ArrayAccess
{
    protected $amysql;

    /**
     * The total time all the queries have taken so far.
     *
     * @var float
     * @access protected
     */
    protected $totalTime = 0.0;
    protected $queries = array();
    protected $queriesData = array();
    private $defaultEncoding = 'utf-8';

    public function __construct(AMysql_Abstract $amysql)
    {
        $this->amysql = $amysql;
    }

    /**
     * @deprecated Profiling is always enabled and cannot be turned off.
     * 
     * @param mixed $enabled 
     * @access public
     * @return $this
     */
    public function setEnabled($enabled)
    {
        return $this;
    }

    /**
     * Gets the profile data as an HTML table with by a template shipped with
     * this library.
     * Can also be called with the help of ArrayAccess via $this['asHtml']
     * 
     * @access public
     * @return string
     */
    public function getAsHtml()
    {
        $encoding = $this->defaultEncoding;

        $tplBaseDir = dirname(__FILE__) . '/../tpl';
        $filename = "$tplBaseDir/profileTemplate.php";
        $profiler = $this;
        ob_start();
        include $filename;
        $html = ob_get_clean();
        return $html;
    }

    /**
     * Gets the profile data as an array.
     * Can also be called with the help of ArrayAccess via $this['asArray']
     * 
     * @access public
     * @return array
     */
    public function getAsArray()
    {
        return array(
            'totalTime' => $this['totalTime'],
            'queriesData' => $this['queriesData']
        );
    }

    public function offsetExists($key)
    {
        return in_array(
            $key,
            array(
                'asHtml', 'totalTime', 'queriesData'
            )
        );
    }

    /**
     * Adds a query and a profile for it to the list of queries.
     * Used by AMysql_Statement. Do not call externally!
     * 
     * @param string $query         The SQL query.
     * @param float $queryTime      The time the query took.
     * @access public
     * @return $this
     */
    public function addQuery($query, $queryTime)
    {
        $this->queries[] = $query;
        $data = array (
            'query' => $query,
            'time' => $queryTime,
            'backtrace' => array(),
        );
        if ($this->amysql->includeBacktrace) {
            $opts = 0;
            if (defined('DEBUG_BACKTRACE_IGNORE_ARGS')) {
                $opts |= DEBUG_BACKTRACE_IGNORE_ARGS;
            }
            $data['backtrace'] = debug_backtrace($opts);
        }
        $this->queriesData[] = $data;
        if (is_numeric($queryTime)) {
            $this->totalTime += $queryTime;
        }
        return $this;
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
        return $this->queries;
    }

    /**
     * Returns an arrays of profiled query data. Each value is an array that consists
     * of:
     *  - query - The SQL query performed
     *  - time - The amount of seconds the query took (float)
     *
     * If profileQueries wss off at any query, its time value will be null.
     * 
     * @return array[]
     */
    public function getQueriesData()
    {
        return $this->queriesData;
    }

    /**
     * Resets the data in the profiler. Not recommended.
     * Use AMysql_Abstract::useNewProfiler() instead if possible.
     * 
     * @access public
     * @return void
     */
    public function reset()
    {
        $this->totalTime = 0.0;
        $this->queriesData = array();
        return $this;
    }

    public function offsetGet($key)
    {
        switch ($key) {
            case 'totalTime':
                return $this->totalTime;
                break;
            case 'queriesData':
                return $this->getQueriesData();
                break;
            case 'asHtml':
                return $this->getAsHtml();
                break;
            case 'asArray':
                return $this->getAsArray();
                break;
            default:
                trigger_error("Invalid key: `$key`.");
                break;
        }
    }

    public function offsetSet($key, $value)
    {
        trigger_error("Access denied. Cannot set key `$key`.", E_USER_WARNING);
    }

    public function offsetUnset($key)
    {
        trigger_error(
            "Access denied. Cannot unset key `$key`.",
            E_USER_WARNING
        );
        return false;
    }

    public function __get($key)
    {
        return $this->offsetGet($key);
    }
}
