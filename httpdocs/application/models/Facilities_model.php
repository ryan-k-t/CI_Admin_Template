<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Facilities_model extends MY_Model {

    protected $_table_name = "facilities";

    public function __construct()
    {
		// Call the CI_Model constructor
		parent::__construct();
    }

    public function get_listing(array $where = array(), array $like = array())
    {
        if($where)
        {
            $this->db->where($where);
        }
        if($like)
        {
            foreach($like as $key=>$value)
            {
                if($key != "facilities.name")
                {
                    $this->db->like($key, $value, 'both');
                }
            }
        }
        if(isset($like["facilities.name"]))
        {
            $this->db->group_start()
                     ->like($this->_table_name.'.display_name', $like['facilities.name'], 'both')
                     ->or_like($this->_table_name.'.select_name', $like['facilities.name'], 'both')
                     ->group_end();
        }
        $this->db->order_by($this->_table_name.'.select_name ASC');
        $this->db->select($this->_table_name.'.display_name, '.$this->_table_name.'.select_name, '.$this->_table_name.'.active, '.$this->_table_name.'.id, facility_types.name AS facility_type');
        $this->db->join('facility_types', 'facility_types.id = '.$this->_table_name.'.facility_type_id');
        $query = $this->db->get($this->_table_name);
        if(!$query)
        {
            $error = $this->db->error();
            log_message('error',__CLASS__.'->'.__FUNCTION__.' experienced an SQL error. The query was: '.$error['code'].' and the message was: '.$error['message']);
            $records = array();
        }
        else
        {
            $records = $query->result();
            $query->free_result();
        }
        log_message('error','DB call: '.$this->db->last_query());

        return $records;
    }

}

class Facility extends ER_Object {
    protected $id;
    protected $facility_type_id;
    protected $display_name;
    protected $select_name;
    protected $active;
    protected $date_added;
    protected $last_modified;
    protected $last_modified_by;

    public function __construct($data = NULL)
    {
        parent::__construct($data);

        $enum_fields = array(
            'active',
        );
        foreach($enum_fields as $enum_field)
        {
            if($this->$enum_field == "")
            {
                $this->$enum_field = $this->_CI->facilities_model->get_field_default_value($enum_field);
            }
        }
    }
}
?>