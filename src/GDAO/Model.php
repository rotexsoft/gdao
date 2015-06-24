<?php

namespace GDAO;

/**
 * 
 * An abstract Model class that can be extended to create a Model class that 
 * performs data Creation, Retrieval, Update and Deletion operations on an sql
 * data-source.
 * 
 * Support for other data-sources like xml, csv, no-SQL, etc. may be added in 
 * the future. 
 * 
 * @author Rotimi Adegbamigbe
 * @copyright (c) 2015, Rotimi Adegbamigbe
 * 
 */
abstract class Model
{
    /**
     * 
     * Name of the primary key column in the db table associated with this model
     * Default value is 'id'.
     * 
     * This is a REQUIRED field & must be properly set by consumers of this class
     * 
     * @todo Work on supporting tables that don't have any primary key column defined
     * 
     * @var string
     * 
     */
    protected $_primary_col = 'id';
    
    /**
     *
     * Name of the db table associated with this model
     * 
     * This is a REQUIRED field & must be properly set by consumers of this class
     * 
     * @var string
     * 
     */
    protected $_table_name = null;

    /**
     *
     * Array of column information for the db table associated with this model.
     * 
     * This is an OPTIONAL field & may be set by consumers of this class or
     * auto-populated by implementers of this class preferably inside the
     * constructor.
     * 
     * It can be a one dimensional array of strings, where each string is the 
     * name of a column in the db table associated with this model.
     * 
     * Eg. for a table posts associated with this model this array could look like 
     * 
     *  ['id', 'title', 'body', ....]
     * 
     * OR
     * 
     * It can be a two dimensional array where the each key is a name of a 
     * column in the db table associated with this model and the value is an
     * array containing more data (meta-data) about the column (it's up to the 
     * implementer of this class to decide what the structure of the meta-data 
     * array will be).
     * 
     * Eg. for a table posts associated with this model this array could look like 
     *  [
     *      'id' => ['type'=>int, 'size'=>10, 'notnull'=>true, ... ],
     *      'title' => ['type'=>varchar, 'size'=>255, 'notnull'=>true, ... ],
     *      'body' => ['type'=>text, 'size'=>null, 'notnull'=>true, ... ],
     *      ......................,
     *      ......................
     *  ]
     * 
     * In both cases above, it will be trivial to implement a getColumnNames()
     * method that returns an array of column names for this Model using code 
     * like this:
     * 
     * if( $this->_table_cols is not empty ) {
     * 
     *      if( $this->_table_cols has numeric keys ) {
     *          
     *          return $this->_table_cols;
     * 
     *      } else {
     *          
     *          //the keys are non-numeric and must be strings that represent
     *          //the column names we're looking for
     *          
     *          return array_keys($this->_table_cols);
     *      }
     * }
     * 
     * It is strongly recommended that the users of this class should stick to 
     * populating this array strictly as a one-dimensional array or strictly as 
     * a two-dimensional array as defined above. Definitions like the one below 
     * should either be rejected (an exception could be thrown) or corrected (by 
     * implementers of this class in parts of their code that access
     * $this->_table_cols).
     * 
     * Bad Definition:
     * [
     *      'id',
     *      'title'=>['type'=>varchar, 'size'=>255, 'notnull'=>true, ... ],
     *      'body',
     *      .......
     * ]
     * 
     * Solution
     * 1. Throw an exception stating that $this->_table_cols has missing metadata
     *    for the 'id' and 'body' columns.
     * 
     * 2. Correct by either converting $this->_table_cols to a one-dimensional
     *    array or a two-dimensional array like below:
     * 
     *    One-dimensional (meta-data for 'title' is discarded):
     * 
     *      ['id', 'title', 'body', ....]
     * 
     *    Two-dimensional (dummy meta-data is added for 'id' and 'body'):
     * 
     *      [
     *         'id' => ['type'=>'', 'size'=>'', 'notnull'=>'', ... ],
     *         'title' => ['type'=>varchar, 'size'=>255, 'notnull'=>true, ... ],
     *         'body' => ['type'=>'', 'size'=>'', 'notnull'=>'', ... ],,
     *         ......................,
     *         ......................
     *      ]
     * 
     * Solution 1, seems to be the best way to go since it involves less code 
     * and would force consumers of implementations of this class to get into
     * the habit of properly populating $this->_table_cols in the recommended
     * formats (1-d or 2-d).
     * 
     * Aura.SqlSchema (https://github.com/auraphp/Aura.SqlSchema , 
     * https://packagist.org/packages/aura/sqlschema ) is a php package that can 
     * be easily used to populate $this->_table_cols. 
     * Db schema meta-data could also easily be queried using the PDO object 
     * available via $this->getPDO().
     * 
     * @var aray
     * 
     */
    protected $_table_cols = array();

    /**
     * 
     * Name of the collection class for this model. 
     * Must be a descendant of \GDAO\Model\Collection
     * 
     * This is an OPTIONAL field & may be set by consumers of this class if they
     * would be calling methods of this class that either return instance(s) of
     * \GDAO\Model\Collection or its descendants or accepts \GDAO\Model\Collection 
     * or its descendants as parameters.
     * 
     * Implementers of this class should check that $this->_collection_class_name 
     * has a valid value before attempting to use it inside method(s) they are 
     * implementing.
     * 
     * @var string
     * 
     */
    protected $_collection_class_name = null;

    /**
     * 
     * Name of the record class for this model. 
     * Must be a descendant of \GDAO\Model\Record
     * 
     * This is a REQUIRED field & must be properly set by consumers of this class
     * 
     * @var string
     * 
     */
    protected $_record_class_name = null;

    /**
     *
     * Name of a column in the db table associated with this model that is used
     * to keep track of the time when a row of data was initially inserted into
     * a db table. 
     * 
     * The column whose name is assigned to $this->_created_timestamp_column_name
     * should be of a timestamp data-type (i.e. it must be able to store day,
     * month, year, hour, minute and second information. Eg. DATETIME / TIMESTAMP 
     * in MySQL, timestamp in Postgresql, datetime2 / datetimeoffset in MSSqlServer).
     * 
     * This is an OPTIONAL field & may be set by consumers of this class if the 
     * db table associated with this model has a column that satisfies the 
     * definitions above.
     * 
     * The value of this field can be used by implementers of this class to 
     * implement functionality that automatically updates the db column that
     * tracks the time a row of data was initially inserted into a db table.
     * 
     * @var string
     * 
     */
    protected $_created_timestamp_column_name = null;   //string

    /**
     *
     * Name of a column in the db table associated with this model that is used
     * to keep track of the time when a row of data was last updated in a db 
     * table.
     * 
     * The column whose name is assigned to $this->_updated_timestamp_column_name
     * should be of a timestamp data-type (i.e. it must be able to store day,
     * month, year, hour, minute and second information. Eg. DATETIME / TIMESTAMP 
     * in MySQL, timestamp in Postgresql, datetime2 / datetimeoffset in MSSqlServer).
     * 
     * This is an OPTIONAL field & may be set by consumers of this class if the 
     * db table associated with this model has a column that satisfies the 
     * definitions above.
     * 
     * The value of this field can be used by implementers of this class to 
     * implement functionality that automatically updates the db column that
     * tracks the time a row of data was last updated in a db table.
     * 
     * @var string
     * 
     */
    protected $_updated_timestamp_column_name = null;   //string
    
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    //* There are 4 arrays below for modeling relationships                  *//
    //*                                                                      *//
    //* Four types of relationships supported                                *//
    //*  - One-To-One (eg. 1 Post has exactly 1 Summary) a.k.a Has-One       *//
    //*  - One-To-Many (eg. 1 Post has Many Comments) a.k.a Has-Many         *//
    //*  - Many-To-One (eg. Many Posts belong to 1 Author) a.k.a Belongs-To  *//
    //*  - Many-To-Many a.k.a Has-Many-Through                               *//
    //*    (eg. 1 Post has Many Tags through the posts_tags table)           *//
    //*                                                                      *//
    //* It is up to the individual(s) extending this class to implement      *//
    //* relationship related features based on the definition structures     *//
    //* outlined below. Things like eager loading and saving related         *//
    //* records are some of the features that can be implemented using       *//
    //* these relationship definitions.                                      *//
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    
    /**
     *
     * 2-dimensional array defining Has-One relationships
     * 
     * The Implementers of this class can use the definition(s) in 
     * \GDAO\Model->_has_one_relationships to implement retrieval of data from
     * db tables associated with other models related to this model.
     * 
     * This is an OPTIONAL field & may be set by consumers of this class if they
     * want to define Has-One relationship(s) between the current model's db 
     * table and other models' db table(s) in their application using the 
     * format described below.
     * 
     *      --------------------------
     *      |         posts          |
     *      --------------------------
     *      ||=======||--------|-----|
     *      ||post_id||........|title|
     *      ||=======||--------|-----|
     *      --------------------------
     *          ||
     *          ||
     *          ||
     *         VVVV
     *      ---------------------------------
     *      |           summaries           |
     *      ---------------------------------
     *      ||=========||--------|----------|
     *      ||s_post_id||........|view_count|
     *      ||=========||--------|----------|
     *      ---------------------------------
     *     
     *      NOTE: the post_id column in the posts table is an auto-incrementing 
     *            integer primary key.
     *     
     *      NOTE: the summaries table does not have a primary key. 
     *            There should be a unique index on its s_post_id column to 
     *            enforce the rule that a post can have only one summary and to 
     *            also improve query performance.
     * 
     * To specify that a model with a \GDAO\Model->_table_name value of 
     * 'posts' has one summary for each post record (based on the schema above),
     * modify \GDAO\Model->_has_one_relationships like below:
     * 
     * \GDAO\Model->_has_one_relationships['summary'] = 
     *      [
     *          'my_models_table' => 'posts',
     *          'foreign_key_col_in_my_models_table' => 'post_id',
     *          
     *          'foreign_models_table' => 'summaries',
     *          'foreign_key_col_in_foreign_models_table' => 's_post_id'
     *          'foreign_models_class_name' => '\\VendorName\\PackageName\\ModelClassName'
     *      ]
     * 
     * NOTE: the array key value 'summary' is a relation name that can be used to 
     *       later access this particular relationship definiton. Any value can 
     *       be used to name a relationship (but it is recommended that it should
     *       not be a name of an existing column in the current model's db table).
     * 
     * NOTE: 'foreign_models_class_name' should contain the name of a Model
     *       class whose _table_name property has the same value as
     *       \GDAO\Model->_has_one_relationships['relation_name']['foreign_models_table'].
     *       In the example above 'relation_name' is substituted with 'summary'.
     * 
     * @var array
     * 
     */
    protected $_has_one_relationships = array();
    
    /**
     *
     * 2-dimensional array defining Has-Many relationships
     * 
     * The Implementers of this class can use the definition(s) in 
     * \GDAO\Model->_has_many_relationships to implement retrieval of data from
     * db tables associated with other models related to this model.
     * 
     * This is an OPTIONAL field & may be set by consumers of this class if they
     * want to define Has-Many relationship(s) between the current model's db 
     * table and other models' db table(s) in their application using the 
     * format described below.
     * 
     *      --------------------------
     *      |         posts          |
     *      --------------------------
     *      ||=======||--------|-----|
     *      ||post_id||........|title|
     *      ||=======||--------|-----|
     *      --------------------------
     *          ||
     *          ||
     *          ==============
     *                      ||
     *                     VVVV
     *      --------------------------------------------
     *      |                comments                  |
     *      --------------------------------------------
     *      |----------||=========||--------|----------|
     *      |comment_id||c_post_id||........|   body   |
     *      |----------||=========||--------|----------|
     *      --------------------------------------------
     *     
     *      NOTE: the post_id column in the posts table is an
     *            auto-incrementing integer primary key.
     *     
     *      NOTE: the comment_id column in the comments table is an
     *            auto-incrementing integer primary key.
     *
     * To specify that a model with a \GDAO\Model->_table_name value of 
     * 'posts' has many comments for each post record (based on the schema above),
     * modify \GDAO\Model->_has_many_relationships like below:
     * 
     * \GDAO\Model->_has_many_relationships['comments'] = 
     *      [
     *          'my_models_table' => 'posts',
     *          'foreign_key_col_in_my_models_table' => 'post_id',
     *          
     *          'foreign_models_table' => 'comments',
     *          'foreign_key_col_in_foreign_models_table' => 'c_post_id'
     *          'foreign_models_class_name' => '\\VendorName\\PackageName\\ModelClassName'
     *      ]
     * 
     * NOTE: the array key value 'comments' is a relation name that can be used to
     *       later access this particular relationship definiton. Any value can be
     *       used to name a relationship (but it is recommended that it should not
     *       be a name of an existing column in the current model's db table).
     * 
     * NOTE: 'foreign_models_class_name' should contain the name of a Model class
     *       whose _table_name property has the same value as
     *       \GDAO\Model->_has_many_relationships['relation_name']['foreign_models_table'].
     *       In the example above 'relation_name' is substituted with 'comments'.
     * 
     * @var array
     * 
     */
    protected $_has_many_relationships = array();
    
    /**
     *
     * 2-dimensional array defining Belongs-To relationships
     * 
     * The Implementers of this class can use the definition(s) in 
     * \GDAO\Model->_belongs_to_relationships to implement retrieval of data 
     * from db tables associated with other models related to this model.
     * 
     * This is an OPTIONAL field & may be set by consumers of this class if they
     * want to define Belongs-To relationship(s) between the current model's db 
     * table and other models' db table(s) in their application using the format 
     * described below.
     * 
     *      ---------------------------
     *      |         authors         |
     *      ---------------------------
     *      ||=========||--------|----|
     *      ||author_id||........|name|
     *      ||=========||--------|----|
     *      ---------------------------
     *          ||
     *          ||
     *          =============
     *                     ||
     *                    VVVV
     *      --------------------------------------
     *      |                posts               |
     *      --------------------------------------
     *      |-------||===========||--------|-----|
     *      |post_id||p_author_id||........|title|
     *      |-------||===========||--------|-----|
     *      --------------------------------------
     *     
     *      NOTE: the author_id column in the authors table is an
     *            auto-incrementing integer primary key.
     *     
     *      NOTE: the post_id column in the posts table is an
     *            auto-incrementing integer primary key.
     * 
     * To specify that a model with a \GDAO\Model->_table_name value of 
     * 'posts' has each of its post records belonging to one author (based on 
     * the schema above), modify \GDAO\Model->_belongs_to_relationships like below:
     * 
     * \GDAO\Model->_belongs_to_relationships['author'] = 
     *      [
     *          'my_models_table' => 'posts',
     *          'foreign_key_col_in_my_models_table' => 'p_author_id',
     *          
     *          'foreign_models_table' => 'authors',
     *          'foreign_key_col_in_foreign_models_table' => 'author_id',
     *          'foreign_models_class_name' => '\\VendorName\\PackageName\\ModelClassName'
     *      ]
     * 
     * NOTE: the array key value 'author' is a relation name that can be used to 
     *       later access this particular relationship definiton. Any value can 
     *       be used to name a relationship (but it is recommended that it should 
     *       not be a name of an existing column in the current model's db table).
     * 
     * NOTE: 'foreign_models_class_name' should contain the name of a Model
     *       class whose _table_name property has the same value as
     *       \GDAO\Model->_belongs_to_relationships['relation_name']['foreign_models_table'].
     *       In the example above 'relation_name' is substituted with 'author'.
     * 
     * @var array
     * 
     */
    protected $_belongs_to_relationships = array();
    
