<?php
include_once(dirname(__FILE__) . '/../../config/config.inc.php');
include_once(dirname(__FILE__) . '/../../init.php');
include_once(dirname(__FILE__) . '/dhl.php');
$dhl = new DHL();
if (Tools::getValue('dhl_random') != $dhl->_dhl_random)
	exit;
$ps_version = floatval(substr(_PS_VERSION_,0,3));

if (Tools::isSubmit('save') AND Tools::getValue('id_order') > 0)
{
	$id_order = Tools::getValue('id_order');
	
	foreach($_POST as $field => $value)
	{
		if($field == 'pack')
		{
			foreach($value as $box => $info)
			{
				/* FIX PACKAGE DELETING ACTION */
				if(!isset($info['type']) || (!isset($info['h']) && !isset($info['w']) && !isset($info['d'])))
					unset($_POST['pack'][$box]);
			}
		}
	}
	
	$data = $_POST;
	Configuration::updateValue('DHL_LABEL_FORMAT', $data['label_format']); //label format, global setting
	unset($data['save']);
	foreach ($data as &$item) {
		$item = is_string($item) ? htmlspecialchars(stripslashes($item)) : $item;
	}
	$data_ser = serialize($data);

	$label_info = Db::getInstance()->executeS('
		SELECT *
		FROM `'._DB_PREFIX_.'fe_dhl_labels_info`
		WHERE `id_order` = '.(int)$id_order.'
	');

	if(is_array($label_info) && sizeof($label_info) > 0)
	{
		if ($_POST['attention_name'] != '')
		Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'fe_dhl_labels_info`
			SET info = \''.($ps_version >= 1.5?Db::getInstance()->_escape($data_ser):mysql_real_escape_string($data_ser)).'\'
			WHERE id_order = '.(int)$id_order.'
		');
	}
	else
	{
		Db::getInstance()->execute('
			INSERT INTO `'._DB_PREFIX_.'fe_dhl_labels_info`
			(id_order, info)
			VALUES ('.(int)$id_order.', \''.($ps_version >= 1.5?Db::getInstance()->_escape($data_ser):mysql_real_escape_string($data_ser)).'\')
		');
	}
	if ($err = Db::getInstance()->getMsgError())
		print "Err = $err";

}
elseif(Tools::isSubmit('load') AND Tools::getValue('id_order') > 0)
{
	$id_order = Tools::getValue('id_order');
	$order = new Order($id_order);
	$data =  Db::getInstance()->executeS('
		SELECT *
		FROM `'._DB_PREFIX_.'fe_dhl_labels_info`
		WHERE `id_order` = '.(int)$id_order.'
		LIMIT 1
	');

	$dhl_carrier = new Carrier($order->id_carrier);

	if(is_array($data) && sizeof($data) > 0)
	{
		$tracking_id_type = $data[0]['tracking_id_type'];
		if(!strlen($tracking_id_type))
			$tracking_id_type = NULL;

		$data = unserialize($data[0]['info']);

		$shop_name = $data['shop_name'];
		$attention_name = $data['attention_name'];
		$phone_number = $data['phone_number'];
		$address1 = $data['address1'];
		$address2 = $data['address2'];
		$shop_city = $data['shop_city'];
		$countries = Country::getCountries($cookie->id_lang, true);
		$states = array();
		$shop_country = $data['shop_country'];
		$shop_state = (isset($data['shop_state']) AND isset($countries[$shop_country]['states'])) ? $data['shop_state'] : '';
		$states = isset($countries[$shop_country]['states']) ? $countries[$shop_country]['states'] : array();
		$shop_postal = $data['shop_postal'];

		$address = new Address($order->id_address_delivery);
		$shipto_company = $data['shipto_company'];
		$shipto_attention_name = $data['shipto_attention_name'];
		$shipto_phone = $data['shipto_phone'];
		$shipto_address1 = $data['shipto_address1'];
		$shipto_address2 = $data['shipto_address2'];
		$shipto_city = $data['shipto_city'];
		$shipto_postcode = $data['shipto_postcode'];
		$shipto_country = $data['shipto_country'];
		$shipto_state = $data['shipto_state'];
		$shipto_states = isset($countries[$shipto_country]['states']) ? $countries[$shipto_country]['states'] : array();

		$shipping_method = $data['shipping_type'];
		$contents = isset($data['contents']) ? $data['contents'] : '';
		$currency = isset($data['currency']) ? $data['currency'] : 'USD';
		
		/** BOX INFORMATION */
		$Dutiable = (isset($data['Dutiable']) ? $data['Dutiable'] : 'Y');
		$ContentType = (isset($data['ContentType']) ? $data['ContentType'] : 'Non-Document');
		/** DUTIABLE INFORMATION */
		$DutyPayer = (isset($data['DutyPayer']) ? $data['DutyPayer'] : 'S');
		$DutyPayerAccount = (isset($data['DutyPayerAccount']) ? $data['DutyPayerAccount'] : '');
		$DeclaredValue = (isset($data['DeclaredValue']) ? $data['DeclaredValue'] : 0.00);
		$DeclaredCurrency = (isset($data['DeclaredCurrency']) ? $data['DeclaredCurrency'] : 'USD');
		$TermsOfTrade = (isset($data['TermsOfTrade']) ? $data['TermsOfTrade'] : 'DDP');
		$ScheduleB = (isset($data['ScheduleB']) ? $data['ScheduleB'] : '');
		$ExportLicense = (isset($data['ExportLicense']) ? $data['ExportLicense'] : '');
		$ShipperEIN = (isset($data['ShipperEIN']) ? $data['ShipperEIN'] : '');
		$ShipperIDType = (isset($data['ShipperIDType']) ? $data['ShipperIDType'] : 0);
		$ImportLicense = (isset($data['ImportLicense']) ? $data['ImportLicense'] : '');
		$ConsigneeEIN = (isset($data['ConsigneeEIN']) ? $data['ConsigneeEIN'] : '');
		$ConsigneeIDType = (isset($data['ConsigneeIDType']) ? $data['ConsigneeIDType'] : 0);
		/** SETTINGS */
		$label_format = $dhl->_dhl_label_format;
		$ShipmentReference = (isset($data['ShipmentReference']) ? $data['ShipmentReference'] : '');
	}
	else //new label
	{
		$tracking_id_type = NULL;
		$shop_name = Configuration::get('DHL_SHIPPER_SHOP_NAME') ? trim(Configuration::get('DHL_SHIPPER_SHOP_NAME')) : trim(Configuration::get('PS_SHOP_NAME'));
		$attention_name = trim(Configuration::get('DHL_SHIPPER_ATTENTION_NAME'));
		$phone_number = Configuration::get('DHL_SHIPPER_PHONE') ? trim(Configuration::get('DHL_SHIPPER_PHONE')) : trim(Configuration::get('PS_SHOP_PHONE'));
		$address1 = Configuration::get('DHL_SHIPPER_ADDR1') ? trim(Configuration::get('DHL_SHIPPER_ADDR1')) : trim(Configuration::get('PS_SHOP_ADDR1'));
		$address2 = Configuration::get('DHL_SHIPPER_ADDR2') ? trim(Configuration::get('DHL_SHIPPER_ADDR2')) : trim(Configuration::get('PS_SHOP_ADDR2'));
		$shop_city = Configuration::get('DHL_SHIPPER_CITY') ? trim(Configuration::get('DHL_SHIPPER_CITY')) : trim(Configuration::get('PS_SHOP_CITY'));
		$countries = Country::getCountries($cookie->id_lang, true);
		$states = array();
		$shop_country = Configuration::get('DHL_SHIPPER_COUNTRY') ? trim(Configuration::get('DHL_SHIPPER_COUNTRY')) : trim(Configuration::get('DHL_ORIGIN_COUNTRY'));
		$shop_country = Country::getByIso($shop_country);
		$shop_state = Configuration::get('DHL_SHIPPER_STATE') ? trim(Configuration::get('DHL_SHIPPER_STATE')) : trim(Configuration::get('DHL_ORIGIN_STATE'));
		$shop_state = State::getIdByIso($shop_state);
		$states = isset($countries[$shop_country]['states']) ? $countries[$shop_country]['states'] : array();
		$shop_postal = Configuration::get('DHL_SHIPPER_POSTCODE') ? trim(Configuration::get('DHL_SHIPPER_POSTCODE')) : trim(Configuration::get('PS_SHOP_CODE'));

		$address = new Address($order->id_address_delivery);
		$shipto_company = $address->company;
		$shipto_attention_name = $address->firstname.' '.$address->lastname;
		$shipto_phone = $address->phone ? $address->phone : $address->phone_mobile;
		$shipto_address1 = $address->address1;
		$shipto_address2 = $address->address2;
		$shipto_city = $address->city;
		$shipto_postcode = $address->postcode;
		$shipto_country = $address->id_country;
		$shipto_state = $address->id_state;
		$shipto_states = isset($countries[$shipto_country]['states']) ? $countries[$shipto_country]['states'] : array();

		$shipping_method =  Db::getInstance()->executeS('
			SELECT `method`
			FROM `'._DB_PREFIX_.'fe_dhl_method`
			WHERE `id_carrier` = '.(int)$order->id_carrier.'
			LIMIT 1
		');
		$shipping_method = (isset($shipping_method[0]['method']) ? $shipping_method[0]['method'] : 'none');
		$contents = '';
		$currency = 'USD';
		
		/** BOX INFORMATION */
		$Dutiable = 'Y';
		$ContentType = 'Non-Document';
		/** DUTIABLE INFORMATION */
		$DutyPayer = 'S';
		$DutyPayerAccount = '';
		$DeclaredValue = 0.00;
		$DeclaredCurrency = 'USD';
		$TermsOfTrade = 'DDP';
		$ScheduleB = '';
		$ExportLicense = '';
		$ShipperEIN = '';
		$ShipperIDType = 0;
		$ImportLicense = '';
		$ConsigneeEIN = '';
		$ConsigneeIDType = 0;
		/** SETTINGS */
		$label_format = $dhl->_dhl_label_format;
		$ShipmentReference = '';
	}

	$dhl_currencies = array();
	$ship_meth = $dhl->getShippingMethods();
	$package_types = $dhl->getPackageTypes();

	$cart = new Cart($order->id_cart);
	if(!sizeof($data))
	{
		$pack_dim = $dhl->getBoxes($order->id_carrier, $order->getTotalWeight(), $cart, 0, 0, 1, 'CP');
		foreach ($pack_dim as &$pack)
		{
			$pack['type'] = Configuration::get('DHL_PACK');
			$pack['insurance'] = 0;
			$pack['Dutiable'] = 'Y';
			$pack['Type'] = 'Non-Document';
			$pack['TermsOfTrade'] = 'DDP';
			$pack['ShipperIDType'] = 0;
			$pack['ConsigneeIDType'] = 0;
		}
		unset($pack);
	}
	else
		$pack_dim = (isset($data['pack']) ? $data['pack'] : array());

	//previously generated labels
	$types = $dhl->getLabelTypes();
	$prev_lab_html = '';
	foreach($types as $type)
	{
		$files = glob('labels/'.$id_order.'/*.'.$type);
		if(is_array($files) AND sizeof($files) > 0)
		{
			$prev_lab_html .= strtoupper($type).': ';
			foreach ($files as $file)
			{
				$number = array();
				preg_match('/.*_(.+)\.'.$type.'/', $file, $number);
				$number = $number[1];
				$prev_lab_html .= '<a target="_index" style="text-decoration:underline;" href="'._MODULE_DIR_.$dhl->name.'/'.$file.'">'.$dhl->l('Label #', 'ajaxLabelSettings').$number.'</a>, ';
			}
			$prev_lab_html = substr($prev_lab_html, 0, strlen($prev_lab_html) - 2);
			$prev_lab_html .= '<br>';
		}
	}

	$html = '
	<link type="text/css" rel="stylesheet" href="'._MODULE_DIR_.$dhl->name.'/css/tooltipster.css" />
	<script type="text/javascript" src="'._MODULE_DIR_.$dhl->name.'/js/jquery.tooltipster.min.js"></script>
	
	<fieldset style="padding:8px; font-weight:normal;  margin:5px 0 10px 0; font-size:13px;">
		<legend>'.$dhl->l('Addresses', 'ajaxLabelSettings').'</legend>
	
		<div style="float:left;width: 48%;margin: 0 4% 0 0">
			<h3>'.$dhl->l('Shipper', 'ajaxLabelSettings').'</h3>
			
			<div style="margin: 5px 0;">
				<label for="shop_name" style="'.($dhl->getPSV() != 1.6 ? ($dhl->getPSV() == 1.5 ? 'width:140px;' : 'width:150px;') : '').' text-align: left;">'.$dhl->l('Shop Name:', 'ajaxLabelSettings').'</label>
				<input type="text" name="shop_name" id="shop_name" value="'.$shop_name.'" class="label_info">
			</div>

			<div style="margin: 5px 0;">
				<label for="attention_name" style="'.($dhl->getPSV() != 1.6 ? ($dhl->getPSV() == 1.5 ? 'width:140px;' : 'width:150px;') : '').' text-align: left;">'.$dhl->l('Your Name', 'ajaxLabelSettings').': <sup>*</sup></label>
				<input type="text" name="attention_name" id="attention_name" value="'.$attention_name.'" class="label_info">
			</div>

			<div style="margin: 5px 0;">
				<label for="phone_number" style="'.($dhl->getPSV() != 1.6 ? ($dhl->getPSV() == 1.5 ? 'width:140px;' : 'width:150px;') : '').' text-align: left;">'.$dhl->l('Phone Number', 'ajaxLabelSettings').': <sup>*</sup></label>
				<input type="text" name="phone_number" id="phone_number" value="'.$phone_number.'" class="label_info">
			</div>

			<div style="margin: 5px 0;">
				<label for="address1" style="'.($dhl->getPSV() != 1.6 ? ($dhl->getPSV() == 1.5 ? 'width:140px;' : 'width:150px;') : '').' text-align: left;">'.$dhl->l('Address Line 1', 'ajaxLabelSettings').': <sup>*</sup></label>
				<input type="text" name="address1" id="address1" value="'.$address1.'" class="label_info">
			</div>

			<div style="margin: 5px 0;">
				<label for="address2" style="'.($dhl->getPSV() != 1.6 ? ($dhl->getPSV() == 1.5 ? 'width:140px;' : 'width:150px;') : '').' text-align: left;">'.$dhl->l('Address Line 2', 'ajaxLabelSettings').':</label>
				<input type="text" name="address2" id="address2" value="'.$address2.'" class="label_info">
			</div>

			<div style="margin: 5px 0;">
				<label for="shop_city" style="'.($dhl->getPSV() != 1.6 ? ($dhl->getPSV() == 1.5 ? 'width:140px;' : 'width:150px;') : '').' text-align: left;">'.$dhl->l('Shop City', 'ajaxLabelSettings').': <sup>*</sup></label>
				<input type="text" name="shop_city" id="shop_city" value="'.$shop_city.'" class="label_info">
			</div>

			<div style="margin: 5px 0;">
				<label for="shop_country" style="'.($dhl->getPSV() != 1.6 ? ($dhl->getPSV() == 1.5 ? 'width:140px;' : 'width:150px;') : '').' text-align: left;">'.$dhl->l('Shop Country', 'ajaxLabelSettings').': <sup>*</sup></label>
				<select name="shop_country" id="shop_country" style="'.($dhl->getPSV() != 1.6 ?  'width:140px;' : '').'" class="label_info">';
					foreach ($countries as $country)
					{
						$html .= '<option value="'.$country['id_country'].'" '.($shop_country == $country['id_country']?"selected":"").'>'.$country['name'].'</option>';
					}
					$html .= '
				</select>
			</div>

			<div style="margin: 5px 0;">
				<label for="shop_state" style="'.($dhl->getPSV() != 1.6 ? ($dhl->getPSV() == 1.5 ? 'width:140px;' : 'width:150px;') : '').' text-align: left;">'.$dhl->l('Shop State', 'ajaxLabelSettings').':</label>';
				$html .= '<select name="shop_state" id="shop_state" style="'.($dhl->getPSV() != 1.6 ?  'width:140px;' : '').'" class="label_info">
							<option value="">'.$dhl->l('-- Select state --', 'ajaxLabelSettings').'</option>';
					foreach ($states as $state)
							$html .= '<option value="'.$state['id_state'].'" '.($shop_state == $state['id_state']?"selected":"").'>'.$state['name'].'</option>';
					$html .= '
				</select>
			</div>

			<div style="margin: 5px 0;">
				<label for="shop_postal" style="'.($dhl->getPSV() != 1.6 ? ($dhl->getPSV() == 1.5 ? 'width:140px;' : 'width:150px;') : '').' text-align: left;">'.$dhl->l('Shop Postal Code', 'ajaxLabelSettings').': <sup>*</sup></label>
				<input type="text" name="shop_postal" id="shop_postal" value="'.$shop_postal.'" class="label_info">
			</div>
		</div>

		<div style="float:left;width: 48%;">
			<h3>'.$dhl->l('Ship to', 'ajaxLabelSettings').'</h3>

			<div style="margin: 5px 0;">
				<label for="shipto_company" style="'.($dhl->getPSV() != 1.6 ? ($dhl->getPSV() == 1.5 ? 'width:140px;' : 'width:150px;') : '').' text-align: left;">'.$dhl->l('Company', 'ajaxLabelSettings').':</label>
				<input type="text" name="shipto_company" id="shipto_company" value="'.$shipto_company.'" class="label_info">
			</div>

			<div style="margin: 5px 0;">
				<label for="shipto_attention_name" style="'.($dhl->getPSV() != 1.6 ? ($dhl->getPSV() == 1.5 ? 'width:140px;' : 'width:150px;') : '').' text-align: left;">'.$dhl->l('Attention Name', 'ajaxLabelSettings').': <sup>*</sup></label>
				<input type="text" name="shipto_attention_name" id="shipto_attention_name" value="'.$shipto_attention_name.'" class="label_info">
			</div>

			<div style="margin: 5px 0;">
				<label for="shipto_phone" style="'.($dhl->getPSV() != 1.6 ? ($dhl->getPSV() == 1.5 ? 'width:140px;' : 'width:150px;') : '').' text-align: left;">'.$dhl->l('Phone', 'ajaxLabelSettings').': <sup>*</sup></label>
				<input type="text" name="shipto_phone" id="shipto_phone" value="'.$shipto_phone.'" class="label_info">
			</div>

			<div style="margin: 5px 0;">
				<label for="shipto_address1" style="'.($dhl->getPSV() != 1.6 ? ($dhl->getPSV() == 1.5 ? 'width:140px;' : 'width:150px;') : '').' text-align: left;">'.$dhl->l('Address Line 1', 'ajaxLabelSettings').': <sup>*</sup></label>
				<input type="text" name="shipto_address1" id="shipto_address1" value="'.$shipto_address1.'" class="label_info">
			</div>

			<div style="margin: 5px 0;">
				<label for="shipto_address2" style="'.($dhl->getPSV() != 1.6 ? ($dhl->getPSV() == 1.5 ? 'width:140px;' : 'width:150px;') : '').' text-align: left;">'.$dhl->l('Address Line 2', 'ajaxLabelSettings').':</label>
				<input type="text" name="shipto_address2" id="shipto_address2" value="'.$shipto_address2.'" class="label_info">
			</div>

			<div style="margin: 5px 0;">
				<label for="shipto_city" style="'.($dhl->getPSV() != 1.6 ? ($dhl->getPSV() == 1.5 ? 'width:140px;' : 'width:150px;') : '').' text-align: left;">'.$dhl->l('City', 'ajaxLabelSettings').': <sup>*</sup></label>
				<input type="text" name="shipto_city" id="shipto_city" value="'.$shipto_city.'" class="label_info">
			</div>

			<div style="margin: 5px 0;">
				<label for="shipto_country" style="'.($dhl->getPSV() != 1.6 ? ($dhl->getPSV() == 1.5 ? 'width:140px;' : 'width:150px;') : '').' text-align: left;">'.$dhl->l('Country', 'ajaxLabelSettings').': <sup>*</sup></label>
				<select name="shipto_country" id="shipto_country" style="'.($dhl->getPSV() != 1.6 ?  'width:140px;' : '').'" class="label_info">';
					foreach ($countries as $country)
					{
						$html .= '<option value="'.$country['id_country'].'" '.($shipto_country == $country['id_country']?"selected":"").'>'.$country['name'].'</option>';
					}
					$html .= '
				</select>
			</div>

			<div style="margin: 5px 0;">
				<label for="shipto_state" style="'.($dhl->getPSV() != 1.6 ? ($dhl->getPSV() == 1.5 ? 'width:140px;' : 'width:150px;') : '').' text-align: left;">'.$dhl->l('State', 'ajaxLabelSettings').':</label>';
				$html .= '
				<select name="shipto_state" id="shipto_state" style="'.($dhl->getPSV() != 1.6 ?  'width:140px;' : '').'" class="label_info">
					<option value="">'.(sizeof($shipto_states) ? $dhl->l('-- Select state --', 'ajaxLabelSettings') : $dhl->l('--', 'ajaxLabelSettings')).'</option>';
					foreach ($shipto_states as $state)
						$html .= '<option value="'.$state['id_state'].'" '.($shipto_state == $state['id_state']?"selected":"").'>'.$state['name'].'</option>';
					$html .= '
				</select>
			</div>

			<div style="margin: 5px 0;">
				<label for="shipto_postcode" style="'.($dhl->getPSV() != 1.6 ? ($dhl->getPSV() == 1.5 ? 'width:140px;' : 'width:150px;') : '').' text-align: left;">'.$dhl->l('Postal Code', 'ajaxLabelSettings').': <sup>*</sup></label>
				<input type="text" name="shipto_postcode" id="shipto_postcode" value="'.$shipto_postcode.'" class="label_info">
			</div>

			<div style="margin: 5px 0;">
				<label for="shipping_type" style="'.($dhl->getPSV() != 1.6 ? ($dhl->getPSV() == 1.5 ? 'width:140px;' : 'width:150px;') : '').' text-align: left;">'.$dhl->l('Shipping Type', 'ajaxLabelSettings').': <sup>*</sup></label>';
				$html .= '<select name="shipping_type" id="shipping_type" style="'.($dhl->getPSV() != 1.6 ?  'width:140px;' : '').'" class="label_info">';
				/** IF IT IS A ORDER WITHOUT A SHIPPING METHOD SELECTED (ORDERS THAT WERE NOT CREATED USING DHL MODULE) */
				if($shipping_method == 'none')
					$html .= '<option value="none" selected="selected">'.$dhl->l('-- Select Shipping Type --', 'ajaxLabelSettings').'</option>';
					
				if(is_array($ship_meth) && count($ship_meth))
				{
					foreach ($ship_meth AS $code => $code_lang)
					{
						$html .='<option value="'.$code.'" '.($shipping_method == $code ? "selected" : "").'>'.$code_lang.'</option>';
					}
				}
				
					$html .= '
				</select>
			</div>

			<div style="margin: 5px 0;">
				<label for="contents" style="'.($dhl->getPSV() != 1.6 ? ($dhl->getPSV() == 1.5 ? 'width:140px;' : 'width:150px;') : '').' text-align: left;">'.$dhl->l('Contents', 'ajaxLabelSettings').': <sup>*</sup></label>     
				<textarea name="contents" id="contents" class="label_info" style="'.($dhl->getPSV() != 1.6 ?  'width:140px;' : 'float: left;').' height:60px;">'.$contents.'</textarea>
				
				<span class="info_tooltip" title="                                               
					'.$dhl->l('Shipment contents description', 'ajaxLabelSettings').'
				"></span>
			</div>

		</div>
	</fieldset>

	<fieldset style="padding:8px; font-weight:normal;  margin:5px 0 10px 0; font-size:13px;">       
		<legend>'.$dhl->l('Boxes', 'ajaxLabelSettings').'</legend>
 
		<div style="float: left; width: 100%">
			<h3>'.$dhl->l('Add Boxes -', 'ajaxLabelSettings').' <span style="font-weight:normal;">'.$dhl->l('add new boxes for this shipment. Once added, they will appear below under "Selected Boxes"', 'ajaxLabelSettings').'</span></h3>
			
			<p style="margin-top: 10px;float: left;width: 100%;">
				'.$dhl->l('Units').': <b class="always_show">'.(($dhl->_dhl_unit) == "LBS" ? $dhl->l('LBS/IN', 'ajaxLabelSettings') : $dhl->l('CM/KG', 'ajaxLabelSettings')).'</b>
			</p>
			
			<p style="float: left; width: 100%">
				<span style="float:left;margin: 0 2% 0 0;">
					'.$dhl->l('Package Type', 'ajaxLabelSettings').': <sup>*</sup>
					
					<br />
					
					<select name="dhl_pack" id="dhl_pack" class="pack_type_select" style="width:180px;">';
						foreach ($package_types as $code => $package_name)
						{
							$html .= '<option value="'.$code.'" '.(Tools::getValue('dhl_pack', $dhl->_dhl_pack) == $code ? "selected" : "").'>'.$package_name.'</option>';
						}
						$html .= '
					</select>
				</span>
				
				<span style="float:left;margin: 0 2% 0 0;" id="dimensions">               
					'.$dhl->l('Width', 'ajaxLabelSettings').': <sup>*</sup>
					<br />
					<input type="text" name="dhl_width" style="width:40px;" id="dhl_width" value="0" />
				</span>
				
				<span style="float:left;margin: 0 2% 0 0;" id="dimensions">               
					'.$dhl->l('Height', 'ajaxLabelSettings').': <sup>*</sup>
					<br />
					<input type="text" name="dhl_height" style="width:40px;" id="dhl_height" value="0" />
				</span>
				
				<span style="float:left;margin: 0 2% 0 0;" id="dimensions">               
					'.$dhl->l('Depth', 'ajaxLabelSettings').': <sup>*</sup>
					<br />
					<input type="text" name="dhl_depth" style="width:40px;" id="dhl_depth" value="0" />
				</span>
				
				<span style="float:left;margin: 0 2% 0 0;">               
					'.$dhl->l('Weight', 'ajaxLabelSettings').': <sup>*</sup>
					<br />
					<input type="text" name="dhl_weight" style="width:55px;" id="dhl_weight" value="0" class="always_show" />
				</span>
				
				<span style="float:left;margin: 0 2% 0 0;">               
					'.$dhl->l('Insured Value', 'ajaxLabelSettings').':
					<br />
					<input type="text" name="dhl_insurance" style="width:85px;" id="dhl_insurance" value="0.00" class="very_always_show" />
				</span>
				
				<span style="float:left;margin: 20px 0 0 0;">
					<img src="'._MODULE_DIR_.'dhl/img/add.gif" style="cursor:pointer;position:relative;top:-2px;vertical-align:middle;" id="add_pack">
				</span>
			</p>
			
			<p style="margin-top: 10px;float: left;width: 100%;">
				'.$dhl->l('Add all boxes you will be using to ship (Click the', 'ajaxLabelSettings').' <img src="'._MODULE_DIR_.'dhl/img/add.gif"> '.$dhl->l('above to add)', 'ajaxLabelSettings').'
			</p>
		</div>

		<div id="packages_list" style="margin: 20px 0 20px 0;float: left; width: 100%">
			<h3>'.$dhl->l('Selected Boxes -', 'ajaxLabelSettings').' <span style="font-weight:normal;">'.$dhl->l('edit/delete the boxes that will be used for this shipment').'</span></h3>
			
			<div style="float: left; width: 100%;" id="package_list_body">
		';
		
		if(!$pack_dim) 
			$pack_dim = array();
		

		foreach ($pack_dim as $id => $package)
		{
			$html .= '
				<p class="package_item" style="float: left; width: 100%; '.($id > 0 ? 'margin: 20px 0 10px 0;border-top: 1px solid #cccccc;padding: 16px 0 0 0;' : '').'">
					<span style="float:left;margin: 0 2% 0 0;">
						'.$dhl->l('Package Type', 'ajaxLabelSettings').': <sup>*</sup>
						<br />
						<select class="pack_type_select" name="pack['.$id.'][type]" id="dhl_pack'.$id.'" style="width:180px;">
			';
						if(is_array($package_types) && count($package_types))
						{
							foreach ($package_types as $code => $package_name)
							{
								$html .= '<option value="'.$code.'" '.($package['type'] == $code ? "selected" : "").'>'.$package_name.'</option>';
							}
						}
						
			$html .= '
						</select>
					</span>

					<span style="float:left;margin: 0 2% 0 0;">
						'.$dhl->l('Width', 'ajaxLabelSettings').': <sup>*</sup>
						<br />
						<input type="text" class="pack_dimensions" id="pack_width_'.$id.'" name="pack['.$id.'][w]" style="width:40px;" value="'.$package['w'].'">
					</span>
					
					<span style="float:left;margin: 0 2% 0 0;">
						'.$dhl->l('Height', 'ajaxLabelSettings').': <sup>*</sup>
						<br />
						<input type="text" class="pack_dimensions" id="pack_height_'.$id.'" name="pack['.$id.'][h]" style="width:40px;" value="'.$package['h'].'">
					</span>
					
					<span style="float:left;margin: 0 2% 0 0;">
						'.$dhl->l('Depth', 'ajaxLabelSettings').': <sup>*</sup>
						<br />
						<input type="text" class="pack_dimensions" id="pack_depth_'.$id.'" name="pack['.$id.'][d]" style="width:40px;" value="'.$package['d'].'">
					</span>
					
					<span style="float:left;margin: 0 2% 0 0;">
						'.$dhl->l('Weight', 'ajaxLabelSettings').': <sup>*</sup>
						<br />
						<input type="text" id="pack_weight_'.$id.'" name="pack['.$id.'][weight]" style="width:55px;" value="'.$package['weight'].'" class="package_weight">
					</span>
					
					<span style="float:left;margin: 0 2% 0 0;">
						'.$dhl->l('Insured Value', 'ajaxLabelSettings').':
						<br />
						<input type="text" id="pack_insurance_'.$id.'" name="pack['.$id.'][insurance]" style="width:85px;" value="'.$package['insurance'].'" class="package_insurance">
					</span>
					
					<span style="float:left;margin: 20px 0 0 0;">
						<img src="'._MODULE_DIR_.'dhl/img/delete.gif" id="pack_delete_'.$id.'" rel="'.$id.'" class="pack_delete" style="cursor:pointer;">  
					</span>
				</p>
			';
	}

	$html .= ' 
			</div>                
		</div>
	</fieldset> 
	
	<fieldset style="margin-top:15px;margin-bottom:15px;font-size: 13px;">
		<legend style="font-size:13px;">'.$dhl->l('Settings', 'ajaxLabelSettings').'</legend> 
		
		<h3>'.$dhl->l('Box Information', 'ajaxLabelSettings').'</h3> 
	
		<div style="margin: 5px 0;width: 100%;float: left;">
			<label for="pack['.$id.']_Dutiable_yes" style="float:left;width:220px;">'.$dhl->l('Is Dutiable', 'ajaxLabelSettings').': <sup>*</sup></label>
			
			<input name="Dutiable" id="Dutiable_yes" type="radio" value="Y" style="padding: 0 3px;" '.($Dutiable == 'Y' ? 'checked="checked"' : '').' />
			<label class="t" for="Dutiable_yes">
				<img src="'._PS_ADMIN_IMG_.'enabled.gif" alt="Yes" title="Yes">
			</label>
			
			<input name="Dutiable" id="Dutiable_no" type="radio" value="N" style="padding: 0 3px;" '.(!isset($Dutiable) || $Dutiable == 'N' ? 'checked="checked"' : '').' />
			<label class="t" for="Dutiable_no">
				<img src="'._PS_ADMIN_IMG_.'disabled.gif" alt="No" title="No">
			</label>
			
			<span class="info_tooltip" title="
				'.$dhl->l('Please work with your DHL representative if you have questions about shipment dutiable status.', 'ajaxLabelSettings').'
			"></span>
		</div> 
		
		<div style="margin: 5px 0;width: 100%;float: left;">
			<label for="ContentType" style="float:left;width:220px;">'.$dhl->l('Content Type', 'ajaxLabelSettings').': <sup>*</sup></label>
			
			<select name="ContentType" id="ContentType" style="width:140px;float: left;margin: 0 10px 0 0;">
				<option value="Document" '.($ContentType == 'Document' ? 'selected="selected"' : '').'>'.$dhl->l('Document', 'ajaxLabelSettings').'</option>
				<option value="Non-Document" '.($ContentType == 'Non-Document' ? 'selected="selected"' : '').'>'.$dhl->l('Non-Document', 'ajaxLabelSettings').'</option>
			</select>
		</div> 

		<h3>'.$dhl->l('Non-document/Dutiable Information', 'ajaxLabelSettings').'</h3> 
	
		<div style="margin: 5px 0;width: 100%;float: left;">
			<label for="DutyPayer" style="float:left;width:220px;">'.$dhl->l('Duty Payer', 'ajaxLabelSettings').': <sup>*</sup></label>
			
			<select name="DutyPayer" id="DutyPayer" style="width:140px;float: left;margin: 0 10px 0 0;">
				<option value="S" '.($DutyPayer == 'S' ? 'selected="selected"' : '').'>'.$dhl->l('Sender', 'ajaxLabelSettings').'</option>
				<option value="R" '.($DutyPayer == 'R' ? 'selected="selected"' : '').'>'.$dhl->l('Recipient', 'ajaxLabelSettings').'</option>
				<option value="T" '.($DutyPayer == 'T' ? 'selected="selected"' : '').'>'.$dhl->l('Third Party', 'ajaxLabelSettings').'</option>
			</select>
			
			<span class="info_tooltip" title="
				'.$dhl->l('Define who is responsible for the products importation taxes', 'ajaxLabelSettings').'. '.$dhl->l('A DHL Account Number is required for Recipient and Third Party payers', 'ajaxLabelSettings').'.
			"></span>
		</div> 
		
		<div style="margin: 5px 0;width: 100%;float: left;">
			<label for="DutyPayerAccount" style="float:left;width:220px;">'.$dhl->l('Duty Payer DHL Account #', 'ajaxLabelSettings').':</label>
			
			<input name="DutyPayerAccount" id="DutyPayerAccount" type="text" value="'.($DutyPayerAccount ? $DutyPayerAccount : '').'" style="width:140px;float: left;margin: 0 10px 0 0;" />
			
			<span class="info_tooltip" title="
				'.$dhl->l('The DHL Account number associated with the duty payment', 'ajaxLabelSettings').'. '.$dhl->l('If payer is Sender, your dhl Account number will be used', 'ajaxLabelSettings').'.
			"></span>
		</div> 
		
		<div style="margin: 5px 0;width: 100%;float: left;">
			<label for="DeclaredValue" style="float:left;width:220px;">'.$dhl->l('Declared Value', 'ajaxLabelSettings').': <sup>*</sup></label>
			
			<input name="DeclaredValue" id="DeclaredValue" type="text" value="'.(isset($DeclaredValue) ? $DeclaredValue : 0.00).'" style="width:140px;float: left;margin: 0 10px 0 0;" />
		</div> 
		
		<div style="margin: 5px 0;width: 100%;float: left;">
			<label for="DeclaredCurrency" style="float:left;width:220px;">'.$dhl->l('Declared Currency', 'ajaxLabelSettings').': <sup>*</sup></label>
			
			<input name="DeclaredCurrency" id="DeclaredCurrency" type="text" maxlength="3" value="'.(isset($DeclaredCurrency) ? $DeclaredCurrency : "USD").'" style="width:140px;float: left;margin: 0 10px 0 0;" />
		</div>                         

		<div style="margin: 5px 0;width: 100%;float: left;">
			<label for="TermsOfTrade" style="float:left;width:220px;">'.$dhl->l('Terms Of Trade', 'ajaxLabelSettings').': <sup>*</sup></label>
			
			<select name="TermsOfTrade" id="TermsOfTrade" style="width:140px;float: left;margin: 0 10px 0 0;">
				<option value="DDP" '.($TermsOfTrade == 'DDP' ? 'selected="selected"' : '').'>'.$dhl->l('DTP - Duties and Taxes Paid', 'ajaxLabelSettings').'</option>
				<option value="DAP" '.($TermsOfTrade == 'DAP' ? 'selected="selected"' : '').'>'.$dhl->l('DTU - Duties and Taxes Unpaid', 'ajaxLabelSettings').'</option>
			</select>
		</div>
		
		<div style="margin: 5px 0;width: 100%;float: left;">
			<label for="ScheduleB" style="float:left;width:220px;">'.$dhl->l('Schedule B', 'ajaxLabelSettings').':</label>
			
			<input name="ScheduleB" id="ScheduleB" type="text" value="'.(isset($ScheduleB) ? $ScheduleB : "").'" style="width:140px;float: left;margin: 0 10px 0 0;" />
		</div> 
		
		<div style="margin: 5px 0;width: 100%;float: left;">
			<label for="ExportLicense" style="float:left;width:220px;">'.$dhl->l('Export License', 'ajaxLabelSettings').':</label>
			
			<input name="ExportLicense" id="ExportLicense" type="text" value="'.(isset($ExportLicense) ? $ExportLicense : "").'" style="width:140px;float: left;margin: 0 10px 0 0;" />
		</div>
		
		<div style="margin: 5px 0;width: 100%;float: left;">
			<label for="ShipperEIN" style="float:left;width:220px;">'.$dhl->l('Shipper EIN Number', 'ajaxLabelSettings').':</label>
			
			<input name="ShipperEIN" id="ShipperEIN" type="text" value="'.(isset($ShipperEIN) ? $ShipperEIN : "").'" style="width:140px;float: left;margin: 0 10px 0 0;" />
		</div>  
		
		<div style="margin: 5px 0;width: 100%;float: left;">
			<label for="ShipperIDType" style="float:left;width:220px;">'.$dhl->l('Shipper ID Type', 'ajaxLabelSettings').':</label>
			
			<select name="ShipperIDType" id="ShipperIDType" style="width:140px;float: left;margin: 0 10px 0 0;">
				<option value="0" '.($ShipperIDType == '0' ? 'selected="selected"' : '').'>--</option>
				<option value="S" '.($ShipperIDType == 'S' ? 'selected="selected"' : '').'>SSN</option>
				<option value="E" '.($ShipperIDType == 'E' ? 'selected="selected"' : '').'>EIN</option>
				<option value="D" '.($ShipperIDType == 'D' ? 'selected="selected"' : '').'>DUNS</option>
			</select>
		</div>
		
		<div style="margin: 5px 0;width: 100%;float: left;">
			<label for="ImportLicense" style="float:left;width:220px;">'.$dhl->l('Import License', 'ajaxLabelSettings').':</label>
			
			<input name="ImportLicense" id="ImportLicense" type="text" value="'.(isset($ImportLicense) ? $ImportLicense : "").'" style="width:140px;float: left;margin: 0 10px 0 0;" />
		</div> 
		
		<div style="margin: 5px 0;width: 100%;float: left;">
			<label for="ConsigneeEIN" style="float:left;width:220px;">'.$dhl->l('Consignee EIN Number', 'ajaxLabelSettings').':</label>
			
			<input name="ConsigneeEIN" id="ConsigneeEIN" type="text" value="'.(isset($ConsigneeEIN) ? $ConsigneeEIN : "").'" style="width:140px;float: left;margin: 0 10px 0 0;" />
		</div>  
		
		<div style="margin: 5px 0;width: 100%;float: left;">
			<label for="ConsigneeIDType" style="float:left;width:220px;">'.$dhl->l('Consignee ID Type', 'ajaxLabelSettings').':</label>
			
			<select name="ConsigneeIDType" id="ConsigneeIDType" style="width:140px;float: left;margin: 0 10px 0 0;">
				<option value="0" '.($ConsigneeIDType == '0' ? 'selected="selected"' : '').'>--</option>
				<option value="S" '.($ConsigneeIDType == 'S' ? 'selected="selected"' : '').'>SSN</option>
				<option value="E" '.($ConsigneeIDType == 'E' ? 'selected="selected"' : '').'>EIN</option>
				<option value="D" '.($ConsigneeIDType == 'D' ? 'selected="selected"' : '').'>DUNS</option>
			</select>
		</div>
		
		<h3>'.$dhl->l('Shipping Label Settings', 'ajaxLabelSettings').'</h3> 

		<div style="margin: 5px 0;width: 100%;float: left;">
			<label for="label_format" style="float:left;width:220px;">'.$dhl->l('Label Format', 'ajaxLabelSettings').': <sup>*</sup></label>
			
			<select name="label_format" id="label_format" style="width:140px;float: left;margin: 0 10px 0 0;" class="label_info">';
				foreach ($types as $type)
				{
					$html .= '<option value="'.$type.'" '.($label_format == $type ? 'selected' : '').'>'.strtoupper($type).'</option>';
				}
			$html .= '
			</select>
			
			<span class="info_tooltip" title="
				'.$dhl->l('Specifies the image format used for a shipping document.').'
			"></span>
		</div>
		
		<div style="margin: 5px 0;width: 100%;float: left;">
			<label for="ShipmentReference" style="float:left;width:220px;">'.$dhl->l('Shipment Reference', 'ajaxLabelSettings').':</label>
			
			<input name="ShipmentReference" id="ShipmentReference" maxlength="35" type="text" value="'.($ShipmentReference ? $ShipmentReference : '').'" style="width:140px;float: left;margin: 0 10px 0 0;" />
		</div>  
	</fieldset>
	
	<input type="submit" class="'.($dhl->getPSV() == 1.6 ? 'btn btn-default' : 'button').'"  id="generateLabel" value="'.$dhl->l('Generate label', 'ajaxLabelSettings').'" style="margin-top:30px;">
	<img src="'._PS_IMG_.'loader.gif" id="labelLoader" style="display:none;">
	
	<p style="margin-top:15px;"><sup>*</sup> '.$dhl->l('Required field.').'</p>
	
	<div style="font-weight:bold;margin:10px 0 0 0;" id="void_response"></div>
	<div id="labels_links" style="line-height:1.5em;"></div>';
	if(strlen($prev_lab_html) > 0)
	{
		$html .= '
		<div id="previous_labels_list">
			<hr style="margin-top:20px;">
			<p style="font-weight:bold;">'.$dhl->l('Previously generated labels', 'ajaxLabelSettings').':</p>
			<p style="line-height:1.5;" id="">'.$prev_lab_html.'</p>
			<input style="margin-top:10px;" type="button" id="delete_labels" class="'.($dhl->getPSV() == 1.6 ? 'btn btn-default' : 'button').'" value="'.$dhl->l('Delete Labels', 'ajaxLabelSettings').'">
		</div>
		';
	}
	else
	{
		$html .= '
		<div id="previous_labels_list">
		</div>
		';
	}
	
	$html .= '
	<script type="text/javascript">
		$(document).ready(function(){ 
			if($("#shop_country").val() == $("#shipto_country").val())       
				$(\'div[id="internationalSettings"], tr[id="internationalSettings"], #boxInternationalSettings, .international_settings\').fadeOut();
			else
				$(\'div[id="internationalSettings"], tr[id="internationalSettings"], #boxInternationalSettings, .international_settings\').fadeIn();
				
			$("#shop_country, #shipto_country").change(function(){
				if($("#shop_country").val() == $("#shipto_country").val())       
					$(\'div[id="internationalSettings"], tr[id="internationalSettings"], #boxInternationalSettings, .international_settings\').fadeOut();
				else
					$(\'div[id="internationalSettings"], tr[id="internationalSettings"], #boxInternationalSettings, .international_settings\').fadeIn();
			});
			
			$("select[class=\'pack_type_select\']").live("change", function(){
				if($(this).val() == "CP")
				{
					/** WIDTH SPAN */
					$(this).parent("span").next("span").children().not(".info_tooltip").fadeIn();
					/** HEIGHT SPAN */
					$(this).parent("span").next("span").next("span").children().not(".info_tooltip").fadeIn();                    
					/** DEPTH SPAN */
					$(this).parent("span").next("span").next("span").next("span").children().not(".info_tooltip").fadeIn();
				}
				else 
				{
					/** WIDTH SPAN */
					$(this).parent("span").next("span").children().not(".always_show, .very_always_show, .info_tooltip").fadeOut();
					/** HEIGHT SPAN */
					$(this).parent("span").next("span").next("span").children().not(".always_show, .very_always_show, .info_tooltip").fadeOut();                                     
					/** DEPTH SPAN */
					$(this).parent("span").next("span").next("span").next("span").children().not(".always_show, .very_always_show, .info_tooltip").fadeOut();
				}
			}).trigger("change");
		});
	</script>
	';
	
	if($ps_version == 1.6)
	{
		$html .= '
		<style>
			form#labelForm sup {
				color: #CC0000;
				font-weight: bold;
			}
			form#labelForm fieldset {
				padding: 15px;
				font-weight: normal;
				margin: 5px 0 10px 0;
				font-size: 13px;
				border: solid 1px #cccccc;
				float: left;
				width: 100%;
			}
			form#labelForm fieldset  legend{
				border: 1px solid #cccccc;
				width: auto;
				padding: 3px 10px;
				color: #555555;
				margin: 0;
			}
			form#labelForm h3 {
				margin: 0 0 26px 0 !important; 
				float: left; 
				width: 100%;
			}
		</style>
		';
	}

	echo $html;
}
elseif(Tools::isSubmit('get_states'))
{
	$id_order = Tools::getValue('id_order');
	$order = new Order($id_order);
	$data =  Db::getInstance()->getRow('
		SELECT *
		FROM `'._DB_PREFIX_.'fe_dhl_labels_info`
		WHERE `id_order` = '.(int)$id_order.'
	');
	$data = unserialize($data['info']);
	if(Tools::getValue('type') == 'shop_country')
		$id_selected_state = ((int)$data['shop_state'] ? $data['shop_state'] : 0);
	else
		$id_selected_state = ((int)$data['shop_state'] ? $data['shop_state'] : 0);
	$id_country = (int)Tools::getValue('id_country');
	$states = State::getStatesByIdCountry($id_country);
	$html = '';
	if(!sizeof($states))
	{
		$html .= '<option>'.$dhl->l('--').'</option>';
	}
	else
	{
		$html .= '<option>'.$dhl->l('-- Select state --').'</option>';
		foreach ($states as $state)
		{
			$html .= '<option value="'.$state['id_state'].'" '.(($id_selected_state && $id_selected_state == $state['id_state']) ? 'selected="selected"' : '').'>'.$state['name'].'</option>';
		}
	}
	echo $html;
}