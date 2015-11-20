function dhl_get_rates(location, is_cart)
{
	var vars = new Object();
	vars['ajax'] = true;
	
	vars['id_product'] = is_cart == ''?id_product:0;
	vars['id_product_attribute'] = is_cart == ''?$('#idCombination').val():0;
	vars['dhl_is_cart'] = is_cart;
	if (is_cart == '')
		vars['qty'] = $("#quantity_wanted").val();
	if (location)
	{
		if (!$("#dhl_dest_zip"+is_cart).val() && $("#dhl_dest_zip"+is_cart).val() != undefined && $("#dhl_dest_zip"+is_cart).attr('hide') != 1)
		{
			alert(dhl_invalid_zip);
			return;
		}
		vars['dhl_dest_zip'] = $("#dhl_dest_zip"+is_cart).val();
		vars['dhl_dest_state'] = $("#dhl_dest_state"+is_cart).val();
		vars['dhl_dest_country'] = $("#dhl_dest_country"+is_cart).val();
		vars['dhl_dest_city'] = $("#dhl_dest_city"+is_cart).val();
		$('#dhl_submit_location'+is_cart).html(dhl_please_wait);
		$('#dhl_submit_location'+is_cart).attr('disabled', 'disabled');
		$('#dhl_submit_location'+is_cart).unbind('click');
	}
	else
	{
		$('#dhl_shipping_rates_button'+is_cart).html(dhl_please_wait);
		$('#dhl_submit_location'+is_cart).unbind('click');
	}
	$.ajax({
			type: 'POST',
			url: baseDir + 'modules/dhl/dhl_preview.php',
			async: true,
			cache: false,
			dataType : "json",
			data: vars,
			success: function(json)
			{
				// add appliance to whishlist module
				/*
				$('#dhl_shipping_rates_button'+is_cart).html(dhl_hide);
				$('#dhl_shipping_rates_button'+is_cart).attr('onclick','');                
				$('#dhl_submit_location'+is_cart).attr('onclick', 'dhl_get_rates(true, "'+(is_cart?"_cart":"")+'")');
				
				$('#dhl_shipping_rates_button'+is_cart).unbind('click').click(function(){
					$('#dhl_shipping_rates'+is_cart).html('');
					$('#dhl_shipping_rates_button'+is_cart).html(dhl_show);
					$('#dhl_shipping_rates_button'+is_cart).show();
					$('#dhl_shipping_rates_button'+is_cart).unbind('click').click(function(){
						dhl_get_rates(false, is_cart);
						return false;
					});
					return false;
				});
				*/
				
				$('#dhl_shipping_rates_button'+is_cart).parent().hide();
				
				$('a[class="dhl_hide_button"]').live('click', function(){
					
					$('#dhl_shipping_rates'+is_cart).html('');
					$('#dhl_shipping_rates_button'+is_cart).html(dhl_show);
					
					$('#dhl_shipping_rates_button'+is_cart).parent().show();
					
					$('#dhl_shipping_rates_button'+is_cart).unbind('click').click(function(){
						dhl_get_rates(false, is_cart);
						return false;
					});
					return false;
				});
				
				$('#dhl_shipping_rates'+is_cart).html(json.dhl_rate_tpl);
				dhl_cart_need_refresh = true;
			},
			error: function ()
			{
				alert('Error!');
			}
	});
}

function dhl_change_carrier(id_carrier, dhl_radio)
{
	$.ajax({
		type: 'POST',
		url: baseDir + 'modules/dhl/dhl_preview.php',
		async: true,
		cache: false,
		data: {'new_id_carrier': id_carrier},
		success: function(json)
		{
			if (json == 0)
			{
				alert(dhl_cart_empty);
				dhl_radio.attr('checked', false);
			}
			else
			{
				if (typeof ajaxCart != 'undefined')
					ajaxCart.refresh();
				if (window.getCarrierListAndUpdate)
					getCarrierListAndUpdate();
			}
		}
	});
}

function dhl_city_display(duration, is_cart)
{
	var id_country = $('#dhl_dest_country' + is_cart).val();
	if(id_country == 21) //if USA
		$('.dhl_id_city' + is_cart).hide(duration);
	else
		$('.dhl_id_city' + is_cart).show(duration);
}

function dhl_define_hide_button(element, is_cart)
{
	element.hide();
	
	$('a[class="dhl_hide_button"]').live('click', function(){
		
		$('#dhl_shipping_rates'+is_cart).hide();
		
		element.html(dhl_show);
		element.show();
		
		$('#dhl_shipping_rates_button'+is_cart).unbind('click').click(function(){
			$('#dhl_shipping_rates' + is_cart).fadeIn(800);
			dhl_city_display(0, is_cart);
		});
		return false;
	});
}

var dhl_cart_need_refresh = false;

$(document).ready(function () {
	$('form[id="buy_block"]').keypress(function(e){
		if (e.keyCode == '13')
			e.preventDefault();
	});
	
	if (typeof ajaxCart != 'undefined' && $('#dhl_shipping_rates_cart').length)
	{
		var origrefresh = ajaxCart.overrideButtonsInThePage;
		ajaxCart.overrideButtonsInThePage = function() {
			origrefresh();
			if (dhl_cart_need_refresh)
			{
				$("a[class='dhl_hide_button']").click();
				dhl_cart_need_refresh = false;
			}
		}
	}
	if (typeof findCombination != 'undefined')
	{
		var origrfcomb = findCombination;
		findCombination = function() {
			origrfcomb();
			$("a[class='dhl_hide_button']").click();
		}
	}

	$('#dhl_dest_country_cart').live('change', function(){
		dhl_city_display(100, '_cart');
	});
	$('#dhl_dest_country').live('change', function(){
		dhl_city_display(100, '');
	});
});