    /**
     *
     * 2-dimensional array defining Has-Many-Through relationships
     * 
     * The Implementers of this class can use the definition(s) in 
     * \GDAO\Model->_has_many_through_relationships to implement retrieval of 
     * data from db tables associated with other models related to this model.
     * 
     * This is an OPTIONAL field & may be set by consumers of this class if they
     * want to define Has-Many-Through relationship(s) between the current model's 
     * db table and other models' db table(s) in their application using the format 
     * described below.
     *     
     *      --------------------------  ------------------------
     *      |         posts          |  |         tags         |
     *      --------------------------  ------------------------
     *      ||=======||--------|-----|  ||======||--------|----|
     *      ||post_id||........|title|  ||tag_id||........|name|
     *      ||=======||--------|-----|  ||======||--------|----|
     *      --------------------------  ------------------------
     *           ||                          ||
     *           ||                          ||
     *           =================           ||
     *                          ||           ||
     *                         VVVV         VVVV
     *      -------------------------------------------
     *      |              posts_tags                 |
     *      -------------------------------------------
     *      |-------------||============||===========||
     *      |posts_tags_id||psts_post_id||psts_tag_id||
     *      |-------------||============||===========||
     *      -------------------------------------------
     *     
     *      NOTE: the post_id column in the posts table is an
     *            auto-incrementing integer primary key.
     *     
     *      NOTE: the tag_id column in the tags table is an
     *            auto-incrementing integer primary key.
     *     
     *      NOTE: the posts_tags_id column in the posts_tags 
     *            table is an auto-incrementing integer primary key. 
     * 
     * To specify that a model with a \GDAO\Model->_table_name value of 
     * 'posts' has many tags for each post record through a join table called
     * posts_tags (based on the schema above), modify 
     * \GDAO\Model->_has_many_through_relationships like below:
     * 
     * \GDAO\Model->_has_many_through_relationships['tags'] = 
     *      [
     *          'my_models_table' => 'posts',
     *          'col_in_my_models_table_linked_to_join_table' => 'post_id',
     *
     *          'join_table_name' => 'posts_tags',
     *          'col_in_join_table_linked_to_my_models_table' => 'psts_post_id',
     *          'col_in_join_table_linked_to_foreign_models_table' => 'psts_tag_id',
     *          'join_models_class_name' => '\\VendorName\\PackageName\\ModelClassName'
     * 
     *          'foreign_models_table' => 'tags',
     *          'col_in_foreign_models_table_linked_to_join_table' => 'tag_id',
     *          'foreign_models_class_name' => '\\VendorName\\PackageName\\ModelClassName'
     *      ]
     * 
     * NOTE: the array key value 'tags' is a relation name that can be used to 
     *       later access this particular relationship definiton. Any value can 
     *       be used to name a relationship (but it is recommended that it should
     *       not be a name of an existing column in the current model's db table).
     * 
     * NOTE: 'foreign_models_class_name' should contain the name of a Model class
     *       whose _table_name property has the same value as
     *       \GDAO\Model->_belongs_to_relationships['relation_name']['foreign_models_table'].
     *       In the example above 'relation_name' is substituted with 'author'.
     * 
     * NOTE: 'foreign_models_class_name' should contain the name of a Model class 
     *       whose _table_name property has the same value as
     *       \GDAO\Model->_has_many_through_relationships['relation_name']['foreign_models_table'].
     *       'join_models_class_name' should contain the name of a Model class 
     *       whose _table_name property has the same value as
     *       \GDAO\Model->_has_many_through_relationships['relation_name']['join_table_name'].
     *       In the example above 'relation_name' is substituted with 'tags'.
     * 
     * @var array
     * 
     */
    protected $_has_many_through_relationships = array();

    /**
     * 
     * A PDO compliant Data Source Name (DSN) string containing the information 
     * required to connect to a desired database. 
     * 
     * @var string
     * 
     * @see \PDO::__construct() See description of the 1st parameter 
     *                          (http://php.net/manual/en/pdo.construct.php) if 
     *                          this Model will indeed be powered by a PDO instance
     * 
     */
    protected $_dsn = '';
    
    /**
     *
     * The username for the database to be connected to.
     * 
     * @var string
     * 
     * @see \PDO::__construct() See description of the 2nd parameter 
     *                          (http://php.net/manual/en/pdo.construct.php) if 
     *                          this Model will indeed be powered by a PDO instance
     * 
     */
    protected $_username = ''; 
    
    /**
     *
     * The password for the database to be connected to.
     * 
     * @var string
     * 
     * @see \PDO::__construct() See description of the 3rd parameter 
     *                          (http://php.net/manual/en/pdo.construct.php) if 
     *                          this Model will indeed be powered by a PDO 
     *                          instance
     * 
     */
    protected $_passwd = '';
    
    /**
     *
     * An array of options for a PDO driver
     * 
     * @var array
     * 
     * @see \PDO::__construct() See description of the 4th parameter 
     *                          (http://php.net/manual/en/pdo.construct.php) if 
     *                          this Model will indeed be powered by a PDO instance
     * 
     */
    protected $_pdo_driver_opts = array();
    
    /**
     *
     * An array that can be used to pass other parameters specific to a child 
     * class extending this class.
     * 
     * Eg. this array may be used to pass initialization value(s) for protected
     * and / or private properties that are defined in this class' subclasses but
     * not defined in this class.
     * 
     * @var array
     * 
     */
    protected $_extra_opts = array();   
    
    protected static $_where_or_having_ops_2_dbms_ops = array(
        '='         => '=', 
        '>'         => '>', 
        '>='        => '>=', 
        '<'         => '>', 
        '<='        => '<=', 
        'in'        => 'IN', 
        'is-null'   => 'IS NULL', 
        'like'      => 'LIKE', 
        '!='        => '<>', 
        'not-in'    => 'NOT IN',
        'not-like'  => 'NOT LIKE', 
        'not-null'  => 'IS NOT NULL'
    );
    
    /**
     * 
     * @param string $dsn
     * @param string $username
     * @param string $passwd
     * @param array $pdo_driver_opts
     * @param array $extra_opts an array that may be used to pass initialization 
     *                          value(s) for protected and / or private properties
     *                          of this class
     * 
     * @see \PDO::__construct(...) for definition of first four parameters
     * 
     */
    public function __construct(
        $dsn = '',
        $username = '', 
        $passwd = '', 
        array $pdo_driver_opts = array(),
        array $extra_opts = array()
    ) {
        $this->_dsn = $dsn;
        $this->_username = $username;
        $this->_passwd = $passwd;
        $this->_pdo_driver_opts = $pdo_driver_opts;
        
        if(count($extra_opts) > 0) {
            
            //set properties of this class specified in $extra_opts
            foreach($extra_opts as $e_opt_key => $e_opt_val) {
  
                if ( property_exists($this, $e_opt_key) ) {
                    
                    $this->$e_opt_key = $e_opt_val;

                } elseif ( property_exists($this, '_'.$e_opt_key) ) {

                    $this->{"_$e_opt_key"} = $e_opt_val;
                }
            }
        }
        
        if( empty($this->_primary_col) || strlen($this->_primary_col) <= 0 ) {
            
            $msg = 'Primary Key Column name ($_primary_col) not set for '.get_class($this);
            throw new ModelPrimaryColNameNotSetDuringConstructionException($msg);
        }
        
        if( empty($this->_table_name) ) {
            
            $msg = 'Table name ($_table_name) not set for '.get_class($this);
            throw new ModelTableNameNotSetDuringConstructionException($msg);
        }
    }

    /**
     * 
     * Implementers of this class can implement magic methods by overriding this method.
     * 
     * For example $this->fetchOneByIdAndTitle(1, 'A title!') will lead to this 
     * method being called (since fetchOneByIdAndTitle() doen't exist in this
     * class) with the following values:
     *      $method === 'fetchOneByIdAndTitle'
     *      $params === [0 => 1, 1 => 'A title!']
     * 
     * The string 'fetchOneByIdAndTitle' can be parsed to extract 'Id' & 'Title'.
     * 
     * @param string $method name of a method that does not exist in this class 
     *                       that is being called
     * @param array $params arguments passed to the non-existent method
     * 
     * @return mixed the return value of the magic method's implementation
     * 
     * @throws \GDAO\ModelMustImplementMethodException
     * 
     */
    public function __call($method, $params) {

        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new ModelMustImplementMethodException($msg);
    }

