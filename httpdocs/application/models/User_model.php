<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class User_model extends MY_Model {

	protected $_table_name = "user_admins";

	public function __construct()
	{
		// Call the CI_Model constructor
		parent::__construct();
	}

    /**
     * [with_credentials description]
     * @param  [type] $username [description]
     * @param  [type] $password [description]
     * @return [type]           [description]
     */
    public function with_credentials($username, $password)
    {
    	if(!$username || !$password)
    	{
			//Obviously we need both
    		return FALSE;
    	}

    	$this->db->where('active', 'yes');
    	$query = $this->db->get_where($this->_table_name, array('username'=>$username));
    	if(!$query)
    	{
    		log_message('error',__CLASS__.'->'.__FUNCTION__.' experienced an SQL error. The error was '.$this->db->error().' and the query was '.$this->db->last_query());
    		return FALSE;
    	}

    	$user_data = $query->row();
    	$query->free_result();

    	if(!$user_data->id)
    	{
			//No user with that id
    		return FALSE;
    	}

    	$test_hash = $this->_salted_string($password, $user_data->salt);

    	if($test_hash != $user_data->hash)
    	{
			//Invalid Password for that user
    		return FALSE;
    	}

    	return $user_data;
    }

	/**
	 * [with_reset_credentials description]
	 * @param  [type] $username         [description]
	 * @param  [type] $email_address    [description]
	 * @param  [type] $public_reset_key [description]
	 * @return [type]                   [description]
	 */
	public function with_reset_credentials($username, $email_address, $public_reset_key)
	{
		if(!$username || !$email_address || !$public_reset_key)
		{
			return FALSE;
		}
		
		$user_data = $this->with_username($username);
		if(!$user_data || !$user_data->id)
		{
			//No user with that name
			return FALSE;
		}
		
		
		$test_hash = $this->_salted_string("PASSWORD-RESET", $public_reset_key.$user_data->salt);
		if($test_hash != $user_data->reset_key)
		{
			//invalid reset key
			return FALSE;
		}
		
		return $user_data;
	}
	
	/**
	 * [with_username description]
	 * @param  string $username 
	 * @return FALSE on failure or the DB object on success
	 */
	public function with_username($username)
	{
		if(!$username)
		{
			//Obviously we need a username
			return FALSE;
		}
		
		$query = $this->db->get_where($this->_table_name, array('username'=>$username));
		if(!$query)
		{
			log_message('error',__CLASS__.'->'.__FUNCTION__.' experienced an SQL error. The error was '.$this->db->error().' and the query was '.$this->db->last_query());
			return FALSE;
		}

		$user_data = $query->row();
		$query->free_result();

		if(!$user_data || !$user_data->id)
		{
			//No user with that name
			return FALSE;
		}
		
		return $user_data;
	}
	
	/**
	 * [generate_reset_key description]
	 * TODO: how to tie this in?!?!
	 * 
	 * @return [type] [description]
	 */
	public function generate_reset_key($user_obj)
	{
		$public_reset_key = $this->_random_salt();

		$this->db->set('reset_key', $this->_salted_string("PASSWORD-RESET", $public_reset_key.$user_obj->salt));
		$this->db->where('id', $user_obj->id);
		$this->db->update($this->_table_name);

		return $public_reset_key;
	}
	
	/**
	 * [random_password description]
	 * @param  $CI db object $user_obj [description]
	 * @param  integer $password_length [description]
	 * @return [type]                   [description]
	 */
	public function set_random_password($user_obj, $password_length=8)
	{
		$pass = "";
		$chars = array(	'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'k', 
			'm', 'n', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 
			'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 
			'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 
			'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '2', '3', '4', 
			'5', '6', '7', '8', '9', '!', '@', '#', '$', '%', 
			'^', '&', '*', '-', '+', '?', ']', ':', '<', '>');

		for($i=0; $i<$password_length; $i++)
		{
			$pass .= $chars[rand(0, count($chars)-1)];
		}

	 	$this->set_password($user_obj, $pass);

	 	return $pass;
	}
	public function set_password ($user_obj, $password) 
	{
		$user_id = $user_obj;

		if (is_object($user_obj)) {
			$user_id = $user_obj->id;	
		}
		
		/*update the user's record */
		$salt = $this->_random_salt();
		$this->db->set('salt', $salt);
		$this->db->set('hash', $this->_salted_string($password, $salt));
		$this->db->where('id', $user_id);
		$this->db->update($this->_table_name);

	}
	
	/**
	 * [_random_salt description]
	 * @return [type] [description]
	 */
	private function _random_salt()
	{
		return md5(rand());
	}
	
	/**
	 * [_salted_string description]
	 * @param  [type] $string [description]
	 * @param  [type] $salt   [description]
	 * @return [type]         [description]
	 */
	private function _salted_string($string, $salt)
	{
		
		return hash('sha256', "{$salt}{$this->config->item('encryption_key')}{$string}", FALSE);
	}


	public function set_last_login($user_id)
	{
		$this->db->set('last_login', date("Y-m-d H:i:s"));
		$this->db->where('id', $user_id);
		$this->db->update($this->_table_name);
	}

	/**
	 * Getter for acces levels
	 * @return object
	 */
	public function get_access_levels ()
	{
		$this->db->from("access_areas");
		$query = $this->db->get();
		$result = $query->result();
		$query->free_result();
		return $result;
	}

	/**
	 * This gets the  access level of the admin
	 * currently signed in.
	 * 
	 * @return object 		Access levels accessible
	 */
	public function get_admin_access_levels () {

		$type_id = $this->get_admin_access_type();
		$type_id = $type_id->user_admin_type_id;

		$this->db->select('user_admin_type_access_areas.access_area_id');
		$this->db->from("user_admin_type_access_areas");
		$this->db->where('user_admin_type_id', $type_id);
		$query = $this->db->get();
		$result = $query->result();
		$query->free_result();
		return $result;
	}

	public function get_admin_access_type ($id=null) 
	{
		if (!$id){
			$id = $this->session->userdata('user')->id;
		}

		$this->db->select('user_admin_type_id');
		$this->db->from('user_admins');
		$this->db->where('id', $id);
		$query = $this->db->get();
		return $query->row();

	}

	public function inactivate($id)
    {
    	return $this->update($id, array('active' => 'no'));
    }
    
    public function activate($id)
    {
    	return $this->update($id, array('active' => 'yes'));
    }

    /**
     * get all the users from db
     * @param  array $params	This allows the function to query for specifcally 
     *                       	what you need. Blank will get all information
     * @return object         	Data from DB
     */
    public function get_users ($params) 
    {
    	if (!empty($params)) {

    		foreach ($params['select'] as $select) {

    			// dont allow to query for passwords
    			if ($select != "salt" ||
    				$select != "reset_key" ||
    				$select != "hash") {

    				$this->db->select($select);
    			}
    		}
    	}
    	$this->db->from('user_admins');
    	$query = $this->db->get();
    	$result = $query->result();
		$query->free_result();
    	return $result;
    }

}
?>