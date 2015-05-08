<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ComponentDeliverableModel
 *
 * @author aadegbam
 */
class ProjectsModel extends \ParisGDAO\Model
{
    protected function _setup() {
        
        $this->_table_name = 'projects';
        $this->_primary_col = 'project_id';
        $this->_collection_class_name = 'ProjectsCollection';
        $this->_record_class_name = 'ProjectRecord';
    }
    
}