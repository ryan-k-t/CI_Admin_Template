<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI =& get_instance();

$CI->load->view('_blocks/header');
?>
	<div id="container">
		<h1><?php echo $heading; ?></h1>
		<?php echo $message; ?>
	</div>
<? $CI->load->view('_blocks/footer'); ?>	