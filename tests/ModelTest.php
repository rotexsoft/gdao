<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ModelTest
 *
 * @author aadegbam
 */
class ModelTest extends \PHPUnit_Framework_TestCase
{
    protected $_mock_model_obj;

    protected function setUp() {
        parent::setUp();

        $this->_mock_model_obj = 
            new \MockModelForTestingNonAbstractMethods('', '', '', [], []);
    }

    public function testValidateWhereOrHavingParamsArrayDoesntStartWithOrKey() {
        
        $expected = '\\GDAO\\ModelBadWhereParamSuppliedException';
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
        
        try{
            
            $this->_mock_model_obj->validateWhereOrHavingParamsArray($data['where']);
            
        } catch (\Exception $actual_exception) {
            
                $msg = 'Test 1: First key is OR or starts with OR#';
                $this->assertInstanceOf( $expected, $actual_exception, $msg);
                
                $substr = "The first key in the where param array cannot start"
                        . " with 'OR' or 'OR#'";              
                $this->assertContains( $substr, $actual_exception->getMessage());
        }
        

    }
}