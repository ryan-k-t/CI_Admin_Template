function browserSupportFileUpload() {
    var isCompatible = false;
    if (window.File && window.FileReader && window.FileList && window.Blob) {
    	isCompatible = true;
    }
    return isCompatible;
}

var import_js = {
	data: null,
	$form: null,
	$submit_button: null,
	disabled_class: "disabled",
	$file_field: null,
	$status_container: null,

	init: function(){
		var $self = this; //scope

		$self.clear_data();

		$self.$form = $("#module-form");
		if($self.$form && $self.$form.length)
		{
			$self.$file_field = $self.$form.find("[name='import_file']");
			if($self.$file_field && $self.$file_field.length > 0)
			{
				$self.$file_field.on("change", $self.parse_uploaded_file);
			}

			$self.$submit_button = $self.$form.find("button.upload");
			if($self.$submit_button && $self.$submit_button.length)
			{
				$self.init_submit_button();

				$self.$submit_button.on("click", $self.upload_data);
			}

			$self.$status_container = $("#upload-status");
			$self.init_status();
		}
	},

	init_submit_button: function(){
		this.$submit_button.addClass(this.disabled_class);
		this.$submit_button.text("Upload");
	},

	init_status: function(){
		this.$status_container.html("<span class=\"initialized\">Choose a file. The data from the file will be previewed here prior to uploading.</span>");
	},

	upload_data: function(){
		if(import_js.$submit_button.hasClass(import_js.disabled_class))
		{
			return;
		}

		var is_validated = true,
			validation = form_validate.validation("#module-form");
		if (!validation[1]) {
            is_validated = false;
        }

		if(!is_validated)
		{
            main_js.show_message_modal("Error!", validation[0], null, null, "danger");
			return;
		}

		import_js.$submit_button.addClass(import_js.disabled_class);
		import_js.$submit_button.text("Processing");

		var clear_screen = function(){
			import_js.init_submit_button();
			import_js.$file_field.val("");
			import_js.clear_data();
		};
		var _form_to_object = function(){
			var array = import_js.$form.serializeArray(),
				obj = {};
			if(array.length == 0)
			{
				return obj;
			}

			for(var i = 0;i < array.length; i++)
			{
				obj[array[i].name] = array[i].value;
			}

			return obj;
		};

		$.ajax({
			url: import_js.$form.attr("action"),
			data: {
				data: import_js.data,
				_ancillary_data: _form_to_object()
			},
			dataType: "json",
			type: "post",
			success: function(data){
				if(data.success)
				{
					var $results = $("#upload-results");

					$results.html("")
							.append("<div class=\"form\"><span class=\"label\">Upload Status</span></div>")
							.append("<table><thead><tr><th>Row #</th><th>Status</th><th>Message</th></tr></thead><tbody></tbody></table>");

					var $results_body = $results.find("tbody"),
						result_row;
					for(var i=0;i<data.rows.length;i++)
					{
						result_row = "<tr><td>"+data.rows[i].row_number+"</td><td>"+(data.rows[i].success ? "OK" : "Fail")+"</td><td>"+data.rows[i].message+"</td></tr>";
						$results_body.append(result_row);
					}
					main_js.show_success_modal("Success", "Your file has been imported successfully", null, clear_screen);
				}
				else
				{
					main_js.show_error_modal("Error", "We were unable to process your data at this time. "+data.message, null, clear_screen);
				}
			}
		});
	},

	clear_data: function(){
		this.data = [];
	},

	parse_uploaded_file: function(event){
		if(!browserSupportFileUpload())
		{
			main_js.show_error_modal("Browser not supported", "The File APIs are not fully supported in this browser!");
			return;
		}

		var actor = $(event.currentTarget),
			data = null,
			file = event.target.files[0],
			reader = new FileReader();

		if(file)
		{
			import_js.clear_data();
			reader.readAsText(file);
		}

		reader.onload = function(e){
			results = Papa.parse( e.target.result );
			if(results)
			{
				import_js.$submit_button.removeClass(import_js.disabled_class);

				var $upload_container = $("#upload-status"),
					row_count = results.data.length;

				/* wipe clean the status container */
				$upload_container.html("");

				if(row_count > 0)
				{
					var table = "<table width=\"100%\"><thead><tr>",
						column_count = results.data[0].length;
					for(i=1;i<=column_count;i++)
					{
						table += "<th>Column "+i+"</th>";
					}
					table += "</tr></thead><tbody></tbody></table>";
					$upload_container.append(table);

					$table_row = $upload_container.find("tbody");

					var row;
					for(i=0;i<row_count;i++)
					{
						if(column_count == results.data[i].length)
						{
							import_js.data.push(results.data[i]);

							row = "<tr>";
							for(j=0;j<results.data[i].length;j++)
							{
								row += "<td>"+results.data[i][j]+"</td>";
							}
							row += "</tr>";
							$table_row.append(row);
						}
					}
				}
			}
			else
			{
				main_js.show_error_modal("Unable to parse data", "We were unable to read your file. Please make sure the file is in the format provided in the sample file and try again.");
			}
		};
		reader.onerror = function(){
			main_js.show_error_modal("File Error!", "Unable to read " + file.fileName);
		};
	}
};

$(document).ready(function(){
	import_js.init();
});
