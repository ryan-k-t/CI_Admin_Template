
var multiDate = { 

	init : function()
	{
		var $doc = $(document);
	    $doc.on('click','.multi-date-group .date-add-more', multiDate.addMore);
	    $doc.on('click','.glyphicon-remove', multiDate.removeItem);
	},

	addMore : function(e)
	{
		var actor = $(e.currentTarget);
		if(actor.hasClass("disabled"))
		{
			return;
		}
		actor.addClass("disabled");

		//Lets get the current target we are on
		var parent = $(this).parent();
		
		//Get the total number of datepickers already on the page
		var numItems = $('.multi-date').length + 1;

		//Append the content onto the last element
		var content = '';

		var randomDigit = Math.floor((Math.random() * 110000) + 1000);

		content += '<div class="form-group field multi-date">';
			content += '<div class="input-group date additional" id="'+randomDigit+'">';
				content += '<input name="multi-date-'+randomDigit+'" max_length="0" class="form-control date-field" placeholder="" dates="" required="required">';
	            content += '<span class="input-group-addon">';
	                content += '<span class="glyphicon glyphicon-calendar"></span>';
	                content += '<span class="glyphicon glyphicon-remove"></span>';
	            content += '</span>';
			content += '</div>';
		content += '</div>';		

		$(content).insertBefore(actor);

		//init the additional datepickers
		parent.find("input.date-field:last-of-type").each(function(){
		    $(this).datepicker();
		});

		//We need to put in the add more button
		actor.removeClass("disabled");
	},

	removeItem : function(e)
	{
		e.preventDefault();

		//Lets make sure there is at least two dates before removing one
		//the first date is not part of this multi-date picker
		if ($('.multi-date').length > 1) {

			//We need to remove the element
			//Lets get the current target we are on
			$(e.currentTarget).closest(".multi-date").remove();

		} else {
			main_js.show_error_modal("Error!", "There must be at least two dates.", null, "danger");
		}
	}
};

$(document).ready(function(){
    multiDate.init();
});
