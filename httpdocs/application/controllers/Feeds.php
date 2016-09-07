<?php

/**
 * Feed controller; fetches data from other related models in AJAX calls
 * Extends Fuel_base_controller to take advantage of the FUEL user/login model
 */
class Feeds extends MY_Controller
{
	function __construct()
	{
		parent::__construct();
	}

	function getcitystate($zip = false)
	{
		$zip = substr(preg_replace('/[^0-9]/', '', $zip),0,5);
		if(strlen($zip) >= 5)
		{
			$response = $this->get_initialized_json_response();
			$query = $this->db->get_where('zipcodes', array('zip' => $zip), 1);
			$this->_output_json($query->result_array());

		}
	}

}