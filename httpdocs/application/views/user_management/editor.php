<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$this->view('_blocks/header');

$create_mode = false;

if (!isset($user_data)) {
	
	$create_mode = true;
	$user_data = new stdClass();
	$user_data->id = '';
	$user_data->username = '';
	$user_data->first_name = '';
	$user_data->last_name = '';
	$user_data->email = '';
	$user_data->facility_id = '';
	$user_data->user_admin_type_id = '';
	$user_data->active = 'yes';
}

?>
<div id="container" data-module-name="user-management">

	<div class="alert inactive" role="alert"></div>
	<? $this->load->view('_blocks/back_to_table', array('url' => '/'.$module)); ?>
	<h2 class="page-title">User Manager : <?= $create_mode ? "Add New" : $user_data->username; ?></h2>
	<div class="row">
		
		<div class="col-md-8">
			<form id="module-form" method="post" autocomplete="off" class="editor">
				<div class="row">
					<div class="col-md-5">
						<?
						echo $this->form_builder->input(
							array(
								'hidden'	=> true,
								'name'		=> 'id',
								'value' 	=> $user_data->id,
								)
							);

						echo $this->form_builder->input(
							array(
								'required'	=> true,
								'label'		=> 'Username',
								'name'		=> 'username',
								'value' 	=> $user_data->username,
								)
							);


						echo $this->form_builder->input(
							array(
								'required'	=> true,
								'label'		=> 'First Name',
								'name'		=> 'first_name',
								'value' 	=> $user_data->first_name,
								)
							);

						echo $this->form_builder->input(
							array(
								'required'	=> true,
								'label'		=> 'Last Name',
								'name'		=> 'last_name',
								'value' 	=> $user_data->last_name,
								)
							);

						echo $this->form_builder->input(
							array(
								'required'	=> true,
								'label'		=> 'Email',
								'name'		=> 'email',
								'value' 	=> $user_data->email,
								)
							);

						echo $this->form_builder->input(
							array(
								'name'		=> 'last_modified_by',
								'value' 	=> $current_user_id,
								'hidden'	=> true
								)
							);

						 // only super admin can change access levels
						if ($super_admin) {

							echo $this->form_builder->dropdown(
								array(
									'label'    => 'Facility', 
									'name'     => 'facility_id', 
									'value'    => $user_data->facility_id, 
									'id'       => 'facility', 
									'options'  => $facilities,
									'comment'  => "Only set if you want to restrict this user to see only data for the selected facility",
									'first_option_label' => "E & R System"
									)
								);

							echo $this->form_builder->dropdown(
								array(
									'required'	=> true,
									'label'    => 'Admin Type', 
									'name'     => 'user_admin_type_id', 
									'value'    => $user_data->user_admin_type_id, 
									'id'       => 'admin_type', 
									'options'  => $user_admin_types
									)
								);
						}

						echo $this->form_builder->radio(
							array(
								'required'	=> true,
								'label'		=> 'Active',
								'name'		=> 'Active',
								'value'		=> $user_data->active,
								'options'	=> array('yes'=>'yes', 'no'=>'no')
								)
							);

							?>
							<section>
								<h3>Password</h3>
								<? if ($create_mode) : ?>
								<p>User will use the forgot your password function at the login page to generate the first password</p>
								<? else : ?>
									<? if ($current_user_id == $user_data->id) :?>
									<p>* Leave this section blank if you do not wish to update your password</p>
									<?
									$comment = "If you do not know your current password please sign out and use the forgot your password.";

									// fake field so chrome will stop trying to autofill
									echo $this->form_builder->input(
										array(
											'hidden'		=> true,
											)
										);
									echo $this->form_builder->input(
										array(
											'label'		=> 'Current Password',
											'name'		=> 'current_password',
											'type'		=> 'password',
											'comment'	=> $comment
											)
										);

									echo $this->form_builder->input(
										array(

											'label'		=> 'New Password',
											'name'		=> 'new_password',
											'type'		=> 'password',
											)
										);

									echo $this->form_builder->input(
										array(
											'label'		=> 'Confirm New Password',
											'name'		=> 'confirm_new_password',
											'type'		=> 'password',
											)
										);
									else :?>
									<p>* You cannot change another users password, deactivate them if you need to remove access.</p>
									<? endif; ?>
							<? endif ?>
						</section>
						<? echo $this->form_builder->button(
							array(
								'type'  => 'submit',
								'value' => $create_mode ? 'Create' : 'Update', 
								'class' => 'save'
								)
							);
						?>
						</div>
					</div>
				</form>
			</div>
	</div> <!-- end .row -->
</div><!-- end #container -->
<script src="/assets/js/core/cms_module.js"></script>
<? $this->view('_blocks/footer');