    /**
     * 
     * Implementers of this class can use this method to provide access to 
     * non-existent or publicly inaccessible (eg. protected) properties of 
     * an instance of this class.
     * 
     * @param string $property_name
     * 
     * @return mixed value of a non-existent or publicly inaccessible property of
     *               an instance of this class.
     * 
     * @throws \GDAO\ModelMustImplementMethodException
     * 
     */
    public function __get($property_name) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new ModelMustImplementMethodException($msg);
    }

    /**
     * 
     * Returns a string representation of an instance of this class.
     * 
     * @return string
     * 
     */
    public function __toString() {

        return print_r($this->toArray(), true);
    }

    /**
     * 
     * Returns an array representation of an instance of this class.
     * 
     * @return array an array representation of an instance of this class.
     * 
     */
    public function toArray() {

        return get_object_vars($this);
    }
    
    /**
     * 
     * Create and return a new collection of zero or more records (instances of \GDAO\Model\Record).
     * 
     * @param \GDAO\Model\GDAORecordsList $list_of_records.
     * @param array $extra_opts an array of other parameters that may be needed 
     *                          in creating an instance of \GDAO\Model\Collection
     * 
     * @return \GDAO\Model\Collection a collection of instances of \GDAO\Model\Record.
     * 
     */
    public function createCollection(\GDAO\Model\GDAORecordsList $list_of_records, array $extra_opts=array()) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new ModelMustImplementMethodException($msg);
    }
    
    /**
     * 
     * Create and return a new record with specified values.
     * 
     * @param array $col_names_and_values
     * @param array $extra_opts an array of other parameters that may be needed 
     *                          in creating an instance of \GDAO\Model\Record
     * 
     * @return \GDAO\Model\Record new record with specified values.
     * 
     */
    public abstract function createRecord(array $col_names_and_values = array(), array $extra_opts=array());

    /**
     * 
     * Delete one or more records matching specified conditions.
     * 
     * @param array $cols_n_vals array of where clause conditions for a delete statement
     *                             to delete one or more records in the db table associated
     *                             with this model.
     *                            
     *                             Eg. for a table 'x' with the following columns:
     *                             'id', 'title' and 'description'
     *              
     *                             ['id'=>5, 'title'=>'yabadabadoo'] should generate the sql below:
     *                             DELETE FROM `x` WHERE id = 5 AND title = 'yabadabadoo'
     *              
     *                             ['id'=>[5,6,7], 'title'=>'yipeedoo'] should generate the sql below:
     *                             DELETE FROM `x` WHERE id IN (5,6,7)  AND title = 'yipeedoo'
     *
     * @return bool|int|null the number of rows deleted if deletion was successful, 
     *                       false for a failed deletion OR null if nothing was 
     *                       deleted (no matching records).
     * 
     */
    public abstract function deleteRecordsMatchingSpecifiedColsNValues(array $cols_n_vals);

    /**
     * Delete the specified record from the database.
     * 
     * NOTE: Implementers of this class must set the record object to a new state 
     *       by a call to $record->setStateToNew() after a successful deletion 
     *       via this method.
     * 
     * @param \GDAO\Model\Record $record
     * 
     * @return bool|null true for a successful deletion, false for a failed 
     *                   deletion OR null if supplied record is a record  
     *                   that has never been saved to the db.
     * 
     */
    public abstract function deleteSpecifiedRecord(\GDAO\Model\Record $record);

    /**
     * 
     * Validates the structure of an array that is meant to contain definitions
     * for building a WHERE or HAVING clause for an SQL statement.
     * 
     * Usage:
     * For example if you have an array like this
     * 
     *  $params = [ 'cols'=>[.....], 'where'=>[.....], ...., 'having'=>[.....] ];
     * 
     * which is the type of $params array expected by \GDAO\Model::fetch*($params),
     * to validate $params['where'] and $params['having'], make the calls below
     * 
     *  $this->_validateWhereOrHavingParamsArray($params['where']);
     *  $this->_validateWhereOrHavingParamsArray($params['having']);
     * 
     * @see phpdoc for \GDAO\Model::fetchAll(array $params) for the definition 
     *      of a valid 'where' or 'having' array
     * 
     * @param array $array 
     * @return bool true if the array has a valid structure
     * @throws \GDAO\ModelBadWhereOrHavingParamSuppliedException
     */
    protected function _validateWhereOrHavingParamsArray(array $array) {

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator($array),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $_1st_level_keys_in_array = array_keys($array);
        $first_key = $_1st_level_keys_in_array[0];

        if (
                $first_key === "OR" || substr($first_key, 0, 3) === "OR#"
        ) {
            //The first key in the first iteration of the loop must !== 'OR' 
            //and not start with 'OR#'
            //Badly structured where / having params array supplied.
            //Where / having params array cannot have its first 
            //entry with a key value starting with 'OR' or 'OR#'. 
            $msg = 'ERROR: Bad where param array supplied to '
                    . get_class($this) . '::' . __FUNCTION__ . '(...). ' 
                    . PHP_EOL . 'The first key in the where param array '
                    . 'cannot start with \'OR\' or \'OR#\'' . PHP_EOL
                    . 'The array passed to ' 
                    . get_class($this) . '::' . __FUNCTION__ . '(...):' 
                    . PHP_EOL . var_export($array, true). PHP_EOL;

            throw new ModelBadWhereOrHavingParamSuppliedException($msg);
        }

        $previous_key = null;
        $previous_value = null;

        foreach ($iterator as $key => $value) {

            $_1st_key_in_curr_value_subarray = '';

            if ( is_array($value) && count($value) > 0 ) {

                $keys_in_current_value_subarray = array_keys($value);
                $_1st_key_in_curr_value_subarray = 
                                  $keys_in_current_value_subarray[0];
            }

            if ( is_array($value) && count($value) <= 0 ) {

                //An empty array. Validation failed.
                //No value in a valid where / having 
                //params array should be an empty array.
                $msg = "ERROR: Bad where param array with an empty sub-array"
                        . " with a key named '{$key}' supplied to "
                        . get_class($this) . '::' . __FUNCTION__ . '(...).' 
                        . PHP_EOL . 'The array passed to ' 
                        . get_class($this) . '::' . __FUNCTION__ . '(...):' 
                        . PHP_EOL . var_export($array, true). PHP_EOL;

                throw new ModelBadWhereOrHavingParamSuppliedException($msg);
            }

            if ( $key === 'col' && !is_string($value) ) {

                //if $key === 'col' then $value must be a string
                $msg = "ERROR: Bad where param array having an entry with a key"
                        . " named 'col' with a non-string value of "
                        . var_export($value, true) . PHP_EOL
                        . "inside the a sub-array in the array passed to "
                        . get_class($this) . '::' . __FUNCTION__ . '(...).' 
                        . PHP_EOL . var_export($array, true). PHP_EOL;

                throw new ModelBadWhereOrHavingParamSuppliedException($msg);
                
            } else if (
                    $key === 'operator' 
                    && !in_array( 
                            $value, array_keys(static::$_where_or_having_ops_2_dbms_ops)
                        )
            ) {
                //$key === 'operator' then $value must be in valid expected
                // operators
                $msg = "ERROR: Bad where param array having an entry with a key"
                        . " named 'operator' with a non-expected value of "
                        . PHP_EOL . var_export($value, true) . PHP_EOL
                        . "inside the array passed to " 
                        . get_class($this) . '::' . __FUNCTION__ . '(...).' 
                        . PHP_EOL . 'Below are the expected values for an array'
                        . ' entry with a key named \'operator\' ' . PHP_EOL 
                        . var_export(
                                 array_keys(static::$_where_or_having_ops_2_dbms_ops),
                                true
                            )
                        . PHP_EOL;

                throw new ModelBadWhereOrHavingParamSuppliedException($msg);
                
            } else if (
                    $key === 'val' &&
                    (
                        (
                            !is_numeric($value) 
                            && !is_string($value) 
                            && !is_array($value)
                            && !is_bool($value)
                        ) 
                        ||
                        (
                            is_string($value) && empty($value)
                        ) 
                        ||
                        (
                            is_array($value) && count($value) <= 0
                        )
                    )
            ) {
                //$key === 'val' must be either numeric, a non-empty string or 
                //a non-empty array or a boolean value 
                $msg = "ERROR: Bad where param array having an entry with a key"
                        . " named 'val' with a non-expected value of "
                        . PHP_EOL . var_export($value, true) . PHP_EOL
                        . "inside the array passed to "
                        . get_class($this) . '::' . __FUNCTION__ . '(...).' 
                        . PHP_EOL . 'Only numeric, non-empty string, boolean or'
                        . ' non-empty array values are allowed for an array entry'
                        . ' with a key named \'val\'.'. PHP_EOL;

                throw new ModelBadWhereOrHavingParamSuppliedException($msg);
                
            } else if (
                is_numeric($key) || $key === "OR" || substr($key, 0, 3) === "OR#"
            ) {
                $has_a_val_key = 
                        (is_array($value)) && array_key_exists('val', $value);

                $has_a_col_and_an_operator_key = 
                                    (is_array($value)) 
                                        && array_key_exists('col', $value) 
                                        && array_key_exists('operator', $value);
                                        
                if ( 
                	!is_array($value) 
                	&& $previous_key !== 'val' 
                	&& !( is_numeric($key) && is_numeric($previous_key) && !is_array($previous_value) ) 
                ) {
                    //$key is numeric or $key === 'OR' or starts with 'OR#' 
                    //then $value must be an array except, if the current key 
                    //is inside a sub-array referenced by a key named 'val' 
                    //ie. if this key is numeric and is inside an array referenced by a 
                    //key named 'val' 
                    //( 'val'=>array(...) is allowed when 'operator'=>'in' & 'operator'=>'not-in' )
                    $msg = "ERROR: Bad where param array having an entry with a"
                            . " key named '{$key}' with a non-expected value of"
                            . PHP_EOL . var_export($value, true) . PHP_EOL
                            . "inside the array passed to "
                            . get_class($this) . '::' . __FUNCTION__ . '(...).' 
                            . PHP_EOL . "Any array entry with a numeric key or "
                            . "a key named 'OR' or a key that starts with 'OR#'"
                            . " must have a value that is an array.". PHP_EOL;

                    throw new ModelBadWhereOrHavingParamSuppliedException($msg);
                    
                } else if (
                	substr($_1st_key_in_curr_value_subarray, 0, 3) === "OR#"
                        || $_1st_key_in_curr_value_subarray === "OR"                        
                ) {
                    //$key is numeric or $key === 'OR' or starts with 'OR#' then 
                    //$value must be an array whose first item's key 
                    //(is not 'OR' or starts with 'OR#')
                    $msg = "ERROR: Bad where param array having an entry with a"
                            . " key named '{$key}' with a non-expected value of"
                            . PHP_EOL . var_export($value, true) . PHP_EOL
                            . "inside the array passed to "
                            . get_class($this) . '::' . __FUNCTION__ . '(...).' 
                            . PHP_EOL . "The first key in any of the sub-arrays"
                            . " in the array passed to "
                            . get_class($this) . '::' . __FUNCTION__ . '(...) '
                            . "cannot start with 'OR' or 'OR#'.". PHP_EOL;

                    throw new ModelBadWhereOrHavingParamSuppliedException($msg);
                    
                } else if (
                        ( $has_a_col_and_an_operator_key || $has_a_val_key ) 
                        &&
                        count(
                            array_filter(
                                    array_keys($value),
                                    function($v) {
                                        return 
                                            is_numeric($v) 
                                            || 
                                            (
                                            	is_string($v)
                                            	&& !in_array( $v, array('col', 'operator', 'val') )
                                            )
                                            ;
                                    }
                                )
                        ) > 0
                ) {
                    //Failed Requirement below
                    //If any of the expected keys ('col', 'operator' or 'val') 
                    //is present, then no other type of key is allowed in the 
                    //particular sub-array
                    $msg = "ERROR: Incorect where condition definition in a"
                            . " sub-array referenced via a key named '{$key}'."
                            . " Sub-array:". PHP_EOL 
                            . var_export($value, true) . PHP_EOL
                            . "inside the array passed to "
                            . get_class($this) . '::' . __FUNCTION__ . '(...).' 
                            . PHP_EOL . "Because one or more of these keys"
                            . " ('col', 'operator' or 'val') are present," 
                            . PHP_EOL . "no other type of key is allowed in the"
                            . " array in which they are present.". PHP_EOL;

                    throw new ModelBadWhereOrHavingParamSuppliedException($msg);
                    
                } else if (
                        $has_a_col_and_an_operator_key 
                        && !$has_a_val_key 
                        && !in_array($value['operator'], array('is-null', 'not-null'))
                ) {
                    //Failed Requirement below
                    //If the $value array is has these 2 keys 'col' & 'operator' 
                    //the operator's value must be either 'is-null' or 'not-null'
                    $msg = "ERROR: Incorect where condition definition in a"
                            . " sub-array referenced via a key named '{$key}'. "
                            . PHP_EOL . var_export($value, true) . PHP_EOL
                            . "inside the array passed to "
                            . get_class($this) . '::' . __FUNCTION__ . '(...).' 
                            . PHP_EOL . 'A sub-array containing keys named'
                            . ' \'col\' and \'operator\' without a key named'
                            . ' \'val\' is valid if and only if the entry with'
                            . ' a key named \'operator\' has either a value of'
                            . ' \'is-null\' or \'not-null\' '. PHP_EOL;

                    throw new ModelBadWhereOrHavingParamSuppliedException($msg);
                    
                } else if (
                        $has_a_col_and_an_operator_key 
                        && $has_a_val_key 
                        && in_array($value['operator'], array('is-null', 'not-null'))
                ) {
                    //Failed Requirement below
                    //For any sub-array containing an item with a key named 'operator' 
                    //with a value of either 'not-null' or 'is-null', there must not be
                    //any item in that sub-array with a key named 'val', but there must
                    //be a corresponding item with a key named 'col' with a string value.
                    $msg = "ERROR: Incorect where condition definition in a"
                            . " sub-array referenced via a key named '{$key}'. "
                            . PHP_EOL . var_export($value, true) . PHP_EOL
                            . "inside the array passed to "
                            . get_class($this) . '::' . __FUNCTION__ . '(...).' 
                            . PHP_EOL . 'A sub-array containing a key named'
                            . ' \'operator\' with a value of \''
                            . $value['operator'].'\' cannot also contain a'
                            . ' key named \'val\'. Please remove the item'
                            . ' with the key named \'val\' from the sub-array.'
                            . PHP_EOL;

                    throw new ModelBadWhereOrHavingParamSuppliedException($msg);
                    
                } elseif ( !$has_a_col_and_an_operator_key && $has_a_val_key ) {

                    //Failed Requirement below
                    //Missing keys ('col' & 'operator') when key named 'val' 
                    //is present
                    $msg = "ERROR: Incorect where condition definition in a"
                            . " sub-array referenced via a key named '{$key}'. "
                            . PHP_EOL . var_export($value, true) . PHP_EOL
                            . "inside the array passed to "
                            . get_class($this) . '::' . __FUNCTION__ . '(...).' 
                            . PHP_EOL . 'A sub-array containing key named'
                            . ' \'val\' without two other entries with keys'
                            . ' named \'col\' and \'operator\' '
                            . PHP_EOL;

                    throw new ModelBadWhereOrHavingParamSuppliedException($msg);
                    
                } else if (
                        $has_a_col_and_an_operator_key 
                        && $has_a_val_key 
                        && in_array($value['operator'], array('in', 'not-in'))
                        && !is_array($value['val'])
                        && !is_string($value['val'])
                        && !is_numeric($value['val'])
                ) {
                    //Failed Requirement below
                    //For any sub-array containing an item with a key named 'operator' 
                    //with a value of either 'in' or 'not-in', there must be another
                    //item in that sub-array with a key named 'val' with either a 
                    //numeric, string or an array value.
                    $msg = "ERROR: Incorect where condition definition in a"
                            . " sub-array referenced via a key named '{$key}'. "
                            . PHP_EOL . var_export($value, true) . PHP_EOL
                            . "inside the array passed to "
                            . get_class($this) . '::' . __FUNCTION__ . '(...).' 
                            . PHP_EOL . 'A sub-array containing a key named'
                            . ' \'operator\' with a value of \''
                            . $value['operator'].'\' contains an item with'
                            . ' a key named \'val\' whose value '
                            . var_export($value['val'], true)
                            . ' is not numeric or a string or an array. Please supply a'
                            . ' numeric or an array or a string value for the item with'
                            . ' the key named \'val\' in the sub-array.'
                            . PHP_EOL;

                    throw new ModelBadWhereOrHavingParamSuppliedException($msg);
                    
                } else if (
                        $has_a_col_and_an_operator_key 
                        && $has_a_val_key 
                        && in_array( $value['operator'], array('like', 'not-like') )
                        && !is_string($value['val'])
                ) {
                    //Failed Requirement below
                    //For any sub-array containing an item with a key named 'operator' 
                    //with a value of either 'like' or 'not-like', there must be another
                    //item in that sub-array with a key named 'val' with a string value.
                    $msg = "ERROR: Incorect where condition definition in a"
                            . " sub-array referenced via a key named '{$key}'. "
                            . PHP_EOL . var_export($value, true) . PHP_EOL
                            . "inside the array passed to "
                            . get_class($this) . '::' . __FUNCTION__ . '(...).' 
                            . PHP_EOL . 'A sub-array containing a key named'
                            . ' \'operator\' with a value of \''
                            . $value['operator'].'\' contains an item with'
                            . ' a key named \'val\' whose value '
                            . var_export($value['val'], true)
                            . ' is not a string. Please supply a'
                            . ' string value for the item with the'
                            . ' key named \'val\' in the sub-array.'
                            . PHP_EOL;

                    throw new ModelBadWhereOrHavingParamSuppliedException($msg);
                    
                } else if (
                        $has_a_col_and_an_operator_key 
                        && $has_a_val_key 
                        && in_array( $value['operator'], array('=', '>', '>=', '<', '<=', '!=') )
                        && !is_string($value['val'])
                        && !is_numeric($value['val'])
                ) {
                    //Failed Requirement below
                    //For any sub-array containing an item with a key named 'operator' 
                    //with any of the following values: '=', '>', '>=', '<', '<=' or '!=', 
                    //there must be another item in that sub-array with a key named 'val' 
                    //with either a numeric or string value.
                    $msg = "ERROR: Incorect where condition definition in a"
                            . " sub-array referenced via a key named '{$key}'. "
                            . PHP_EOL . var_export($value, true) . PHP_EOL
                            . "inside the array passed to "
                            . get_class($this) . '::' . __FUNCTION__ . '(...).' 
                            . PHP_EOL . 'A sub-array containing a key named'
                            . ' \'operator\' with a value of \''
                            . $value['operator'].'\' contains an item with'
                            . ' a key named \'val\' whose value '
                            . var_export($value['val'], true)
                            . ' is not a string or numeric. Please supply a'
                            . ' numeric or string value for the item with the'
                            . ' key named \'val\' in the sub-array.'
                            . PHP_EOL;

                    throw new ModelBadWhereOrHavingParamSuppliedException($msg);
                }
                
            } else if (
                    !is_numeric($key) 
                    && !in_array($key, array('col', 'operator', 'val', 'OR')) 
                    && substr($key, 0, 3) !== "OR#"
            ) {
                //The key is not in the range of allowable values
                $msg = "ERROR: Bad where param array having an entry with a"
                        . " non-expected key named '{$key}' with a value of "
                        . PHP_EOL . var_export($value, true) . PHP_EOL
                        . "inside the array passed to "
                        . get_class($this) . '::' . __FUNCTION__ . '(...).' 
                        . PHP_EOL . "Allowed keys are as follows:" . PHP_EOL
                        . "Any of these keys ('col', 'operator', 'val' or 'OR')"
                        . " or the key must be a numeric key or a string that"
                        . " starts with 'OR#'.". PHP_EOL;

                throw new ModelBadWhereOrHavingParamSuppliedException($msg);
            }
            
            $previous_key = $key;
            $previous_value = $value;
            
        }//foreach ($iterator as $key => $value)
        
        //if we got this far, then the array must be valid
        return true;
    }
    
    /**
     * 
     * This method builds a valid WHERE or HAVING clause (excluding the WHERE 
     * and HAVING keywords) that can be appended after the WHERE or HAVING
     * section of any SELECT, INSERT, UPDATE or DELETE sql statements.
     * 
     * It returns a two element array with the first item being the clause and
     * the second item being an array of values to be bound to the clause (or
     * an empty array if there are no parameters to be bound).
     * 
     * For Example:
     *  Given the array below
     * $data = [
     *     'where' => 
     *         [
     *             0 => [ 'col' => 'col_1', 'operator' => '<', 'val' => 58],
     *             1 => [ 'col' => 'col_2', 'operator' => '<', 'val' => 68],
     *             [
     *                 0 => [ 'col' => 'col_11', 'operator' => '>', 'val' => 581],
     *                 1 => [ 'col' => 'col_21', 'operator' => '>', 'val' => 681],
     *                 'OR#3' => [
     *                     0 => [ 'col' => 'col_12', 'operator' => '<', 'val' => 582],
     *                     1 => [ 'col' => 'col_22', 'operator' => '<', 'val' => 682]
     *                 ],
     *                 2 => [ 'col' => 'col_31', 'operator' => '>=', 'val' => 583],
     *                 'OR#4' => [
     *                     0 => [ 'col' => 'col_4', 'operator' => '=', 'val' => 584],
     *                     1 => [ 'col' => 'col_5', 'operator' => '=', 'val' => 684],
     *                 ]
     *             ],
     *             3 => [ 'col' => 'column_name_44', 'operator' => '<', 'val' => 777],
     *             4 => [ 'col' => 'column_name_55', 'operator' => 'is-null'],
     *         ]
     * ];
     * 
     * $this->_getWhereOrHavingClauseWithParams($data['where']); 
     * 
     * will return an array whose first item is the string below:
     *  
     *      "(
     *      	col_1 > :_1_ 
     *      	AND
     *      	col_2 > :_2_ 
     *      	AND
     *      	(
     *      		col_11 > :_3_ 
     *      		AND
     *      		col_21 > :_4_ 
     *      		OR
     *      		(
     *      			col_12 > :_5_ 
     *      			AND
     *      			col_22 > :_6_ 
     *      		)
     *      		AND
     *      		col_31 >= :_7_ 
     *      		OR
     *      		(
     *      			col_4 = :_8_ 
     *      			AND
     *      			col_5 = :_9_ 
     *      		)
     *      	)
     *      	AND
     *      	column_name_44 > :_10_ 
     *      	AND
     *      	column_name_55 IS NULL
     *      )";
     * 
     *  and whose second item is the array below:
     * 
     *  [
     *    '_1_' => 58, '_2_' => 68, '_3_' => 581, '_4_' => 681, '_5_' => 582,
     *    '_6_' => 682, '_7_' => 583, '_8_' => 584, '_9_' => 684, '_10_' => 777
     *  ];
     * 
     * VERY STRONGLY SUGGESTED USAGE PATTERN:
     * 
     *      if( $this->_validateWhereOrHavingParamsArray($data['where']) ) 
     *      {
     *          $clause_and_params = 
     *              $this->_getWhereOrHavingClauseWithParams($data['where']);
     *      
     *          $sql = "SELECT * FROM some_table WHERE " . $clause_and_params[0];
     *          $params_2_bind_2_sql = $clause_and_params[1];
     * 
     *          // a PDO connection
     *          $pdo = new PDO(...);
     *      
     *          // prepare the statment
     *          $sth = $pdo->prepare($sql);
     *      
     *          // bind the values and execute
     *          $sth->execute($params_2_bind_2_sql);
     *      
     *          // get the results back as an associative array
     *          $result = $sth->fetch(PDO::FETCH_ASSOC);
     *      }
     * 
     * Callers of this method should first validate $array via
     * $this->_validateWhereOrHavingParamsArray($array) before calling this 
     * method (like in the usage pattern code snippet above).
     * 
     * @param array $array an array of where or having condition(s) definition as
     *                     specified in the params documentation of the fetch* methods
     * @param int $indent_level the number of tab characters to add to the sql clause
     * @return array an array of two items, the first is the having or where 
     *               clause sql string and the second item is an associative
     *               array of parameters to bind to the query
     * @throws ModelBadWhereOrHavingParamSuppliedException
     * 
     * @see \GDAO\Model::_validateWhereOrHavingParamsArray(array $array)
     * 
     */
    protected function _getWhereOrHavingClauseWithParams(array &$array, $indent_level=0) {

        static $bind_params_index;

        if( !isset($bind_params_index) ) {

            $bind_params_index = 0;
        }

        $i = 0;
        $result_sql = '';
        $result_bind_params = array();
        $result_sql .= str_repeat("\t", $indent_level). '('. PHP_EOL;

        foreach ( $array as $key => $value ) {

            if ( is_numeric($key) || $key === "OR" || substr($key, 0, 3) === "OR#" ) {

                $and_or = ( is_numeric($key) ) ? 'AND' : 'OR' ;

                if( $i > 0 ) {

                    //not the first item
                    $result_sql .= str_repeat("\t", ($indent_level + 1) ). $and_or. PHP_EOL;
                }

                if( is_array($value) ) {

                    $has_a_val_key = 
                        (is_array($value)) && array_key_exists('val', $value);

                    $has_a_col_and_an_operator_key = 
                        (is_array($value)) 
                        && array_key_exists('col', $value) 
                        && array_key_exists('operator', $value);

                    if( $has_a_col_and_an_operator_key ) {
                        
                        $operator_is_in_or_not_in = 
                            in_array($value['operator'], array('not-in', 'in'));

                        //quote $value['col'] and $value['val'] as needed
                        $db_specific_operator = 
                            static::$_where_or_having_ops_2_dbms_ops[$value['operator']];

                        if( 
                            !$has_a_val_key 
                            ||  in_array( $value['operator'], array('not-null', 'is-null') ) 
                        ) {
                            $result_sql .= str_repeat("\t", ($indent_level + 1) )
                                     . "{$value['col']} $db_specific_operator" . PHP_EOL;

                        } else if( $has_a_val_key ) {

                            //$value['val'] should be pdo quoted.
                            $quoted_val = '';
                            
                            if (is_array($value['val'])) {

                                //quote all string values
                                array_walk(
                                        
                                    $value['val'],

                                    function(&$val, $key, $pdo) {
                                        $val = 
                                            (is_string($val)) 
                                                ? $pdo->quote($val) : $val;
                                    },

                                    $this->getPDO()
                                );

                                $quoted_val = 
                                    " (" . implode(',', $value['val']) . ") ";
                            } else {

                                $quoted_val = 
                                    (
                                        !$operator_is_in_or_not_in 
                                        && is_string($value['val'])
                                    ) ? 
                                        $this->getPDO()->quote($value['val']) 
                                                                : $value['val'];
                                
                                if($operator_is_in_or_not_in) {
                                    
                                    if(
                                        is_numeric($value['val'])
                                        ||
                                        (
                                            is_string($value['val'])
                                            && strpos($value['val'], '(') === false
                                            && strpos($value['val'], ')') === false
                                        )
                                    ) {
                                        $quoted_val = "($quoted_val)";
                                    }
                                }
                            }
                            
                            $bind_params_index++;
                            
                            if( !$operator_is_in_or_not_in ) {
                                
                                $result_sql .= str_repeat("\t", ($indent_level + 1) )
                                         . "{$value['col']} $db_specific_operator :_{$bind_params_index}_ " 
                                         . PHP_EOL;
                                $result_bind_params["_{$bind_params_index}_"] = $quoted_val;
                                
                            } else {
                                //no need for named place holder just place the
                                //quoted val directly.
                                $result_sql .= str_repeat("\t", ($indent_level + 1) )
                                         . "{$value['col']} $db_specific_operator $quoted_val " 
                                         . PHP_EOL;
                            }
                        }
                    } else {
                        //a sub-array of more conditions, recurse
                        $full_result = $this->_getWhereOrHavingClauseWithParams($value, ($indent_level + 1) );
                        
                        $result_sql .= $full_result[0];
                        $result_bind_params = array_merge($result_bind_params, $full_result[1]);
                    }
                } else {
                    //throw exception badly structured array
                    $msg = "ERROR: Bad where param array having an entry with a"
                           . " key named '$key' with a non-expected value of "
                           . PHP_EOL . var_export($value, true) . PHP_EOL
                           . "inside the array: "
                           . PHP_EOL . var_export($array, true) . PHP_EOL
                           . " passed to " 
                           . get_class($this) . '::' . __FUNCTION__ . '(...).' 
                           . PHP_EOL;

                    throw new ModelBadWhereOrHavingParamSuppliedException($msg);
                }
            }
            $i++;
        }

        return array( 
                    $result_sql.str_repeat("\t", $indent_level) . ')' . PHP_EOL,
                    $result_bind_params
               );
    }
    
    /**
     * 
     * Fetch a collection (an instance of GDAO\Model\Collection or any of its 
     * sub-classes) of records (instances of \GDAO\Model\Record or any of its 
     * sub-classes) [Eager Loading should be implemented here].
     * 
     * @param array $params an array of parameters for the fetch with the keys (case-sensitive) below
     * 
     *  `relations_to_include`
     *      : (array) An array of relation names as defined in any or all of 
     *        \GDAO\Model->_has_one_relationships, 
     *        \GDAO\Model->_has_many_relationships,
     *        \GDAO\Model->_belongs_to_relationships and 
     *        \GDAO\Model->_has_many_through_relationships. 
     *        Eager-fetch related rows of data for each relation name.
     * 
     *        NOTE: each key in the \GDAO\Model->_*_relationships arrays is a 
     *              relation name. Eg. array_keys($this->_has_one_relationships)
     *              returns an array of Has-One relation name(s) for a model.
     * 
     *        NOTE: Implementers of this class should make the retreived related
     *              data accessible in each record via a property named with the
     *              same name as the relation name. For example, if there exists
     *              $this->_has_one_relationships['comments'], the retreived 
     *              comments for each record returned by this fetch method should
     *              be accessible via $record->comments. Where $record is a 
     *              reference to one of the records returned by this method.
     *
     *  `distinct`
     *      : (bool) True if the DISTINCT keyword should be added to the query, 
     *        else false if the DISTINCT keyword should be ommitted. 
     * 
     *        NOTE: If `distinct` is not set/specified, implementers of this class 
     *              should give it a default value of false.
     * 
     *  `cols`
     *      : (array) An array of the name(s) of column(s) to be returned.
     *        Expressions like 'COUNT(col_name) AS some_col' are allowed as a column name.
     *        Return only these columns.
     *        Eg. to generate SELECT col_1, col_2, col_3 ......
     *        use: 
     *          [
     *              'cols' => [ 'col_1', 'col_2', 'col_3' ]
     *          ]
     * 
     *        NOTE: If `cols` is not set/specified or is assigned an empty array
     *              value, implementers of this class should select all columns
     *              from the table associated with the model 
     *              (ie. SELECT * FROM models_table ..... ).
     * 
     *  `where`
     *      : (array) an array of parameters for building a WHERE clause, 
     *        Eg. to generate 
     *          WHERE column_name_1 > 58 AND column_name_2 > 58
     *             OR (column_name_1 < 58 AND column_name_2 < 58)
     *            AND column_name_3 >= 58
     *             OR (column_name_4 = 58 AND column_name_5 = 58)
     *        use:
     *          [
     *              'where' => 
     *                [
     *                   [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                   [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                   'OR'=> [
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR#2'=> [
     *                              [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                              [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ],
     *                          ]
     *                ]
     *          ]
     * 
     *        The 'operator' could be assigned any one of these values:
     *          [ 
     *              '=', '>', '>=', '<', '<=', 'in', 'is-null', 'like',  
     *              '!=', 'not-in', 'not-like', 'not-null'
     *          ]
     *    
     *        NOTE: To add OR conditions add an OR key. For multiple OR conditions
     *              append a # and a unique string after the # so that the 
     *              subsequent OR conditions do not override the previous ones.
     *              Implementers of this class just need to check if an array 
     *              key inside the 'where' array starts with OR or OR# in order
     *              to add the condition as an OR condition. 
     *        NOTE: Consumers of any implementation of this class should be careful 
     *              not to make the first key in the 'where' array or the first 
     *              key in any of the array(s) inside the 'where' array an 'OR' 
     *              or 'OR#...' key. Below are some bad examples and their
     *              corrected equivalents
     * 
     *          #BAD 1 - first key in $array['where'] is 'OR' 
     *          $array = [
     *              'where' => 
     *                [
     *                   'OR'=> [ //offending entry. should not be the first item here
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                ]
     *          ]
     * 
     *          #GOOD 1 - moved the entry with 'OR' key away from first position 
     *                    in $array['where']
     *          $array = [
     *              'where' => 
     *                [
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR'=> [ //Fixed. No longer the first item in $array['where']
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                ]
     *          ]
     *          
     *          #BAD 2 - first key in $array['where']['OR'] is 'OR' 
     *          $array = [
     *             'where' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             'OR'=> [ //offending entry. should not be the first item here
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *          
     *          #GOOD 2 - moved the entry with 'OR' key away from first position 
     *                    in $array['where']['OR'] 
     *          $array = [
     *             'where' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             'OR'=> [ //Fixed. No longer the first item in $array['where']['OR']
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *              
     *        NOTE: Implementers of this class should convert each operator to the 
     *              DB specific operator. Eg. for MySQL, convert 'not-null' to 
     *              'IS NOT NULL'.
     *        NOTE: For any sub-array containing an item with a key named 'operator' 
     *              with a value of either 'not-null' or 'is-null', there must not be
     *              any item in that sub-array with a key named 'val', but there must
     *              be a corresponding item with a key named 'col' with a string value.
     *        NOTE: The operators: 'in' and 'not-in' allow 'val' to be set to an array
     *              or string value. If 'val' is a string, it must be a valid
     *              value that a NOT IN or IN operator expects including the opening
     *              and closing brackets. Eg. "( 1, 2, 3 )" or "( '4', '5', '6' )".
     *        NOTE: Implementers of this class can validate the structure of 
     *              this sub-array by passing it to
     *              \GDAO\Model::_validateWhereOrHavingParamsArray(array $array)
     *        NOTE: Implementers of this class can generate a WHERE clause 
     *              (excluding the WHERE keyword) from this sub-array by passing 
     *              it to \GDAO\Model::_getWhereOrHavingClauseWithParams(...)
     *
     *  `group`
     *      : (array) An array of the name(s) of column(s) which the results 
     *        will be grouped by.
     *        Eg. to generate ' GROUP BY column_name_1, column_name_2 '
     *        use the array below:
     *          [
     *              'group' => ['column_name_1', 'column_name_2']
     *          ]
     * 
     *  `having`
     *      : (array) An array of parameters for building a HAVING clause.
     *        Eg. to generate 
     *          HAVING column_name_1 > 58 AND column_name_2 > 58
     *              OR (column_name_1 < 58 AND column_name_2 < 58)
     *             AND column_name_3 >= 58
     *              OR (column_name_4 = 58 AND column_name_5 = 58)
     *        use:
     *          [
     *              'having' => 
     *                [
     *                   [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                   [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                   'OR'=> [
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR#2'=> [
     *                              [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                              [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ],
     *                          ]
     *                ]
     *          ]
     * 
     *        The 'operator' could be assigned any one of these values:
     *          [ 
     *              '=', '>', '>=', '<', '<=', 'in', 'is-null', 'like',  
     *              '!=', 'not-in', 'not-like', 'not-null'
     *          ]
     *    
     *        NOTE: To add OR conditions add an OR key. For multiple OR conditions
     *              append a # and a unique string after the # so that the 
     *              subsequent OR conditions do not override the previous ones.
     *              Implementers of this class just need to check if an array 
     *              key inside the 'having' array starts with OR or OR# in order
     *              to add the condition as an OR condition. 
     *        NOTE: Consumers of any implementation of this class should be careful 
     *              not to make the first key in the 'having' array or the first 
     *              key in any of the array(s) inside the 'having' array an 'OR' 
     *              or 'OR#...' key. Below are some bad examples and their
     *              corrected equivalents
     * 
     *          #BAD 1 - first key in $array['having'] is 'OR' 
     *          $array = [
     *              'having' => 
     *                [
     *                   'OR'=> [ //offending entry. should not be the first item here
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                ]
     *          ]
     * 
     *          #GOOD 1 - moved the entry with 'OR' key away from first position 
     *                    in $array['having']
     *          $array = [
     *              'having' => 
     *                [
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR'=> [ //Fixed. No longer the first item in $array['having']
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                ]
     *          ]
     *          
     *          #BAD 2 - first key in $array['having']['OR'] is 'OR' 
     *          $array = [
     *             'having' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             'OR'=> [ //offending entry. should not be the first item here
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *          
     *          #GOOD 2 - moved the entry with 'OR' key away from first position 
     *                    in $array['having']['OR'] 
     *          $array = [
     *             'having' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             'OR'=> [ //Fixed. No longer the first item in $array['having']['OR']
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *              
     *        NOTE: Implementers of this class should convert each operator to the 
     *              DB specific operator. Eg. for MySQL, convert 'not-null' to 
     *              'IS NOT NULL'.
     *        NOTE: For any sub-array containing an item with a key named 'operator' 
     *              with a value of either 'not-null' or 'is-null', there must not be
     *              any item in that sub-array with a key named 'val', but there must
     *              be a corresponding item with a key named 'col' with a string value.
     *        NOTE: The operators: 'in' and 'not-in' allow 'val' to be set to an array
     *              or string value. If 'val' is a string, it must be a valid
     *              value that a NOT IN or IN operator expects including the opening
     *              and closing brackets. Eg. "( 1, 2, 3 )" or "( '4', '5', '6' )".
     *        NOTE: Implementers of this class can validate the structure of 
     *              this sub-array by passing it to
     *              \GDAO\Model::_validateWhereOrHavingParamsArray(array $array)
     *        NOTE: Implementers of this class can generate a HAVING clause 
     *              (excluding the HAVING keyword) from this sub-array by passing 
     *              it to \GDAO\Model::_getWhereOrHavingClauseWithParams(...)
     * 
     *  `order`
     *      : (array) an array of parameters for building an ORDER BY clause.
     *        The values are the column names to ORDER BY.
     *        Eg. to generate 'ORDER BY col_1 ASC, col_2 DESC' use:
     *          [
     *              'order' => [ 'col_1 ASC', 'col_2 DESC' ] 
     *          ]
     * 
     *  `limit_offset`
     *      : (int) Limit offset. Offset of the first row to return
     * 
     *        NOTE: Implementers of this class should use the `limit_offset` 
     *              value with the appropriate limit & offset mechanism for the 
     *              DB system their implementation supports. 
     *              Eg. for MySQL: 
     *                      LIMIT $params['limit_size']
     *                      OFFSET $params['limit_offset']
     * 
     *                  for MSSQL Server:
     *                      OFFSET $params['limit_offset'] ROWS
     *                      FETCH NEXT $params['limit_size'] ROWS ONLY
     * 
     *  `limit_size`
     *      : (int) Limit to a count of this many records.
     * 
     *        NOTE: Implementers of this class should use the `limit_size` 
     *              value with the appropriate limit & offset mechanism for the 
     *              DB system their implementation supports. 
     *              Eg. for MySQL: 
     *                      LIMIT $params['limit_size']
     *                      OFFSET $params['limit_offset']
     * 
     *                  for MSSQL Server:
     *                      OFFSET $params['limit_offset'] ROWS
     *                      FETCH NEXT $params['limit_size'] ROWS ONLY
     * 
     * @return GDAO\Model\Collection 
     * 
     */
    public function fetchAll(array $params = array()) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new ModelMustImplementMethodException($msg);
    }

    /**
     * 
     * Fetch an array of records (instances of \GDAO\Model\Record or any of its 
     * sub-classes) [Eager Loading should be considered here].
     * 
     * @param array $params an array of parameters for the fetch with the keys (case-sensitive) below
     * 
     *  `relations_to_include`
     *      : (array) An array of relation names as defined in any or all of 
     *        \GDAO\Model->_has_one_relationships, 
     *        \GDAO\Model->_has_many_relationships,
     *        \GDAO\Model->_belongs_to_relationships and 
     *        \GDAO\Model->_has_many_through_relationships. 
     *        Eager-fetch related rows of data for each relation name.
     * 
     *        NOTE: each key in the \GDAO\Model->_*_relationships arrays is a 
     *              relation name. Eg. array_keys($this->_has_one_relationships)
     *              returns an array of Has-One relation name(s) for a model.
     * 
     *        NOTE: Implementers of this class should make the retreived related
     *              data accessible in each record via a property named with the
     *              same name as the relation name. For example, if there exists
     *              $this->_has_one_relationships['comments'], the retreived 
     *              comments for each record returned by this fetch method should
     *              be accessible via $record->comments. Where $record is a 
     *              reference to one of the records returned by this method.
     *
     *  `distinct`
     *      : (bool) True if the DISTINCT keyword should be added to the query, 
     *        else false if the DISTINCT keyword should be ommitted. 
     * 
     *        NOTE: If `distinct` is not set/specified, implementers of this class 
     *              should give it a default value of false.
     * 
     *  `cols`
     *      : (array) An array of the name(s) of column(s) to be returned.
     *        Expressions like 'COUNT(col_name) AS some_col' are allowed as a column name.
     *        Return only these columns.
     *        Eg. to generate SELECT col_1, col_2, col_3 ......
     *        use: 
     *          [
     *              'cols' => [ 'col_1', 'col_2', 'col_3' ]
     *          ]
     * 
     *        NOTE: If `cols` is not set/specified or is assigned an empty array
     *              value, implementers of this class should select all columns
     *              from the table associated with the model 
     *              (ie. SELECT * FROM models_table ..... ).
     * 
     *  `where`
     *      : (array) an array of parameters for building a WHERE clause, 
     *        Eg. to generate 
     *          WHERE column_name_1 > 58 AND column_name_2 > 58
     *             OR (column_name_1 < 58 AND column_name_2 < 58)
     *            AND column_name_3 >= 58
     *             OR (column_name_4 = 58 AND column_name_5 = 58)
     *        use:
     *          [
     *              'where' => 
     *                [
     *                   [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                   [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                   'OR'=> [
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR#2'=> [
     *                              [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                              [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ],
     *                          ]
     *                ]
     *          ]
     * 
     *        The 'operator' could be assigned any one of these values:
     *          [ 
     *              '=', '>', '>=', '<', '<=', 'in', 'is-null', 'like',  
     *              '!=', 'not-in', 'not-like', 'not-null'
     *          ]
     *    
     *        NOTE: To add OR conditions add an OR key. For multiple OR conditions
     *              append a # and a unique string after the # so that the 
     *              subsequent OR conditions do not override the previous ones.
     *              Implementers of this class just need to check if an array 
     *              key inside the 'where' array starts with OR or OR# in order
     *              to add the condition as an OR condition. 
     *        NOTE: Consumers of any implementation of this class should be careful 
     *              not to make the first key in the 'where' array or the first 
     *              key in any of the array(s) inside the 'where' array an 'OR' 
     *              or 'OR#...' key. Below are some bad examples and their
     *              corrected equivalents
     * 
     *          #BAD 1 - first key in $array['where'] is 'OR' 
     *          $array = [
     *              'where' => 
     *                [
     *                   'OR'=> [ //offending entry. should not be the first item here
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                ]
     *          ]
     * 
     *          #GOOD 1 - moved the entry with 'OR' key away from first position 
     *                    in $array['where']
     *          $array = [
     *              'where' => 
     *                [
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR'=> [ //Fixed. No longer the first item in $array['where']
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                ]
     *          ]
     *          
     *          #BAD 2 - first key in $array['where']['OR'] is 'OR' 
     *          $array = [
     *             'where' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             'OR'=> [ //offending entry. should not be the first item here
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *          
     *          #GOOD 2 - moved the entry with 'OR' key away from first position 
     *                    in $array['where']['OR'] 
     *          $array = [
     *             'where' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             'OR'=> [ //Fixed. No longer the first item in $array['where']['OR']
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *              
     *        NOTE: Implementers of this class should convert each operator to the 
     *              DB specific operator. Eg. for MySQL, convert 'not-null' to 
     *              'IS NOT NULL'.
     *        NOTE: For any sub-array containing an item with a key named 'operator' 
     *              with a value of either 'not-null' or 'is-null', there must not be
     *              any item in that sub-array with a key named 'val', but there must
     *              be a corresponding item with a key named 'col' with a string value.
     *        NOTE: The operators: 'in' and 'not-in' allow 'val' to be set to an array
     *              or string value. If 'val' is a string, it must be a valid
     *              value that a NOT IN or IN operator expects including the opening
     *              and closing brackets. Eg. "( 1, 2, 3 )" or "( '4', '5', '6' )".
     *        NOTE: Implementers of this class can validate the structure of 
     *              this sub-array by passing it to
     *              \GDAO\Model::_validateWhereOrHavingParamsArray(array $array)
     *        NOTE: Implementers of this class can generate a WHERE clause 
     *              (excluding the WHERE keyword) from this sub-array by passing 
     *              it to \GDAO\Model::_getWhereOrHavingClauseWithParams(...)
     * 
     *  `group`
     *      : (array) An array of the name(s) of column(s) which the results 
     *        will be grouped by.
     *        Eg. to generate ' GROUP BY column_name_1, column_name_2 '
     *        use the array below:
     *          [
     *              'group' => ['column_name_1', 'column_name_2']
     *          ]
     * 
     *  `having`
     *      : (array) An array of parameters for building a HAVING clause.
     *        Eg. to generate 
     *          HAVING column_name_1 > 58 AND column_name_2 > 58
     *              OR (column_name_1 < 58 AND column_name_2 < 58)
     *             AND column_name_3 >= 58
     *              OR (column_name_4 = 58 AND column_name_5 = 58)
     *        use:
     *          [
     *              'having' => 
     *                [
     *                   [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                   [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                   'OR'=> [
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR#2'=> [
     *                              [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                              [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ],
     *                          ]
     *                ]
     *          ]
     * 
     *        The 'operator' could be assigned any one of these values:
     *          [ 
     *              '=', '>', '>=', '<', '<=', 'in', 'is-null', 'like',  
     *              '!=', 'not-in', 'not-like', 'not-null'
     *          ]
     *    
     *        NOTE: To add OR conditions add an OR key. For multiple OR conditions
     *              append a # and a unique string after the # so that the 
     *              subsequent OR conditions do not override the previous ones.
     *              Implementers of this class just need to check if an array 
     *              key inside the 'having' array starts with OR or OR# in order
     *              to add the condition as an OR condition. 
     *        NOTE: Consumers of any implementation of this class should be careful 
     *              not to make the first key in the 'having' array or the first 
     *              key in any of the array(s) inside the 'having' array an 'OR' 
     *              or 'OR#...' key. Below are some bad examples and their
     *              corrected equivalents
     * 
     *          #BAD 1 - first key in $array['having'] is 'OR' 
     *          $array = [
     *              'having' => 
     *                [
     *                   'OR'=> [ //offending entry. should not be the first item here
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                ]
     *          ]
     * 
     *          #GOOD 1 - moved the entry with 'OR' key away from first position 
     *                    in $array['having']
     *          $array = [
     *              'having' => 
     *                [
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR'=> [ //Fixed. No longer the first item in $array['having']
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                ]
     *          ]
     *          
     *          #BAD 2 - first key in $array['having']['OR'] is 'OR' 
     *          $array = [
     *             'having' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             'OR'=> [ //offending entry. should not be the first item here
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *          
     *          #GOOD 2 - moved the entry with 'OR' key away from first position 
     *                    in $array['having']['OR'] 
     *          $array = [
     *             'having' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             'OR'=> [ //Fixed. No longer the first item in $array['having']['OR']
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *              
     *        NOTE: Implementers of this class should convert each operator to the 
     *              DB specific operator. Eg. for MySQL, convert 'not-null' to 
     *              'IS NOT NULL'.
     *        NOTE: For any sub-array containing an item with a key named 'operator' 
     *              with a value of either 'not-null' or 'is-null', there must not be
     *              any item in that sub-array with a key named 'val', but there must
     *              be a corresponding item with a key named 'col' with a string value.
     *        NOTE: The operators: 'in' and 'not-in' allow 'val' to be set to an array
     *              or string value. If 'val' is a string, it must be a valid
     *              value that a NOT IN or IN operator expects including the opening
     *              and closing brackets. Eg. "( 1, 2, 3 )" or "( '4', '5', '6' )".
     *        NOTE: Implementers of this class can validate the structure of 
     *              this sub-array by passing it to
     *              \GDAO\Model::_validateWhereOrHavingParamsArray(array $array)
     *        NOTE: Implementers of this class can generate a HAVING clause 
     *              (excluding the HAVING keyword) from this sub-array by passing 
     *              it to \GDAO\Model::_getWhereOrHavingClauseWithParams(...)
     * 
     *  `order`
     *      : (array) an array of parameters for building an ORDER BY clause.
     *        The values are the column names to ORDER BY.
     *        Eg. to generate 'ORDER BY col_1 ASC, col_2 DESC' use:
     *          [
     *              'order' => [ 'col_1 ASC', 'col_2 DESC' ] 
     *          ]
     * 
     *  `limit_offset`
     *      : (int) Limit offset. Offset of the first row to return
     * 
     *        NOTE: Implementers of this class should use the `limit_offset` 
     *              value with the appropriate limit & offset mechanism for the 
     *              DB system their implementation supports. 
     *              Eg. for MySQL: 
     *                      LIMIT $params['limit_size']
     *                      OFFSET $params['limit_offset']
     * 
     *                  for MSSQL Server:
     *                      OFFSET $params['limit_offset'] ROWS
     *                      FETCH NEXT $params['limit_size'] ROWS ONLY
     * 
     *  `limit_size`
     *      : (int) Limit to a count of this many records.
     * 
     *        NOTE: Implementers of this class should use the `limit_size` 
     *              value with the appropriate limit & offset mechanism for the 
     *              DB system their implementation supports. 
     *              Eg. for MySQL: 
     *                      LIMIT $params['limit_size']
     *                      OFFSET $params['limit_offset']
     * 
     *                  for MSSQL Server:
     *                      OFFSET $params['limit_offset'] ROWS
     *                      FETCH NEXT $params['limit_size'] ROWS ONLY
     * 
     * @return array of records (instances of \GDAO\Model\Record or any of its 
     *               sub-classes).
     * 
     */
    public abstract function fetchAllAsArray(array $params = array());

    /**
     * 
     * Fetch an array of db data. Each record is an associative array and not an
     * instance of \GDAO\Model\Record [Eager Loading should be considered here].
     * 
     * @param array $params an array of parameters for the fetch with the keys (case-sensitive) below
     * 
     *  `relations_to_include`
     *      : (array) An array of relation names as defined in any or all of 
     *        \GDAO\Model->_has_one_relationships, 
     *        \GDAO\Model->_has_many_relationships,
     *        \GDAO\Model->_belongs_to_relationships and 
     *        \GDAO\Model->_has_many_through_relationships. 
     *        Eager-fetch related rows of data for each relation name.
     * 
     *        NOTE: each key in the \GDAO\Model->_*_relationships arrays is a 
     *              relation name. Eg. array_keys($this->_has_one_relationships)
     *              returns an array of Has-One relation name(s) for a model.
     * 
     *        NOTE: Implementers of this class should make the retreived related
     *              data accessible in each record via an array key named with the
     *              same name as the relation name. For example, if there exists
     *              $this->_has_one_relationships['comments'], the retreived 
     *              comments for each record returned by this fetch method should
     *              be accessible via $record['comments']. Where $record is a 
     *              reference to one of the records returned by this method.
     *
     *  `distinct`
     *      : (bool) True if the DISTINCT keyword should be added to the query, 
     *        else false if the DISTINCT keyword should be ommitted. 
     * 
     *        NOTE: If `distinct` is not set/specified, implementers of this class 
     *              should give it a default value of false.
     * 
     *  `cols`
     *      : (array) An array of the name(s) of column(s) to be returned.
     *        Expressions like 'COUNT(col_name) AS some_col' are allowed as a column name.
     *        Return only these columns.
     *        Eg. to generate SELECT col_1, col_2, col_3 ......
     *        use: 
     *          [
     *              'cols' => [ 'col_1', 'col_2', 'col_3' ]
     *          ]
     * 
     *        NOTE: If `cols` is not set/specified or is assigned an empty array
     *              value, implementers of this class should select all columns
     *              from the table associated with the model 
     *              (ie. SELECT * FROM models_table ..... ).
     * 
     *  `where`
     *      : (array) an array of parameters for building a WHERE clause, 
     *        Eg. to generate 
     *          WHERE column_name_1 > 58 AND column_name_2 > 58
     *             OR (column_name_1 < 58 AND column_name_2 < 58)
     *            AND column_name_3 >= 58
     *             OR (column_name_4 = 58 AND column_name_5 = 58)
     *        use:
     *          [
     *              'where' => 
     *                [
     *                   [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                   [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                   'OR'=> [
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR#2'=> [
     *                              [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                              [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ],
     *                          ]
     *                ]
     *          ]
     * 
     *        The 'operator' could be assigned any one of these values:
     *          [ 
     *              '=', '>', '>=', '<', '<=', 'in', 'is-null', 'like',  
     *              '!=', 'not-in', 'not-like', 'not-null'
     *          ]
     *    
     *        NOTE: To add OR conditions add an OR key. For multiple OR conditions
     *              append a # and a unique string after the # so that the 
     *              subsequent OR conditions do not override the previous ones.
     *              Implementers of this class just need to check if an array 
     *              key inside the 'where' array starts with OR or OR# in order
     *              to add the condition as an OR condition. 
     *        NOTE: Consumers of any implementation of this class should be careful 
     *              not to make the first key in the 'where' array or the first 
     *              key in any of the array(s) inside the 'where' array an 'OR' 
     *              or 'OR#...' key. Below are some bad examples and their
     *              corrected equivalents
     * 
     *          #BAD 1 - first key in $array['where'] is 'OR' 
     *          $array = [
     *              'where' => 
     *                [
     *                   'OR'=> [ //offending entry. should not be the first item here
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                ]
     *          ]
     * 
     *          #GOOD 1 - moved the entry with 'OR' key away from first position 
     *                    in $array['where']
     *          $array = [
     *              'where' => 
     *                [
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR'=> [ //Fixed. No longer the first item in $array['where']
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                ]
     *          ]
     *          
     *          #BAD 2 - first key in $array['where']['OR'] is 'OR' 
     *          $array = [
     *             'where' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             'OR'=> [ //offending entry. should not be the first item here
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *          
     *          #GOOD 2 - moved the entry with 'OR' key away from first position 
     *                    in $array['where']['OR'] 
     *          $array = [
     *             'where' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             'OR'=> [ //Fixed. No longer the first item in $array['where']['OR']
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *              
     *        NOTE: Implementers of this class should convert each operator to the 
     *              DB specific operator. Eg. for MySQL, convert 'not-null' to 
     *              'IS NOT NULL'.
     *        NOTE: For any sub-array containing an item with a key named 'operator' 
     *              with a value of either 'not-null' or 'is-null', there must not be
     *              any item in that sub-array with a key named 'val', but there must
     *              be a corresponding item with a key named 'col' with a string value.
     *        NOTE: The operators: 'in' and 'not-in' allow 'val' to be set to an array
     *              or string value. If 'val' is a string, it must be a valid
     *              value that a NOT IN or IN operator expects including the opening
     *              and closing brackets. Eg. "( 1, 2, 3 )" or "( '4', '5', '6' )".
     *        NOTE: Implementers of this class can validate the structure of 
     *              this sub-array by passing it to
     *              \GDAO\Model::_validateWhereOrHavingParamsArray(array $array)
     *        NOTE: Implementers of this class can generate a WHERE clause 
     *              (excluding the WHERE keyword) from this sub-array by passing 
     *              it to \GDAO\Model::_getWhereOrHavingClauseWithParams(...)
     * 
     *  `group`
     *      : (array) An array of the name(s) of column(s) which the results 
     *        will be grouped by.
     *        Eg. to generate ' GROUP BY column_name_1, column_name_2 '
     *        use the array below:
     *          [
     *              'group' => ['column_name_1', 'column_name_2']
     *          ]
     * 
     *  `having`
     *      : (array) An array of parameters for building a HAVING clause.
     *        Eg. to generate 
     *          HAVING column_name_1 > 58 AND column_name_2 > 58
     *              OR (column_name_1 < 58 AND column_name_2 < 58)
     *             AND column_name_3 >= 58
     *              OR (column_name_4 = 58 AND column_name_5 = 58)
     *        use:
     *          [
     *              'having' => 
     *                [
     *                   [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                   [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                   'OR'=> [
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR#2'=> [
     *                              [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                              [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ],
     *                          ]
     *                ]
     *          ]
     * 
     *        The 'operator' could be assigned any one of these values:
     *          [ 
     *              '=', '>', '>=', '<', '<=', 'in', 'is-null', 'like',  
     *              '!=', 'not-in', 'not-like', 'not-null'
     *          ]
     *    
     *        NOTE: To add OR conditions add an OR key. For multiple OR conditions
     *              append a # and a unique string after the # so that the 
     *              subsequent OR conditions do not override the previous ones.
     *              Implementers of this class just need to check if an array 
     *              key inside the 'having' array starts with OR or OR# in order
     *              to add the condition as an OR condition. 
     *        NOTE: Consumers of any implementation of this class should be careful 
     *              not to make the first key in the 'having' array or the first 
     *              key in any of the array(s) inside the 'having' array an 'OR' 
     *              or 'OR#...' key. Below are some bad examples and their
     *              corrected equivalents
     * 
     *          #BAD 1 - first key in $array['having'] is 'OR' 
     *          $array = [
     *              'having' => 
     *                [
     *                   'OR'=> [ //offending entry. should not be the first item here
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                ]
     *          ]
     * 
     *          #GOOD 1 - moved the entry with 'OR' key away from first position 
     *                    in $array['having']
     *          $array = [
     *              'having' => 
     *                [
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR'=> [ //Fixed. No longer the first item in $array['having']
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                ]
     *          ]
     *          
     *          #BAD 2 - first key in $array['having']['OR'] is 'OR' 
     *          $array = [
     *             'having' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             'OR'=> [ //offending entry. should not be the first item here
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *          
     *          #GOOD 2 - moved the entry with 'OR' key away from first position 
     *                    in $array['having']['OR'] 
     *          $array = [
     *             'having' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             'OR'=> [ //Fixed. No longer the first item in $array['having']['OR']
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *              
     *        NOTE: Implementers of this class should convert each operator to the 
     *              DB specific operator. Eg. for MySQL, convert 'not-null' to 
     *              'IS NOT NULL'.
     *        NOTE: For any sub-array containing an item with a key named 'operator' 
     *              with a value of either 'not-null' or 'is-null', there must not be
     *              any item in that sub-array with a key named 'val', but there must
     *              be a corresponding item with a key named 'col' with a string value.
     *        NOTE: The operators: 'in' and 'not-in' allow 'val' to be set to an array
     *              or string value. If 'val' is a string, it must be a valid
     *              value that a NOT IN or IN operator expects including the opening
     *              and closing brackets. Eg. "( 1, 2, 3 )" or "( '4', '5', '6' )".
     *        NOTE: Implementers of this class can validate the structure of 
     *              this sub-array by passing it to
     *              \GDAO\Model::_validateWhereOrHavingParamsArray(array $array)
     *        NOTE: Implementers of this class can generate a HAVING clause 
     *              (excluding the HAVING keyword) from this sub-array by passing 
     *              it to \GDAO\Model::_getWhereOrHavingClauseWithParams(...)
     *     
     *  `order`
     *      : (array) an array of parameters for building an ORDER BY clause.
     *        The values are the column names to ORDER BY.
     *        Eg. to generate 'ORDER BY col_1 ASC, col_2 DESC' use:
     *          [
     *              'order' => [ 'col_1 ASC', 'col_2 DESC' ] 
     *          ]
     * 
     *  `limit_offset`
     *      : (int) Limit offset. Offset of the first row to return
     * 
     *        NOTE: Implementers of this class should use the `limit_offset` 
     *              value with the appropriate limit & offset mechanism for the 
     *              DB system their implementation supports. 
     *              Eg. for MySQL: 
     *                      LIMIT $params['limit_size']
     *                      OFFSET $params['limit_offset']
     * 
     *                  for MSSQL Server:
     *                      OFFSET $params['limit_offset'] ROWS
     *                      FETCH NEXT $params['limit_size'] ROWS ONLY
     * 
     *  `limit_size`
     *      : (int) Limit to a count of this many records.
     * 
     *        NOTE: Implementers of this class should use the `limit_size` 
     *              value with the appropriate limit & offset mechanism for 
     *              the DB system their implementation supports. 
     *              Eg. for MySQL: 
     *                      LIMIT $params['limit_size']
     *                      OFFSET $params['limit_offset']
     * 
     *                  for MSSQL Server:
     *                      OFFSET $params['limit_offset'] ROWS
     *                      FETCH NEXT $params['limit_size'] ROWS ONLY
     * 
     * @return array
     * 
     */
    public abstract function fetchArray(array $params = array());

    /**
     * 
     * Fetch an array of values for a specified column.
     * 
     * @param array $params an array of parameters for the fetch with the keys (case-sensitive) below
     * 
     *  `distinct`
     *      : (bool) True if the DISTINCT keyword should be added to the query, 
     *        else false if the DISTINCT keyword should be ommitted. 
     * 
     *        NOTE: If `distinct` is not set/specified, implementers of this class 
     *              should give it a default value of false.
     * 
     *  `cols`
     *      : (array) An array of the name(s) of column(s) to be returned. Only 
     *        the first one will be honored.
     *        Expressions like 'COUNT(col_name) AS some_col' are allowed as a column name.
     *        Eg. to generate SELECT col_1 FROM......
     *        use: 
     *          [
     *              'cols' => [ 'col_1' ]
     *          ]
     *          OR
     *          [
     *              'cols' => [ 'col_1', 'col_2' ]
     *          ]
     *          OR
     *          [
     *              'cols' => [ 'col_1', 'col_2', 'col_3' ]
     *          ]
     * 
     *        NOTE: If `cols` is not set/specified or is assigned an empty array
     *              value, implementers of this class should select all columns
     *              from the table associated with the model 
     *              (ie. SELECT * FROM models_table ..... ).
     * 
     *  `where`
     *      : (array) an array of parameters for building a WHERE clause, 
     *        Eg. to generate 
     *          WHERE column_name_1 > 58 AND column_name_2 > 58
     *             OR (column_name_1 < 58 AND column_name_2 < 58)
     *            AND column_name_3 >= 58
     *             OR (column_name_4 = 58 AND column_name_5 = 58)
     *        use:
     *          [
     *              'where' => 
     *                [
     *                   [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                   [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                   'OR'=> [
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR#2'=> [
     *                              [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                              [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ],
     *                          ]
     *                ]
     *          ]
     * 
     *        The 'operator' could be assigned any one of these values:
     *          [ 
     *              '=', '>', '>=', '<', '<=', 'in', 'is-null', 'like',  
     *              '!=', 'not-in', 'not-like', 'not-null'
     *          ]
     *    
     *        NOTE: To add OR conditions add an OR key. For multiple OR conditions
     *              append a # and a unique string after the # so that the 
     *              subsequent OR conditions do not override the previous ones.
     *              Implementers of this class just need to check if an array 
     *              key inside the 'where' array starts with OR or OR# in order
     *              to add the condition as an OR condition. 
     *        NOTE: Consumers of any implementation of this class should be careful 
     *              not to make the first key in the 'where' array or the first 
     *              key in any of the array(s) inside the 'where' array an 'OR' 
     *              or 'OR#...' key. Below are some bad examples and their
     *              corrected equivalents
     * 
     *          #BAD 1 - first key in $array['where'] is 'OR' 
     *          $array = [
     *              'where' => 
     *                [
     *                   'OR'=> [ //offending entry. should not be the first item here
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                ]
     *          ]
     * 
     *          #GOOD 1 - moved the entry with 'OR' key away from first position 
     *                    in $array['where']
     *          $array = [
     *              'where' => 
     *                [
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR'=> [ //Fixed. No longer the first item in $array['where']
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                ]
     *          ]
     *          
     *          #BAD 2 - first key in $array['where']['OR'] is 'OR' 
     *          $array = [
     *             'where' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             'OR'=> [ //offending entry. should not be the first item here
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *          
     *          #GOOD 2 - moved the entry with 'OR' key away from first position 
     *                    in $array['where']['OR'] 
     *          $array = [
     *             'where' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             'OR'=> [ //Fixed. No longer the first item in $array['where']['OR']
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *              
     *        NOTE: Implementers of this class should convert each operator to the 
     *              DB specific operator. Eg. for MySQL, convert 'not-null' to 
     *              'IS NOT NULL'.
     *        NOTE: For any sub-array containing an item with a key named 'operator' 
     *              with a value of either 'not-null' or 'is-null', there must not be
     *              any item in that sub-array with a key named 'val', but there must
     *              be a corresponding item with a key named 'col' with a string value.
     *        NOTE: The operators: 'in' and 'not-in' allow 'val' to be set to an array
     *              or string value. If 'val' is a string, it must be a valid
     *              value that a NOT IN or IN operator expects including the opening
     *              and closing brackets. Eg. "( 1, 2, 3 )" or "( '4', '5', '6' )".
     *        NOTE: Implementers of this class can validate the structure of 
     *              this sub-array by passing it to
     *              \GDAO\Model::_validateWhereOrHavingParamsArray(array $array)
     *        NOTE: Implementers of this class can generate a WHERE clause 
     *              (excluding the WHERE keyword) from this sub-array by passing 
     *              it to \GDAO\Model::_getWhereOrHavingClauseWithParams(...)
     * 
     *  `group`
     *      : (array) An array of the name(s) of column(s) which the results 
     *        will be grouped by.
     *        Eg. to generate ' GROUP BY column_name_1, column_name_2 '
     *        use the array below:
     *          [
     *              'group' => ['column_name_1', 'column_name_2']
     *          ]
     * 
     *  `having`
     *      : (array) An array of parameters for building a HAVING clause.
     *        Eg. to generate 
     *          HAVING column_name_1 > 58 AND column_name_2 > 58
     *              OR (column_name_1 < 58 AND column_name_2 < 58)
     *             AND column_name_3 >= 58
     *              OR (column_name_4 = 58 AND column_name_5 = 58)
     *        use:
     *          [
     *              'having' => 
     *                [
     *                   [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                   [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                   'OR'=> [
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR#2'=> [
     *                              [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                              [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ],
     *                          ]
     *                ]
     *          ]
     * 
     *        The 'operator' could be assigned any one of these values:
     *          [ 
     *              '=', '>', '>=', '<', '<=', 'in', 'is-null', 'like',  
     *              '!=', 'not-in', 'not-like', 'not-null'
     *          ]
     *    
     *        NOTE: To add OR conditions add an OR key. For multiple OR conditions
     *              append a # and a unique string after the # so that the 
     *              subsequent OR conditions do not override the previous ones.
     *              Implementers of this class just need to check if an array 
     *              key inside the 'having' array starts with OR or OR# in order
     *              to add the condition as an OR condition. 
     *        NOTE: Consumers of any implementation of this class should be careful 
     *              not to make the first key in the 'having' array or the first 
     *              key in any of the array(s) inside the 'having' array an 'OR' 
     *              or 'OR#...' key. Below are some bad examples and their
     *              corrected equivalents
     * 
     *          #BAD 1 - first key in $array['having'] is 'OR' 
     *          $array = [
     *              'having' => 
     *                [
     *                   'OR'=> [ //offending entry. should not be the first item here
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                ]
     *          ]
     * 
     *          #GOOD 1 - moved the entry with 'OR' key away from first position 
     *                    in $array['having']
     *          $array = [
     *              'having' => 
     *                [
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR'=> [ //Fixed. No longer the first item in $array['having']
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                ]
     *          ]
     *          
     *          #BAD 2 - first key in $array['having']['OR'] is 'OR' 
     *          $array = [
     *             'having' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             'OR'=> [ //offending entry. should not be the first item here
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *          
     *          #GOOD 2 - moved the entry with 'OR' key away from first position 
     *                    in $array['having']['OR'] 
     *          $array = [
     *             'having' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             'OR'=> [ //Fixed. No longer the first item in $array['having']['OR']
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *              
     *        NOTE: Implementers of this class should convert each operator to the 
     *              DB specific operator. Eg. for MySQL, convert 'not-null' to 
     *              'IS NOT NULL'.
     *        NOTE: For any sub-array containing an item with a key named 'operator' 
     *              with a value of either 'not-null' or 'is-null', there must not be
     *              any item in that sub-array with a key named 'val', but there must
     *              be a corresponding item with a key named 'col' with a string value.
     *        NOTE: The operators: 'in' and 'not-in' allow 'val' to be set to an array
     *              or string value. If 'val' is a string, it must be a valid
     *              value that a NOT IN or IN operator expects including the opening
     *              and closing brackets. Eg. "( 1, 2, 3 )" or "( '4', '5', '6' )".
     *        NOTE: Implementers of this class can validate the structure of 
     *              this sub-array by passing it to
     *              \GDAO\Model::_validateWhereOrHavingParamsArray(array $array)
     *        NOTE: Implementers of this class can generate a HAVING clause 
     *              (excluding the HAVING keyword) from this sub-array by passing 
     *              it to \GDAO\Model::_getWhereOrHavingClauseWithParams(...)
     * 
     *  `order`
     *      : (array) an array of parameters for building an ORDER BY clause.
     *        The values are the column names to ORDER BY.
     *        Eg. to generate 'ORDER BY col_1 ASC, col_2 DESC' use:
     *          [
     *              'order' => [ 'col_1 ASC', 'col_2 DESC' ] 
     *          ]
     * 
     *  `limit_offset`
     *      : (int) Limit offset. Offset of the first row to return
     * 
     *        NOTE: Implementers of this class should use the `limit_offset` 
     *              value with the appropriate limit & offset mechanism for the 
     *              DB system their implementation supports. 
     *              Eg. for MySQL: 
     *                      LIMIT $params['limit_size']
     *                      OFFSET $params['limit_offset']
     * 
     *                  for MSSQL Server:
     *                      OFFSET $params['limit_offset'] ROWS
     *                      FETCH NEXT $params['limit_size'] ROWS ONLY
     * 
     *  `limit_size`
     *      : (int) Limit to a count of this many records.
     * 
     *        NOTE: Implementers of this class should use the `limit_size` 
     *              value with the appropriate limit & offset mechanism for the 
     *              DB system their implementation supports. 
     *              Eg. for MySQL: 
     *                      LIMIT $params['limit_size']
     *                      OFFSET $params['limit_offset']
     * 
     *                  for MSSQL Server:
     *                      OFFSET $params['limit_offset'] ROWS
     *                      FETCH NEXT $params['limit_size'] ROWS ONLY
     * 
     * @return array
     * 
     */
    public abstract function fetchCol(array $params = array());

    /**
     * 
     * Fetch a single record matching the specified params.
     * 
     * @param array $params an array of parameters for the fetch with the keys (case-sensitive) below
     * 
     *  `relations_to_include`
     *      : (array) An array of relation names as defined in any or all of 
     *        \GDAO\Model->_has_one_relationships, 
     *        \GDAO\Model->_has_many_relationships,
     *        \GDAO\Model->_belongs_to_relationships and 
     *        \GDAO\Model->_has_many_through_relationships. 
     *        Eager-fetch related rows of data for each relation name.
     * 
     *        NOTE: each key in the \GDAO\Model->_*_relationships arrays is a 
     *              relation name. Eg. array_keys($this->_has_one_relationships)
     *              returns an array of Has-One relation name(s) for a model.
     * 
     *        NOTE: Implementers of this class should make the retreived related
     *              data accessible in the returned record via a property named 
     *              with the same name as the relation name. For example, if 
     *              there exists $this->_has_one_relationships['comments'], the 
     *              retreived comments for the record returned by this fetch 
     *              method should be accessible via $record->comments. Where 
     *              $record is a reference to the record returned by this method.
     *
     *  `distinct`
     *      : (bool) True if the DISTINCT keyword should be added to the query, 
     *        else false if the DISTINCT keyword should be ommitted. 
     * 
     *        NOTE: If `distinct` is not set/specified, implementers of this class 
     *              should give it a default value of false.
     * 
     *  `cols`
     *      : (array) An array of the name(s) of column(s) to be returned.
     *        Expressions like 'COUNT(col_name) AS some_col' are allowed as a column name.
     *        Return only these columns.
     *        Eg. to generate SELECT col_1, col_2, col_3 ......
     *        use: 
     *          [
     *              'cols' => [ 'col_1', 'col_2', 'col_3' ]
     *          ]
     * 
     *        NOTE: If `cols` is not set/specified or is assigned an empty array
     *              value, implementers of this class should select all columns
     *              from the table associated with the model 
     *              (ie. SELECT * FROM models_table ..... ).
     * 
     *  `where`
     *      : (array) an array of parameters for building a WHERE clause, 
     *        Eg. to generate 
     *          WHERE column_name_1 > 58 AND column_name_2 > 58
     *             OR (column_name_1 < 58 AND column_name_2 < 58)
     *            AND column_name_3 >= 58
     *             OR (column_name_4 = 58 AND column_name_5 = 58)
     *        use:
     *          [
     *              'where' => 
     *                [
     *                   [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                   [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                   'OR'=> [
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR#2'=> [
     *                              [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                              [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ],
     *                          ]
     *                ]
     *          ]
     * 
     *        The 'operator' could be assigned any one of these values:
     *          [ 
     *              '=', '>', '>=', '<', '<=', 'in', 'is-null', 'like',  
     *              '!=', 'not-in', 'not-like', 'not-null'
     *          ]
     *    
     *        NOTE: To add OR conditions add an OR key. For multiple OR conditions
     *              append a # and a unique string after the # so that the 
     *              subsequent OR conditions do not override the previous ones.
     *              Implementers of this class just need to check if an array 
     *              key inside the 'where' array starts with OR or OR# in order
     *              to add the condition as an OR condition. 
     *        NOTE: Consumers of any implementation of this class should be careful 
     *              not to make the first key in the 'where' array or the first 
     *              key in any of the array(s) inside the 'where' array an 'OR' 
     *              or 'OR#...' key. Below are some bad examples and their
     *              corrected equivalents
     * 
     *          #BAD 1 - first key in $array['where'] is 'OR' 
     *          $array = [
     *              'where' => 
     *                [
     *                   'OR'=> [ //offending entry. should not be the first item here
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                ]
     *          ]
     * 
     *          #GOOD 1 - moved the entry with 'OR' key away from first position 
     *                    in $array['where']
     *          $array = [
     *              'where' => 
     *                [
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR'=> [ //Fixed. No longer the first item in $array['where']
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                ]
     *          ]
     *          
     *          #BAD 2 - first key in $array['where']['OR'] is 'OR' 
     *          $array = [
     *             'where' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             'OR'=> [ //offending entry. should not be the first item here
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *          
     *          #GOOD 2 - moved the entry with 'OR' key away from first position 
     *                    in $array['where']['OR'] 
     *          $array = [
     *             'where' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             'OR'=> [ //Fixed. No longer the first item in $array['where']['OR']
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *              
     *        NOTE: Implementers of this class should convert each operator to the 
     *              DB specific operator. Eg. for MySQL, convert 'not-null' to 
     *              'IS NOT NULL'.
     *        NOTE: For any sub-array containing an item with a key named 'operator' 
     *              with a value of either 'not-null' or 'is-null', there must not be
     *              any item in that sub-array with a key named 'val', but there must
     *              be a corresponding item with a key named 'col' with a string value.
     *        NOTE: The operators: 'in' and 'not-in' allow 'val' to be set to an array
     *              or string value. If 'val' is a string, it must be a valid
     *              value that a NOT IN or IN operator expects including the opening
     *              and closing brackets. Eg. "( 1, 2, 3 )" or "( '4', '5', '6' )".
     *        NOTE: Implementers of this class can validate the structure of 
     *              this sub-array by passing it to
     *              \GDAO\Model::_validateWhereOrHavingParamsArray(array $array)
     *        NOTE: Implementers of this class can generate a WHERE clause 
     *              (excluding the WHERE keyword) from this sub-array by passing 
     *              it to \GDAO\Model::_getWhereOrHavingClauseWithParams(...)
     * 
     *  `group`
     *      : (array) An array of the name(s) of column(s) which the results 
     *        will be grouped by.
     *        Eg. to generate ' GROUP BY column_name_1, column_name_2 '
     *        use the array below:
     *          [
     *              'group' => ['column_name_1', 'column_name_2']
     *          ]
     * 
     *  `having`
     *      : (array) An array of parameters for building a HAVING clause.
     *        Eg. to generate 
     *          HAVING column_name_1 > 58 AND column_name_2 > 58
     *              OR (column_name_1 < 58 AND column_name_2 < 58)
     *             AND column_name_3 >= 58
     *              OR (column_name_4 = 58 AND column_name_5 = 58)
     *        use:
     *          [
     *              'having' => 
     *                [
     *                   [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                   [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                   'OR'=> [
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR#2'=> [
     *                              [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                              [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ],
     *                          ]
     *                ]
     *          ]
     * 
     *        The 'operator' could be assigned any one of these values:
     *          [ 
     *              '=', '>', '>=', '<', '<=', 'in', 'is-null', 'like',  
     *              '!=', 'not-in', 'not-like', 'not-null'
     *          ]
     *    
     *        NOTE: To add OR conditions add an OR key. For multiple OR conditions
     *              append a # and a unique string after the # so that the 
     *              subsequent OR conditions do not override the previous ones.
     *              Implementers of this class just need to check if an array 
     *              key inside the 'having' array starts with OR or OR# in order
     *              to add the condition as an OR condition. 
     *        NOTE: Consumers of any implementation of this class should be careful 
     *              not to make the first key in the 'having' array or the first 
     *              key in any of the array(s) inside the 'having' array an 'OR' 
     *              or 'OR#...' key. Below are some bad examples and their
     *              corrected equivalents
     * 
     *          #BAD 1 - first key in $array['having'] is 'OR' 
     *          $array = [
     *              'having' => 
     *                [
     *                   'OR'=> [ //offending entry. should not be the first item here
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                ]
     *          ]
     * 
     *          #GOOD 1 - moved the entry with 'OR' key away from first position 
     *                    in $array['having']
     *          $array = [
     *              'having' => 
     *                [
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR'=> [ //Fixed. No longer the first item in $array['having']
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                ]
     *          ]
     *          
     *          #BAD 2 - first key in $array['having']['OR'] is 'OR' 
     *          $array = [
     *             'having' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             'OR'=> [ //offending entry. should not be the first item here
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *          
     *          #GOOD 2 - moved the entry with 'OR' key away from first position 
     *                    in $array['having']['OR'] 
     *          $array = [
     *             'having' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             'OR'=> [ //Fixed. No longer the first item in $array['having']['OR']
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *              
     *        NOTE: Implementers of this class should convert each operator to the 
     *              DB specific operator. Eg. for MySQL, convert 'not-null' to 
     *              'IS NOT NULL'.
     *        NOTE: For any sub-array containing an item with a key named 'operator' 
     *              with a value of either 'not-null' or 'is-null', there must not be
     *              any item in that sub-array with a key named 'val', but there must
     *              be a corresponding item with a key named 'col' with a string value.
     *        NOTE: The operators: 'in' and 'not-in' allow 'val' to be set to an array
     *              or string value. If 'val' is a string, it must be a valid
     *              value that a NOT IN or IN operator expects including the opening
     *              and closing brackets. Eg. "( 1, 2, 3 )" or "( '4', '5', '6' )".
     *        NOTE: Implementers of this class can validate the structure of 
     *              this sub-array by passing it to
     *              \GDAO\Model::_validateWhereOrHavingParamsArray(array $array)
     *        NOTE: Implementers of this class can generate a HAVING clause 
     *              (excluding the HAVING keyword) from this sub-array by passing 
     *              it to \GDAO\Model::_getWhereOrHavingClauseWithParams(...)
     * 
     *  `order`
     *      : (array) an array of parameters for building an ORDER BY clause.
     *        The values are the column names to ORDER BY.
     *        Eg. to generate 'ORDER BY col_1 ASC, col_2 DESC' use:
     *          [
     *              'order' => [ 'col_1 ASC', 'col_2 DESC' ] 
     *          ]
     * 
     * @return \GDAO\Model\Record
     * 
     */
    public abstract function fetchOne(array $params = array());

    /**
     * 
     * Fetch an array of key-value pairs from the db table, where the 
     * 1st column's value is the key and the 2nd column's value is the value.
     * 
     * @param array $params an array of parameters for the fetch with the keys (case-sensitive) below
     *
     *  `distinct`
     *      : (bool) True if the DISTINCT keyword should be added to the query, 
     *        else false if the DISTINCT keyword should be ommitted. 
     * 
     *        NOTE: If `distinct` is not set/specified, implementers of this class 
     *              should give it a default value of false.
     * 
     *  `cols`
     *      : (array) An array of the name(s) of column(s) or aggregate sql function
     *        calls to be returned. Only the first two array items will be honored.
     *        Expressions like 'COUNT(col_name) AS some_col' are allowed as a column name.
     *        Eg. to generate 'SELECT col_1, col_2 FROM.....'
     *        use: 
     *          [
     *              'cols' => [ 'col_1', 'col_2' ]
     *          ]
     *        OR
     *          [
     *              'cols' => [ 'col_1', 'col_2', 'col_3' ]
     *          ]
     * 
     *        NOTE: If `cols` is not set/specified or is assigned an empty array
     *              value, implementers of this class should select all columns
     *              from the table associated with the model 
     *              (ie. SELECT * FROM models_table ..... ).
     * 
     *  `where`
     *      : (array) an array of parameters for building a WHERE clause, 
     *        Eg. to generate 
     *          WHERE column_name_1 > 58 AND column_name_2 > 58
     *             OR (column_name_1 < 58 AND column_name_2 < 58)
     *            AND column_name_3 >= 58
     *             OR (column_name_4 = 58 AND column_name_5 = 58)
     *        use:
     *          [
     *              'where' => 
     *                [
     *                   [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                   [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                   'OR'=> [
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR#2'=> [
     *                              [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                              [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ],
     *                          ]
     *                ]
     *          ]
     * 
     *        The 'operator' could be assigned any one of these values:
     *          [ 
     *              '=', '>', '>=', '<', '<=', 'in', 'is-null', 'like',  
     *              '!=', 'not-in', 'not-like', 'not-null'
     *          ]
     *    
     *        NOTE: To add OR conditions add an OR key. For multiple OR conditions
     *              append a # and a unique string after the # so that the 
     *              subsequent OR conditions do not override the previous ones.
     *              Implementers of this class just need to check if an array 
     *              key inside the 'where' array starts with OR or OR# in order
     *              to add the condition as an OR condition. 
     *        NOTE: Consumers of any implementation of this class should be careful 
     *              not to make the first key in the 'where' array or the first 
     *              key in any of the array(s) inside the 'where' array an 'OR' 
     *              or 'OR#...' key. Below are some bad examples and their
     *              corrected equivalents
     * 
     *          #BAD 1 - first key in $array['where'] is 'OR' 
     *          $array = [
     *              'where' => 
     *                [
     *                   'OR'=> [ //offending entry. should not be the first item here
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                ]
     *          ]
     * 
     *          #GOOD 1 - moved the entry with 'OR' key away from first position 
     *                    in $array['where']
     *          $array = [
     *              'where' => 
     *                [
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR'=> [ //Fixed. No longer the first item in $array['where']
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                ]
     *          ]
     *          
     *          #BAD 2 - first key in $array['where']['OR'] is 'OR' 
     *          $array = [
     *             'where' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             'OR'=> [ //offending entry. should not be the first item here
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *          
     *          #GOOD 2 - moved the entry with 'OR' key away from first position 
     *                    in $array['where']['OR'] 
     *          $array = [
     *             'where' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             'OR'=> [ //Fixed. No longer the first item in $array['where']['OR']
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *              
     *        NOTE: Implementers of this class should convert each operator to the 
     *              DB specific operator. Eg. for MySQL, convert 'not-null' to 
     *              'IS NOT NULL'.
     *        NOTE: For any sub-array containing an item with a key named 'operator' 
     *              with a value of either 'not-null' or 'is-null', there must not be
     *              any item in that sub-array with a key named 'val', but there must
     *              be a corresponding item with a key named 'col' with a string value.
     *        NOTE: The operators: 'in' and 'not-in' allow 'val' to be set to an array
     *              or string value. If 'val' is a string, it must be a valid
     *              value that a NOT IN or IN operator expects including the opening
     *              and closing brackets. Eg. "( 1, 2, 3 )" or "( '4', '5', '6' )".
     *        NOTE: Implementers of this class can validate the structure of 
     *              this sub-array by passing it to
     *              \GDAO\Model::_validateWhereOrHavingParamsArray(array $array)
     *        NOTE: Implementers of this class can generate a WHERE clause 
     *              (excluding the WHERE keyword) from this sub-array by passing 
     *              it to \GDAO\Model::_getWhereOrHavingClauseWithParams(...)
     * 
     *  `group`
     *      : (array) An array of the name(s) of column(s) which the results 
     *        will be grouped by.
     *        Eg. to generate ' GROUP BY column_name_1, column_name_2 '
     *        use the array below:
     *          [
     *              'group' => ['column_name_1', 'column_name_2']
     *          ]
     * 
     *  `having`
     *      : (array) An array of parameters for building a HAVING clause.
     *        Eg. to generate 
     *          HAVING column_name_1 > 58 AND column_name_2 > 58
     *              OR (column_name_1 < 58 AND column_name_2 < 58)
     *             AND column_name_3 >= 58
     *              OR (column_name_4 = 58 AND column_name_5 = 58)
     *        use:
     *          [
     *              'having' => 
     *                [
     *                   [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                   [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                   'OR'=> [
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR#2'=> [
     *                              [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                              [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ],
     *                          ]
     *                ]
     *          ]
     * 
     *        The 'operator' could be assigned any one of these values:
     *          [ 
     *              '=', '>', '>=', '<', '<=', 'in', 'is-null', 'like',  
     *              '!=', 'not-in', 'not-like', 'not-null'
     *          ]
     *    
     *        NOTE: To add OR conditions add an OR key. For multiple OR conditions
     *              append a # and a unique string after the # so that the 
     *              subsequent OR conditions do not override the previous ones.
     *              Implementers of this class just need to check if an array 
     *              key inside the 'having' array starts with OR or OR# in order
     *              to add the condition as an OR condition. 
     *        NOTE: Consumers of any implementation of this class should be careful 
     *              not to make the first key in the 'having' array or the first 
     *              key in any of the array(s) inside the 'having' array an 'OR' 
     *              or 'OR#...' key. Below are some bad examples and their
     *              corrected equivalents
     * 
     *          #BAD 1 - first key in $array['having'] is 'OR' 
     *          $array = [
     *              'having' => 
     *                [
     *                   'OR'=> [ //offending entry. should not be the first item here
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                ]
     *          ]
     * 
     *          #GOOD 1 - moved the entry with 'OR' key away from first position 
     *                    in $array['having']
     *          $array = [
     *              'having' => 
     *                [
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR'=> [ //Fixed. No longer the first item in $array['having']
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                ]
     *          ]
     *          
     *          #BAD 2 - first key in $array['having']['OR'] is 'OR' 
     *          $array = [
     *             'having' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             'OR'=> [ //offending entry. should not be the first item here
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *          
     *          #GOOD 2 - moved the entry with 'OR' key away from first position 
     *                    in $array['having']['OR'] 
     *          $array = [
     *             'having' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             'OR'=> [ //Fixed. No longer the first item in $array['having']['OR']
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *              
     *        NOTE: Implementers of this class should convert each operator to the 
     *              DB specific operator. Eg. for MySQL, convert 'not-null' to 
     *              'IS NOT NULL'.
     *        NOTE: For any sub-array containing an item with a key named 'operator' 
     *              with a value of either 'not-null' or 'is-null', there must not be
     *              any item in that sub-array with a key named 'val', but there must
     *              be a corresponding item with a key named 'col' with a string value.
     *        NOTE: The operators: 'in' and 'not-in' allow 'val' to be set to an array
     *              or string value. If 'val' is a string, it must be a valid
     *              value that a NOT IN or IN operator expects including the opening
     *              and closing brackets. Eg. "( 1, 2, 3 )" or "( '4', '5', '6' )".
     *        NOTE: Implementers of this class can validate the structure of 
     *              this sub-array by passing it to
     *              \GDAO\Model::_validateWhereOrHavingParamsArray(array $array)
     *        NOTE: Implementers of this class can generate a HAVING clause 
     *              (excluding the HAVING keyword) from this sub-array by passing 
     *              it to \GDAO\Model::_getWhereOrHavingClauseWithParams(...)
     *    
     *  `order`
     *      : (array) an array of parameters for building an ORDER BY clause.
     *        The values are the column names to ORDER BY.
     *        Eg. to generate 'ORDER BY col_1 ASC, col_2 DESC' use:
     *          [
     *              'order' => [ 'col_1 ASC', 'col_2 DESC' ] 
     *          ]
     * 
     *  `limit_offset`
     *      : (int) Limit offset. Offset of the first row to return
     * 
     *        NOTE: Implementers of this class should use the `limit_offset` 
     *              value with the appropriate limit & offset mechanism for the 
     *              DB system their implementation supports. 
     *              Eg. for MySQL: 
     *                      LIMIT $params['limit_size']
     *                      OFFSET $params['limit_offset']
     * 
     *                  for MSSQL Server:
     *                      OFFSET $params['limit_offset'] ROWS
     *                      FETCH NEXT $params['limit_size'] ROWS ONLY
     * 
     *  `limit_size`
     *      : (int) Limit to a count of this many records.
     * 
     *        NOTE: Implementers of this class should use the `limit_size` 
     *              value with the appropriate limit & offset mechanism for the 
     *              DB system their implementation supports. 
     *              Eg. for MySQL: 
     *                      LIMIT $params['limit_size']
     *                      OFFSET $params['limit_offset']
     * 
     *                  for MSSQL Server:
     *                      OFFSET $params['limit_offset'] ROWS
     *                      FETCH NEXT $params['limit_size'] ROWS ONLY
     * 
     * @return array
     * 
     */
    public abstract function fetchPairs(array $params = array());

    /**
     * 
     * Fetch a single value from the db table matching params.
     * 
     * @param array $params an array of parameters for the fetch with the keys (case-sensitive) below
     * 
     *  `distinct`
     *      : (bool) True if the DISTINCT keyword should be added to the query, 
     *        else false if the DISTINCT keyword should be ommitted. 
     * 
     *        NOTE: If `distinct` is not set/specified, implementers of this class 
     *              should give it a default value of false.
     * 
     *  `cols`
     *      : (array) An array of the name(s) of column(s) or aggregate sql function
     *        call(s) to be returned. Only the first one will be honored.
     *        Expressions like 'COUNT(col_name) AS some_col' are allowed as a column name.
     *        Eg. Both: 
     *          [
     *              'cols' => [ 'col_1', 'col_2', 'col_3' ]
     *          ]
     *          and
     *          [
     *              'cols' => [ 'col_1']
     *          ]
     *          will generate  'SELECT col_1 FROM .....'
     * 
     *        NOTE: If `cols` is not set/specified or is assigned an empty array
     *              value, implementers of this class should select all columns
     *              from the table associated with the model 
     *              (ie. SELECT * FROM models_table ..... ).
     * 
     *  `where`
     *      : (array) an array of parameters for building a WHERE clause, 
     *        Eg. to generate 
     *          WHERE column_name_1 > 58 AND column_name_2 > 58
     *             OR (column_name_1 < 58 AND column_name_2 < 58)
     *            AND column_name_3 >= 58
     *             OR (column_name_4 = 58 AND column_name_5 = 58)
     *        use:
     *          [
     *              'where' => 
     *                [
     *                   [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                   [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                   'OR'=> [
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR#2'=> [
     *                              [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                              [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ],
     *                          ]
     *                ]
     *          ]
     * 
     *        The 'operator' could be assigned any one of these values:
     *          [ 
     *              '=', '>', '>=', '<', '<=', 'in', 'is-null', 'like',  
     *              '!=', 'not-in', 'not-like', 'not-null'
     *          ]
     *    
     *        NOTE: To add OR conditions add an OR key. For multiple OR conditions
     *              append a # and a unique string after the # so that the 
     *              subsequent OR conditions do not override the previous ones.
     *              Implementers of this class just need to check if an array 
     *              key inside the 'where' array starts with OR or OR# in order
     *              to add the condition as an OR condition. 
     *        NOTE: Consumers of any implementation of this class should be careful 
     *              not to make the first key in the 'where' array or the first 
     *              key in any of the array(s) inside the 'where' array an 'OR' 
     *              or 'OR#...' key. Below are some bad examples and their
     *              corrected equivalents
     * 
     *          #BAD 1 - first key in $array['where'] is 'OR' 
     *          $array = [
     *              'where' => 
     *                [
     *                   'OR'=> [ //offending entry. should not be the first item here
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                ]
     *          ]
     * 
     *          #GOOD 1 - moved the entry with 'OR' key away from first position 
     *                    in $array['where']
     *          $array = [
     *              'where' => 
     *                [
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR'=> [ //Fixed. No longer the first item in $array['where']
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                ]
     *          ]
     *          
     *          #BAD 2 - first key in $array['where']['OR'] is 'OR' 
     *          $array = [
     *             'where' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             'OR'=> [ //offending entry. should not be the first item here
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *          
     *          #GOOD 2 - moved the entry with 'OR' key away from first position 
     *                    in $array['where']['OR'] 
     *          $array = [
     *             'where' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             'OR'=> [ //Fixed. No longer the first item in $array['where']['OR']
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *              
     *        NOTE: Implementers of this class should convert each operator to the 
     *              DB specific operator. Eg. for MySQL, convert 'not-null' to 
     *              'IS NOT NULL'.
     *        NOTE: For any sub-array containing an item with a key named 'operator' 
     *              with a value of either 'not-null' or 'is-null', there must not be
     *              any item in that sub-array with a key named 'val', but there must
     *              be a corresponding item with a key named 'col' with a string value.
     *        NOTE: The operators: 'in' and 'not-in' allow 'val' to be set to an array
     *              or string value. If 'val' is a string, it must be a valid
     *              value that a NOT IN or IN operator expects including the opening
     *              and closing brackets. Eg. "( 1, 2, 3 )" or "( '4', '5', '6' )".
     *        NOTE: Implementers of this class can validate the structure of 
     *              this sub-array by passing it to
     *              \GDAO\Model::_validateWhereOrHavingParamsArray(array $array)
     *        NOTE: Implementers of this class can generate a WHERE clause 
     *              (excluding the WHERE keyword) from this sub-array by passing 
     *              it to \GDAO\Model::_getWhereOrHavingClauseWithParams(...)
     * 
     *  `group`
     *      : (array) An array of the name(s) of column(s) which the result
     *        will be grouped by.
     *        Eg. to generate ' GROUP BY column_name_1, column_name_2 '
     *        use the array below:
     *          [
     *              'group' => ['column_name_1', 'column_name_2']
     *          ]
     * 
     *  `having`
     *      : (array) An array of parameters for building a HAVING clause.
     *        Eg. to generate 
     *          HAVING column_name_1 > 58 AND column_name_2 > 58
     *              OR (column_name_1 < 58 AND column_name_2 < 58)
     *             AND column_name_3 >= 58
     *              OR (column_name_4 = 58 AND column_name_5 = 58)
     *        use:
     *          [
     *              'having' => 
     *                [
     *                   [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                   [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                   'OR'=> [
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR#2'=> [
     *                              [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                              [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ],
     *                          ]
     *                ]
     *          ]
     * 
     *        The 'operator' could be assigned any one of these values:
     *          [ 
     *              '=', '>', '>=', '<', '<=', 'in', 'is-null', 'like',  
     *              '!=', 'not-in', 'not-like', 'not-null'
     *          ]
     *    
     *        NOTE: To add OR conditions add an OR key. For multiple OR conditions
     *              append a # and a unique string after the # so that the 
     *              subsequent OR conditions do not override the previous ones.
     *              Implementers of this class just need to check if an array 
     *              key inside the 'having' array starts with OR or OR# in order
     *              to add the condition as an OR condition. 
     *        NOTE: Consumers of any implementation of this class should be careful 
     *              not to make the first key in the 'having' array or the first 
     *              key in any of the array(s) inside the 'having' array an 'OR' 
     *              or 'OR#...' key. Below are some bad examples and their
     *              corrected equivalents
     * 
     *          #BAD 1 - first key in $array['having'] is 'OR' 
     *          $array = [
     *              'having' => 
     *                [
     *                   'OR'=> [ //offending entry. should not be the first item here
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                ]
     *          ]
     * 
     *          #GOOD 1 - moved the entry with 'OR' key away from first position 
     *                    in $array['having']
     *          $array = [
     *              'having' => 
     *                [
     *                   [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *                   'OR'=> [ //Fixed. No longer the first item in $array['having']
     *                              [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                              [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ]
     *                          ],
     *                ]
     *          ]
     *          
     *          #BAD 2 - first key in $array['having']['OR'] is 'OR' 
     *          $array = [
     *             'having' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             'OR'=> [ //offending entry. should not be the first item here
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *          
     *          #GOOD 2 - moved the entry with 'OR' key away from first position 
     *                    in $array['having']['OR'] 
     *          $array = [
     *             'having' => 
     *               [
     *                  [ 'col'=>'column_name_1', 'operator'=>'>', 'val'=>58 ],
     *                  [ 'col'=>'column_name_2', 'operator'=>'>', 'val'=>58 ],
     *                  'OR'=> [
     *                             [ 'col'=>'column_name_4', 'operator'=>'=', 'val'=>58 ],
     *                             'OR'=> [ //Fixed. No longer the first item in $array['having']['OR']
     *                                         [ 'col'=>'column_name_1', 'operator'=>'<', 'val'=>58 ],
     *                                         [ 'col'=>'column_name_2', 'operator'=>'<', 'val'=>58 ],
     *                                    ],
     *                             [ 'col'=>'column_name_5', 'operator'=>'=', 'val'=>58 ]
     *                         ],
     *                  [ 'col'=>'column_name_3', 'operator'=>'>=', 'val'=>58 ],
     *               ]
     *         ]
     *              
     *        NOTE: Implementers of this class should convert each operator to the 
     *              DB specific operator. Eg. for MySQL, convert 'not-null' to 
     *              'IS NOT NULL'.
     *        NOTE: For any sub-array containing an item with a key named 'operator' 
     *              with a value of either 'not-null' or 'is-null', there must not be
     *              any item in that sub-array with a key named 'val', but there must
     *              be a corresponding item with a key named 'col' with a string value.
     *        NOTE: The operators: 'in' and 'not-in' allow 'val' to be set to an array
     *              or string value. If 'val' is a string, it must be a valid
     *              value that a NOT IN or IN operator expects including the opening
     *              and closing brackets. Eg. "( 1, 2, 3 )" or "( '4', '5', '6' )".
     *        NOTE: Implementers of this class can validate the structure of 
     *              this sub-array by passing it to
     *              \GDAO\Model::_validateWhereOrHavingParamsArray(array $array)
     *        NOTE: Implementers of this class can generate a HAVING clause 
     *              (excluding the HAVING keyword) from this sub-array by passing 
     *              it to \GDAO\Model::_getWhereOrHavingClauseWithParams(...)
     *    
     *  `order`
     *      : (array) an array of parameters for building an ORDER BY clause.
     *        The values are the column names to ORDER BY.
     *        Eg. to generate 'ORDER BY col_1 ASC, col_2 DESC' use:
     *          [
     *              'order' => [ 'col_1 ASC', 'col_2 DESC' ] 
     *          ]
     * 
     * @return mixed A single value either from a column in a row of the db table 
     *               associated with this model or the result of a sql aggregate
     *               function (eg. MAX(col_name)).
     * 
     */
    public abstract function fetchValue(array $params = array());

    /**
     * 
     * Return the PDO object powering this model or throw 
     * \GDAO\ModelRequiresPdoInstanceException if no PDO object is available.
     * 
     * @return \PDO the PDO object powering this model.
     * 
     * @throws \GDAO\ModelRequiresPdoInstanceException
     * 
     */
    public abstract function getPDO();

    /**
     * 
     * Insert one row to the model table with the specified values.
     * 
     * An exception (\GDAO\ModelPrimaryColValueNotRetrievableAfterInsertException)
     * should be thrown if auto-incremented primary-key value of the inserted 
     * record could not be retrieved (in this case the insert could have still
     * been successful).
     * 
     * An exception (\GDAO\ModelInvalidInsertValueSuppliedException) should be
     * thrown if any of the values supplied for insertion is not a boolean, NULL,
     * number or string value (this should happen before even attempting to 
     * perform the insert).
     * 
     * @param array $col_names_n_vals an array whose keys are the names of columns
     *                                in the database and whose corresponding values
     *                                are the values to be inserted in each column.
     *                                The values must be one of these types:
     *                                boolean, numeric, NULL or string.
     *                                Implementers of this class should check
     *                                that the supplied values are of the expected 
     *                                type, else they should throw the following
     *                                exception: \GDAO\ModelInvalidInsertValueSuppliedException
     * 
     * @return bool|array false if insert failed, else return an array of the 
     *                    inserted data including auto-incremented primary-key 
     *                    value if the insert succeeded.
     * 
     * @throws \GDAO\ModelInvalidInsertValueSuppliedException
     * @throws \GDAO\ModelPrimaryColValueNotRetrievableAfterInsertException
     * 
     */
    public abstract function insert($col_names_n_vals=array());

    /**
     * 
     * Updates rows in the model's db table.
     * 
     * An exception (\GDAO\ModelInvalidUpdateValueSuppliedException) should be
     * thrown if any of the values supplied for update is not a boolean, NULL,
     * number or string value (this should happen before even attempting to 
     * perform the update).
     * 
     * @param array $col_names_n_values_2_save array of data to be used to update the matched records.
     *                                         An array whose keys are the names of columns
     *                                         in the database and whose corresponding values
     *                                         are the values to be updated for each column.
     *                                         The values must be one of these types:
     *                                         boolean, numeric, NULL or string.
     *                                         Implementers of this class should check
     *                                         that the supplied values are of the expected 
     *                                         type, else they should throw the following
     *                                         exception: \GDAO\ModelInvalidUpdateValueSuppliedException
     * 
     * @param array $col_names_n_values_2_match array of where clause conditions for an update statement
     *                                          to update one or more records in the db table associated
     *                                          with this model.
     *                            
     *                                          Eg. for a table 'x' with the following columns:
     *                                          'id', 'title' and 'description'
     *              
     *                                          ['id'=>5, 'title'=>'yabadabadoo'] should generate the sql below:
     *                                          UPDATE `x` SET ...  WHERE id = 5 AND title = 'yabadabadoo'
     *              
     *                                          ['id'=>[5,6,7], 'title'=>'yipeedoo'] should generate the sql below:
     *                                          UPDATE `x` SET ...  WHERE id IN (5,6,7)  AND title = 'yipeedoo'
     * 
     * @return bool|array false if update failed, or return the number of rows
     *                    updated if the update was successful or return
     *                    null if there were no matching records.
     * 
     * @throws \GDAO\ModelInvalidUpdateValueSuppliedException
     * 
     */
    public abstract function updateRecordsMatchingSpecifiedColsNValues(
        array $col_names_n_values_2_save = array(), 
        array $col_names_n_values_2_match = array()
    );
    
    /**
     * 
     * Update the specified record in the database.
     * Save all fields in the specified record to the corresponding row in the db.
     * 
     * @param \GDAO\Model\Record $record
     * 
     * @return bool true for a successful update, false for a failed update 
     *              OR null if supplied record is a record that has never been
     *              saved to the db.
     * 
     */
    public abstract function updateSpecifiedRecord(\GDAO\Model\Record $record);
    
    //////////////////////////////////////
    // Getters for non-public properties
    //////////////////////////////////////
    
    /**
     * 
     * Get the value of $this->_created_timestamp_column_name.
     * 
     * @return string the value of $this->_created_timestamp_column_name.
     * 
     */
    public function getCreatedTimestampColumnName() {

        return $this->_created_timestamp_column_name;
    }
    
    /**
     * 
     * Get the value of $this->_primary_col.
     * 
     * @return string the value of $this->_primary_col.
     * 
     */
    public function getPrimaryColName() {

        return $this->_primary_col;
    }

    /**
     * 
     * Get an array of table column names.
     * 
     * @return array an array of table column names.
     * 
     */
    public function getTableCols() {

        $keys = array_keys($this->_table_cols);
        
        if( $keys === range(0, count($this->_table_cols) - 1) ) {
            
            //$this->_table_cols is a sequential array with numeric keys
            //its values are most likely to be column names
            return $this->_table_cols;

        } else {
            
            $keys_are_strings = true;
            
            foreach($keys as $key) {
                
                if( !is_string($key) ) {
                    
                    $keys_are_strings = false;
                    break;
                }
            }

            return ($keys_are_strings)? array_keys($this->_table_cols): array();
        }
    }

    /**
     * 
     * Get the value of $this->_table_name.
     * 
     * @return string the value of $this->_table_name.
     * 
     */
    public function getTableName() {

        return $this->_table_name;
    }

    /**
     * 
     * Get the value of $this->_updated_timestamp_column_name.
     * 
     * @return string the value of $this->_updated_timestamp_column_name.
     * 
     */
    public function getUpdatedTimestampColumnName() {

        return $this->_updated_timestamp_column_name;
    }
}

class ModelRequiresPdoInstanceException extends \Exception{}
class ModelMustImplementMethodException extends \Exception{}
class ModelInvalidInsertValueSuppliedException extends \Exception{}
class ModelInvalidUpdateValueSuppliedException extends \Exception{}
class ModelBadWhereOrHavingParamSuppliedException extends \Exception{}
class ModelTableNameNotSetDuringConstructionException extends \Exception {}
class ModelPrimaryColNameNotSetDuringConstructionException extends \Exception {}
class ModelPrimaryColValueNotRetrievableAfterInsertException extends \Exception {}