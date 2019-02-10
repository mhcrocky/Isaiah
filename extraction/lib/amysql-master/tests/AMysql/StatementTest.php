<?php /* vim: set tabstop=8 expandtab : */
class StatementTest extends AMysql_TestCase {

    public function testDoubleExecute() {
        $sql = "SELECT * FROM $this->tableName";
        $stmt = $this->_amysql->prepare($sql);
        $stmt->execute();
	$this->setExpectedException('LogicException');
        $stmt->execute();
    }

    public function testNamedBinds1() {
	$sql = ":foo: :bar:";
	$binds = array (
	    ':foo:' => 'text1',
	    ':bar:' => 'text2'
	);
	$stmt = $this->_amysql->prepare($sql);
	$expected = "'text1' 'text2'";
	$stmt->binds = $binds;
	$this->assertEquals($expected, $stmt->getSql());
    }

    public function testNamedBinds2() {
	$sql = ":foo: :foo:";
	$binds = array (
	    ':foo:' => 'text1'
	);
	$stmt = $this->_amysql->prepare($sql);
	$expected = "'text1' 'text1'";
	$stmt->binds = $binds;
	$this->assertEquals($expected, $stmt->getSql());
    }

    public function testNamedBinds3() {
	$sql = ":fooo :foo";
	$binds = array (
	    ':foo' => 'shorter',
	    ':fooo' => 'longer'
	);
	$stmt = $this->_amysql->prepare($sql);
	$expected = "'longer' 'shorter'";
	$stmt->binds = $binds;
	$this->assertEquals($expected, $stmt->getSql());
    }

    public function testNamedBinds4() {
	$sql = ":foo :bar";
	$binds = array (
	    'foo' => ':bar',
	    'bar' => 'cheese'
	);
	$stmt = $this->_amysql->prepare($sql);
	$expected = "':bar' 'cheese'";
	$stmt->binds = $binds;
	$this->assertEquals($expected, $stmt->getSql());
    }

    public function testAutoColon1() {
	$sql = ":foo :bar";
	$binds = array (
	    'foo' => 's1',
	    'bar' => 's2'
	);
	$stmt = $this->_amysql->prepare($sql);
	$expected = "'s1' 's2'";
	$stmt->binds = $binds;
	$this->assertEquals($expected, $stmt->getSql());
    }

    public function testAutoColon2() {
	$sql = ":foo:bar";
	$binds = array (
	    'foo' => 's1',
	    'bar' => 's2'
	);
	$stmt = $this->_amysql->prepare($sql);
	$expected = "'s1''s2'";
	$stmt->binds = $binds;
	$this->assertEquals($expected, $stmt->getSql());
    }

    public function testAutoColon3() {
	$sql = ":ékezet:árvíz";
	$binds = array (
	    'ékezet' => 's1',
	    'árvíz' => 's2'
	);
	$stmt = $this->_amysql->prepare($sql);
	$expected = "'s1''s2'";
	$stmt->binds = $binds;
	$this->assertEquals($expected, $stmt->getSql());
    }

    public function testAutoColon4() {
	$sql = ":foo\n:bar :a:b :c :d";
	$binds = array (
	    'foo' => 's1',
	    'bar' => 's2',
	    'a' => 's3',
	    'b' => 's4',
	    'c' => 's5',
	    'd' => ''
	);
	$stmt = $this->_amysql->prepare($sql);
	$expected = "'s1'\n's2' 's3''s4' 's5' ''";
	$stmt->binds = $binds;
	$this->assertEquals($expected, $stmt->getSql());
    }

    public function testNoAutoColon1() {
	$sql = "@ékezet:árvíz:";
	$binds = array (
	    '@ékezet' => 's1',
	    'árvíz:' => 's2'
	);
	$stmt = $this->_amysql->prepare($sql);
	$expected = "'s1''s2'";
	$stmt->binds = $binds;
	$this->assertEquals($expected, $stmt->getSql());
    }

    public function testPairUp() {
	$data = array (
	    'string' => array (
		3, 'blah'
	    )
	);
	$this->_amysql->insert($this->tableName, $data);
	$stmt = $this->_amysql->query("SELECT * FROM $this->tableName");
	$results = $stmt->pairUp();
	$expected = array (
	    '1' => '3',
	    '2' => 'blah'
	);
	$this->assertEquals($expected, $results);
    }

