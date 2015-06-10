<?php

/**
 * Description of ModelTest
 *
 * @author aadegbam
 */
class ModelTest extends \PHPUnit_Framework_TestCase
{
    protected $_mock_model_obj_with_no_db_connection;

    protected function setUp() {
        parent::setUp();

        $this->_mock_model_obj_with_no_db_connection = 
            new \MockModelForTestingNonAbstractMethods('', '', '', [], []);
    }

    public function testValidateWhereOrHavnParamsDoesntStartWithOrKey() {
        
        $data = [
            'where' =>
            [
                'OR' => [ 'col' => 'column_name_4', 'operator' => 'not-null'],
                [
                    [ 'col' => 'column_name_4', 'operator' => 'in', 'val' => [1, 2] ],
                    'OR#21' => [ 'col' => 'column_name_4', 'operator' => 'not-null'],
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
                [ 'col'=>[], 'operator'=>'>', 'val'=>58 ],
                'OR'=> [
                           [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                       ],
                [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
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
                 [ 'col'=>12, 'operator'=>'>', 'val'=>58 ],
                 'OR'=> [
                            ['col'=>'column_name_1', 'operator'=>'<', 'val'=>58],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
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
                 [ 'col'=>12.1, 'operator'=>'>', 'val'=>58 ],
                 'OR'=> [
                            ['col'=>'column_name_1', 'operator'=>'<', 'val'=>58],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
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
                 [ 'col'=>true, 'operator'=>'>', 'val'=>58 ],
                 'OR'=> [
                            ['col'=>'column_name_1', 'operator'=>'<', 'val'=>58],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
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
                 [ 'col'=>[12.1], 'operator'=>'>', 'val'=>58 ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
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
                 [ 'col'=>(new stdclass()), 'operator'=>'>', 'val'=>58 ],
                 'OR'=> [
                            ['col'=>'column_name_1', 'operator'=>'<', 'val'=>58],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
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
                 [ 'col'=> null, 'operator'=>'>', 'val'=>58 ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
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
                 [ 'col'=> tmpfile(), 'operator'=>'>', 'val'=>58 ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
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
                 [ 'col'=> function() { echo 'blah'; }, 'operator'=>'>', 'val'=>58 ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 3: array with a key named 'col' must only have a string value; Callback/Callable Supplied";
        $substr = "ERROR: Bad where param array having an entry with a key named"
                . " 'col' with a non-string value of ";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }
    
    public function testValidateWhereOrHavnParamsMustHaveOneOfTheExpectedValuesForKeysNamedOperator() {
        
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'<===>', 'val'=>58 ],
                 'OR'=> [
                            ['col'=>'column_name_1', 'operator'=>'<', 'val'=>58],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 4: array with a key named 'operator' with a value that is not amongst the expected operator values.";
        $substr = "ERROR: Bad where param array having an entry with a key named"
                . " 'operator' with a non-expected value of";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }
    
    public function testValidateWhereOrHavnParamsMustHaveOneOfTheExpectedValuesForKeysNamedVal() {
        
        $data = [
            'where' =>
              [ 'col'=> 'yay', 'operator'=>'>', 'val'=>tmpfile(), ]
        ];
        
        $msg = "Test 5: array with a key named 'val' must only have a numeric, "
             . "non-empty string, non-empty array or a boolean value. Resource supplied.";
        $substr = "Only numeric, non-empty string, boolean or non-empty array "
                . "values are allowed for an array entry with a key named 'val'.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ///////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [ 'col'=> 'yay', 'operator'=>'>', 'val'=>function() { echo 'blah'; }, ]
        ];
        
        $msg = "Test 5: array with a key named 'val' must only have a numeric, "
             . "non-empty string, non-empty array or a boolean value. Callback/Callable supplied.";
        $substr = "Only numeric, non-empty string, boolean or non-empty array "
                . "values are allowed for an array entry with a key named 'val'.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ///////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [ 'col'=> 'yay', 'operator'=>'>', 'val'=>(new stdclass()), ]
        ];
        
        $msg = "Test 5: array with a key named 'val' must only have a numeric, "
             . "non-empty string, non-empty array or a boolean value. Object supplied.";
        $substr = "Only numeric, non-empty string, boolean or non-empty array "
                . "values are allowed for an array entry with a key named 'val'.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ///////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [ 'col'=> 'yay', 'operator'=>'>', 'val'=>null, ]
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
                 [ 'col'=> 'yay', 'operator'=>'>', 'val'=>'yikes!' ],
                 'OR'=> [
                            ['col'=>'column_name_1', 'operator'=>'<', 'val'=>58],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>'roy' ],
                 [1,2,3,4],
              ]
        ];
        
        //Test 6: an array key named 'OR' or starts with 'OR#' or is numeric must have a non-empty array value.
        //	  An exception to this rule is if this key is numeric and is inside an array referenced by a
        //	  key named 'val' ('val'=>array(...) is allowed for 'operator'=>'in' & 'operator'=>'not-in')
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
                 [ 'col'=> 'yay', 'operator'=>'>', 'val'=> 'yay' ],
                 'OR'=> [
                            'OR'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
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
                 [ 'col'=> 'yay', 'operator'=>'>', 'val'=> 'yay' ],
                 'OR'=> [
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }
    
    public function testValidateWhereOrHavnParamsMustOnlyHaveTheKeysNamedColAndOperatorAndValInTheSameSubArray() {
        
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'>', 'val'=> 'yay', 'yab'=>'doo' ],
                 'OR'=> [
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 8: If any of the expected keys ('col', 'operator' or 'val')"
             . " is present, then no other type of key is allowed in the particular sub-array.";
        
        $substr = "Because one or more of these keys ('col', 'operator' or 'val') are present,";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = "no other type of key is allowed in the array in which they are present.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }
    
    public function testValidateWhereOrHavnParamsCanHaveKeysNamedColAndOperatorWithNoKeyNamedValInASubArray() {
        
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'>' ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 9: a sub-array like this ('col'=>'..', 'operator'=>'..') is"
             . " allowed only if operator has either the value 'is-null' or 'not-null'.";
        $substr = "A sub-array containing keys named 'col' and 'operator' without"
                . " a key named 'val' is valid if and only if the entry with a key"
                . " named 'operator' has either a value of 'is-null' or 'not-null'";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }
    
    public function testValidateWhereOrHavnParamsMustOnlyHaveKeysNamedColAndOperatorInASubArrayWhenOperatorHasExpectedValues() {
        
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'is-null', 'val'=>'yooo!' ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 10: For any sub-array containing an item with a key named 'operator'"
             . " with a value of either 'not-null' or 'is-null', there must not "
             . "be any item in that sub-array with a key named 'val', but there "
             . "must be a corresponding item with a key named 'col' with a string value.";
        $substr = "A sub-array containing a key named 'operator' with a value of"
                . " 'is-null' cannot also contain a key named 'val'. Please remove"
                . " the item with the key named 'val' from the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'not-null', 'val'=>'yooo!' ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'operator' with a value of"
                . " 'not-null' cannot also contain a key named 'val'. Please remove"
                . " the item with the key named 'val' from the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }
    
    public function testValidateWhereOrHavnParamsMustHaveKeysNamedColAndOperatorIfKeyNamedValIsInASubArray() {
        
        $data = [
            'where' =>
              [
                 [ 'val'=>'yooo!' ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 11: Missing keys ('col' & 'operator') when key named 'val' is present";
        $substr = "A sub-array containing key named 'val' without two other entries with keys named 'col' and 'operator'";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }
    
    public function testValidateWhereOrHavnParamsKeyNamedValHasOnlyNumericOrStringOrArrayValueIfOperatorIsInOrNotIn() {
        
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'not-in', 'val'=>null ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 12: For any sub-array containing an item with a key named "
             . "'operator' with a value of either 'in' or 'not-in', there must "
             . "be an item in that sub-array with a key named 'val' with a string"
             . " or array value.";
        $substr = "A sub-array containing a key named 'operator' with a value of 'not-in' contains an item with a key named 'val' whose value NULL is not numeric or a string or an array. Please supply a numeric or an array or a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'not-in', 'val'=>true ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of 'not-in' contains an item with a key named 'val' whose value true is not numeric or a string or an array. Please supply a numeric or an array or a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'not-in', 'val'=>(new stdclass()) ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of 'not-in' contains an item with a key named 'val' whose value stdClass::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not numeric or a string or an array. Please supply a numeric or an array or a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'not-in', 'val'=>tmpfile() ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of 'not-in' contains an item with a key named 'val' whose value NULL is not numeric or a string or an array. Please supply a numeric or an array or a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'not-in', 'val'=>function() { echo 'blah'; } ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of 'not-in' contains an item with a key named 'val' whose value Closure::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not numeric or a string or an array. Please supply a numeric or an array or a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'in', 'val'=>null ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of 'in' contains an item with a key named 'val' whose value NULL is not numeric or a string or an array. Please supply a numeric or an array or a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'in', 'val'=>true ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of 'in' contains an item with a key named 'val' whose value true is not numeric or a string or an array. Please supply a numeric or an array or a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'in', 'val'=>(new stdclass()) ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of 'in' contains an item with a key named 'val' whose value stdClass::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not numeric or a string or an array. Please supply a numeric or an array or a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'in', 'val'=>tmpfile() ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of 'in' contains an item with a key named 'val' whose value NULL is not numeric or a string or an array. Please supply a numeric or an array or a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'in', 'val'=>function() { echo 'blah'; } ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of 'in' contains an item with a key named 'val' whose value Closure::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not numeric or a string or an array. Please supply a numeric or an array or a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }
    
    public function testValidateWhereOrHavnParamsKeyNamedValHasOnlyStringValueIfOperatorIsLikeOrNotLike() {
        
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'like', 'val'=>911 ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 13: For any sub-array containing an item with a key named "
             . "'operator' with a value of either 'like' or 'not-like', there must"
             . " be an item in that sub-array with a key named 'val' with a string value.";
        $substr = "A sub-array containing a key named 'operator' with a value of 'like' contains an item with a key named 'val' whose value 911 is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'like', 'val'=>911.198 ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'operator' with a value of 'like' contains an item with a key named 'val' whose value 911.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = "is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'like', 'val'=>null ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'operator' with a value of 'like' contains an item with a key named 'val' whose value NULL is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'like', 'val'=>[911, null] ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'operator' with a value of 'like' contains an item with a key named 'val' whose value array (";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ") is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'like', 'val'=>true ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'operator' with a value of 'like' contains an item with a key named 'val' whose value true is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'like', 'val'=>(new stdclass()) ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'operator' with a value of 'like' contains an item with a key named 'val' whose value stdClass::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'like', 'val'=>tmpfile() ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'operator' with a value of 'like' contains an item with a key named 'val' whose value NULL is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'like', 'val'=>function() { echo 'blah'; } ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'operator' with a value of 'like' contains an item with a key named 'val' whose value Closure::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'not-like', 'val'=>911 ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'operator' with a value of 'not-like' contains an item with a key named 'val' whose value 911 is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'not-like', 'val'=>911.198 ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'operator' with a value of 'not-like' contains an item with a key named 'val' whose value 911.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = "is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'not-like', 'val'=>null ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'operator' with a value of 'not-like' contains an item with a key named 'val' whose value NULL is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'not-like', 'val'=>[911, null] ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'operator' with a value of 'not-like' contains an item with a key named 'val' whose value array (";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ") is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'not-like', 'val'=>true ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'operator' with a value of 'not-like' contains an item with a key named 'val' whose value true is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'not-like', 'val'=>(new stdclass()) ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'operator' with a value of 'not-like' contains an item with a key named 'val' whose value stdClass::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'not-like', 'val'=>tmpfile() ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'operator' with a value of 'not-like' contains an item with a key named 'val' whose value NULL is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=> 'yay', 'operator'=>'not-like', 'val'=>function() { echo 'blah'; } ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                            'OR#2'=>[ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'operator' with a value of 'not-like' contains an item with a key named 'val' whose value Closure::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not a string. Please supply a string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }
    
    public function testValidateWhereOrHavnParamsOperatorsEqLtGtLteGteNeqHaveCorrespondingNumericOrStringValues() {

        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'>', 'val'=>true ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 14 - For any sub-array containing an item with a key named "
             . "'operator' with any of the following values: '=', '>', '>=', '<', '<=' or '!=',"
             . " there must be another item in that sub-array with a key named 'val' with either"
             . " a numeric or string value.";
        $substr = "A sub-array containing a key named 'operator' with a value of '>' contains an item with a key named 'val' whose value true is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'>', 'val'=>[12.1] ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '>' contains an item with a key named 'val' whose value array (";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ") is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'>', 'val'=>(new stdclass()) ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '>' contains an item with a key named 'val' whose value stdClass::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'>', 'val'=>null ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '>' contains an item with a key named 'val' whose value NULL is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'>', 'val'=>tmpfile() ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '>' contains an item with a key named 'val' whose value NULL is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'>', 'val'=>function() { echo 'blah'; } ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '>' contains an item with a key named 'val' whose value Closure::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'=', 'val'=>true ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'operator' with a value of '=' contains an item with a key named 'val' whose value true is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'=', 'val'=>[12.1] ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '=' contains an item with a key named 'val' whose value array (";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ") is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'=', 'val'=>(new stdclass()) ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '=' contains an item with a key named 'val' whose value stdClass::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'=', 'val'=>null ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '=' contains an item with a key named 'val' whose value NULL is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'=', 'val'=>tmpfile() ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '=' contains an item with a key named 'val' whose value NULL is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'=', 'val'=>function() { echo 'blah'; } ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '=' contains an item with a key named 'val' whose value Closure::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'>=', 'val'=>true ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'operator' with a value of '>=' contains an item with a key named 'val' whose value true is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'>=', 'val'=>[12.1] ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '>=' contains an item with a key named 'val' whose value array (";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ") is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'>=', 'val'=>(new stdclass()) ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '>=' contains an item with a key named 'val' whose value stdClass::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'>=', 'val'=>null ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '>=' contains an item with a key named 'val' whose value NULL is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'>=', 'val'=>tmpfile() ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '>=' contains an item with a key named 'val' whose value NULL is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'>=', 'val'=>function() { echo 'blah'; } ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '>=' contains an item with a key named 'val' whose value Closure::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'<', 'val'=>true ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'<', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'operator' with a value of '<' contains an item with a key named 'val' whose value true is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'<', 'val'=>[12.1] ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'<', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '<' contains an item with a key named 'val' whose value array (";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ") is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'<', 'val'=>(new stdclass()) ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'<', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '<' contains an item with a key named 'val' whose value stdClass::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'<', 'val'=>null ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'<', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '<' contains an item with a key named 'val' whose value NULL is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'<', 'val'=>tmpfile() ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'<', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '<' contains an item with a key named 'val' whose value NULL is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'<', 'val'=>function() { echo 'blah'; } ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'<', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '<' contains an item with a key named 'val' whose value Closure::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'<=', 'val'=>true ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<=', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'<=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'operator' with a value of '<=' contains an item with a key named 'val' whose value true is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'<=', 'val'=>[12.1] ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<=', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'<=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '<=' contains an item with a key named 'val' whose value array (";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ") is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'<=', 'val'=>(new stdclass()) ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<=', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'<=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '<=' contains an item with a key named 'val' whose value stdClass::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'<=', 'val'=>null ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<=', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'<=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '<=' contains an item with a key named 'val' whose value NULL is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'<=', 'val'=>tmpfile() ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<=', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'<=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '<=' contains an item with a key named 'val' whose value NULL is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'<=', 'val'=>function() { echo 'blah'; } ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<=', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'<=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '<=' contains an item with a key named 'val' whose value Closure::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'!=', 'val'=>true ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'!=', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'!=', 'val'=>58 ],
              ]
        ];
        
        $substr = "A sub-array containing a key named 'operator' with a value of '!=' contains an item with a key named 'val' whose value true is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'!=', 'val'=>[12.1] ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'!=', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'!=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '!=' contains an item with a key named 'val' whose value array (";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ") is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'!=', 'val'=>(new stdclass()) ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'!=', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'!=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '!=' contains an item with a key named 'val' whose value stdClass::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'!=', 'val'=>null ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'!=', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'!=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '!=' contains an item with a key named 'val' whose value NULL is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'!=', 'val'=>tmpfile() ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'!=', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'!=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '!=' contains an item with a key named 'val' whose value NULL is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        ////////////////////////////////////////////////////////////////////////
        $data = [
            'where' =>
              [
                 [ 'col'=>'col_name', 'operator'=>'!=', 'val'=>function() { echo 'blah'; } ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'!=', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'!=', 'val'=>58 ],
              ]
        ];
        $substr = "A sub-array containing a key named 'operator' with a value of '!=' contains an item with a key named 'val' whose value Closure::__set_state(array(";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
        
        $substr = ")) is not a string or numeric. Please supply a numeric or string value for the item with the key named 'val' in the sub-array.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }
    
    private function _testParamsArray4Exception($data, $label, $expected_err_msg_substr) {
        
        $expected = '\\GDAO\\ModelBadWhereParamSuppliedException';
        
        try {
            $this->_mock_model_obj_with_no_db_connection->validateWhereOrHavingParamsArray($data);
            
        } catch (\Exception $actual_exception) {
            
                $this->assertInstanceOf( $expected, $actual_exception, $label);           
                $this->assertContains( $expected_err_msg_substr, $actual_exception->getMessage());
        }
    }
    
    public function testValidateWhereOrHavnParamsHasKeysInTheAcceptableRange() {
        
        $data = [
            'where' =>
              [
                 'yay'=>[ 'col'=> 'col_name', 'operator'=>'>', 'val'=>function() { echo 'blah'; } ],
                 'OR'=> [
                            [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
                        ],
                 [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
              ]
        ];
        
        $msg = "Test 15 - an array key must be numeric or any of ('col', 'operator', 'val', 'OR') or start with 'OR#'";
        $substr = "Allowed keys are as follows:";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);

        $substr = "Any of these keys ('col', 'operator', 'val' or 'OR') or the key must be a numeric key or a string that starts with 'OR#'.";
        $this->_testParamsArray4Exception($data['where'], $msg, $substr);
    }
}