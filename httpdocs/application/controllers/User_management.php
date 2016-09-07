<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * the authentication suite should extend MY_Controller
 */
class User_management extends MY_Controller implements Module_Interface 
{

	public function __construct()
    {
        parent::__construct();
    }



    /****** BEGIN INTERFACE FUNCTIONS ******/

    public function create () 
    {
        
        if (!check_access_area(USER_MANAGEMENT)){
            $this->access_error('You cannot create users.');
            return;
        }

        $session_data = $this->session->userdata('user');
        $current_user_id = $session_data->id;

        $data = array(
            'title'           => 'User: Create',
            'module'          => $this->module_name,
            'current_user_id' => $current_user_id
        );

        $data = $this->set_standard_data($data);

        $this->load->view('user_management/editor', $data);
        return;

    }

    /**
     * Edit user account
     * Checks if we have an id and if it matches
     * the current admin signed in. If it does not 
     * match then checks if it is the super admin
     * who is th only type that can edit other accounts
     * 
     * @return resource view/user_management/edit.php
     */
    public function edit ($edit_user_id = NULL) 
    {
        if ($edit_user_id == null) {
            $this->access_error('Must have a user to edit');
            return;
        }
        // get user data and make sure they are trying to edit own account
        // unless they are super admin (1)

        $session_data = $this->session->userdata('user');

        $current_user_id = $session_data->id;

        $this->load->model('user_model');

        // if the user is trying to edit another user
        if ($edit_user_id != $current_user_id) {

            // check if they are not super admin
            if (!check_access_area(USER_MANAGEMENT)) {

                $this->access_error('You cannot edit another user.');
                return;
            }

            // if they made it here then they are super admin
        }

        $user_data = $this->user_model->with_id($edit_user_id);

        $data = array(
            'title'             => 'User Edit: '.$session_data->username,
            'current_user_id'   => $current_user_id,
            'user_data'         => $user_data,
            'module'            => $this->module_name
        );
        $data = $this->set_standard_data($data);

        // view edit page
        $this->load->view('user_management/editor', $data);
    }

    public function delete()
    {
        show_404();
    }

    public function export()
    {
        show_404();
    }

