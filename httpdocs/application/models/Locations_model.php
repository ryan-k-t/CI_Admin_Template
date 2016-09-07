<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Locations_model extends MY_Model {

    protected $_table_name = "locations";

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
                $this->db->like($key, $value, 'both');
            }
        }
        $this->db->order_by($this->_table_name.'.name ASC');
        $this->db->select($this->_table_name.'.name, facilities.select_name AS facility_name, '.$this->_table_name.'.active, '.$this->_table_name.'.address, '.$this->_table_name.'.id,'.$this->_table_name.'.room_number');
        $this->db->join('facilities', 'facilities.id = '.$this->_table_name.'.facility_id');
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

        return $records;
    }

    /**
     * get all the locations for a given facility
     * @param  int  $facility_id the ID of the facility
     * @param  boolean $active_only 
     * @return array               an array of CI dataset objects
     */
    public function with_facility($facility_id, $active_only = TRUE)
    {
        $this->db->where('facility_id', $facility_id);
        if($active_only)
        {
            $this->db->where('active', 'yes');
        }
        $this->db->order_by('name ASC');
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

        return $records;
    }

    /**
     * a function for inserting a record into the database via import. this prepopulates the fields
     * last_modified, last_modified_by and date_added
     *
     * if there is an _ancillary_data variable set in the data that contains a facility_id
     * that will be used to locate the cleanez code via that facility's currently active contract
     * --otherwise--
     * if there is a facility_id in the data that will be used to locate the facility's currently active contract
     * and use the cleanez code from that
     * --otherwise--
     * it pulls the most recent contract with that cleanez code and locates the facility
     * from that contract
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
        $ancillary_data = null;
        if(isset($data['_ancillary_data']))
        {
            $ancillary_data = $data['_ancillary_data'];
            unset($data['_ancillary_data']);
        }

        /**
         * make sure the cleanez code is in uppercase
         */
        $data['cleanez_code'] = strtoupper($data['cleanez_code']);

        $_CI =& get_instance();

        /**
         * if a facility was selected on the import we override
         * what was set on the individual row
         */
        if(isset($ancillary_data['facility_id']) && !empty($ancillary_data['facility_id']))
        {
            $data['facility_id'] = $ancillary_data['facility_id'];
        }
        if(!isset($data['facility_id']) || empty($data['facility_id']))
        {
            if(!isset($data['cleanez_code']))
            {
                $success['message'] = "No Facility ID or CleanEZ Code specified";
                return $response;
            }

            /**
             * find the most recent contract that has this cleanez_code and we'll use that to derive our facility
             */
            $_CI->load->model('contracts_model');
            $contracts = $_CI->contracts_model->get_all( 
                array(
                    'cleanez_code' => $data['cleanez_code']
                ),
                'start_date DESC'
            );
            if(count($contracts) == 0)
            {
                $success['message'] = "No contracts found with a CleanEZ Code of ".$data['cleanez_code'];
                return $response;
            }
            $data['facility_id'] = $contracts[0]->facility_id;
        }

        /**
         * let's make certain this facility exists
         */
        $_CI->load->model('facilities_model');
        $facility = $_CI->facilities_model->with_id($data['facility_id']);
        if(!$facility || !$facility->id)
        {
            $success['message'] = "No facility found with an ID of ".$data['facility_id'];
            return $response;
        }

        /**
         * set the cleanez code to whatever the facility has set
         * for it's active contract --- in essence this renders the cleanez_code column moot?!
         */
        $facility = new Facility($facility);
        $data['cleanez_code'] = $facility->get_cleanez_code();


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
}

class Location extends ER_Object {
    protected $id;
    protected $facility_id;
    protected $name;
    protected $address;
    protected $room_number;
    protected $active;
    protected $date_added;
    protected $last_modified;
    protected $last_modified_by;

    public function __construct($data = NULL)
    {
        parent::__construct($data);
    }

    public function get_facility()
    {
        if(!$this->facility_id)
        {
            return FALSE;
        }

        $this->_CI->load->model('facilities_model');
        return $this->_CI->facilities_model->with_id($this->facility_id);
    }

}
?>