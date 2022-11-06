<?php
declare(strict_types=1);
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
 * @copyright (c) 2022, Rotexsoft
 * 
 */
abstract class Model
{
    /**
     * 
     * Name of the primary key column in the db table associated with this model
     * Default value is null.
     * 
     * This is a REQUIRED field & must be properly set by consumers of this class
     * 
     * @todo Work on supporting tables that don't have any primary key column defined
     * 
     */
    protected string $_primary_col = '';
    
    /**
     *
     * Name of the db table associated with this model
     * 
     * This is a REQUIRED field & must be properly set by consumers of this class
     * 
     */
    protected string $_table_name = '';

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
     * Eg. for a table posts associated with an instance of this model this 
     * array could look like:
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
     */
    protected array $_table_cols = [];

    /**
     * 
     * Name of the collection class for this model. 
     * The class must implement \GDAO\Model\CollectionInterface
     * 
     * This is an OPTIONAL field & may be set by consumers of this class if they
     * would be calling methods of this class that return instance(s) of
     * \GDAO\Model\CollectionInterface or that accept instance(s) of 
     * \GDAO\Model\CollectionInterface as parameters.
     * 
     * Implementers of this class should check that $this->_collection_class_name 
     * has a valid value before attempting to use it inside method(s) they are 
     * implementing.
     * 
     */
    protected ?string $_collection_class_name = null;

    /**
     * 
     * Name of the record class for this model. 
     * The class must implement \GDAO\Model\RecordInterface
     * 
     * This is a REQUIRED field & must be properly set by consumers of this class
     * 
     */
    protected ?string $_record_class_name = null;

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
     */
    protected ?string $_created_timestamp_column_name = null;

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
     */
    protected ?string $_updated_timestamp_column_name = null; //string
    
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    //* The array below ($this->_relations) is for modeling relationships.   *//
    //*                                                                      *//
    //* Four types of relationships are supported:                           *//
    //*  - One-To-One (eg. 1 Post has exactly 1 Summary) a.k.a Has-One       *//
    //*  - One-To-Many (eg. 1 Post has Many Comments) a.k.a Has-Many         *//
    //*  - Many-To-One (eg. Many Posts belong to 1 Author) a.k.a Belongs-To  *//
    //*  - Many-To-Many a.k.a Has-Many-Through                               *//
    //*    (eg. 1 Post has Many Tags through the posts_tags table)           *//
    //*                                                                      *//
    //* It is up to the individual(s) extending this class to implement      *//
    //* relationship related features based on the definition structures     *//
    //* outlined below. Things like eager loading and saving related         *//
    //* records are some of the features that can be implemented             *//
    //* using these relationship definitions.                                *//
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
  