    public function index()
    {
        if (!check_access_area(USER_MANAGEMENT)) {
            $this->access_error('You cannot view this section.');
            return;
        }
        
        $qUsers['select'] = array(
            'id',
            'first_name',
            'last_name',
            'username',
            'active'
            );

        $users = $this->user_model->get_users($qUsers);

        /**
         * Start of table data
         */
        $table['headline'] = "Users";

        

        $table['table_header'] = array(
            array('label' => 'First Name'),
            array('label' => 'Last Name'),
            array('label' => 'Username'),
            array('label' => 'Active'),
            array('label' => '') // edit
            );

        $table['table_actions'] = array(
            array('add' => '/user-management/create'),
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
        foreach ($users as $user) {
            $user_array = (array) $user;

            $user_id = $user_array['id'];
            unset($user_array['id']);
            $tds = array();
            foreach ($user_array as $label) {

                $single_td = array('label' => $label);
                array_push($tds, $single_td);
            }

            // add edit
            $edit = array(
                'id'        => $user_id,
                'name'      => $user->username,
                'url'       => '/user-management/edit/',
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
            'title'     => 'User Management',
            'html_table'     => $html_table
            );


        $this->load->view('user_management/index', $data);
    }

    public function insert()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        if (!check_access_area(USER_MANAGEMENT)) {
            $response['success'] = false;
            $response['message'] = "Access Denied";
            $this->_output_json($response);
            return;
        }

        // put in insert functionality here
        //
 
        /**
         * Make sure there is no one with the same username or email 
         */
        
        $data = $this->input->post(null, true);

         if ($this->check_if_email_exists($data['email'])) {
            $response['success'] = false;
            $response['message'] = "Email already exists";
            $this->_output_json($response);
            return;
         }


         if ($this->check_if_username_exists($data['username'])) {
            $response['success'] = false;
            $response['message'] = "Username already exists";
            $this->_output_json($response);
            return;
         }

         /* manually set the last_modified fields */
        $session_data = $this->session->userdata('user');
        $current_user_id = $session_data->id;

        $data['last_modified_by'] = $current_user_id;
        $data['last_modified'] = $update_time = date("Y-m-d H:i:s");

        if (!$new_id = $this->user_model->insert($data)) {

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
        $edit_user_id = $data['id'];

        $this->load->model('user_model');

        // if the user is trying to edit another user
        if ($edit_user_id != $current_user_id) {

            // check if they are not super admin
            if (!check_access_area(USER_MANAGEMENT)) {
                $response['success'] = false;
                $response['message'] = "Access Denied";
                $this->_output_json($response);
                return;
            }

        }

        /**
         * check to make sure the data passed in has an ID and the ID refers to an existing record
         */
        if (!$data = $this->check_valid_record('user_model')) {
            return;
        }

        /**
         * Make sure there is no one with the same username or email 
         */

         if ($this->check_if_email_exists($data['email'], $edit_user_id)) {
            $response['success'] = false;
            $response['message'] = "Email already exists";
            $this->_output_json($response);
            return;
         }


         if ($this->check_if_username_exists($data['username'], $edit_user_id)) {
            $response['success'] = false;
            $response['message'] = "Username already exists";
            $this->_output_json($response);
            return;
         }
        
        $id = $data['id'];
        /* we don't want this mucking up our SQL */
        unset($data['id']);

        $current_password = "";
        $confirm_new_password = "";
        $new_password = "";

        /* save for later */
        if (isset($data['current_password']) &&
            isset($data['confirm_new_password']) &&
            isset($data['new_password'])) {

            $current_password = $data['current_password'];
            $confirm_new_password = $data['confirm_new_password'];
            $new_password = $data['new_password'];

            unset($data['current_password']);
            unset($data['confirm_new_password']);
            unset($data['new_password']);    
        } 
        
        

        /* manually set the last_modified fields */
        $session_data = $this->session->userdata('user');
        $current_user_id = $session_data->id;

        $data['last_modified_by'] = $current_user_id;
        $data['last_modified'] = $update_time = date("Y-m-d H:i:s");


        if (!$this->user_model->update($id, $data)) {

            $response['message'] = "Unable to save updated record at this time";
            $this->_output_json($response);
            return;
        }

        /**
         * Check if we have an updated password
         * if so check to see if current password matches
         * check if new passwords match
         * update passwords
         */
        
        if ($current_password != "" ||
            $confirm_new_password != "" ||
            $new_password != "") {

            if (!$this->user_model->with_credentials(
                $data['username'], $current_password)) {
                
                $response['message'] = "Incorrect current password.";
                $this->_output_json($response);
                return;
            }

            if ($confirm_new_password != $new_password) {
                $response['message'] = "Passwords do not match";
                $this->_output_json($response);
                return;
            }

            $this->user_model->set_password($id, $new_password);


        }

        $response['success'] = true;
        $response['message'] = 'saved';
        $this->_output_json($response);
    }

    /****** END INTERFACE FUNCTIONS ******/

    public function inactivate ($id = null) 
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        $this->load->model('user_model');

        $response = $this->get_initialized_json_response();

        if (!check_access_area(USER_MANAGEMENT)) {
            $response['success'] = false;
            $response['message'] = "Access Denied";
            $this->_output_json($response);
            return;
        }


        $id = $this->input->post('id', true);
        if (!$id) {
            $response['message'] = "Invalid Data";
            $this->_output_json($response);
            return;
        }

        $user = $this->user_model->with_id($id);
        if (!$user) {
            $response['message'] = "No user found with that ID";
            $this->_output_json($response);
            return;
        }

        $response['success'] = $this->user_model->inactivate($id);
        $response['message'] = $response['success'] ? "" : "Unable to update data at this time.";
        $this->_output_json($response);
    }

    private function set_standard_data ($values) 
    {

        // check user access and pass it to the view
        $super_admin = false;
        if (check_access_area(USER_MANAGEMENT)) {
            $super_admin = true;
        }

        $this->load->model('facilities_model');
        $facilities = $this->facilities_model->get_option_list(
            'id', 'display_name', null, 'display_name');

        $this->load->model('user_admin_types_model');
        $user_admin_types = $this->user_admin_types_model->get_option_list(
            'id', 'name', null, 'name');

        $data = array(
            'facilities'        => $facilities,
            'user_admin_types'  => $user_admin_types,
            'super_admin'       => $super_admin,
        );

        return array_merge($data, $values);
    }

    /**
     * Checks if the email already exists and is not the current user
     * @param  string $new_email 
     * @return boolen               if we have more than 1 result return true
     */
    private function check_if_email_exists ($new_email, $user_id = null) 
    {
        // check if we have model loaded
        if (!class_exists('user_model')) {
            $this->load->model('user_model');
        }

        // query for users with email address, if we have more than 1 result
        // return error
        $emails = $this->user_model->get_users(
            array('select' => array('email', 'id'))
            );
        
        $counter = 0;
        
        foreach ($emails as $email) {

            // if emails match and it is not the current user
            if ($email->email == $new_email && $email->id != $user_id) {
                $counter++;
            }
        }

        if ($counter > 0) {
            return true;
        }

        return false;    
        
    }

    /**
     * Checks if the username already exists and is not the current user
     * @param  string $new_username 
     * @return boolen               if we have more than 1 result return true
     */
    private function check_if_username_exists ($new_username, $user_id = null) 
    {
        // check if we have model loaded
        if (!class_exists('user_model')) {
            $this->load->model('user_model');
        }

        
        $usernames = $this->user_model->get_users(
            array('select' => array('username', 'id'))
            );

        $counter = 0;
        foreach ($usernames as $username) {
            
            // if usernames match and it is not the current user
            if ($username->username == $new_username
                && $username->id != $user_id) {
                $counter++;
            }
        }

        if ($counter > 0) {
            return true;
        }

        return false; 
    }
}
?>