<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Form_builder 
{
	private $_CI;

	function __construct()
	{
		if (is_cli())
		{
			log_message('debug', __CLASS__.': Initialization under CLI aborted.');
			return;
		}
		$this->_CI =& get_instance();
		log_message('DEBUG', __CLASS__.' library initialized');
	}


	/**
	 * generates an input field
	 * 
	 * @param  array $params an associative array with desired parameters
	 *                       for available parameters see _set_parameters function below
	 *                       
	 * @return html         Returns DOM HTML
	 */
	public function input($params)
	{
		$parameters = $this->_set_parameters($params);

		/**
		 * set the default type
		 */
		if (!isset($parameters['type']) || empty($parameters['type'])) {
			$parameters['type'] = 'text';
		}
		/**
		 * make sure the type and hidden parameters are in sync
		 */
		if(isset($parameters['hidden']) && $parameters['hidden'] == TRUE)
		{
			unset($parameters['hidden']);
			$parameters['type'] = "hidden";
		}

		$attributes = $this->_get_attributes($parameters);
		$attributes_string = $this->_get_attributes_string($attributes);

		$output = "";
		if ($parameters['type'] != "hidden") {
			$output .= "
				<div class=\"field field-input".(strlen($parameters['field_class']) > 0 ? " ".$parameters['field_class'] : "")."\">";
			$output .= "<span class=\"label\">".$parameters['label'].($parameters['required'] ? "<span class=\"cRed\">*</span>" : "").($parameters['comment'] ? "<i class=\"fa fa-info-circle\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"".$parameters['comment']."\"></i>" : "")."</span>";
		}
		$output .= "
			<input".(strlen($attributes_string) > 0 ? " ".$attributes_string : "")." />";
		if ($parameters['type'] != "hidden") {
			if(!empty($parameters['after_html']))
			{
				$output .= "
				".$parameters['after_html'];
			}
			$output .= "
				</div>";
		}

		return $output;
	}

	/**
	 * generates a textarea field
	 * 
	 * @param  array $params an associative array with desired parameters
	 *                       for available parameters see _set_parameters function below
	 *                       
	 * @return html         Returns DOM HTML
	 */
	public function textarea($params)
	{
		$parameters = $this->_set_parameters($params);

		unset($parameters['type']);
		/**
		 * HTML textareas don't need a value attribute
		 */
		$value = $parameters['value'];
		unset($parameters['value']);

		$attributes = $this->_get_attributes($parameters);
		$attributes_string = $this->_get_attributes_string($attributes);
		$output = "
			<div class=\"field field-textarea".(strlen($parameters['field_class']) > 0 ? " ".$parameters['field_class'] : "")."\">
				<span class=\"label\">".$parameters['label'].($parameters['required'] ? "<span class=\"cRed\">*</span>" : "").($parameters['comment'] ? "<i class=\"fa fa-info-circle\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"".$parameters['comment']."\"></i>" : "")."</span>
				<textarea".(strlen($attributes_string) > 0 ? " ".$attributes_string : "").">".$value."</textarea>";
		if(!empty($parameters['after_html']))
		{
			$output .= "
			".$parameters['after_html'];
		}
		$output .= "
			</div>";
		return $output;
	}

	/**
	 * generates an input field that's hooked into a datepicker
	 * 
	 * @param  array $params an associative array with desired parameters 
	 *                       for available parameters see _set_parameters function below
	 *                       
	 * @return html         Returns DOM HTML
	 */
	public function date_picker($params)
	{
		$parameters = $this->_set_parameters($params);

		/**
		 * this won't need a type attribute
		 */
		unset($parameters['type']);
		/**
		 * this won't use a value attribute
		 */
		$value = $parameters['value'];
		unset($parameters['value']);

		$id = $parameters['id'];
		unset($parameters['id']);

		if (isset($parameters['class']) && !empty($parameters['class'])) {
			$parameters['class'] = $parameters['class'].' form-control';
		} else {
			$parameters['class'] = "form-control";
		}

		$attributes = $this->_get_attributes($parameters);
		$attributes_string = $this->_get_attributes_string($attributes);
		$output = "
			<div class=\"form-group field".(strlen($parameters['field_class']) > 0 ? " ".$parameters['field_class'] : "")."\">
				<span class=\"label\">".$parameters['label'].($parameters['required'] ? "<span class=\"cRed\">*</span>" : "").($parameters['comment'] ? "<i class=\"fa fa-info-circle\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"".$parameters['comment']."\"></i>" : "")."</span>
				<div class=\"input-group date\" id=\"".$id."\">
					<input".(strlen($attributes_string) > 0 ? " ".$attributes_string : "")." />
                    <span class=\"input-group-addon\">
                        <span class=\"glyphicon glyphicon-calendar\"></span>
                    </span>
				</div>";
		if(!empty($parameters['after_html']))
		{
			$output .= "
			".$parameters['after_html'];
		}
		$output .= "
						</div>
		<script type=\"text/javascript\">
			$(document).ready(function() {
                $('#".$id."').datepicker();";

        if ($value) { 
        	$output .= "$('#".$id."').datepicker('setDate', '".$value."');";
        }
	                
        $output .= "
	            });
	        </script>
			";
		return $output;
	}

	/**
	 * generates an input field that's hooked into a datepicker
	 * 
	 * @param  array $params an associative array with desired parameters 
	 *                       for available parameters see _set_parameters function below
	 *                       
	 * @return html         Returns DOM HTML
	 */
	public function multi_date_picker($params)
	{
		$parameters = $this->_set_parameters($params);

		/**
		 * this won't need a type attribute
		 */
		unset($parameters['type']);
		/**
		 * this won't use a value attribute
		 */
		$value = $parameters['value'];
		unset($parameters['value']);

		$id = $parameters['id'];
		unset($parameters['id']);

		if (isset($parameters['class']) && !empty($parameters['class'])) {
			$parameters['class'] = $parameters['class'].' form-control';
		} else {
			$parameters['class'] = "form-control";
		}

		$attributes = $this->_get_attributes($parameters);
		$attributes_string = $this->_get_attributes_string($attributes);
		
		$output = "";
		$count = 0;
		$array_count = count($parameters['options']);

		$output .= "
		<script src='/assets/js/core/multi_date.js'></script>
		<div class='multi-date-group field'>";
		if($parameters['label'])
		{
			$output .= "<span class=\"label\">".$parameters['label'].($parameters['required'] ? "<span class=\"cRed\">*</span>" : "").($parameters['comment'] ? "<i class=\"fa fa-info-circle\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"".$parameters['comment']."\"></i>" : "")."</span>";
		}

		//Loop through and print out the individual date pickers
		foreach($parameters['options'] as $key=>$val)
		{
			//For every date lets up the count
			$count++;

			$output .= "
			<div class=\"form-group field multi-date".(strlen($parameters['field_class']) > 0 ? " ".$parameters['field_class'] : "")."\">
				<div class=\"input-group date ".($count != 1 ? ' additional' : '')."\" id=\"".$key."\">
					<input name='multi-date-".rand(1000,100000)."' max_length='0' class='form-control' placeholder='' dates='' ".($parameters['required'] ? 'required="required"' : '')."/>
                    <span class=\"input-group-addon\">
                        <span class=\"glyphicon glyphicon-calendar\"></span>
                        <span class=\"glyphicon glyphicon-remove\"></span>
                    </span>
				</div>";
			$output .= "</div>
			<script type=\"text/javascript\">
				$(document).ready(function() {
	                $('#".$key."').datepicker();";

	        if ($val) { 
	        	$output .= "$('#".$key."').datepicker('setDate', '".$val."');";
	        }
		                
	        $output .= "
		            });
		        </script>
				";
		}
		$output .= "<span class='date-add-more btn'>Add More</span>";

		if(!empty($parameters['after_html']))
		{
			$output .= "
			".$parameters['after_html'];
		}
		$output .= "</div>";

		return $output;
	}


	/**
	 * generates structure for radio buttons / checkboxes
	 * since the output is predominantly the same this function handles it all
	 * 
	 * @param  string $type   radio or checkbox
	 * @param  array $params  an associative array with desired parameters 
	 *                        for available parameters see _set_parameters function below
	 *                        
	 * @return html         Returns DOM HTML
	 */
	private function _generate_toggle_field($type, $params)
	{
		/* force the type to an accepted type */
		$type = strtolower($type);
		if ($type != "radio" && $type != "checkbox") {
			$type = "radio";
		}

		$parameters = $this->_set_parameters($params);
		$output = "
			<div class=\"field field-".$type.(strlen($parameters['field_class']) > 0 ? " ".$parameters['field_class'] : "")."\">
				<span class=\"label\">".$parameters['label'].($parameters['required'] ? "<span class=\"cRed\">*</span>" : "").($parameters['comment'] ? "<i class=\"fa fa-info-circle\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"".$parameters['comment']."\"></i>" : "")."</span>";

		/**
		 * make sure the value for checkboxes is an array
		 */
		if (!is_array($parameters['value'])) {
			$parameters['value'] = array($parameters['value']);
		}

		$counter = 0;
		foreach ($parameters['options'] as $option_value => $option_label) {
			$counter++;

			$id = $parameters['name']."-".$counter;
			$radio_parameters = array(
				'id'       => $id,
				'name'     => $parameters['name'],
				'type'     => $type,
				'value'    => $option_value,
				'required' => $parameters['required'],
				'class'    => strlen($parameters['class']) > 0 ? $parameters['class'].' radio-select' : 'radio-select',
				'disabled' => $parameters['disabled'],
				'checked'  => in_array($option_value, $parameters['value'])
			);
			$attributes = $this->_get_attributes($radio_parameters);
			$attributes_string = $this->_get_attributes_string($attributes);

			$output .= "
				<input".(strlen($attributes_string) > 0 ? " ".$attributes_string : "")." />
				<label for=\"".$id."\">
					<span><span></span></span>
					".$option_label."
				</label>";
		}

		if(!empty($parameters['after_html']))
		{
			$output .= "
			".$parameters['after_html'];
		}
		$output .= "</div>";

		return $output;
	}

	/**
	 * generates an input field that's hooked into a datepicker
	 * 
	 * @param  array $params an associative array with desired parameters 
	 *                       for available parameters see _set_parameters function below
	 *                       
	 * @return html         Returns DOM HTML
	 */
	public function radio($params)
	{
		return $this->_generate_toggle_field('radio', $params);
	}

	/**
	 * A short-cut function to generate radio buttons for published
	 * 
	 * @param  string $value Yes or No
	 * @return HTML          Returns DOM HTML
	 */
	public function published($value)
	{
		$params = array(
			'field_class' => 'field-published',
			'label'       => 'Published',
			'required'    => TRUE,
			'name'        => 'published',
			'id'          => 'published',
			'value'       => $value,
			'options'     => array(
				'yes' => 'yes',
				'no'  => 'no'
			),
		);
		return $this->_generate_toggle_field('radio', $params);
	}

	/**
	 * This is for putting in data set of checkboxes
	 * 
	 * @param  array $params an associative array with desired parameters 
	 *                       for available parameters see _set_parameters function below
	 *                       
	 * @return HTML          Returns DOM HTML
	 */
	public function checkbox($params)
	{
		return $this->_generate_toggle_field('checkbox', $params);
	}

	/**
	 * This is called when you need a button
	 * @param  array $params an associative array with desired parameters 
	 *                       for available parameters see _set_parameters function below
	 *                       
	 * @return html         Returns DOM HTML
	 */
	public function button($params)
	{
		$parameters = $this->_set_parameters($params);
		
		/**
		 * HTML buttons don't need a value attribute
		 */
		$value = $parameters['value'];
		unset($parameters['value']);

		$attributes = $this->_get_attributes($parameters);
		$attributes_string = $this->_get_attributes_string($attributes);
		$output = "
			<div class=\"field button".(strlen($parameters['field_class']) > 0 ? " ".$parameters['field_class'] : "")."\">
				<button".(strlen($attributes_string) > 0 ? " ".$attributes_string : "").">{$value}</button>
			</div>";
		return $output;
	}

	/**
	 * A simple call to create a basic submit button
	 * @return html         Returns DOM HTML
	 */
	public function submit_button()
	{
		return $this->button(array(
			'type'  => 'submit',
			'value' => 'Submit'
		));
	}
	
	/**
	 * Generates a dropdown
	 * 
	 * @param  array $params Uses base params plus allows for multiple as a boolean
	 * @return html          Returns DOM HTML
	 */
	public function dropdown($params)
	{
		$parameters = $this->_set_parameters($params);
		$attributes = $this->_get_attributes($parameters);
		$unneeded_general_attributes = array('type','max_length','placeholder');
		foreach($unneeded_general_attributes as $unneeded)
		{
			if(array_key_exists($unneeded, $attributes))
			{
				unset($attributes[$unneeded]);
			}
		}
		$attributes_string = $this->_get_attributes_string($attributes);
		if ($parameters['type'] != "hidden") {
			
			$output = "

				<div class=\"field dropdown".(strlen($parameters['field_class']) > 0 ? " ".$parameters['field_class'] : "")."\">
					<span class=\"label\">".$parameters['label'].($parameters['required'] ? "<span class=\"cRed\">*</span>" : "").($parameters['comment'] ? "<i class=\"fa fa-info-circle\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"".$parameters['comment']."\"></i>" : "")."</span>
					<select".(strlen($attributes_string) > 0 ? " ".$attributes_string : "").">
						<option value=\"\">".$parameters['first_option_label']."</option>";

			if (!is_array($parameters['options'])) {
				$parameters['options'] = array($parameters['options']);
			}

			foreach ($parameters['options'] as $option_value => $option_label) {
				$output .= "<option".($parameters['value'] == $option_value ? " selected" : "")." value=\"{$option_value}\">{$option_label}</option>";
			}
			$output .= "
					</select>";
			if(!empty($parameters['after_html']))
			{
				$output .= "
				".$parameters['after_html'];
			}
			$output .= "
				</div>";
			$output .= "<script type=\"text/javascript\">\n";
			$output .= "$(document).ready(function() {\n";
			
			$output .= "$(\"#".$parameters['id']."\").select2();\n";
			
			$output .= "});\n";
			$output .= "</script>\n";
		} else {
			$output = "<input".(strlen($attributes_string) > 0 ? " ".$attributes_string : "")." value='".$parameters['value']."'/>";
		}
		return $output;
	}

	public function multiple_select ($params) 
	{

		// example here https://jsfiddle.net/ivan_sim/2ftk5c8z/

		$parameters = $this->_set_parameters($params);
		$attributes = $this->_get_attributes($parameters);
		$attributes_string = $this->_get_attributes_string($attributes);

		if (!is_array($parameters['options'])) {
			$parameters['options'] = array($parameters['options']);
		}

		$selections = "";
		foreach ($parameters['options'] as $option_value => $option_label) {
			$selections .= "{ id: \"".$option_value."\", text: \"".$option_label."\"},\n";
		}
		$selected = "";

		// this requires a little clean up to work for all associated arrays
		foreach ($parameters['values'] as $value) {
			foreach ($value as $the_id) {
				$selected .= $the_id."," ;
			}
			
		}
		$selected = trim($selected, ",");

		
		$output = "<span class='label'>".$parameters['label'];

		if ($parameters['required']) {
			$output .= "<span class='cRed'>*</span>".($parameters['comment'] ? "<i class=\"fa fa-info-circle\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"".$parameters['comment']."\"></i>" : "");	
		}
		
		$output .= "</span>";


		$output = "<input id='".$parameters['id']."' value='".$selected."'";
		$output .= "name = '".$parameters['name']."'";
		$output .= "/>\n";

		$output .= "<script type=\"text/javascript\">\n";
			$output .= "$(document).ready(function() {\n";
				$output .= "selections_".$parameters['id']."=[".$selections."];\n";

		$output .= "
		var extract_preselected_ids = function(element){
        var preselected_ids = [];
        if(element.val())
	            $(element.val().split(\",\")).each(function () {
	                preselected_ids.push({id: this});
	            });
	        
	        return preselected_ids;
    	};
    
	    var preselect = function(preselected_ids){
	        var pre_selections = [];
	        for(index in selections_".$parameters['id'].")
	            for(id_index in preselected_ids)
	                if (selections_".$parameters['id']."[index].id == preselected_ids[id_index].id)
	                    pre_selections.push(selections_".$parameters['id']."[index]);
	        return pre_selections;
	    };";
		
		$output .= "
		$('#".$parameters['id']."').select2({
	        placeholder: 'Select Plans',
	        minimumInputLength: 0,
	        multiple: true,
	        allowClear: true,
	        data: function(){
	            return {results: selections_".$parameters['id']."}
	        },  
	        initSelection: function(element, callback){
	            var preselected_ids = extract_preselected_ids(element);
	            var preselections = preselect(preselected_ids);
	            callback(preselections);
	        }
    	});";


			$output .= "});\n";
		$output .= "</script>\n";

		return $output;
		
	}

	/**
	 * Generates a dropdown with the US states as options
	 * 
	 * @param  array $params 
	 * @return html         
	 */
	public function state_dropdown($params)
	{
		$params['options'] = $this->_CI->config->item('us_states');

		if (!isset($params['value']) || empty($params['value'])) {
			$params['value'] = 'NH';
		}
		return $this->dropdown($params);
	}

	/**
	 * Generates a dropdown with countries as options
	 * 
	 * @param  array $params 
	 * @return html         
	 */
	public function country_dropdown($params)
	{
		$params['options'] = $this->_CI->config->item('countries');
		if (!isset($params['value']) || empty($params['value'])) {
			$params['value'] = 'United States';
		}
		return $this->dropdown($params);
	}
	




	/**
	 * set the base parameters for all of our form elements
	 * 
	 * @param   array $params the parameters as set by the deve into the function call
	 * @return	array
	 */
	private function _set_parameters($params)
	{
		/**
		 * id:	 		ID attribute of the field. This value will be auto generated
		 * 				if not provided. Set to FALSE if you don't want an ID value
		 * name: 		Name attribute of the field
		 * type:		Type attribute of the field (text, select, password, etc.)
		 * max_length: 	Maxlength parameter to associate with the field
		 * comment: 	Comment to assicate with the field's label'
		 * label:		Label to associate with the field
		 * required:	Puts a required flag next to field label
		 * class:       the CSS class attribute to associate with the form element
		 * field_class: the CSS class attribute to associate with the field element
		 * value:       the value of the field
		 * disabled:    sets disabled attribute on the field
		 * placeholder: the placeholder value
		 * options:   	list of options -- used for enums, radio buttons, selects
		 * 				example : array('yes'=>1, 'no'=>2)
		 * first_option_label: what will be displayed on a dropdown as the initial/empty value's label
		 * 				
		 * data:		data attributes
		 * after_html:  HTML DOM elements to be displayed after the form field
		 */
		$defaults = array(
			'id'                 => '',
			'name'               => '',
			'type'               => '',  
			'max_length'         => 0,
			'comment'            => '',
			'label'              => '',
			'required'           => FALSE,
			'class'              => '',
			'field_class'        => '',
			'value'              => '',
			'disabled'           => FALSE,
			'readonly'			 => FALSE,
			'placeholder'        => '',
			'options'            => array(),
			'first_option_label' => 'Select an Item',
			'data'               => array(),
			'after_html'         => ''
		);

		return array_merge($defaults, $params);
	}

	/**
	 * [_get_attributes description]
	 * @param  array $params the parameters for an element
	 * @return array         an associative array of attributes for a DOM element
	 */
	private function _get_attributes(array $params)
	{
		$ignored_params = array(
			'comment',
			'label',
			'field_class',
			'options',
			'first_option_label',
			'data',
			'values',
			'after_html'
		);

		$attributes = array();
		foreach ($params as $key=>$value) {
			if (in_array($key, $ignored_params)) {
				continue;
			}

			switch ($key) {
				case 'required':
				case 'disabled':
				case 'multiple':
				case 'checked':
				case 'readonly':
					$attr_value = $value === TRUE ? $key : NULL;
					break;
				
				default:
					$attr_value = $value;
					break;
			}

			if (!is_null($attr_value)) {
				$attributes[$key] = $attr_value;
			}
		}

		/**
		 * populate our data attributes
		 */
		if (isset($params['data']) && is_array($params['data']) && count($params['data']) > 0) {
			foreach ($params['data'] as $data_key => $data_value) {
				$attributes["data-".$data_key] = $data_value;
			}
		}

		return $attributes;
	}

	/**
	 * transforms an associative array into a parameter string
	 * such as id="x" name="y" class="z"
	 * 
	 * @param  array  $attributes [description]
	 * @return string
	 */
	private function _get_attributes_string(array $attributes)
	{
		if (count($attributes) == 0) {
			return "";
		}

		$strings = array();
		foreach ($attributes as $key=>$value) {
			if($key == "max_length" && $value == "0")
			{
				continue;
			}

			if(strlen($value))
			{
				$strings[] = $key."=\"".$value."\"";
			}
		}
		return implode(" ", $strings);
	}
}
?>
