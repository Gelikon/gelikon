<?php
include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
if(substr(_PS_VERSION_, 0, 3) == '1.5' && Tools::getValue('id_employee'))
{
	$context = Context::getContext();
	$context->cookie->id_employee = Tools::getValue('id_employee');
	$context->employee = new Employee(Tools::getValue('id_employee'));
}
include_once(dirname(__FILE__) . '/dhl.php');
include_once(dirname(__FILE__) . '/classes/LabelPrinting.php');
$dhl = new DHL();        
$LabelPrinting = new LabelPrinting();

if (Tools::getValue('dhl_random') != $dhl->_dhl_random)
	exit;

if(Tools::isSubmit('shippingLabel') AND Tools::isSubmit('id_order'))
{
	$label = $LabelPrinting->getShippingLabel(Tools::getValue('id_order'));
	$ajax = Tools::getValue('ajax');
	$tryagain = ($ajax == 1 ? $dhl->l('Please change incorrect settings and try again.', 'display_label') : $dhl->l('Please close this tab and change incorrect settings at previous page.', 'display_label'));
	$html = '';
		$tempLabels = array();

	if(isset($label['errors']) && is_array($label['errors']) && count($label['errors']))
	{
		$html .= '<p style="color: red;margin: 20px 0 0 0;border: 1px solid;padding: 5px;"><span style="font-weight:bold;">'.$dhl->l('There is ', 'display_label').count($label['errors']).$dhl->l(' error(s)', 'display_label').'</span><br /><br />';
			foreach($label['errors'] as $error){ 
			$html .= ($error['HelpContext'] ? '[#'. $error['HelpContext'] .'] ' : '- '). $error['Description'] .'<br />';   
		}
		$html .= '<br />'.$tryagain.'</p>';
	}
	elseif($label[0] == 0) //if error
	{
		$html .= '<p style="color: red;margin: 20px 0 0 0;border: 1px solid;padding: 5px;"><span style="font-weight:bold;">'.$dhl->l('Error', 'display_label').':</span> '.$label[1].'<br /><br />'.$tryagain.'</p>';
	}
	elseif($label[0] == 1) //if success
	{
		//create folder for labels of this order
		$folder = 'labels/'.Tools::getValue('id_order');
		if(!file_exists($folder))
		{
			mkdir($folder);
			chmod($folder, 0777);
			copy('labels/index.php',$folder.'/index.php');
		}
		$random_str = md5(time());
		
		foreach ($label[1] as $id => $label_data)
		{       
			if (!is_numeric($id))
				continue;
			
			$path_to_label = $folder.'/'.$random_str.'_'.$id.'.'.strtolower($dhl->_dhl_label_format);

			$label_link = _MODULE_DIR_.$dhl->name.'/'.$path_to_label;
			$tempLabels[] = $label_link;
			
			$fp = fopen($path_to_label, 'wb');       
			fwrite($fp, base64_decode($label_data->LabelImage->OutputImage));
			fclose($fp);

			$html .= '<p style="color: green;margin: 20px 0 0 0;border: 1px solid;padding: 5px;"><span style="font-weight:bold;">'.$dhl->l('Package', 'display_label').' '.($id+1).': '.$dhl->l('Labels Generated Successeful!', 'display_label').'</span></p>';
		} 
	}
	else
		$html .= '<p><span style="font-weight:bold;">Error:</span> '.$dhl->l('Unknown error occured.').'</p><p>'.$dhl->l('Please close this tab and change wrong settings at previous page.', 'display_label').'</p>';

	if($ajax == 1)
	{
		$return = new stdClass();
		
		if(is_array($tempLabels) && count($tempLabels))
		{
			$tempLabels = implode(',', $tempLabels);
			$return->labels = $tempLabels;
		}
		else
		{
			$return->labels = 0;
		}
		
		if( (isset($labels['errors']) && is_array($labels['errors']) && count($labels['errors'])) || (isset($label[0]) && $label[0] == 0) )
			$return->status = 0;
		else
			$return->status = 1;
			
		$return->html = $html;
		echo json_encode($return);
	}
	else
	{
		echo '
			<!doctype html>
			<html>
				<head></head>
				<body style="margin: 0;">
					'.$html.'
				</body>
			</html>
		';
	}
}

