<?php /* vim: set expandtab : */
/**
 * Class for making custom expressions for AMysql value binding.
 * This is what you should use to be able to add specific kinds
 * of purposely unquoted, non-numeric values into prepared
 * statements, such as being able to call mysql functions to
 * set values.
 *
 * This class can be extended and is recommended to do so if you want
 * custom expressions; in which case you would have to manually
 * instatiate that class each time, rather than using AMysql::expr()
 *
 * Expression codes 0-999 should be considered reserved to this class;
 * if you extend this class, please use at least 1000 as expression code ints.
 *
 * Visit https://github.com/amcsi/amysql
 * @author Szerémi Attila
 * @created 2011.06.10. 13:26:56  
 * @license     MIT License; http://www.opensource.org/licenses/mit-license.php
 **/ 
class AMysql_Expr
{

    public $prepared;

    public $amysql;

    /**
     * Literal string
     *
     * e.g. $currentTimestampBind = new AMysql_Expr(
     * 		AMysql_Expr::LITERAL, 'CURRENT_TIMESPAMP'
     * 	);
     *	// or
     *	$currentTimestampBind = new AMysql_Expr('CURRENT_TIMESTAMP');
     **/         
    const LITERAL = 0;
    const EXPR_LITERAL = 0;

    /**
     * IN() function. In this case, the 2nd parameter is the table name, the
     * third is the array of values
     *
     * e.g.
     * 	$idIn = new AMysql_Expr(AMysql_Expr::COLUMN_IN, 'id', array (
     *		'3', '4', '6'
     * 	));
     **/         
    const COLUMN_IN = 1;
    const EXPR_COLUMN_IN = 1;

    /**
     * Escapes wildcards for a LIKE statement.
     * The second parameter has to be the string to escape, and
     * the third is optional, and is the sprintf format of the string,
     * where literal wildcards should appear. The default is %%%s%%,
     * where the input of "something" will result in:
     *	"'%something%' ESCAPE '='"
     **/         
    const ESCAPE_LIKE = 2;

    /**
     * Escapes a table name and encloses it in quotes. The second parameter is the table name. 
     */
    const ESCAPE_TABLE = 3;
    const EXPR_TABLE = 3;

    /**
     * Escapes a column name and encloses it in quotes. The second parameter is the column name. 
     */
    const ESCAPE_COLUMN = 4;
    const EXPR_COLUMN = 4;

    /**
     * @constructor
     * This constructor accepts different parameters in different cases.
     * Before everything, if the first parameter is an AMysql instance, it
     * is saved, and is shifted from the arguments array, and the following
     * applies in either case:
     * The first parameter is mandatory: the one that gives the type of
     * expression. The types of expressions can be found as constants on this
     * class, and their documentation can be found above each constant type
     * declaration.
     *
     * @params ...$variadic
     * 
     * In case of a literal string, you can just pass the literal string as
     * the only parameter.
     **/         
    public function __construct(/* args */)
    {
        $args = func_get_args();
        if ($args[0] instanceof AMysql_Abstract) {
            $this->amysql = array_shift($args);
        }
        if ($args) {
            call_user_func_array(array($this, 'set'), $args);
        }
    }

    public function set()
    {
        $args = func_get_args();
        // literal
        if (is_string($args[0])) {
            $prepared = $args[0];
        } else {
            $prepared = $this->setByArray($args);
        }
        $this->prepared = $prepared;
    }

    /**
     * This method performs the logic of determining what the
     * expression should result in.
     * When extending this class, it is recommended that you
     * override this method, determine whether the expression code
     * is something that you want to handle, otherwise call and return
     * the parent's setByArray() method
     * 
     * @param array $args           The first index should contain the type
     *                              of expression to use, the rest depends on
     *                              the type of expression. See the class
     *                              constants for available types.
     * @access protected
     * @return string               The literal string to use
     */
    protected function setByArray(array $args)
    {
        switch ($args[0]) {
            case self::EXPR_LITERAL:
                $prepared = $args[1];
                break;
            case self::EXPR_COLUMN_IN:
                $prepared = '';
                if ($args[2]) {
                    foreach ($args[2] as &$val) {
                        $val = $this->amysql->escape($val);
                    }
                    $prepared = ' ' . $this->escapeColumn($args[1]) . ' IN ' .
                        '(' . join(', ', $args[2]) . ') ';
                } else {
                    // If the array is empty, don't break the WHERE syntax
                    $prepared = 0;
                }
                break;
            case self::ESCAPE_LIKE:
                $format = '%%%s%%';
                if (!empty($args[2])) {
                    $format = $args[2];
                }
                $likeEscaped = AMysql_Abstract::escapeLike($args[1]);
                $formatted = sprintf($format, $likeEscaped);
                if ($this->amysql) {
                    if ('mysqli' == $this->amysql->isMysqli) {
                        $escaped = $this->amysql->link->real_escape_string(
                            $formatted
                        );
                    } else {
                        $escaped = mysql_real_escape_string(
                            $formatted,
                            $this->amysql->link
                        );
                    }
                } else {
                    $escaped = mysql_real_escape_string($formatted);
                }
                $prepared = "'$escaped'";
                $prepared .= " ESCAPE '='";
                break;
            case self::ESCAPE_TABLE:
                $prepared = $this->amysql->escapeTable($args[1]);
                break;
            case self::ESCAPE_COLUMN:
                $prepared = $this->amysql->escapeColumn($args[1]);
                break;
            default:
                throw new Exception("No such expression type: `$args[0]`.");
                break;
        }
        return $prepared;
    }

    public function escapeTable($table)
    {
        if ($this->amysql instanceof AMysql_Abstract) {
            return $this->amysql->escapeTable($table);
        }
        return AMysql_Abstract::escapeIdentifier($table);
    }

    public function escapeColumn($column)
    {
        if ($this->amysql instanceof AMysql_Abstract) {
            return $this->amysql->escapeColumn($column);
        }
        return AMysql_Abstract::escapeIdentifier($column);
    }

    public function toString()
    {
        if (!isset($this->prepared)) {
            throw new Exception("No prepared string for mysql expression.");
        }
        return (string) $this->prepared;
    }

    /**
     * Returns the literal string this expression should resolve to.
     * 
     * @access public
     * @return string
     */
    public function __toString()
    {
        return (string) $this->toString();
    }
} 
