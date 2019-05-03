<?php
/**
 * The class is for helping with the assembly of a mysqli SELECT string.
 * Rather than having to manually the string - especially of not only the values
 * of certain parameters would be dynamic, but the tables, columns, joins, wheres
 * and everything else is - you can get a new instance of this class by calling
 * $amyql->select(), and you can freely add fragments to the SELECT query with
 * the different methods of this class.
 * When you call ->execute(), the final, but unbound SQL string is automatically
 * prepared for binding with the passed (or pre-set) binds and execution.
 *
 * Since the bindings are applied after the SELECT sql string is assembled,
 * you are encouraged to use :named placeholders for WHEREs and anything similar,
 * binding the values with ->bindValue() each time, or collecting all the binds
 * to use in the end and passing it to ->execute().
 *
 * Please check @link tests/AMysql/SelectTest.php for examples.
 *
 * @todo    not choosing any columns should default to all columns rather than
 *          failing
 *
 * Anatomy of a select:
 * SELECT <SELECT OPTIONS> <COLUMNS> FROM <FROMS> <JOINS> <WHERES> <GROUP BYS> <HAVINGS>
 * <ORDER BYS> <LIMIT> <OFFSET>
 * 
 * Visit https://github.com/amcsi/amysql
 * @author      Szerémi Attila
 * @license     MIT License; http://www.opensource.org/licenses/mit-license.php
 */
class AMysql_Select extends AMysql_Statement
{

    protected $selectOptions = array ();
    protected $columnLiteral;
    protected $columns = array ();
    protected $froms = array ();
    protected $joins = array ();
    protected $wheres = array ();
    protected $groupBys = array ();
    protected $havings = array ();
    protected $orderBys = array ();
    protected $limit = null;
    protected $offset = null;

    /**
     * Adds a select option.
     *
     * e.g.
     * SQL_CALC_FOUND_ROWS
     * DISTINCT
     *
     * @param string $selectOption       The select option.
     * @access public
     * @return AMysql_Select (chainable)
     */
    public function option($selectOption)
    {
        $this->selectOptions[$selectOption] = $selectOption;
        return $this;
    }

    /**
     * Formats a column name and an optional alias to form `columnName` AS alias.
     * The alias is automatically not taken into account if it's numeric.
     * No need to worry about escaping the select all star character '*'.
     * 
     * @param string $columnName    The column name.
     * @param string $alias         (Optional) the alias to select AS
     * @access public
     * @return string
     */
    public function formatSelectColumn($columnName, $alias = null)
    {
        if ('*' == $columnName[strlen($columnName) - 1]) {
            return '*' === $columnName ? '*'
                : $this->amysql->escapeColumn(substr($columnName, 0, -2)) .
                '.*'
            ;
        }
        $ret = $this->amysql->escapeColumn($columnName);
        if ($alias && !is_numeric($alias)) {
            $ret .= ' AS ' . $this->amysql->escapeColumn($alias);
        }
        return $ret;
    }

    /**
     * Adds one or more COLUMN to the list of columns to select. 
     * 
     * @param string|AMysql_Expr|array $columns         The column name. Can be an array of column
     *                                                  names in which case the key can mark the
     *                                                  optional alias of the column.
     * @param array $options                            (Options) an array of config options
     *                                                  columnPrefix - prefix each column with this
     *                                                  table prefix
     * @access public
     * @return AMysql_Select (chainable)
     */
    public function column($columns, $options = array ())
    {
        $columns = (array) $columns;
        $columnPrefix = !empty($options['columnPrefix']) ? $options['columnPrefix'] . '.' : '';
        foreach ($columns as $alias => $columnName) {
            if ('*' == $columnName[strlen($columnName)- 1]) {
                $key = '*';
            } else {
                $key = $alias && !is_numeric($alias) ? $alias : $columnName;
            }
            $this->columns[$key] = $this->formatSelectColumn("$columnPrefix$columnName", $alias);
        }
        return $this;
    }

    /**
     * Add this literal string between select options and columns.
     *
     * @param string $columnLiteral       Literal string
     * @access public
     * @return AMysql_Select (chainable)
     */
    public function columnLiteral($columnLiteral)
    {
        if ($this->columnLiteral) {
            $this->columnLiteral .= ", $columnLiteral";
        } else {
            $this->columnLiteral = $columnLiteral;
        }
        return $this;
    }

    /**
     * Formats a table name and an optional alias to form `tableName` AS alias.
     * The alias is automatically not taken into account if it's numeric.
     * 
     * @param string $tableName      The table name.
     * @param string $alias         (Optional) the alias to select AS
     * @access public
     * @return string
     */
    public function formatFrom($tableName, $alias = null)
    {
        $ret = $this->amysql->escapeTable($tableName);
        if ($alias && !is_numeric($alias)) {
            $ret .= ' AS ' . $this->amysql->escapeTable($alias);
        }
        return $ret;
    }