    public function testPairUpColumnNames() {
	$data = array (
	    'string' => array (
		3, 'blah'
	    )
	);
	$this->_amysql->insert($this->tableName, $data);
	$stmt = $this->_amysql->query("SELECT * FROM $this->tableName");
	$results = $stmt->pairUp('string', 'id');
	$expected = array (
	    '3' => '1',
	    'blah' => '2'
	);
	$this->assertEquals($expected, $results);
    }

    public function testPairUpMixed() {
	$data = array (
	    'string' => array (
		3, 'blah'
	    )
	);
	$this->_amysql->insert($this->tableName, $data);
	$stmt = $this->_amysql->query("SELECT * FROM $this->tableName");
	$results = $stmt->pairUp('string', 0);
	$expected = array (
	    '3' => '1',
	    'blah' => '2'
	);
	$this->assertEquals($expected, $results);
    }

    public function testPairUpSame() {
	$data = array (
	    'string' => array (
		3, 'blah'
	    )
	);
	$this->_amysql->insert($this->tableName, $data);
	$stmt = $this->_amysql->query("SELECT * FROM $this->tableName");
	$results = $stmt->pairUp(1, 1);
	$expected = array (
	    '3' => '3',
	    'blah' => 'blah'
	);
	$this->assertEquals($expected, $results);
    }

    public function testFetchObject() {
	$data = array (
	    'string' => array (
		3, 'blah'
	    )
	);
	$this->_amysql->insert($this->tableName, $data);
	$stmt = $this->_amysql->query("SELECT * FROM $this->tableName");
	$result = $stmt->fetchObject();
	$this->assertEquals('1', $result->id);
	$this->assertEquals('3', $result->string);
    }

    public function testFetchObject2() {
	$data = array (
	    'string' => array (
		3, 'blah'
	    )
	);
	$this->_amysql->insert($this->tableName, $data);
	$stmt = $this->_amysql->query("SELECT * FROM $this->tableName");
	$result = $stmt->fetchObject();
	$this->assertTrue($result instanceof stdClass);
	$this->assertEquals(3, $result->string);
	$result = $stmt->fetchObject('ArrayObject', 
	    array (array (),
		ArrayObject::ARRAY_AS_PROPS | ArrayObject::STD_PROP_LIST
	    )
	);
	$this->assertTrue($result instanceof ArrayObject);
	$this->assertEquals('blah', $result->string);
    }

    public function testFetchAllAssoc() {
	$data = array (
	    'string' => array (
		3, 'blah'
	    )
	);
	$this->_amysql->insert($this->tableName, $data);
	$stmt = $this->_amysql->query("SELECT * FROM $this->tableName");
	$results = $stmt->fetchAllAssoc();
	$expected = array (
	    array (
		'id' => '1',
		'string' => '3'
	    ),
	    array (
		'id' => '2',
		'string' => 'blah'
	    )
	);
	$this->assertEquals($expected, $results);
    }

    public function testFetchAllDefault() {
	$data = array (
	    'string' => array (
		3, 'blah'
	    )
	);
	$this->_amysql->insert($this->tableName, $data);
	$stmt = $this->_amysql->query("SELECT * FROM $this->tableName");
	$results = $stmt->fetchAll();
	$expected = array (
	    array (
		'id' => '1',
		'string' => '3'
	    ),
	    array (
		'id' => '2',
		'string' => 'blah'
	    )
	);
	$this->assertEquals($expected, $results);
    }

    public function testFetchAllAssocIdColumn() {
	$data = array (
	    'string' => array (
		3, 'blah'
	    )
	);
	$this->_amysql->insert($this->tableName, $data);
	$stmt = $this->_amysql->query("SELECT * FROM $this->tableName");
	$results = $stmt->fetchAllAssoc('id');
	$expected = array (
	    '1' => array (
		'id' => '1',
		'string' => '3'
	    ),
	    '2' => array (
		'id' => '2',
		'string' => 'blah'
	    )
	);
	$this->assertEquals($expected, $results);
    }

