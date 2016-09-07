//dependencies:
//  jQuery
//  main.js

;
var cms_module = { 
    module_name : "",
    url_segments : [],
    action_name : "",
    container_selector : "#container",
    $container : null,
    wait_time : 1000,
    disabled_class : "disabled",
    disable_options : false,
    save_callback : "",

    init : function(container_selector, action_name)
    {
        var $self = this; //scope
        
        // if we have called this 
        if (typeof(container_selector) != "undefined" && 
            container_selector !== null) {
            $self.container_selector = container_selector;
        }

        // This is for order_manager.js @ add_customer_form
        if (typeof(action_name) != "undefined" && 
            action_name !== null) {
            $self.action_name = action_name;
        }

        $self.$container = $( $self.container_selector );
        $self.module_name = $self.$container.data("module-name");
        if(!$self.module_name)
        {
            return;
        }

        $self.url_segments = window.location.pathname.split("/");
        if($self.url_segments.length > 0)
        {
            if($self.url_segments[0] == "")
            {
                $self.url_segments.shift();
            }

            if($self.url_segments[0] == $self.module_name)
            {
                if($self.url_segments.length > 0)
                {
                    $self.action_name = $self.url_segments[1];
                }
            }
            else
            {
                $self.action_name == $self.url_segments[0];
            }
        }

        $self.init_events();
    },

    init_events : function()
    {
        console.log('cms_module.init_events');
        var $self = this,
            $doc = $(document);

        $self.$container.off('click', '.save');
        $self.$container.off('click', '.save_and_duplicate');
        $self.$container.off('click', '.delete');
        $self.$container.off('click', '.inactivate');
        $self.$container.off('click', '.activate');
        $self.$container.off('click', '.new-record');
        $self.$container.off('submit', 'form.editor');
        $self.$container.off('click', '#filter-search');
        $self.$container.off('keyup', '.filters input[type="text"]');
        $self.$container.off('click', ".listing header .export");
        $self.$container.off('click', ".quick-link .export-trigger");

        $self.$container.on('click', '.save', $self.save);
        $self.$container.on('click', '.save_and_duplicate', $self.save);

        $self.$container.on('click', '.delete', $self.delete);
        $self.$container.on('click', '.inactivate', $self.inactivate);
        $self.$container.on('click', '.activate', $self.activate);
        $self.$container.on('click', '.new-record', function(event){
            event.preventDefault();
            window.location = "/"+$self.module_name+"/create";
        });
        $self.$container.on('submit', 'form.editor', function(event){
            event.preventDefault();
            $self.$container.find(".save").trigger("click");
        });
        $self.$container.on('click', '#filter-search', $self.filter);
        $self.$container.on('keyup', '.filters input[type="text"]', function(e){
            e.preventDefault();
            if(e.keyCode == 13)
            {
                $self.$container.find("#filter-search").trigger("click");
            }
        });

        $self.$container.on('click', ".quick-link .export-trigger", function(){
            $(".listing header .export").trigger("click");
        });
        $self.$container.on('click', ".listing header .export", $self.export);
    },

    export : function(event)
    {
        var actor = $(event.currentTarget);
        window.location.href = actor.attr("href");
    },

    filter : function(event)
    {
        
        event.preventDefault();

        var actor = $(event.currentTarget),
            $filters = actor.closest(".filters").find("[name]"),
            filters = {},
            $el,
            val;

        /* block if we're currently filtering */
        if(actor.hasClass(cms_module.disabled_class))
        {
            return;
        }
        actor.addClass(cms_module.disabled_class);

        $filters.each(function(){
            $el = $(this);
            val = $el.val().trim();
            if(val != "")
            {
                filters[$el.attr("name")] = val;
            }
        });

        var panel = cms_module.$container.find(".listing");
        if(panel && panel.length > 0)
        {
            $.ajax({
                url: "/"+cms_module.module_name+"/listing",
                type: "post",
                dataType: "html",
                data: filters,
                complete: function(){
                    actor.removeClass(cms_module.disabled_class);
                },
                success: function(data){
                    if(data && data.length > 0)
                    {
                        panel.html(data);
                        window.Sortable.initTable( cms_module.$container.find("table[data-sortable]")[0] );
                    }
                },
                error: function(){}
            });
        }
    },

    // url will be pointing to which controller that will take the data
    // redirect is inteded for redirecting users to save page after they create a page, this will
    // preven users from creating multiple of the same entry and cause them to just update their current entry
    save : function(event, check_validation)
    {
        var save_duplicate = false;

        event.preventDefault();

        // This is so if they double click it does not save multiple times.
        var actor = $(event.currentTarget);
        if (actor.hasClass(cms_module.disabled_class)) {
            return;
        }

        actor.addClass(cms_module.disabled_class);
        var url_endpoint = "";
        // console.log(cms_module.action_name);
        switch (cms_module.action_name) {
          case "create":
            url_endpoint = "insert";
            break;
          case "edit":
            url_endpoint = "update";
            break;
          default:
            break;
        }


        /**
         * This will check if someone clicked save and duplicate. If so
         * then lets change the url endpoint to save and duplicate. We need to 
         * keep the url_endpoint though so we know to update or insert the 
         * current data
         */
        var submit_url = "/"+cms_module.module_name+"/"+url_endpoint;
        
        if (actor.hasClass('save_and_duplicate')) {
            submit_url = "/"+cms_module.module_name+"/save_and_duplicate/"+url_endpoint;
        }

        if(!url_endpoint) 
        {
            actor.removeClass(cms_module.disabled_class);
            return; // we need to know what we're doing ...
        }

        // check validation
        if (typeof(check_validation) == "undefined" || 
            check_validation == null) {
            check_validation = true;
        }

        // this is setup like this so we can override it
        var is_validated = true;
        
        if (check_validation) {
            /**
             * This takes the container element you want to validate. Default
             * is body.
             */
            // console.log(cms_module.container_selector);
            validation = form_validate.validation(cms_module.container_selector);
            if (!validation[1]) {
                is_validated = false;
            }
        }
        


        if (!is_validated) {
            main_js.show_message_modal("Error!", validation[0], null, null, "danger");
            actor.removeClass(cms_module.disabled_class);

        } else {
            
            var data = $( cms_module.container_selector+' form.editor' ).serialize();
            
            $.ajax({
                type:"POST",
                url: submit_url,
                data: data,

                success: function(res){                  
                    if (res.success)
                    {
                        main_js.show_success_modal("Success", "The record was saved successfully", "OK", function(){
                            
                            console.log(typeof cms_module.save_callback);
                            if (typeof cms_module.save_callback == "function") {
                                console.log('callback called');
                                cms_module.save_callback(res);
                                return;
                            }
                            if (res.duplicate) {
                                
                              // send them to create but pass duplicate data id.
                              window.setTimeout(function(){
                                        window.location.replace('/'+cms_module.module_name+'/create/'+res.id);
                                    }, cms_module.wait_time);
                              return;
                                
                            }
                            // call back function run this
                            
                            // otherwise check for id for redirect

                            if (typeof res.id !== 'undefined') {

                                var redirect = function(){
                                    window.setTimeout(function(){
                                        window.location.replace('/'+cms_module.module_name+'/edit/'+res.id);
                                    }, cms_module.wait_time);
                                }

                                if(cms_module.action_name == "create")
                                {
                                    if(comments_js.has_queue(cms_module.module_name))
                                    {
                                        comments_js.process_queue(cms_module.module_name, res.id, redirect);
                                    }
                                    else
                                    {
                                        redirect();
                                    }
                                }
                                else
                                {
                                    redirect();
                                }
                            }
                        });
                    } else
                    {
                        var redirect = null;
                        if(res.redirect_to)
                        {
                            redirect = function(){
                                window.location.href = res.redirect_to;
                            }
                        }
                        main_js.show_error_modal("Error!", res.message, null, redirect);
                    }
                },
                error: function (res) {
                    main_js.show_error_modal("Error!", "An unknown error was encountered", null, null);
                },
                complete: function() {
                    actor.removeClass(cms_module.disabled_class);
                }
            });  
        
        } // end of else
    },

    delete : function(event)
    {
        event.preventDefault();

        var actor = $(event.currentTarget),
            id = actor.data("id"),
            name = actor.data("name");

        if(!id || !name) return;

        if(actor.hasClass(cms_module.disabled_class))
        {
            return;
        }

        actor.addClass(cms_module.disabled_class);

        main_js.show_confirm_modal("Confirm Deletion", "You are about to delete "+name+". Are you sure? This action can not be reversed.", "Cancel", "Yes", function(){ 
            $.ajax({
                url: '/'+cms_module.module_name+'/delete',
                data: {id: id},
                type: 'POST',
                dataType: 'json',
                error: function(jq, status, text){
                    main_js.show_error_modal("Error!", "An unknown error was encountered");
                },
                success: function(data){
                    if(data.success)
                    {
                        main_js.show_success_modal("Success", "The record was deleted successfully", "OK", function(){
                            window.setTimeout(function(){
                                document.location.reload();
                            }, cms_module.wait_time);
                        });
                    }
                    else
                    {
                        var redirect = null;
                        if(res.redirect_to)
                        {
                            redirect = function(){
                                window.location.href = res.redirect_to;
                            }
                        }
                        main_js.show_error_modal("Error!", data.message, null, redirect);
                    }
                },
                complete: function(){
                    actor.removeClass(cms_module.disabled_class);
                }
            });
            return false;
        });
    },


    inactivate : function(event)
    {
        event.preventDefault();

        var actor = $(event.currentTarget),
            id = actor.data("id"),
            name = actor.data("name");

        if(!id || !name) return;

        if(actor.hasClass(cms_module.disabled_class))
        {
            return;
        }

        main_js.show_confirm_modal("Confirm Deactivation", "You are about to deactivate "+name+". Are you sure?", "Cancel", "Yes", function(){ 
            $.ajax({
                url: '/'+cms_module.module_name+'/inactivate',
                data: {id: id},
                type: 'POST',
                dataType: 'json',
                error: function(jq, status, text){
                    main_js.show_error_modal("Error!", "An unknown error was encountered");
                },
                success: function(data){
                    if(data.success)
                    {
                        main_js.show_success_modal("Success", "The record was deactivated successfully", "OK", function(){
                            window.setTimeout(function(){
                                document.location.reload();
                            }, cms_module.wait_time);
                        });
                    }
                    else
                    {
                        var redirect = null;
                        if(res.redirect_to)
                        {
                            redirect = function(){
                                window.location.href = res.redirect_to;
                            }
                        }
                        main_js.show_error_modal("Error!", data.message, null, redirect);
                    }
                },
                beforeSend: function(){
                    actor.addClass(cms_module.disabled_class);
                },
                complete: function(){
                    actor.removeClass(cms_module.disabled_class);
                }
            });
            return false;
        });
    },
    activate : function(event)
    {
        event.preventDefault();

        var actor = $(event.currentTarget),
            id = actor.data("id"),
            name = actor.data("name");

        if(!id || !name) return;

        if(actor.hasClass(cms_module.disabled_class))
        {
            return;
        }

        main_js.show_confirm_modal("Confirm Activation", "You are about to activate "+name+". Are you sure?", "Cancel", "Yes", function(){ 
            $.ajax({
                url: '/'+cms_module.module_name+'/activate',
                data: {id: id},
                type: 'POST',
                dataType: 'json',
                error: function(jq, status, text){
                    main_js.show_error_modal("Error!", "An unknown error was encountered");
                },
                success: function(data){
                    if(data.success)
                    {
                        main_js.show_success_modal("Success", "The record was activated successfully", "OK", function(){
                            window.setTimeout(function(){
                                document.location.reload();
                            }, cms_module.wait_time);
                        });
                    }
                    else
                    {
                        var redirect = null;
                        if(res.redirect_to)
                        {
                            redirect = function(){
                                window.location.href = res.redirect_to;
                            }
                        }
                        main_js.show_error_modal("Error!", data.message, null, redirect);
                    }
                },
                beforeSend: function(){
                    actor.addClass(cms_module.disabled_class);
                },
                complete: function(){
                    actor.removeClass(cms_module.disabled_class);
                }
            });
        });
    },
};

$(document).ready(function(){
    cms_module.init();
});