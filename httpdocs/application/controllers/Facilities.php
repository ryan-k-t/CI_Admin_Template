<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * the authentication suite should extend MY_Controller
 */
class Facilities extends MY_Controller implements Module_Interface {

    public $listing_like_fields = array('name');

	public function __construct()
    {
        parent::__construct();

        $this->load->model('facility_types_model');
        $this->load->model('facilities_model');
    }



    /****** BEGIN INTERFACE FUNCTIONS ******/

    public function create()
    {
        $data = array(
            'title'           => "Create New | ".__CLASS__,
            'record'          => new Facility(),
            'locations'       => array(),
            'facility_types'  => $this->facility_types_model->get_option_list('id', 'name')
        );
            
        if (!check_access_area(FACILITY_MANAGER)) {
            $this->access_error('You cannot create facilities.');
        }

        $this->load->view('facilities/editor', $data);
    }

    public function delete()
    {
        //there is no delete function ... !!!!
        show_404();
    }

    public function edit($id = NULL)
    {
        if (is_null($id)) {
            show_404();
            return;
        }
        
        if (!check_access_area(FACILITY_MANAGER)) {
            $this->access_error('You do not have permission to edit facilities.');
        }

        $facility = $this->facilities_model->with_id($id);
        if (!$facility) {
            show_404();
            return;
        }

        $facility = new Facility($facility);

        $this->load->model('locations_model');
        $locations = $this->locations_model->with_facility($id);
        $types = $this->facility_types_model->get_option_list('id', 'name');

        $data = array(
            'title'             => $facility->display_name." | ".__CLASS__,
            'record'            => $facility,
            'facility_types'    => $types,
            'locations'         => $locations
        );
            

        $this->load->view('facilities/editor', $data);
    }

    public function export()
    {
        show_404();
    }

    public function index()
    {
        if (!check_access_area(FACILITY_MANAGER)){
            $this->access_error();
        }

        $facilities = $this->facilities_model->get_listing();

        $facility_types = $this->facility_types_model->get_option_list('id', 'name');
        $quick_link = $this->load->view('_blocks/quick_links/facilities', 
            array('facility_types' => $facility_types ),TRUE);
		$data = array(
            'title'          => __CLASS__,
            'module_name'    => $this->module_name,
            'table'          => $this->_listing($facilities),
            'quick_link'     => $quick_link
		);
			
		$this->load->view('listing_page', $data);
    }

    /**
     * An AJAX request to insert a record
     * @return JSON
     */
    public function insert()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        /* prep our response */
        $response = $this->get_initialized_json_response();

        if (!check_access_area(FACILITY_MANAGER)) {
            $response['message'] = "Access Denied";
            $this->_output_json($response);
            return;
        }

        /* make sure our data is clean */
        $data = $this->input->post(NULL, TRUE);

        /* we won't need id even if it is set */
        unset($data['id']);


        /* manually set the last_modified fields */
        $session_data = $this->session->userdata('user');
        $current_user_id = $session_data->id;
        $insert_time = date("Y-m-d H:i:s");

        $this->facilities_model->set_date_added($data);
        $this->facilities_model->set_last_modified($data, $current_user_id);


        /* put the relational fields aside from the main data */
        $relational_fields = array();
        foreach ($data as $key=>$value) {
            if (stripos($key, "__") === 0) {
                $segments = explode("__", $key);
                if (empty($segments[0])) {
                    array_shift($segments);
                }

                $relational_fields[$segments[0]][] = $value;
                unset($data[$key]);
            }
        }

        /* start a DB transaction so it either all works or none */
        $this->db->trans_begin();

        if (!$new_id = $this->facilities_model->insert($data)) {
            $this->db->trans_rollback();

            $response['message'] = "Unable to save updated record at this time";
            $this->_output_json($response);
            return;
        }


        /* update relational data */
        $relational_success = TRUE;
        foreach ($relational_fields as $table=>$values) {
            switch ($table) {
                
                default:
                    break;
            }

            if (is_array($values)) {
                foreach ($values as $value) {
                    /* no need for empties */
                    $value = trim($value);
                    if (strlen($value) == 0) {
                        continue;
                    }


                    /* stop processing foreach(values) if we encounter an issue */
                    if (!$result) {
                        $relational_success = FALSE;
                        break;
                    }
                }
            }

            /* stop processing foreach(relational_fields) if we encounter an issue */
            if (!$relational_success) {
                break;
            }
        }

