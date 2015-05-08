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
class ComponentsModel extends \ParisGDAO\Model
{
    protected function _setup() {
        
        $this->_table_name = 'components';
        $this->_primary_col = 'component_id';
        $this->_collection_class_name = 'ComponentsCollection';
        $this->_record_class_name = 'ComponentRecord';
    }
    
}