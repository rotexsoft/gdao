<?php
declare(strict_types=1);

/**
 * Tests all testable methods in GDAO\Model via a child class 
 * (\MockModelForTestingNonAbstractMethods).
 *
 * @author aadegbam
 */
class ModelTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void {

        parent::setUp();
    }
    
////////////////////////////////////////////////////////////////////////////////
// Start Tests for \GDAO\Model::__construct(....)
////////////////////////////////////////////////////////////////////////////////
    
    public function testThatConstructorSetsFirstFourParamsCorrectly(): void {
        
        $model = new \ModelForTestingNonAbstractMethods(
                    'testdsn',
                    'testusername',
                    'testpasswd',
                    [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'],
                    'component_id', 
                    'components'
                );
                
        $msg = __METHOD__;
        
        $model_obj_as_array = $model->toArray();
        $this->assertTrue($model_obj_as_array['primary_col'] === 'component_id', $msg);
        $this->assertTrue($model_obj_as_array['table_name'] === 'components', $msg);
        $this->assertTrue($model_obj_as_array['dsn'] === 'testdsn', $msg);
        $this->assertTrue($model_obj_as_array['username'] === 'testusername', $msg);
        $this->assertTrue($model_obj_as_array['passwd'] === 'testpasswd', $msg);
        $this->assertTrue($model_obj_as_array['pdo_driver_opts'] === [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'], $msg);
    }

    public function testThatConstructorThrowsExceptionForEmptyPrimaryColName(): void {
        
        $this->expectException(\GDAO\ModelPrimaryColNameNotSetDuringConstructionException::class);
        
        new \ModelForTestingNonAbstractMethods(
                    'testdsn',
                    'testusername',
                    'testpasswd',
                    [],
                    '', 
                    'components'
                );
    }

    public function testThatConstructorThrowsExceptionForEmptyTableName(): void {
        
        $this->expectException(\GDAO\ModelTableNameNotSetDuringConstructionException::class);
        
        new \ModelForTestingNonAbstractMethods(
                    'testdsn',
                    'testusername',
                    'testpasswd',
                    [],
                    'component_id', 
                    ''
                );
    }
    
    public function testThatConstructorSetsModelPropertiesCorrectlyViaTheExtraoptsArray(): void {

        $rel = [
            'a_relation_name'=> [
                'relation_type' => \GDAO\Model::RELATION_TYPE_BELONGS_TO,
                'foreign_key_col_in_my_table' => 'output_id',
                'foreign_table' => 'project_outputs',
                'foreign_key_col_in_foreign_table' => 'output_id',
                'primary_key_col_in_foreign_table' => 'output_id',
                'foreign_models_class_name' => '\\VendorName\\PackageName\\ModelClassName',
                'foreign_models_collection_class_name' => '\\VendorName\\PackageName\\ModelClassName\\Collection',
                'foreign_models_record_class_name' => '\StdClass',
                'sql_query_modifier' => function(object $selectObj): object {
                    // Do some stuff to / with $selectObj
                    return $selectObj;
                },
            ],
        ];
        
        $model = 
            (
                new \ModelForTestingNonAbstractMethods(
                    '',
                    '',
                    '',
                    [],
                    'component_id',
                    'components'
                )
            )->setRelations($rel)
             ->setCollectionClassName('TestCollectionClassName')
             ->setRecordClassName('TestRecordClassName')
             ->setTableCols(['col1', 'col2'])
             ->setCreatedTimestampColumnName('test_c_col_name')
             ->setUpdatedTimestampColumnName('test_u_col_name')
             ->setDsn('testdsn')
             ->setUsername('testusername')
             ->setPasswd('testpasswd')
             ->setPdoDriverOpts([PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);
        
        $msg = __METHOD__;
        
        $model_obj_as_array = $model->toArray();
        
        $this->assertTrue($model_obj_as_array['primary_col'] === 'component_id', $msg);
        $this->assertTrue($model->getPrimaryCol() === 'component_id', $msg);
        
        $this->assertTrue($model_obj_as_array['table_name'] === 'components', $msg);
        $this->assertTrue($model->getTableName() === 'components', $msg);
        
        $this->assertTrue($model_obj_as_array['relations'] === $rel, $msg);
        $this->assertTrue($model->getRelations() === $rel, $msg);
        
        $this->assertTrue($model_obj_as_array['collection_class_name'] === 'TestCollectionClassName', $msg);
        $this->assertTrue($model->getCollectionClassName() === 'TestCollectionClassName', $msg);
        
        $this->assertTrue($model_obj_as_array['record_class_name'] === 'TestRecordClassName', $msg);
        $this->assertTrue($model->getRecordClassName() === 'TestRecordClassName', $msg);
        
        $this->assertTrue($model_obj_as_array['table_cols'] === ['col1', 'col2'], $msg);
        $this->assertTrue($model->getTableCols() === ['col1', 'col2'], $msg);
        
        $this->assertTrue($model_obj_as_array['created_timestamp_column_name'] === 'test_c_col_name', $msg);
        $this->assertTrue($model->getCreatedTimestampColumnName() === 'test_c_col_name', $msg);
        
        $this->assertTrue($model_obj_as_array['updated_timestamp_column_name'] === 'test_u_col_name', $msg);
        $this->assertTrue($model->getUpdatedTimestampColumnName() === 'test_u_col_name', $msg);
        
        $this->assertTrue($model_obj_as_array['dsn'] === 'testdsn', $msg);
        $this->assertTrue($model->getDsn() === 'testdsn', $msg);
        
        $this->assertTrue($model_obj_as_array['username'] === 'testusername', $msg);
        $this->assertTrue($model->getUsername() === 'testusername', $msg);
        
        $this->assertTrue($model_obj_as_array['passwd'] === 'testpasswd', $msg);
        $this->assertTrue($model->getPasswd() === 'testpasswd', $msg);
        
        $this->assertTrue($model_obj_as_array['pdo_driver_opts'] === [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'], $msg);
        $this->assertTrue($model->getPdoDriverOpts() === [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'], $msg);
        
        $model->setPrimaryCol('new_pk_col')
              ->setTableName('new_table_name');
        
        $model_obj_as_array2 = $model->toArray(); // repopulate after calling setters
        $this->assertTrue($model_obj_as_array2['primary_col'] === 'new_pk_col', $msg);
        $this->assertTrue($model_obj_as_array2['table_name'] === 'new_table_name', $msg);
        $this->assertTrue($model->getPrimaryCol() === 'new_pk_col', $msg);
        $this->assertTrue($model->getTableName() === 'new_table_name', $msg);
    }
    
////////////////////////////////////////////////////////////////////////////////
// End of Tests for \GDAO\Model::__construct(....)
////////////////////////////////////////////////////////////////////////////////

    public function testThat__toStringWorksAsExpected(): void {
        
        $model = new \ModelForTestingNonAbstractMethods(
            'testdsn',
            'testusername',
            'testpasswd',
            [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'],
            'component_id', 
            'components'
        );
        
        $modelAsArray = $model->toArray();
        // $model->__toString() is just a string representation of $model->toArray()
        $modelToStringAsArray = eval(' return ' . $model->__toString() . ';');
        
        self::assertEquals($modelAsArray, $modelToStringAsArray);
    }
    
    public function testThatToArrayWorksAsExpected(): void {
        
        $model = new \ModelForTestingNonAbstractMethods(
            'testdsn',
            'testusername',
            'testpasswd',
            [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'],
            'component_id', 
            'components'
        );
        
        $expected_array = [
            'primary_col' => 'component_id',
            'table_name' => 'components',
            'table_cols' => [],
            'collection_class_name' => null,
            'record_class_name' => null,
            'created_timestamp_column_name' => null,
            'updated_timestamp_column_name' => null,
            'relations' => [],
            'dsn' => 'testdsn',
            'username' => 'testusername',
            'passwd' => 'testpasswd',
            'pdo_driver_opts' => [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']
        ];

        $msg = __METHOD__;
        $this->assertTrue( $expected_array === $model->toArray(), $msg);
    }

    public function testThatCreateNewCollectionThrowsException(): void {
        
        $this->expectException(\GDAO\ModelMustImplementMethodException::class);
        
        $model = new \ModelForTestingNonAbstractMethods(
                    'testdsn',
                    'testusername',
                    'testpasswd',
                    [],
                    'component_id',
                    'components'
                );
        
        $model->createNewCollection();
    }

    public function testThatFetchRecordsIntoCollectionThrowsException(): void {
        
        $this->expectException(\GDAO\ModelMustImplementMethodException::class);
        
        $model = new \ModelForTestingNonAbstractMethods(
                    'testdsn',
                    'testusername',
                    'testpasswd',
                    [],
                    'component_id',
                    'components'
                );
        
        $model->fetchRecordsIntoCollection();
    }
    
    public function testThatGetSetCreatedTimestampColumnNameWorksAsExpected(): void {

        //create model setting property values with exact property names
        $model = (new \ModelForTestingNonAbstractMethods(
                    '', '', '', [],
                    'component_id',
                    'components'
                ))->setCreatedTimestampColumnName('test_c_col_name');
        
        $msg = __METHOD__;
        $this->assertTrue($model->getCreatedTimestampColumnName() === 'test_c_col_name', $msg);
    }
    
    public function testThatGetUpdatedTimestampColumnNameWorksAsExpected(): void {

        //create model setting property values with exact property names
        $model = (new \ModelForTestingNonAbstractMethods(
                    '', '', '', [], 'component_id', 'components'
                ))->setUpdatedTimestampColumnName('test_u_col_name');
        $msg = __METHOD__;
        $this->assertTrue($model->getUpdatedTimestampColumnName() === 'test_u_col_name', $msg);
    }
    
    public function testThatGetPrimaryColNameWorksAsExpected(): void {

        //create model setting property values with exact property names
        $model = new \ModelForTestingNonAbstractMethods(
                    '', '', '', [], 'component_id',  'components'
                );
        $msg = __METHOD__;
        $this->assertTrue($model->getPrimaryColName() === 'component_id', $msg);
        $this->assertTrue($model->getPrimaryColName(true) === 'components.component_id', $msg);
    }
    
    public function testThatGetTableNameWorksAsExpected(): void {

        //create model setting property values with exact property names
        $model = new \ModelForTestingNonAbstractMethods(
                    '', '', '', [], 'component_id', 'components'
                );
        $msg = __METHOD__;
        $this->assertTrue($model->getTableName() === 'components', $msg);
    }
    
    public function testThatGetTableColNamesWorksAsExpected(): void {
        
        //create model setting property values with exact property names
        $model = (new \ModelForTestingNonAbstractMethods(
                '',
                '',
                '',
                [],
                'component_id', 
                'components'
            ))->setTableCols(['col1', 'col2']);
        
        $model2 = (new \ModelForTestingNonAbstractMethods(
                '',
                '',
                '',
                [],
                'component_id', 
                'components'
            ))->setTableCols(
                    [
                        'col1'=>['sub array with metadata for col1'], 
                        'col2'=>['sub array with metadata for col2']
                    ]
                );
        
        $model3 = (new \ModelForTestingNonAbstractMethods(
                '',
                '',
                '',
                [],
                'component_id',
                'components'
            ))->setTableCols(
                [
                    'col1'=>['sub array with metadata for col1'], 
                    'col2'=>['sub array with metadata for col2'],
                    'col3',
                    'col4'=>['sub array with metadata for col4']
                ]
            );
        $msg = __METHOD__;
        $this->assertTrue(['col1', 'col2'] === $model->getTableColNames(), $msg);
        $this->assertTrue(['col1', 'col2'] === $model2->getTableColNames(), $msg);
        $this->assertTrue(['col1', 'col2', 'col3', 'col4'] === $model3->getTableColNames(), $msg);
    }
    
    public function testThatGetRelationNamesWorksAsExpected(): void {

        $rel = [
            'a_relation_name'=> [
                'relation_type' => \GDAO\Model::RELATION_TYPE_BELONGS_TO,
                'foreign_key_col_in_my_table' => 'output_id',
                'foreign_table' => 'project_outputs',
                'foreign_key_col_in_foreign_table' => 'output_id',
                'primary_key_col_in_foreign_table' => 'output_id',
                'foreign_models_class_name' => '\\VendorName\\PackageName\\ModelClassName',
                'foreign_models_collection_class_name' => '\\VendorName\\PackageName\\ModelClassName\\Collection',
                'foreign_models_record_class_name' => '\StdClass',
                'sql_query_modifier' => function(object $selectObj): object {
                    // Do some stuff to / with $selectObj
                    return $selectObj;
                },
            ],
            'a_relation_name2'=> [
                'relation_type' => \GDAO\Model::RELATION_TYPE_HAS_MANY,
                'foreign_key_col_in_my_table' => 'output_id',
                'foreign_table' => 'project_outputs',
                'foreign_key_col_in_foreign_table' => 'output_id',
                'primary_key_col_in_foreign_table' => 'output_id',
                'foreign_models_class_name' => '\\VendorName\\PackageName\\ModelClassName',
                'foreign_models_collection_class_name' => '\\VendorName\\PackageName\\ModelClassName\\Collection',
                'foreign_models_record_class_name' => '\StdClass',
                'sql_query_modifier' => function(object $selectObj): object {
                    // Do some stuff to / with $selectObj
                    return $selectObj;
                },
            ],
        ];
        
        //create model setting property values with exact property names
        $model = (new \ModelForTestingNonAbstractMethods(
                '',
                '',
                '',
                [],
                'component_id',
                'components'
            ))->setTableCols(['col1', 'col2'])
              ->setRelations($rel);
        $msg = __METHOD__;
        $this->assertTrue(['a_relation_name', 'a_relation_name2'] === $model->getRelationNames(), $msg);
    }
}