if(Tools::isSubmit('delete_labels') AND Tools::isSubmit('id_order'))
{
	$id_order = Tools::getValue('id_order');
	rmDirRec('labels/'.$id_order);
}

if(Tools::isSubmit('show_existing') AND Tools::isSubmit('id_order'))
{
	$id_order = Tools::getValue('id_order');
	$types = $dhl->getLabelTypes();
	$prev_lab_html = '';
	$html = '';
	foreach($types as $type)
	{
		$files = glob('labels/'.$id_order.'/*.'.$type);
		if(is_array($files) AND sizeof($files) > 0)
		{
			$prev_lab_html .= strtoupper($type).': ';
			foreach ($files as $file) {
				$number = array();
				preg_match('/.*_(.+)\.'.$type.'/', $file, $number);
				$number = $number[1];
				$prev_lab_html .= '<a target="_index" style="text-decoration:underline;" href="'._MODULE_DIR_.$dhl->name.'/'.$file.'">'.$dhl->l('Label #', 'display_label').$number.'</a>, ';
			}
			$prev_lab_html = substr($prev_lab_html, 0, strlen($prev_lab_html) - 2);
			$prev_lab_html .= '<br>';
		}
	}
	$html .= '
		<div id="previous_labels_list">
			<hr style="margin-top:20px;">
			<p style="font-weight:bold;">'.$dhl->l('Previously generated labels', 'display_label').':</p>
			<p style="line-height:1.5;" id="">'.$prev_lab_html.'</p>
			<input style="margin-top:10px;" type="button" id="delete_labels" class="button" value="'.$dhl->l('Delete Labels', 'display_label').'">
		</div>
		';

	echo $html;
}

if(Tools::isSubmit('void_shipment') AND Tools::isSubmit('id_order'))
{
	$id_order = Tools::getValue('id_order');
	$label_data = Db::getInstance()->executeS('
		SELECT *
		FROM `'._DB_PREFIX_.'fe_dhl_labels_info`
		WHERE `id_order` = '.(int)$id_order.'
	');
	if(Tools::getValue('void_return') == 1)
	{
		$tracking_numbers = unserialize($label_data[0]['return_tracking_numbers']);
		$cancel_label = $dhl->deleteShipment($tracking_numbers, $id_order, $label_data[0]['return_tracking_id_type']);
	}
	else
	{
		$tracking_numbers = unserialize($label_data[0]['tracking_numbers']);
		$cancel_label = $dhl->deleteShipment($tracking_numbers, $id_order, $label_data[0]['tracking_id_type']);
	}


	if($cancel_label[0])
	{
		if(Tools::getValue('void_return') == 1)
		{
			Db::getInstance()->execute('
				UPDATE `'._DB_PREFIX_.'fe_dhl_labels_info`
				SET `return_tracking_id_type` = \'\', `return_tracking_numbers` = \'\'
				WHERE `id_order` = '.(int)$id_order.'
			');
		}
		else
		{
			Db::getInstance()->execute('
				UPDATE `'._DB_PREFIX_.'fe_dhl_labels_info`
				SET `tracking_id_type` = \'\', `tracking_numbers` = \'\'
				WHERE `id_order` = '.(int)$id_order.'
			');
		}

		$return = new stdClass();
		$return->status = 1;
		$return->text = $dhl->l('Shipment deleted successfully.', 'display_label');
		echo json_encode($return);
	}
	else
	{
		$return = new stdClass();
		$return->status = 0;
		$return->text = $cancel_usual_label[1];
		$return->text .= '<br>'.$cancel_return_label[1];
		echo json_encode($return);
	}
}


//recursive delete function
function rmDirRec($dir)
{
	$objs = glob($dir."/*");
	if ($objs)
	{
		foreach($objs as $obj)
		{
			is_dir($obj) ? rmDirRec($obj) : @unlink($obj);
		}
	}
	@rmdir($dir);
}

