jQuery(function(){

	/*
	if(!jQuery.isFunction(jQuery.fn.on)){
		var jquery_version_error_message = 'The contact form requires jQuery 1.7.2 to work properly.<br />jQuery '+jQuery().jquery+' is loaded.';
		jQuery('.cfg-contactform').prepend('<p style="color:#FF0000">'+jquery_version_error_message+'</p>');
	}
	*/

	jQuery('#contactform .cfg-uploadfilename').val(''); // FF may keep the file name in the cfg-uploadfilename input after submitting and refreshing the page

	jQuery('#contactform .cfg-captcha-refresh').click(function(){

		$('#contactform .cfg-captcha-img').attr('src','contactform/inc/captcha.php?r=' + Math.random());
	});

	jQuery('#contactform .cfg-submit').click(function(){

		var formcontainer = jQuery(this).closest('.cfg-contactform');
		var loading = formcontainer.find('.cfg-loading');

		loading.show();

		var submit_btn =  jQuery(this);
		submit_btn.hide();

		formcontainer.find('.cfg-errormessage').hide().remove();

		var form_value_array = Array();
		var radio_value = Array();
		var checkbox_value = Array();
		var selectmultiple_value = Array();
		var deleteuploadedfile_value = Array();

		formcontainer.find('.cfg-form-value').each(function()
		{
			var elementlabel = jQuery(this).closest('.cfg-element-container').find('.cfg-label-value');
			var elementlabel_id = elementlabel.closest('label').attr('id');
			var elementlabel_value = elementlabel.html();



			// catch uploads
			if(jQuery(this).hasClass('cfg-uploadfilename'))
			{
				var key = jQuery(this).prop('name');
				var value =  jQuery.trim(jQuery(this).val());

				var deletefile = jQuery(this).closest('.cfg-element-content').find('.cfg-uploaddeletefile').val();

				form_value_array.push({'element_id': key, 'element_value': value, 'elementlabel_id':elementlabel_id, 'elementlabel_value':elementlabel_value, 'element_type':'upload', 'filename':value, 'element_type':'upload', 'deletefile':deletefile});
			}


			// catch input text values, textarea values, select values
			if(jQuery(this).is('.cfg-type-text, .cfg-type-textarea, .cfg-type-select'))
			{
				var key = jQuery(this).prop('id');
				var value = jQuery('#'+jQuery(this).prop('id')).val();
				form_value_array.push({'element_id': key, 'element_value': value, 'elementlabel_id':elementlabel_id, 'elementlabel_value':elementlabel_value});
			}


			// catch radiobutton values
			if(jQuery(this).is(':radio'))
			{
				var key = jQuery(this).prop('name');
				var value = jQuery(this).val();

				var check_index_radio_form_value = form_value_array.length+1;

				if(jQuery(this).is(':checked')){
					form_value_array.push({'element_id': key, 'element_value': value, 'elementlabel_id':elementlabel_id, 'elementlabel_value':elementlabel_value});
					radio_value[key] = value;
				}

				if( jQuery(this).is( jQuery(this).closest('.cfg-element-container').find('input[name='+key+']:last')) )
				{
					if(!radio_value[key]){
						form_value_array.push({'element_id': key, 'element_value': '', 'elementlabel_id':elementlabel_id, 'elementlabel_value':elementlabel_value});
					}
				}
			}


			// catch checkbox values
			if(jQuery(this).is(':checkbox'))
			{
				var key = jQuery(this).prop('name');
				var value = jQuery(this).val();

				if(jQuery(this).is(':checked'))
				{

					form_value_array.push({'element_id': key, 'element_value': value, 'elementlabel_id':elementlabel_id, 'elementlabel_value':elementlabel_value});

					checkbox_value[key] = value;

				}

				if( jQuery(this).is(jQuery(this).closest('.cfg-element-container').find('input[name='+key+']:last')))
				{
					// we are at the last checkbox, and the checkbox[name] array value is still empty => insert fieldname: '' in the notification
					if(!checkbox_value[key])
					{
						form_value_array.push({'element_id': key, 'element_value': '', 'elementlabel_id':elementlabel_id, 'elementlabel_value':elementlabel_value});
					}
				}
			}

			// catch multiple select values
			if(jQuery(this).hasClass('cfg-type-selectmultiple'))
			{
				var key = jQuery(this).prop('name'); // must be placed here, not in each() or php will return Undefined index: element_id

				jQuery(this).find('option').each(function()
				{
					var value = jQuery(this).val();

					if(jQuery(this).is(':selected'))
					{
						form_value_array.push({'element_id': key, 'element_value': value, 'elementlabel_id':elementlabel_id, 'elementlabel_value':elementlabel_value});
						selectmultiple_value[key] = value;
					}

					if( jQuery(this).is( jQuery(this).closest('.cfg-type-selectmultiple').find('option:last')) )
					{
						// we are at the last option, and the selectmultiple[name] array value is still empty => insert fieldname: '' in the notification
						if(!selectmultiple_value[key])
						{
							form_value_array.push({'element_id': key, 'element_value': '', 'elementlabel_id':elementlabel_id, 'elementlabel_value':elementlabel_value});
						}
					}

				});

			}

			// catch time values
			if(jQuery(this).hasClass('cfg-type-time'))
			{
				//var key = jQuery(this).find('.cfg-time-hour').prop('name');
				var key = jQuery(this).closest('.cfg-element-container').find('.cfg-time-hour').prop('name');
				var ampm = jQuery(this).closest('.cfg-element-container').find('.cfg-time-ampm').val();
				if(ampm == undefined) ampm = ''; // no quote on undefined
				var value = jQuery(this).closest('.cfg-element-container').find('.cfg-time-hour').val()+':'+jQuery(this).closest('.cfg-element-container').find('.cfg-time-minute').val()+' '+ampm;

				form_value_array.push({'element_id': key, 'element_value': value, 'elementlabel_id':elementlabel_id, 'elementlabel_value':elementlabel_value});
			}

		});


		// catch list of uploaded files to delete
		formcontainer.find('.cfg-deleteuploadedfile').each(function(){
			deleteuploadedfile_value.push(jQuery(this).val());
		});


		var captcha_img;
		var captcha_input;

		if(formcontainer.find('.cfg-captcha-img').length)
		{
			captcha_img = 1;
			captcha_input = formcontainer.find('.cfg-captcha-input').val();
		}


		// console.log(deleteuploadedfile_value);
		// console.log(form_value_array);


		jQuery.post('contactform/inc/form-validation.php',
				{
				'captcha_img':captcha_img,
				'captcha_input':captcha_input,
				'form_value_array':form_value_array,
				'deleteuploadedfile':deleteuploadedfile_value
				},
				function(data)
				{
					loading.hide();

					data = jQuery.trim(data);

					// 	console.log(data);

					response = jQuery.parseJSON(data);

					if(response['status'] == 'ok')
					{

						if(response['redirect_url'])
						{
							window.location.href = response['redirect_url'];
						} else
						{
							validation_message = '<div class="cfg-validationmessage">'+response['message']+'</div>';

							formcontainer.find('.cfg-element-container').each(function()
							{
								if(!jQuery(this).find('.cfg-title').html())
								{
									jQuery(this).slideUp('fast');
								}
							});

							jQuery('html, body').animate({scrollTop:formcontainer.offset().top}, 'fast');

							formcontainer.find('.cfg-contactform-content').append(validation_message);
						}

					} else
					{
						submit_btn.show();

						for(var i=0; i<response['message'].length; i++)
						{
							var optioncontainer = jQuery('[name*='+response['message'][i]['element_id']+']:first').closest('.cfg-element-content');

							jQuery('<div class="cfg-errormessage">'+response['message'][i]['errormessage']+'</div>').prependTo(optioncontainer).fadeIn();
						}

						// scrolls to the first error message
						jQuery('html,body').animate({scrollTop: jQuery('#'+response['message'][0]['elementlabel_id']).offset().top},'fast');

					}
				} /* end function data */
			); /* end jQuery.post */
	}); /* end click submit */


	// DELETE UPLOADED FILE
	jQuery('body').on('click', '#contactform .cfg-deleteupload', function()
	{
		var filename = jQuery(this).closest('.cfg-uploadsuccess-container').find('.cfg-deleteupload-filename').val();

		// to add the filename to the list of files to delete
		// // the .cfg-deleteuploadedfile input can also be added in case of chain upload (handlers.js)
		jQuery(this).closest('.cfg-element-content').append('<input value="'+filename+'" type="hidden" class="cfg-deleteuploadedfile" />');

		// reset the upload input that contains the filename value
		jQuery(this).closest('.cfg-element-content').find('.cfg-uploadfilename').val('');

		// must come last, jQuery(this) is used to access closest elements
		jQuery(this).closest('.cfg-uploadsuccess-container').remove();

	});


});

