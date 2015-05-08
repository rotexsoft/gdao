<?php

namespace GDAO;

/**
 * Assumptions with this API. 
 * DB has a single auto-incrementing numeric primary key column 
 * 
 * This API is architected with the intent of having Records and Collections 
 * created via the Model.
 * 
 * Users of any implementation of this API should not be directly instantiating 
 * new Collections or Records they should be doing this by calling the appropriate
 * implementation of
 * \GDAO\Model::createCollection(..) or \GDAO\Model::createRecord(..)
 * 
 * 
// Relationships should be handled using __call and/or __get at the Record level
// Need to add mechanism for paging, e.g. limit and offset in mysql.
 */

/**
 * Description of Model
 *
 * @author aadegbam
 */
abstract class Model
{
    protected $_primary_col = 'id';                     //The column name for the 
    //primary key; default is 'id'.

    /**
     * Name of the collection class for this model. 
     * Must be a descendant of \GDAO\Model\Collection
     * 
     * @var string 
     */
    protected $_collection_class_name = null;

    /**
     * Name of the record class for this model. 
     * Must be a descendant of \GDAO\Model\Record
     * 
     * @var string 
     */
    protected $_record_class_name = null;

    protected $_table_name = null;                      //The table name.

    protected $_table_cols = array();                   //The column specification 
    //array for all columns 
    //in this table

    protected $_created_timestamp_column_name = null;   //string

    protected $_updated_timestamp_column_name = null;   //string
    
    //Arrays for modeling relationships
    //Four types of relationships supported
    // - One-To-One (e.g. One Post has exactly One Summary) a.k.a Has-One
    // - One-To-Many (e.g. One Post has Many Comments) a.k.a Has-Many
    // - Many-To-One (e.g. Many Posts belong to One Author) a.k.a Belongs-To
    // - Many-To-Many a.k.a Has-Many-Through 
    //   (e.g. eg: One Post has Many Tags through the posts_tags table)
    //
    // It is up to the individual(s) extending this class to implement
    // this relationship functionality based on definition structures
    // outlined below. Things like eager loading and saving related
    // records are some of the functionalities that can be implemented
    // using the relationship definitions
    
    /**
     *
     * 2-dimensional array defining Has-One relationships
     * 
            --------------------------
            |         posts          |
            --------------------------
            ||=======||--------|-----|
            ||post_id||........|title|
            ||=======||--------|-----|
            --------------------------
                ||
                ||
                ||
               VVVV
            ---------------------------------
            |           summaries           |
            ---------------------------------
            ||=========||--------|----------|
            ||s_post_id||........|view_count|
            ||=========||--------|----------|
            ---------------------------------

            NOTE: the post_id column in the posts table is an auto-incrementing 
            integer primary key.

            NOTE: the summaries table does not have a primary key. 
            There should be a unique index on its s_post_id column to 
            enforce the rule that a post can have only one summary
            and to also improve query performance.
     * 
     * Inside the model with \GDAO\Model->_table_name === 'posts'
     * 
     * Structure
     * [
     *    'summary' => //has_one relation name preferably in singular form
     *      [
     *          'my_models_table' => 'posts',
     *          'foreign_key_col_in_my_models_table' => 'post_id',
     *          
     *          'foreign_models_table' => 'summaries',
     *          'foreign_key_col_in_foreign_models_table' => 's_post_id',
     *      ],
     *      .......,
     *      .......
     * ]
     * 
     * @var array
     */
    protected static $_has_one_relationships = array();
    
