<?php

/**
 * Tests all testable methods in GDAO\Model via a child class 
 * (\MockModelForTestingNonAbstractMethods).
 *
 * @author aadegbam
 */
class RecordsListTest extends \PHPUnit\Framework\TestCase
{
    protected $_mock_model_obj_with_memory_sqlite_connection;

    protected function setUp(): void {
        
        parent::setUp();

        ////////////////////////////////////////////////////////////////////////
        $this->_mock_model_obj_with_memory_sqlite_connection = 
            new \ModelForTestingNonAbstractMethods('', '', '', [], []);
        
        $pdo = new \PDO("sqlite::memory:");
        $this->_mock_model_obj_with_memory_sqlite_connection->setPDO($pdo);
        ////////////////////////////////////////////////////////////////////////
    }
    
////////////////////////////////////////////////////////////////////////////////
// Start Tests for \GDAO\Model::__construct(....)
////////////////////////////////////////////////////////////////////////////////

    public function testThatConstructorThrowsExceptionWhenANonRecordInterfaceObjectIsPresent() {
        
        $this->expectException(\InvalidArgumentException::class);
        
        $array_of_alleged_recs =
            [
                new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection),
                new stdClass()
            ];
        
        //line below should cause an exception to be thrown.
        new \GDAO\Model\RecordsList($array_of_alleged_recs);
    }
    
    public function testThatConstructorWorksAsExpected() {
        
        $record1 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record2 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record3 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record4 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record5 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record6 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        
        $array_of_records = [$record1, $record2, $record3, $record4, $record5, $record6];
        $record_list_obj = new \GDAO\Model\RecordsList($array_of_records);
        
        $msg = __METHOD__;
        $this->assertTrue(count($record_list_obj) === 6, $msg);
                
        $list_as_array = $record_list_obj->toArray();
        $this->assertTrue(in_array($record1, $list_as_array), $msg);
        $this->assertTrue(in_array($record2, $list_as_array), $msg);
        $this->assertTrue(in_array($record3, $list_as_array), $msg);
        $this->assertTrue(in_array($record4, $list_as_array), $msg);
        $this->assertTrue(in_array($record5, $list_as_array), $msg);
        $this->assertTrue(in_array($record6, $list_as_array), $msg);
    }

    public function testThatAddWorksAsExpected() {
        
        $record1 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record2 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record3 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record4 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record5 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record6 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        
        $array_of_records = [$record1, $record2];
        $record_list_obj = new \GDAO\Model\RecordsList($array_of_records);
        
        $msg = __METHOD__;
        $this->assertTrue(count($record_list_obj) === 2, $msg);
        
        $record_list_obj->add($record3);
        $list_as_array = $record_list_obj->toArray();
        $new_last_item = array_pop($list_as_array);
        $this->assertTrue($new_last_item === $record3, $msg); //new last element
        $this->assertTrue(count($record_list_obj) === 3, $msg); //total count has been increased by one
        
        $record_list_obj->add($record4);
        $list_as_array = $record_list_obj->toArray();
        $new_last_item = array_pop($list_as_array);
        $this->assertTrue($new_last_item === $record4, $msg); //new last element
        $this->assertTrue(count($record_list_obj) === 4, $msg); //total count has been increased by one
        
        $record_list_obj->add($record5);
        $list_as_array = $record_list_obj->toArray();
        $new_last_item = array_pop($list_as_array);
        $this->assertTrue($new_last_item === $record5, $msg); //new last element
        $this->assertTrue(count($record_list_obj) === 5, $msg); //total count has been increased by one
        
        $record_list_obj->add($record6);
        $list_as_array = $record_list_obj->toArray();
        $new_last_item = array_pop($list_as_array);
        $this->assertTrue($new_last_item === $record6, $msg); //new last element
        $this->assertTrue(count($record_list_obj) === 6, $msg); //total count has been increased by one
    }

    public function testThatAddRangeThrowsExceptionWhenANonRecordInterfaceObjectIsPresent() {
        
        $this->expectException(\InvalidArgumentException::class);
        
        $array_of_alleged_recs = [
            new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection),
        ];
        $record_list_obj = new \GDAO\Model\RecordsList($array_of_alleged_recs);
        
        //line below should cause an exception to be thrown.
        $record_list_obj->addRange([new stdClass()]);
    }
    
    public function testThatAddRangeWorksAsExpected() {
        
        $record1 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record2 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record3 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record4 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record5 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record6 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        
        $array_of_records = [$record1, $record2];
        $record_list_obj = new \GDAO\Model\RecordsList($array_of_records);
        
        $msg = __METHOD__;
        $this->assertTrue(count($record_list_obj) === 2, $msg);
        
        $record_list_obj->addRange([$record3, $record4, $record5, $record6]);
        $this->assertTrue(count($record_list_obj) === 6, $msg);
        
        $list_as_array = $record_list_obj->toArray();
         $this->assertTrue(in_array($record3, $list_as_array), $msg);
         $this->assertTrue(in_array($record4, $list_as_array), $msg);
         $this->assertTrue(in_array($record5, $list_as_array), $msg);
         $this->assertTrue(in_array($record6, $list_as_array), $msg);
    }
    
    public function testThatClearWorksAsExpected() {
        
        $array_of_records = [
            new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection),
        ];
        $record_list_obj = new \GDAO\Model\RecordsList($array_of_records);
        $record_list_obj->clear();

        $msg = __METHOD__;
        $this->assertTrue($record_list_obj->toArray() === [], $msg);
    }
    
    public function testThatRemoveAllWorksAsExpected() {
        
        $record1 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record2 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record3 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record4 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record5 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record6 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        
        $array_of_records = [
            $record1, $record2, $record3, $record4, $record5, $record6, $record1
        ];
        $record_list_obj = new \GDAO\Model\RecordsList($array_of_records);
        
        $msg = __METHOD__;
        $this->assertTrue(count($record_list_obj) === 7, $msg);
        
        $result = $record_list_obj->removeAll($record1);
        $this->assertTrue($result, $msg);
        $this->assertTrue(count($record_list_obj) === 5, $msg);
        
        $result = $record_list_obj->removeAll($record2);
        $this->assertTrue($result, $msg);
        $this->assertTrue(count($record_list_obj) === 4, $msg);
        
        $result = $record_list_obj->removeAll($record3);
        $this->assertTrue($result, $msg);
        $this->assertTrue(count($record_list_obj) === 3, $msg);
        
        $result = $record_list_obj->removeAll($record4);
        $this->assertTrue($result, $msg);
        $this->assertTrue(count($record_list_obj) === 2, $msg);
        
        $result = $record_list_obj->removeAll($record5);
        $this->assertTrue($result, $msg);
        $this->assertTrue(count($record_list_obj) === 1, $msg);
        
        $result = $record_list_obj->removeAll($record6);
        $this->assertTrue($result, $msg);
        $this->assertTrue(count($record_list_obj) === 0, $msg);
    }
    
    public function testThatRemoveFirstWorksAsExpected() {
        
        $record1 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record2 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record3 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record4 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record5 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record6 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        
        $array_of_records = [
            $record1, $record2, $record3, $record4, $record5, $record6, $record1
        ];
        $record_list_obj = new \GDAO\Model\RecordsList($array_of_records);
        
        $msg = __METHOD__;
        $this->assertTrue(count($record_list_obj) === 7, $msg);
        
        $result = $record_list_obj->removeFirst($record1);
        $list_as_array = $record_list_obj->toArray();
        $new_first_item = array_shift($list_as_array);
        
        $this->assertTrue($result, $msg);//test return value
        $this->assertTrue($new_first_item !== $record1, $msg); //former first element has been removed
        $this->assertTrue($new_first_item === $record2, $msg); //new first element
        $this->assertTrue(count($record_list_obj) === 6, $msg); //total count has been decreased by one
    }
    
    public function testThatRemoveLastWorksAsExpected() {
        
        $record1 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record2 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record3 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record4 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record5 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record6 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        
        $array_of_records = [
            $record1, $record2, $record3, $record4, $record5, $record6, $record1
        ];
        $record_list_obj = new \GDAO\Model\RecordsList($array_of_records);
        
        $msg = __METHOD__;
        $this->assertTrue(count($record_list_obj) === 7, $msg);
        
        $result = $record_list_obj->removeLast($record1);
        $list_as_array = $record_list_obj->toArray();
        $new_last_item = array_pop($list_as_array);
        
        $this->assertTrue($result, $msg);//test return value
        $this->assertTrue($new_last_item !== $record1, $msg); //former last element has been removed
        $this->assertTrue($new_last_item === $record6, $msg); //new last element
        $this->assertTrue(count($record_list_obj) === 6, $msg); //total count has been decreased by one
    }
    
    public function testThatToArrayWorksAsExpected() {
        
        $record1 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record2 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record3 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record4 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record5 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record6 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        
        $array_of_records = [
            $record1, $record2, $record3, $record4, $record5, $record6, $record1
        ];
        $record_list_obj = new \GDAO\Model\RecordsList($array_of_records);
        
        $msg = __METHOD__;
        $this->assertTrue($record_list_obj->toArray() === $array_of_records, $msg);
    }
    
    public function testThatCountWorksAsExpected() {
        
        $record1 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record2 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record3 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record4 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record5 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record6 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        
        $array_of_records = [
            $record1, $record2, $record3, $record4, $record5, $record6, $record1
        ];
        $record_list_obj = new \GDAO\Model\RecordsList($array_of_records);
        
        $msg = __METHOD__;
        $this->assertTrue($record_list_obj->count() === 7, $msg);
        
        $record_list_obj->add($record1);
        $record_list_obj->add($record2);
        $record_list_obj->add($record3);
        $record_list_obj->add($record4);
        $this->assertTrue($record_list_obj->count() === 11, $msg);
        
        $record_list_obj->addRange([$record5, $record6]);
        $this->assertTrue($record_list_obj->count() === 13, $msg);
    }

    public function testThatGetIteratorWorksAsExpected() {
        
        $record1 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record2 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record3 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record4 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record5 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        $record6 = new RecordForTestingRecordsList([], $this->_mock_model_obj_with_memory_sqlite_connection);
        
        $array_of_records = [
            $record1, $record2, $record3, $record4, $record5, $record6, $record1
        ];
        $record_list_obj = new \GDAO\Model\RecordsList($array_of_records);
        
        $msg = __METHOD__;
        $iterator = $record_list_obj->getIterator();
        $this->assertTrue( $iterator instanceof \ArrayIterator, $msg);
        $this->assertTrue($iterator->count() === 7, $msg);
        
        //iterate through items
        $iterator->rewind();
        $this->assertTrue( $iterator->current() === $record1, $msg); //1st element
        $iterator->next();
        
        $this->assertTrue( $iterator->current() === $record2, $msg); //2nd element
        $iterator->next();
        
        $this->assertTrue( $iterator->current() === $record3, $msg); //3rd element
        $iterator->next();
        
        $this->assertTrue( $iterator->current() === $record4, $msg); //4th element
        $iterator->next();
        
        $this->assertTrue( $iterator->current() === $record5, $msg); //5th element
        $iterator->next();
        
        $this->assertTrue( $iterator->current() === $record6, $msg); //6th element
        $iterator->next();
        
        $this->assertTrue( $iterator->current() === $record1, $msg); //7th and last element
    }
}
