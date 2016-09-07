<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * the authentication suite should extend MY_Controller
 */
class Locations extends MY_Controller implements Module_Interface {

	public function __construct()
    {
        parent::__construct();

        $this->load->model('locations_model');
        $this->load->model('facilities_model');
        $this->display_name = ucwords($this->module_name);
    }



    /****** BEGIN INTERFACE FUNCTIONS ******/

    public function create()
    {
        $record = new Location();
        $get_facility_id = $this->input->get('facility_id');
        if($get_facility_id)
        {
            $facility = $this->facilities_model->with_id($get_facility_id);
            if($facility && $facility->id)
            {
                $record->facility_id = $facility->id;
                $record->cleanez_code = $facility->cleanez_code;
            }
        }

        $data = array(
            'title'      => "Create New | ".__CLASS__,
            'record'     => $record,
            'facilities' => $this->facilities_model->get_option_list('id', 'select_name', array(), 'select_name')
        );
            
        if (!check_access_area(LOCATION_MANAGER)){
            $this->access_error('You cannot create '.$this->module_name.'.');
        }

        $this->load->view($this->module_name.'/editor', $data);
    }

    public function delete()
    {
        //there is no delete function ... !!!!
        show_404();
    }

    public function edit($id = NULL)
    {
        if(is_null($id)) {
            show_404();
            return;
        }
        
        if (!check_access_area(LOCATION_MANAGER)) {
            $this->access_error('You do not have permission to edit '.$this->module_name.'.');
        }

        $record = $this->locations_model->with_id($id);
        if(!$record) {
            show_404();
            return;
        }

        $record = new Location($record);

        $data = array(
            'title'      => $record->name." | ".__CLASS__,
            'record'     => $record,
            'facilities' => $this->facilities_model->get_option_list('id', 'select_name', array(), 'select_name')
        );

        $this->load->view($this->module_name.'/editor', $data);
    }

    public function export()
    {
        show_404();
    }

