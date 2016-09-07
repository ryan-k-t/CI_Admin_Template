/** fallback to protect when console.log isn't supported */
if (typeof console === "undefined" || typeof console.log === "undefined") {
    console = {};
    console.log = function () { };
}

$(function () {
	$('[data-toggle="tooltip"]').tooltip();
});

var $doc = $(document);
$doc.ready(function() {
	$doc.on('click', 'a[href=""], a[href="#"]', function(e) {  e.preventDefault();  });
});

var main_js = {
	show_confirm_modal : function(title, message, negative_label, positive_label, positive_callback, negative_callback, message_type){
		var modal = $("#erSiteConfirmModal");
		if(!modal || modal.length == 0) return;

		if(typeof negative_label === "undefined") negative_label = "Close";
		if(typeof positive_label === "undefined") positive_label = "OK";
		if(typeof message_type === "undefined") message_type = "warning";

		var header_class = "";
		switch (message_type) {
		  case "warning":
		  case "success":
		  case "info":
		  case "danger":
		    header_class = "bg-"+message_type;
		    break;
		  default:
		    break;
		}

		if(header_class)
		{
			modal.find(".modal-header").addClass(header_class);
		}
		modal.find(".modal-title").text(title);
		modal.find(".modal-body").html(message);

		var $negative_action = modal.find(".action-negative");
		$negative_action.text(negative_label);
		$negative_action.off("click");
		if(typeof negative_callback === "function")
		{
			$negative_action.on("click", function(){
				negative_callback();
				modal.modal("hide");
			});
		}
		else
		{
			$negative_action.on("click", function(){
				modal.modal("hide");
			});			
		}

		var $positive_action = modal.find(".action-positive");
		$positive_action.text(positive_label);

		$positive_action.off("click");
		if(typeof positive_callback === "function")
		{
			$positive_action.on("click", function(){
				positive_callback();
				modal.modal("hide");
			});
		}
		else
		{
			$positive_action.on("click", function(){
				modal.modal("hide");
			});			
		}

		modal.modal();
	},

	show_error_modal : function(title, message, button_label, callback)
	{
		main_js.show_message_modal(title, message, button_label, callback, "danger");
	},
	show_warning_modal : function(title, message, button_label, callback)
	{
		main_js.show_message_modal(title, message, button_label, callback, "warning");
	},
	show_success_modal : function(title, message, button_label, callback)
	{
		main_js.show_message_modal(title, message, button_label, callback, "success");
	},
	show_info_modal : function(title, message, button_label, callback)
	{
		main_js.show_message_modal(title, message, button_label, callback, "info");
	},
	show_message_modal : function(title, message, button_label, callback, message_type)
	{
		var modal = $("#erSiteMessageModal");
		if(!modal || modal.length == 0) return;

		if(typeof button_label === "undefined" || button_label == null) button_label = "Close";

		var header_class = "";
		switch (message_type) {
		  case "warning":
		  case "success":
		  case "info":
		  case "danger":
		    header_class = "bg-"+message_type;
		    break;
		  default:
		    break;
		}

		var header = modal.find(".modal-header");
		header.attr("class", "modal-header");
		if(header_class)
		{
			header.addClass(header_class);
		}

		modal.find(".modal-title").text(title);
		modal.find(".modal-body").html(message);

		var $action = modal.find(".action");
		$action.text(button_label);

		$action.off("click");
		if(typeof callback === "function")
		{
			$action.on("click", function(){
				callback();
				modal.modal("hide");
			});
		}
		else
		{
			$action.on("click", function(){
				modal.modal("hide");
			});			
		}

		modal.modal();
	}
};
