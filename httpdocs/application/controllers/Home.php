<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * the authentication suite should extend MY_Controller
 */
class Home extends MY_Controller {

	public function __construct()
    {
        parent::__construct();
        $this->load->helper('text');
    }

    public function index()
    {

        $quicklink_data = array(
        );


		$data = array(
			'title'		 	=> 'Home',
		);
			
		$this->load->view('home', $data);
    }

    //Just a helpful function to convert and create the table data
    private function _convert_table($table,$data)
    {
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
        foreach ($data as $example) {
            $data_array = (array) $example;

            unset($data_array['id']);
            $tds = array();
            foreach ($data_array as $label) {

                $single_td = array('label' => $label);
                array_push($tds, $single_td);
            }

            array_push($table['table_rows'], $tds);
            
        }

        return $this->table_builder->sortable_table($table);
    }
}
?>