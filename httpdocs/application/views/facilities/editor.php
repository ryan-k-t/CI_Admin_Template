<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->view('_blocks/header');

/* making sure view args are set -- otherwise it throws errors */
if (!isset($record)) {
	$record = new Facility();
}

if (!$record->id) {
	$create_mode = TRUE;

	$button_text = "Create";
	$title_text = "Add New";
	$custom_fields = array();
} else {
	$create_mode = FALSE;

	$button_text = "Update";
	$title_text = $record->display_name;
}

?>

<div id="container" data-module-name="facilities">

	<div class="alert inactive" role="alert"></div>
	<? $this->load->view('_blocks/back_to_table', array('url' => '/facilities')); ?>
	<h2 class="page-title">Facilities Manager : <?= $title_text; ?></h2>
	<div class="row">
		
		<div class="col-md-8">
			<form id="module-form" method="post" class="editor">
				<div class="row">
					<div class="col-md-5">
						<?
						echo $this->form_builder->input(array(
							'name' => 'id', 
							'value' => ($create_mode ? '' : $record->id), 
							'type' => 'hidden', 
						));
						echo $this->form_builder->dropdown(array(
							'label'    => 'Type', 
							'name'     => 'facility_type_id', 
							'value'    => ($create_mode ? '' : $record->facility_type_id), 
							'required' => TRUE, 
							'id'       => 'facility_type_id', 
							'options'  => $facility_types
						));
						echo $this->form_builder->input(array(
							'label'    => 'Display Name', 
							'name' => 'display_name', 
							'value' => ($create_mode ? '' : $record->display_name), 
							'placeholder' => 'The Company Name', 
							'required' =>  TRUE
						));
						echo $this->form_builder->input(array(
							'label'    => 'Select Name', 
							'name' => 'select_name', 
							'value' => ($create_mode ? '' : $record->select_name), 
							'placeholder' => 'Company Name, The', 
							'required' => TRUE
						));

						echo $this->form_builder->radio(array(
							'label'    => 'Active', 
							'name' => 'active', 
							'value' => ($create_mode ? $this->facilities_model->get_field_default_value('active') : $record->active), 
							'required' => TRUE, 
							'id' => 'active', 
							'options' => $this->facilities_model->get_field_enum_values('active')
						));
						?>
					</div>

					<div class="col-md-5 col-md-offset-2">
					</div>
				</div>

				<div class="row">
					<div class="col-md-12">
						<?= $this->form_builder->button(array(
							'type' => 'submit', 
							'value' => $button_text, 
							"class" => "save"
						)); ?>
					</div>
				</div>
			</form>
		</div>

		<div class="col-md-3 col-md-offset-1">
			<? if (!$create_mode) { ?>
				<section>
					<h5>Locations</h5>
					<? if(count($locations) > 0) { ?>
                        <ul class="non-bulleted">
                            <? foreach($locations as $location){ ?>
                                <li><a href="/locations/edit/<?= $location->id; ?>"><?= $location->name; ?></a></li>
                            <? } ?>
                        </ul>
                        <p><a href="/locations/create?facility_id=<?= $record->id; ?>">Create a new location <i class="fa fa-angle-double-right"></i></a></p>
					<? } else { ?>
                        <p><i>There are currently no locations for this facility. <a href="/locations/create?facility_id=<?= $record->id; ?>">Create a location</a>.</i></p>
					<? } ?>
					<div class="form">
						<a href="/locations/import/<?=$record->id?>" class="btn" target="_blank">Import Locations</a>		
					</div>
				</section>
			<? } ?>

			<? $this->load->view('_blocks/comments', array('id' => $record->id, 'table' => 'facilities')); ?>
		</div>

	</div>

</div>

<script src="/assets/js/core/cms_module.js"></script>


<? $this->view('_blocks/footer'); ?>	