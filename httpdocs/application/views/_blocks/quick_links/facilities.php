
	<section id="facilities-quick-link" class="quick-link clearfix">
		<button id="new-facility" class="new-record">New Facility</button>
		<div class="filters clearfix">
			<div class="field">
				<label for="filter-facility_type_id">Type</label>
				<select id="filter-facility_type_id" name="filter-facility_type_id">
					<option value=""></option>
					<? foreach($facility_types as $value=>$label){ ?>
						<option value="<?=$value;?>"><?= ucwords($label); ?></option>
					<? } ?>
				</select>
			</div>
			<div class="field"><label for="filter-name">Name</label><input type="text" id="filter-name" name="filter-name"></div>
			<div class="field no-label"><button id="filter-search">Search</button></div>
		</div>
	</section>
