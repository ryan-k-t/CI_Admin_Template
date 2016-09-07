<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->view('_blocks/html_top'); ?>

<div id="container">
	<div id="body">
		<?php $password_reset = isset($password_reset) ? $password_reset : FALSE; ?>
		<div class="login-wrapper">
			<div class="form-container">
				<form id="login-form" class="show">
					<?php if($password_reset) { ?>
					<p>Your password has been set to <input type="text" class="new-password auto-select" value="<?php echo $password_reset ?>" /><br />
						Please change it after logging in!</p>
						<?php } ?>
						<input type="hidden" name="forward_to" value="<?php echo $forward_to?>" />

						<div class="form-group">
							<label for="username-input">Username</label>
							<input id="username-input" name="username" type="text" class="form-control" />
						</div>
						<div class="form-group">
							<label for="password-input">Password</label>
							<input id="password-input" name="password" type="password" class="form-control" />
							<a href="#" class="forgot-password">[Forgot My Password]</a>
						</div>
						<div class="clearfix">
							<button type="submit" class="btn btn-default login-button">Login</button>
						</div>
					</form>

					<form id="reset-request-form" method="post" action="/login/request-password" class="hide">
						<h2>Request a password reset</h2>
						<input type="hidden" name="forward_to" value="<?php echo $forward_to?>" />
						<div class="form-group">
							<label for="reset-username">Username</label>
							<input id="reset-username" name="reset-username" type="text" class="form-control" />
							<a href="#" class="show-login">[Login]</a>
						</div>
						<div class="clearfix">
							<button class="reset-request-button btn btn-default" type="submit">Submit Request</button>
						</div>
						<div class="message"></div>
					</form>
				</div>
		</div> <!-- .login-wrapper -->
	</div> <!-- #body -->
</div> <!-- #container -->

	<? $this->view('_blocks/footer'); ?>