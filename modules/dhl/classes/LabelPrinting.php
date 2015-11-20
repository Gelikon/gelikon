<?php
if(!in_array('DHL', get_declared_classes()))
	require_once( _PS_MODULE_DIR_ . 'dhl/dhl.php');

class LabelPrinting extends DHL 
{
	private $_request = '';
	private $_xml = '';
	private $_post_response = '';
	private $_response = array('errors' => array(), 'success' => array());
	private $_tracking_numbers = array();
	private $_trackingIdType = null;
	private $_error = false;
	
	private $_dhl_post_url = "";
	private $_dhl_label_infos = array();
	private $_dhl_label_packages = array();
	
	function __construct()
	{
		parent::__construct();
		$this->_dhl_post_url = $this->getServerURL();
		
		if(isset($_POST))
		{
			$this->prepareLabelInfo();
			$this->_dhl_label_packages = Tools::getValue('pack');
			
			$this->_response['errors'] = $this->validateRequiredInfos();
			$this->_response['success'] = array();
		}
	}
	
	public function getShippingLabel($id_order)
	{
		if(is_array($this->_response['errors']) && !count($this->_response['errors']))
		{				
			$this->_request = $this->buildLabelRequest();

			$error_code = null;
			do
			{
				$this->_post_response = $this->curl_post($this->_dhl_post_url, $this->_request);
				$this->_xml = @simplexml_load_string($this->_post_response);
				
				if($this->_xml === false)
					$this->_xml = @simplexml_load_string(utf8_encode($this->_post_response));

				$error_code = @$this->_xml->GetCapabilityResponse->Note->Condition->ConditionCode;
				$date = date('Y-m-d', strtotime('+1 day', strtotime(date('Y-m-d'))));
			} while($error_code == '1003'); //do while error will be non "Pick-up service is not provided on this day."

			/** IF TIME OUT ERROR */
			if($this->_post_response == 28)
			{
				$this->_error = $this->l('[Error #28] Time out error. The request took more than the time limit of execution');
				$this->_error = array(0, $this->_error);
			}
			//if error
			elseif($this->_xml->Response->Status->Condition)
			{
				$this->_error = (strlen($this->_xml->Response->Status->Condition->ConditionData) ? $this->_xml->Response->Status->Condition->ConditionData : $this->_xml->Response->Status->Condition->ConditionCode);
				$this->_error = array(0, $this->_error);
			}
			//if success
			else
			{
				$this->_response[] = $this->_xml;
				$this->_trackingIdType = $this->_dhl_label_infos['ShippingType']; //the same for all packages, shipping method
				$this->_tracking_numbers[] = (string)$this->_xml->Pieces->Piece->LicensePlate;
			}
		}
		else
		{
			$this->_response[0] = 0;
			return $this->_response;    
		}

		//if some request failed but before it were successful requests, we need to cancel all successful requests
		if($this->_error)
			return $this->_error;

		//if success
		if((int)$this->_dhl_label_order_status > 0)
		{
			$history = new OrderHistory();
			$history->id_order = (int)$id_order;
			$history->changeIdOrderState($this->_dhl_label_order_status, $id_order);
			$history->addWithemail();
		}

		//saving tracking number
		$order = new Order($id_order);
		$order->shipping_number = implode(', ', $this->_tracking_numbers);
		$order->update();
		$id_order_carrier = Db::getInstance()->getValue('
			 SELECT `id_order_carrier`
			 FROM `'._DB_PREFIX_.'order_carrier`
			 WHERE `id_order` = '.(int)$order->id);
		if ($id_order_carrier)
		{
			$order_carrier = new OrderCarrier($id_order_carrier);
			$order_carrier->tracking_number = implode(', ', $this->_tracking_numbers);
			$order_carrier->update();
		}
		//sending email to customer
		if ($order->shipping_number)
		{
			global $_LANGMAIL;
			$customer = new Customer((int)($order->id_customer));
			$carrier = new Carrier((int)($order->id_carrier));
			if (!Validate::isLoadedObject($customer) OR !Validate::isLoadedObject($carrier))
				die(Tools::displayError());
			$templateVars = array(
				'{followup}' => str_replace('@', $order->shipping_number, $carrier->url),
				'{firstname}' => $customer->firstname,
				'{lastname}' => $customer->lastname,
				'{id_order}' => (int)($order->id)
			);
	
			if($this->getPSV() == 1.6)
				$templateVars['{order_name}'] = $order->getUniqReference();
			
			@Mail::Send((int)$order->id_lang, 'in_transit', Mail::l('Package in transit', (int)$order->id_lang), $templateVars,
				$customer->email, $customer->firstname.' '.$customer->lastname, NULL, NULL, NULL, NULL,
				_PS_MAIL_DIR_, true);
		}
		$this->_tracking_numbers = serialize($this->_tracking_numbers);
		Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'fe_dhl_labels_info`
			SET `tracking_id_type` = \''.pSQL($this->_trackingIdType).'\', `tracking_numbers` = \''.$this->_tracking_numbers.'\'
			WHERE `id_order` = '.(int)$id_order.'
		');

		return array(1, $this->_response);
	}
	
	protected function prepareLabelInfo()
	{
		$this->_dhl_label_infos = array();

		$this->_dhl_label_infos['ShippingType'] = Tools::getValue('shipping_type');
		$this->_dhl_label_infos['Contents'] = Tools::getValue('contents');

		$this->_dhl_label_infos['Shipper']['CompanyName'] = htmlspecialchars(trim(Tools::getValue('shop_name')));
		$this->_dhl_label_infos['Shipper']['PersonName'] = htmlspecialchars(trim(Tools::getValue('attention_name')));
		$this->_dhl_label_infos['Shipper']['PhoneNumber'] = htmlspecialchars(trim(Tools::getValue('phone_number')));
		$this->_dhl_label_infos['Shipper']['AddressLine1'] = htmlspecialchars(trim(Tools::getValue('address1')));
		$this->_dhl_label_infos['Shipper']['AddressLine2'] = htmlspecialchars(trim(Tools::getValue('address2')));
		$this->_dhl_label_infos['Shipper']['City'] = htmlspecialchars(trim(Tools::getValue('shop_city')));
		$shop_country = new Country(trim(Tools::getValue('shop_country')));
		$this->_dhl_label_infos['Shipper']['CountryCode'] = $shop_country->iso_code;
		$this->_dhl_label_infos['Shipper']['CountryName'] = $shop_country->name[$this->context->language->id];
		$shop_state = new State(trim(Tools::getValue('shop_state')));
		$this->_dhl_label_infos['Shipper']['Division'] = Country::containsStates($shop_country->id) ? $shop_state->iso_code : '';
		$this->_dhl_label_infos['Shipper']['PostalCode'] = htmlspecialchars(trim(Tools::getValue('shop_postal')));
		
		$order = new Order(Tools::getValue('id_order'));
		$address = new Address($order->id_address_delivery);
		$customerState = $address->id_state ? (new State($address->id_state)) : NULL;
		$customerCountry = new Country($address->id_country);                     
		
		$this->_dhl_label_infos['Consignee']['PersonName'] = htmlspecialchars(Tools::getValue('shipto_attention_name', ''));
		$shipto_company = htmlspecialchars(Tools::getValue('shipto_company', ''));
		$this->_dhl_label_infos['Consignee']['CompanyName'] = ($shipto_company == '' ? $this->_dhl_label_infos['Consignee']['PersonName'] : $shipto_company);
		$this->_dhl_label_infos['Consignee']['PhoneNumber'] = htmlspecialchars(Tools::getValue('shipto_phone', ''));
		$this->_dhl_label_infos['Consignee']['AddressLine1'] = htmlspecialchars(Tools::getValue('shipto_address1', ''));
		$this->_dhl_label_infos['Consignee']['AddressLine2'] = htmlspecialchars(Tools::getValue('shipto_address2', ''));
		$this->_dhl_label_infos['Consignee']['City'] = htmlspecialchars(Tools::getValue('shipto_city', ''));
		$shipto_state = Tools::getValue('shipto_state') != '' ? new State(Tools::getValue('shipto_state')) : (is_object($customerState) ? $customerState : NULL);
		$shipto_country = Tools::getValue('shipto_country') != '' ? new Country(Tools::getValue('shipto_country')) : $customerCountry;
		$this->_dhl_label_infos['Consignee']['Division'] = Country::containsStates($shipto_country->id) ? ($shipto_state ? htmlspecialchars($shipto_state->iso_code) : '') : '';
		$this->_dhl_label_infos['Consignee']['CountryCode'] = htmlspecialchars($shipto_country->iso_code);
		$this->_dhl_label_infos['Consignee']['CountryName'] = htmlspecialchars($shipto_country->name[$this->context->language->id]);
		$this->_dhl_label_infos['Consignee']['PostalCode'] = htmlspecialchars(Tools::getValue('shipto_postcode', ''));
		
		/** GET CURRENCY */
		if($this->_dhl_currency_used == 'BILLCU')
			$currency = new Currency($order->id_currency);
		elseif($this->_dhl_currency_used == 'PULCL' && $shipto_country->id_currency) 
			$currency = new Currency($order->id_currency);                       
		elseif($this->_dhl_currency_used == 'BASEC')
			$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
		else
			$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT')); 
								   
		$this->_dhl_label_infos['Currency_ISO'] = $currency->iso_code;  
		
		/** BOX INFORMATION */ 
		/** VERIFY IF PACKAGE IS DUTIABLE */
		$EUCountries = $this->EUCountries();
		
		if( $this->_dhl_label_infos['Consignee']['CountryCode'] != $this->_dhl_label_infos['Shipper']['CountryCode']
		&&  (Tools::getValue('Dutiable') == 'Y' || Tools::getValue('ContentType') == 'Non-Document') 
		){
			if(in_array($this->_dhl_label_infos['Shipper']['CountryCode'], $EUCountries) === true) 
			{
				if(in_array($this->_dhl_label_infos['Consignee']['CountryCode'], $EUCountries) === true)   
					$this->_dhl_label_infos['Dutiable'] = 'N';
				else
					$this->_dhl_label_infos['Dutiable'] = 'Y';
			}
			else
				$this->_dhl_label_infos['Dutiable'] = 'Y'; 
		}
		else
			$this->_dhl_label_infos['Dutiable'] = 'N';
		/** DUTIABLE INFORMATION */
		$this->_dhl_label_infos['DutyPayer'] = Tools::getValue('DutyPayer');
		if(Tools::getValue('DutyPayer') == 'S')
			$this->_dhl_label_infos['DutyPayerAccount'] = $this->_dhl_account_number;
		else
			$this->_dhl_label_infos['DutyPayerAccount'] = Tools::getValue('DutyPayerAccount');    	
		$this->_dhl_label_infos['DeclaredValue'] = Tools::getValue('DeclaredValue');   
		$this->_dhl_label_infos['DeclaredCurrency'] = Tools::getValue('DeclaredCurrency');   
		$this->_dhl_label_infos['ScheduleB'] = Tools::getValue('ScheduleB');   
		$this->_dhl_label_infos['ExportLicense'] = Tools::getValue('ExportLicense');   
		$this->_dhl_label_infos['ShipperEIN'] = Tools::getValue('ShipperEIN');   
		$this->_dhl_label_infos['ShipperIDType'] = Tools::getValue('ShipperIDType');   
		$this->_dhl_label_infos['ConsigneeIDType'] = Tools::getValue('ConsigneeIDType');   
		$this->_dhl_label_infos['ImportLicense'] = Tools::getValue('ImportLicense');   
		$this->_dhl_label_infos['ConsigneeEIN'] = Tools::getValue('ConsigneeEIN');   
		$this->_dhl_label_infos['TermsOfTrade'] = Tools::getValue('TermsOfTrade'); 
		/** SETTINGS */
		$this->_dhl_label_infos['LabelImageFormat'] = Tools::getValue('label_format');
		$this->_dhl_label_infos['ShipmentReference'] = Tools::getValue('ShipmentReference'); 
		
		
		if($this->_dhl_label_infos['Dutiable'] == 'Y')
			$this->_dhl_label_infos['ShippingType'] = $this->dutiableCarrierMethods($this->_dhl_label_infos['ShippingType']); 
	}
	
	/*
	* Validate label settings, and package information
	* 
	* @return array(
	*   [] => array(
	*       'Description' => Error description,
	*       'HelpContext' => 0 (when Help Context returns 0, it is not an USPS default error. In this case "[#{CODE}] {Error description}" does not appear )
	*   ),
	* );
	*/
	protected function validateRequiredInfos()
	{
		$return = array();
		
		/* VALIDATE LABEL SETTINGS */
		
		/* Verify Shipping Type, if there is something selected */
		if(!strlen($this->_dhl_label_infos['ShippingType']) || $this->_dhl_label_infos['ShippingType'] == 'none'){
			$return[] = array(
				'Description' => Tools::displayError('Shipping Type is a required field.'),
				'HelpContext' => 0 
			);
		}
		
		/* Verify Image Type, if there is something selected */
		if(!strlen($this->_dhl_label_infos['LabelImageFormat'])){
			$return[] = array(
				'Description' => Tools::displayError('Label Format is a required field.'),
				'HelpContext' => 0 
			);
		}
		
		/* Verify Label Stock Type, if there is something selected */
		if(!strlen($this->_dhl_label_infos['Contents'])){
			$return[] = array(
				'Description' => Tools::displayError('Contents is a required field.'),
				'HelpContext' => 0 
			);
		}
		
		/* VALIDATE REQUIRED CUSTOMER INFOS */
		
		/* Verify Name, if there is something wrote */
		if(!strlen($this->_dhl_label_infos['Consignee']['PersonName'])){
			$return[] = array(
				'Description' => Tools::displayError('Customer Name is a required field.'),
				'HelpContext' => 0 
			);
		}
		
		/* Verify Address, if there is something wrote */
		if(!strlen($this->_dhl_label_infos['Consignee']['AddressLine1'])){
			$return[] = array(
				'Description' => Tools::displayError('Customer Address Line 1 is a required field.'),
				'HelpContext' => 0 
			);
		}
		
		/* Verify City, if there is something wrote */
		if(!strlen($this->_dhl_label_infos['Consignee']['City'])){
			$return[] = array(
				'Description' => Tools::displayError('Customer City is a required field.'),
				'HelpContext' => 0 
			);
		}
		
		/* Verify Country, if there is something selected */
		if(!strlen($this->_dhl_label_infos['Consignee']['CountryCode'])){
			$return[] = array(
				'Description' => Tools::displayError('Customer Country is a required field.'),
				'HelpContext' => 0 
			);
		}
		
		$country = new Country($this->_dhl_label_infos['Consignee']['CountryCode']);
		
		/* Verify ZIP Code, if there is something wrote. For non international shipping, if is a valid format */
		if(!strlen($this->_dhl_label_infos['Consignee']['PostalCode'])){
			$return[] = array(
				'Description' => Tools::displayError('Customer Zip Code is a required field.'),
				'HelpContext' => 0 
			);
		}elseif($country->iso_code == 'US' && !preg_match('/^[0-9]{5}$/', $this->_dhl_label_infos['Consignee']['PostalCode'])){
			$return[] = array(
				'Description' => Tools::displayError('Customer Zip Code format is incorrect. (Ex: 20770)'),
				'HelpContext' => 0 
			);
		}
		
		/* VALIDATE SENDER INFORMATION */  
		
		/* Verify Name, if there is something wrote */
		if(!strlen($this->_dhl_label_infos['Shipper']['PersonName'])){
			$return[] = array(
				'Description' => Tools::displayError('Your Name is a required field.'),
				'HelpContext' => 0 
			);
		}
		
		/* Verify Address, if there is something wrote */
		if(!strlen($this->_dhl_label_infos['Shipper']['AddressLine1'])){
			$return[] = array(
				'Description' => Tools::displayError('Your Address Line 1 is a required field.'),
				'HelpContext' => 0 
			);
		}
		
		/* Verify City, if there is something wrote */
		if(!strlen($this->_dhl_label_infos['Shipper']['City'])){
			$return[] = array(
				'Description' => Tools::displayError('Your City is a required field.'),
				'HelpContext' => 0 
			);
		}
		
		/* Verify Country, if there is something selected */
		if(!strlen($this->_dhl_label_infos['Shipper']['CountryCode'])){
			$return[] = array(
				'Description' => Tools::displayError('Your Country is a required field.'),
				'HelpContext' => 0 
			);
		}
		
		$country = new Country($this->_dhl_label_infos['Shipper']['CountryCode']);
		
		/* Verify ZIP Code, if there is something wrote. For non international shipping, if is a valid format */
		if(!strlen($this->_dhl_label_infos['Shipper']['PostalCode'])){
			$return[] = array(
				'Description' => Tools::displayError('Your Zip Code is a required field.'),
				'HelpContext' => 0 
			);
		}elseif($country->iso_code == 'US' && !preg_match('/^[0-9]{5}$/', $this->_dhl_label_infos['Shipper']['PostalCode'])){
			$return[] = array(
				'Description' => Tools::displayError('Your Zip Code format is incorrect. (Ex: 20770)'),
				'HelpContext' => 0 
			);
		}
		
		$shipmentZone = $this->_DHLRegionByCountryIso($this->_dhl_label_infos['Shipper']['CountryCode']);
		$ValidPackageType = $this->_getValidPackageTypeByDHLZone(strtoupper($shipmentZone));
		$ValidPackageType = array_flip($ValidPackageType);
		
		/* VALIDATE PACKAGES */
		if(is_array($this->_dhl_label_packages) && count($this->_dhl_label_packages))
		{
			foreach ($this->_dhl_label_packages as $key => $package)
			{				
				$key++;
				
				/** VALIDATE PACKAGE TYPE */
				if(isset($package['type']) && in_array($package['type'], $ValidPackageType) === false)
				{
					$return[] = array(
						'Description' => Tools::displayError('The type of the package '). $key .Tools::displayError(' is not valid for the shipment origin.'),
						'HelpContext' => 0 
					);
				}
				
				/* Verify Package Width, if there is something wrote, format and if greater than 0 */
				if($package['type'] == 'CP' && isset($package['w']) && !is_numeric($package['w']))
				{
					$return[] = array(
						'Description' => Tools::displayError('Width of the package '). $key .Tools::displayError(' need to be numeric.'),
						'HelpContext' => 0 
					);
				}
				elseif($package['type'] == 'CP' && isset($package['w']) && $package['w'] <= 0)
				{
					$return[] = array(
						'Description' => Tools::displayError('Width of the package '). $key .Tools::displayError(' need to be greater than 0.'),
						'HelpContext' => 0 
					);
				}    
				
				/* Verify Package Height, if there is something wrote, format and if greater than 0 */
				if($package['type'] == 'CP' && isset($package['h']) && !is_numeric($package['h']))
				{
					$return[] = array(
						'Description' => Tools::displayError('Height of the package '). $key .Tools::displayError(' need to be numeric.'),
						'HelpContext' => 0 
					);
				}
				elseif($package['type'] == 'CP' && isset($package['h']) && $package['h'] <= 0)
				{
					$return[] = array(
						'Description' => Tools::displayError('Height of the package '). $key .Tools::displayError(' need to be greater than 0.'),
						'HelpContext' => 0 
					);
				}  
				
				/* Verify Package Depth, if there is something wrote, format and if greater than 0 */
				if($package['type'] == 'CP' && isset($package['d']) && !is_numeric($package['d']))
				{
					$return[] = array(
						'Description' => Tools::displayError('Depth of the package '). $key .Tools::displayError(' need to be numeric.'),
						'HelpContext' => 0 
					);
				}
				elseif($package['type'] == 'CP' && isset($package['d']) && $package['d'] <= 0)
				{
					$return[] = array(
						'Description' => Tools::displayError('Depth of the package '). $key .Tools::displayError(' need to be greater than 0.'),
						'HelpContext' => 0 
					);
				}  
				
				/* Verify Package Weight, if there is something wrote, format and if greater than 0 */
				if(!strlen($package['weight']))
				{
					$return[] = array(
						'Description' => Tools::displayError('Field Weight of the package '). $key .Tools::displayError(' is required.'),
						'HelpContext' => 0 
					);
				}
				elseif(!is_numeric($package['weight']))
				{
					$return[] = array(
						'Description' => Tools::displayError('Wight of the package '). $key .Tools::displayError(' need to be numeric.'),
						'HelpContext' => 0 
					);
				}
				elseif($package['weight'] <= 0)
				{
					$return[] = array(
						'Description' => Tools::displayError('Weight of the package '). $key .Tools::displayError(' need to be greater than 0.'),
						'HelpContext' => 0 
					);
				}
				
				/* Verify Package Insured Value, if there is something wrote, format and if greater than 0 */
				if(isset($package['insurance']) && $package['insurance'] < 0){
					$return[] = array(
						'Description' => Tools::displayError('Insured Value of the package '). $key .Tools::displayError(' need to be greater than 0.'),
						'HelpContext' => 0 
					);
				}elseif(isset($package['insurance']) && !is_numeric($package['insurance'])){
					$return[] = array(
						'Description' => Tools::displayError('Insured Value of the package '). $key .Tools::displayError(' need to be numeric.'),
						'HelpContext' => 0 
					);
				}    
			}
		}
		else
		{
			$return[] = array(
				'Description' => Tools::displayError('You must create at least one box.'),
				'HelpContext' => 0 
			);
		}
		
		return $return;
	}
	
	protected function buildLabelRequest($package = array())
	{		
		if($this->_DHLRegionByCountryIso($this->_dhl_label_infos['Shipper']['CountryCode']) == 'ea')
		{
			$request =
			'<?xml version="1.0" encoding="UTF-8"?>
			<req:ShipmentValidateRequestEA xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com ship-val-req_EA.xsd">
				<Request>
					<ServiceHeader>
						<MessageTime>'.date('c').'</MessageTime>
						<MessageReference>'.$this->generateMessageReference(30).'</MessageReference>
						<SiteID>'.$this->_dhl_site_id.'</SiteID>
						<Password>'.$this->_dhl_pass.'</Password>
					</ServiceHeader>
				</Request>
				<NewShipper>N</NewShipper>
				<LanguageCode>en</LanguageCode>
				<PiecesEnabled>Y</PiecesEnabled>
				<Billing>
					<ShipperAccountNumber>'.$this->_dhl_account_number.'</ShipperAccountNumber>
					<ShippingPaymentType>S</ShippingPaymentType>
					<BillingAccountNumber>'.$this->_dhl_account_number.'</BillingAccountNumber>
					<DutyPaymentType>'.$this->_dhl_label_infos['DutyPayer'].'</DutyPaymentType>
					'.($this->_dhl_label_infos['DutyPayer'] != 'R' || $this->_dhl_label_infos['DutyPayerAccount'] ? '<DutyAccountNumber>'.$this->_dhl_label_infos['DutyPayerAccount'].'</DutyAccountNumber>' : '').'
				</Billing>
				<Consignee>
					<CompanyName>'.$this->_dhl_label_infos['Consignee']['CompanyName'].'</CompanyName>
					<AddressLine>'.$this->_dhl_label_infos['Consignee']['AddressLine1'].'</AddressLine>
			';
			
			if(strlen($this->_dhl_label_infos['Consignee']['AddressLine2']))
			{
				$request .= '
					<AddressLine>'.$this->_dhl_label_infos['Consignee']['AddressLine2'].'</AddressLine>
				';
			}
			
			$request .= '
					<City>'.$this->_dhl_label_infos['Consignee']['City'].'</City>
					<Division>'.$this->_dhl_label_infos['Consignee']['Division'].'</Division>
					<PostalCode>'.$this->_dhl_label_infos['Consignee']['PostalCode'].'</PostalCode>
					<CountryCode>'.$this->_dhl_label_infos['Consignee']['CountryCode'].'</CountryCode>
					<CountryName>'.$this->_dhl_label_infos['Consignee']['CountryName'].'</CountryName>
					<Contact>
						<PersonName>'.$this->_dhl_label_infos['Consignee']['PersonName'].'</PersonName>
						<PhoneNumber>'.$this->_dhl_label_infos['Consignee']['PhoneNumber'].'</PhoneNumber>
					</Contact>
				</Consignee>
			';
			
			if($this->_dhl_label_infos['Dutiable'] == 'Y')
			{
				$request .= '
					<Dutiable>
						<DeclaredValue>'.number_format($this->_dhl_label_infos['DeclaredValue'], 2, '.', '').'</DeclaredValue>
						<DeclaredCurrency>'.$this->_dhl_label_infos['DeclaredCurrency'].'</DeclaredCurrency>
						'.($this->_dhl_label_infos['ShipperEIN'] ? '<ShipperEIN>'.$this->_dhl_label_infos['ShipperEIN'].'</ShipperEIN>' : '').'
						<TermsOfTrade>'.$this->_dhl_label_infos['TermsOfTrade'].'</TermsOfTrade>
					</Dutiable>
				';
				
				if($this->_dhl_label_infos['Dutiable'] == 'N')
					$this->_dhl_label_infos['Dutiable'] = 'Y';
			}    
			
			$request .= '
				<Reference>
					<ReferenceID>'.($this->_dhl_label_infos['ShipmentReference'] ? $this->_dhl_label_infos['ShipmentReference'] : $this->generateMessageReference(30)).'</ReferenceID>
				</Reference>
				<ShipmentDetails>
					<NumberOfPieces>'.count($this->_dhl_label_packages).'</NumberOfPieces>
					<CurrencyCode>'.$this->_dhl_label_infos['Currency_ISO'].'</CurrencyCode>
					<Pieces>
			';
			
			$pieceID = $packagesWeight = $packagesInsurance = 0;
			foreach($this->_dhl_label_packages as $package)
			{
				$pieceID++;
				$request .= '
					<Piece>
						<PieceID>'.$pieceID.'</PieceID>
						<PackageType>'.$package['type'].'</PackageType>
						<Weight>'.round($package['weight'], 1).'</Weight>
						<DimWeight>'.round($package['weight'], 1).'</DimWeight>
						<Depth>'.round($package['d'], 1).'</Depth>
						<Width>'.round($package['w'], 1).'</Width>
						<Height>'.round($package['h'], 1).'</Height>
						<PieceContents>'.$this->_dhl_label_infos['Contents'].'</PieceContents>
					</Piece>
				';
				$packagesWeight += $package['weight'];
				$packagesInsurance += $package['insurance'];
			}
			
			$request .= '
					</Pieces>
					<PackageType>CP</PackageType>
					<Weight>'.$packagesWeight.'</Weight>
					<DimensionUnit>'.($this->_dhl_unit == 'KGS' ? 'C' : 'I').'</DimensionUnit>
					<WeightUnit>'.($this->_dhl_unit == 'KGS' ? 'K' : 'L').'</WeightUnit>
					<GlobalProductCode>'.$this->_dhl_label_infos['ShippingType'].'</GlobalProductCode>
					<LocalProductCode>'.$this->_dhl_label_infos['ShippingType'].'</LocalProductCode>
					<DoorTo>DD</DoorTo>
					<Date>'.date('Y-m-d').'</Date>
					<Contents>'.$this->_dhl_label_infos['Contents'].'</Contents>
					<IsDutiable>'.$this->_dhl_label_infos['Dutiable'].'</IsDutiable>
					<InsuredAmount>'.number_format($packagesInsurance, 2, '.', '').'</InsuredAmount>
				</ShipmentDetails>
				<Shipper>
					<ShipperID>'.$this->_dhl_site_id.'</ShipperID>
					<CompanyName>'.$this->_dhl_label_infos['Shipper']['CompanyName'].'</CompanyName>
					<RegisteredAccount>'.$this->_dhl_account_number.'</RegisteredAccount>
					<AddressLine>'.$this->_dhl_label_infos['Shipper']['AddressLine1'].'</AddressLine>
			';
			
			if(strlen($this->_dhl_label_infos['Shipper']['AddressLine2']))
			{
				$request .= '
					<AddressLine>'.$this->_dhl_label_infos['Shipper']['AddressLine2'].'</AddressLine>
				';
			}
			
			$request .= '
					<City>'.$this->_dhl_label_infos['Shipper']['City'].'</City>
					<Division>'.$this->_dhl_label_infos['Shipper']['Division'].'</Division>
					<PostalCode>'.$this->_dhl_label_infos['Shipper']['PostalCode'].'</PostalCode>
					<CountryCode>'.$this->_dhl_label_infos['Shipper']['CountryCode'].'</CountryCode>
					<CountryName>'.$this->_dhl_label_infos['Shipper']['CountryName'].'</CountryName>
					<Contact>
						<PersonName>'.$this->_dhl_label_infos['Shipper']['PersonName'].'</PersonName>
						<PhoneNumber>'.$this->_dhl_label_infos['Shipper']['PhoneNumber'].'</PhoneNumber>
					</Contact>
				</Shipper>
			';
			
			if($this->_dhl_label_infos['Dutiable'] == 'Y' && $this->_dhl_label_infos['TermsOfTrade'] == 'DDP')
			{
				$request .= '  
				<SpecialService>
					<SpecialServiceType>DD</SpecialServiceType>
				</SpecialService>
				';
			}
			
			if($packagesInsurance > 0)
			{
				$request .= '  
				<SpecialService>
					<SpecialServiceType>II</SpecialServiceType>
				</SpecialService>
				';
			}
			
			$request .= '
				<LabelImageFormat>'.strtoupper($this->_dhl_label_infos['LabelImageFormat']).'</LabelImageFormat>
			</req:ShipmentValidateRequestEA>
			';
		}
		elseif($this->_DHLRegionByCountryIso($this->_dhl_label_infos['Shipper']['CountryCode']) == 'ap')
		{
			$request =
			'<?xml version="1.0" encoding="UTF-8"?>
			<req:ShipmentValidateRequestAP xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com ship-val-req_AP.xsd">
				<Request>
					<ServiceHeader>
						<MessageTime>'.date('c').'</MessageTime>
						<MessageReference>'.$this->generateMessageReference(30).'</MessageReference>
						<SiteID>'.$this->_dhl_site_id.'</SiteID>
						<Password>'.$this->_dhl_pass.'</Password>
					</ServiceHeader>
				</Request>
				<LanguageCode>en</LanguageCode>
				<PiecesEnabled>Y</PiecesEnabled>
				<Billing>
					<ShipperAccountNumber>'.$this->_dhl_account_number.'</ShipperAccountNumber>
					<ShippingPaymentType>S</ShippingPaymentType>
					<BillingAccountNumber>'.$this->_dhl_account_number.'</BillingAccountNumber>
					<DutyPaymentType>'.$this->_dhl_label_infos['DutyPayer'].'</DutyPaymentType>
					'.($this->_dhl_label_infos['DutyPayer'] != 'R' || $this->_dhl_label_infos['DutyPayerAccount'] ? '<DutyAccountNumber>'.$this->_dhl_label_infos['DutyPayerAccount'].'</DutyAccountNumber>' : '').'
				</Billing>
				<Consignee>
					<CompanyName>'.$this->_dhl_label_infos['Consignee']['CompanyName'].'</CompanyName> 
					<AddressLine>'.$this->_dhl_label_infos['Consignee']['AddressLine1'].'</AddressLine>
			';
			
			if(strlen($this->_dhl_label_infos['Consignee']['AddressLine2']))
			{
				$request .= '
					<AddressLine>'.$this->_dhl_label_infos['Consignee']['AddressLine2'].'</AddressLine>
				';
			}
			
			$request .= '
					<City>'.$this->_dhl_label_infos['Consignee']['City'].'</City>
					<PostalCode>'.$this->_dhl_label_infos['Consignee']['PostalCode'].'</PostalCode>
					<CountryCode>'.$this->_dhl_label_infos['Consignee']['CountryCode'].'</CountryCode>
					<CountryName>'.$this->_dhl_label_infos['Consignee']['CountryName'].'</CountryName>
					<Contact>
						<PersonName>'.$this->_dhl_label_infos['Consignee']['PersonName'].'</PersonName>
						<PhoneNumber>'.$this->_dhl_label_infos['Consignee']['PhoneNumber'].'</PhoneNumber>
					</Contact>
				</Consignee>
			';
			
			if($this->_dhl_label_infos['Dutiable'] == 'Y')
			{
				$request .= '
					<Dutiable>
						<DeclaredValue>'.number_format($this->_dhl_label_infos['DeclaredValue'], 2, '.', '').'</DeclaredValue>
						<DeclaredCurrency>'.$this->_dhl_label_infos['DeclaredCurrency'].'</DeclaredCurrency>
						'.($this->_dhl_label_infos['ShipperEIN'] ? '<ShipperEIN>'.$this->_dhl_label_infos['ShipperEIN'].'</ShipperEIN>' : '').'
						<TermsOfTrade>'.$this->_dhl_label_infos['TermsOfTrade'].'</TermsOfTrade>
					</Dutiable>
				';
				
				if($this->_dhl_label_infos['Dutiable'] == 'N')
					$this->_dhl_label_infos['Dutiable'] = 'Y';
			}    
			
			$request .= '
				<Reference>
					<ReferenceID>'.($this->_dhl_label_infos['ShipmentReference'] ? $this->_dhl_label_infos['ShipmentReference'] : $this->generateMessageReference(30)).'</ReferenceID>
				</Reference>
				<ShipmentDetails>
					<NumberOfPieces>'.count($this->_dhl_label_packages).'</NumberOfPieces>
					<CurrencyCode>'.$this->_dhl_label_infos['Currency_ISO'].'</CurrencyCode>
					<Pieces>
			';
			
			$pieceID = $packagesWeight = $packagesInsurance = 0;
			foreach($this->_dhl_label_packages as $package)
			{
				$pieceID++;
				$request .= '
					<Piece>
						<PieceID>'.$pieceID.'</PieceID>
						<PackageType>'.$package['type'].'</PackageType>
						<Weight>'.round($package['weight'], 1).'</Weight>
						<DimWeight>'.round($package['weight'], 1).'</DimWeight>
						<Depth>'.round($package['d'], 1).'</Depth>
						<Width>'.round($package['w'], 1).'</Width>
						<Height>'.round($package['h'], 1).'</Height>
						<PieceContents>'.$this->_dhl_label_infos['Contents'].'</PieceContents>
					</Piece>
				';
				$packagesWeight += $package['weight'];
				$packagesInsurance += $package['insurance'];
			}
			
			$request .= '
					</Pieces>
					<PackageType>CP</PackageType>
					<Weight>'.$packagesWeight.'</Weight>
					<DimensionUnit>'.($this->_dhl_unit == 'KGS' ? 'C' : 'I').'</DimensionUnit>
					<WeightUnit>'.($this->_dhl_unit == 'KGS' ? 'K' : 'L').'</WeightUnit>
					<GlobalProductCode>'.$this->_dhl_label_infos['ShippingType'].'</GlobalProductCode>
					<LocalProductCode>'.$this->_dhl_label_infos['ShippingType'].'</LocalProductCode>
					<DoorTo>DD</DoorTo>
					<Date>'.date('Y-m-d').'</Date>
					<Contents>'.$this->_dhl_label_infos['Contents'].'</Contents>
					<IsDutiable>'.$this->_dhl_label_infos['Dutiable'].'</IsDutiable>
					<InsuredAmount>'.number_format($packagesInsurance, 2, '.', '').'</InsuredAmount>
				</ShipmentDetails>
				<Shipper>
					<ShipperID>'.$this->_dhl_site_id.'</ShipperID>
					<CompanyName>'.$this->_dhl_label_infos['Shipper']['CompanyName'].'</CompanyName>
					<AddressLine>'.$this->_dhl_label_infos['Shipper']['AddressLine1'].'</AddressLine>
			';
			
			if(strlen($this->_dhl_label_infos['Shipper']['AddressLine2']))
			{
				$request .= '
					<AddressLine>'.$this->_dhl_label_infos['Shipper']['AddressLine2'].'</AddressLine>
				';
			}
			
			$request .= '
					<City>'.$this->_dhl_label_infos['Shipper']['City'].'</City>
					<PostalCode>'.$this->_dhl_label_infos['Shipper']['PostalCode'].'</PostalCode>
					<CountryCode>'.$this->_dhl_label_infos['Shipper']['CountryCode'].'</CountryCode>
					<CountryName>'.$this->_dhl_label_infos['Shipper']['CountryName'].'</CountryName>
					<Contact>
						<PersonName>'.$this->_dhl_label_infos['Shipper']['PersonName'].'</PersonName>
						<PhoneNumber>'.$this->_dhl_label_infos['Shipper']['PhoneNumber'].'</PhoneNumber>
					</Contact>
				</Shipper>
			';
			
			if($this->_dhl_label_infos['Dutiable'] == 'Y' && $this->_dhl_label_infos['TermsOfTrade'] == 'DDP')
			{
				$request .= '  
				<SpecialService>
					<SpecialServiceType>DD</SpecialServiceType>
				</SpecialService>
				';
			}
			
			if($packagesInsurance > 0)
			{
				$request .= '  
				<SpecialService>
					<SpecialServiceType>II</SpecialServiceType>
				</SpecialService>
				';
			}
			
			$request .= '
				<LabelImageFormat>'.strtoupper($this->_dhl_label_infos['LabelImageFormat']).'</LabelImageFormat>
			</req:ShipmentValidateRequestAP>
			';
		}
		/** AM REQUEST */
		else
		{
			$request =
			'<?xml version="1.0" encoding="UTF-8"?>
			<req:ShipmentValidateRequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com ship-val-req_AM.xsd">
				<Request>
					<ServiceHeader>
						<MessageTime>'.date('c').'</MessageTime>
						<MessageReference>'.$this->generateMessageReference(30).'</MessageReference>
						<SiteID>'.$this->_dhl_site_id.'</SiteID>
						<Password>'.$this->_dhl_pass.'</Password>
					</ServiceHeader>
				</Request>
				<RequestedPickupTime>Y</RequestedPickupTime>
				<NewShipper>N</NewShipper>
				<LanguageCode>en</LanguageCode>
				<PiecesEnabled>Y</PiecesEnabled>
				<Billing>
					<ShipperAccountNumber>'.$this->_dhl_account_number.'</ShipperAccountNumber>
					<ShippingPaymentType>S</ShippingPaymentType>
					<BillingAccountNumber>'.$this->_dhl_account_number.'</BillingAccountNumber>
					<DutyPaymentType>'.$this->_dhl_label_infos['DutyPayer'].'</DutyPaymentType>
					'.($this->_dhl_label_infos['DutyPayer'] != 'R' || $this->_dhl_label_infos['DutyPayerAccount'] ? '<DutyAccountNumber>'.$this->_dhl_label_infos['DutyPayerAccount'].'</DutyAccountNumber>' : '').'
				</Billing>
				<Consignee>
					<CompanyName>'.$this->_dhl_label_infos['Consignee']['CompanyName'].'</CompanyName>
					<AddressLine>'.$this->_dhl_label_infos['Consignee']['AddressLine1'].'</AddressLine>
			';
			
			if(strlen($this->_dhl_label_infos['Consignee']['AddressLine2']))
			{
				$request .= '
					<AddressLine>'.$this->_dhl_label_infos['Consignee']['AddressLine2'].'</AddressLine>
				';
			}
			
			$request .= '
					<City>'.$this->_dhl_label_infos['Consignee']['City'].'</City>
					<Division>'.$this->_dhl_label_infos['Consignee']['Division'].'</Division>
					<PostalCode>'.$this->_dhl_label_infos['Consignee']['PostalCode'].'</PostalCode>
					<CountryCode>'.$this->_dhl_label_infos['Consignee']['CountryCode'].'</CountryCode>
					<CountryName>'.$this->_dhl_label_infos['Consignee']['CountryName'].'</CountryName>
					<Contact>
						<PersonName>'.$this->_dhl_label_infos['Consignee']['PersonName'].'</PersonName>
						<PhoneNumber>'.$this->_dhl_label_infos['Consignee']['PhoneNumber'].'</PhoneNumber>
					</Contact>
				</Consignee>
			';
			
			if($this->_dhl_label_infos['Dutiable'] == 'Y')
			{
				$request .= '
					<Dutiable>       
						<DeclaredValue>'.number_format($this->_dhl_label_infos['DeclaredValue'], 2, '.', '').'</DeclaredValue>
						<DeclaredCurrency>'.$this->_dhl_label_infos['DeclaredCurrency'].'</DeclaredCurrency>
						<ScheduleB>'.$this->_dhl_label_infos['ScheduleB'].'</ScheduleB>
						<ExportLicense>'.$this->_dhl_label_infos['ExportLicense'].'</ExportLicense>
						<ShipperEIN>'.$this->_dhl_label_infos['ShipperEIN'].'</ShipperEIN>
						<ShipperIDType>'.$this->_dhl_label_infos['ShipperIDType'].'</ShipperIDType>
						<ConsigneeIDType>'.$this->_dhl_label_infos['ConsigneeIDType'].'</ConsigneeIDType>
						<ImportLicense>'.$this->_dhl_label_infos['ImportLicense'].'</ImportLicense>
						<ConsigneeEIN>'.$this->_dhl_label_infos['ImportLicense'].'</ConsigneeEIN>
						<TermsOfTrade>'.$this->_dhl_label_infos['TermsOfTrade'].'</TermsOfTrade>
					</Dutiable>
				';
				
				if($this->_dhl_label_infos['Dutiable'] == 'N')
					$this->_dhl_label_infos['Dutiable'] = 'Y';
			}    
			
			$request .= '
				<Reference>
					<ReferenceID>'.($this->_dhl_label_infos['ShipmentReference'] ? $this->_dhl_label_infos['ShipmentReference'] : $this->generateMessageReference(30)).'</ReferenceID>
				</Reference>
				<ShipmentDetails>
					<NumberOfPieces>'.count($this->_dhl_label_packages).'</NumberOfPieces>
					<Pieces>
			';
			
			$pieceID = $packagesWeight = $packagesInsurance = 0;
			foreach($this->_dhl_label_packages as $package)
			{
				$pieceID++;
				$request .= '
					<Piece>
						<PieceID>'.$pieceID.'</PieceID>
						<PackageType>'.$package['type'].'</PackageType>
						<Weight>'.round($package['weight'], 1).'</Weight>
						<DimWeight>'.round($package['weight'], 1).'</DimWeight>
						<Width>'.round($package['w'], 1).'</Width>
						<Height>'.round($package['h'], 1).'</Height>
						<Depth>'.round($package['d'], 1).'</Depth>
						<PieceContents>'.$this->_dhl_label_infos['Contents'].'</PieceContents>
					</Piece>
				';
				$packagesWeight += $package['weight'];
				$packagesInsurance += $package['insurance'];
			}
			
			$request .= '
					</Pieces>
					<Weight>'.$packagesWeight.'</Weight>
					<WeightUnit>'.($this->_dhl_unit == 'KGS' ? 'K' : 'L').'</WeightUnit>
					<ProductCode>'.$this->_dhl_label_infos['ShippingType'].'</ProductCode>
					<GlobalProductCode>'.$this->_dhl_label_infos['ShippingType'].'</GlobalProductCode>
					<LocalProductCode>'.$this->_dhl_label_infos['ShippingType'].'</LocalProductCode>
					<Date>'.date('Y-m-d').'</Date>
					<Contents>'.$this->_dhl_label_infos['Contents'].'</Contents>
					<DoorTo>DD</DoorTo>
					<DimensionUnit>'.($this->_dhl_unit == 'KGS' ? 'C' : 'I').'</DimensionUnit>
					<InsuredAmount>'.number_format($packagesInsurance, 2, '.', '').'</InsuredAmount>
					<IsDutiable>'.$this->_dhl_label_infos['Dutiable'].'</IsDutiable>
					<CurrencyCode>'.$this->_dhl_label_infos['Currency_ISO'].'</CurrencyCode>
				</ShipmentDetails>
				<Shipper>
					<ShipperID>'.$this->_dhl_site_id.'</ShipperID>
					<CompanyName>'.$this->_dhl_label_infos['Shipper']['CompanyName'].'</CompanyName>
					<RegisteredAccount>'.$this->_dhl_account_number.'</RegisteredAccount>
					<AddressLine>'.$this->_dhl_label_infos['Shipper']['AddressLine1'].'</AddressLine>
			';
			
			if(strlen($this->_dhl_label_infos['Shipper']['AddressLine2']))
			{
				$request .= '
					<AddressLine>'.$this->_dhl_label_infos['Shipper']['AddressLine2'].'</AddressLine>
				';
			}
			
			$request .= '
					<City>'.$this->_dhl_label_infos['Shipper']['City'].'</City>
					<Division>'.$this->_dhl_label_infos['Shipper']['Division'].'</Division>
					<PostalCode>'.$this->_dhl_label_infos['Shipper']['PostalCode'].'</PostalCode>
					<CountryCode>'.$this->_dhl_label_infos['Shipper']['CountryCode'].'</CountryCode>
					<CountryName>'.$this->_dhl_label_infos['Shipper']['CountryName'].'</CountryName>
					<Contact>
						<PersonName>'.$this->_dhl_label_infos['Shipper']['PersonName'].'</PersonName>
						<PhoneNumber>'.$this->_dhl_label_infos['Shipper']['PhoneNumber'].'</PhoneNumber>
					</Contact>
				</Shipper>
			';
			
			if($this->_dhl_label_infos['Dutiable'] == 'Y' && $this->_dhl_label_infos['TermsOfTrade'] == 'DDP')
			{
				$request .= '  
				<SpecialService>
					<SpecialServiceType>DD</SpecialServiceType>
				</SpecialService>
				';
			}
			
			if($packagesInsurance > 0)
			{
				$request .= '  
				<SpecialService>
					<SpecialServiceType>II</SpecialServiceType>
				</SpecialService>
				';
			}
			
			$request .= '
				<LabelImageFormat>'.strtoupper($this->_dhl_label_infos['LabelImageFormat']).'</LabelImageFormat>
			</req:ShipmentValidateRequest>
			';
		}

		return $request;
	}
}
