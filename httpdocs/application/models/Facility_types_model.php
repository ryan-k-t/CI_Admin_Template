<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Facility_types_model extends MY_Model {

    protected $_table_name = "facility_types";

    public function __construct()
    {
		// Call the CI_Model constructor
		parent::__construct();
    }
}
?>