    /**
     * 
     * A 2-dimensional array meant to hold definitions of Belongs-To, Has-One,
     * Has-Many and Has-Many-Through relationships that the table associated  
     * with the current model has with other tables in the database.
     * 
     * The Implementers of this class can use the definition(s) in 
     * \GDAO\Model->_relations to implement retrieval of data from
     * db tables associated with other models related to this model.
     * 
     * This is an OPTIONAL field & may be set by consumers of this class if 
     * they want to define Belongs-To, Has-One, Has-Many or Has-Many-Through 
     * relationship(s) between the current model's db table and other models' db 
     * table(s) in their application using the any of the formats described below.
     * 
     * =====================================
     * // GENERAL STRUCTURE OF THIS ARRAY //
     * =====================================
     * 
     * NOTE: 'relation_name1' and 'relation_nameN' are just placeholders for 
     *       illustration purposes. Consumers of any implementation of this class
     *       are free to name their relations with any name they so desire, as long
     *       as each name is a valid name that can be used for naming the property of
     *       any php class.
     * 
     * \GDAO\Model->_relations = 
     *  [
     *      'relation_name1' => 
     *       [
     *          //the entry below's value must be one of 
     *          // * \GDAO\Model::RELATION_TYPE_HAS_ONE, 
     *          // * \GDAO\Model::RELATION_TYPE_HAS_MANY,
     *          // * \GDAO\Model::RELATION_TYPE_BELONGS_TO or
     *          // * \GDAO\Model::RELATION_TYPE_HAS_MANY_THROUGH.
     *          'relation_type' => \GDAO\Model::RELATION_TYPE_BELONGS_TO, 
     * 
     *          /////////////////////////////////////////////////////////////////////////////////
     *          //the entry below is not needed for \GDAO\Model::RELATION_TYPE_HAS_MANY_THROUGH
     * 
     *          'foreign_key_col_in_my_table' => 'p_author_id',
     *          /////////////////////////////////////////////////////////////////////////////////
     * 
     *          /////////////////////////////////////////////////////////////////////////////////
     *          //the entry below is needed for all types of relationships
     * 
     *          'foreign_table' => 'authors',
     *          /////////////////////////////////////////////////////////////////////////////////
     * 
     *          /////////////////////////////////////////////////////////////////////////////////
     *          //the entry below is not needed for \GDAO\Model::RELATION_TYPE_HAS_MANY_THROUGH
     * 
     *          'foreign_key_col_in_foreign_table' => 'author_id',
     *          /////////////////////////////////////////////////////////////////////////////////
     * 
     * 
     *          /////////////////////////////////////////////////////////////////////////////////
     *          //the entry below is only needed for \GDAO\Model::RELATION_TYPE_HAS_MANY_THROUGH
     * 
     *          'col_in_my_table_linked_to_join_table' => 'post_id',
     *          /////////////////////////////////////////////////////////////////////////////////
     *
     *          /////////////////////////////////////////////////////////////////////////////////
     *          //the entry below is only needed for \GDAO\Model::RELATION_TYPE_HAS_MANY_THROUGH
     *          
     *          'join_table' => 'posts_tags',
     *          /////////////////////////////////////////////////////////////////////////////////
     *          
     *          /////////////////////////////////////////////////////////////////////////////////
     *          //the entry below is only needed for \GDAO\Model::RELATION_TYPE_HAS_MANY_THROUGH
     *          
     *          'col_in_join_table_linked_to_my_table' => 'psts_post_id',
     *          /////////////////////////////////////////////////////////////////////////////////        
     *      
     *          /////////////////////////////////////////////////////////////////////////////////
     *          //the entry below is only needed for \GDAO\Model::RELATION_TYPE_HAS_MANY_THROUGH
     * 
     *          'col_in_join_table_linked_to_foreign_table' => 'psts_tag_id',
     *          /////////////////////////////////////////////////////////////////////////////////        
     *  
     *          /////////////////////////////////////////////////////////////////////////////////
     *          //the entry below is only needed for \GDAO\Model::RELATION_TYPE_HAS_MANY_THROUGH
     * 
     *          'col_in_foreign_table_linked_to_join_table' => 'tag_id',
     *          /////////////////////////////////////////////////////////////////////////////////
     * 
     *          /////////////////////////////////////////////////////////////////////////////////
     *          // The values below are used for instantiating classes needed for
     *          // returning related data.
     *          //
     *          // They must ALL BE SET or ALL NOT BE SET.
     *          // If they are ALL NOT SET, then the related records should be  
     *          // returned using arrays.
     *          //
     *          // Each related record will be stored in an instance of 
     *          // $this->_relations['relation_name1']['foreign_models_record_class_name']
     *          // if set.
     *          //
     *          // The related records for each record of this model will be stored in
     *          // an instance of 
     *          // $this->_relations['relation_name1']['foreign_models_collection_class_name']
     *          // if set.
     *          //
     *          // The records and collections will have their corresponding model set to
     *          // an instance of
     *          // $this->_relations['relation_name1']['foreign_models_class_name']
     *          // if set.
     *          // 
     *          // The value associated with 'foreign_models_class_name', must 
     *          // be a name of any class that is a sub-class of \GDAO\Model.
     *          // 
     *          // The value associated with 'foreign_models_collection_class_name',  
     *          // must be a name of any class that either directly implements
     *          // \GDAO\Model\CollectionInterface or is a sub-class of a class
     *          // that implements \GDAO\Model\CollectionInterface.
     *          // 
     *          // The value associated with 'foreign_models_record_class_name',  
     *          // must be a name of any class that either directly implements
     *          // \GDAO\Model\RecordInterface or is a sub-class of a class that
     *          // implements \GDAO\Model\RecordInterface.
     *          // 
     *          // 'primary_key_col_in_foreign_table' must be set in order to
     *          // be able to create a model instance for the related records.
     *          /////////////////////////////////////////////////////////////////////////////////
     *          
     *          'foreign_models_class_name' => '\\VendorName\\PackageName\\ModelClassName'
     *          'primary_key_col_in_foreign_table' => 'post_id'
     *          'foreign_models_collection_class_name' => '\\VendorName\\PackageName\\ModelClassName\\Collection'
     *          'foreign_models_record_class_name' => '\\VendorName\\PackageName\\ModelClassName\\Record'
     * 
     *          /////////////////////////////////////////////////////////////////////////////////
     *          //The entry below can be used to modify the sql query for retrieving data from
     *          //$this->_relations['relation_name1']['foreign_table'].
     *          //See the documentation for the $params parameter for 
     *          //$this->fetchRecordsIntoCollection(..) in order to understand 
     *          //the expected value(s) that should be set for 
     *          //$this->_relations['relation_name1']['foreign_table_sql_params']
     *          //NOTE: that the `relations_to_include`, `limit_offset` and
     *          //      `limit_size` entries acceptable in the $params parameter 
     *          //      for $this->fetchRecordsIntoCollection(..) should not be included in the  
     *          //      value to be set for $this->_relations['relation_name1']['foreign_table_sql_params']
     * 
     *          'foreign_table_sql_params'=> [....]
     * 
     *          /////////////////////////////////////////////////////////////////////////////////
     *          // An Array of optional extra options to be passed to the foreign model's
     *          // constructor.
     * 
     *          'extra_opts_for_foreign_model'=> [....]
     *          /////////////////////////////////////////////////////////////////////////////////
     *       ],
     *      ......,
     *      ......,
     *      'relation_nameN'=>[ ...]
     *  ]
     * 
     * Example Schema for a `Has-One` relationship
     * ============================================
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
     * modify \GDAO\Model->_relations like below:
     * 
     * \GDAO\Model->_relations['summary'] = 
     *      [
     *          'relation_type' => \GDAO\Model::RELATION_TYPE_HAS_ONE,
     *  
     *          'foreign_key_col_in_my_table' => 'post_id',
     *          
     *          'foreign_table' => 'summaries',
     *          'foreign_key_col_in_foreign_table' => 's_post_id'
     * 
     *          'primary_key_col_in_foreign_table' => 'summary_id'
     *          'foreign_models_class_name' => '\\VendorName\\PackageName\\ModelClassName'
     *          'foreign_models_collection_class_name' => '\\VendorName\\PackageName\\ModelClassName\\Collection'
     *          'foreign_models_record_class_name' => '\\VendorName\\PackageName\\ModelClassName\\Record'
     *      ]
     * 
     * NOTE: the array key value 'summary' is a relation name that can be used to 
     *       later access this particular relationship definition. Any value can 
     *       be used to name a relationship (but it is recommended that it should
     *       not be a name of an existing column in the current model's db table).
     * 
     * NOTE: 'foreign_models_class_name' should contain the name of a Model
     *       class whose _table_name property has the same value as
     *       \GDAO\Model->_relations['relation_name']['foreign_table'].
     *       'relation_name' should be substituted with 'summary' in this case.
     * 
     * Example Schema for a `Belongs-To` relationship
     * ===============================================
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
     * the schema above), modify \GDAO\Model->_relations like below:
     * 
     * \GDAO\Model->_relations['author'] = 
     *      [
     *          'relation_type' => \GDAO\Model::RELATION_TYPE_BELONGS_TO,
     * 
     *          'foreign_key_col_in_my_table' => 'p_author_id',
     *          
     *          'foreign_table' => 'authors',
     *          'foreign_key_col_in_foreign_table' => 'author_id',
     * 
     *          'primary_key_col_in_foreign_table' => 'post_id'
     *          'foreign_models_class_name' => '\\VendorName\\PackageName\\ModelClassName'
     *          'foreign_models_collection_class_name' => '\\VendorName\\PackageName\\ModelClassName\\Collection'
     *          'foreign_models_record_class_name' => '\\VendorName\\PackageName\\ModelClassName\\Record'
     *      ]
     * 
     * NOTE: the array key value 'author' is a relation name that can be used to 
     *       later access this particular relationship definition. Any value can 
     *       be used to name a relationship (but it is recommended that it should 
     *       not be a name of an existing column in the current model's db table).
     * 
     * NOTE: 'foreign_models_class_name' should contain the name of a Model
     *       class whose _table_name property has the same value as
     *       \GDAO\Model->_relations['relation_name']['foreign_table'].
     *       'relation_name' should be substituted with 'author' in this case.
     * 
     * Example Schema for a `Has-Many` relationship
     * =============================================
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
     *      NOTE: the post_id column in the posts table is an auto-incrementing 
     *            integer primary key.
     *     
     *      NOTE: the comment_id column in the comments table is an
     *            auto-incrementing integer primary key.
     *
     * To specify that a model with a \GDAO\Model->_table_name value of 
     * 'posts' has many comments for each post record (based on the schema above),
     * modify \GDAO\Model->_relations like below:
     * 
     * \GDAO\Model->_relations['comments'] = 
     *      [
     *          'relation_type' => \GDAO\Model::RELATION_TYPE_HAS_MANY,
     * 
     *          'foreign_key_col_in_my_table' => 'post_id',
     *          
     *          'foreign_table' => 'comments',
     *          'foreign_key_col_in_foreign_table' => 'c_post_id'
     * 
     *          'primary_key_col_in_foreign_table' => 'comment_id'
     *          'foreign_models_class_name' => '\\VendorName\\PackageName\\ModelClassName'
     *          'foreign_models_collection_class_name' => '\\VendorName\\PackageName\\ModelClassName\\Collection'
     *          'foreign_models_record_class_name' => '\\VendorName\\PackageName\\ModelClassName\\Record'
     *      ]
     * 
     * NOTE: the array key value 'comments' is a relation name that can be used to
     *       later access this particular relationship definition. Any value can be
     *       used to name a relationship (but it is recommended that it should not
     *       be a name of an existing column in the current model's db table).
     * 
     * NOTE: 'foreign_models_class_name' should contain the name of a Model class
     *       whose _table_name property has the same value as
     *       \GDAO\Model->_relations['relation_name']['foreign_table'].
     *       'relation_name' should be substituted with 'comments' in this case.
     * 
     * Example Schema for a `Has-Many-Through` relationship
     * =====================================================
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
     *      NOTE: the post_id column in the posts table is an auto-incrementing
     *            integer primary key.
     *     
     *      NOTE: the tag_id column in the tags table is an auto-incrementing 
     *            integer primary key.
     *     
     *      NOTE: the posts_tags_id column in the posts_tags 
     *            table is an auto-incrementing integer primary key. 
     * 
     * To specify that a model with a \GDAO\Model->_table_name value of 
     * 'posts' has many tags for each post record through a join table called
     * posts_tags (based on the schema above), modify 
     * \GDAO\Model->_relations like below:
     * 
     * \GDAO\Model->_relations['tags'] = 
     *      [
     *          'relation_type' => \GDAO\Model::RELATION_TYPE_HAS_MANY_THROUGH,
     * 
     *          'col_in_my_table_linked_to_join_table' => 'post_id',
     *
     *          'join_table' => 'posts_tags',
     *          'col_in_join_table_linked_to_my_table' => 'psts_post_id',
     *          'col_in_join_table_linked_to_foreign_table' => 'psts_tag_id',
     * 
     *          'foreign_table' => 'tags',
     *          'col_in_foreign_table_linked_to_join_table' => 'tag_id',
     *          
     *          'primary_key_col_in_foreign_table' => 'tag_id'
     *          'foreign_models_class_name' => '\\VendorName\\PackageName\\ModelClassName'
     *          'foreign_models_collection_class_name' => '\\VendorName\\PackageName\\ModelClassName\\Collection'
     *          'foreign_models_record_class_name' => '\\VendorName\\PackageName\\ModelClassName\\Record'
     *      ]
     * 
     * NOTE: the array key value 'tags' is a relation name that can be used to 
     *       later access this particular relationship definition. Any value can 
     *       be used to name a relationship (but it is recommended that it should
     *       not be a name of an existing column in the current model's db table).
     * 
     * NOTE: 'foreign_models_class_name' should contain the name of a Model class 
     *       whose _table_name property has the same value as
     *       \GDAO\Model->_relations['relation_name']['foreign_table'].
     *       'relation_name' should be substituted with 'tags' in this case.
     * 
     */
    protected array $_relations = [];
    