    public function index()
    {
        if (!check_access_area(LOCATION_MANAGER)) {
            $this->access_error();
        }

        $records = $this->locations_model->get_listing();

        $this->load->helper('text');
        foreach ($records as $record) {
            $record->address = character_limiter($record->address, 100);
        }

		$data = array(
            'title'          => __CLASS__,
            'module_name'    => $this->module_name,
            'table'          => $this->_listing( $records ),
            'quick_link'     => $this->load->view('_blocks/quick_links/'.$this->module_name, array('facilities' => $this->facilities_model->get_option_list('id', 'select_name', array(), 'select_name')), TRUE)
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

        if (!check_access_area(LOCATION_MANAGER)) {
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

        $data['last_modified_by'] = $current_user_id;
        $data['last_modified'] = $insert_time = date("Y-m-d H:i:s");
        $data['date_added'] = $insert_time;


        if(!$new_id = $this->locations_model->insert($data)) {
            $response['message'] = "Unable to insert record at this time";
            $this->_output_json($response);
            return;
        }

        $response['success'] = TRUE;
        $response['id'] = $new_id;
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


        if (!check_access_area(LOCATION_MANAGER)) {
            $response['message'] = "Access Denied";
            $this->_output_json($response);
            return;
        }


        /**
         * check to make sure the data passed in has an ID 
         * and the ID refers to an existing record
         */
        if (!$data = $this->check_valid_record($this->module_name.'_model')) {
            return;
        }


        $id = $data['id'];
        /* we don't want this mucking up our SQL */
        unset($data['id']);


        /* manually set the last_modified fields */
        $session_data = $this->session->userdata('user');
        $current_user_id = $session_data->id;

        $data['last_modified_by'] = $current_user_id;
        $data['last_modified'] = $update_time = date("Y-m-d H:i:s");


        /* add the id parameter to the response 
         * -- used for redirection if needed 
         * -- inherited/borrowed from insert */
        $response['id'] = $id;

        if(!$this->locations_model->update($id, $data))
        {
            $response['message'] = "Unable to update record at this time";
            $this->_output_json($response);
            return;
        }

        $response['success'] = TRUE;
        $this->_output_json($response);
    }

    /****** END INTERFACE FUNCTIONS ******/

    /**
     * build a DOM listing of the records passed in 
     *
     * this overrides the _listing function of Base_Controller
     * 
     * @param  array $records 
     * @return DOM/string          
     */
    protected function _listing(array $records = array())
    {
        /**
         * do you want to build a table?!?
         */
        $table_settings = array(
            'headline' => ucwords($this->module_name),
            'table_header' => array(
                array('label' => 'Name'),
                array('label' => 'Facility'),
                array('label' => 'Address'),
                array('label' => 'Room #'),
                array('label' => ''), // activate/inactivate
                array('label' => '') // edit
            ),
            'table_actions' => array(
                array('import' => '/'.$this->module_name.'/import'),
                array('add' => '/'.$this->module_name.'/create')
            ),
            'table_rows' => array(),
            'no_rows_content' => "There currently are no records in the system based off your criteria. To add one, go <a href=\"/".$this->module_name."/create\">here</a>."
        );

        foreach($records as $record)
        {
            if($record->active == "yes")
            {
                $status_column = $this->table_builder->inactivate_column(array(
                    'id'   => $record->id,
                    'url'  => "/".$this->module_name."/inactivate",
                    'name' => $record->name
                ));
            }
            else
            {
                $status_column = $this->table_builder->activate_column(array(
                    'id'   => $record->id,
                    'url'  => "/".$this->module_name."/activate",
                    'name' => $record->name
                ));
            }
            $table_settings['table_rows'][] = array(
                array('label' => $record->name),
                array('label' => $record->facility_name),
                array('label' => $record->address),
                array('label' => $record->room_number),
                array('label' => $status_column),
                array('label' => $this->table_builder->edit_column(array(
                    'id'   => $record->id,
                    'name' => $record->name,
                    'url'  => "/".$this->module_name."/edit"
                    ))
                )
            );
        }

        return $this->table_builder->sortable_table($table_settings);
    }

    /**
     * show the import screen
     * @return 
     */
    public function import($facility_id = '')
    {
        if (!check_access_area(LOCATION_MANAGER)) {
            $this->access_error();
            return;
        }

        $facilities_options = $this->facilities_model->get_option_list('id', 'select_name', array(), 'select_name');
        $data = array(
            'title'        => $this->display_name.' | Import',
            'display_name' => $this->display_name,
            'module'       => $this->module_name,
            'sample_doc'   => 'Sample-Location-Import.csv',
            's_facility_id' => $facility_id,
            'facilities'    => $facilities_options,
        );
            
        $this->load->view('locations/import', $data);
    }
    /**
     * an AJAX function to handle the uploading of data from the import screen
     */
    public function upload()
    {
        if(!$this->input->is_ajax_request())
        {
            show_404();
            return;
        }
        
        // check if they are not super admin
        if (!check_access_area(LOCATION_MANAGER)) {
            $response = $this->get_initialized_json_response();
            $response['message'] = "Access Denied";
            $this->_output_json($response);
            return;
        }

        $expected_fields = array(
            "facility_id",
            "cleanez_code",
            "name",
            "address",
            "room_number",
        );
        $response = $this->process_import($expected_fields);

        $this->_output_json($response);
    }


    private function _update_active_state($active = TRUE)
    {
        if(!is_bool($active)) return FALSE;
        if(!$this->input->is_ajax_request())
        {
            show_404();
            return;
        }

        $response = $this->get_initialized_json_response();

        if (!check_access_area(LOCATION_MANAGER)) {
            $response['message'] = "Access Denied";
            $this->_output_json($response);
            return;
        }

        /**
         * check to make sure the data passed in has an ID and the ID refers to an existing record
         */
        if(!$data = $this->check_valid_record($this->module_name.'_model'))
        {
            return;
        }

        $response['success'] = $active ? $this->locations_model->activate($data['id']) : $this->locations_model->inactivate($data['id']);
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
}
?>