function isPopulatedObject($obj)
{
	return ($obj && $obj.length > 0);
}



var login_js = {
	$reset_request_form: null,
	$login_form: null,

	initialize: function()
	{
		var $d = $(document),
			self = this; //scope

		self.$reset_request_form = $("#reset-request-form");
		self.$login_form = $("#login-form");

		$d.on('click', '.login-button', self.login);
		$d.on('click', '.logout-button', self.logout);
		$d.on('click', '.impersonate-user-button', self.impersonate_user);
		$d.on('click', '.restore-user-button', self.restore_user);
		$d.on('click', '.forgot-password', self.show_password_reset);
		$d.on('click', '.reset-request-button', self.request_password_reset);
		$d.on('click', '.show-login', self.show_login);
		$d.on('click', '.change-password', self.change_password);
		$d.on('click', '.change-user-password', self.change_user_password);
		$d.on('keyup', '#reset-username', function(e){
			if(e.keyCode == 13)
			{
				self.request_password_reset;
			}
		});

	},

	login: function()
	{
		$.ajax({
			url: '/login/authenticate/',
			data: $("#login-form").serialize(),
			type: 'post',
			dataType: 'json',
			beforeSend: function(){  },
			complete: function(){  },
			success: function(response){
				if(response.success)
				{
					window.location = response.forward_to ? response.forward_to : '/';
				} else if(response.message.length > 0) {
					alert(response.message);
				}
				else
				{
					alert('Could not log in. Please try again later.');
				}
			},
			error: function(jqXHR, status, err){
				alert('Could not log in. Please try again later.');
			}
		});
		
		return false;
	},

	logout: function()
	{
		window.location='/login/logout/';
	},

	is_authenticated: function(callback)
	{
		var result = false;
		$.ajax({
			url: '/login/authenticated/',
			type: 'post',
			dataType: 'json',
			complete: function(){
				return result;
			},
			success: function(response){
				if(response.authenticated == true)
				{
					if(callback && (typeof callback == "function")){
						callback();
					}
					result = true;
				} else if(response.authenticated == false) {
					window.location='/login';
				}
			}
		});
	},

	change_password: function()
	{
		var dialog_div = $("<div title=\"Change Password\"/>");
		var directions = $("<p/>").text("Enter your current password below, followed by whatever you would like for your new password. Be sure to enter your new password twice to confirm the spelling.");
		
		var current_password_input = $("<input/>").attr({type: 'password', name: 'current_password'}).css({width: '98%'});
		var new_password_input = $("<input/>").attr({type: 'password', name: 'new_password'}).css({width: '98%'});
		var confirm_password_input = $("<input/>").attr({type: 'password', name: 'confirm_password'}).css({width: '98%'});
		
		var _current_password = $("<div/>").addClass('form-item').text("Current Password").append(current_password_input);
		var _new_password = $("<div/>").addClass('form-item').text("New Password").append(new_password_input);
		var _confirm_password = $("<div/>").addClass('form-item').text("Confirm Password").append(confirm_password_input);
		
		var submit_dialog = function(){
			Wait(true);
			var current_password = $("[name=current_password]", dialog_div).val();
			var new_password = $("[name=new_password]", dialog_div).val();
			var confirm_password = $("[name=confirm_password]", dialog_div).val();
			
			if(!(current_password && new_password && confirm_password))
			{
				Wait(false);
				error_message("You must fill in all three fields to set your password.");
				return false;
			}
			else if(new_password != confirm_password)
			{
				Wait(false);
				error_message("The Confirm Password field must match the New Password field");
				return false;
			}
			else
			{
				$.ajax({
					url: '/authentication/change-password/',
					type: 'post',
					data: {current_password: current_password, new_password: new_password},
					dateType: 'json',
					error: function(){ 
						Wait(false);
						dialog_div.dialog('close');
						error_message('Error connecting to server. Please try again in a few minutes');
					},
					success: function(response){	
						Wait(false);
						dialog_div.dialog('close');
						if(response.success)
						{
							success_message("Your password was successfully updated.");
						}
						else if(response.message.length > 0)
						{
							error_message(response.message);
						}
						else
						{
							error_message('Could not log in. Please try again later.');
						}
					}
				});
			}
		}
		
		confirm_password_input.on('keyup', function(e){
			if(e.keyCode == 13)
			{
				submit_dialog()
			}
		});
		
		dialog_div.append(directions).append(_current_password).append(_new_password).append(_confirm_password).dialog({
			width: 400,
			modal: true,
			close: function(event, ui){
				$(this).dialog("destroy");
				$(this).remove();
			},
			buttons: {
				'Submit': submit_dialog,
				'Cancel': function(){ $(this).dialog('close') }
			}
		});
		
		return false;
	},

	change_user_password: function()
	{
		var dialog_div = $("<div title=\"Change Password\"/>");
		var directions = $("<p/>").text("This form will allow you to update a password for a user with an access level lower than yours. Enter their username and desired password below.");
		
		var username_input = $("<input/>").attr({type: 'text', name: 'username'}).css({width: '98%'});
		var new_password_input = $("<input/>").attr({type: 'password', name: 'new_password'}).css({width: '98%'});
		
		var _username = $("<div/>").addClass('form-item').text("Username").append(username_input);
		var _new_password = $("<div/>").addClass('form-item').text("New Password").append(new_password_input);
		
		var submit_dialog = function(){
			Wait(true);
			var username = $("[name=username]", dialog_div).val();
			var new_password = $("[name=new_password]", dialog_div).val();
			
			if(!(username && new_password))
			{
				Wait(false);
				error_message("You must fill in all three fields to set your password.");
				return false;
			}
			else
			{
				$.ajax({
					url: '/authentication/change-password/',
					type: 'post',
					dataType: 'json',
					data: {username: username, new_password: new_password},
					error: function(){ 
						Wait(false);
						dialog_div.dialog('close');
						error_message('Error connecting to server. Please try again in a few minutes');
					},
					success: function(response){	
						Wait(false);
						dialog_div.dialog('close');
						if(response.success)
						{
							success_message(username+"'s password was successfully updated.");
						}
						else if(response.message.length > 0)
						{
							error_message(response.message);
						}
						else
						{
							error_message('Could not log in. Please try again later.');
						}
					}
				});
			}
		}
		
		new_password_input.on('keyup', function(e){
			if(e.keyCode == 13)
			{
				submit_dialog()
			}
		});
		
		dialog_div.append(directions).append(_username).append(_new_password).dialog({
			width: 400,
			modal: true,
			close: function(event, ui){
				$(this).dialog("destroy");
				$(this).remove();
			},
			buttons: {
				'Submit': submit_dialog,
				'Cancel': function(){ $(this).dialog('close') }
			}
		});
		
		return false;
	},

	show_login: function(e)
	{
		e.preventDefault();

		if(isPopulatedObject(login_js.$login_form))
		{
			if(isPopulatedObject(login_js.$reset_request_form))
			{
				login_js.$reset_request_form.removeClass("show").addClass("hidden");
			}
			login_js.$login_form.removeClass("hidden").addClass("show");
		}
	},

	request_password_reset: function(e)
	{
		e.preventDefault();

		var message_box = login_js.$reset_request_form.find(".message");
		message_box.removeClass("bg-danger bg-success show").addClass("hidden");

		var username = $('#reset-username').val().trim();
		if(username)
		{
			$.ajax({
				url: '/login/request_password/',
				type: 'post',
				dataType: 'json',
				data: {username: username},
				error: function(){ 
					alert('Error connecting to server. Please try again in a few minutes');
				},
				success: function(response){
					message_box.removeClass("show").addClass("hidden");
					message_box.html(response.message);
					if(response.success)
					{
						message_box.removeClass("bg-danger").addClass("bg-success");
					}
					else
					{
						message_box.removeClass("bg-success").addClass("bg-danger");
					}
					message_box.removeClass("hidden").addClass("show");
				}
			});
		}
	},

	show_password_reset: function(e)
	{
		e.preventDefault();

		if(isPopulatedObject(login_js.$reset_request_form))
		{
			if(isPopulatedObject(login_js.$login_form))
			{
				login_js.$login_form.removeClass("show").addClass("hidden");
			}
			login_js.$reset_request_form.removeClass("hidden").addClass("show");
		}
	},

	restore_user: function()
	{
		$.ajax({
			url: '/login/restore_user/',
			type: 'post',
			dataType: 'json',
			beforeSend: function(){ },
			complete: function() { },
			error: function(){ 
				alert('Error connecting to server. Please try again in a few minutes') 
			},
			success: function(response){
				if(response.success)
				{
					window.location.href = '/';
				}
				else if(response.message.length > 0)
				{
					alert(response.message);
				}
				else
				{
					alert('Could not complete request at this time. Please try again later.');
				}
			}
		});
	},

	impersonate_user: function()
	{
		var dialog_div = $("<div title=\"Impersonate User\"/>");
		var directions = $("<p/>").text("Enter the username of the user you want to impersonate in the field below. Please note that upon doing so you will be logged out of your account.");
		var username_input = $("<input/>").attr({type: 'text', name: 'username', 'id': 'impersonate-username'}).css({width: '98%'});
		
		var submit_dialog = function(){
			var username = $('#impersonate-username').val();
			if(username)
			{
				$.ajax({
					url: '/authentication/impersonate/',
					type: 'post',
					dataType: 'json',
					data: {username: username},
					beforeSend: function(){ },
					complete: function() { },
					error: function(){ 
						error_message('Error connecting to server. Please try again in a few minutes') 
					},
					success: function(response){
						if(response.success)
						{
							window.location.href = '/';
						}
						else if(response.message.length > 0)
						{
							error_message(response.message);
						}
						else
						{
							error_message('Could not complete request at this time. Please try again later.');
						}
					}
				});
			}
		};
		
		username_input.on('keyup', function(e){
			if(e.keyCode == 13)
			{
				submit_dialog()
			}
		});
		
		dialog_div.append(directions).append(username_input).dialog({
			width: 400,
			modal: true,
			close: function(event, ui){
				$(this).dialog("destroy");
				$(this).remove();
			},
			buttons: {
				'Impersonate': submit_dialog,
				'Cancel': function(){ $(this).dialog('close') }
			}
		});
	}

};
login_js.initialize();