    public const RELATION_TYPE_HAS_ONE = 'rt_ho';
    public const RELATION_TYPE_HAS_MANY = 'rt_hm';
    public const RELATION_TYPE_BELONGS_TO = 'rt_bt';
    public const RELATION_TYPE_HAS_MANY_THROUGH = 'rt_hmt';

    /**
     * 
     * A PDO compliant Data Source Name (DSN) string containing the information 
     * required to connect to a desired database.
     * 
     * @see \PDO::__construct() See description of the 1st parameter 
     *                          (http://php.net/manual/en/pdo.construct.php) if 
     *                          this Model will indeed be powered by a PDO instance
     * 
     */
    protected string $_dsn = '';
    
    /**
     *
     * The username for the database to be connected to.
     * 
     * @see \PDO::__construct() See description of the 2nd parameter 
     *                          (http://php.net/manual/en/pdo.construct.php) if 
     *                          this Model will indeed be powered by a PDO instance
     * 
     */
    protected string $_username = ''; 
    
    /**
     *
     * The password for the database to be connected to.
     * 
     * @see \PDO::__construct() See description of the 3rd parameter 
     *                          (http://php.net/manual/en/pdo.construct.php) if 
     *                          this Model will indeed be powered by a PDO 
     *                          instance
     * 
     */
    protected string $_passwd = '';
    
    /**
     *
     * An array of options for a PDO driver
     * 
     * @see \PDO::__construct() See description of the 4th parameter 
     *                          (http://php.net/manual/en/pdo.construct.php) if 
     *                          this Model will indeed be powered by a PDO instance
     * 
     */
    protected array $_pdo_driver_opts = [];
    
    /**
     * 
     * @param string $dsn
     * @param string $username
     * @param string $passwd
     * @param array $pdo_driver_opts see \PDO::setAttribute(..) documentation
     * @param array $extra_opts an array that may be used to pass initialization 
     *                          value(s) for protected and / or private properties
     *                          of this class
     * 
     * @see \PDO::__construct(...) for definition of first four parameters
     * 
     * @throws \GDAO\ModelPrimaryColNameNotSetDuringConstructionException
     * @throws \GDAO\ModelTableNameNotSetDuringConstructionException
     * 
     */
    public function __construct(
        string $dsn = '',
        string $username = '', 
        string $passwd = '', 
        array $pdo_driver_opts = [],
        array $extra_opts = []
    ) {
        $this->_dsn = $dsn;
        $this->_username = $username;
        $this->_passwd = $passwd;
        $this->_pdo_driver_opts = $pdo_driver_opts;
        
        //set properties of this class specified in $extra_opts
        foreach($extra_opts as $e_opt_key => $e_opt_val) {
  
            if ( property_exists($this, $e_opt_key) ) {
                
                $this->$e_opt_key = $e_opt_val;

            } elseif ( property_exists($this, '_'.$e_opt_key) ) {

                $this->{"_$e_opt_key"} = $e_opt_val;
            }
        }
        
        if( strlen($this->_primary_col) <= 0 ) {
            
            $msg = 'Primary Key Column name ($_primary_col) not set for '.get_class($this);
            throw new ModelPrimaryColNameNotSetDuringConstructionException($msg);
        }
        
        if( strlen($this->_table_name) <= 0 ) {
            
            $msg = 'Table name ($_table_name) not set for '.get_class($this);
            throw new ModelTableNameNotSetDuringConstructionException($msg);
        }
    }

