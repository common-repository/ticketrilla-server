/*jslint browser: true, this: true*/
/*jshint maxlen:false*/
/*global window, CKEDITOR, jQuery*/

(function ($) {
    "use strict";
	var TTLS = {
		attachments : [],
	}

    // switch to ticket by clicking on a raw
    if ( $(".ttls__tickets").length ) {
        $(document).on("click", ".ttls__tickets tr", function(){
            if ( $(this).find(".ttls__tickets-url").length ) {
                window.location = $(this).find(".ttls__tickets-url").attr('href');
            }
        });
    }

    // cues in forms
    $(document).on("mouseenter", ".ttls__label-info", function(){
        var endInfo = $(this).offset().top + $(this).find( ".ttls__label-info-hidden" ).height(),
            endScreen = $(window).scrollTop() + $(window).height();
        if ( endInfo > endScreen ) {
            $(this).find( ".ttls__label-info-hidden" ).addClass('to-top');
        } else {
            $(this).find( ".ttls__label-info-hidden" ).removeClass('to-top');
        }
        $( this ).find( ".ttls__label-info-hidden" ).stop().fadeIn(300);
    });
    $(document).on("mouseleave", ".ttls__label-info", function(){
        $( this ).find( ".ttls__label-info-hidden" ).stop().fadeOut(300);
    });


    // autocorrection of height in textarea for content
    $(document).on( "ready load change keyup keydown paste cut", ".ttls__settings-inner textarea.form-control", function (){
        $(this).height(0).height(this.scrollHeight - 10);
    }).find( "textarea" ).change();

    // date selection
    $(".ttls__datepicker").datepicker({
        autoclose: true,
        todayHighlight: true,
        dateFormat: 'yy-mm-dd'
    });

    // start the script for generating corrected htaccess
    $(document).on("click", "#ttls_generate_htaccess", function(){
        var $button = $(this);
        var data = {
            action: "ttls_generate_htaccess"
        };
        jQuery.post( ajaxurl, data, function(response) {
            if ( response.success ) {
                ttlsAddAlert( "success", "", response.data.message );
            } else {
                ttlsAddAlert( "danger", "", response.data.message );
            }
        });
    });

    // text editor
    if ($("#ttls-ckeditor").length) {
        CKEDITOR.replace("ttls-ckeditor");
    }

    if ( $("#ttls__settings-menu").length ) {
        // menu scroll in settings
        var $settingMenu = $("#ttls__settings-menu");
        var $settingMenuParent = $("#ttls__settings-menu").parent();
        $(window).on("scroll", function () {
            if ( $( window ).width() > 992 && $(window).scrollTop() + 64 > $settingMenuParent.offset().top ) {
                var marginTop = $(window).scrollTop() - $settingMenuParent.offset().top + 64;

                if ( $settingMenuParent.next().height() > $settingMenu.height() + marginTop ) {
                    $settingMenu.css("position", "fixed");
                    var $parentWidth = $settingMenuParent.width();
                    $settingMenu.css("width", $parentWidth);
                }

            } else {
                $settingMenu.css("position", "static");
            }
        });

        // slow transition
        $(document).on("click", "#ttls__settings-menu a[data-scroll]", function () {
            var target = $(this).attr("href");
            if (target.length) {
                event.preventDefault();
                $("html, body").stop().animate({
                    scrollTop: $(target).offset().top - 82
                }, 400);
            }
        });
        
        $(window).on("resize", function () {
	        var $parentWidth = $settingMenuParent.width();
            $settingMenu.css("width", $parentWidth);
        });
    }

    if ( $(".ttls__users").length ) {
        // table heading scroll
        $(window).on("scroll", function () {
            if ( $(window).scrollTop() > $(".ttls__users thead").offset().top && $(window).scrollTop() < ( $(".ttls__users thead").offset().top + $(".ttls__users table").height() - 40 ) ) {
                $(".ttls__users thead span").each(function(){
                    $(this).width( $(this).parent().width() );
                });
                $(".ttls__users thead span").fadeIn(300).css('display', 'block');
            } else {
                $(".ttls__users thead span").fadeOut(300);
            }
        });

    }


    // DEVELOPER ACTIONS
    // line markup of deleted developer sending deleted user to form
    $(document).on("click", ".ttls_developer_delete", function(){
	    var devID = $(this).data("developer");
        $(".ttls_for_delete").removeClass("ttls_for_delete");
        $(this).parents("tr").addClass("ttls_for_delete");
        $("#ttls_delete_user_id").val( devID );
        $("#ttls_recepient_users option[value!=" + devID + "]:gt(0)").attr('disabled', false);
        $("#ttls_recepient_users option[value=" + devID + "]").attr('disabled', true);
    });

    // DEVELOPER ACTIONS
    // deleting developer
    $(document).on("submit","form.ttls-delete-developer",function(){
        var $thisForm = $(this);
        var $line = $(".ttls_for_delete");
        $thisForm.find(".help-block").remove();
        $thisForm.find(".has-error").removeClass("has-error");
        $thisForm.find(".modal-footer span").remove();
        $thisForm.addClass("ttls__disabled");
        var data = {
            action: "ttls_delete_user",
            fields: $thisForm.serialize(),
        };
        jQuery.post( ajaxurl, data, function( response ) {
            $thisForm.removeClass("ttls__disabled");
            $thisForm.find(".modal-footer span").remove();
            if ( response.success ) {
                $(".ttls_for_delete").remove();
                $thisForm.find(".modal-footer").prepend("<span class=\"text-success\">"+response.data.message+"</span>");
            } else {
                $.each( response.data.errors, function(key, message){
                    if ( $("*[name="+key+"]").length ) {
                        $("*[name="+key+"]").parents(".form-group")
                            .addClass("has-error")
                            .append("<span class=\"help-block error\">"+message+"</span>");
                    }
                });
                $thisForm.find(".modal-footer").prepend("<span class=\"text-danger\">"+response.data.message+"</span>");
            }
        });
        return false;
    });


    // SETTINGS ACTIONS
    // update profile
    $(document).on("submit","form.ttls-profile-form",function(){
        var $thisForm = $(this);
        $thisForm.find(".help-block.status").remove();
        $thisForm.find(".has-error").removeClass(".has-error");
        $thisForm.addClass("ttls__disabled");
        var data = {
            action: "ttls_update_profile",
            fields: $thisForm.serialize(),
        };
        jQuery.post( ajaxurl, data, function( response ) {
            $thisForm.removeClass("ttls__disabled");
            $thisForm.find(".ttls__settings-inner-footer").find("span").remove();
            if ( response.success ) {
                $thisForm.find(".ttls__settings-inner-footer").prepend("<span class=\"text-success\">"+response.data.message+"</span>");
            } else {
                $.each( response.data.errors, function(key, message){
                    if ( $("*[name="+key+"]").length ) {
                        $("*[name="+key+"]").parents(".form-group")
                            .addClass("has-error")
                            .append("<span class=\"help-block error\">"+message+"</span>");
                    }
                });
                $thisForm.find(".ttls__settings-inner-footer").prepend("<span class=\"text-danger\">"+response.data.message+"</span>");
            }
        });
        return false;
    });

    // SETTINGS ACTIONS
    // save section value
    $(document).on("submit","form.ttls-setting-form",function(){
        var $thisForm = $(this);
        $thisForm.find(".ttls__settings-inner-footer").find("span").remove();
        $thisForm.find(".has-error").removeClass("has-error");
        $thisForm.find(".error").remove();
        $thisForm.addClass("ttls__disabled");
        var data = {
            action: "ttls_update_settings",
            fields: $thisForm.serialize(),
        };
        jQuery.post( ajaxurl, data, function( response ) {
            $thisForm.removeClass("ttls__disabled");
            $thisForm.find(".ttls__settings-inner-footer").find("span").remove();
            if ( response.success ) {
                $thisForm.find(".ttls__settings-inner-footer").prepend("<span class=\"text-success\">"+response.data.message+"</span>");
            } else {
                $.each( response.data.errors, function(key, message){
                    if ( $("*[name="+key+"]").length ) {
                        $("*[name="+key+"]").parents(".form-group")
                            .addClass("has-error")
                            .append("<span class=\"help-block error\">"+message+"</span>");
                    }
                });
                $thisForm.find(".ttls__settings-inner-footer").prepend("<span class=\"text-danger\">"+response.data.message+"</span>");
            }
        });
        return false;
    });

    // SETTINGS ACTIONS
    // change section status, if any data was changed
    $(document).on( "change keyup", "form.ttls-setting-form input, form.ttls-setting-form select, form.ttls-setting-form textarea",
        function(){
            $(this).parents("form").find(".ttls__settings-inner-footer").find("span").remove();
            $(this).parents("form").find(".ttls__settings-inner-footer").prepend("<span class=\"text-warning\">Pending changes</span>");
        }
    );

    // SETTINGS ACTIONS
    // save product settings (plugin admin)
    $(document).on("submit",".ttls-admin-save-product-form",function(e){
        e.preventDefault();
        var form = $(this);
        var data = form.serializeArray();
        data = addCheckboxesValues(data, form);
        var container = $('#ttls-container');

        $(container).addClass('ttls__disabled');

        $.ajax( {
            type : 'post',
            url : ajaxurl,
            data : data,
        } ).done( function( response ) {
            if ( response.data.html !== undefined ) {
                container.replaceWith( response.data.html );
            }
        } ).always( function( response ) {
            $(container).removeClass('ttls__disabled');
        } );
    });

    	// ATTACHMENT ACTIONS
        // a button for adding attachments to a response
        $(document).on("change", "#ttls-ticket-attachment", function(){
            var adder = this;
            var $adder = $(adder);

            $.each( adder.files, function( key, value ){
                var data = new FormData();
                data.append( "file", value );
                data.append( "action", "ttls_add_response_attachment" );
                data.append( "ticket", $adder.data("ticket") );

                var $newattach = $("<li><span class=\"ttls__attachments-icon\"><i class=\"fa fa-sync-alt fa-spin\"></i></span><div class=\"ttls__attachments-info\"><div class=\"ttls__attachments-name\">"+$adder.data("textdownload")+"</div><div class=\"progress\"><div role=\"progressbar\" class=\"progress-bar progress-bar-info progress-bar-striped\"></div></div></div></li>");


                $.ajax({
                    url: ajaxurl,
                    data: data,
                    processData: false,
                    contentType: false,
                    dataType: "json",
                    type: "POST",
                    beforeSend: function() {
                        $adder.parent().before( $newattach );
                    },
                    success: function( response ){

                        $newattach.parent().prepend( response.data.box );
                        $newattach.remove();

                    },
                    xhr: function() {
                        var myXhr = $.ajaxSettings.xhr();

                        if ( myXhr.upload ) {
                            myXhr.upload.addEventListener( "progress", function(e) {
                                if ( e.lengthComputable ) {
                                    var perc = ( e.loaded / e.total ) * 100;
                                    perc = perc.toFixed(2);
                                    $newattach.find(".progress-bar").css( "width", perc + "%" );
                                }
                            }, false );
                        }

                        return myXhr;
                    }
                });

            });

        });

        // ATTACHMENT ACTIONS
        // delete uploaded file (prior to sending it to client)
        $(document).on("click", ".ttls_delete_temp_attachment", function(){
            var $button = $(this);
            var $attach = $(this).parent().parent().parent();
            var result = confirm("Are you sure to delete this attachment?");
            if ( result ) {
                var data = {
                    action: "ttls_delete_response_attachment",
                    attachment: $button.data("attachment"),
                };
                jQuery.post( ajaxurl, data, function(response) {
                    if ( response.success ) {
                        $attach.remove();
                    } else {
                        ttlsAddAlert( "danger", "", response.data.message );
                    }
                });
            }
        });

        // ATTACHMENT ACTIONS
        // button of manual attachments upload
        $(document).on("click", ".ttls_manual_load_attachment", function(){
            var $attach = $(this).parent().parent().parent();
            var $button = $(this);
            var result = confirm("Are you sure to load this attachment?");
            if ( result ) {
                var data = {
                    action: "ttls_manual_load_attachment",
                    attachment: $button.data("attachment"),
                };
                jQuery.post( ajaxurl, data, function(response) {
                    if ( response.success ) {
                        $attach.after( $(response.data.box) );
                        $attach.remove();
                    } else {
                        ttlsAddAlert( "danger", "", response.data.message );
                    }
                });
            }
        });

        // FORMS ACTIONS
        // copying button results
        $(document).on("click", ".ttls_copy_form_result", function(){
            var $btn = $(this);
            var text = $btn.parents("form").find(".modal-footer").find(".help-block")
                        .html()
                            .replace(/<\/{0,1}pre>/g, "");
            $("body").append("<div id=\"ttls_copy_container\">"+text+"</div>");
            var ta = document.getElementById('ttls_copy_container');
            window.getSelection().selectAllChildren(ta);

            var succeed;
            try {
                succeed = document.execCommand("copy");
            } catch(e) {
                succeed = false;
            }
            if ( succeed ) {
                document.execCommand('copy');
                window.getSelection().removeAllRanges();
                $btn.text( "Copied!" );
                ta.remove();
            } else {
                alert("Error!");
                window.getSelection().removeAllRanges();
                ta.remove();
            }
            //cleanning the highlighted text

        });


        // TICKET ACTIONS
        // ajax load of new responses

        $(document).on("click", "#ttls_load_more_responses", function( e ){
            e.preventDefault();
            var link = $(this).attr("href");
            if ( !link ) {
                return false;
            }
            $(this).attr("href", "");
            $(this).text("Loading...");

            jQuery.get( link, function(response) {
                history.pushState(null, null, link );
                var content = "<div>"+response+"</div>";
                var new_pagination = $(content).find(".ttls__tickets-controls .pagination").html();
                $(".ttls__tickets-controls .pagination").html( new_pagination );
                var elems = $(content).find(".ttls__tickets-responses").find("li").not("li li");
                elems.css("opacity", 0 ).css("left", 50);
                var i = 0;
                elems.each(function(){
                    $(".ttls__tickets-responses").append( $(this) );

                    $(this).delay(i*100).animate({
                        opacity: 1,
                        left: 0
                    }, i*100);
                    i++;
                });
            });

            return false;
        });

        // TICKET ACTIONS
        // ticket response
        $(document).on("submit","form.ttls-send-response",function(){
            var $thisForm = $(this);
            $thisForm.addClass("ttls__disabled");
            var data = {
                action: "ttls_add_response",
                fields: $thisForm.serialize(),
            };
            jQuery.post( ajaxurl, data, function( response ) {
                $thisForm.removeClass("ttls__disabled");
                if ( response.success ) {
                    $thisForm.find(".ttls_attachment").remove();
                    CKEDITOR.instances["ttls-ckeditor"].setData('');
                    ttlsAddAlert( "success", "", response.data.message );
                    if ( getParameterByName("order") == "DESC" ) {
                        $(".ttls__tickets-responses").prepend( response.data.box );
                    } else {
                        $(".ttls__tickets-responses").append( response.data.box );
                    }
                    wp.heartbeat.connectNow();
                } else {
                    ttlsAddAlert( "danger", "",response.data.message );
                }
            });
            return false;
        });

        // TICKET ACTIONS
        // add ticket
        
        $(document).on("submit","form.ttls-send-ticket",function(e){
			e.preventDefault();
			var form = e.target;
			var formData = new FormData( form );
			var action = 'ttls_add_ticket';
			var attachments = $('#ttls-mailbox-attachments');
	
			$(form).addClass('ttls__disabled');
	
			formData.append( 'action', action );
			formData.delete('ttls_attachment');
			TTLS.attachments.forEach( function( el ) {
				formData.append( 'ttls_attachment[]', el, el.name );
			} );
			$.ajax( {
				type : 'post',
				url : ajaxurl,
				data : formData,
				processData : false,
				contentType : false
			} ).done( function( response ) {
				if ( response.data ) {
					if( response.success && response.data.ticket_url ) {
						window.location.replace( response.data.ticket_url );
					} else if ( response.data.html ) {
						var formUpdate = $( response.data.html );
						$('#ttls-mailbox-attachments', formUpdate).replaceWith( attachments );
						$( form ).replaceWith( formUpdate );
						ttlsInitCkeditor(formUpdate.find('#ttls-ckeditor'));
					}
				}
			} ).always( function( response ) {
                if ( ! response.success ) {
                    ttlsHanldeAjaxError( response, form );
                }
			} );
        });

        // TICKET ACTIONS
        // add ticket attachments

		$(document).on( 'change', '#ttls-add-ticket-attachment', function (e) {
			var input = e.target;
			for (var i = 0, numFiles = input.files.length; i < numFiles; i++) {
				var newFile = input.files[i];				
				var compare = TTLS.attachments.filter( File => File.name === newFile.name );
				if ( ! compare.length ) {
					TTLS.attachments.push( newFile );
					var fileSize;
					if ( newFile.size >= 1000 ) {
						if ( newFile.size >= 1000000 ) {
							fileSize = Math.ceil( newFile.size / 1000000 ) + ' MB';
						} else {
							fileSize = Math.ceil( newFile.size / 1000) + ' KB';
						}
					} else {
						fileSize = Math.ceil(newFile.size) + ' B';
					}
					var fileName = newFile.name.length > 20 ? newFile.name.substr( 0, 20) + '...' : newFile.name;
					var attachmentBox = $( '.ttls-attachment-template' ).clone().removeClass( 'hidden ttls-attachment-template' );
					attachmentBox.find( '.size' ).text( fileSize );
					attachmentBox.find( '.title' ).text( fileName );
					attachmentBox.find( '.ttls-ticket-attachment-delete' ).data( 'file-name', newFile.name );
					attachmentBox.prependTo( '#ttls-mailbox-attachments' );
				}
			}
			input.value = '';
		} );
	
        // TICKET ACTIONS
        // delete ticket attachments

		
		$(document).on( 'click', '.ttls-ticket-attachment-delete', function (e) {
			e.preventDefault();
			var el = $( e.target ).closest( 'a' );
			var fileName = el.data( 'file-name' );
			el.parents( 'li' ).remove();
			for (var i = 0; i < TTLS.attachments.length; i++) {
				if ( TTLS.attachments[i].name === fileName ) {
					TTLS.attachments.splice( i, 1 );
				}
			}
		} );

        // SETTINGS ACTIONS
        // generate a code for product support inclusion via client plugin
        $(document).on("submit","form.ttls-settings-generator",function(){
            var $thisForm = $(this);
            var $thisFormStatus = $(this).find(".modal-footer");

            $thisForm.find("*").removeClass("has-error");
            $thisFormStatus.find(".text-danger").remove();
            $thisForm.find(".errors").remove();

            var data = {
                action: "ttls_settings_generate",
                fields: $thisForm.serialize(),
            };
            jQuery.post( ajaxurl, data, function( response ) {
                if ( response.success ) {
                    $thisFormStatus.prepend( "<span class=\"help-block success\">"+response.data.message+"</span>" );
                    var copy_btn_text = $thisForm.find("button[type=\"submit\"]").data("copy_text");
                    $thisForm.find("button[type=\"submit\"]").after("<a class=\"btn btn-dark ttls_copy_form_result\">"+copy_btn_text+"</a>");
                    $thisForm.find("button[type=\"submit\"]").remove();

                } else {
                    $.each( response.data.errors, function( key, message){
                        $thisForm.find("*[name="+key+"]").parents(".form-group")
                            .addClass("has-error")
                            .append("<span class=\"error text-danger\">"+message+"</span>");
                    } );
                    if ( $thisFormStatus.find(".success").length ) {
                        $thisFormStatus.find(".success").prepend( "<span class=\"error text-danger\">"+response.data.message+"</span>" );
                    } else {
                        $thisFormStatus.prepend( "<span class=\"error text-danger\">"+response.data.message+"</span>" );
                    }

                }
            });
            return false;
        });


        // LICENSE ACTIONS
        // adding license to an existing user
        $(document).on("submit","form.ttls-license-add",function(){

            var $thisForm = $(this);
            var user = $thisForm.find("*[name=\"user\"]").val();
            var $thisFormStatus = $(this).find(".modal-footer");

            $thisForm.find("*").removeClass("has-error");
            $thisFormStatus.find(".text-danger").remove();
            $thisForm.find(".errors").remove();

            var data = {
                action: "ttls_add_license",
                fields: $thisForm.serialize(),
            };
            jQuery.post( ajaxurl, data, function( response ) {
                if ( response.success ) {
                    if ( $thisFormStatus.find(".success").length ) {
                        $thisFormStatus.find(".success").append("<br>"+response.data.message);
                    } else {
                        $thisFormStatus.prepend( "<span class=\"help-block success\">"+response.data.message+"</span>" );
                    }
                    if ( $(".ttls__users table").length ) {
                        $(".ttls__users table").find(".ttls_user_label[data-user="+user+"]").eq(0)
                            .attr("rowspan", function( ind, old ){
                                return parseInt(old)+1;
                            } );
                        $(".ttls__users table").find(".ttls_user_label[data-user="+user+"]").last().parent().before( response.data.box );
                    }
                    if ( $(".ttls__license table").length ) {
                        $(".ttls__license table").find("tbody").prepend( response.data.box );
                    }

                    var copy_btn_text = $thisForm.find("button[type=\"submit\"]").data("copy_text");
                    $thisForm.find("button[type=\"submit\"]").after("<a class=\"btn btn-dark ttls_copy_form_result\">"+copy_btn_text+"</a>");
                    $thisForm.find("button[type=\"submit\"]").remove();

                } else {
                    $.each( response.data.errors, function( key, message){
                        $thisForm.find("*[name="+key+"]").parents(".form-group")
                            .addClass("has-error")
                            .append("<span class=\"error text-danger\">"+message+"</span>");
                    } );
                    if ( $thisFormStatus.find(".success").length ) {
                        $thisFormStatus.find(".success").prepend( "<span class=\"error text-danger\">"+response.data.message+"</span>" );
                    } else {
                        $thisFormStatus.prepend( "<span class=\"error text-danger\">"+response.data.message+"</span>" );
                    }

                }
            });
            return false;
        });

        // LICENSE ACTIONS
        // CLIENT ACTIONS
        // create a user and add license
        $(document).on("submit","form.ttls-new-client",function(){
            var $thisForm = $(this);
            var $thisFormStatus = $(this).find(".modal-footer");

            $thisForm.find("*").removeClass("has-error");
            $thisFormStatus.find(".text-danger").remove();
            $thisForm.find(".errors").remove();

            var data = {
                action: "ttls_create_client",
                fields: $thisForm.serialize(),
            };
            jQuery.post( ajaxurl, data, function( response ) {
                if ( response.success ) {
                    $thisFormStatus.prepend( "<span class=\"help-block success\">"+response.data.message+"</span>" );

                    $thisForm.removeClass("ttls-new-client").addClass("ttls-license-add");
                    $thisForm.find("button[type=\"submit\"]").text('Add License');
                    $thisForm.find("*[name=email]").attr("disabled", "");
                    $thisForm.find("*[name=name]").attr("disabled", "");
                    $thisForm.find("*[name=login]").attr("disabled", "")
                        .after("<input type=\"hidden\" name=\"user\" value="+response.data.user+">");

                    $thisForm.find("select.ttls_license_form_changer").attr('disabled', true);
                    $thisForm.trigger("submit");
                } else {
                    $.each( response.data.errors, function( key, message){
                        $thisForm.find("*[name="+key+"]").parents(".form-group")
                            .addClass("has-error")
                            .append("<span class=\"errors text-danger\">"+message+"</span>");
                    } );
                    $thisFormStatus.prepend( "<span class=\"errors text-danger\">"+response.data.message+"</span>" );
                }
            });
            return false;
        });

        // LICENSE ACTIONS
        // filter by product

        $(document).on("change", ".ttls_license_filter_product", function() {
            var url = $(this).val();
            if(url){
                window.location.href = url;
            }
        });

        // LICENSE ACTIONS
        // show the selected, and hide all other
        // fields of the license, as well as disabling all inactive
        // licenses in order to avoid overwriting the selected one
        $(document).on("change", ".ttls_license_select_type", function(){
            var $all = $(this).children("option[data-bs-target]");
            var $form = $(this).parents("form");
            $all.each(function(){
                if ( $(this).data('bs-target') ) {
                    if ( $(this).is(":selected") ) {
                        $form.find( $(this).data('bs-target') ).collapse('show');
                        $form.find( $(this).data('bs-target') ).find("*").attr("disabled", false );
                    } else {
                        $form.find( $(this).data('bs-target') ).collapse('hide');
                        $form.find( $(this).data('bs-target') ).find("*").attr("disabled", true );
                    }
                }
            });
        });

        // LICENSE ACTIONS
        // change the form from adding licenses to adding users
        // and vice versa
        $(document).on("change", ".ttls_license_form_changer", function(){
            var $thisForm = $(this).parents("form");
            if ( !$(this).val() ) {
                $(".ttls__license-newuser").collapse('show');
                $(".ttls__license-newuser").find("*").attr("disabled", false );
                $thisForm.removeClass("ttls-license-add").addClass("ttls-new-client");
                $thisForm.find("button[type=\"submit\"]").text( $thisForm.find("button[type=\"submit\"]").data("createuser") );



                $thisForm.find(".modal-title").text( $thisForm.find(".modal-title").data("createuser") );



            } else {
                $(".ttls__license-newuser").collapse('hide');
                $(".ttls__license-newuser").find("*").attr("disabled", true );
                $thisForm.removeClass("ttls-new-client").addClass("ttls-license-add");
                $thisForm.find("button[type=\"submit\"]").text( $thisForm.find("button[type=\"submit\"]").data("addlicense") );
                $thisForm.find(".modal-title").text( $thisForm.find(".modal-title").data("addlicense") );
            }
        });



        // LICENSE ACTIONS
        // user form fields for
        // adding license popup
        $(document).on("click", ".ttls_add_license", function(){
            var userlogin = $(this).data("userlogin");
            var userid = $(this).data("userid");
            $("#ttls_new_license_user").val(userid);
            $("#ttls_new_license_user_login").val(userlogin);
        });


        // LICENSE ACTIONS
        // delete license
        $(document).on("click", ".ttls_delete_license", function(){
            if( ! window.confirm( TTLSL['license_delete_confirmation'] ) ) {
                return false;
            }
            var $line = $(this).parents(".ttls_license_row");
            var license = $(this).data("license");
            var data = {
                action: "ttls_delete_license",
                license: license,
            };
            jQuery.post( ajaxurl, data, function(response) {
                if ( response.success ) {
                    if ( $(".ttls__users").length ) {

                        if ( $line.find(".ttls_user_label").is(":visible") ) {
                            if ( $line.find(".ttls_user_label").data("user") == $line.next().find(".ttls_user_label").data("user") ) {
                                var rowspan_u = $line.find(".ttls_user_label").attr( "rowspan" )-1;
                                $line.next().find(".ttls_user_label").attr("rowspan", rowspan_u ).show();

                            }
                        } else {
                            $line.parents("table")
                                .find(".ttls_user_label[data-user="+$line.find(".ttls_user_label").data("user")+"]")
                                .eq(0).attr("rowspan", function( ind, old ){
                                    return parseInt(old)-1;
                                } );

                        }

                        if ( $line.find(".ttls_license_label").is(":visible") ) {
                            var rowspan_l = $line.find(".ttls_license_label").attr( "rowspan" )-1;
                            if ( rowspan_l > 0) {
                                $line.next().find(".ttls_license_label").attr("rowspan", rowspan_l ).show();
                            }
                        } else {
                            $line.parents("table")
                                .find(".ttls_license_label[data-license="+$line.find(".ttls_license_label").data("license")+"][data-user="+$line.find(".ttls_license_label").data("user")+"]")
                                .eq(0).attr("rowspan", function( ind, old ){
                                    return parseInt(old)-1;
                                } );
                        }

                    }



                    $line.remove();
                } else {
                    ttlsAddAlert( "danger", "",response.data.message );
                }
            });
        });


        // LICENSE ACTIONS
        // form fields with editing license popup
        var userLicenseModalEl = document.getElementById('ttlsLicense');
        var userLicenseModal = false;
        if(userLicenseModalEl){
            userLicenseModal = new bootstrap.Modal(userLicenseModalEl);
        }

        $(document).on("click", ".ttls_edit_license_link", function(){
            var $modal = $("#ttlsLicense");
            if(userLicenseModal){
                userLicenseModal.show();
            }
            $modal.find(".modal-dialog").addClass("ttls__disabled");
            var $modalBody = $modal.find(".modal-content")
            var license = $(this).data("license");

            var data = {
                action: "ttls_edit_license",
                license: license,
            };
            jQuery.post( ajaxurl, data, function(response) {
                $modal.find(".modal-dialog").removeClass("ttls__disabled");
                $modalBody.html( response );
                $(".ttls__datepicker").datepicker({
                    autoclose: true,
                    todayHighlight: true,
                    dateFormat: 'yy-mm-dd'
                });
            });
        });

        // LICENSE ACTIONS
        // change URL status - allow, forbid
        $(document).on("click", ".ttls_url_move", function(){
            var $btn = $(this);
            var $line = $btn.parent().parent();
            var data = {
                action: "ttls_move_url",
                url: $btn.data("url"),
                license: $btn.data("license"),
                mode: $btn.data("mode"),
            };
            jQuery.post( ajaxurl, data, function(response) {
                if ( response.success ) {
                    $line.after( response.data.box );
                    $line.remove();
                } else {
                    ttlsAddAlert( "danger", "",response.data.message );
                }
            });
        });

        // LICENSE ACTIONS
        // change server status - allow, forbid
        $(document).on("click", ".ttls_server_move", function(){
            var $btn = $(this);
            var $line = $btn.parent().parent();
            var data = {
                action: "ttls_move_server",
                server: $btn.data("server"),
                license: $btn.data("license"),
                mode: $btn.data("mode"),
            };
            jQuery.post( ajaxurl, data, function(response) {
                if ( response.success ) {
                    $line.after( response.data.box );
                    $line.remove();
                } else {
                    ttlsAddAlert( "danger", "",response.data.message );
                }
            });
        });

        // LICENSE ACTIONS
        // Product Dynamic Licenses

        $(document).on('change', '.ttls-license-select', function(){
            var form = $(this).parents('form');
            var target = $(this).val();
            var selector = '.ttls-license-fields-' + target;
            $(selector, form).removeClass('collapse').find('input, textarea, select').prop('disabled', false);
            $(selector, form).siblings().addClass('collapse').find('input, textarea, select').prop('disabled', true);
        });

        $(document).on('click', '.ttls-license-field-checkbox', function(){
            var target = '#' + $(this).val();
            var disabled = ! $(this).prop('checked');
            $(target).prop('disabled', disabled).parents('.form-group').toggleClass('collapse');
        });

        // LICENSE ACTIONS
        // Add/Save Product License

        $(document).on('click', '.ttls-product-save-btn', function(){
            var form = $(this).parents('.modal-content').find('form');
            form.trigger('submit');
        });
    
        $(document).on('submit', '.ttls-client-save-product-form', function(e){
            e.preventDefault();
            var form = $(this);
            var modal = form.parents('.modal');
            var modalDialog = $('.modal-dialog', modal);
            var data = form.serializeArray();
            data = addCheckboxesValues(data, form);
            var action = 'ttls_save_product';
            modalDialog.addClass('ttls__disabled');
            data.push( {
                'name' : 'action',
                'value' : action,
            } );
    
            $.post( {
                'url' : ajaxurl,
                'data' : data,
            }).done( function( response ) {
                if ( response.success ) {
                    modal.modal('hide');
                    $( modal ).one('hidden.bs.modal', function(){
                        var container = $('#ttls-container');
                        if ( response.data.html !== undefined ) {
                            container.addClass('ttls__disabled').replaceWith( response.data.html  );
                        }                
                    });
                    wp.heartbeat.connectNow();
                } else {
                    if ( response.data.html !== undefined ) {
                        var newForm = $(response.data.html).find('form');
                        if ( newForm.length ) {
                            form.replaceWith( newForm );
                        }
                    }
                }
            }).fail( function( response ) {
                alert('Ajax Error');
            }).always( function(){
                modalDialog.removeClass('ttls__disabled');
            });
        });
    
        // WIDGET ACTIONS
        // save widget positions - after moving
        $(document).on("submit","form.ttls-widget-area",function(){
            var $thisForm = $(this);
            var data = {
                action: "ttls_widget_save_position",
                fields: $thisForm.serialize(),
            };
            jQuery.post( ajaxurl, data, function( response ) {
                if ( !response.success ) {
                    ttlsAddAlert( "danger", "",response.data.message );
                }
            });
            return false;
        });

        // LICENSE ACTIONS
        // save licensing options while editing
        $(document).on("submit","form.ttls-license-control",function(){
            var $thisForm = $(this);
            $thisForm.addClass("ttls__disabled");
            $thisForm.find(".error").remove();
            $thisForm.find(".modal-footer").find("span").remove();
            $thisForm.find(".has-error").removeClass("has-error");
            var data = {
                action: "ttls_update_license",
                fields: $thisForm.serialize(),
            };
            jQuery.post( ajaxurl, data, function( response ) {
                $thisForm.removeClass("ttls__disabled");
                if ( response.success ) {
                    $thisForm.find(".modal-footer").prepend("<span class=\"text-success\">"+response.data.message+"</span>");
                } else {
                    $.each( response.data.errors, function(key, message){
                        $thisForm.find("*[name=\""+key+"\"]").parents(".form-group")
                            .addClass("has-error")
                            .append("<span class=\"help-block error\">"+message+"</span>");
                    });
                    $thisForm.find(".modal-footer").prepend("<span class=\"text-danger\">"+response.data.message+"</span>");
                }
            });
            return false;
        });


        // TICKET ACTIONS
        // accept a ticket from widget
        $(document).on("click",".ttls_widget_take_ticket",function(){
            var $thisRow = $(this).parent().parent();
            var $btn = $(this);
            $thisRow.addClass("ttls__disabled");
            var data = {
                action: "ttls_take_ticket",
                ticket: $btn.data('ticket'),
            };
            jQuery.post( ajaxurl, data, function( response ) {
                $thisRow.removeClass("ttls__disabled");
                if ( response.success ) {
                    ttlsAddAlert( "success", "", response.data.message );
                    $thisRow.removeClass( 'ttls_free_ticket' );
                    $btn.removeClass('ttls_widget_take_ticket')
                        .attr( 'href', response.data.link )
                        .text( response.data.button );
                } else {
                    ttlsAddAlert( "danger", "",response.data.message );
                }
            });
            return false;
        });

        // TICKET ACTIONS
        // change status, developer ticket
        // close ticket
        $(document).on("submit","form.ttls-ticket-control",function(){
            var $thisForm = $(this);
            $thisForm.addClass("ttls__disabled");
            var data = {
                action: "ttls_update_ticket",
                fields: $thisForm.serialize(),
            };
            jQuery.post( ajaxurl, data, function( response ) {
                $thisForm.removeClass("ttls__disabled");
                ttlsUpdateTicketResponseHandler( response );
            });
            return false;
        });

        // TICKET ACTIONS
        // close/reopen ticket for client
        $(document).on("click",".ttls-client-ticket-edit",function(e){
	        e.preventDefault();
	        var btn = $(this);
            var $thisForm = btn.find('form');
            $("#ttls-container").addClass("ttls__disabled");
            var data = $thisForm.serializeArray();
			data.push( {
				'name' : 'action',
				'value' : 'ttls_client_edit_ticket',
			} );
            jQuery.post( ajaxurl, data, function( response ) {
	            if( response.data.new_status !== undefined ) {
		            $("input[name='status']", $thisForm).val(response.data.new_status);
	            }
	            if( response.data.new_status_text !== undefined ) {
		            btn.find("span").text(response.data.new_status_text);
	            }
                $("#ttls-container").removeClass("ttls__disabled");
                ttlsUpdateTicketResponseHandler( response );
            });
            return false;
        });


        // DEVELOPER EDIT
        // form fields for editing developer popup
        $(document).on("click", ".ttls_edit_developer", function(){
            var $modal = $("#ttlsEditDeveloper");
            $modal.find('.modal-dialog').addClass("ttls__disabled");
            var $modalBody = $modal.find(".modal-content")
            var developer = $(this).data("developer");

            var data = {
                action: "ttls_edit_developer",
                developer: developer,
            };
            jQuery.post( ajaxurl, data, function(response) {
                $modal.find('.modal-dialog').removeClass("ttls__disabled");
                $modalBody.html( response );
            });
        });

        // DEVELOPER EDIT
        // save value
        $(document).on("submit","form.ttls-developer-settings",function(){
            var $thisForm = $(this);
            $thisForm.find(".has-error").removeClass("has-error");
            $thisForm.find(".help-block.error").remove();
            $thisForm.find(".modal-footer").find("span").remove();

            $thisForm.addClass("ttls__disabled");
            var data = {
                action: "ttls_update_developer",
                fields: $thisForm.serialize(),
            };
            jQuery.post( ajaxurl, data, function( response ) {
                $thisForm.removeClass("ttls__disabled");
                if ( response.success ) {
                    $thisForm.find(".modal-footer").prepend("<span class=\"text-success\">"+response.data.message+"</span>");
                } else {
                    $thisForm.find(".modal-footer").prepend("<span class=\"text-danger\">"+response.data.message+"</span>");
                    $.each( response.data.errors, function(key, message){
                        $thisForm.find("*[name=\""+key+"\"]").parents(".form-group")
                            .addClass("has-error")
                            .append("<span class=\"help-block error\">"+message+"</span>");
                    });
                }
            });
            return false;
        });

        // open WordPress loader for attaching images
        $(document).on('click', '.ttls_add_image', function(){
            var send_attachment_bkp = wp.media.editor.send.attachment;
            var button = $(this);
            wp.media.editor.send.attachment = function(props, attachment) {
                $(button).attr('title', 'Replace');
                $(button).find("*[name=image]").val(attachment.id).trigger('change');
                $(button).find("img").attr('src', attachment.url).fadeIn(300);
                if ( !$(button).find(".fa-redo").length ) {
                    $(button).find("img").after("<span class=\"fa fa-redo\"></span>");
                }
                wp.media.editor.send.attachment = send_attachment_bkp;
            }
            wp.media.editor.open(button);
            return false;
        });

        // DEVELOPER EDIT
        // activate change password
        $(document).on('click', '.ttls_activate_password_changing', function(){
            var $btn = $(this);
            var $group = $btn.parents('.form-group');
            var $field = $group.find('input');
            if ( $field.attr('disabled') ) {
                $field.val( $field.data('password') ).attr( 'disabled', false );
                $btn.removeClass('btn-info').addClass('btn-warning').text( $btn.data("cancel") );
            } else {
                $field.val('').attr( 'disabled', true );
                $btn.removeClass('btn-warning').addClass('btn-info').text( $btn.data("change") );
            }
        });


        // CREATE DEVELOPER
        // create a new developer
        $(document).on("submit","form.ttls-new-developer",function(){
            var $thisForm = $(this);
            $thisForm.find(".has-error").removeClass("has-error");
            $thisForm.find(".help-block.error").remove();
            $thisForm.find(".modal-footer").find("span").remove();

            $thisForm.addClass("ttls__disabled");
            var data = {
                action: "ttls_create_developer",
                fields: $thisForm.serialize(),
            };
            jQuery.post( ajaxurl, data, function( response ) {
                $thisForm.removeClass("ttls__disabled");
                if ( response.success ) {
                    $thisForm.find(".modal-footer").prepend("<span class=\"help-block success\">"+response.data.message+"</span>");
                    var copy_btn_text = $thisForm.find("button[type=\"submit\"]").data("copy_text");
                    $thisForm.find("button[type=\"submit\"]").after("<a class=\"btn btn-dark ttls_copy_form_result\">"+copy_btn_text+"</a>");
                    $thisForm.find("button[type=\"submit\"]").remove();
                } else {
                    $thisForm.find(".modal-footer").prepend("<span class=\"text-danger\">"+response.data.message+"</span>");
                    $.each( response.data.errors, function(key, message){
                        $thisForm.find("*[name=\""+key+"\"]").parents(".form-group")
                            .addClass("has-error")
                            .append("<span class=\"help-block error\">"+message+"</span>");
                    });
                }
            });
            return false;
        });

        if ($(".ttls__widget-freeTickets").length) {
            setInterval(function () {
              // indefinite cycle every 3 seconds
                $(".ttls__widget-freeTickets").find(".toDelete").remove();
                var data = {
                    action: "ttls_widget_check_free",
                }
                jQuery.post(ajaxurl, data, function (response) {
                    var status = $(".ttls__status");
                    if (response.success) {
                        $(".ttls__widget-freeTickets").find(".ttls_no_free_ticket").remove();
                        $(".ttls__widget-freeTickets")
                            .find(".ttls_free_ticket")
                            .addClass("toDelete");
                        $.each(response.data.tickets, function (key, box) {
                            var $row = $(".ttls__widget-freeTickets").find(
                            ".toDelete[data-ticket=" + key + "]"
                            );
                            if ($row.length) {
                                $row.removeClass("toDelete new");
                            } else {
                            $(".ttls__widget-freeTickets")
                                .find("tbody")
                                .append($(box).addClass("new"));
                            }
                        })
                        $(".ttls__widget-freeTickets")
                            .find(".toDelete")
                            .css("background-color", "rgba(255, 170, 51, 0.25)");
                    } else {
                        $(".ttls__widget-freeTickets").find(".ttls_free_ticket").remove();
                        if (
                            !$(".ttls__widget-freeTickets").find(".ttls_no_free_ticket").length
                        ) {
                            $(".ttls__widget-freeTickets")
                            .find("tbody")
                            .append(
                                '<tr class="ttls_no_free_ticket"><td colspan="3">' +
                                response.data.message +
                                "</td></tr>"
                            );
                        }
                    }
                });
            }, 3000); // indefinite cycle end
        }
          
        if ( $(".ttls__status").length ) {

            setInterval( function() { // indefinite cycle every 3 seconds

                // check ticket for current status
                var data = {
                    action: "ttls_check_ticket_status",
                    ticket: $(".ttls__status").data("ticket"),
                    prev_status:$(".ttls__status").data("status"),
                    prev_dev:$(".ttls__status").data("dev"),
                };
                jQuery.post( ajaxurl, data, function(response) {
                    var status = $(".ttls__status");
                    if ( response.success ) {
                        status.after( response.data.box );
                        status.remove();
                        ttlsAddAlert( "info", "", response.data.message );
                    }
                });

            }, 3000); // indefinite cycle end

        }
        
        $(document).on('heartbeat-tick', function(e, data){
		    var countEl = $('.ttls__pending-tickets-count');
		    if(!countEl.length) return;
	
		    if(data.ttls_tickets_count){
		        countEl.text(data.ttls_tickets_count).removeClass('count-0');
		    } else {
			    countEl.addClass('count-0');
		    }
        });

        /* ============ Pending Counters ============ */

        $(document).on('heartbeat-tick', function(e, data){
            if ( data.ttls_pending_counts ) {
                updatePendingCounts(data.ttls_pending_counts);
            }
        });

        function updatePendingCount(selector, value) {
            var countEl = $(selector);
            if(!countEl.length) return;

            if(value){
                    countEl.text(value).removeClass('count-0');
            } else {
                countEl.addClass('count-0');
            }
    }

    function updatePendingCounts(counts){
        $.each(counts, function(index, count){
            updatePendingCount(count.selector, count.value);
        });
    }
    function getCheckboxValue(index, el){
        return {name: el.name, value: el.checked ? 'y' : '' };
    }
    
    function addCheckboxesValues(data, form){
        var checkboxesData = $('input:checkbox', form).map(getCheckboxValue);
        checkboxesData.each(function(index, el){
            if ( ! el.value ) {
                data.push(this);
            }
        });
        return data;
    }
        
}(jQuery));

