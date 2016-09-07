<? $this->view('_blocks/html_top') ?>

<header id="site-header">
	<? if ($this->session->has_userdata('user')) { ?>
		<section id="settings-link">
			<a href="/user-management/edit/<?=$this->session->user->id?>"><i class="fa fa-cog" aria-hidden="true"></i>Settings</a>
		</section>
	<? } ?>

	<section id="header-logo">
		
	</section>

	<? if ($this->session->has_userdata('user')) { ?>
		<section id="user-links">
			<div>Logged in as <span class="strong"><?= $this->session->user->username; ?></span></div>|<div><a href="/login/logout">Log Out <i class="fa fa-lock" aria-hidden="true"></i></a></div>
		</section>
	<? } ?>


	<nav id="site-nav"> 
		<ul>
		<? if (
			(check_access_area(FACILITY_MANAGER)) ||
			(check_access_area(LOCATION_MANAGER))):?>
			<li>Facilities
				<ul class="children">
					<? if (check_access_area(FACILITY_MANAGER)): ?>
					<li><a href="/facilities">Facilities Manager</a></li>
					<? endif ?>
					<? if (check_access_area(LOCATION_MANAGER)): ?>
					<li><a href="/locations">Locations Manager</a></li>
					<? endif ?>
				</ul>
			</li>
			<? endif // shows facility top level ?>

			<? if (
			(check_access_area(USER_MANAGEMENT))):?>
			<li>Admin Functionality
				<ul class="children">
					<? if (check_access_area(USER_MANAGEMENT)): ?>
					<li><a href="/user-management">User Management</a></li>
					<? endif ?>
				</ul>
			</li>
			<? endif // shows admin functionality top level ?>
			
		</ul>
	</nav>
</header>