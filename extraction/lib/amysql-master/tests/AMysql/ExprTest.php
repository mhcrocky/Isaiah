<?php /* vim: set tabstop=8 expandtab : */
class ExprTest extends AMysql_TestCase {

    public function testLiteral()
    {
        $stmt = $this->_amysql->newStatement();
        $expr = $this->_amysql->expr('LOL');
        $quoted = $stmt->quoteInto('?', $expr);
        $this->assertEquals('LOL', $quoted);
    }

    public function testColumnIn()
    {
        $stmt = $this->_amysql->newStatement();
        $expr = $this->_amysql->expr(AMysql_Expr::COLUMN_IN, 'tableName', array('LOL', 1, 2, 3));
        $quoted = $stmt->quoteInto('?', $expr);
        $this->assertEquals(" `tableName` IN ('LOL', 1, 2, 3) ", $quoted);
    }

    public function testEscapeLike()
    {
        $stmt = $this->_amysql->newStatement();
        $expr = $this->_amysql->expr(AMysql_Expr::ESCAPE_LIKE, 'lol');
        $quoted = $stmt->quoteInto('?', $expr);
        $this->assertEquals("'%lol%' ESCAPE '='", $quoted);
    }

    public function testEscapeLikeEquals()
    {
        $stmt = $this->_amysql->newStatement();
        $expr = $this->_amysql->expr(AMysql_Expr::ESCAPE_LIKE, 'lol=');
        $quoted = $stmt->quoteInto('?', $expr);
        $this->assertEquals("'%lol==%' ESCAPE '='", $quoted);
    }

    public function testEscapeLikePercent()
    {
        $stmt = $this->_amysql->newStatement();
        $expr = $this->_amysql->expr(AMysql_Expr::ESCAPE_LIKE, 'lol%');
        $quoted = $stmt->quoteInto('?', $expr);
        $this->assertEquals("'%lol=%%' ESCAPE '='", $quoted);
    }

    public function testEscapeCustomPattern()
    {
        $stmt = $this->_amysql->newStatement();
        $expr = $this->_amysql->expr(AMysql_Expr::ESCAPE_LIKE, 'l_o%l', '%%_%s');
        $quoted = $stmt->quoteInto('?', $expr);
        $this->assertEquals("'%_l=_o=%l' ESCAPE '='", $quoted);
    }

    public function testEscapeColumnName()
    {
        $stmt = $this->_amysql->newStatement();
        $expr = $this->_amysql->expr(AMysql_Expr::ESCAPE_TABLE, 'table');
        $quoted = $stmt->quoteInto('?', $expr);
        $this->assertEquals("`table`", $quoted);
    }

    public function testEscapeColumnNameWithSpecialChar()
    {
        $stmt = $this->_amysql->newStatement();
        $expr = $this->_amysql->expr(AMysql_Expr::ESCAPE_TABLE, 'tabl`e');
        $quoted = $stmt->quoteInto('?', $expr);
        $this->assertEquals("`tabl\\`e`", $quoted);
    }
}
