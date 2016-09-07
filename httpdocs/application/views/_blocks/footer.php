<footer id="footer">
	<?php 
	if (ENVIRONMENT === 'development') : ?> 
	<p id="devinfo">Page rendered in <strong>{elapsed_time}</strong> seconds. CodeIgniter Version <strong><?=CI_VERSION?></strong></p>
	<? endif; ?>
</footer>

<div class="modal fade" id="erSiteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="erSiteConfirmModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="erSiteConfirmModalLabel">Modal title</h4>
			</div>
			<div class="modal-body">
			...
			</div>
			<div class="modal-footer">
				<button type="button" class="action-negative btn btn-default">Close</button>
				<button type="button" class="action-positive btn btn-primary">Save changes</button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="erSiteMessageModal" tabindex="-1" role="dialog" aria-labelledby="erSiteMessageModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="erSiteMessageModalLabel">Modal title</h4>
			</div>
			<div class="modal-body">
			...
			</div>
			<div class="modal-footer">
				<button type="button" class="action btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<? $this->view('_blocks/html_end') ?>