        if (!$relational_success) {
            $this->db->trans_rollback();

            $response['message'] = "Unable to save updated record at this time";

        } else {
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                $response['message'] = "Unable to save updated record at this time";

            } else {
                $this->db->trans_commit();
                $response['success'] = TRUE;
                $response['id'] = $new_id;
            }
        }
        $this->_output_json($response);
    }

    /**
     * an AJAX request to update an existing record
     * @return JSON
     */
    public function update()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        /* prep our response */
        $response = $this->get_initialized_json_response();


        if (!check_access_area(FACILITY_MANAGER)) {
            $response['message'] = "Access Denied";
            $this->_output_json($response);
            return;
        }


        /**
         * check to make sure the data passed in has an ID and the ID 
         * refers to an existing record
         */
        if (!$data = $this->check_valid_record('facilities_model')) {
            return;
        }


        $id = $data['id'];
        /* we don't want this mucking up our SQL */
        unset($data['id']);


        /* manually set the last_modified fields */
        $session_data = $this->session->userdata('user');
        $current_user_id = $session_data->id;
        $this->facilities_model->set_last_modified($data, $current_user_id);
        $update_time = date("Y-m-d H:i:s");

        /* start a DB transaction so it either all works or none */
        $this->db->trans_begin();

        /* set aside our relational fields */
        $relational_fields = array();
        foreach ($data as $key=>$value) {
            if (stripos($key, "__") === 0) {
                $segments = explode("__", $key);
                if (empty($segments[0])) {
                    array_shift($segments);
                }
                $relational_fields[$segments[0]][] = $value;
                unset($data[$key]);
            }
        }

        if (!$this->facilities_model->update($id, $data)) {
            $this->db->trans_rollback();

            $response['message'] = "Unable to save updated record at this time";
            $this->_output_json($response);
            return;
        }


        /* update relational data */
        $relational_success = TRUE;
        foreach ($relational_fields as $table=>$values) {
            switch ($table) {
                default:
                    break;
            }

            if (is_array($values)) {
                foreach ($values as $value) {

                    $value = trim($value);
                    if (strlen($value) == 0) {
                        continue;
                    }

                    switch ($table) {
                        default:
                            # code...
                            break;
                    }

                    /* stop processing foreach(values) if we encounter an issue */
                    if (!$result) {
                        $relational_success = FALSE;
                        break;
                    }
                }
            }

            /* stop processing foreach(relational_fields) if we encounter an issue */
            if (!$relational_success) {
                break;
            }
        }

        if (!$relational_success) {
            $this->db->trans_rollback();
            $response['message'] = "Unable to save updated record at this time";

        } else {
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                $response['message'] = "Unable to save updated record at this time";

            } else {
                $this->db->trans_commit();
                $response['success'] = TRUE;
            }
        }
        $this->_output_json($response);
    }

    /****** END INTERFACE FUNCTIONS ******/





    private function _update_active_state($active = TRUE)
    {
        if (!is_bool($active)) {
            return FALSE;
        }

        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        $response = $this->get_initialized_json_response();

        if (!check_access_area(FACILITY_MANAGER)) {
            $response['message'] = "Access Denied";
            $this->_output_json($response);
            return;
        }

        /**
         * check to make sure the data passed in has an ID and the ID refers to an existing record
         */
        if (!$data = $this->check_valid_record('facilities_model')) {
            return;
        }

        $response['success'] = $active ? $this->facilities_model->activate($data['id']) : $this->facilities_model->inactivate($data['id']);
        $response['message'] = $response['success'] ? "" : "Unable to update data at this time.";
        $this->_output_json($response);
    }
    public function activate()
    {
        return $this->_update_active_state(TRUE);
    }
    public function inactivate()
    {
        return $this->_update_active_state(FALSE);
    }



    protected function _listing(array $records = array())
    {
        /**
         * do you want to build a table?!?
         */
        $table_settings = array(
            'headline' => ucwords($this->module_name),
            'table_header' => array(
                array('label' => 'Display Name'),
                array('label' => 'Select Name'),
                array('label' => 'Facility Type'),
                array('label' => ''), // activate/inactivate
                array('label' => '') // edit
            ),
            'table_actions' => array(
                array('add' => '/'.$this->module_name.'/create')
            ),
            'table_rows' => array(),
            'no_rows_content' => "There currently are no records in the system. 
            To add one, go <a href=\"/".$this->module_name."/create\">here</a>."
        );
        foreach ($records as $facility) {
            if ($facility->active == "yes") {
                $status_column = $this->table_builder->inactivate_column(array(
                    'id'   => $facility->id,
                    'url'  => "/".$this->module_name."/inactivate",
                    'name' => $facility->display_name
                ));

            } else {
                $status_column = $this->table_builder->activate_column(array(
                    'id'   => $facility->id,
                    'url'  => "/".$this->module_name."/activate",
                    'name' => $facility->display_name
                ));
            }
            $table_settings['table_rows'][] = array(
                array('label' => $facility->display_name),
                array('label' => $facility->select_name),
                array('label' => $facility->facility_type),
                array('label' => $status_column),
                array('label' => $this->table_builder->edit_column(array(
                    'id'   => $facility->id,
                    'name' => $facility->display_name,
                    'url'  => "/".$this->module_name."/edit"
                    ))
                )
            );
        }
        return $this->table_builder->sortable_table($table_settings);
    }



    /**
     * an AJAX request to get the options for locations of a given facility
     * 
     * @param  int  $facility_id 
     * @return string             HTML representation of an option list <option>
     */
    function get_locations($facility_id, $value_field = "id")
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        if (!is_numeric($facility_id)) {
            log_message('error',__CLASS__.'->'.__FUNCTION__.' expects the 
                parameter to be numeric. The parameter was '.$facility_id);
            echo "";
            return;
        }

        $facility_id = (int)$facility_id;
        
        $this->load->model('locations_model');
        $option_list = $this->locations_model->get_option_list($value_field, 'name', array('facility_id' => $facility_id), 'name');

        echo build_options($option_list);
    }


}
?>