    public function testFetchAllAssocIdColumn2() {
	$data = array (
	    'string' => array (
		3, 'blah'
	    )
	);
	$this->_amysql->insert($this->tableName, $data);
	$stmt = $this->_amysql->query("SELECT * FROM $this->tableName");
	$results = $stmt->fetchAllAssoc(1);
	$expected = array (
	    '3' => array (
		'id' => '1',
		'string' => '3'
	    ),
	    'blah' => array (
		'id' => '2',
		'string' => 'blah'
	    )
	);
	$this->assertEquals($expected, $results);
    }

    public function testBindParam() {
	$sql = " :a ";
	$bind = 1;
	$stmt = $this->_amysql->prepare($sql);
	$stmt->bindParam('a', $bind);
	$bind = 2;
	$resultSql = $stmt->getSql();
	$this->assertEquals(' 2 ', $resultSql);
    }

    public function testBindValue() {
	$sql = " :a ";
	$bind = 1;
	$stmt = $this->_amysql->prepare($sql);
	$stmt->bindValue('a', $bind);
	$bind = 2;
	$resultSql = $stmt->getSql();
	$this->assertEquals(' 1 ', $resultSql);
    }

    public function testSetFetchModeExtraArgs() {
	$data = array (
	    'string' => array (
		3, 'blah'
	    )
	);
	$this->_amysql->insert($this->tableName, $data);
	$stmt = $this->_amysql->query("SELECT * FROM $this->tableName");
	$stmt->setFetchMode(AMysql_Abstract::FETCH_OBJECT, 'ArrayObject',
	    array (array (),
		ArrayObject::ARRAY_AS_PROPS | ArrayObject::STD_PROP_LIST
	    )
	);
	$result = $stmt->fetch();
	$this->assertTrue($result instanceof ArrayObject);
	$this->assertEquals('3', $result->string);
    }

    /**
     * 
     **/
    public function repeat20() {
	return array_fill(0, 20, array ());
    }

    public function testCount() {
	$data = array (
	    array (
		'string' => 3
	    ),
	    array (
		'string' => 'blah',
	    )
	);
	$this->_amysql->insert($this->tableName, $data);
	$stmt = $this->_amysql->query("SELECT * FROM $this->tableName");
	$this->assertEquals(2, count($stmt));
    }

    public function testCountNonSelect() {
	$data = array (
	    array (
		'string' => 3
	    ),
	    array (
		'string' => 'blah',
	    )
	);
	$this->_amysql->insert($this->tableName, $data);
	$stmt = $this->_amysql->lastStatement;
	$this->setExpectedException('LogicException');
	count($stmt);
    }

    public function testFetchAllColumn()
    {
        $data = array (
            array (
                'string' => 3
            ),
            array (
                'string' => 'blah',
            )
        );
        $this->_amysql->insert($this->tableName, $data);
        $stmt = $this->_amysql->lastStatement;
        $sql = "SELECT * FROM $this->tableName";
        $stmt = $this->_amysql->query($sql);


        $result = $stmt->fetchAllColumn(0);
        $expected = array (1, 2);
        $this->assertEquals($expected, $result);

        $result = $stmt->fetchAllColumn('id');
        $expected = array (1, 2);
        $this->assertEquals($expected, $result);

        $result = $stmt->fetchAllColumn(1);
        $expected = array ('3', 'blah');
        $this->assertEquals($expected, $result);

        $result = $stmt->fetchAllColumn('string');
        $expected = array ('3', 'blah');
        $this->assertEquals($expected, $result);
    }

    public function testFetchAllColumns() {
	$data = array (
	    array (
		'string' => 3
	    ),
	    array (
		'string' => 'blah',
	    )
	);
	$this->_amysql->insert($this->tableName, $data);
	$stmt = $this->_amysql->lastStatement;
        $sql = "SELECT * FROM $this->tableName";
        $stmt = $this->_amysql->query($sql);
        $result = $stmt->fetchAllColumns();

        $expected = array (
            'id' => array ('1', '2'),
            'string' => array ('3', 'blah')
        );
        $this->assertEquals($expected, $result);
    }

    public function testFetchAllColumnsEmpty() {
        $sql = "SELECT * FROM $this->tableName";
        $stmt = $this->_amysql->query($sql);
        $result = $stmt->fetchAllColumns();
        $this->assertEquals(array(), $result);
    }

