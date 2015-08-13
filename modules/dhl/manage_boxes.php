<?php
function array2object($array) {
 
	if (is_array($array)) {
		$obj = new StdClass();
 
		foreach ($array as $key => $val){
			$obj->$key = $val;
		}
	}
	else { $obj = $array; }
 
	return $obj;
}

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
include(dirname(__FILE__) . '/classes/RateAvailableServices.php');
include_once(dirname(__FILE__) . '/JSON.php');
include_once(dirname(__FILE__) . '/dhl.php');
$exceptions = false;
$dhl = new DHL();

$return = array();

if (isset($_POST['action']) && $_POST['action'] == 'add')
{
	$dhl->_dhl_boxes[] = array((float)$_POST['width'], (float)$_POST['height'], (float)$_POST['depth'], (float)$_POST['weight']<=0?150:(float)$_POST['weight']);
	// Sort needed //
	foreach($dhl->_dhl_boxes as $box)
	{
		$tot = round($box[0] * $box[1] * $box[2]);
		while (isset($arr_tmp[$tot]))
			$tot++;
		$arr_tmp[$tot] = $box;
	}
	ksort($arr_tmp);
	foreach ($arr_tmp as $box)
		$sorted[] = $box;
}
else if (isset($_POST['action']) && $_POST['action'] == 'remove')
{
	foreach ($dhl->_dhl_boxes as $key => $val)
		if ($key != $_POST['box'])
			$sorted[] = $val;
}
else if (isset($_POST['action']) && $_POST['action'] == 'edit')
{
	foreach ($dhl->_dhl_boxes as $key => $val)
		if ($key != $_POST['box'])
			$sorted[] = $val;
		else 
			$sorted[] = array((float)$_POST['width'], (float)$_POST['height'], (float)$_POST['depth'], (float)$_POST['max']<=0?150:(float)$_POST['max'],htmlspecialchars($_POST['name'], ENT_QUOTES), str_replace(" ","",$_POST['productid']),str_replace(" ","",$_POST['categoryid']),str_replace(" ","",$_POST['manufacturerid']),str_replace(" ","",$_POST['supplierid']));
}
else 
{
	$sorted = $dhl->_dhl_boxes;
}
if (!is_array($sorted))
	$sorted = array();
Configuration::updateValue('DHL_BOXES', serialize($sorted));
if (sizeof($sorted) > 0)
$html = '
		<table width="100%">
		 <tr height="31">
			<td align="left" width="35%">
				'.$dhl->l('Name', 'manage_boxes').'
				
				<span class="info_tooltip" title="
						'.$dhl->l('After adding a name or changing box dimensions, click').' <img src=\''._MODULE_DIR_.'dhl/img/update.gif\' /> '.$dhl->l('to save').'.
						<br /><br />
						'.$dhl->l('Box name is optional, it is only used for internal organization').'
				"></span>

				&nbsp;&nbsp;&nbsp;
			</td>
			<td align="left">'.$dhl->l('Width').' &nbsp;&nbsp;&nbsp;</td>
			<td align="left">'.$dhl->l('Height').' &nbsp;&nbsp;&nbsp;</td>
			<td align="left">'.$dhl->l('Depth').' &nbsp;&nbsp;&nbsp;</td>
			<td align="left"><span title="'.$dhl->l('Max Weight').'">'.$dhl->l('Max W').'</span> &nbsp;&nbsp;&nbsp;</td>
			'.($exceptions?'<td align="left">'.$dhl->l('Exceptions').' &nbsp;&nbsp;&nbsp;</td>':'').'
			<td align="left" width="20"> &nbsp;&nbsp;&nbsp;</td>
			<td align="left" width="20"> &nbsp;&nbsp;&nbsp;</td>
		</tr>';
foreach ($sorted as $key => $box)
{
	$html .= '<tr height="31">
			<td align="left" valign="top"><input type="text" id="name_'.$key.'" style="width:200px;" value="'.(isset($box[4])?$box[4]:'').'" /></td>
			<td align="left" valign="top"><input type="text" id="width_'.$key.'"" style="width:40px" value="'.$box[0].'" /></td>
			<td align="left" valign="top"><input type="text" id="height_'.$key.'" style="width:40px" value="'.$box[1].'" /></td>
			<td align="left" valign="top"><input type="text" id="depth_'.$key.'" style="width:40px" value="'.$box[2].'" /></td>
			<td align="left" valign="top"><input type="text" id="max_'.$key.'" style="width:55px" value="'.$box[3].'" /></td>
			'.($exceptions?'<td align="left" valign="top">
				<span><img src="'._MODULE_DIR_.'dhl/img/down.gif" style="cursor:pointer;" id="expand_box_'.$key.'" stat="off" onclick="expand_box('.$key.')" /> '.(substr_count($box[5],",")>=1?substr_count($box[5],",")+1:($box[5]!=''?1:0)).'</span>
				<div class="expanded_'.$key.'" style="display:none">
					'.$dhl->l('Product IDs (1,2,3,etc..)').'
					<br />
					<textarea id="productid_'.$key.'" style="width:140px;height:45px">'.$box[5].'</textarea> 
				</div>
				<div class="not_expanded_'.$key.'" style="display:none">
					'.$dhl->l('Category IDs').'
					<br />
					<textarea id="categoryid_'.$key.'" style="width:140px;height:45px">'.$box[6].'</textarea> 
				</div>
				<div class="not_expanded_'.$key.'" style="display:none">
					'.$dhl->l('Manufacturer IDs').'
					<br />
					<textarea id="manufacturerid_'.$key.'" style="width:140px;height:45px">'.$box[7].'</textarea> 
				</div>
				<div class="not_expanded_'.$key.'" style="display:none">
					'.$dhl->l('Supplier IDs').'
					<br />
					<textarea id="supplierid_'.$key.'" style="width:140px;height:45px">'.$box[8].'</textarea> 
				</div>
			</td>':'').'
			<td align="left" valign="top"><img src="'._MODULE_DIR_.'dhl/img/update.gif" id="edit_box_'.$key.'" style="cursor:pointer;" onclick="edit_box('.$key.')" /></td>
			<td align="left" valign="top"><img src="'._MODULE_DIR_.'dhl/img/delete.gif" style="cursor:pointer;" onclick="remove_box('.$key.')" /></td>
	</tr>';
}

$html .= "</table>";
$return['boxes'] = $html;

ob_end_clean();
if (!function_exists('json_decode') )
{
	$j = new JSON();
	print $j->serialize(array2object($return));
}
else
	print json_encode($return);
?>