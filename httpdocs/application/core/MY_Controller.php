<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Base_Controller extends CI_Controller
{
	/**
	 * the name of this module, to be used as a simple Don't Repeat Yourself item ...
     * -- this is the name of the module as it would be seen in links or in the URL
	 */
	public $module_name;

    /**
     * the name to be referenced for the model. this is commonly the module name with the dashes replaced with an underscore
     */
    public $model_name;

    /**
     * the name of this database table, to be used as a simple Don't Repeat Yourself item ...
     *
     * this is only really necessary if the module_name and database table names aren't in sync
     * for instance: email-templates vs. email_templates
     */
    public $table_name;

    /**
     * the front-end name of this section, to be used as a simple Don't Repeat Yourself item ...
     * this is the human readable version of the module's name (ie: Bag Manager for bag-manager)
     */
    public $display_name;

	/**
	 * used for the listing() function. Filter fields named within this array will 
	 * use a like call for the DB query
	 */
	public $listing_like_fields = array("name");
	


	function __construct($module_name = "")
	{
		parent::__construct();
		$this->module_name = $module_name ? $module_name : strtolower(get_class($this));
        if(empty($this->model_name))
        {
            $this->model_name = str_replace("-", "_", $this->module_name);
        }
        if(empty($this->table_name))
        {
            $this->table_name = $this->module_name;
        }
	}

	/**
	 * Shortcut to send json headers and response
	 * @param  array 	$arr 	Array to output
	 * @return string       	Output in JSON format
	 */
	protected function _output_json($arr = false)
	{
		if(is_array($arr))
		{
			header('Content-Type: application/json');
			echo json_encode($arr);
		}
	}

	/**
	 * creates basic array structure for AJAX JSON response object
	 * @return array
	 */
	protected function get_initialized_json_response()
	{
		return array("success"=>FALSE, "message"=>"");
	}

    protected function access_error($message = "") 
    {
        show_error($message, 500, ACCESS_ERROR);
    }

    /**
     * standard check to see if the post array has an ID value
     * and that the ID value refers to a legitimate record
     * 
     * @param  string $model_name 
     * @return mixed             FALSE on error or POST data on success
     */
    protected function check_valid_record($model_name)
    {
    	$response = $this->get_initialized_json_response();

        /* clean our data */
        $data = $this->input->post(NULL, TRUE);

        /* make sure they at least provided an ID! */
        if(!isset($data['id']))
        {
            log_message('error',get_class($this).'->'.__FUNCTION__.' does not have an ID value in the POST');
            $response['message'] = "Invalid Data";
            $this->_output_json($response);
            return FALSE;
        }

        /* make sure the model is loaded */
        if(isset($this->$model_name))
        {
        	$this->load->model($model_name);
        }

        /* check to make sure the record even exists */
        $record = $this->$model_name->with_id($data['id']);
        if(!$record || !$record->id)
        {
            $response['message'] = "There is no record in the system that matches the data provided";
            $this->_output_json($response);
            return FALSE;
        }

        return $data;
    }

    /**
     * A generic AJAX function to return the DOM for a table listing of records
     * filters are applied via POST arguments. Fields prefixed by "filter-" will have their name changed
     * to strip out "filter-";
     * 
     * @return HTML
     */
    public function listing()
    {
        if(!$this->input->is_ajax_request())
        {
            show_404();
            return;
        }

        $filters = $this->input->post(NULL, TRUE);

        /**
         * lets separate any fields we want to "like" on from our filters
         * it will also alter our $filters array to prefix with the table name
         */
        $likes = array();
        $wheres = array();
        /** lets also remove the "filter-" prefix to our fields */
        foreach($filters as $key=>$value)
        {
            $deprefixed_key = str_replace("filter-", "", $key);
            if(in_array($deprefixed_key, $this->listing_like_fields))
            {
                $likes[$this->table_name.".".$deprefixed_key] = $value;
            }
            else
            {
                $wheres[$this->table_name.".".$deprefixed_key] = $value;
            }
        }


        $model = $this->get_model();
        if(!$this->$model)
        {
        	log_message('error','Model '.$model.' does not exist');
        	echo "";
        	return;
        }
        /* make sure we have the function we are looking for */
        $function_name = "get_listing";
        if(!method_exists($this->$model, $function_name))
        {
        	log_message('error','Function "'.$function_name.'" does not exist in model: '.$model);
        	echo "";
        	return;
        }

        $records = $this->$model->$function_name($wheres, $likes);
        echo $this->_listing($records);
    }

    protected function _listing(array $records = array())
    {
    	return "";
    }

    public function process_import ($expected_fields) 
    {
        /* prep our response */
        $response = $this->get_initialized_json_response();
        $model = $this->get_model();

         /* make sure our data is clean */
        $post_data = $this->input->post(NULL, TRUE);

        log_message('debug','HERE is our IMPORT DATA');
        log_message('debug',print_r($post_data, TRUE));

        $data_to_import = $post_data['data'];
        if(!$data_to_import)
        {
            log_message('error',"No data to import");
            $response['message'] = "Data is not properly assigned";
            return $response;
        }

        if(!is_array($data_to_import))
        {
            log_message('error',"Data to import is not in an array");
            $response['message'] = "Data is not properly assigned";
            return $response;
        }

        if(count($data_to_import) < 1) /* the 1st row is assumed the headers */
        {
            log_message('error',"Data to import does not have any data?!");
            $response['message'] = "Data is not properly assigned";
            return $response;
        }

        /* add an element to the response for showing each rows status */
        $response['rows'] = array();

        /* pluck off the header row */
        array_shift($data_to_import);
        $row_number = 1;
        $response['rows'][] = array(
            "row_number" => $row_number,
            "success"    => FALSE,
            "message"    => "Header row ignored"
        );

        $current_user_id = $this->get_current_user_id();

        /* the order of the expected fields array should match
        what is coming across as columns in the data */
        
        $expected_fields_count = count($expected_fields);
        foreach($data_to_import as $row)
        {
            $row_number++;

            $row_response = array(
                "row_number" => $row_number,
                "success"    => FALSE,
                "message"    => ""
            );

            if($expected_fields_count != count($row))
            {
                $row_response['message'] = "The # of fields in this row do not match the number of rows for this record";
                $response['rows'][] = $row_response;
                continue;
            }

            $has_content = FALSE;
            $data = array();
            foreach($row as $index=>$column)
            {
                $data[ $expected_fields[$index] ] = $column;
                if(!empty($column))
                {
                    $has_content = TRUE;
                }
            }

            if(!$has_content)
            {
                $row_response['message'] = "There is no data for this row";
                $response['rows'][] = $row_response;
                continue;
            }
            
            if($this->$model->record_exists($data))
            {
                $row_response['message'] = "There already is a record in the system for this entry";
                $response['rows'][] = $row_response;
                continue;
            }

            /**
             * add any included ancillary data to the row
             */
            $data['_ancillary_data'] = $post_data['_ancillary_data'];

            $import_response = $this->$model->import_insert($data, $current_user_id);
            $import_response['row_number'] = $row_number;
            $response['rows'][] = $import_response;
        }

        $response['success'] = TRUE;
        return $response;
    }

    protected function get_model () 
    {
        $model = $this->model_name."_model";
        /* load the model if necessary */
        if(!$this->$model)
        {
            $this->load->model($model.".php");
        }
        return $model;
    }
}

