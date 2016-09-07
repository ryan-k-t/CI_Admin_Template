<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->view('_blocks/header'); ?>

<div id="container">
	<div id="body">
		<form action="">
		<?
		echo $this->form_builder->published('no');
		// echo $this->form_builder->published('yes');
		
		echo $this->form_builder->country_dropdown(
			array(
				'label' => 'Student Country',
				'name' => 'student_country',
				'id' => 'customer_country',
				'required' => TRUE
			)
		);


		echo $this->form_builder->state_dropdown(
			array(
				'label' => 'State',
				'name' => 'student_state',
				'id' => 'user_state',
				'required' => TRUE,
			)
		);

		echo $this->form_builder->dropdown(
			array(
				'label' => 'Schools',
				'name' => 'school_id',
				'required' => TRUE,
				'options' => $facilities,
				'id' => 'dropdown'
			)
		);


		echo $this->form_builder->dropdown(
			array(
				'label' => 'Schools Multiple',
				'name' => 'schools_multiple',
				'required' => TRUE,
				'options' => $facilities,
				'id' => 'multi_dropdown',
				'multiple' => TRUE
			)
		);

		echo $this->form_builder->input(
			array(
				'label' => 'input field',
				'name' => 'name',
				'placeholder' => 'placeholder goes here',
				'required' => TRUE
			)
		);

		echo $this->form_builder->input(
			array(
				'label' => 'input field',
				'name' => 'name',
				'value' => 'value',
			)
		);

		echo $this->form_builder->textarea(
			array(
				'label' => 'I am a textarea',
				'name' => 'ta',
				'id' => 'ID-ID',
				'placeholder' => "Put text in me"
			)
		);

		echo $this->form_builder->radio(
			array(
				'label' => 'Radio Button',
				'name' => 'radio_name',
				'options' => $facilities,
				'value' => 1
			)
		);

		echo $this->form_builder->checkbox(
			array(
				'label' => 'School Checkbox',
				'name' => 'facility',
				'options' => $facilities,
				'value' => array(1,2),
				'required' => TRUE
			)
		);

		$date = date('d-m-Y');

		echo $this->form_builder->date_picker(
			array(
				'label' => 'Contact Date',
				'name' => 'contact_start_date',
				'id' => 'contact_start_date',
				'required' => TRUE,
				'value' => $date
			)
		);

		echo $this->form_builder->submit_button();
		?>
		</form>

	</div>
</div>
<? $this->view('_blocks/footer'); ?>