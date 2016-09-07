<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
// add your site specific functions here


/* safe conversions to and from for use in URLs */
function base64url_encode($str)
{
    $base64 = base64_encode($str);
    return strtr($base64, '+/=', '-_~');
}

function base64url_decode($str)
{
	$text = strtr($str, "-_~", "+/=");
	return base64_decode($text);
}


function is_secure() {
  return
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || $_SERVER['SERVER_PORT'] == 443;
}

/**
 * Boolean check to determine string content is a JSON object string
 *
 * @param	mixed	possible serialized string
 * @return 	boolean
 */
function is_json_str($data)
{
	if (is_string($data))
	{
		$json = json_decode($data, TRUE);
		return ($json !== NULL AND $data != $json);
	}
	return NULL;
}

/**
 * This will check the access level of the current signed in
 * user based off the required access level they are trying
 * to access
 * 
 * @param  string $access_area Access area tring to be 
 *                             accessed
 *                             
 * @return boolean              True or false
 */
function check_access_area ($access_area = null) {
		
	if (!$access_area){
		return;
	}

	$CI = & get_instance();

	// get admin access levels	
	
	if (!class_exists('user_model')) {
	    $CI->load->model('user_model');
	}

	$access = $CI->user_model->get_admin_access_levels();

	if (!$access) {
		return;
	}
	
	// convert object to array
	foreach ($access as $value) {
		$access_array[] = $value->access_area_id;	
	}
	
	$result = in_array($access_area, $access_array);

	return $result;
}


/**
 * takes an associative array and returns a string of <option> elements
 * @param  array $array 
 * @return string        HTML
 */
function build_options($array)
{
    $return = "";
    foreach($array as $key=>$value)
    {
        $return .= "<option value=\"".$key."\">".$value."</option>";
    }
    echo $return;
}

function is_empty_date_value($date)
{
	if(is_null($date) || $date == "" || $date == "0000-00-00" || $date == "0000-00-00 00:00:00")
	{
		return TRUE;
	}

	return FALSE;
}
/**
 * fixes the date picker format
 * 
 * @param  string $date
 * @return date
 */
function fix_date_from_date_picker ($date)
{
	if(is_empty_date_value($date))
	{
		return null;
	}

	return date('Y-m-d', strtotime($date));
}

function set_date_for_date_picker ($date) 
{
	if(is_empty_date_value($date))
	{
		return "";
	}

	return date('m/d/Y', strtotime($date));
}

function formatted_date($date_string, $format = "n/j/Y g:i:s A")
{
	if(is_empty_date_value($date_string))
	{
		return "";
	}

	$time = strtotime($date_string);
	if($time === FALSE || $time == -1)
	{
		return "";
	}

	return date($format, $time);
}


/**
 * mask a credit card
 * @param  string $number           the account number
 * @param  string $maskingCharacter optional -- what to replace the masked characters with
 * @return string                   
 */
function mask_cc($number, $maskingCharacter = 'x')
{
    return substr($number, 0, 4) . str_repeat($maskingCharacter, strlen($number) - 8) . substr($number, -4);
}

/**
 * mask all the characters except for the last one
 * @param  string $string           the account number
 * @param  string $maskingCharacter optional -- what to replace the masked characters with
 * @return string                   
 */
function mask_all_but_last_char($string, $maskingCharacter = 'x')
{
    return str_repeat($maskingCharacter, strlen($string) - 1) . substr($string, -1);
}
/* End of file my_helper.php */
/* Location: ./application/helpers/my_helper.php */
