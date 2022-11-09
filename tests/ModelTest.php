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
    protected $_mock_model_obj_with_no_db_connection;
    protected $_mock_model_obj_with_memory_sqlite_connection;

    protected function setUp(): void {

        parent::setUp();

        $this->_mock_model_obj_with_no_db_connection = 
            new \ModelForTestingNonAbstractMethods('', '', '', [], []);

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
    
    public function testThatConstructorSetsFirstFourParamsCorrectly(): void {
        
        $model = new \ModelForTestingNonAbstractMethods(
                    'test_dsn',
                    'test_username',
                    'test_passwd',
                    [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'],
                    [
                        '_primary_col'=>'component_id', 
                        '_table_name'=>'components',
                    ]
                );
                
        $msg = __METHOD__;
        
        $model_obj_as_array = $model->toArray();
        $this->assertTrue($model_obj_as_array['_primary_col'] === 'component_id', $msg);
        $this->assertTrue($model_obj_as_array['_table_name'] === 'components', $msg);
        $this->assertTrue($model_obj_as_array['_dsn'] === 'test_dsn', $msg);
        $this->assertTrue($model_obj_as_array['_username'] === 'test_username', $msg);
        $this->assertTrue($model_obj_as_array['_passwd'] === 'test_passwd', $msg);
        $this->assertTrue($model_obj_as_array['_pdo_driver_opts'] === [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'], $msg);
    }

    public function testThatConstructorThrowsExceptionForEmptyPrimaryColName(): void {
        
        $this->expectException(\GDAO\ModelPrimaryColNameNotSetDuringConstructionException::class);
        
        new \ModelForTestingNonAbstractMethods(
                    'test_dsn',
                    'test_username',
                    'test_passwd',
                    [],
                    [
                        //'_primary_col'=>'component_id', 
                        '_table_name'=>'components',
                    ]
                );
    }

    public function testThatConstructorThrowsExceptionForEmptyTableName(): void {
        
        $this->expectException(\GDAO\ModelTableNameNotSetDuringConstructionException::class);
        
        new \ModelForTestingNonAbstractMethods(
                    'test_dsn',
                    'test_username',
                    'test_passwd',
                    [],
                    [
                        '_primary_col'=>'component_id', 
                        //'_table_name'=>'components',
                    ]
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
                'foreign_table_sql_params' => [
                    'cols' => ['project_outputs.deliverable_id', 'component_deliverables.deliverable', 'component_deliverables.component_id'],
                    'where' => [
                        [ 'col' => 'project_outputs.hidden_fiscal_year', 'op' => '=', 'val' => 16 ],
                        [ 'col' => 'project_outputs.deactivated', 'op' => '=', 'val' => 0],
                        [ 'col' => 'project_outputs.parent_id', 'op' => 'is-null'],
                    ],
                ]
            ],
        ];
        
        //create model setting property values with exact property names
        $model = new \ModelForTestingNonAbstractMethods(
                '',
                '',
                '',
                [],
                [
                    '_primary_col'=>'component_id', 
                    '_table_name'=>'components', 
                    '_relations'=>$rel,
                    '_collection_class_name'=>'TestCollectionClassName',
                    '_record_class_name'=>'TestRecordClassName',
                    '_table_cols'=>['col1', 'col2'],
                    '_created_timestamp_column_name'=> 'test_c_col_name',
                    '_updated_timestamp_column_name'=> 'test_u_col_name',
                    '_dsn'=> 'test_dsn',
                    '_username'=> 'test_username',
                    '_passwd'=> 'test_passwd',
                    '_pdo_driver_opts'=> [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'],
                ]
            );
        
        //create model setting property values with property names (excluding the underscore prefix)
        $model2 = new \ModelForTestingNonAbstractMethods(
                '',
                '',
                '',
                [],
                [
                    'primary_col'=>'component_id', 
                    'table_name'=>'components', 
                    'relations'=>$rel,
                    'collection_class_name'=>'TestCollectionClassName',
                    'record_class_name'=>'TestRecordClassName',
                    'table_cols'=>['col1', 'col2'],
                    'created_timestamp_column_name'=> 'test_c_col_name',
                    'updated_timestamp_column_name'=> 'test_u_col_name',
                    'dsn'=> 'test_dsn',
                    'username'=> 'test_username',
                    'passwd'=> 'test_passwd',
                    'pdo_driver_opts'=> [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'],
                ]
            );
        
        $msg = __METHOD__;
        
        $model_obj_as_array = $model->toArray();
        $this->assertTrue($model_obj_as_array['_primary_col'] === 'component_id', $msg);
        $this->assertTrue($model_obj_as_array['_table_name'] === 'components', $msg);
        $this->assertTrue($model_obj_as_array['_relations'] === $rel, $msg);
        $this->assertTrue($model_obj_as_array['_collection_class_name'] === 'TestCollectionClassName', $msg);
        $this->assertTrue($model_obj_as_array['_record_class_name'] === 'TestRecordClassName', $msg);
        $this->assertTrue($model_obj_as_array['_table_cols'] === ['col1', 'col2'], $msg);
        $this->assertTrue($model_obj_as_array['_created_timestamp_column_name'] === 'test_c_col_name', $msg);
        $this->assertTrue($model_obj_as_array['_updated_timestamp_column_name'] === 'test_u_col_name', $msg);
        $this->assertTrue($model_obj_as_array['_dsn'] === 'test_dsn', $msg);
        $this->assertTrue($model_obj_as_array['_username'] === 'test_username', $msg);
        $this->assertTrue($model_obj_as_array['_passwd'] === 'test_passwd', $msg);
        $this->assertTrue($model_obj_as_array['_pdo_driver_opts'] === [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'], $msg);
        
        $model2_obj_as_array = $model2->toArray();
        $this->assertTrue($model2_obj_as_array['_primary_col'] === 'component_id', $msg);
        $this->assertTrue($model2_obj_as_array['_table_name'] === 'components', $msg);
        $this->assertTrue($model2_obj_as_array['_relations'] === $rel, $msg);
        $this->assertTrue($model2_obj_as_array['_collection_class_name'] === 'TestCollectionClassName', $msg);
        $this->assertTrue($model2_obj_as_array['_record_class_name'] === 'TestRecordClassName', $msg);
        $this->assertTrue($model2_obj_as_array['_table_cols'] === ['col1', 'col2'], $msg);
        $this->assertTrue($model2_obj_as_array['_created_timestamp_column_name'] === 'test_c_col_name', $msg);
        $this->assertTrue($model2_obj_as_array['_updated_timestamp_column_name'] === 'test_u_col_name', $msg);
        $this->assertTrue($model2_obj_as_array['_dsn'] === 'test_dsn', $msg);
        $this->assertTrue($model2_obj_as_array['_username'] === 'test_username', $msg);
        $this->assertTrue($model2_obj_as_array['_passwd'] === 'test_passwd', $msg);
        $this->assertTrue($model2_obj_as_array['_pdo_driver_opts'] === [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'], $msg);
    }
    
////////////////////////////////////////////////////////////////////////////////
// End of Tests for \GDAO\Model::__construct(....)
////////////////////////////////////////////////////////////////////////////////

    public function testThat__toStringWorksAsExpected(): void {
        
        $model = new \ModelForTestingNonAbstractMethods(
            'test_dsn',
            'test_username',
            'test_passwd',
            [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'],
            [
                '_primary_col'=>'component_id', 
                '_table_name'=>'components',
            ]
        );
        
        $sub_str = <<<EOT
array (
  '_primary_col' => 'component_id',
  '_table_name' => 'components',
  '_table_cols' => 
  array (
  ),
  '_collection_class_name' => NULL,
  '_record_class_name' => NULL,
  '_created_timestamp_column_name' => NULL,
  '_updated_timestamp_column_name' => NULL,
  '_relations' => 
  array (
  ),
  '_dsn' => 'test_dsn',
  '_username' => 'test_username',
  '_passwd' => 'test_passwd',
  '_pdo_driver_opts' => 
  array (
    1002 => 'SET NAMES utf8',
  ),
)
EOT;
//echo $model->__toString(); exit;
        PHP_OS_FAMILY !== 'Windows' && $this->assertStringContainsString($sub_str, $model->__toString());
    }
    
    public function testThatToArrayWorksAsExpected(): void {
        
        $model = new \ModelForTestingNonAbstractMethods(
            'test_dsn',
            'test_username',
            'test_passwd',
            [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'],
            [
                '_primary_col'=>'component_id', 
                '_table_name'=>'components',
            ]
        );
        
        $expected_array = [
            '_primary_col' => 'component_id',
            '_table_name' => 'components',
            '_table_cols' => [],
            '_collection_class_name' => null,
            '_record_class_name' => null,
            '_created_timestamp_column_name' => null,
            '_updated_timestamp_column_name' => null,
            '_relations' => [],
            '_dsn' => 'test_dsn',
            '_username' => 'test_username',
            '_passwd' => 'test_passwd',
            '_pdo_driver_opts' => [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']
        ];

        $msg = __METHOD__;
        $this->assertTrue( $expected_array === $model->toArray(), $msg);
    }

    public function testThatCreateNewCollectionThrowsException(): void {
        
        $this->expectException(\GDAO\ModelMustImplementMethodException::class);
        
        $model = new \ModelForTestingNonAbstractMethods(
                    'test_dsn',
                    'test_username',
                    'test_passwd',
                    [],
                    [
                        '_primary_col'=>'component_id', 
                        '_table_name'=>'components',
                    ]
                );
        
        $model->createNewCollection();
    }

    public function testThatFetchRecordsIntoCollectionThrowsException(): void {
        
        $this->expectException(\GDAO\ModelMustImplementMethodException::class);
        
        $model = new \ModelForTestingNonAbstractMethods(
                    'test_dsn',
                    'test_username',
                    'test_passwd',
                    [],
                    [
                        '_primary_col'=>'component_id', 
                        '_table_name'=>'components',
                    ]
                );
        
        $model->fetchRecordsIntoCollection();
    }
    
    public function testThatGetCreatedTimestampColumnNameWorksAsExpected(): void {

        //create model setting property values with exact property names
        $model = new \ModelForTestingNonAbstractMethods(
                    '', '', '', [],
                    [
                        '_primary_col'=>'component_id', 
                        '_table_name'=>'components',
                        '_created_timestamp_column_name'=> 'test_c_col_name',
                    ]
                );
        $msg = __METHOD__;
        $this->assertTrue($model->getCreatedTimestampColumnName() === 'test_c_col_name', $msg);
    }
    
    public function testThatGetUpdatedTimestampColumnNameWorksAsExpected(): void {

        //create model setting property values with exact property names
        $model = new \ModelForTestingNonAbstractMethods(
                    '', '', '', [],
                    [
                        '_primary_col'=>'component_id', 
                        '_table_name'=>'components',
                        '_updated_timestamp_column_name'=> 'test_u_col_name',
                    ]
                );
        $msg = __METHOD__;
        $this->assertTrue($model->getUpdatedTimestampColumnName() === 'test_u_col_name', $msg);
    }
    
    public function testThatGetPrimaryColNameWorksAsExpected(): void {

        //create model setting property values with exact property names
        $model = new \ModelForTestingNonAbstractMethods(
                    '', '', '', [],
                    [
                        '_primary_col'=>'component_id', 
                        '_table_name'=>'components',
                    ]
                );
        $msg = __METHOD__;
        $this->assertTrue($model->getPrimaryColName() === 'component_id', $msg);
        $this->assertTrue($model->getPrimaryColName(true) === 'components.component_id', $msg);
    }
    
    public function testThatGetTableNameWorksAsExpected(): void {

        //create model setting property values with exact property names
        $model = new \ModelForTestingNonAbstractMethods(
                    '', '', '', [],
                    [
                        '_primary_col'=>'component_id', 
                        '_table_name'=>'components',
                    ]
                );
        $msg = __METHOD__;
        $this->assertTrue($model->getTableName() === 'components', $msg);
    }
    
    public function testThatGetTableColNamesWorksAsExpected(): void {
        
        //create model setting property values with exact property names
        $model = new \ModelForTestingNonAbstractMethods(
                '',
                '',
                '',
                [],
                [
                    '_primary_col'=>'component_id', 
                    '_table_name'=>'components',
                    '_table_cols'=>['col1', 'col2'],
                ]
            );
        $model2 = new \ModelForTestingNonAbstractMethods(
                '',
                '',
                '',
                [],
                [
                    '_primary_col'=>'component_id', 
                    '_table_name'=>'components',
                    '_table_cols'=>[
                                    'col1'=>['sub array with metadata for col1'], 
                                    'col2'=>['sub array with metadata for col2']
                                ],
                ]
            );
        $model3 = new \ModelForTestingNonAbstractMethods(
                '',
                '',
                '',
                [],
                [
                    '_primary_col'=>'component_id', 
                    '_table_name'=>'components',
                    '_table_cols'=>[
                                    'col1'=>['sub array with metadata for col1'], 
                                    'col2'=>['sub array with metadata for col2'],
                                    'col3',
                                    'col4'=>['sub array with metadata for col4']
                                ],
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
                'foreign_table_sql_params' => [
                    'cols' => ['project_outputs.deliverable_id', 'component_deliverables.deliverable', 'component_deliverables.component_id'],
                    'where' => [
                        [ 'col' => 'project_outputs.hidden_fiscal_year', 'op' => '=', 'val' => 16 ],
                        [ 'col' => 'project_outputs.deactivated', 'op' => '=', 'val' => 0],
                        [ 'col' => 'project_outputs.parent_id', 'op' => 'is-null'],
                    ],
                ]
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
                'foreign_table_sql_params' => [
                    'cols' => ['project_outputs.deliverable_id', 'component_deliverables.deliverable', 'component_deliverables.component_id'],
                    'where' => [
                        [ 'col' => 'project_outputs.hidden_fiscal_year', 'op' => '=', 'val' => 16 ],
                        [ 'col' => 'project_outputs.deactivated', 'op' => '=', 'val' => 0],
                        [ 'col' => 'project_outputs.parent_id', 'op' => 'is-null'],
                    ],
                ]
            ],
        ];
        
        //create model setting property values with exact property names
        $model = new \ModelForTestingNonAbstractMethods(
                '',
                '',
                '',
                [],
                [
                    '_primary_col'=>'component_id', 
                    '_table_name'=>'components', 
                    '_relations'=>$rel,
                    '_table_cols'=>['col1', 'col2'],
                ]
            );
        $msg = __METHOD__;
        $this->assertTrue(['a_relation_name', 'a_relation_name2'] === $model->getRelationNames(), $msg);
    }

    protected function _isHhvm(): bool {
        
        return defined('HHVM_VERSION');
    }
}
