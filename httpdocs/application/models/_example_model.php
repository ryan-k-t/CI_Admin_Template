<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class _example_model extends MY_Model {

    protected $_table_name = "_example_table_name";

    public function __construct()
    {
		// Call the CI_Model constructor
		parent::__construct();
    }

}
?>