<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->view('_blocks/header');

/* making sure view args are set -- otherwise it throws errors */
if(!isset($record)) 
{
	$record = new Location();
}

if(!$record->id)
{
	$create_mode = TRUE;

	$button_text = "Create";
	$title_text = "Add New";
}
else
{
	$create_mode = FALSE;

	$button_text = "Update";
	$title_text = $record->name;
}

//$facilities = array_merge(array(""=>"Select an Item"), $facilities);

$module_name = "Locations";
?>
<div id="container" data-module-name="<?= strtolower($module_name); ?>">

	<div class="alert inactive" role="alert"></div>
	<? $this->load->view('_blocks/back_to_table', array('url' => '/locations')); ?>
	<h2 class="page-title"><?= $module_name; ?> Manager : <?= $title_text; ?></h2>
	<div class="row">
		
		<div class="col-md-8">
			<form id="module-form" method="post" class="editor">
				<div class="row">
					<div class="col-md-5">
						<?
						echo $this->form_builder->input(array(
							'name'  => 'id', 
							'value' => $record->id, 
							'type'  => 'hidden', 
						));
						echo $this->form_builder->dropdown(array(
							'label'    => 'Facility', 
							'name'     => 'facility_id', 
							'value'    => $record->facility_id, 
							'required' => TRUE, 
							'id'       => 'facility_id', 
							'options'  => $facilities
						));
						echo $this->form_builder->input(array(
							'label'    => 'Name', 
							'name'     => 'name', 
							'value'    => $record->name, 
							'required' =>  TRUE
						));
						echo $this->form_builder->textarea(array(
							'label'    => 'Address', 
							'name'     => 'address', 
							'value'    => $record->address, 
							'required' => TRUE
						));
						echo $this->form_builder->input(array(
							'label'    => 'Room Number', 
							'name'     => 'room_number', 
							'value'    => $record->room_number, 
						));
						echo $this->form_builder->radio(array(
							'label'    => 'Active', 
							'name'     => 'active', 
							'value'    => ($create_mode ? $this->locations_model->get_field_default_value('active') : $record->active), 
							'required' => TRUE, 
							'id'       => 'active', 
							'options'  => $this->locations_model->get_field_enum_values('active')
						));
						?>
					</div>

					<div class="col-md-5 col-md-offset-2">
					</div>
				</div>

				<div class="row">
					<div class="col-md-12">
						<?= $this->form_builder->button(array('type' => 'submit', 'value' => $button_text, "class" => "save")); ?>
					</div>
				</div>
			</form>
		</div>

		<div class="col-md-3 col-md-offset-1">
			<? $this->load->view('_blocks/comments', array('id' => $create_mode ? "" : $record->id, 'table' => 'locations')); ?>
		</div>

	</div>

</div>

<script src="/assets/js/core/cms_module.js"></script>


<? $this->view('_blocks/footer'); ?>	