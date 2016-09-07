<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * the authentication suite should extend MY_Controller
 */
class Comments extends MY_Controller implements Module_Interface 
{

	public function __construct()
    {
        parent::__construct();
        /**
         * load the model. Note: this will also add the class Customer as well;
         */
        $this->load->model('comments_model');
    }



    /****** BEGIN INTERFACE FUNCTIONS ******/

    public function create () 
    {
        show_404();
    }

    public function delete()
    {
        show_404();
    }

    public function edit($id = NULL) 
    {
        show_404();
    }

    public function export()
    {
        show_404();
    }

    public function index()
    {
        show_404();
    }

    public function insert()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        /* make sure our data is clean */
        $data = $this->input->post(NULL, TRUE);

        $response = $this->get_initialized_json_response();

        /* manually set the last_modified fields */
        $session_data = $this->session->userdata('user');
        $current_user_id = $session_data->id;

        $data['user_admin_id'] = $current_user_id;
        $data['datetime_posted'] = date("Y-m-d H:i:s");

        if(!$new_id = $this->comments_model->insert($data))
        {
            $response['message'] = "Unable to save record at this time";
        }
        else
        {
            $response['success'] = TRUE;
            $response['id'] = $new_id;
        }
        $this->_output_json($response);
    }

    public function update()
    {
        show_404();
    }

    /****** END INTERFACE FUNCTIONS ******/


    public function show_pending()
    {
        if(!$this->input->is_ajax_request())
        {
            show_404();
            return;
        }

        $post_data = $this->input->post(NULL, TRUE);

        $this->load->view('comments/listing', array('comments'=> $post_data['comments'], 'pending' => TRUE));
    }

    public function show($table, $id)
    {
        if(!$this->input->is_ajax_request())
        {
            show_404();
            return;
        }

        $comments = $this->comments_model->with_table_key($table, $id);
        $this->load->view('comments/listing', array('comments'=> $comments, 'pending' => FALSE));
    }

    /**
     * insert a group of comments to a single record via AJAX
     * @return JSON
     */
    public function insert_batch()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        /* make sure our data is clean */
        $data = $this->input->post(NULL, TRUE);

        $response = $this->get_initialized_json_response();

        $session_data = $this->session->userdata('user');

        $record_data = array(
            'key'             => $data['key'],
            'table'           => $data['table'],
            'user_admin_id'   => $session_data->id,
            'datetime_posted' => date("Y-m-d H:i:s")
        );

        $failed = FALSE;
        foreach($data['comments'] as $comment)
        {
            $record_data['comment'] = $comment;
            if(!$new_id = $this->comments_model->insert($record_data))
            {
                $response['message'] = "Unable to save the comment \"".$comment."\" at this time";
                $failed = TRUE;
                break;
            }
        }

        $response['success'] = !$failed;
        $this->_output_json($response);
    }
}
?>