    /**
     *
     * 2-dimensional array defining Has-Many relationships
     * 
            --------------------------
            |         posts          |
            --------------------------
            ||=======||--------|-----|
            ||post_id||........|title|
            ||=======||--------|-----|
            --------------------------
                ||
                ||
                ==============
                            ||
                           VVVV
            --------------------------------------------
            |                comments                  |
            --------------------------------------------
            |----------||=========||--------|----------|
            |comment_id||c_post_id||........|   body   |
            |----------||=========||--------|----------|
            --------------------------------------------

            NOTE: the post_id column in the posts table is an
            auto-incrementing integer primary key.

            NOTE: the comment_id column in the comments table is an
            auto-incrementing integer primary key.
     * 
     * Inside the model with \GDAO\Model->_table_name === 'posts'
     * 
     * Structure
     * [
     *    'comments' => //has_many relation name preferably in plural form
     *      [
     *          'my_models_table' => 'posts',
     *          'foreign_key_col_in_my_models_table' => 'post_id',
     *          
     *          'foreign_models_table' => 'comments',
     *          'foreign_key_col_in_foreign_models_table' => 'c_post_id',
     *      ],
     *      .......,
     *      .......
     * ]
     * 
     * @var array
     */
    protected static $_has_many_relationships = array();
    
    /**
     *
     * 2-dimensional array defining Belongs-To relationships
     * 
            ---------------------------
            |         authors         |
            ---------------------------
            ||=========||--------|----|
            ||author_id||........|name|
            ||=========||--------|----|
            ---------------------------
                ||
                ||
                =============
                           ||
                          VVVV
            --------------------------------------
            |                posts               |
            --------------------------------------
            |-------||===========||--------|-----|
            |post_id||p_author_id||........|title|
            |-------||===========||--------|-----|
            --------------------------------------

            NOTE: the author_id column in the authors table is an
            auto-incrementing integer primary key.

            NOTE: the post_id column in the posts table is an
            auto-incrementing integer primary key.
     * 
     * Inside the model with \GDAO\Model->_table_name === 'posts'
     * 
     * Structure
     * [
     *    'author' => //belongs_to relation name preferably in singular form
     *      [
     *          'my_models_table' => 'posts',
     *          'foreign_key_col_in_my_models_table' => 'p_author_id',
     *          
     *          'foreign_models_table' => 'authors',
     *          'foreign_key_col_in_foreign_models_table' => 'author_id',
     *      ],
     *      .......,
     *      .......
     * ]
     * 
     * @var array
     */
    protected static $_belongs_to_relationships = array();
    
    /**
     *
     * 2-dimensional array defining Has-Many-Through relationships
     * 
            --------------------------  ------------------------
            |         posts          |  |         tags         |
            --------------------------  ------------------------
            ||=======||--------|-----|  ||======||--------|----|
            ||post_id||........|title|  ||tag_id||........|name|
            ||=======||--------|-----|  ||======||--------|----|
            --------------------------  ------------------------
                 ||                          ||
                 ||                          ||
                 =================           ||
                                ||           ||
                               VVVV         VVVV
            -------------------------------------------
            |              posts_tags                 |
            -------------------------------------------
            |-------------||============||===========||
            |posts_tags_id||psts_post_id||psts_tag_id||
            |-------------||============||===========||
            -------------------------------------------

            NOTE: the post_id column in the posts table is an
            auto-incrementing integer primary key.

            NOTE: the tag_id column in the tags table is an
            auto-incrementing integer primary key.

            NOTE: the posts_tags_id column in the posts_tags 
            table is an auto-incrementing integer primary key. 
     * 
     * Inside the model with \GDAO\Model->_table_name === 'posts'
     * 
     * Structure
     * [
     *    'tags' => //has_many_through relation name preferably in plural form
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
     *      ],
     *      .......,
     *      .......
     * ]
     * 
     * @var array
     */
    protected static $_has_many_through_relationships = array();



    /**
     * 
     * @param type $dsn
     * @param type $username
     * @param type $passwd
     * @param type $pdo_driver_opts
     * @param type $extra_opts
     * 
     * @see PDO::__construct(...) for definition of first four parameters
     */

    public function __construct(
        $dsn = '',
        $username = '', 
        $passwd = '', 
        $pdo_driver_opts = array(),
        $extra_opts = array()
    ) {
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