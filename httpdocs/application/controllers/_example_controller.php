<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * the authentication suite should extend MY_Controller
 */
class _example_controller extends MY_Controller implements Module_Interface 
{

	public function __construct()
    {
        parent::__construct();
        $this->load->model('_example_model');
    }



    /****** BEGIN INTERFACE FUNCTIONS ******/

    public function create () 
    {
        
        if (!check_access_area(ACCESS_LEVEL)) {
            $this->access_error('You cannot create examples.');
        }

        $data = array(
            'title'     => 'example: Create'
        );

        $data = $this->set_standard_data($data);

        $this->load->view('examples/editor', $data);
        return;

    }


    public function edit ($id = NULL) 
    {
        if ($id == null) {
            $this->access_error('Must have a example to edit');
        }

        if (!check_access_area(ACCESS_LEVEL)) {
            $this->access_error('You cannot edit examples.');
        }
        
        $data = $this->_example_model->with_id($id);

        if (!$data) {  
            $this->access_error('example does not exist.');   
        }

        $data = array(
            'title'        => 'Edit: '.$data->name,
            'data'         => $data,
        );
        $data = $this->set_standard_data($data);

        // view edit page
        $this->load->view('examples/editor', $data);
        return;
    }

    public function export()
    {
        show_404();
    }

    public function delete()
    {
        show_404();
    }

    public function index()
    {
        if (!check_access_area(ACCESS_LEVEL)) {
            $this->access_error('You cannot view this section.');
        }
        
        $qData['select'] = array(
            'id',
            'name',
        );

        $data = $this->_example_model->get_examples($qData);

        /**
         * Start of table data
         */
        $table['headline'] = "_example_title Manager";

        

        $table['table_header'] = array(
            array('label' => 'Name'),
            array('label' => '') // edit
            );

        $table['table_actions'] = array(
            array('add' => '/_example/create'),
            );

        /**
         * Make rows
         *
         * Converts object to array.
         * Saves the ID from object_array
         * Removes ID from object_array
         * Loops through object array and creates columns
         * Adds an edit which uses User_id to the end
         */
        
        $table['table_rows'] = array();
        foreach ($data as $example) {
            $data_array = (array) $example;

            unset($data_array['id']);
            $tds = array();
            foreach ($data_array as $label) {

                $single_td = array('label' => $label);
                array_push($tds, $single_td);
            }

            // add edit
            $edit = array(
                'id'        => $example->id,
                'name'      => $example->name,
                'url'       => '/_example/edit/',
                );

            $single_td['label'] = $this->table_builder->edit_column($edit);
            
            array_push($tds, $single_td);

            array_push($table['table_rows'], $tds);
        }
   
        $html_table = $this->table_builder->sortable_table($table);

        /**
         * Data sent to view
         */
        $data = array(
            'title'     => '_example_title',
            'table'     => $html_table,
            );

        $data = $this->set_standard_data($data);
        $this->load->view('listing_page', $data);
    }

    public function insert()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        if (!check_access_area(ACCESS_LEVEL)) {
            $response['success'] = false;
            $response['message'] = "Access Denied";
            $this->_output_json($response);
        }
        
        $data = $this->input->post(null, true);


         /* manually set the last_modified fields */
        $session_data = $this->session->userdata('user');
        $current_user_id = $session_data->id;

        $data['last_modified_by'] = $current_user_id;
        $data['last_modified'] = $update_time = date("Y-m-d H:i:s");

        if (!$new_id = $this->_example_model->insert($data)) {

            $response['message'] = "Unable to save updated record at this time";
            $this->_output_json($response);
            return;
        }

        $response['id'] = $new_id;
        $response['success'] = true;
        $response['message'] = 'saved';
        $this->_output_json($response);
    }

    public function update()
    {

        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        $response = $this->get_initialized_json_response();

        $session_data = $this->session->userdata('user');

        $current_user_id = $session_data->id;

        $data = $this->input->post(null, true);


        /**
         * check to make sure the data passed in has an ID and 
         * the ID refers to an existing record
         */
        if (!$data = $this->check_valid_record('_example_model')) {
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


        if (!$this->_example_model->update($id, $data)) {

            $response['message'] = "Unable to save updated record at this time";
            $this->_output_json($response);
            return;
        }

       

        $response['success'] = true;
        $response['message'] = 'saved';
        $this->_output_json($response);
    }

    /****** END INTERFACE FUNCTIONS ******/


    private function set_standard_data ($values) 
    {
        $session_data = $this->session->userdata('user');

        $current_user_id = $session_data->id;
        $data = array(
            'current_user_id'   => $current_user_id,
            'module_name'       => 'examples'
            );

        return array_merge($data, $values);
    }
}
?>