function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return "";
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

function ttlsAddAlert( type, title, message ){ // warning | info | success | danger
    var alert = "<div role=\"alert\" class=\"alert alert-" + type + " alert-dismissible\" style=\"display: none;\">" +
            "<button type=\"button\" data-bs-dismiss=\"alert\" aria-label=\"Close\" class=\"close\">" +
            "<span aria-hidden=\"true\">&times;</span></button>" +
            "<strong>" + title + "</strong> " + message + "</div>";
    jQuery(alert).prependTo( ".ttls__alerts" ).fadeIn(500).delay(2000).fadeOut(400).queue(function() { jQuery(this).remove(); });
}

function ttlsUpdateTicketResponseHandler( response ) {
	if ( response.success ) {
        wp.heartbeat.connectNow();
	    if ( getParameterByName("order") == "DESC" ) {
	        jQuery(".ttls__tickets-responses").prepend( response.data.box );
	    } else {
	        jQuery(".ttls__tickets-responses").append( response.data.box );
	    }
	} else {
	    ttlsAddAlert( "danger", "",response.data.message );
	}
	
}

function ttlsHanldeAjaxError( response, form ) {
	jQuery(form).removeClass('ttls__disabled');
	var errorMessage = "Error";
	if ( response.data.message !== undefined ) {
		errorMessage = response.data.message;
	}
	ttlsAddAlert( "danger", "", errorMessage );
}

function ttlsInitCkeditor(ckeditor) {
	if ( ckeditor.length ) {
		CKEDITOR.replace( ckeditor.attr('id') );
	}
	
}

