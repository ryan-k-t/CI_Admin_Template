
	<section id="locations-quick-link" class="quick-link clearfix">
		<button id="new-location" class="new-record">New Location</button>
		<div class="filters clearfix">
			<div class="field"><label for="filter-name">Name</label><input type="text" id="filter-name" name="filter-name"></div>
			<div class="field">
				<label for="filter-facility">Facility</label>
				<select id="filter-facility_id" name="filter-facility_id">
					<option value=""></option>
					<? foreach($facilities as $value=>$label){ ?>
						<option value="<?=$value;?>"><?= ucwords($label); ?></option>
					<? } ?>
				</select>
			</div>
			<div class="field no-label"><button id="filter-search">Search</button></div>
		</div>
	</section>