    public function testFetchAllColumnsNamed() {
	$data = array (
	    array (
		'string' => 3
	    ),
	    array (
		'string' => 'blah',
	    )
	);
	$this->_amysql->insert($this->tableName, $data);
	$stmt = $this->_amysql->lastStatement;
        $sql = "SELECT * FROM $this->tableName";
        $stmt = $this->_amysql->query($sql);
        $result = $stmt->fetchAllColumns(1);

        $expected = array (
            'id' => array ('3' => '1', 'blah' => '2'),
            'string' => array ('3' => '3', 'blah' => 'blah')
        );
        $this->assertEquals($expected, $result);
    }

    public function testProfiling() {
	$data = array (
	    array (
		'string' => 3
	    ),
	    array (
		'string' => 'blah',
	    )
	);
        $this->_amysql->useNewProfiler();
        $this->_amysql->insert($this->tableName, $data);
        $this->assertTrue(is_float( 
            $this->_amysql->lastStatement->queryTime
        ));
        $this->assertGreaterThan(0.0, $this->_amysql->lastStatement->queryTime);
        $this->assertSame($this->_amysql->totalTime,
            $this->_amysql->lastStatement->queryTime);
    }

    public function testProfilingClass() {
	$data = array (
	    array (
		'string' => 3
	    ),
	    array (
		'string' => 'blah',
	    )
	);
        $this->_amysql->useNewProfiler();
        $profiler = $this->_amysql->getProfiler();
        $this->_amysql->insert($this->tableName, $data);
        $this->assertInternalType('float', 
            $this->_amysql->lastStatement->queryTime
        );
        $this->assertGreaterThan(0.0, $profiler['totalTime']);
        $this->assertSame(
            $profiler['totalTime'],
            $this->_amysql->lastStatement->queryTime
        );
    }

    public function testProfilingClassQueriesData() {
	$data = array (
	    array (
		'string' => 3
	    ),
	    array (
		'string' => 'blah',
	    )
	);
        $this->_amysql->useNewProfiler();
        $profiler = $this->_amysql->getProfiler();
        $this->_amysql->insert($this->tableName, $data);
        $lastQueryData = $profiler['queriesData'][count($profiler['queriesData']) - 1];
        $this->assertInternalType('array', $lastQueryData);
        $keys = array('query', 'time', 'backtrace');
        $this->assertEquals($keys, array_keys($lastQueryData));
        $this->assertSame($profiler['totalTime'], $lastQueryData['time']);
        $this->assertSame($this->_amysql->lastStatement->query, $lastQueryData['query']);
    }

    public function testProfilingMethodsInAbstract()
    {
	$data = array (
	    array (
		'string' => 3
	    ),
	    array (
		'string' => 'blah',
	    )
	);
        $this->_amysql->useNewProfiler();
        $profiler = $this->_amysql->getProfiler();
        $this->_amysql->insert($this->tableName, $data);
        $queriesData = $this->_amysql->getQueriesData();
        $lastQueryData = $queriesData[count($profiler['queriesData']) - 1];
        $this->assertInternalType('array', $lastQueryData);
        $keys = array('query', 'time', 'backtrace');
        $this->assertEquals($keys, array_keys($lastQueryData));
    }

    public function testProfilingGetQueries()
    {
	$data = array (
	    array (
		'string' => 3
	    ),
	    array (
		'string' => 'blah',
	    )
	);
        $this->_amysql->useNewProfiler();
        $profiler = $this->_amysql->getProfiler();
        $stmt = $this->_amysql->ins($this->tableName, $data);
        $queryString = "INSERT INTO `abstracttest` (`string`) VALUES (3), ('blah')";

        $queriesInProfiler = $this->_amysql->getProfiler()->getQueries();
        $this->assertSame($queryString, $queriesInProfiler[0]);

        $queries = $this->_amysql->getQueries();
        $this->assertSame($queryString, $queries[0]);
    }

    public function testInsertId()
    {
	$data = array (
	    array (
		'string' => 3
	    ),
	);
        
        $stmt = $this->_amysql->ins($this->tableName, $data);
        $insertId = $stmt->insertId();
        $stmt = $this->_amysql->ins($this->tableName, $data);
        $insertId2 = $stmt->insertId();

        $this->assertEquals(1, $insertId);
        $this->assertEquals(2, $insertId2);
    }

}
?>