    /**
     * 1) Adds one or more table name to the list of tables to select FROM.
     *
     * 2) Alternatively, if you only select from 1 table here, you can supply an array
     * of columns to select, having them automatically prefixed to the needed prefix
     * of the table selected from. Similar to Zend Framework 1.
     * 
     * e.g.
     * ->from(array('p' => 'products'),
     *       array('product_id', 'product_name'));// Build this query:
     * // results in: SELECT p."product_id", p."product_name" FROM "products" AS p
     *
     * You can use literals as table names with AMysql_Expr.
     * 
     * @param string|AMysql_Expr|array $tables          The table name. Can be an array or table
     *                                                  names in which case the key can mark the
     *                                                  optional alias of the table.
     * @param array $columns                            (Optional)
     *                                                  The columns from this table to select.
     *                                                  Do not use if you are selecting from more than 1 tables!
     * @access public
     * @return AMysql_Select (chainable)
     */
    public function from($tables, $columns = array ())
    {
        $tables = (array) $tables;
        foreach ($tables as $alias => $tableName) {
            $key = !is_numeric($alias) ? $alias : $tableName;
            $this->froms[$key] = $this->formatFrom($tableName, $alias);
        }
        if ($columns) {
            $key = !is_numeric($alias) ? $alias : $tableName;
            $columnOptions = array ();
            $columnOptions['columnPrefix'] = $key;
            $this->column($columns, $columnOptions);
        }
        return $this;
    }

    /**
     * Adds a JOIN 
     * 
     * @param string $type      Type of join. 'left' would be LEFT JOIN, 'inner'
     *                          would be INNER JOIN. Leaving this falsy will result
     *                          in a normal JOIN.
     * @param string $table     The table name to join. Can be a 1 element array of
     *                          ['alias' => 'tableName']
     * @param string $on        The ON clause unbound.
     * @param array $columns    (Optional) The columns from this table to select. TODO!
     * @param boolean $prepend  (Optional) whether to prepend this JOIN to the other
     *                          joins. Default: false (append).
     * @access public
     * @return AMysql_Select (chainable)
     */
    public function join($type, $table, $on, $columns = array (), $prepend = false)
    {
        $table = (array) $table;
        $tableName = reset($table);
        $alias = key($table);
        $joinText = $type ? strtoupper($type) . ' JOIN' : 'JOIN';
        $tableText = $this->formatFrom($tableName, $alias);
        $text = "$joinText $tableText ON ($on)";
        if ($prepend) {
            array_unshift($this->joins, $text);
        } else {
            $this->joins[] = $text;
        }
        if ($columns) {
            $key = !is_numeric($alias) ? $alias : $tableName;
            $columnOptions = array ();
            $columnOptions['columnPrefix'] = $key;
            $this->column($columns, $columnOptions);
        }
        return $this;
    }

    /**
     * Adds a WHERE fragment. All fragments are joined by an AND
     * at the end. 
     * WARNING: When binding a WHERE part that is an AMysql_Expr, you shouldn't
     * pass it to this method. You should instead pass a new bind string
     * (e.g. :wherePart) and then make a bind to the expression using
     * "wherePart" as the key. If you ignore this and the expression contains 
     * any bind-related substrings, unexpected results will happen when 
     * placeholders are bound.
     * Note that this object cannot automatically place
     * it in for you as a bind due to not knowing whether named or unnamed 
     * binds are being used.
     * 
     * @param string $where     Unbound WHERE fragment
     * @access public
     * @return AMysql_Select (chainable)
     */
    public function where($where)
    {
        $this->wheres[] = $where;
        return $this;
    }

    /**
     * Syntactic sugar for $this->where($where)->bindValue($key, $val);
     *
     * Usage e.g.
     *  $select->whereBind('id = :id', 'id', 3)
     * 
     * @param string $where     Unbound WHERE fragment
     * @param mixed $key        @see AMysql_Statement::bindValue()
     * @param mixed $val        @see AMysql_Statement::bindValue()
     * @access public
     * @return AMysql_Select (chainable)
     */
    public function whereBind($where, $key, $val)
    {
        return $this->where($where)->bindValue($key, $val);
    }

    /**
     * Adds an GROUP BY parameter 
     * 
     * @param name $col         Column name
     * @param bool $desc        (Optional) Whether to sort DESC. Default: false
     * @param bool $prepend     (Optional) Whether to prepend this parameter.
     *                          Default: FALSE
     * @access public
     * @return AMysql_Select (chainable)
     */
    public function groupBy($col, $desc = false, $prepend = false)
    {
        $what = $this->amysql->escapeColumn($col);
        if ($desc) {
            $what .= ' DESC';
        }
        if ($prepend) {
            array_unshift($this->groupBys, $what);
        } else {
            $this->groupBys[] = $what;
        }
        return $this;
    }

