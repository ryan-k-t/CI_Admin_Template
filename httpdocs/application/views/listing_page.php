<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->view('_blocks/header');
?>

<div id="container" data-module-name="<?= $module_name; ?>">

	<div class="alert inactive" role="alert"></div>

	<? echo isset($quick_link) ? $quick_link : "";

	if (isset($table)) : ?>
		<section class="listing">
		<?= $table;	?>
		</section>

	<? endif; ?>
</div>

<script src="/assets/js/core/cms_module.js"></script>

<? $this->view('_blocks/footer'); ?>
