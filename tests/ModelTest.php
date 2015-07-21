<?php

/**
 * Description of ModelTest
 *
 * @author aadegbam
 */
class ModelTest extends \PHPUnit_Framework_TestCase
{
    protected $_mock_model_obj_with_no_db_connection;
    protected $_mock_model_obj_with_memory_sqlite_connection;

    protected function setUp() {
        parent::setUp();

        $this->_mock_model_obj_with_no_db_connection = 
            new \MockModelForTestingNonAbstractMethods('', '', '', [], []);
        
        ////////////////////////////////////////////////////////////////////////
        $this->_mock_model_obj_with_memory_sqlite_connection = 
            new \MockModelForTestingNonAbstractMethods('', '', '', [], []);
        
        $pdo = new \PDO("sqlite::memory:");
        $this->_mock_model_obj_with_memory_sqlite_connection->setPDO($pdo);
        ////////////////////////////////////////////////////////////////////////
    }

////////////////////////////////////////////////////////////////////////////////
// Start Tests for \GDAO\Model::_validateWhereOrHavingParamsArray(array $array)
////////////////////////////////////////////////////////////////////////////////
    
    public function testValidateWhereOrHavnParamsDoesntStartWithOrKey() {
        
        $data = [
            'where' =>
            [
                'OR' => [ 'col' => 'column_name_4', 'op' => 'not-null'],
                [
                    [ 'col' => 'column_name_4', 'op' => 'in', 'val' => [1, 2] ],
                    'OR#21' => [ 'col' => 'column_name_4', 'op' => 'not-null'],
                ]
            ]
        ];
        
        $msg = 'Test 1: First key is OR or starts with OR#';
        $substr = "The first key in the where param array cannot start"
                . " with 'OR' or 'OR#'";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }

