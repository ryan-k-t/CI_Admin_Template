<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class MY_Model extends CI_Model {

    protected $_table_name;

    public function __construct()
    {
		// Call the CI_Model constructor
		parent::__construct();
    }

    /* =================== BEGIN UTILITY FUNCTIONS =================== */
    public function get_field_default_value($field_name)
    {
        $q = $this->db->query( "SHOW COLUMNS FROM {$this->_table_name} WHERE Field = '{$field_name}'" );
        if (!$q) {
            $error = $this->db->error();
            log_message('error',__FUNCTION__.' experienced an SQL error. The query was: '.$error['code'].' and the message was: '.$error['message']);
            return FALSE;
        }

        $row = $q->row();
        $q->free_result();
        return $row->Default;
    }

    public function get_field_enum_values($field_name)
    {
        $q = $this->db->query( "SHOW COLUMNS FROM {$this->_table_name} WHERE Field = '{$field_name}'" );
        if (!$q) {
            $error = $this->db->error();
            log_message('error',__FUNCTION__.' experienced an SQL error. The query was: '.$error['code'].' and the message was: '.$error['message']);
            return FALSE;
        }
        $row = $q->row();
        $q->free_result();

        $return = array();
        preg_match("/^enum\(\'(.*)\'\)$/", $row->Type, $matches);
        if($matches)
        {
            $values = explode("','", $matches[1]);
            foreach($values as $value)
            {
                $return[$value] = $value;
            }
        }

        return $return;
    }

    public function has_field($field_name)
    {
        $q = $this->db->query( "SHOW COLUMNS FROM {$this->_table_name} WHERE Field = '{$field_name}'" );
        if (!$q) {
            $error = $this->db->error();
            log_message('error',__FUNCTION__.' experienced an SQL error. The query was: '.$error['code'].' and the message was: '.$error['message']);
            return FALSE;
        }
        $result = $q->num_rows() > 0;
        $q->free_result();
        return $result;
    }

    public function get_fields()
    {
        return $this->db->list_fields($this->_table_name);
    }
    /* =================== END UTILITY FUNCTIONS =================== */




    /**
     * determine if the record exists based off the criteria passed
     * @param  array $where an associative array of DB field names as the indexes and their corresponding values
     * @return bool        
     */
    public function record_exists($where)
    {
        if (!$this->_table_name) {
            log_message('error',get_class($this)."->".__FUNCTION__." has no table name defined!");
            return FALSE;
        }

        $query = $this->db->get_where($this->_table_name, $where);
        if (!$query) {
            $error = $this->db->error();
            log_message('error',__FUNCTION__.' experienced an SQL error. The query was: '.$error['code'].' and the message was: '.$error['message']);
            return FALSE;
        }

        $return = $query->num_rows() > 0;
        $query->free_result();
        return $return;
    }

    /**
     * a basic record acquisition call using the primary key (id);
     * 
     * @param  int $id 
     * @return stdClass object on success or FALSE
     */
    public function with_id($id)
    {
    	if (!$this->_table_name) {
    		log_message('error',get_class($this)."->".__FUNCTION__." has no table name defined!");
    		return FALSE;
    	}

    	if (!ctype_digit($id) && !is_int($id)) {
    		log_message('error',get_class($this)."->".__FUNCTION__." expects the id parameter to be an integer. Value passed was ".$id);
    		return FALSE;
    	}

    	$query = $this->db->get_where($this->_table_name, array('id' => $id));
    	if (!$query) {
    		$error = $this->db->error();
    		log_message('error',__FUNCTION__.' experienced an SQL error. The query was: '.$error['code'].' and the message was: '.$error['message']);
    		return FALSE;
    	}

    	$record = $query->num_rows() > 0 ? $query->row() : FALSE;
    	$query->free_result();
    	return $record;
    }

    /**
     * a basic record deletion call using the primary keyt (id)
     * @param  int $id 
     * @return bool     
     */
    public function delete($id)
    {
    	if (!$this->_table_name) {
    		log_message('error',get_class($this)."->".__FUNCTION__." has no table name defined!");
    		return FALSE;
    	}

    	if (!ctype_digit($id) && !is_int($id)) {
    		log_message('error',get_class($this)."->".__FUNCTION__." expects the id parameter to be an integer. Value passed was ".$id);
    		return FALSE;
    	}

    	$this->db->where('id', $id);
    	if (!$this->db->delete($this->_table_name)) {
    		$error = $this->db->error();
    		log_message('error',__FUNCTION__.' experienced an SQL error. The query was: '.$error['code'].' and the message was: '.$error['message']);
    		return FALSE;
    	}

    	/* we'll ignore a false "negative" if no rows were deleted -- don't know how else to segment the results from a failure due to SQL code vs. no record to delete */
    	return TRUE;
    }

    /**
     * a generic function for updating a database record
     * @param  int    $id   
     * @param  array  $data an associative array of key=>value pairs 
     * @return bool
     */
    public function update($id, array $data)
    {
    	if (!$this->_table_name) {
    		log_message('error',get_class($this)."->".__FUNCTION__." has no table name defined!");
    		return FALSE;
    	}

    	if (!ctype_digit($id) && !is_int($id)) {
    		log_message('error',get_class($this)."->".__FUNCTION__." expects the id parameter to be an integer. Value passed was ".$id);
    		return FALSE;
    	}

    	$this->db->where('id', $id);
    	if (!$this->db->update($this->_table_name, $data)) {
    		$error = $this->db->error();
    		log_message('error',__FUNCTION__.' experienced an SQL error. The query was: '.$error['code'].' and the message was: '.$error['message']);
    		return FALSE;
    	}

    	/* we'll ignore a false "negative" if no rows were updated */
    	return TRUE;
    }

    /**
     * a generic function for inserting a database record
     * @param  array  $data an associative array of key=>value pairs 
     * @return FALSE on error and the id on success
     */
    public function insert(array $data)
    {
    	if (!$this->_table_name) {
    		log_message('error',get_class($this)."->".__FUNCTION__." has no table name defined!");
    		return FALSE;
    	}

    	if (!$this->db->insert($this->_table_name, $data)) {
    		$error = $this->db->error();
    		log_message('error',__FUNCTION__.' experienced an SQL error. The query was: '.$error['code'].' and the message was: '.$error['message']);
    		return FALSE;
    	}

    	return $this->db->insert_id();
    }

    /**
     * a generic function for inserting a record into the database. this prepopulates the fields
     * last_modified, last_modified_by and date_added
     * 
     * @param  array  $data            an associative array of key=>value pairs
     * @param  int    $current_user_id  
     * @return an associative array    fields are success, message and id
     */
    public function import_insert(array $data, $current_user_id)
    {
        $response = array(
            'success' => FALSE,
            'message' => '',
            'id'      => 0
        );

        if (!$this->_table_name) {
            log_message('error',get_class($this)."->".__FUNCTION__." has no table name defined!");
            $success['message'] = "Object configuration conflict. See log for details.";
            return $response;
        }

        /**
         * if we were passed ancillary data we need to remove it from 
         * the data array otherwise it will bork the insert
         */
        if(isset($data['_ancillary_data']))
        {
            $ancillary_data = $data['_ancillary_data'];
            unset($data['_ancillary_data']);
        }

        $this->set_last_modified($data, $current_user_id);
        $this->set_date_added($data);

        if (!$this->db->insert($this->_table_name, $data)) {
            $error = $this->db->error();
            log_message('error',__FUNCTION__.' experienced an SQL error. The query was: '.$error['code'].' and the message was: '.$error['message']);
            $success['message'] = "Unable to insert new row. See log for details.";
            return $response;
        }

        $response['id'] = $this->db->insert_id();
        $response['success'] = TRUE;

        return $response;
    }

    /**
     * simple function to update the date_added value in the data array
     * 
     * @param array  $data           an associative array passed by reference so we don't have to send it back
     */
    public function set_date_added(array &$data)
    {
        if($this->has_field("date_added"))
        {
            $data['date_added'] = date("Y-m-d H:i:s");
        }

        $data;
    }

    /**
     * simple function to update the last_modified_by and last_modified values in the data array
     * 
     * @param array  &$data           an associative array passed by reference so we don't have to send it back
     * @param int $current_user_id    the user id of the person who updated the record
     */
    public function set_last_modified(array &$data, $current_user_id)
    {
        if($this->has_field("last_modified_by"))
        {
            $data['last_modified_by'] = $current_user_id;
        }
        if($this->has_field("last_modified"))
        {
            $data['last_modified'] = date("Y-m-d H:i:s");
        }
        return $data;
    }

    /**
     * a basic function to return a simple option list / associative array
     * @param  string $value_field the db field to use for the value
     * @param  string $label_field the db field to use for the label
     * @param  array  $where       an optional where clause array to use to limit the records returned
     * @param  string $order_by_field       the db field to sort by -- will be ascending
     * @return array              an associative array
     */
    public function get_option_list(
        $value_field, $label_field, $where = array(), $order_by_field = "")
    {
    	if (!$this->_table_name) {
    		log_message('error',get_class($this)."->".__FUNCTION__." has no table name defined!");
    		return FALSE;
    	}

    	$this->db->select($value_field.",".$label_field);

        /**
         * Only want active listings except on buisness segments.
         * On that one we want all inactive but not archived.
         */
        if ($this->has_field('archived')) {
            $this->db->where('archived','no');
        } 
        if (!$this->has_field('archived') &&
             $this->has_field('active')
        ) {
            $this->db->where('active','yes');            
        }

        /**
         * where and ordering
         */
    	if ($where) {
    		$this->db->where($where);
    	}

    	if ($order_by_field) {
    		$this->db->order_by($order_by_field, "ASC");
    	}

    	$query = $this->db->get($this->_table_name);
    	if (!$query) {
    		$error = $this->db->error();
    		log_message('error',get_class($this)."->".__FUNCTION__.' experienced an SQL error. The query was: '.$error['code'].' and the message was: '.$error['message']);
    		return FALSE;
    	}

    	$result = $query->result_array();
    	$query->free_result();

    	/* now build our return array */
    	$return = array();
    	foreach ($result as $row) {
    		$return[ $row[$value_field] ] = $row[$label_field];
    	}
    	return $return;
    }

    /**
     * generic function to return all records for the given table
     * 
     * @param  array  $where    an optional where clause array to use to limit the records returned
     * @param  string $order_by a string representation of an SQL order by clause
     * @return array           an array of dataset results (or FALSE on error)
     */
    public function get_all ($where = array(), $order_by = "")
    {
    	if(!$this->_table_name) {
    		log_message('error',get_class($this)."->".__FUNCTION__." has no table name defined!");
    		return FALSE;
    	}

    	if ($where) {
    		$this->db->where($where);
    	}
    	if ($order_by) {
    		$this->db->order_by($order_by);
    	}

        $this->db->select($this->_table_name.".*");
    	$query = $this->db->get($this->_table_name);
    	if (!$query) {
    		$error = $this->db->error();
    		log_message('error',get_class($this)."->".__FUNCTION__.' experienced an SQL error. The query was: '.$error['code'].' and the message was: '.$error['message']);
    		return FALSE;
    	}

        $result = $query->num_rows() > 0 ? $query->result() : array();
    	$query->free_result();
    	return $result;
    }

    /**
     * a generic function to return a filtered list of records
     * @param  array  $where an associative array of field data comparisions
     * @param  array  $likes an associative array of field wildcard comparisons
     * @return array           an array of dataset results (or FALSE on error)
     */
    public function get_listing(array $where = array(), array $likes = array())
    {
        foreach($likes as $key=>$value)
        {
            $this->db->like($key, $value, 'both');
        }
        return $this->get_all($where);
    }


    public function inactivate($id)
    {
        if(!$this->has_field('active'))
        {
            return FALSE;
        }
        
        return $this->update($id, array('active' => 'no'));
    }

    public function activate($id)
    {
        if(!$this->has_field('active'))
        {
            return FALSE;
        }
        
        return $this->update($id, array('active' => 'yes'));
    }
}

class ER_Object {
    protected $_CI;

    public function __construct($data = NULL)
    {
        $this->_CI =& get_instance();
        
        if(is_array($data) || is_object($data))
        {
            $this->populate($data);
        }
    }
    /**
     * populate the properties of the object
     * 
     * @param  object/associative array $data 
     * @return n/a       
     */
    public function populate($data)
    {
        foreach($data as $key=>$value)
        {
            $this->__set($key, $value);
        }
    }

    public function __set($name, $value)
    {
        $function = "set_".$name;
        if(method_exists($this, $function))
        {
            return $this->$function($value);
        }

        if(property_exists($this, $name))
        {
            return $this->$name = $value;
        }

        throw new Exception("Inexistent property: ".$name);
    }

    public function __get($name)
    {
        $function = "get_".$name;
        if(method_exists($this, $function))
        {
            return $this->$function();
        }

        if(property_exists($this, $name))
        {
            return $this->$name;
        }

        throw new Exception("Inexistent property: ".$name);
    }
}
?>