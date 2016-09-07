<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Comments_model extends MY_Model {

    protected $_table_name = "comments";

    public function __construct()
    {
		// Call the CI_Model constructor
		parent::__construct();
    }

    public function with_table_key($table, $key)
    {
    	return $this->get_all(array('table' => $table, 'key' => $key), 'datetime_posted DESC');
    }
}

class Comment extends ER_Object {
	protected $id;
	protected $table;
	protected $key;
	protected $user_admin_id;
	protected $datetime_posted;
	protected $comment;

	public function __construct($data = NULL)
	{
		parent::__construct($data);
	}

    private $_user_admin;
    protected $user_admin;
    public function get_user_admin()
    {
    	$CI =& get_instance();
    	$CI->load->model('user_model');
    	return $CI->user_model->with_id($this->user_admin_id);
    }

    public function get_datetime_posted_formatted($format_string)
    {
    	return date($format_string, strtotime($this->datetime_posted));
    }
}
?>