    public function testValidateWhereOrHavnParamsWithOneOrMoreEmptySubArrays() {
        
        $data = [
            'where' =>
            [
                [ 'col'=>[], 'op'=>'>', 'val'=>58 ],
                'OR'=> [
                           [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                       ],
                [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
            ]
        ];
        
        $msg = 'Test 2: Empty array as sub-array';
        $substr = "ERROR: Bad where param array with an empty sub-array"
                . " with a key named 'col' supplied to";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }

    public function testValidateWhereOrHavnParamsMustHaveStringValueForKeysNamedCol() {
        
        $data = [
            'where' =>
              [
                 [ 'col'=>12, 'op'=>'>', 'val'=>58 ],
                 'OR'=> [
                            ['col'=>'column_name_1', 'op'=>'<', 'val'=>58],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 3: array with a key named 'col' must only have a string value; Integer Supplied";
        $substr = "ERROR: Bad where param array having an entry with a key named"
                . " 'col' with a non-string value of ";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>12.1, 'op'=>'>', 'val'=>58 ],
                 'OR'=> [
                            ['col'=>'column_name_1', 'op'=>'<', 'val'=>58],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 3: array with a key named 'col' must only have a string value; Float Supplied";
        $substr = "ERROR: Bad where param array having an entry with a key named"
                . " 'col' with a non-string value of ";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>true, 'op'=>'>', 'val'=>58 ],
                 'OR'=> [
                            ['col'=>'column_name_1', 'op'=>'<', 'val'=>58],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 3: array with a key named 'col' must only have a string value; Boolean Supplied";
        $substr = "ERROR: Bad where param array having an entry with a key named"
                . " 'col' with a non-string value of ";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>[12.1], 'op'=>'>', 'val'=>58 ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 3: array with a key named 'col' must only have a string value; Array Supplied";
        $substr = "ERROR: Bad where param array having an entry with a key named"
                . " 'col' with a non-string value of ";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>(new stdclass()), 'op'=>'>', 'val'=>58 ],
                 'OR'=> [
                            ['col'=>'column_name_1', 'op'=>'<', 'val'=>58],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 3: array with a key named 'col' must only have a string value; Object Supplied";
        $substr = "ERROR: Bad where param array having an entry with a key named"
                . " 'col' with a non-string value of ";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data =  [
            'where' =>
              [
                 [ 'col'=> null, 'op'=>'>', 'val'=>58 ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 3: array with a key named 'col' must only have a string value; NULL Supplied";
        $substr = "ERROR: Bad where param array having an entry with a key named"
                . " 'col' with a non-string value of ";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> tmpfile(), 'op'=>'>', 'val'=>58 ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 3: array with a key named 'col' must only have a string value; Resource Handle Supplied";
        $substr = "ERROR: Bad where param array having an entry with a key named"
                . " 'col' with a non-string value of ";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> function() { echo 'blah'; }, 'op'=>'>', 'val'=>58 ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 3: array with a key named 'col' must only have a string value; Callback/Callable Supplied";
        $substr = "ERROR: Bad where param array having an entry with a key named"
                . " 'col' with a non-string value of ";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }
    
    public function testValidateWhereOrHavnParamsMustHaveOneOfTheExpectedValuesForKeysNamedOp() {
        
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'<===>', 'val'=>58 ],
                 'OR'=> [
                            ['col'=>'column_name_1', 'op'=>'<', 'val'=>58],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 4: array with a key named 'op' with a value that is not amongst the expected operator values.";
        $substr = "ERROR: Bad where param array having an entry with a key named"
                . " 'op' with a non-expected value of";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }
    
    public function testValidateWhereOrHavnParamsMustHaveOneOfTheExpectedValuesForKeysNamedVal() {
        
        $data = [
            'where' =>
              [ 'col'=> 'yay', 'op'=>'>', 'val'=>tmpfile(), ]
        ];
        
        $msg = "Test 5: array with a key named 'val' must only have a numeric, "
             . "non-empty string, non-empty array or a boolean value. Resource supplied.";
        $substr = "Only numeric, non-empty string, boolean or non-empty array "
                . "values are allowed for an array entry with a key named 'val'.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ///////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [ 'col'=> 'yay', 'op'=>'>', 'val'=>function() { echo 'blah'; }, ]
        ];
        
        $msg = "Test 5: array with a key named 'val' must only have a numeric, "
             . "non-empty string, non-empty array or a boolean value. Callback/Callable supplied.";
        $substr = "Only numeric, non-empty string, boolean or non-empty array "
                . "values are allowed for an array entry with a key named 'val'.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ///////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [ 'col'=> 'yay', 'op'=>'>', 'val'=>(new stdclass()), ]
        ];
        
        $msg = "Test 5: array with a key named 'val' must only have a numeric, "
             . "non-empty string, non-empty array or a boolean value. Object supplied.";
        $substr = "Only numeric, non-empty string, boolean or non-empty array "
                . "values are allowed for an array entry with a key named 'val'.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ///////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [ 'col'=> 'yay', 'op'=>'>', 'val'=>null, ]
        ];
        
        $msg = "Test 5: array with a key named 'val' must only have a numeric, "
             . "non-empty string, non-empty array or a boolean value. NULL supplied.";
        $substr = "Only numeric, non-empty string, boolean or non-empty array "
                . "values are allowed for an array entry with a key named 'val'.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }
    
    public function testValidateWhereOrHavnParamsMustHaveAnArrayValueForNumericKeysOrKeysStartingWithOr() {
        
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'>', 'val'=>'yikes!' ],
                 'OR'=> [
                            ['col'=>'column_name_1', 'op'=>'<', 'val'=>58],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>'roy' ],
                 [1,2,3,4],
              ]
        ];
        
        //Test 6: an array key named 'OR' or starts with 'OR#' or is numeric must have a non-empty array value.
        //	  An exception to this rule is if this key is numeric and is inside an array referenced by a
        //	  key named 'val' ('val'=>array(...) is allowed for 'op'=>'in' & 'op'=>'not-in')
        $msg = "Test 6: Any array entry with a numeric key or a key named 'OR' "
             . "or a key that starts with 'OR#' must have a value that is an array.";
        $substr = "Any array entry with a numeric key or a key named 'OR' or a "
                . "key that starts with 'OR#' must have a value that is an array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }
    
    public function testValidateWhereOrHavnParamsMustHaveANonEmptyArrayValueForNumericKeysOrKeysStartingWithOr() {
        
        $data =  [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'>', 'val'=> 'yay' ],
                 'OR'=> [
                            'OR'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 7: an array key named 'OR' or starts with 'OR#' or is"
             . " numeric must have a non-empty array value. The first item in"
             . " the non-empty array must not have an array key named 'OR' or"
             . " starts with 'OR#'.";
        $substr = "The first key in any of the sub-arrays in the array passed to "
                .  get_class($this->_mock_model_obj_with_no_db_connection) 
                ."::_validateWhereOrHavingParamsArray(...) cannot start with 'OR' or 'OR#'.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        //////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'>', 'val'=> 'yay' ],
                 'OR'=> [
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }
    
    public function testValidateWhereOrHavnParamsMustOnlyHaveTheKeysNamedColAndOpAndValInTheSameSubArray() {
        
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'>', 'val'=> 'yay', 'yab'=>'doo' ],
                 'OR'=> [
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 8: If any of the expected keys ('col', 'op' or 'val')"
             . " is present, then no other type of key is allowed in the particular sub-array.";
        
        $substr = "Because one or more of these keys ('col', 'op' or 'val') are present,";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = "no other type of key is allowed in the array in which they are present.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }
    
    public function testValidateWhereOrHavnParamsCanHaveKeysNamedColAndOpWithNoKeyNamedValInASubArray() {
        
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'>' ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 9: a sub-array like this ('col'=>'..', 'op'=>'..') is"
             . " allowed only if operator has either the value 'is-null' or 'not-null'.";
        $substr = "A sub-array containing keys named 'col' and 'op' without"
                . " a key named 'val' is valid if and only if the entry with a key"
                . " named 'op' has either a value of 'is-null' or 'not-null'";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }
    
    public function testValidateWhereOrHavnParamsMustOnlyHaveKeysNamedColAndOpInASubArrayWhenOpHasExpectedValues() {
        
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'is-null', 'val'=>'yooo!' ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 10: For any sub-array containing an item with a key named 'op'"
             . " with a value of either 'not-null' or 'is-null', there must not "
             . "be any item in that sub-array with a key named 'val', but there "
             . "must be a corresponding item with a key named 'col' with a string value.";
        $substr = "A sub-array containing a key named 'op' with a value of"
                . " 'is-null' cannot also contain a key named 'val'. Please remove"
                . " the item with the key named 'val' from the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'not-null', 'val'=>'yooo!' ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'op' with a value of"
                . " 'not-null' cannot also contain a key named 'val'. Please remove"
                . " the item with the key named 'val' from the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }
    
    public function testValidateWhereOrHavnParamsMustHaveKeysNamedColAndOpIfKeyNamedValIsInASubArray() {
        
        $data = [
            'where' =>
              [
                 [ 'val'=>'yooo!' ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 11: Missing keys ('col' & 'op') when key named 'val' is present";
        $substr = "A sub-array containing key named 'val' without two other entries with keys named 'col' and 'op'";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }
    
    public function testValidateWhereOrHavnParamsKeyNamedValHasOnlyNumericOrStringOrArrayValueIfOpIsInOrNotIn() {
        
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'not-in', 'val'=>null ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 12: For any sub-array containing an item with a key named "
             . "'op' with a value of either 'in' or 'not-in', there must "
             . "be an item in that sub-array with a key named 'val' with a string"
             . " or array value.";
        $substr = "A sub-array containing a key named 'op' with a value of 'not-in' contains an item with a key named 'val' whose value NULL is not numeric or a string or an array. Please supply a numeric or an array or a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'not-in', 'val'=>true ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of 'not-in' contains an item with a key named 'val' whose value true is not numeric or a string or an array. Please supply a numeric or an array or a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'not-in', 'val'=>(new stdclass()) ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of 'not-in' contains an item with a key named 'val' whose value stdClass::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not numeric or a string or an array. Please supply a numeric or an array or a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'not-in', 'val'=>tmpfile() ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of 'not-in' contains an item with a key named 'val' whose value ";
        $substr2 = " is not numeric or a string or an array. Please supply a numeric or an array or a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        $this->_testParamsArray4Exception($data['where'], $msg, $substr2);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'not-in', 'val'=>function() { echo 'blah'; } ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $closure_label = 'Closure::__set_state(array(';
                 
        if($this->_isHhvm()){
            
            $closure_label = '\'Closure$ModelTest::';
        }
                 
        $substr = "A sub-array containing a key named 'op' with a value of 'not-in' contains an item with a key named 'val' whose value $closure_label";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = " is not numeric or a string or an array. Please supply a numeric or an array or a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'in', 'val'=>null ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of 'in' contains an item with a key named 'val' whose value NULL is not numeric or a string or an array. Please supply a numeric or an array or a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'in', 'val'=>true ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of 'in' contains an item with a key named 'val' whose value true is not numeric or a string or an array. Please supply a numeric or an array or a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'in', 'val'=>(new stdclass()) ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of 'in' contains an item with a key named 'val' whose value stdClass::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not numeric or a string or an array. Please supply a numeric or an array or a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'in', 'val'=>tmpfile() ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of 'in' contains an item with a key named 'val' whose value";
        $substr2 = "is not numeric or a string or an array. Please supply a numeric or an array or a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        $this->_testParamsArray4Exception($data['where'], $msg, $substr2);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'in', 'val'=>function() { echo 'blah'; } ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
                 
        $closure_label = 'Closure::__set_state(array(';
                 
        if($this->_isHhvm()){
            
            $closure_label = '\'Closure$ModelTest::';
        }
                 
        $substr = "A sub-array containing a key named 'op' with a value of 'in' contains an item with a key named 'val' whose value $closure_label";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = " is not numeric or a string or an array. Please supply a numeric or an array or a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }
    
    public function testValidateWhereOrHavnParamsKeyNamedValHasOnlyStringValueIfOpIsLikeOrNotLike() {
        
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'like', 'val'=>911 ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 13: For any sub-array containing an item with a key named "
             . "'op' with a value of either 'like' or 'not-like', there must"
             . " be an item in that sub-array with a key named 'val' with a string value.";
        $substr = "A sub-array containing a key named 'op' with a value of 'like' contains an item with a key named 'val' whose value 911 is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'like', 'val'=>911.198 ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'op' with a value of 'like' contains an item with a key named 'val' whose value 911.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = "is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'like', 'val'=>null ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'op' with a value of 'like' contains an item with a key named 'val' whose value NULL is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'like', 'val'=>[911, null] ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'op' with a value of 'like' contains an item with a key named 'val' whose value array (";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ") is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'like', 'val'=>true ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'op' with a value of 'like' contains an item with a key named 'val' whose value true is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'like', 'val'=>(new stdclass()) ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'op' with a value of 'like' contains an item with a key named 'val' whose value stdClass::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'like', 'val'=>tmpfile() ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'op' with a value of 'like' contains an item with a key named 'val' whose value";
        $substr2 = " is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        $this->_testParamsArray4Exception($data['where'], $msg, $substr2);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'like', 'val'=>function() { echo 'blah'; } ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $closure_label = 'Closure::__set_state(array(';
                 
        if($this->_isHhvm()){
            
            $closure_label = '\'Closure$ModelTest::';
        }
                 
        $substr = "A sub-array containing a key named 'op' with a value of 'like' contains an item with a key named 'val' whose value $closure_label";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = " is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'not-like', 'val'=>911 ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'op' with a value of 'not-like' contains an item with a key named 'val' whose value 911 is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'not-like', 'val'=>911.198 ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'op' with a value of 'not-like' contains an item with a key named 'val' whose value 911.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = "is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'not-like', 'val'=>null ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'op' with a value of 'not-like' contains an item with a key named 'val' whose value NULL is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'not-like', 'val'=>[911, null] ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'op' with a value of 'not-like' contains an item with a key named 'val' whose value array (";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ") is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'not-like', 'val'=>true ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'op' with a value of 'not-like' contains an item with a key named 'val' whose value true is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'not-like', 'val'=>(new stdclass()) ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'op' with a value of 'not-like' contains an item with a key named 'val' whose value stdClass::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'not-like', 'val'=>tmpfile() ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'op' with a value of 'not-like' contains an item with a key named 'val' whose value";
        $substr2 = " is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        $this->_testParamsArray4Exception($data['where'], $msg, $substr2);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'op'=>'not-like', 'val'=>function() { echo 'blah'; } ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $closure_label = 'Closure::__set_state(array(';
                 
        if($this->_isHhvm()){
            
            $closure_label = '\'Closure$ModelTest::';
        }   
                 
        $substr = "A sub-array containing a key named 'op' with a value of 'not-like' contains an item with a key named 'val' whose value $closure_label";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = " is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }
    
    public function testValidateWhereOrHavnParamsOpsEqLtGtLteGteNeqHaveCorrespondingNumericOrStringValues() {

        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'>', 'val'=>true ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 14 - For any sub-array containing an item with a key named "
             . "'op' with any of the following values: '=', '>', '>=', '<', '<=' or '!=',"
             . " there must be another item in that sub-array with a key named 'val' with either"
             . " a numeric or string value.";
        $substr = "A sub-array containing a key named 'op' with a value of '>' contains an item with a key named 'val' whose value true is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'>', 'val'=>[12.1] ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of '>' contains an item with a key named 'val' whose value array (";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ") is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'>', 'val'=>(new stdclass()) ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of '>' contains an item with a key named 'val' whose value stdClass::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'>', 'val'=>null ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of '>' contains an item with a key named 'val' whose value NULL is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'>', 'val'=>tmpfile() ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of '>' contains an item with a key named 'val' whose value";
        $substr2 = " is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        $this->_testParamsArray4Exception($data['where'], $msg, $substr2);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'>', 'val'=>function() { echo 'blah'; } ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
                 
        $closure_label = 'Closure::__set_state(array(';
                 
        if($this->_isHhvm()){
            
            $closure_label = '\'Closure$ModelTest::';
        }
                 
        $substr = "A sub-array containing a key named 'op' with a value of '>' contains an item with a key named 'val' whose value $closure_label";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = " is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'=', 'val'=>true ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'op' with a value of '=' contains an item with a key named 'val' whose value true is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'=', 'val'=>[12.1] ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of '=' contains an item with a key named 'val' whose value array (";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ") is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'=', 'val'=>(new stdclass()) ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of '=' contains an item with a key named 'val' whose value stdClass::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'=', 'val'=>null ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of '=' contains an item with a key named 'val' whose value NULL is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'=', 'val'=>tmpfile() ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of '=' contains an item with a key named 'val' whose value";
        $substr2 = " is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        $this->_testParamsArray4Exception($data['where'], $msg, $substr2);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'=', 'val'=>function() { echo 'blah'; } ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
                 
        $closure_label = 'Closure::__set_state(array(';
                 
        if($this->_isHhvm()){
            
            $closure_label = '\'Closure$ModelTest::';
        }
                 
        $substr = "A sub-array containing a key named 'op' with a value of '=' contains an item with a key named 'val' whose value $closure_label";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = " is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'>=', 'val'=>true ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'op' with a value of '>=' contains an item with a key named 'val' whose value true is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'>=', 'val'=>[12.1] ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of '>=' contains an item with a key named 'val' whose value array (";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ") is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'>=', 'val'=>(new stdclass()) ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of '>=' contains an item with a key named 'val' whose value stdClass::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'>=', 'val'=>null ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of '>=' contains an item with a key named 'val' whose value NULL is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'>=', 'val'=>tmpfile() ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of '>=' contains an item with a key named 'val' whose value";
        $substr2 = " is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        $this->_testParamsArray4Exception($data['where'], $msg, $substr2);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'>=', 'val'=>function() { echo 'blah'; } ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
                 
        $closure_label = 'Closure::__set_state(array(';
                 
        if($this->_isHhvm()){
            
            $closure_label = '\'Closure$ModelTest::';
        }
                 
        $substr = "A sub-array containing a key named 'op' with a value of '>=' contains an item with a key named 'val' whose value $closure_label";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = " is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'<', 'val'=>true ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'<', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'op' with a value of '<' contains an item with a key named 'val' whose value true is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'<', 'val'=>[12.1] ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'<', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of '<' contains an item with a key named 'val' whose value array (";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ") is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'<', 'val'=>(new stdclass()) ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'<', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of '<' contains an item with a key named 'val' whose value stdClass::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'<', 'val'=>null ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'<', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of '<' contains an item with a key named 'val' whose value NULL is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'<', 'val'=>tmpfile() ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'<', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of '<' contains an item with a key named 'val' whose value";
        $substr2 = "is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        $this->_testParamsArray4Exception($data['where'], $msg, $substr2);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'<', 'val'=>function() { echo 'blah'; } ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'<', 'val'=>58 ],
              ]
        ];
                 
        $closure_label = 'Closure::__set_state(array(';
                 
        if($this->_isHhvm()){
            
            $closure_label = '\'Closure$ModelTest::';
        }
                 
        $substr = "A sub-array containing a key named 'op' with a value of '<' contains an item with a key named 'val' whose value $closure_label";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = " is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'<=', 'val'=>true ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<=', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'<=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'op' with a value of '<=' contains an item with a key named 'val' whose value true is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'<=', 'val'=>[12.1] ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<=', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'<=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of '<=' contains an item with a key named 'val' whose value array (";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ") is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'<=', 'val'=>(new stdclass()) ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<=', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'<=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of '<=' contains an item with a key named 'val' whose value stdClass::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'<=', 'val'=>null ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<=', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'<=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of '<=' contains an item with a key named 'val' whose value NULL is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'<=', 'val'=>tmpfile() ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<=', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'<=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of '<=' contains an item with a key named 'val' whose value ";
        $substr2 = " is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        $this->_testParamsArray4Exception($data['where'], $msg, $substr2);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'<=', 'val'=>function() { echo 'blah'; } ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<=', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'<=', 'val'=>58 ],
              ]
        ];
                 
        $closure_label = 'Closure::__set_state(array(';
                 
        if($this->_isHhvm()){
            
            $closure_label = '\'Closure$ModelTest::';
        }
                 
        $substr = "A sub-array containing a key named 'op' with a value of '<=' contains an item with a key named 'val' whose value $closure_label";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = " is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'!=', 'val'=>true ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'!=', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'!=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'op' with a value of '!=' contains an item with a key named 'val' whose value true is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'!=', 'val'=>[12.1] ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'!=', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'!=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of '!=' contains an item with a key named 'val' whose value array (";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ") is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'!=', 'val'=>(new stdclass()) ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'!=', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'!=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of '!=' contains an item with a key named 'val' whose value stdClass::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'!=', 'val'=>null ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'!=', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'!=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of '!=' contains an item with a key named 'val' whose value NULL is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'!=', 'val'=>tmpfile() ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'!=', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'!=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'op' with a value of '!=' contains an item with a key named 'val' whose value ";
        $substr2 = " is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        $this->_testParamsArray4Exception($data['where'], $msg, $substr2);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'op'=>'!=', 'val'=>function() { echo 'blah'; } ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'!=', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'!=', 'val'=>58 ],
              ]
        ];
                 
        $closure_label = 'Closure::__set_state(array(';
                 
        if($this->_isHhvm()){
            
            $closure_label = '\'Closure$ModelTest::';
        }
                 
        $substr = "A sub-array containing a key named 'op' with a value of '!=' contains an item with a key named 'val' whose value $closure_label";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = " is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }
    
    
    public function testValidateWhereOrHavnParamsHasKeysInTheAcceptableRange() {
        
        $data = [
            'where' =>
              [
                 'yay'=>[ 'col'=> 'col_name', 'op'=>'>', 'val'=>function() { echo 'blah'; } ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'op'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'op'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 15 - an array key must be numeric or any of ('col', 'op', 'val', 'OR') or start with 'OR#'";
        $substr = "Allowed keys are as follows:";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        $substr = "Any of these keys ('col', 'op', 'val' or 'OR') or the key must be a numeric key or a string that starts with 'OR#'.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }
    
    private function _testParamsArray4Exception($data, $label, $expected_err_msg_substr) {

        $expected = '\\GDAO\\ModelBadWhereOrHavingParamSuppliedException';
        
        try {
            $this->_mock_model_obj_with_no_db_connection->validateWhereOrHavingParamsArray($data);
            
        } catch (\Exception $actual_exception) {
            
                $this->assertInstanceOf( $expected, $actual_exception, $label);           
                $this->assertContains( $expected_err_msg_substr, $actual_exception->getMessage());
        }
    }
    
////////////////////////////////////////////////////////////////////////////////
// End of Tests for \GDAO\Model::_validateWhereOrHavingParamsArray(array $array)
////////////////////////////////////////////////////////////////////////////////
    
    public function testToEnsureThatGetWhereOrHavingClauseWithParamsWorksAsExpected() {
        
        $data = [
            'where' => 
                [
                    0 => [ 'col' => 'col_1', 'op' => '<', 'val' => 58],
                    1 => [ 'col' => 'col_2', 'op' => '<', 'val' => 68],
                    [
                        0 => [ 'col' => 'col_11', 'op' => '>', 'val' => 581],
                        1 => [ 'col' => 'col_21', 'op' => '>', 'val' => 681],
                        'OR#3' => [
                            0 => [ 'col' => 'col_12', 'op' => '<', 'val' => 582],
                            1 => [ 'col' => 'col_22', 'op' => '<', 'val' => 682]
                        ],
                        2 => [ 'col' => 'col_31', 'op' => '>=', 'val' => 583],
                        'OR#4' => [
                            0 => [ 'col' => 'col_4', 'op' => '=', 'val' => 584],
                            1 => [ 'col' => 'col_5', 'op' => '=', 'val' => 684],
                        ]
                    ],
                    3 => [ 'col' => 'column_name_44', 'op' => '<', 'val' => 777],
                    4 => [ 'col' => 'column_name_55', 'op' => 'is-null'],
                ]
        ];
        
        $mock_model_obj = $this->_mock_model_obj_with_memory_sqlite_connection;
                
        $result = 
            $mock_model_obj->getWhereOrHavingClauseWithParams($data['where']);
        
        $expected_sql = <<<EOT
(
	col_1 > :_1_ 
	AND
	col_2 > :_2_ 
	AND
	(
		col_11 > :_3_ 
		AND
		col_21 > :_4_ 
		OR
		(
			col_12 > :_5_ 
			AND
			col_22 > :_6_ 
		)
		AND
		col_31 >= :_7_ 
		OR
		(
			col_4 = :_8_ 
			AND
			col_5 = :_9_ 
		)
	)
	AND
	column_name_44 > :_10_ 
	AND
	column_name_55 IS NULL
)
EOT;

        $this->assertContains($expected_sql, $result[0]);
        
        $expected_params = [
            '_1_' => 58, '_2_' => 68, '_3_' => 581, '_4_' => 681, '_5_' => 582,
            '_6_' => 682, '_7_' => 583, '_8_' => 584, '_9_' => 684, '_10_' => 777
        ];

        $this->assertEquals($expected_params, $result[1]);
    }
    
    protected function _isHhvm() {
        return defined('HHVM_VERSION');
    }
}