/* The MX_Controller class is autoloaded as required */
class MY_Controller extends Base_Controller
{
	static $redirect_path = "/login";
	/**
	 * Check for user session
	 *
	 * if they are not signed in push them
	 * to login controller
	 *
	 */
	function __construct($module_name = "")
	{
		parent::__construct($module_name);
		if(!$this->session->has_userdata('user'))
		{
			$this->session->set_userdata('auth_bump', $this->uri->uri_string());
            if($this->input->is_ajax_request())
            {
                $response = $this->get_initialized_json_response();
                $response['message'] = "Unfortunately, your session has expired. Please log-in again.";
                $response['redirect_to'] = self::$redirect_path;
                $this->_output_json($response);
                exit();
            }
            else
            {
                self::go_to_login();
            }
			return;
		}
	}

	static function go_to_login()
	{
		redirect(self::$redirect_path);
	}

    public function get_current_user_id()
    {
        $session_data = $this->session->userdata('user');
        return $session_data->id;
    }
}

/**
 * an interface to require modules to have specific, structured functions
 * if your controller does not require one of these functions just script it
 * to show_404() and that's all
 */
interface Module_Interface {

	/**
	 * intended for basic page displaying fields to populate
	 */
	public function create();

	/**
	 * JSON request to delete record via POST
	 */
	public function delete();

	/**
	 * intended for basic page displaying record for editing
	 */
	public function edit($id = NULL);

	/**
	 * intended for basic listing page
	 */
	public function index();

	/**
	 * JSON request to insert record via POST
	 */
	public function insert();

	/**
	 * JSON request to update record via POST
	 */
	public function update();

	/**
	 * JSON request to export record listing via POST
	 */
	public function export();
}
?>