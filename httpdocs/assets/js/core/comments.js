var comments_js = {
	disabled_class : "disabled",

	init : function(){
		var $d = $(document);

		$d.on("click", ".comments .add-new button", comments_js.click_new_comment);
		$d.on("click", ".new-comment-panel button[type='submit']", comments_js.click_save_comment);
		$d.on("click", ".new-comment-panel .close", comments_js.click_close_comment);

		if(!window.comment_queue)
		{
			window.comment_queue = {};
		}
	},

	click_new_comment : function(event){
		event.preventDefault();

		var actor = $(event.currentTarget);
		if(actor.hasClass(comments_js.disabled_class))
		{
			return;
		}

		var panel = actor.closest(".comments").find(".new-comment-panel");
		panel.slideDown("slow", function(){
			panel.find("textarea").focus();
		});
		actor.addClass(comments_js.disabled_class);
	},

	click_close_comment : function(event){
		event.preventDefault();
		var actor = $(event.currentTarget),
			form = actor.closest(".new-comment-panel");

		form.slideUp("slow", function(){
			form.find("textarea").val("");
			form.closest(".comments").find(".add-new button").removeClass(comments_js.disabled_class);
		});
	},

	click_save_comment : function(event){
		event.preventDefault();
		var actor = $(event.currentTarget);
		if(actor.hasClass(comments_js.disabled_class))
		{
			return;
		}

		actor.addClass(comments_js.disabled_class);

		var form = actor.closest(".new-comment-panel"),
			comment = form.find("textarea").val().trim(),
			slug = actor.closest(".comments").data("slug"),
			segments = slug.split("|"),
			id = segments[0],
			model = segments[1];

		var close_panel = function()
		{
			form.slideUp("slow", function(){
				form.find("textarea").val("");
				actor.removeClass(comments_js.disabled_class);
				form.closest(".comments").find(".add-new button").removeClass(comments_js.disabled_class);
			});
		};

		if(id == "")
		{
			comments_js.add_to_queue(model, comment);
			$.ajax({
				url: "/comments/show_pending",
				data: {"comments" : window.comment_queue[model]},
				type: "post",
				dataType: "html",
				complete: function(){
					close_panel();
				},
				success: function(data){
					actor.closest(".comments").find(".listing").html(data);
				}
			});
			return;
		}

		//do an AJAX call to add the comment, then reload the "block"
		$.ajax({
			url: "/comments/insert",
			data: {
				'comment': comment,
				'key': id,
				'table': model
			},
			type: "post",
			dataType: "json",
			complete: function(){},
			success: function(response){
				if(response.success)
				{
					$.ajax({
						url: "/comments/show/"+model+"/"+id,
						dataType: "html",
						success: function(data){
							close_panel();
							actor.closest(".comments").find(".listing").html(data);
						}
					});
				}
				else
				{
					main_js.show_error_modal("Error", response.message);
				}
			},
			error: function(){
				main_js.show_error_modal("Error", response.message);
			}
		});
	},

	add_to_queue : function(model, comment)
	{
		if(!window.comment_queue.hasOwnProperty(model))
		{
			window.comment_queue[model] = [];
		}
		window.comment_queue[model].push(comment);
	},

	has_queue : function(model)
	{
		if(window.comment_queue.hasOwnProperty(model) && window.comment_queue[model].length > 0)
		{
			return true;
		}

		return false;
	},

	process_queue : function(model, id, callback)
	{
		if(!comments_js.has_queue(model))
		{
			if(typeof callback === 'function')
			{
				callback();
			}
			return;
		}

		$.ajax({
			url: "/comments/insert_batch",
			data: {
				'comments': window.comment_queue[model],
				'key': id,
				'table': model
			},
			type: "post",
			dataType: "json",
			complete: function(){},
			success: function(response){
				if(response.success)
				{
					if(typeof callback === 'function')
					{
						callback();
					}
				}
				else
				{
					main_js.show_error_modal("Error", response.message, null, callback);
				}
			},
			error: function(){
				main_js.show_error_modal("Error", "An Unknown Error has occurred. Unfortunately your comments were not saved completely.", null, callback);
			}
		});
	}
};
$(document).ready(function(){
	comments_js.init();
});