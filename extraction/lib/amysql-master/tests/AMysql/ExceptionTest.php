<?php
class ExceptionTest extends AMysql_TestCase
{

    public function testGetParams()
    {
        $amysql = $this->_amysql;
        $data = array(
            'id' => 1,
            'string' => 'bla'
        );
        $amysql->insert($this->tableName, $data);
        try {
            $amysql->insert($this->tableName, $data);
            $this->fail('An exception should be thrown');
        } catch (AMysql_Exception $e) {
            $this->assertEquals(
                AMysql_Exception::CODE_DUPLICATE_ENTRY,
                $e->getCode()
            );
            $props = $e->getParams();
            $this->assertEquals(array('1', 'PRIMARY'), $props);
        }
    }

    public function testChildConstraintCode()
    {
        $this->setUpConstraintTables();
        try {
            $this->_amysql->insert('child', array(
                'id' => 1,
                'parent_id' => 1,
            ));
            $this->tearDownConstraintTables();
            $this->fail('An exception should be thrown');
        } catch (AMysql_Exception $e) {
            $this->assertEquals(
                AMysql_Exception::CODE_CHILD_FOREIGN_KEY_CONSTRAINT_FAILS,
                $e->getCode()
            );
            $expected = '`' . AMYSQL_TEST_DB . '`.`child`, CONSTRAINT `bla` FOREIGN KEY (`parent_id`) REFERENCES `parent` (`id`)';
            $this->assertEquals($expected, $e->getParams(0));
        }
        $this->tearDownConstraintTables();
    }

    public function testParentConstraintCode()
    {
        $this->setUpConstraintTables();
        try {
            $this->_amysql->insert('parent', array(
                'id' => 1,
                'bla' => 1,
            ));
            $this->_amysql->insert('child', array(
                'id' => 1,
                'parent_id' => 1,
            ));
            $this->_amysql->delete('parent', 'id = ?', 1);
            $this->tearDownConstraintTables();
            $this->fail('An exception should be thrown');
        } catch (AMysql_Exception $e) {
            $this->assertEquals(
                AMysql_Exception::CODE_PARENT_FOREIGN_KEY_CONSTRAINT_FAILS,
                $e->getCode()
            );
            $expected = '`' . AMYSQL_TEST_DB . '`.`child`, CONSTRAINT `bla` FOREIGN KEY (`parent_id`) REFERENCES `parent` (`id`)';
            $this->assertEquals($expected, $e->getParams(0));
        }
        $this->tearDownConstraintTables();
    }

    public function setUpConstraintTables()
    {
        $q = array();
        $q[] = <<<EOT
DROP TABLE IF EXISTS child
EOT;
        $q[] = <<<EOT
DROP TABLE IF EXISTS parent
EOT;
        $q[] = <<<EOT
--
-- Table structure for table `child`
--

CREATE TABLE IF NOT EXISTS `child` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
EOT;
    $q[] = <<<EOT
--
-- Table structure for table `parent`
--

CREATE TABLE IF NOT EXISTS `parent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bla` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
EOT;
        $q[] = <<<EOT
--
-- Constraints for dumped tables
--

--
-- Constraints for table `child`
--
ALTER TABLE `child`
  ADD CONSTRAINT `bla` FOREIGN KEY (`parent_id`) REFERENCES `parent` (`id`);
EOT;
        foreach ($q as $query) {
            $this->_amysql->query($query);
        }

    }

    public function tearDownConstraintTables()
    {
        $q = array();
        $q[] = <<<EOT
DROP TABLE IF EXISTS child
EOT;
        $q[] = <<<EOT
DROP TABLE IF EXISTS parent
EOT;
        foreach ($q as $query) {
            $this->_amysql->query($query);
        }
    }
}
?>
