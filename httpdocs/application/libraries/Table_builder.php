<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Table_builder 
{
	private $_CI;

	function __construct()
	{
		$this->_CI =& get_instance();
		log_message("DEBUG", __CLASS__." library initialized");
	}

	/**
	 * called when you need a sortable table
	 * @param  array params 	This takes in the defaults in set_parameters()
	 * @return html          
	 */
	public function sortable_table ($params)
	{
		$params = $this->set_parameters($params);

		$output = "<section class='dash-block'>";

		$output .= "<header>";
		$output .= "<h3>".$params["headline"]."</h3>";

		if (!empty($params['table_actions'])) {
			$output .= $this->add_actions($params['table_actions']);
		}

		$output .= "</header>";

		$output .= "<main>";

		$output .= "<table class='sortable-theme-light ".$params['table_class'];
		$output .= " ' data-sortable data-sortable-initialize=\"true\"";
		$output .= ">";

		// make sure the arrays have data
		if (!empty($params['table_header'])) {
			$output .= $this->build_header($params['table_header']);
		}

		if (!empty($params['table_rows'])) {
			$output .= $this->build_body($params['table_rows']);
		} else if (!empty($params['no_rows_content'])) {
			$output .= $this->build_no_content_message($params['table_header'], $params['no_rows_content']);
		}

		if (!empty($params['table_footer'])) {
			$output .= $this->build_footer($params['table_footer']);
		}

		$output .= "</table>";

		$output .= "</main>";

		$output .= "</section>";

		return $output;
	}

	/**
	 * This function will ass actions to the top of the table
	 * @param array $actions associative array
	 */
	private function add_actions ($actions) 
	{
		
		$output = "";

		/**
		 * Actions will go in this order
		 * view 		view_all
		 * add 			add new
		 * export 		will export current filter
		 * export_all 	will export all
		 * import 		import
		 */

		foreach ($actions as $key=>$value) :

			if (isset($value['view'])) {
				if (is_array($value['view'])) {
					foreach ($value['view'] as $key=>$val) {
						$url = $key;
						$label = $val;
					}
				} else {
					$url = $value['view'];
					$label = "View All";
				}
				$action_params['view_all'] = array(
					'label'			=> $label,
					'url'			=> $url,
					'icon_class'	=> 'fa-arrow-circle-right',
					'class'			=> 'view_all'
				);
			}

			if (isset($value['add'])) {
				if (is_array($value['add'])) {
					foreach ($value['add'] as $key=>$val) {
						$url = $key;
						$label = $val;
					}
				} else {
					$url = $value['add'];
					$label = "Add New";
				}
				$action_params['add_new'] = array(
					'label'			=> $label,
					'url'			=> $url,
					'icon_class'	=> 'fa-plus-circle',
					'class'			=> 'add_new'
				);
			}

			if (isset($value['export'])) {
				if (is_array($value['export'])) {
					foreach ($value['export'] as $key=>$val) {
						$url = $key;
						$label = $val;
					}
				} else {
					$url = $value['export'];
					$label = "Export";
				}
				$action_params['export'] = array(
					'label'			=> $label,
					'url'			=> $url,
					'icon_class'	=> 'fa-cloud-download',
					'class'			=> 'export'
				);

			}

			if (isset($value['export_all'])) {
				if (is_array($value['export_all'])) {
					foreach ($value['export_all'] as $key=>$val) {
						$url = $key;
						$label = $val;
					}
				} else {
					$url = $value['export_all'];
					$label = "Export All";
				}
				$action_params['export_all'] = array(
					'label'			=> $label,
					'url'			=> $url,
					'icon_class'	=> 'fa-plus-circle',
					'class'			=> 'export_all'
				);
			}

			if (isset($value['import'])) {
				if (is_array($value['import'])) {
					foreach ($value['import'] as $key=>$val) {
						$url = $key;
						$label = $val;
					}
				} else {
					$url = $value['import'];
					$label = "Import";
				}
				$action_params['import'] = array(
					'label'			=> $label,
					'url'			=> $url,
					'icon_class'	=> 'fa-cloud-upload',
					'class'			=> 'import'
				);
			}

		endforeach;

		$output = "<div class=\"actions\">";
		foreach ($action_params as $value) {
			$output .= "<a href=\"".$value['url']."\" ";
			$output .= "class=\"".$value['class']."\">";
			$output .= $value['label'];

			$output .= "<i class=\"fa ".$value['icon_class']."\"></i>";

			$output .= "</a>";
		}
		$output .= "</div>";

		return $output;
	}

	private function build_header (array $table_header) 
	{

		$header = "<thead><tr>";
		
		foreach ($table_header as $label) {
			if (isset($label['label']) && $label['label'] !="") {
				$header .= "<th ";

				// if the variables are not set then we want defaults
				if (!isset($label['data_attr'])) {
					$label['data_attr'] = array(
						'label' => 'sortable',
						'value' => 'true'
					);
				}

				$header .= $this->set_data_attributes($label['data_attr']);

				$header .= ">";
				$header .= $label['label'];
				$header .= "</th>";
			} else {
				// if no label dont make it sortable
				$header .= "<th ";
				$label['data_attr'] = array(
					'label' => 'sortable',
					'value' => 'false'
				);
				$header .= $this->set_data_attributes($label['data_attr']);
				$header .= ">";
				$header .= "</th>";
			}

		}

		$header .= "</tr></thead>";

		return $header;
	}

	private function build_body (array $rows) 
	{

		$body = "<tbody>";

		// entire row
		foreach ($rows as $row) {

			$body .= "<tr>";

			// specific column values
			foreach ($row as $column) {

				if (isset($column['label'])) {
					$body .= "<td ";

					if (!empty($column['data_attr'])) {
						$body .= $this->set_data_attributes($column['data_attr']);
					}

					$body .= ">";
					$body .= $column['label'];
					$body .= "</td>";
				}
			}

			$body .= "</tr>";
		}

		$body .= "</tbody>";


		return $body;
	}

	private function build_footer (array $table_footer) 
	{
		// this is not currently used but may be needed in the future
		$footer = "<tfoot>";

		// entire row
		foreach ($table_footer as $key => $value) {
			$footer .= "<tr>";
				$footer .= "<td>".$key."</td>";

				foreach($value as $item)
				{
					$footer .= "<td>".$item."</td>";
				}
			$footer .= "</tr>";
		}

		$footer .= "</tfoot>";

		return $footer;
	}

	/**
	 * returns a string of DOM elements that represents a single table cell
	 * spanning all the columns of the table with the $content as it's content
	 * 
	 * @param  array  $table_header 
	 * @param  string $content      
	 * @return string 
	 */
	private function build_no_content_message(array $table_header, $content)
	{
		if(empty($table_header))
		{
			return "";
		}

		return "<tr><td colspan=\"".count($table_header)."\" class=\"no-rows\">".$content."</td></tr>";
	}

	private function set_parameters($params)
	{

		/**
		 * @defaults
		 * id 				id for the section
		 * headline			headline above the table
		 * table_class		class that is on the table
		 * table_data_attr  array of data attributes for the table
		 * table_header		array of header values with data_attr
		 * table_rows		array of rows values with data_attr
		 * no_rows_content  string that will be displayed in single cell that 
		 * 					spans the width of the table should there be no rows
		 *
		 * @dafaults['data_attr']
		 * label
		 * value
		 */
		

		$defaults = array(
			'id'			=> '',
			'headline'		=> '',
			'table_class'	=> '',
			'table_header'	=> array(
				'label'		=> '',
				'data_attr'	=> array(
					'label'	=> 'sortable',
					'value' => 'true'
					),
				'edit'		=> false,
				),
			'table_rows'	=> array(
				'label'		=>'',
				'data_attr'	=> array(
					'label'	=> '',
					'value' => ''
					),
				'edit'		=> '',
				),
			'table_footer'	=> array(),
			'data_attr'		=> array(),
			'no_rows_content' => ''
			);

		return array_merge($defaults, $params);
	}

	/**
	 * creates dom for data attributes
	 * 
	 * @param array $attributes 	takes array with labels and values
	 * @return html         		Returns DOM HTML
	 */
	private function set_data_attributes ($attr) 
	{
		$data_attr = "";

		// if they are set but are set to blank then we want no
		if ($attr['label'] != '') {
			$data_attr .= "data-".$attr['label']."=\"".$attr['value']."\" ";
		}

		return $data_attr;
	}

	public function edit_column ($params) 
	{
		$values = array(
			'label'		=> "edit",
			'class'		=> "edit",
			'url'		=> $params['url'],
			'id'		=> $params['id']
			);

		$output = $this->create_column($values);
		return $output;

	}


	public function inactivate_column ($params) 
	{

        /**
         * client preferred 'Deactivate' to 'Inactivate'
         */
		$values = array(
			'label'		=> "Deactivate",
			'class'		=> "inactivate",
			'url'		=> $params['url'],
			'data_attr' => array(
				'label'	=> 'name',
				'value'	=> $params['name']
				),
			'id'		=> $params['id']
			);

		$output = $this->create_column($values);
		return $output;
	}

	public function activate_column ($params) 
	{
		$values = array(
			'label'		=> "Activate",
			'class'		=> "activate",
			'url'		=> $params['url'],
			'data_attr' => array(
				'label'	=> 'name',
				'value'	=> $params['name']
			),
			'id'		=> $params['id']
		);

		$output = $this->create_column($values);
		return $output;
	}

	public function delete_column ($params) 
	{
		$values = array(
			'label'		=> "delete",
			'class'		=> "delete",
			'url'		=> $params['url'],
			'data_attr' => array(
				'label'	=> 'name',
				'value'	=> $params['name']
				),
			'id'		=> $params['id']
			);

		$output = $this->create_column($values);
		return $output;
	}

	public function view_column ($params) 
	{
		$values = array(
			'label'		=> "view",
			'class'		=> "view",
			'url'		=> $params['url'],
			'id'		=> $params['id']
			);

		$output = $this->create_column($values);
		return $output;
	}

	private function create_column ($params) 
	{
		if(substr($params['url'], -1) != "/")
		{
			$params['url'] .= "/";
		}
		$output = "<a href=\"".$params['url'].$params['id']."\"";

		if (isset($params['data_attr'])) {
			$output .= $this->set_data_attributes($params['data_attr']);
			$output .= $this->set_data_attributes(array('label'=>'id','value'=>$params['id']));
		}
		$output .= "class=\"".$params['class']."\"";;
		$output .= ">";
		$output .= $params['label'];
		$output .= "</a>";

		return $output;
	}
}
?>