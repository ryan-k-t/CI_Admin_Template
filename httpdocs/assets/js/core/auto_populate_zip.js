$(document).ready(function(){
	zip_js.init();
});

var zip_js = {

	init : function () 
	{

		// ZIP code City/State Autopopulate
		$('#postal_code').on('blur', function() {
			if ($(this).val().length == 5) {
				try { xhr.abort(); } catch (e) {}
				xhr = $.getJSON('/feeds/getcitystate/' + $(this).val(), null, function(data) {
					if (data && !data.error) {
						$('#city').val(data[0].city.split(' ').map(function(val) {return val.charAt(0) + val.substring(1).toLowerCase();}).join(' ')); // Ha. One-liner to capitalize words. You're welcome.
						$('#state').select2("val", data[0].state);
					}
				});
			}
		});		
	}, 

	uninitialized : function ()
	{
		$('#postal_code').off('blur');
	}


}