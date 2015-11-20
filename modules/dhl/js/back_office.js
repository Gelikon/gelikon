$(function(){
	$('#ups_form select, #ups_form input').unbind('mouseover');

	$("#dhl_pack").bind("change", function(){
		if ($('#dhl_pack').val() != 'CP') {
			$('#dhl_my_pack').fadeOut(1200);
		}
		else {
			$('#dhl_my_pack').fadeIn(1200);
		}
	});
});

/*
vars invalid_site_id, invalid_pass, invalid_zip, module_path from dhl.php
*/
function validate_as()
{
	if ($("#dhl_key").val() == "")
	{
		alert(invalid_site_id);
		return false;
	}
	if ($("#dhl_pass").val() == "")
	{
		alert(invalid_pass);
		return false;
	}
	if ($("#dhl_origin_zip").val() == "")
	{
		alert(invalid_zip);
		return false;
	}
	return true;
}

function show_package_size()
{
	if ($("#dhl_packages_single").attr("checked"))
	{
		$("#dhl_package_size_product").attr('disabled',true);
		$("#dhl_package_size_fixed").attr("checked",true);
	}
	else
		$("#dhl_package_size_product").attr('disabled',false);
		
	if ($("#dhl_packages_single").attr("checked") && $("#dhl_package_size_fixed").attr("checked"))
	{
		$('#product_box').fadeOut('fast');
		$('#fixed_multi_box').fadeOut('fast');
		$('#fixed_box_multiple').fadeOut('fast');
		$('#fixed_box').fadeIn('fast');
	}
	else if ($("#dhl_packages_single").attr("checked") && $("#dhl_package_size_product").attr("checked"))
	{
		$('#fixed_box_multiple').fadeOut('fast');
		$('#fixed_multi_box').fadeOut('fast');
		$('#fixed_box').fadeOut('fast');
		$('#product_box').fadeIn('fast');
	}
	else if ($("#dhl_packages_multiple").attr("checked") && $("#dhl_package_size_product").attr("checked"))
	{
		$('#fixed_box_multiple').fadeOut('fast');
		$('#fixed_box').fadeOut('fast');
		$('#fixed_multi_box').fadeIn('fast');
		$('#product_box').fadeIn('fast');
	}
	else if ($("#dhl_packages_multiple").attr("checked") && $("#dhl_package_size_fixed").attr("checked"))
	{
		$('#product_box').fadeOut('fast');
		$('#fixed_multi_box').fadeOut('fast');
		$('#fixed_box').fadeIn('fast');
		$('#fixed_box_multiple').fadeIn('fast');
	}
}

function add_box() {
	$.ajax({
		type: 'POST',
		url: module_path + 'manage_boxes.php',
		async: false,
		cache: false,
		dataType : "json",
		data: {'action':'add','width':$("#dhl_box_width").val(),'height':$("#dhl_box_height").val(),'depth':$("#dhl_box_depth").val(),'weight':$("#dhl_box_weight").val()},
		success:function(feed) {
			$("#dhl_boxes").html(feed.boxes);
			$("#dhl_box_width").val("");
			$("#dhl_box_height").val("");
			$("#dhl_box_depth").val("");
			$("#dhl_box_weight").val("");
		  }
	});
}

function expand_box(id)
{
	if ($("#expand_box_"+id).attr("stat") == "off")
	{
		$("#expand_box_"+id).attr("src", module_path + "img/up.gif");
		$("#expand_box_"+id).attr("stat","on");
		$(".expanded_"+id).show();
	}
	else
	{
		$("#expand_box_"+id).attr("src", module_path + "img/down.gif");
		$("#expand_box_"+id).attr("stat","off");
		$(".expanded_"+id).hide();
	}
}

function edit_box(id) {
	$.ajax({
		type: 'POST',
		url: module_path + 'manage_boxes.php',
		async: false,
		cache: false,
		dataType : "json",
		data: {'action':'edit','box':id,'name':$("#name_"+id).val(),'width':$("#width_"+id).val(),'height':$("#height_"+id).val(),'depth':$("#depth_"+id).val(),'max':$("#max_"+id).val(),'productid':$("#productid_"+id).val(),'categoryid':$("#categoryid_"+id).val(),'manufacturerid':$("#manufacturerid_"+id).val(),'supplierid':$("#supplierid_"+id).val()},
		success:function(feed) {
			$("#edit_box_"+id).attr("src", module_path + "img/ok.gif");
			setTimeout(function() {
				$("#edit_box_"+id).attr("src", module_path + "img/update.gif");
			}, 1000);
		}
	});
}

function remove_box(id) {
	$.ajax({
		type: 'POST',
		url: module_path + 'manage_boxes.php',
		async: false,
		cache: false,
		dataType : "json",
		data: {'action':'remove','box':id},
		success:function(feed) {
			$("#dhl_boxes").html(feed.boxes);
		}
	});
}

$(document).ready(function() {
	show_package_size();
	$.ajax({
		type: 'POST',
		url: module_path + 'manage_boxes.php',
		async: false,
		cache: false,
		dataType : "json",
		success:function(feed) {
			$("#dhl_boxes").html(feed.boxes);
		}
	});

	$(".hint_img").live({
		mouseenter:
		   function()  {
				$(this).siblings(".hint").show();
		   },
		mouseleave:
		   function() {
				$(this).siblings(".hint").stop().hide();
		   }
	});
});