    /**
     * 
     * Implementers of this class can implement magic methods by overriding this method.
     * 
     * For example $this->fetchOneByIdAndTitle(1, 'A title!') will lead to this 
     * method being called (since fetchOneByIdAndTitle() doesn't exist in this
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
     * Returns a string representation of an instance of this class.
     */
    public function __toString(): string {

        return var_export($this->toArray(), true);
    }

    /**
     * 
     * Returns an array representation of an instance of this class.
     * 
     * @return array an array representation of an instance of this class.
     * 
     */
    public function toArray(): array {

        return get_object_vars($this);
    }
    
    /**
     * 
     * Create and return a new collection of zero or more records (instances of \GDAO\Model\RecordInterface).
     * 
     * This method is not declared abstract in order to allow both implementers
     * and consumers of this API to be able to implement or use this API without
     * collections. The Model and Record classes are mandatory, the collection 
     * class is optional(php arrays are a good & natively available alternative).
     * 
     * @param array $extra_opts an array of other parameters that may be needed 
     *                          in creating an instance of \GDAO\Model\Collection
     * @param \GDAO\Model\RecordInterface[] $list_of_records 
     * 
     * @return \GDAO\Model\CollectionInterface a collection of instances of \GDAO\Model\RecordInterface.
     * 
     */
    public function createNewCollection(array $extra_opts=[], \GDAO\Model\RecordInterface ...$list_of_records): \GDAO\Model\CollectionInterface {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new ModelMustImplementMethodException($msg);
    }
    
    /**
     * 
     * Create and return a new record with specified values.
     * 
     * @param array $col_names_and_values
     * @param array $extra_opts an array of other parameters that may be needed 
     *                          in creating an instance of \GDAO\Model\RecordInterface
     * 
     * @return \GDAO\Model\RecordInterface new record with specified values.
     * 
     */
    public abstract function createNewRecord(array $col_names_and_values = [], array $extra_opts=[]): \GDAO\Model\RecordInterface;

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
     * @return int|null the number of rows deleted if deletion was successful, 
     *                  OR null if nothing was deleted (no matching records).
     * 
     * @throws \PDOException
     * 
     */
    public abstract function deleteMatchingDbTableRows(array $cols_n_vals=[]): ?int;

    /**
     * Delete the specified record from the database.
     * 
     * NOTE: Implementers of this class must set the record object to a new state 
     *       by a call to $record->setStateToNew() after a successful deletion 
     *       via this method. The record data will still be inside the record 
     *       object.
     * 
     * @param \GDAO\Model\RecordInterface $record
     * 
     * @return bool|null true for a successful deletion, false if deletion failed 
     *                   OR null if supplied record is a record that has never 
     *                   been saved to the db.
     * 
     * @throws \PDOException
     * 
     */
    public abstract function deleteSpecifiedRecord(\GDAO\Model\RecordInterface $record): ?bool;
    