    /**
     * Adds an GROUP BY parameter with no escaping.
     * 
     * @param name $col         What to group by. Can list multiple literals separated by commas.
     * @param bool $prepend     (Optional) Whether to prepend this parameter.
     *                          Default: FALSE
     * @access public
     * @return AMysql_Select (chainable)
     */
    public function groupByLiteral($what, $prepend = false)
    {
        if ($prepend) {
            array_unshift($this->groupBys, $what);
        } else {
            $this->groupBys[] = $what;
        }
        return $this;
    }

    /**
     * Adds a HAVING fragment. All fragments are joined by an AND
     * at the end. 
     * 
     * @param string $having        Unbound HAVING fragment
     * @access public
     * @return AMysql_Select (chainable)
     */
    public function having($having)
    {
        $this->havings[] = $having;
        return $this;
    }

    /**
     * Adds an ORDER BY parameter 
     * 
     * @param name $col         Column name
     * @param bool $desc        (Optional) Whether to sort DESC. Default: false
     * @param bool $prepend     (Optional) Whether to prepend this parameter.
     *                          Default: FALSE
     * @access public
     * @return AMysql_Select (chainable)
     */
    public function orderBy($col, $desc = false, $prepend = false)
    {
        $what = $this->amysql->escapeColumn($col);
        if ($desc) {
            $what .= ' DESC';
        }
        if ($prepend) {
            array_unshift($this->orderBys, $what);
        } else {
            $this->orderBys[] = $what;
        }
        return $this;
    }

    /**
     * Adds an ORDER BY parameter with no escaping.
     * 
     * @param name $col         What to order by. Can list multiple literals separated by commas.
     * @param bool $prepend     (Optional) Whether to prepend this parameter.
     *                          Default: FALSE
     * @access public
     * @return AMysql_Select (chainable)
     */
    public function orderByLiteral($what, $prepend = false)
    {
        if ($prepend) {
            array_unshift($this->orderBys, $what);
        } else {
            $this->orderBys[] = $what;
        }
        return $this;
    }

    /**
     * Adds a LIMIT
     * You can only limit the number of rows returned here (e.g. LIMIT 10).
     * To set the offset (e.g. LIMIT 20, 10), you must do:
     * $select->limit(10)->offset(20);
     * 
     * @param int $limit    The LIMIT
     * @access public
     * @return AMysql_Select (chainable)
     */
    public function limit($limit)
    {
        $this->limit = (is_numeric($limit) && 0 < $limit) ? (int) $limit : null;
        return $this;
    }

    /**
     * Adds an OFFSET
     * 
     * @param int $offset   The OFFSET
     * @access public
     * @return AMysql_Select (chainable)
     */
    public function offset($offset)
    {
        $this->offset = (is_numeric($offset) && 0 <= $offset) ?
            (int) $offset :
            null;
        return $this;
    }

    /**
     * Gets the full, bound SQL string ready for use with MySQL.
     * 
     * @param string $prepared      (Optional) Only for parent compatibility.
     * @access public
     * @return string
     */
    public function getSql($prepared = null)
    {
        if (!$prepared) {
            $this->prepared = $this->getUnboundSql();
        }
        $ret = parent::getSql($prepared);
        return $ret;
    }

    /**
     * Gets the SQL string without the binds applied yet.
     * 
     * @access public
     * @return string
     */
    public function getUnboundSql()
    {
        $parts = array ('SELECT');
        if ($this->selectOptions) {
            $parts[] = join(', ', $this->selectOptions);
        }

        $columns = $this->columns;
        if ($this->columnLiteral) {
            $columns[] = $this->columnLiteral;
        }
        $parts[] = join(', ', $columns);

        $parts = array (join(' ', $parts)); // okay so everything so far should be 1 part.

        if ($this->froms) {
            $parts[] = 'FROM ' . join(', ', $this->froms);
        }

        foreach ($this->joins as $join) {
            $parts[] = $join;
        }

        if ($this->wheres) {
            $parts[] = 'WHERE ' . join(' AND ', $this->wheres);
        }

        if ($this->groupBys) {
            $ob = array ();
            foreach ($this->groupBys as $groupBy) {
                $ob[] = $groupBy;
            }
            $ob = join(', ', $ob);
            $parts[] = 'GROUP BY ' . $ob;
        }

        if ($this->havings) {
            $parts[] = 'HAVING ' . join(' AND ', $this->havings);
        }

        if ($this->orderBys) {
            $ob = array ();
            foreach ($this->orderBys as $orderBy) {
                $ob[] = $orderBy;
            }
            $ob = join(', ', $ob);
            $parts[] = 'ORDER BY ' . $ob;
        }

        if ($this->limit) {
            $parts[] = "LIMIT $this->limit";
        }

        if ($this->offset) {
            $parts[] = "OFFSET $this->offset";
        }
        $sql = join("\n", $parts);
        return $sql;
    }
}
