<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * this class exists outside of the authentication suite
 * as such it only extends the CI_Controller whereas items within
 * the authentication suite should extend MY_Controller
 *
 *
 *
 * This class handles the admin user login/logout functionality
 */
class Login extends Base_Controller {

	public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
    }


	/**
	 * general login page -- if the user navigates here and is already logged in it will go to the homepage or their bump page
	 */
	public function index()
	{
		//if they're already logged in send them to the sport-type choice page
		if($this->session->has_userdata('user'))
		{
			redirect($this->session->has_userdata('auth_bump') ? $this->session->userdata('auth_bump') : '/');
			return;
		}

		$data = array(
			'forward_to' => $this->session->has_userdata('auth_bump') ? $this->session->userdata('auth_bump') : '/',
			'title'		 => 'Login',
			'body_class' => 'login',
		);
			
		$this->load->view('login', $data);
	}
	
	/**
	 * basic logout
	 */
	function logout()
	{
		$this->session->sess_destroy();
		redirect('/');
	}

	/**
	 * This is the URL sent to the user for logging in with a reset password. The password is randomly generated and then they should be able
	 * to set their own password
	 * 
	 * @param  string $username      
	 * @param  string $email_address 
	 * @param  string $reset_key     
	 * @return n/a
	 */
	function reset($username, $email_address, $reset_key)
	{
		$email_address = str_replace(':AT:', '@', $email_address);
		
		log_message('debug', "###### RESET A PASSWORD ######## ");
		log_message('debug', "Username: {$username}");
		log_message('debug', "Email: {$email_address}");
		log_message('debug', "Key: {$reset_key}");
		
		$user = $this->user_model->with_reset_credentials($username, $email_address, $reset_key);
		if($user)
		{
			$new_password = $this->user_model->set_random_password($user);

			$this->load->view('login', array(
				'password_reset'	=> $new_password,
				'forward_to'    	=> $this->session->has_userdata('auth_bump') ? $this->session->userdata('auth_bump') : '/',
				'title'				=> 'Reset Password',
				'body_class'		=> 'login',				
			));
		}
		else
		{
			log_message('error',"COULDN'T FIND A USER MATCHING THE CREDS PROVIDED!!!");
			redirect('/');
		}
	}
	
	/**
	 * Used to determine if the user is still logged in via AJAX;
	 * that way AJAX calls don't go into the ether when the session is expired
	 * @return JSON object with a boolean property of authenticated
	 */
	function authenticated()
	{
		$result = array('authenticated'=>FALSE);
		if($this->session->has_userdata('user'))
		{
			$result['authenticated'] = is_numeric($this->session->user->id);
		}
		$this->_output_json($result);
	}

	/**
	 * an AJAX post request that accepts the following post arguments
	 * 		username
	 * 		password
	 * 		
	 * @return JSON object
	 *         success: boolean
	 *         message: text
	 *         forward_to: text / url
	 */
	function authenticate()
	{
		$response = $this->get_initialized_json_response();
		$data = $this->input->post(null, true);

		if($data['username'] && $data['password'])
		{
			log_message('debug','username: '.$data['username']);
			log_message('debug','password: '.$data['password']);
			$user = $this->user_model->with_credentials($data['username'], $data['password']);
		}
		
		if($user)
		{	
			$forward_to = $data['forward_to'];

			$this->_set_active_user($user);
			$this->session->userdata('forward_to', $forward_to);
			$response['forward_to'] = $forward_to;
			$response['success'] = TRUE;

			/* may need this logic later
			if($user->type > user_model::DEALER)
			{
				$this->_set_active_user($user);
				$_SESSION['forward_to'] = $forward_to;
				$response['success'] = TRUE;
			} else {
				$dealer = $this->dealer_model->with_id($user->dealer);
				if(!$dealer->id || $dealer->active == FALSE)
				{
					$response['message'] = 'The Dealer Account associated with this user has been de-activated';
				} else {
					$this->_set_active_user($user);
					$_SESSION['forward_to'] = $forward_to;
					$response['success'] = TRUE;
				}
			}
			*/
		} else {
			$response['message'] = 'Invalid Username or Password';
		}

		$this->_output_json($response);
	}
	

	/**
	 * INCOMPLETE
	 * 
	 * @return [type] [description]
	 */
	function impersonate()
	{	
		if(!($_SESSION['user']->type == User_model::ADMIN || $_SESSION['user']->type == User_model::DEV))
		{
			return FALSE;
		}
		
		$response = $this->get_initialized_json_response();

		$username = $this->input->post('username');
		$user = $this->user_model->with_username($username);
		if($user)
		{
			if($user->type < $_SESSION['user']->type)
			{
				/* create a fallback for this account before switching to impersonated account */
				$this->_set_revert_user();
				/* impersonate the user but don't update their history */
				$this->_set_active_user($user, FALSE);
				$response['success'] = TRUE;
			}
			else
			{
				$response['message'] = "You do not have permission to impersonate user \"{$username}\"";
			}
		}
		else
		{
			$response['message'] = "User \"{$username}\" not found";
		}

		$this->_output_json($response);
	}
	
	/**
	 * generates an email with a URL to the reset page -- which will generate a random password for the user
	 * 
	 * @return JSON
	 */
	function request_password()
	{
		$response = $this->get_initialized_json_response();

		$username = $this->input->post('username');
		if(!$user = $this->user_model->with_username($username))
		{
			log_message("DEBUG", "@@@@@@@@@@@@@@ SOMEONE REQUESTED A RESET FOR \"{$username}\" -- BUT THERE IS NO SUCH USER! @@@@@@@");
			$response['message'] = "User not found";
			$this->_output_json($response);
			return;
		}

		log_message("DEBUG", "#################### REQUEST A PASSWORD RESET FOR USER \"{$user->username}\" #####################");
		log_message("DEBUG", print_r($user, TRUE));
		
		if(!$user->email)
		{
			$response['message'] = "No e-mail address on file for that user. Please contact your supervisor for assistance";
			$this->_output_json($response);
			return;
		}
		
		$reset_key = $this->user_model->generate_reset_key($user);
		$email_address = str_replace('@', ':AT:', $user->email);
		$reset_link = "http".(is_secure() ? "s" : "")."://".$_SERVER['SERVER_NAME']."/login/reset/".$username."/".$email_address."/".$reset_key."/";
		
		$this->load->library('email');
		$this->email->initialize(array(
			'mailtype' => 'text'
		));
		
		$message = "This e-mail is being sent to you because you requested a password reset for your account on http".(is_secure() ? "s" : "")."://".$_SERVER['SERVER_NAME']."/.\n\nTo recover your password, visit: ".$reset_link;
		$this->email->from($this->config->item('wedu_serverEmail'), $this->config->item('wedu_serverFrom'));
		$this->email->to($user->email);
		$this->email->subject("Password Recovery");
		$this->email->message($message);
		$this->email->send();
		
		$response['message'] = "A password recovery link has been sent to the e-mail address associated with your username. Please check your junk email and spam filtering to make sure the email was not inappropriately flagged as junk or spam.";
		$response['success'] = TRUE;
		$this->_output_json($response);
	}
	

	/**
	 * INCOMPLETE!!!!
	 * 
	 * @return JSON
	 */
	function change_password()
	{
		$response = $this->get_initialized_json_response();

		if($this->input->post('username') && $_SESSION['user']->type >= User_model::ADMIN)
		{
			$user = $this->user_model->with_username($this->input->post('username'));
			if($user->type > $_SESSION['user']->type)
			{
				unset($user);
				$response['message'] = "You do not have permission to modify this user's password";
				$this->_output_json($response);
				return;
			}
		}
		else
		{		
			$user_id = $_SESSION['user']->id;
			$username = $this->db->get_where('Users', array('id'=>$user_id))->row()->username;
			$password = $this->input->post('current_password');
			$user = $this->user_model->with_credentials($username, $password);
		}
		
		if($user)
		{
			$user->set('password', $this->input->post('new_password'))->save();
			$response['success'] = TRUE;
		}
		else
		{
			$response['message'] = "Invalid Current Password. Please try again.";
		}
		$this->_output_json($response);
	}
	

	/**
	 * INCOMPLETE!!!!
	 *
	 * 
	 * @return JSON
	 */
	function restore_user()
	{
		if(!$this->session->has_userdata('revert') || !isset($this->session->revert['user']))
		{
			return FALSE;
		}
		
		$response = $this->get_initialized_json_response();

		$this->session->set_userdata('user', $this->session->revert['user']);
		$this->session->unset_userdata('revert');

		$response['success'] = TRUE;
		$this->_output_json($response);
	}

	/**
	 * Take the currently logged in user and stash their information so they can revert to this login later
	 *
	 * Used in conjunction with impersonate
	 */
	private function _set_revert_user()
	{
		$this->session->set_userdata('revert', array(
			'user' => $this->session->userdata('user')
		));
	}

	/**
	 * this sets the active user in the session
	 * @param obj  $user           
	 * @param boolean $update_history whether or not to log the last login for the user -- not needed when impersonating
	 */
	private function _set_active_user($user, $update_history = TRUE)
	{
		if(!is_bool($update_history)) $update_history = TRUE;

		$session_user = $user;
		unset($session_user->salt);
		unset($session_user->hash);
		$this->session->set_userdata('user', $user);
		if($this->session->has_userdata('auth_bump'))
		{
			$this->session->unset_userdata('auth_bump');
		}

	
		/* update their DB info for tracking, etc. */
		if($update_history)
		{
			$this->user_model->set_last_login($user->id);
		}
			
	}
}
