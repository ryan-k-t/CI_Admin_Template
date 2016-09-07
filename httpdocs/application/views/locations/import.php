<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->view('_blocks/header');
?>

<div id="container" data-module-name="<?= $module; ?>">

	<div class="alert inactive" role="alert"></div>
	<a class = "back-link" href="/<?= $module; ?>/">Back to table view</a>
	<h2 class="page-title"><?= $display_name; ?> : Import</h2>
	<div class="row">
	
		<div class="col-md-3">
			<form id="module-form" method="post" class="editor" action="/<?=$module?>/upload">

			<?
			echo $this->form_builder->dropdown(array(
				'label'    => 'Facility', 
				'name'     => 'facility_id', 
				'value'    => $s_facility_id, 
				'required' => true, 
				'id'       => 'facility_id', 
				'options'  => $facilities
			)); 
			?>
			<div class="field">
				<label for="">Facility ID for Column Facility_ID: </label>
				<span id="facility_output">
					<?
					if ($s_facility_id != "") {
						echo $s_facility_id;
					}
					?>
				</span>
			</div>
				<?= $this->form_builder->input(array(
					'label' => 'Upload Import File', 
					'name'  => 'import_file',
					'type'  => 'file',
					'id'    => $module.'_import_file',
					'accept' => '.csv,text/csv'
				)); ?>
				<?= $this->form_builder->button(array('type' => 'submit', 'value' => "Upload", "class" => "upload disabled")); ?>
			</form>
			<p><i class="fa fa-file-text-o" style="margin-right:.25rem;"></i><a href="/assets/docs/<?=$sample_doc?>">View a sample file which will display how the file should be laid out</a></p>
		</div>

		<div class="col-md-8 col-md-offset-1">
			<div class="form"><span class="label">Data to be Uploaded</span></div>
			<div id="upload-status">
			</div>
			<span class="note">NOTE: Data will not be uploaded until you click on the "UPLOAD" button. The first row of data will be ignored as that is designated to be the column headings</span>

			<div id="upload-results"></div>
		</div>

	</div>

</div>

<script src="/assets/js/wedu/cms_module.js"></script>
<script src="/assets/js/wedu/import.js"></script>
<script src="/assets/js/wedu/location_import.js"></script>


<? $this->view('_blocks/footer'); ?>	