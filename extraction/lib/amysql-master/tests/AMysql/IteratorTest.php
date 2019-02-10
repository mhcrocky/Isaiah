<?php /* vim: set tabstop=8 expandtab : */
class IteratorTest extends AMysql_TestCase {

    public function testIterate() {
	$data = array (
	    'string' => array (
		3, 'blah'
	    )
	);
	$this->_amysql->insert($this->tableName, $data);
	$stmt = $this->_amysql->query("SELECT * FROM $this->tableName");
	$i = 0;
	foreach ($stmt as $key => $value) {
	    if ($i == 0) {
		$this->assertEquals(0, $key);
		$this->assertEquals('3', $value['string']);
	    }
	    if ($i == 1) {
		$this->assertEquals(1, $key);
		$this->assertEquals('blah', $value['string']);
	    }
	    if ($i == 2) {
		$this->fail();
	    }
	    $i++;
	}
	$i = 0;
	foreach ($stmt as $key => $value) {
	    if ($i == 0) {
		$this->assertEquals(0, $key);
		$this->assertEquals('3', $value['string']);
	    }
	    if ($i == 1) {
		$this->assertEquals(1, $key);
		$this->assertEquals('blah', $value['string']);
	    }
	    if ($i == 2) {
		$this->fail();
	    }
	    $i++;
	}
    }

    public function testIterateNonSelect() {
	$data = array (
	    'string' => array (
		3, 'blah'
	    )
	);
	$this->_amysql->insert($this->tableName, $data);
	$stmt = $this->_amysql->lastStatement;
	$this->setExpectedException('LogicException');
	foreach ($stmt as $key => $value) {
	}
    }
}
?>

