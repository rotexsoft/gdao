<?php

namespace GDAO;

/**
 * An abstract Model class that can be extended to create a Model class that 
 * performs data Creation, Retrieval, Update and Deletion operations on an sql
 * data-source.
 * 
 * Support for other data-sources like xml, csv, no-SQL, etc. may be added in 
 * the future. 
 * 
 * @author Rotimi Adegbamigbe
 * @copyright (c) 2015, Rotimi Adegbamigbe
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
     * @todo Working on supporting tables that do not have any primary key column defined
     * 
     * @var string
     */
    protected $_primary_col = 'id';
    
    /**
     *
     * Name of the db table associated with this model
     * 
     * This is a REQUIRED field & must be properly set by consumers of this class
     * 
     * @var string
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
     * array containing more data about the column (it's up to the implementer
     * of this class to decide what the structure of the metadata array will be).
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
     * 
     * @var string
     */
    protected $_created_timestamp_column_name = null;   //string

    /**
     *
     * Name of a column in the db table associated with this model that is used
     * to keep track of the time when a row of data was last updated in a db 
     * table.
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
     * tracks the time a row of data was last updated in a db table.
     * 
     * @var string
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
    //* this relationship related features based on definition structures    *//
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
     *      integer primary key.
     *     
     *      NOTE: the summaries table does not have a primary key. 
     *      There should be a unique index on its s_post_id column to 
     *      enforce the rule that a post can have only one summary
     *      and to also improve query performance.
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
     *      ]
     * 
     * NOTE: the array key value 'summary' is a relation name that can be used to 
     * later access this particular relationship definiton. Any value can be used 
     * to name a relationship (but it is recommended that it should not be a name 
     * of an existing column in the current model's db table)
     * 
     * @var array
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
     *      auto-incrementing integer primary key.
     *     
     *      NOTE: the comment_id column in the comments table is an
     *      auto-incrementing integer primary key.
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
     *      ]
     * 
     * NOTE: the array key value 'comments' is a relation name that can be used to 
     * later access this particular relationship definiton. Any value can be used 
     * to name a relationship (but it is recommended that it should not be a name 
     * of an existing column in the current model's db table)
     * 
     * @var array
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
     *      auto-incrementing integer primary key.
     *     
     *      NOTE: the post_id column in the posts table is an
     *      auto-incrementing integer primary key.
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
     *      ]
     * 
     * NOTE: the array key value 'author' is a relation name that can be used to 
     * later access this particular relationship definiton. Any value can be used 
     * to name a relationship (but it is recommended that it should not be a name 
     * of an existing column in the current model's db table)
     * 
     * @var array
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
     *      auto-incrementing integer primary key.
     *     
     *      NOTE: the tag_id column in the tags table is an
     *      auto-incrementing integer primary key.
     *     
     *      NOTE: the posts_tags_id column in the posts_tags 
     *      table is an auto-incrementing integer primary key. 
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
     *
     *          'foreign_models_table' => 'tags',
     *          'col_in_foreign_models_table_linked_to_join_table' => 'tag_id',
     *      ]
     * 
     * NOTE: the array key value 'tags' is a relation name that can be used to 
     * later access this particular relationship definiton. Any value can be used 
     * to name a relationship (but it is recommended that it should not be a name 
     * of an existing column in the current model's db table)
     * 
     * 
     * @var array
     */
    protected $_has_many_through_relationships = array();

    /**
     * 
     * A PDO compliant Data Source Name (DSN) string containing the information 
     * required to connect to a desired database. 
     * 
     * @var string
     * @see \PDO::__construct() See description of the 1st parameter 
     *                          (http://php.net/manual/en/pdo.construct.php) if 
     *                          this Model will indeed be powered by a PDO instance
     */
    protected $_dsn = '';
    
    /**
     *
     * The username for the database to be connected to.
     * 
     * @var string
     * @see \PDO::__construct() See description of the 2nd parameter 
     *                          (http://php.net/manual/en/pdo.construct.php) if 
     *                          this Model will indeed be powered by a PDO instance
     */
    protected $_username = ''; 
    
    /**
     *
     * The password for the database to be connected to.
     * 
     * @var string
     * @see \PDO::__construct() See description of the 3rd parameter 
     *                          (http://php.net/manual/en/pdo.construct.php) if 
     *                          this Model will indeed be powered by a PDO instance
     */
    protected $_passwd = '';
    
    /**
     *
     * An array of options for a PDO driver
     * 
     * @var array
     * @see \PDO::__construct() See description of the 4th parameter 
     *                          (http://php.net/manual/en/pdo.construct.php) if 
     *                          this Model will indeed be powered by a PDO instance
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
     */
    protected $_extra_opts = array();
    
    /**
     * 
     * @param string $dsn
     * @param string $username
     * @param string $passwd
     * @param array $pdo_driver_opts
     * @param array $extra_opts an array of other parameters that may be needed 
     *                          in creating an instance of this class
     * 
     * @see \PDO::__construct(...) for definition of first four parameters
     */
    public function __construct(
        $dsn = '',
        $username = '', 
        $passwd = '', 
        $pdo_driver_opts = array(),
        $extra_opts = array()
    ) {
        $this->_dsn = $dsn;
        $this->_username = $username;
        $this->_passwd = $passwd;
        $this->_pdo_driver_opts = $pdo_driver_opts;
        $this->_extra_opts = $extra_opts;
        $this->_setup();
        
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
     * Model setup. 
     * Set the properties of this model (like $this->_primary_col) here.
     * 
     * @return void
     * 
     */
    protected abstract function _setup();

    public function __call($method, $params) {

        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__;
        throw new GDAOModelMustImplementMethodException($msg);
    }

    public function __get($key) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__;
        throw new GDAOModelMustImplementMethodException($msg);
    }

    public function __toString() {

        return print_r($this->toArray(), true);
    }

    public function toArray() {

        return get_object_vars($this);
    }
    
    /**
     * 
     * Create and return a new collection of zero or more records
     * 
     * @param \GDAO\Model\GDAORecordsList $list_of_records
     * @return \GDAO\Model\Collection a collection of instances of \GDAO\Model\Record
     */
    public function createCollection(\GDAO\Model\GDAORecordsList $list_of_records) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__;
        throw new GDAOModelMustImplementMethodException($msg);
    }
    
    /**
     * 
     * Create and return a new record with specified values
     * 
     * @param array $col_names_and_values
     * @param bool $is_new value to set for the _is_new property of the record to be returned
     *             true if the record is to be treated as new (i.e. never been saved to the db),
     *             else false.
     * 
     * @return \GDAO\Model\Record new record with specified values
     */
    public abstract function createRecord(array $col_names_and_values = array(), $is_new=true);

    /**
     * Delete one or more records matching specified conditions
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
     * @return bool true for a successful deletion, false for a failed deletion 
     *              OR null if nothing was deleted (an empty array was supplied).                            
     */
    public abstract function deleteRecordsMatchingSpecifiedColsNValues(array $cols_n_vals);

    /**
     * Delete the specified record from the database. 
     * The record object must be set to a new state by a call to 
     * $record->setStateToNew() after a successful deletion.
     * 
     * @param \GDAO\Model\Record $record
     * 
     * @return bool true for a successful deletion, false for a failed deletion 
     *              OR null if supplied record is a new record that has never 
     *              been saved to the db.  
     */
    public abstract function deleteSpecifiedRecord(\GDAO\Model\Record $record);

    /**
     * 
     * Fetch a collection (an instance of GDAO\Model\Collection or any of its 
     * sub-classes) of records [Eager Loading should be considered here]
     * 
     * @param array $params
     * @return GDAO\Model\Collection
     */
    public function fetchAll($params = array()) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__;
        throw new GDAOModelMustImplementMethodException($msg);
    }

    /**
     * 
     * Fetch an array of records (instances of \GDAO\Model\Record or any of its 
     * sub-classes) [Eager Loading should be considered here]
     * 
     * @param array $params
     * @return array of records (instances of \GDAO\Model\Record or any of its 
     *               sub-classes)
     */
    public abstract function fetchAllAsArray($params = array());

    /**
     * 
     * Fetch an array of db data. Each record is an associative array and not an
     * instance of \GDAO\Model\Record [Eager Loading should be considered here]
     * 
     * @param array $params
     * @return array
     */
    public abstract function fetchArray($params = array());

    /**
     * 
     * Fetch an array of values for a specified column
     * 
     * @param array $params
     * @return array
     */
    public abstract function fetchCol($params = array());

    /**
     * Fetch a record matching the specified params
     * 
     * @param array $params
     * @return \GDAO\Model\Record
     */
    public abstract function fetchOne($params = array());

    /**
     * 
     * Fetch an array of key-value pairs from the db table, where the 
     * 1st column's value is the key and the 2nd column's value is the value.
     * 
     * @param array $params
     * @return array
     */
    public abstract function fetchPairs($params = array());

    /**
     * 
     * Fetch a single value from the db table matching params
     * 
     * @param array $params
     * @return mixed
     */
    public abstract function fetchValue($params = array());

    /**
     * 
     * @return bool|\PDO Return the PDO object powering this model or false if PDO is not being used.
     * 
     */
    public abstract function getPDO();

    /**
     * 
     * Insert one row to the model table with the specified values.
     * Can throw exception (if desired) if the insert operation fails.
     * By default if the insert fails just return false, else return an array of 
     * the inserted data including auto-incremented values if the insert succeeded.
     * 
     * @param array $col_names_n_vals
     * @param bool $throw_exception
     */
    public abstract function insert($col_names_n_vals=array(), $throw_exception=false);

    /**
     * 
     * Updates rows in the model table. Can throw exception (if desired) if the 
     * update operation fails.
     * By default if the update fails just return false. Return an array of the 
     * updated data if the update succeeded.
     * 
     * @param type $col_names_n_values
     * @param type $col_names_n_values_2_match
     * @param type $throw_exception
     */
    public abstract function update(
        $col_names_n_values = array(), 
        $col_names_n_values_2_match = array(),
        $throw_exception = false
    );
    
    //////////////////////////////////////
    //Getters for non-public properties
    //////////////////////////////////////
    
    public function getCreatedTimestampColumnName() {

        return $this->_created_timestamp_column_name;
    }
    
    public function getPrimaryColName() {

        return $this->_primary_col;
    }

    public function getTableCols() {

        return $this->_table_cols;
    }

    public function getTableName() {

        return $this->_table_name;
    }

    public function getUpdatedTimestampColumnName() {

        return $this->_updated_timestamp_column_name;
    }
}

class ModelPrimaryColNameNotSetDuringConstructionException extends \Exception {}
class ModelTableNameNotSetDuringConstructionException extends \Exception {}
class GDAOModelMustImplementMethodException extends \Exception{}