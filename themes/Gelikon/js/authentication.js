/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
$(document).ready(function(){
	$(document).on('submit', '#create-account_form', function(e){
		e.preventDefault();
		submitFunction();
	});
//        $(document).on('submit', '#create-account_form_nav, #create-account_form_nav_min', function(e){
//
//            return false
//            var id_form = $(this).attr('id'),email,pass;
//            if(id_form == 'create-account_form_nav'){
//                email = $('#email_create_nav').val();
//                pass = $('#password_nav').val();
//            }else{
//                email = $('#email_create_nav_min').val();
//                pass = $('#password_nav_min').val();
//            }
//            if(!email)
//                email = 'test';
//            if(pass){
//                alert('234');
//                return true;
//            }else{
////                submitFunctionNav(email);
//            }
//            return false
//            alert(email + ' ' + pass);
//            
//        });
        
        
	$('.is_customer_param').hide();
        $('#SubmitCreate, #SubmitCreateMin').click(function(e){
           
            
            e.preventDefault();
            $('#create_account_error').html('').hide();
            var id_form = $(this).attr('id'),email,pass;
            if(id_form == 'SubmitCreate'){
                email = $('#email_create_nav').val();
                pass = $('#password_nav').val();
            }else{
                email = $('#email_create_nav_min').val();
                pass = $('#password_nav_min').val();
            }
            

            $.ajax({
		type: 'POST',
		url: baseUri,
		async: true,
		cache: false,
		dataType : "json",
		data: 
		{
			controller: 'authentication',
			SubmitCreate: 1,
			ajax: true,
			email_create: email,
			back: $('input[name=back]').val(),
			token: token
		},
		success: function(jsonData)
		{
                                                            if(page_name == 'index'){
                                            $('.gk-panel-promo').remove();
//                                            var destination = 500;
//                                            $("body,html").animate({"scrollTop" : destination},1000);
//                                            if ($.browser.safari) {
//                                                $('body').animate({ scrollTop: destination }, 1100); //1100 - скорость
//                                            } else {
//                                                $('html').animate({ scrollTop: destination }, 1100);
//                                            }
                                        }
//			if (jsonData.hasError) 
//			{
//				var errors = '';
//				for(error in jsonData.errors)
//					//IE6 bug fix
//					if(error != 'indexOf')
//						errors += '<li>' + jsonData.errors[error] + '</li>';
//				$('#create_account_error').html('<ol>' + errors + '</ol>').show();
//			}
//			else
//			{
//                              
				// adding a div to display a transition
				$('#center_column').html('<div id="noSlide">' + $('#center_column').html() + '</div>');
				$('#noSlide').fadeOut('slow', function()
				{
					$('#noSlide').html(jsonData.page);
					$(this).fadeIn('slow', function()
					{
						if (typeof bindUniform !=='undefined')
							bindUniform();
						if (typeof bindStateInputAndUpdate !=='undefined')
							bindStateInputAndUpdate();
//						document.location = '#account-creation';

					});
				});

                                //$("select, input").styler();
//			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown)
		{
			error = "TECHNICAL ERROR: unable to load form.\n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus;
			if (!!$.prototype.fancybox)
			{
			    $.fancybox.open([
		        {
		            type: 'inline',
		            autoScale: true,
		            minHeight: 30,
		            content: "<p class='fancybox-error'>" + error + '</p>'
		        }],
				{
			        padding: 0
			    });
			}
			else
			    alert(error);
		}
            });
        });
        
});



function submitFunction()
{
	$('#create_account_error').html('').hide();
	$.ajax({
		type: 'POST',
		url: baseUri,
		async: true,
		cache: false,
		dataType : "json",
		data: 
		{
			controller: 'authentication',
			SubmitCreate: 1,
			ajax: true,
			email_create: $('#email_create').val(),
			back: $('input[name=back]').val(),
			token: token
		},
		success: function(jsonData)
		{       
                        $("select, input").styler();
			if (jsonData.hasError) 
			{
				var errors = '';
				for(error in jsonData.errors)
					//IE6 bug fix
					if(error != 'indexOf')
						errors += '<li>' + jsonData.errors[error] + '</li>';
				$('#create_account_error').html('<ol>' + errors + '</ol>').show();
			}
			else
			{
				// adding a div to display a transition
				$('#center_column').html('<div id="noSlide">' + $('#center_column').html() + '</div>');
				$('#noSlide').fadeOut('slow', function()
				{
					$('#noSlide').html(jsonData.page);
					$(this).fadeIn('slow', function()
					{
						if (typeof bindUniform !=='undefined')
							bindUniform();
						if (typeof bindStateInputAndUpdate !=='undefined')
							bindStateInputAndUpdate();
						document.location = '#account-creation';
					});
				});
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown)
		{
			error = "TECHNICAL ERROR: unable to load form.\n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus;
			if (!!$.prototype.fancybox)
			{
			    $.fancybox.open([
		        {
		            type: 'inline',
		            autoScale: true,
		            minHeight: 30,
		            content: "<p class='fancybox-error'>" + error + '</p>'
		        }],
				{
			        padding: 0
			    });
			}
			else
			    alert(error);
		}
	});
}