    /**
     * 
     * Fetch a collection (an instance of GDAO\Model\CollectionInterface) of 
     * records (instances of \GDAO\Model\RecordInterface) 
     * [Eager Loading should be implemented here].
     * 
     * This method is not declared abstract in order to allow both implementers
     * and consumers of this API to be able to implement or use this API without
     * collections. The Model and Record classes are mandatory, the collection 
     * class is optional(php arrays are a good & natively available alternative).
     * 
     * The methods described below must be implemented by implementers of this package
     * These methods will help build the sql query that will be used to perform the fetch
     * and must be called before this method is called.
     * These methods must return the instance of this class that they were called on.
     * If none of these methods are called before the fetch, the fetch should be done
     * with the generic query below
     *          SELECT * FROM $this->getTableName()
     * 
     *  `relations_to_include` a method like withRelations that accepts an array of
     *                         relation names as defined in \GDAO\Model->_relations. 
     *                          Eager-fetch related rows of data for each relation name.
     * 
     *        NOTE: each key in the \GDAO\Model->_relations array is a 
     *              relation name. Eg. array_keys($this->_relations)
     *              returns an array of relation name(s) for a model.
     * 
     *        NOTE: Implementers of this class should make the retrieved related
     *              data accessible in each record via a property named with the
     *              same name as the relation name. For example, if there exists
     *              $this->_relations['comments'], the retrieved 
     *              comments for each record returned by this fetch method should
     *              be accessible via $record->comments. Where $record is a 
     *              reference to one of the records returned by this method.
     *
     *  `distinct` a method for adding the DISTINCT keyword to the query
     * 
     *  `cols` method that generates db column names or expressions to return in a SELECT query
     *      Eg: SELECT col_1, col_2, col_3
     *      : An array of the name(s) of column(s) to be returned.
     *        Expressions like 'COUNT(col_name) AS some_col' should be allowed as a column name.
     * 
     *        NOTE: If `cols` is not set/specified or is assigned an empty array
     *              value, implementers of this class should select all columns
     *              from the table associated with the model 
     *              (ie. SELECT * FROM models_table ..... ).
     * 
     *  `where` methods like where & orWhere for generating a WHERE clause, 
     *        Eg:
     *          WHERE column_name_1 > 58 AND column_name_2 > 58
     *             OR (column_name_1 < 58 AND column_name_2 < 58)
     *            AND column_name_3 >= 58
     *             OR (column_name_4 = 58 AND column_name_5 = 58)
     *
     *  `groupBy` method for generating a GROUP BY clause:
     *        Eg: GROUP BY column_name_1, column_name_2
     * 
     *  `having` methods like having & orHaving that can generate a HAVING clause.
     *        Eg: 
     *          HAVING column_name_1 > 58 AND column_name_2 > 58
     *              OR (column_name_1 < 58 AND column_name_2 < 58)
     *             AND column_name_3 >= 58
     *              OR (column_name_4 = 58 AND column_name_5 = 58)
     * 
     *  `order` method
     *      : for building an ORDER BY clause.
     *        Eg: 'ORDER BY col_1 ASC, col_2 DESC'
     * 
     * `limit` method that builds the appropriate limit clause for the db being used
     *      `limit_offset`
     *          : (int) Limit offset. Offset of the first row to return
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
     *      `limit_size`
     *          : (int) Limit to a count of this many records.
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
     * @return \GDAO\Model\CollectionInterface|bool return a collection of matched record object(s) or false if no matching record(s) were found 
     * 
     * @throws \PDOException
     * 
     */
    public function fetchRecordsIntoCollection(?object $query=null, array $relations_to_include=[]) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new ModelMustImplementMethodException($msg);
    }

    /**
     * 
     * Fetch an array of records (instances of \GDAO\Model\RecordInterface or 
     * any of its sub-classes) [Eager Loading should be considered here].
     * 
     * This method is not declared abstract in order to allow both implementers
     * and consumers of this API to be able to implement or use this API without
     * collections. The Model and Record classes are mandatory, the collection 
     * class is optional(php arrays are a good & natively available alternative).
     * 
     * The methods described below must be implemented by implementers of this package
     * These methods will help build the sql query that will be used to perform the fetch
     * and must be called before this method is called.
     * These methods must return the instance of this class that they were called on.
     * If none of these methods are called before the fetch, the fetch should be done
     * with the generic query below
     *          SELECT * FROM $this->getTableName()
     * 
     *  `relations_to_include` a method like withRelations that accepts an array of
     *                         relation names as defined in \GDAO\Model->_relations. 
     *                          Eager-fetch related rows of data for each relation name.
     * 
     *        NOTE: each key in the \GDAO\Model->_relations array is a 
     *              relation name. Eg. array_keys($this->_relations)
     *              returns an array of relation name(s) for a model.
     * 
     *        NOTE: Implementers of this class should make the retrieved related
     *              data accessible in each record via a property named with the
     *              same name as the relation name. For example, if there exists
     *              $this->_relations['comments'], the retrieved 
     *              comments for each record returned by this fetch method should
     *              be accessible via $record->comments. Where $record is a 
     *              reference to one of the records returned by this method.
     *
     *  `distinct` a method for adding the DISTINCT keyword to the query
     * 
     *  `cols` method that generates db column names or expressions to return in a SELECT query
     *      Eg: SELECT col_1, col_2, col_3
     *      : An array of the name(s) of column(s) to be returned.
     *        Expressions like 'COUNT(col_name) AS some_col' should be allowed as a column name.
     * 
     *        NOTE: If `cols` is not set/specified or is assigned an empty array
     *              value, implementers of this class should select all columns
     *              from the table associated with the model 
     *              (ie. SELECT * FROM models_table ..... ).
     * 
     *  `where` methods like where & orWhere for generating a WHERE clause, 
     *        Eg:
     *          WHERE column_name_1 > 58 AND column_name_2 > 58
     *             OR (column_name_1 < 58 AND column_name_2 < 58)
     *            AND column_name_3 >= 58
     *             OR (column_name_4 = 58 AND column_name_5 = 58)
     *
     *  `groupBy` method for generating a GROUP BY clause:
     *        Eg: GROUP BY column_name_1, column_name_2
     * 
     *  `having` methods like having & orHaving that can generate a HAVING clause.
     *        Eg: 
     *          HAVING column_name_1 > 58 AND column_name_2 > 58
     *              OR (column_name_1 < 58 AND column_name_2 < 58)
     *             AND column_name_3 >= 58
     *              OR (column_name_4 = 58 AND column_name_5 = 58)
     * 
     *  `order` method
     *      : for building an ORDER BY clause.
     *        Eg: 'ORDER BY col_1 ASC, col_2 DESC'
     * 
     * `limit` method that builds the appropriate limit clause for the db being used
     *      `limit_offset`
     *          : (int) Limit offset. Offset of the first row to return
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
     *      `limit_size`
     *          : (int) Limit to a count of this many records.
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
     * @return array of records (instances of \GDAO\Model\RecordInterface).
     * 
     * @throws \PDOException
     * 
     */
    public abstract function fetchRecordsIntoArray(?object $query=null, array $relations_to_include=[]): array;

    /**
     *
     * Fetch an array of db data. Each record is an associative array and not an
     * instance of \GDAO\Model\RecordInterface [Eager Loading should be considered here].
     *
     * The methods described below must be implemented by implementers of this package
     * These methods will help build the sql query that will be used to perform the fetch
     * and must be called before this method is called.
     * These methods must return the instance of this class that they were called on.
     * If none of these methods are called before the fetch, the fetch should be done
     * with the generic query below
     *          SELECT * FROM $this->getTableName()
     * 
     *  `relations_to_include` a method like withRelations that accepts an array of
     *                         relation names as defined in \GDAO\Model->_relations. 
     *                          Eager-fetch related rows of data for each relation name.
     * 
     *        NOTE: each key in the \GDAO\Model->_relations array is a 
     *              relation name. Eg. array_keys($this->_relations)
     *              returns an array of relation name(s) for a model.
     * 
     *        NOTE: Implementers of this class should make the retrieved related
     *              data accessible in each record via a property named with the
     *              same name as the relation name. For example, if there exists
     *              $this->_relations['comments'], the retrieved 
     *              comments for each record returned by this fetch method should
     *              be accessible via $record->comments. Where $record is a 
     *              reference to one of the records returned by this method.
     *
     *  `distinct` a method for adding the DISTINCT keyword to the query
     * 
     *  `cols` method that generates db column names or expressions to return in a SELECT query
     *      Eg: SELECT col_1, col_2, col_3
     *      : An array of the name(s) of column(s) to be returned.
     *        Expressions like 'COUNT(col_name) AS some_col' should be allowed as a column name.
     * 
     *        NOTE: If `cols` is not set/specified or is assigned an empty array
     *              value, implementers of this class should select all columns
     *              from the table associated with the model 
     *              (ie. SELECT * FROM models_table ..... ).
     * 
     *  `where` methods like where & orWhere for generating a WHERE clause, 
     *        Eg:
     *          WHERE column_name_1 > 58 AND column_name_2 > 58
     *             OR (column_name_1 < 58 AND column_name_2 < 58)
     *            AND column_name_3 >= 58
     *             OR (column_name_4 = 58 AND column_name_5 = 58)
     *
     *  `groupBy` method for generating a GROUP BY clause:
     *        Eg: GROUP BY column_name_1, column_name_2
     * 
     *  `having` methods like having & orHaving that can generate a HAVING clause.
     *        Eg: 
     *          HAVING column_name_1 > 58 AND column_name_2 > 58
     *              OR (column_name_1 < 58 AND column_name_2 < 58)
     *             AND column_name_3 >= 58
     *              OR (column_name_4 = 58 AND column_name_5 = 58)
     * 
     *  `order` method
     *      : for building an ORDER BY clause.
     *        Eg: 'ORDER BY col_1 ASC, col_2 DESC'
     * 
     * `limit` method that builds the appropriate limit clause for the db being used
     *      `limit_offset`
     *          : (int) Limit offset. Offset of the first row to return
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
     *      `limit_size`
     *          : (int) Limit to a count of this many records.
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
     *
     * @throws \PDOException
     * @return mixed[]
     */
    public abstract function fetchRowsIntoArray(?object $query=null, array $relations_to_include=[]): array;

    /**
     *
     * Fetch an array of values for a specified column.
     *
     * The methods described below must be implemented by implementers of this package
     * These methods will help build the sql query that will be used to perform the fetch
     * and must be called before this method is called.
     * These methods must return the instance of this class that they were called on.
     * If none of these methods are called before the fetch, the fetch should be done
     * with the generic query below
     *          SELECT * FROM $this->getTableName()
     * 
     *  `relations_to_include` a method like withRelations that accepts an array of
     *                         relation names as defined in \GDAO\Model->_relations. 
     *                          Eager-fetch related rows of data for each relation name.
     * 
     *        NOTE: each key in the \GDAO\Model->_relations array is a 
     *              relation name. Eg. array_keys($this->_relations)
     *              returns an array of relation name(s) for a model.
     * 
     *        NOTE: Implementers of this class should make the retrieved related
     *              data accessible in each record via a property named with the
     *              same name as the relation name. For example, if there exists
     *              $this->_relations['comments'], the retrieved 
     *              comments for each record returned by this fetch method should
     *              be accessible via $record->comments. Where $record is a 
     *              reference to one of the records returned by this method.
     *
     *  `distinct` a method for adding the DISTINCT keyword to the query
     * 
     *  `cols` method that generates db column names or expressions to return in a SELECT query
     *      Eg: SELECT col_1, col_2, col_3
     *      : An array of the name(s) of column(s) to be returned.
     *        Expressions like 'COUNT(col_name) AS some_col' should be allowed as a column name.
     * 
     *        NOTE: If `cols` is not set/specified or is assigned an empty array
     *              value, implementers of this class should select all columns
     *              from the table associated with the model 
     *              (ie. SELECT * FROM models_table ..... ).
     * 
     *  `where` methods like where & orWhere for generating a WHERE clause, 
     *        Eg:
     *          WHERE column_name_1 > 58 AND column_name_2 > 58
     *             OR (column_name_1 < 58 AND column_name_2 < 58)
     *            AND column_name_3 >= 58
     *             OR (column_name_4 = 58 AND column_name_5 = 58)
     *
     *  `groupBy` method for generating a GROUP BY clause:
     *        Eg: GROUP BY column_name_1, column_name_2
     * 
     *  `having` methods like having & orHaving that can generate a HAVING clause.
     *        Eg: 
     *          HAVING column_name_1 > 58 AND column_name_2 > 58
     *              OR (column_name_1 < 58 AND column_name_2 < 58)
     *             AND column_name_3 >= 58
     *              OR (column_name_4 = 58 AND column_name_5 = 58)
     * 
     *  `order` method
     *      : for building an ORDER BY clause.
     *        Eg: 'ORDER BY col_1 ASC, col_2 DESC'
     * 
     * `limit` method that builds the appropriate limit clause for the db being used
     *      `limit_offset`
     *          : (int) Limit offset. Offset of the first row to return
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
     *      `limit_size`
     *          : (int) Limit to a count of this many records.
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
     * @throws \PDOException
     * @return mixed[]
     */
    public abstract function fetchCol(?object $query=null): array;

    /**
     * 
     * Fetch a single record matching the specified params.
     * 
     * The methods described below must be implemented by implementers of this package
     * These methods will help build the sql query that will be used to perform the fetch
     * and must be called before this method is called.
     * These methods must return the instance of this class that they were called on.
     * If none of these methods are called before the fetch, the fetch should be done
     * with the generic query below (returning the first row)
     *          SELECT * FROM $this->getTableName() 
     * 
     *  `relations_to_include` a method like withRelations that accepts an array of
     *                         relation names as defined in \GDAO\Model->_relations. 
     *                          Eager-fetch related rows of data for each relation name.
     * 
     *        NOTE: each key in the \GDAO\Model->_relations array is a 
     *              relation name. Eg. array_keys($this->_relations)
     *              returns an array of relation name(s) for a model.
     * 
     *        NOTE: Implementers of this class should make the retrieved related
     *              data accessible in each record via a property named with the
     *              same name as the relation name. For example, if there exists
     *              $this->_relations['comments'], the retrieved 
     *              comments for each record returned by this fetch method should
     *              be accessible via $record->comments. Where $record is a 
     *              reference to one of the records returned by this method.
     *
     *  `distinct` a method for adding the DISTINCT keyword to the query
     * 
     *  `cols` method that generates db column names or expressions to return in a SELECT query
     *      Eg: SELECT col_1, col_2, col_3
     *      : An array of the name(s) of column(s) to be returned.
     *        Expressions like 'COUNT(col_name) AS some_col' should be allowed as a column name.
     * 
     *        NOTE: If `cols` is not set/specified or is assigned an empty array
     *              value, implementers of this class should select all columns
     *              from the table associated with the model 
     *              (ie. SELECT * FROM models_table ..... ).
     * 
     *  `where` methods like where & orWhere for generating a WHERE clause, 
     *        Eg:
     *          WHERE column_name_1 > 58 AND column_name_2 > 58
     *             OR (column_name_1 < 58 AND column_name_2 < 58)
     *            AND column_name_3 >= 58
     *             OR (column_name_4 = 58 AND column_name_5 = 58)
     *
     *  `groupBy` method for generating a GROUP BY clause:
     *        Eg: GROUP BY column_name_1, column_name_2
     * 
     *  `having` methods like having & orHaving that can generate a HAVING clause.
     *        Eg: 
     *          HAVING column_name_1 > 58 AND column_name_2 > 58
     *              OR (column_name_1 < 58 AND column_name_2 < 58)
     *             AND column_name_3 >= 58
     *              OR (column_name_4 = 58 AND column_name_5 = 58)
     * 
     *  `order` method
     *      : for building an ORDER BY clause.
     *        Eg: 'ORDER BY col_1 ASC, col_2 DESC'
     * 
     * `limit` method that builds the appropriate limit clause for the db being used
     *      `limit_offset`
     *          : (int) Limit offset. Offset of the first row to return
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
     *      `limit_size`
     *          : (int) Limit to a count of this many records.
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
     * @return \GDAO\Model\RecordInterface|bool return a record object if found or false if no matching record was found
     * 
     * @throws \PDOException
     * 
     */
    public abstract function fetchOneRecord(?object $query=null, array $relations_to_include=[]);

    /**
     *
     * Fetch an array of key-value pairs from the db table, where the 
     * 1st column's value is the key and the 2nd column's value is the value.
     *
     * The methods described below must be implemented by implementers of this package
     * These methods will help build the sql query that will be used to perform the fetch
     * and must be called before this method is called.
     * These methods must return the instance of this class that they were called on.
     * If none of these methods are called before the fetch, the fetch should be done
     * with the generic query below
     *          SELECT * FROM $this->getTableName()
     * 
     *  `relations_to_include` a method like withRelations that accepts an array of
     *                         relation names as defined in \GDAO\Model->_relations. 
     *                          Eager-fetch related rows of data for each relation name.
     * 
     *        NOTE: each key in the \GDAO\Model->_relations array is a 
     *              relation name. Eg. array_keys($this->_relations)
     *              returns an array of relation name(s) for a model.
     * 
     *        NOTE: Implementers of this class should make the retrieved related
     *              data accessible in each record via a property named with the
     *              same name as the relation name. For example, if there exists
     *              $this->_relations['comments'], the retrieved 
     *              comments for each record returned by this fetch method should
     *              be accessible via $record->comments. Where $record is a 
     *              reference to one of the records returned by this method.
     *
     *  `distinct` a method for adding the DISTINCT keyword to the query
     * 
     *  `cols` method that generates db column names or expressions to return in a SELECT query
     *      Eg: SELECT col_1, col_2, col_3
     *      : An array of the name(s) of column(s) to be returned.
     *        Expressions like 'COUNT(col_name) AS some_col' should be allowed as a column name.
     * 
     *        NOTE: If `cols` is not set/specified or is assigned an empty array
     *              value, implementers of this class should select all columns
     *              from the table associated with the model 
     *              (ie. SELECT * FROM models_table ..... ).
     * 
     *  `where` methods like where & orWhere for generating a WHERE clause, 
     *        Eg:
     *          WHERE column_name_1 > 58 AND column_name_2 > 58
     *             OR (column_name_1 < 58 AND column_name_2 < 58)
     *            AND column_name_3 >= 58
     *             OR (column_name_4 = 58 AND column_name_5 = 58)
     *
     *  `groupBy` method for generating a GROUP BY clause:
     *        Eg: GROUP BY column_name_1, column_name_2
     * 
     *  `having` methods like having & orHaving that can generate a HAVING clause.
     *        Eg: 
     *          HAVING column_name_1 > 58 AND column_name_2 > 58
     *              OR (column_name_1 < 58 AND column_name_2 < 58)
     *             AND column_name_3 >= 58
     *              OR (column_name_4 = 58 AND column_name_5 = 58)
     * 
     *  `order` method
     *      : for building an ORDER BY clause.
     *        Eg: 'ORDER BY col_1 ASC, col_2 DESC'
     * 
     * `limit` method that builds the appropriate limit clause for the db being used
     *      `limit_offset`
     *          : (int) Limit offset. Offset of the first row to return
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
     *      `limit_size`
     *          : (int) Limit to a count of this many records.
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
     *
     * @throws \PDOException
     * @return mixed[]
     */
    public abstract function fetchPairs(?object $query=null): array;

    /**
     * 
     * Fetch a single value from the db table matching params.
     * 
     * The methods described below must be implemented by implementers of this package
     * These methods will help build the sql query that will be used to perform the fetch
     * and must be called before this method is called.
     * These methods must return the instance of this class that they were called on.
     * If none of these methods are called before the fetch, the fetch should be done
     * with the generic query below
     *          SELECT * FROM $this->getTableName()
     * 
     *  `relations_to_include` a method like withRelations that accepts an array of
     *                         relation names as defined in \GDAO\Model->_relations. 
     *                          Eager-fetch related rows of data for each relation name.
     * 
     *        NOTE: each key in the \GDAO\Model->_relations array is a 
     *              relation name. Eg. array_keys($this->_relations)
     *              returns an array of relation name(s) for a model.
     * 
     *        NOTE: Implementers of this class should make the retrieved related
     *              data accessible in each record via a property named with the
     *              same name as the relation name. For example, if there exists
     *              $this->_relations['comments'], the retrieved 
     *              comments for each record returned by this fetch method should
     *              be accessible via $record->comments. Where $record is a 
     *              reference to one of the records returned by this method.
     *
     *  `distinct` a method for adding the DISTINCT keyword to the query
     * 
     *  `cols` method that generates db column names or expressions to return in a SELECT query
     *      Eg: SELECT col_1, col_2, col_3
     *      : An array of the name(s) of column(s) to be returned.
     *        Expressions like 'COUNT(col_name) AS some_col' should be allowed as a column name.
     * 
     *        NOTE: If `cols` is not set/specified or is assigned an empty array
     *              value, implementers of this class should select all columns
     *              from the table associated with the model 
     *              (ie. SELECT * FROM models_table ..... ).
     * 
     *  `where` methods like where & orWhere for generating a WHERE clause, 
     *        Eg:
     *          WHERE column_name_1 > 58 AND column_name_2 > 58
     *             OR (column_name_1 < 58 AND column_name_2 < 58)
     *            AND column_name_3 >= 58
     *             OR (column_name_4 = 58 AND column_name_5 = 58)
     *
     *  `groupBy` method for generating a GROUP BY clause:
     *        Eg: GROUP BY column_name_1, column_name_2
     * 
     *  `having` methods like having & orHaving that can generate a HAVING clause.
     *        Eg: 
     *          HAVING column_name_1 > 58 AND column_name_2 > 58
     *              OR (column_name_1 < 58 AND column_name_2 < 58)
     *             AND column_name_3 >= 58
     *              OR (column_name_4 = 58 AND column_name_5 = 58)
     * 
     *  `order` method
     *      : for building an ORDER BY clause.
     *        Eg: 'ORDER BY col_1 ASC, col_2 DESC'
     * 
     * `limit` method that builds the appropriate limit clause for the db being used
     *      `limit_offset`
     *          : (int) Limit offset. Offset of the first row to return
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
     *      `limit_size`
     *          : (int) Limit to a count of this many records.
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
     * @return mixed A single value either from a column in a row of the db table 
     *               associated with this model or the result of a sql aggregate
     *               function (eg. MAX(col_name)), or null if no matching record 
     *               was found.
     * 
     * @throws \PDOException
     * 
     */
    public abstract function fetchValue(?object $query=null);

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
    public abstract function getPDO(): \PDO;

    /**
     * 
     * Insert one row to the model table with the specified values.
     * 
     * An exception (\GDAO\ModelPrimaryColValueNotRetrievableAfterInsertException)
     * should be thrown if the primary key col is auto-incrementing and the 
     * auto-incremented primary-key value of the inserted record could not be 
     * retrieved (in this case the insert could have still been successful).
     * 
     * An exception (\GDAO\ModelInvalidInsertValueSuppliedException) should be
     * thrown if any of the values supplied for insertion is not a boolean, NULL,
     * number or string value (this should happen before even attempting to 
     * perform the insert).
     * 
     * @param array $data_2_insert an array whose keys are the names of columns
     *                                in the database and whose corresponding values
     *                                are the values to be inserted in each column.
     *                                The values must be one of these types:
     *                                boolean, numeric, NULL or string.
     *                                Implementers of this class should check
     *                                that the supplied values are of the expected 
     *                                type, else they should throw the following
     *                                exception: \GDAO\ModelInvalidInsertValueSuppliedException
     * 
     * @return bool|array false if insert failed, else if the insert succeeded 
     *                    return an array of the inserted data including 
     *                    auto-incremented primary-key value (if the 
     *                    primary key col is auto-incrementing). 
     *                    A \PDOException will be automatically thrown if things 
     *                    fail at the PDO level.
     * 
     * @throws \PDOException
     * @throws \GDAO\ModelInvalidInsertValueSuppliedException
     * @throws \GDAO\ModelPrimaryColValueNotRetrievableAfterInsertException
     * 
     */
    public abstract function insert(array $data_2_insert=[]);

    /**
     * 
     * Insert one or more rows to the model table with the specified values.
     * It is meant to batch all the data to be inserted into one sql query. 
     * Eg:
     *   $this->insertMany(
     *             [ 
     *               ['a'=>1, 'b'=>2, 'c'=>3],
     *               ['a'=>4, 'b'=>5, 'c'=>6],
     *               ['a'=>7, 'b'=>8, 'c'=>9]
     *             ]
     *          );
     * 
     *   Should generate the following kind of sql statement:
     * 
     *      INSERT INTO tbl_name 
     *                  (a,b,c)
     *           VALUES (1,2,3),
     *                  (4,5,6),
     *                  (7,8,9);
     * 
     * NOTE: Implementers of this API SHOULD NOT use multiple sql statements or
     *       repeated calls to $this->insert() to implement this functionality.
     * 
     * An exception (\GDAO\ModelInvalidInsertValueSuppliedException) should be
     * thrown if any of the values supplied for insertion is not a boolean, NULL,
     * number or string value (this should happen before even attempting to 
     * perform the insert).
     * 
     * @param array $rows_of_data_2_insert an array of arrays where each subarray 
     *                                     holds data for a new row to be inserted 
     *                                     into the db table. Each subarray's keys 
     *                                     are the names of columns in the database 
     *                                     and the corresponding values are the values 
     *                                     to be inserted in each column.
     *                                     The values must be one of these types:
     *                                     boolean, numeric, NULL or string.
     *                                     Implementers of this class should check
     *                                     that the supplied values are of the expected 
     *                                     type, else they should throw the following
     *                                     exception: \GDAO\ModelInvalidInsertValueSuppliedException
     * 
     * @return bool|array false if insert failed, true if the insert succeeded. 
     *                    A \PDOException will be automatically thrown if things 
     *                    fail at the PDO level.
     * 
     * @throws \PDOException
     * @throws \GDAO\ModelInvalidInsertValueSuppliedException
     * 
     */
    public abstract function insertMany(array $rows_of_data_2_insert=[]);

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
     * @throws \PDOException
     * @throws \GDAO\ModelInvalidUpdateValueSuppliedException
     * 
     */
    public abstract function updateMatchingDbTableRows(
        array $col_names_n_values_2_save = [], 
        array $col_names_n_values_2_match = []
    );
    
    /**
     * 
     * Update the specified record in the database.
     * Save all fields in the specified record to the corresponding row in the db.
     * 
     * @param \GDAO\Model\RecordInterface $record
     * 
     * @return bool|null true for a successful update, false for a failed update 
     *                   OR null if supplied record is a record that has never been
     *                   saved to the db.
     * 
     * @throws \PDOException
     * 
     */
    public abstract function updateSpecifiedRecord(\GDAO\Model\RecordInterface $record): ?bool;
    
    //////////////////////////////////////
    // Getters for non-public properties
    //////////////////////////////////////
    /**
     *
     * Get the value of $this->_created_timestamp_column_name.
     *
     * @return string|null the value of $this->_created_timestamp_column_name.
     */
    public function getCreatedTimestampColumnName(): ?string {

        return $this->_created_timestamp_column_name;
    }
    
    /**
     * 
     * Get the value of $this->_primary_col.
     * 
     * @param bool $prepend_table_name true to return "{$this->_table_name}.{$this->_primary_col}"
     *                                 or false to return "{$this->_primary_col}"
     * 
     * @return string the value of $this->_primary_col.
     * 
     */
    public function getPrimaryColName(bool $prepend_table_name=false): string {

        return $prepend_table_name ?
                "{$this->_table_name}.{$this->_primary_col}" : $this->_primary_col;
    }

    /**
     *
     * Get the value of $this->_table_name.
     *
     * @return string the value of $this->_table_name.
     */
    public function getTableName(): string {

        return $this->_table_name;
    }
    
    /**
     * 
     * Get an array of table column names.
     * 
     * @return array an array of table column names.
     * 
     */
    public function getTableColNames(): array {

        $keys = array_keys($this->_table_cols);
        
        if( $keys === range(0, count($this->_table_cols) - 1) ) {
            
            //$this->_table_cols is a sequential array with numeric keys
            //its values are most likely to be column names
            return $this->_table_cols;

        } else {
            
            $keys_2_return = [];
            
            foreach($this->_table_cols as $key => $potential_col_metadata) {
                
                if( is_string($key) ) {
                    
                    $keys_2_return[] = $key;
                    
                } elseif( is_string($potential_col_metadata) ) {
                    
                    $keys_2_return[] = $potential_col_metadata;
                }
            }

            return $keys_2_return;
        }
    }

    /**
     * 
     * Get an array of relation names defined in $this->_relations.
     * 
     * @return array an array of relation names defined in $this->_relations.
     * 
     */
    public function getRelationNames(): array {

        return array_keys($this->_relations);
    }

    /**
     *
     * Get the value of $this->_updated_timestamp_column_name.
     *
     * @return string|null the value of $this->_updated_timestamp_column_name.
     */
    public function getUpdatedTimestampColumnName(): ?string {

        return $this->_updated_timestamp_column_name;
    }
}
