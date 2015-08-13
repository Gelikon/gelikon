<?php
require_once( _PS_MODULE_DIR_ . 'dhl/classes/init.php');

Class DHL extends PrestoChangeoCarrierModule
{
	protected $_html = '';
	public $id_carrier;
	public $_dhl_site_id;
	public $_dhl_pass;
	public $_dhl_account_number;
	public $_dhl_dropoff;
	public $_dhl_pack;
	public $_dhl_boxes;
	public $_dhl_width;
	public $_dhl_height;
	public $_dhl_depth;
	public $_dhl_weight;
	public $_dhl_unit;
	public $_dhl_origin_zip;
	public $_dhl_origin_country;
	public $_dhl_origin_city;
	public $_dhl_mode;
	public $_dhl_reload_carriers;
	public $_dhl_packages;
	public $_dhl_package_size;
	public $_dhl_packages_per_box;
	public $_dhl_override_address;
	public $_dhl_debug_mode;
	public $_dhl_xml_log;
	public $_dhl_address_display;
	public $_dhl_currency_used;
	public $_dhl_enable_discount = false;
	public $_dhl_discount_rate;
	
	/** LABEL PRINTING: SHIPPER INFORMATION */
	public $_dhl_shipper_shop_name;
	public $_dhl_shipper_attention_name;
	public $_dhl_shipper_phone;
	public $_dhl_shipper_addr1;
	public $_dhl_shipper_addr2;
	public $_dhl_shipper_city;
	public $_dhl_shipper_country;
	public $_dhl_shipper_state;
	public $_dhl_shipper_postcode;
	/** LABEL PRINTING: GLOBAL INFORMATION */
	public $_dhl_enable_labels;
	public $_dhl_label_order_status;
	public $_dhl_carbon;
	public $_dhl_label_format;
	
	public $_dhl_random;
	public $_dhl_log_filename;
	
	protected $_full_version = 12300;

	function __construct()
	{
		$this->name = 'dhl';
		$this->tab = 'shipping_logistics';
		$this->author = 'Presto-Changeo';
		$this->version = '1.2.3';

		parent::__construct(); // The parent construct is required for translations
		$this->_refreshProperties();
		
		if ((float)substr(_PS_VERSION_,0,3) >= 1.6)
			$this->bootstrap = true;

		$this->displayName = $this->l('DHL Shipping');
		$this->description = $this->l('Get real time DHL shipping rates');
		if (!$this->_dhl_site_id || !$this->_dhl_pass)
			$this->warning = $this->l('You must enter your DHL account info');
		if ($this->upgradeCheck('DHL'))
			$this->warning = $this->l('We have released a new version of the module,') .' '.$this->l('request an upgrade at ').' https://www.presto-changeo.com/en/contact_us';
	}

	function install()
	{
		if (!parent::install() ||
		!$this->registerHook('productActions') ||
		!$this->registerHook('updateCarrier'))
			return false;
		if (!$this->registerHook('adminOrder'))
			return false;

		$this->checkHookCartShippingPreview();

		if (!$this->registerHook('cartShippingPreview'))
			return false;

		Configuration::updateValue('DHL_INSTALL','inline');
		$country = new Country(Configuration::get('PS_COUNTRY_DEFAULT'));
		Configuration::updateValue('DHL_ORIGIN_COUNTRY', $country->iso_code);
		Configuration::updateValue('DHL_ORIGIN_ZIP', Configuration::get('PS_SHOP_CODE'));
		Configuration::updateValue('DHL_BOXES', serialize(array()));
		Configuration::updateValue('DHL_WIDTH', 0);
		Configuration::updateValue('DHL_OVERRIDE_ADDRESS', 1);
		Configuration::updateValue('DHL_HEIGHT', 0);
		Configuration::updateValue('DHL_DEPTH', 0);
		Configuration::updateValue('DHL_WEIGHT', 100);
		Configuration::updateValue('DHL_PACKAGES_PER_BOX', 0);
		Configuration::updateValue('DHL_MODE', 'live');
		Configuration::updateValue('DHL_UNIT', 'LBS');
		Configuration::updateValue('DHL_PACKAGES', 'multiple');
		Configuration::updateValue('DHL_PACKAGE_SIZE', 'fixed');
		Configuration::updateValue('DHL_ADDRESS_DISPLAY', serialize(array('country' => 1, 'state' => 1, 'city' => 1, 'zip' => 1)));
		Configuration::updateValue('PRESTO_CHANGEO_UC',time());

		$query = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fe_dhl_method` (
			 `id_dhl_method` int(11) NOT NULL AUTO_INCREMENT,
			 `id_carrier` int(11) NOT NULL,
			 `method` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			 `free_shipping` decimal(9,2) NOT NULL,
			 `free_shipping_product` TEXT NOT NULL,
			 `free_shipping_category` TEXT NOT NULL,
			 `free_shipping_manufacturer` TEXT NOT NULL,
			 `free_shipping_supplier` TEXT NOT NULL,
			 `extra_shipping_type` int(1) NOT NULL,
			 `extra_shipping_amount` decimal(9,2) NOT NULL,
			 `insurance_minimum` int(11) NOT NULL,
			 `insurance_type` int(1) NOT NULL,
			 `insurance_amount` decimal(9,2) NOT NULL,
			 PRIMARY KEY (`id_dhl_method`),
			 KEY `id_carrier` (`id_carrier`),
			 KEY `method` (`method`)
			 ) ENGINE='._MYSQL_ENGINE_.'  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
		Db::getInstance()->execute($query);

		$query = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'fe_dhl_rate_cache`;';
		Db::getInstance()->execute($query);
		$query = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fe_dhl_rate_cache` (
			`id_dhl_rate` int(11) NOT NULL AUTO_INCREMENT,
			`id_carrier` int(11) NOT NULL,
			`origin_zip` varchar(11) NOT NULL,
			`origin_country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
			`dest_zip` varchar(11) NOT NULL,
			`dest_country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
			`method` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			`insurance` int(11) NOT NULL,
			`dropoff` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			`packing` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			`packages` decimal(17,2) NOT NULL,
			`weight` decimal(17,2) NOT NULL,
			`rate` decimal(17,2) NOT NULL,
			`currency` varchar(3) NOT NULL,
			`quote_date` int(11) NOT NULL,
			PRIMARY KEY (`id_dhl_rate`),
			KEY `origin_zip` (`origin_zip`,`origin_country`,`dest_zip`,`dest_country`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
		Db::getInstance()->execute($query);

		$query = '
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fe_dhl_package_rate_cache` (
			`id_package` int(11) NOT NULL AUTO_INCREMENT,
			`id_dhl_rate` int(11) NOT NULL,
			`weight` decimal(17,2) NOT NULL,
			`width` decimal(17,2) NOT NULL,
			`height` decimal(17,2) NOT NULL,
			`depth` decimal(17,2) NOT NULL,
			PRIMARY KEY (`id_package`),
			KEY `weight` (`weight`,`width`,`height`,`depth`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;';
		Db::getInstance()->execute($query);

		$query = '
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fe_dhl_hash_cache` (
			`id_hash` int(11) NOT NULL AUTO_INCREMENT,
			`id_dhl_rate` int(11) NOT NULL,
			`hash` varchar(40) NOT NULL,
			`hash_date` int(11) NOT NULL,
			PRIMARY KEY (`id_hash`),
			KEY `hash` (`hash`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;';
		Db::getInstance()->execute($query);

		$query = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'fe_dhl_invalid_dest`;';
		Db::getInstance()->execute($query);
		$query = '
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fe_dhl_invalid_dest` (
				`id_invalid` int(11) NOT NULL AUTO_INCREMENT,
				`method` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
				`zip` varchar(11) NOT NULL,
				`country` varchar(2) NOT NULL,
				`ondate` int(11) NOT NULL,
				PRIMARY KEY (`id_invalid`),
				KEY `zip` (`zip`,`country`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
		Db::getInstance()->execute($query);
		
		$query = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'fe_dhl_labels_info`;';
		Db::getInstance()->execute($query);
		Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fe_dhl_labels_info` (
				`id_label_info` int(11) NOT NULL AUTO_INCREMENT,
				`id_order` int(11) NOT NULL,
				`info` TEXT NOT NULL,
				`tracking_id_type` TINYTEXT NOT NULL,
				`tracking_numbers` TEXT NOT NULL,
				PRIMARY KEY (`id_label_info`)
			) ENGINE = '._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		');

		return true;
	}

	function uninstall()
	{
		if (!parent::uninstall())
			return false;
		return true;
	}

	protected function _refreshProperties()
	{
		$this->_dhl_site_id = str_replace(' ', '', Configuration::get('DHL_SITE_ID'));
		$this->_dhl_pass = str_replace(' ', '', Configuration::get('DHL_PASS'));
		$this->_dhl_account_number = str_replace(' ', '', Configuration::get('DHL_ACCOUNT_NUMBER'));
		$this->_dhl_dropoff = Configuration::get('DHL_DROPOFF');
		$this->_dhl_pack = Configuration::get('DHL_PACK');
		$temp_box = Configuration::get('DHL_BOXES');
		$this->_dhl_boxes = strlen($temp_box) > 5 ? unserialize($temp_box) : array();

		// Make sure the boxes are ordered from small to large.
		$this->sortBoxes();

		$this->_dhl_width = floatval(Configuration::get('DHL_WIDTH'));
		$this->_dhl_height = floatval(Configuration::get('DHL_HEIGHT'));
		$this->_dhl_depth = floatval(Configuration::get('DHL_DEPTH'));
		$this->_dhl_weight = floatval(Configuration::get('DHL_WEIGHT'));
		$this->_dhl_unit = Configuration::get('DHL_UNIT');
		$this->_dhl_origin_zip = Configuration::get('DHL_ORIGIN_ZIP');
		$this->_dhl_origin_country = Configuration::get('DHL_ORIGIN_COUNTRY');
		if ($this->_dhl_origin_country == '')
		{
			$country = new Country(Configuration::get('PS_COUNTRY_DEFAULT'));
			Configuration::updateValue('DHL_ORIGIN_COUNTRY', $country->iso_code);
		}
		$this->_dhl_origin_city = Configuration::get('DHL_ORIGIN_CITY');
		$this->_dhl_origin_city = Configuration::get('DHL_ORIGIN_CITY');
		$this->_dhl_mode = Configuration::get('DHL_MODE');
		$this->_dhl_reload_carriers = Configuration::get('DHL_RELOAD_CARRIERS');
		$this->_dhl_packages = Configuration::get('DHL_PACKAGES');
		$this->_dhl_package_size = Configuration::get('DHL_PACKAGE_SIZE');
		$this->_dhl_packages_per_box = (int)Configuration::get('DHL_PACKAGES_PER_BOX');
		$this->_dhl_override_address = (int)Configuration::get('DHL_OVERRIDE_ADDRESS');
		$this->_dhl_debug_mode = (int)Configuration::get('DHL_DEBUG_MODE');
		$this->_dhl_xml_log = (int)Configuration::get('DHL_XML_LOG');
		$tmp_display = Configuration::get('DHL_ADDRESS_DISPLAY');
		if (strlen($tmp_display) > 6)
			$this->_dhl_address_display = unserialize($tmp_display);
		else
		{
				$this->_dhl_address_display = array('country' => 1, 'state' => 1, 'city' => 1, 'zip' => 1);
				Configuration::updateValue('DHL_ADDRESS_DISPLAY', serialize($this->_dhl_address_display));
		}
		$this->_dhl_currency_used = Configuration::get('DHL_CURRENCY_USED');
		$this->_dhl_discount_rate = Configuration::get('DHL_DISCOUNT_RATE');
		$this->_last_updated = Configuration::get('PRESTO_CHANGEO_UC');

		if ($this->_dhl_reload_carriers)
		{
			$query = 'SELECT * FROM `'._DB_PREFIX_.'fe_dhl_method` GROUP BY id_carrier';
			$mresult = Db::getInstance()->executeS($query);
			foreach ($mresult AS $mrow)
			{
				$query = 'SELECT cz.id_zone, rw.id_range_weight, cz.id_zone, d.price FROM `'._DB_PREFIX_.'range_weight` rw, `'._DB_PREFIX_.'carrier_zone` cz LEFT JOIN `'._DB_PREFIX_.'delivery` d ON cz.id_carrier = d.id_carrier AND d.id_zone = cz.id_zone WHERE cz.id_carrier = '.$mrow['id_carrier'].' AND cz.id_carrier = rw.id_carrier';
				$result = Db::getInstance()->ExecuteS($query);
				foreach ($result as $row)
				{
					if ($row['price'] == "" || $row['price'] == "NULL")
					{
						$query = 'INSERT INTO `'._DB_PREFIX_.'delivery` (id_carrier, id_range_weight, id_zone, price) VALUES("'.$mrow['id_carrier'].'", "'.$row['id_range_weight'].'", "'.$row['id_zone'].'","0")';
						Db::getInstance()->Execute($query);
					}
				}
			}
			Configuration::updateValue('DHL_RELOAD_CARRIERS','');
		}
		
		/** LABEL PRINTING: SHIPPER INFORMATION */
		$this->_dhl_shipper_shop_name = (Configuration::get('DHL_SHIPPER_SHOP_NAME') ? Configuration::get('DHL_SHIPPER_SHOP_NAME') : Configuration::get('PS_SHOP_NAME'));
		$this->_dhl_shipper_attention_name = Configuration::get('DHL_SHIPPER_ATTENTION_NAME');
		$this->_dhl_shipper_phone = (Configuration::get('DHL_SHIPPER_PHONE') ? Configuration::get('DHL_SHIPPER_PHONE') : Configuration::get('PS_SHOP_PHONE'));
		$this->_dhl_shipper_addr1 = (Configuration::get('DHL_SHIPPER_ADDR1') ? Configuration::get('DHL_SHIPPER_ADDR1') : Configuration::get('PS_SHOP_ADDR1'));
		$this->_dhl_shipper_addr2 = (Configuration::get('DHL_SHIPPER_ADDR2') ? Configuration::get('DHL_SHIPPER_ADDR2') : Configuration::get('PS_SHOP_ADDR2'));
		$this->_dhl_shipper_city = (Configuration::get('DHL_SHIPPER_CITY') ? Configuration::get('DHL_SHIPPER_CITY') : Configuration::get('PS_SHOP_CITY'));
		$this->_dhl_shipper_country = (Configuration::get('DHL_SHIPPER_COUNTRY') ? Configuration::get('DHL_SHIPPER_COUNTRY') : Configuration::get('DHL_ORIGIN_COUNTRY'));
		$this->_dhl_shipper_state = (Configuration::get('DHL_SHIPPER_STATE') ? Configuration::get('DHL_SHIPPER_STATE') : Configuration::get('DHL_ORIGIN_STATE'));
		$this->_dhl_shipper_postcode = (Configuration::get('DHL_SHIPPER_POSTCODE') ? Configuration::get('DHL_SHIPPER_POSTCODE') : Configuration::get('PS_SHOP_CODE'));
		/** LABEL PRINTING: GLOBAL INFORMATION */
		$this->_dhl_enable_labels = Configuration::get('DHL_ENABLE_LABELS');
		$this->_dhl_label_order_status = Configuration::get('DHL_LABEL_ORDER_STATUS');
		$this->_dhl_label_format = Configuration::get('DHL_LABEL_FORMAT');
		
		$this->_dhl_random = Configuration::get('DHL_RANDOM');
		if ($this->_dhl_random == '')
		{
			$this->_dhl_random = md5(mt_rand().time());
			Configuration::updateValue('DHL_RANDOM', $this->_dhl_random);
		}
		$this->_dhl_log_filename = "dhl_xml_log_".$this->_dhl_random.".txt";
	}

	public function getContent()
	{
		$this->_postProcess();
		$this->_displayForm();

		return $this->_html;
	}

	protected function _displayForm()
	{
		$ps_version  = $this->getPSV();
		$this->checkHookCartShippingPreview();
		$this->applyUpdates();
		$verified = $this->checkDHLSettings();
		$zones = Zone::getZones(true);
		$carriers = $this->getCarriers();
		$weightUnit = strtolower(Configuration::get('PS_WEIGHT_UNIT'));
		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
		$dropoff_types = $this->getDropoffTypes();
		$ship_meth = $this->getShippingMethods();
		$package_types = $this->getPackageTypes();
		$countries = Country::getCountries($this->context->language->id, true);
		
		$this->_html .= '<link type="text/css" rel="stylesheet" href="'.$this->_path.'css/admin.css" />';
		$this->_html .= '<link type="text/css" rel="stylesheet" href="'.$this->_path.'css/tooltipster.css" />';
		if($ps_version == 1.6)
		{
			$this->_html .= '
			<style>
			sup {
				color: #CC0000;
				font-weight: bold;
			}
			</style> 
			';
		}
		
		$this->_html .= '
			<script type="text/javascript">
				var invalid_site_id = "'.$this->l('You must enter a valid DHL Site ID').'";
				var invalid_pass = "'.$this->l('You must enter a valid DHL Password').'";
				var invalid_zip = "'.$this->l('You must enter a valid Zipcode, if there are no Zipcodes in your country, enter 00000').'";
				var module_path = "'.(isset($this->context->shop->virtual_uri) && $this->context->shop->virtual_uri != '' ? substr($this->context->shop->virtual_uri,0, -1) : '').$this->_path.'";
			</script>
			<script type="text/javascript" src="'.$this->_path.'js/back_office.js"></script>
			<script type="text/javascript" src="'.$this->_path.'js/jquery.tooltipster.min.js"></script>
		';
		$this->_html .= 
			($ps_version >= 1.5 ? '<div style="'.($ps_version < 1.6 ? 'width:900px;margin:auto' : '' ).'">' : '').
			$this->getModuleRecommendations('DHL').
			'<h2 style="clear:both;padding-top:5px;">'.$this->displayName.' '.$this->version.'</h2>'.
			'<b>'.$this->l('For any technical questions, or problems with the module, please contact us using our').' <a href="https://www.presto-changeo.com/en/contact_us" target="_index"><u>'.$this->l('Contact Form').'</u></b></a><br /><br />';
		if($verified == 28)
        {
            if($this->getPSV() == '1.6')
                $this->_html .= $this->displayError($this->l('Unable to connect to DHL server'));
            else
			    $this->_html .= '<div class="alert error">'.$this->l('Unable to connect to DHL server').'</div>';
        }
			
		if ($url = $this->upgradeCheck('DHL'))
		{
			$this->_html .=
			($ps_version >= 1.6?'<div class="panel">':
			'<fieldset class="width3" style="background-color:#FFFAC6;width:900px;">').
			($ps_version < 1.6 ? '<legend>': '<h3>').
			'<img src="'.$this->_path.'logo.gif" style="'.($this->getPSV() == 1.6 ? 'margin: 0 10px 0 0;' : '').'" /> '.$this->l('New Version Available').
			($ps_version < 1.6 ? '</legend>': '</h3>').
			$this->l('We have released a new version of the module. For a list of new features, improvements and bug fixes, view the ').'<a href="'.$url.'#change" target="_index"><b><u>'.$this->l('Change Log').'</b></u></a> '.$this->l('on our site.').'
			<br />'.
			$this->l('For real-time alerts about module updates, be sure to join us on our') .' <a href="http://www.facebook.com/pages/Presto-Changeo/333091712684" target="_index"><u><b>Facebook</b></u></a> / <a href="http://twitter.com/prestochangeo1" target="_index"><u><b>Twitter</b></u></a> '.$this->l('pages').'.
			<br /><br />'.
			$this->l('Please').' <a href="https://www.presto-changeo.com/en/contact_us" target="_index"><b><u>'.$this->l('contact us').'</u></b></a> '.$this->l('to request an upgrade to the latest version.').
			($ps_version < 1.6 ? '</fieldset>': '</div>').'
			<br />';
		}

		$this->_html .= '
			<form action="'.$_SERVER['REQUEST_URI'].'" name="dhl_form" id="dhl_form" method="post">
				'.($ps_version >= 1.6?'<div class="panel">':
				'<fieldset class="width3" style="width:900px;">').
				($ps_version < 1.6 ? '<legend>': '<h3>').'
					<img src="'.$this->_path.'logo.gif" style="'.($this->getPSV() == 1.6 ? 'margin: 0 10px 0 0;' : '').'" /> '.$this->l('Installation Instructions').' (<a href="'.$_SERVER['REQUEST_URI'].'&ups_shi='.Configuration::get('UPS_INSTALL').'" style="color:blue;text-decoration:underline">'.(Configuration::get('UPS_INSTALL')=="inline"?"Collapse":"Expand").'</a>)
				'.($ps_version < 1.6 ? '</legend>': '</h3>').'
				<div id="dhl_install" style="padding-left:10px;display:'.Configuration::get('DHL_INSTALL').'">
					<table width="900" style="font-size:13px;">
		';
		
		if ($weightUnit != "lb" && $weightUnit != "lbs" && $weightUnit != "kg" && $weightUnit != "oz" && $weightUnit != "g")
		{	
			$this->_html .= '
				<tr height="30">
					<td align="left">
						<li style="margin-left:10px"><b style="color:red">'.$this->l('The Module doesn\'t recognize your weight unit, please use lb, kg, oz or g').'.</b></li>
					</td>
				</tr>
			';
		}
		
		$this->_html .= '
				<tr height="40">
					<td align="left">
						<b>'.$this->l('1) To enable Block Cart Preview, the following changes need to be made:').'</b><br/>
						<p style="margin-left:10px;margin-top:0;">
		';
		
		if($ps_version < 1.5)
		{
			$modified_file = @file(dirname(__FILE__).'/override_1.4/classes/FrontController.php');
			$server_file = @file(dirname(__FILE__).'/../../override/classes/FrontController.php');
			if (sizeof($server_file) <= 1 || !$this->overrideCheck($modified_file, $server_file))
				$this->_html .= '
						<br />'.$this->l('Copy').'&nbsp; <b>/modules/dhl/override_1.4/classes/FrontController.php</b> &nbsp;'.$this->l('to').'&nbsp; <b>/override/classes</b>
						<br />
						'.$this->l('If a file with the exact name already exists (not one starting with _ ), then copy line #33 from our modified file to your file (place it inside the preProcess() function.');
			else
				$this->_html .= '
						<br /><b style="color:green">FrontController.php '.$this->l('is installed correctly').'</b>';

			$this->_html .= '
						<br />
						<br />
						'.$this->l('Edit').'&nbsp; <b>/modules/blockcart/blockcart.tpl</b> '.$this->l('and add the following line where you want the button to appear (typically after the last').' &lt;/p&gt; '.$this->l('in the file').'.
						<br />
						<br />
						{if isset($HOOK_CART_SHIPPING_PREVIEW)}
						<br />
						&nbsp;&nbsp;&nbsp;&nbsp;{$HOOK_CART_SHIPPING_PREVIEW}
						<br />
						{/if}
						<br />
						<br />
						'.$this->l('Make sure to clear the smarty cache (turn on force recompile in Preferences->Performance)');
		}
		else
		{
			if($ps_version == 1.5)
				$overridePath = 'override_1.5';
			elseif($ps_version == 1.6)
				$overridePath = 'override_1.6';
			
			$modified_file = @file(dirname(__FILE__).'/'.$overridePath.'/classes/Carrier.php');
			$server_file = @file(dirname(__FILE__).'/../../override/classes/Carrier.php');
			if (sizeof($server_file) <= 1 || !$this->overrideCheck($modified_file, $server_file))
			$this->_html .= '
						<br />'.$this->l('Copy').'&nbsp; <b>/modules/dhl/'.$overridePath.'/classes/Carrier.php</b> &nbsp;'.$this->l('to').'&nbsp; <b>/override/classes</b>
						<br />
							'.$this->l('If a file with the exact name already exists, then copy lines #24-27 from our modified file to your file.');
				else
					$this->_html .= '
						<br /><b style="color:green">Carrier.php '.$this->l('is installed correctly').'</b>';

				$this->_html .= '
						<br />
						<br />
						'.$this->l('Edit').'&nbsp; <b>/modules/blockcart/blockcart.tpl</b> '.$this->l('and add the following line where you want the button to appear (typically after the last').' &lt;/p&gt; '.$this->l('in the file').'.
						<br />
						<br />
						{hook h = "cartShippingPreview"}
						<br />
						<br />
						'.$this->l('Make sure to clear the smarty cache (turn on force recompile in Preferences->Performance)');
		}
				$this->_html .= '
						</p>
							<br />
							<b>'.$this->l('2) To obtain shipping rates and generate labels on your site you will need a DHL Login and Password (different from the ones you use to login to their website). To obtain this info, send an email to ').'<a href="mailto:xmlrequests@dhl.com" style="color:blue">xmlrequests@dhl.com</a>'.$this->l('. In the email state that you are integrating your ecommerce website with DHL and require a DHL Login and Password to access their API. Once you receive the login info enter it below.').'</b>
			   <br />
			   <br />
							<b>'.$this->l('3) To configure this module, complete each of the 6 sections below. As you successfully enter the requested information, each section will turn from red to green once you press "Update" to signify that the section is complete.').'</b>
							<p style="margin-left:10px">

							</p>
					</td>
				</tr>
				</table>
					</div>
			'.($ps_version < 1.6 ? '</fieldset>': '</div>').'
			
			<br />
			
			'.($ps_version >= 1.6?'<div class="panel">':
			'<fieldset class="width3" style="width:900px;">').
				($ps_version < 1.6 ? '<legend>': '<h3>').'
					<img src="'.$this->_path.'logo.gif" style="'.($this->getPSV() == 1.6 ? 'margin: 0 10px 0 0;' : '').'" /> '.$this->l('DHL Account Setting').'
				'.($ps_version < 1.6 ? '</legend>': '</h3>').'
				
				'.($ps_version >= 1.6?'<div class="panel">':
				'<fieldset class="inner_fieldset" style="width:850px;">').
					($ps_version < 1.6 ? '<legend class="'.($verified ? 'validated' : 'not_validated').'">': '<h3 class="'.($verified ? 'validated' : 'not_validated').'">').'
						<img src="'.$this->_path.'logo.gif" style="'.($this->getPSV() == 1.6 ? 'margin: 0 10px 0 0;' : '').'" /> '.$this->l('Step 1: Account Info').'
					'.($ps_version < 1.6 ? '</legend>': '</h3>').'

					<table width="100%" cellpadding="0" cellspacing="0">
						<tr height="31">
							<td align="right" width="20%">
								'.$this->l('DHL Login').':&nbsp;
							</td>
							<td align="left" width="25%">
								<input type="text" name="dhl_site_id" style="width:190px;float: left;margin: 0 10px 0 0;" id="dhl_site_id" value="'.Tools::getValue('dhl_site_id', $this->_dhl_site_id).'" />
							</td>
							<td align="right" width="20%">
								'.$this->l('Account Number').':&nbsp;
							</td>
							<td align="left">
								<input type="text" name="dhl_account_number" style="width:190px;float: left;margin: 0 10px 0 0;" id="dhl_account_number" value="'.Tools::getValue('dhl_account_number', $this->_dhl_account_number).'" />
								<span class="info_tooltip" title="
									'.$this->l('Account Number only required for label printing or discounted rates. If your DHL account is eligible for discounted rates or you plan to print labels, enter your account number here.').'
								"></span>
							</td>
						</tr>
						<tr height="31">
							<td align="right" width="20%">
								'.$this->l('Password').':&nbsp;
							</td>
							<td align="left" width="25%">
								<input type="text" name="dhl_pass" style="width:190px;float: left;margin: 0 10px 0 0;" id="dhl_pass" value="'.Tools::getValue('dhl_pass', $this->_dhl_pass).'" />
							</td>
							<td align="right" width="20%">
								'.$this->l('Origin Zipcode').':&nbsp;
							</td>
							<td align="left">
								<input type="text" name="dhl_origin_zip" style="width:190px;float: left;margin: 0 10px 0 0;" id="dhl_origin_zip" value="'.Tools::getValue('dhl_origin_zip', $this->_dhl_origin_zip).'" />
								<span class="info_tooltip" title="
									'.$this->l('Zipcode where you ship your packages from.').'
								"></span>
							</td>
						</tr>
						<tr height="31">
							<td align="right" width="20%">
								'.$this->l('Origin Country').':&nbsp;
							</td>
							<td align="left" width="25%">
								<select name="dhl_origin_country" style="'.($this->getPSV() < 1.6 ? 'width:200px;padding: 1px;' : 'width:190px;').' float: left;margin: 0 10px 0 0;">';
									foreach ($countries as $country)
										$this->_html .= '<option value="'.$country['iso_code'].'" '.(Tools::getValue('dhl_origin_country', $this->_dhl_origin_country) == $country['iso_code']?"selected":"").'>'.$country['name'].'</option>';
									$this->_html .= '
								</select>
							</td>
							<td align="right" width="20%">
								'.$this->l('Origin City').':&nbsp;
							</td>
							<td align="left">
								<input type="text" name="dhl_origin_city" value="'.Tools::getValue('dhl_origin_city', $this->_dhl_origin_city).'" style="width:190px;float: left;margin: 0 10px 0 0;">
							</td>
						</tr>
						<tr height="31">
							<td align="right" width="20%">
								'.$this->l('Account Mode').':&nbsp;
							</td>
							<td align="left" width="25%">
								<select name="dhl_mode" style="'.($this->getPSV() < 1.6 ? 'width:200px;padding: 1px;' : 'width:190px;').' float: left;margin: 0 10px 0 0;">
									<option value="test" '.(Tools::getValue('dhl_mode', $this->_dhl_mode) == 'test' ? 'selected="selected"' : '').'>'.$this->l('Development').'</option>
									<option value="live" '.(Tools::getValue('dhl_mode', $this->_dhl_mode) == 'live' ? 'selected="selected"' : '').'>'.$this->l('Production').'</option>
								</select>
							</td>
							<td align="right" width="20%">
								&nbsp;
							</td>
							<td align="left">
								<b style="color:'.($verified == 1 ? 'green">'.$this->l('Account Setting Verified') : ($verified == 28 ? 'red">' : 'red">'.$this->l('Incorrect Account Setting'))).'</b>
							</td>
						</tr>
						<tr height="31">
							<td colspan="4" style="text-align:center;">
								<input onclick="return validate_as()" type="submit" value="'.$this->l('Update').'" name="updateSettings" class="'.($this->getPSV() == 1.6 ? 'btn btn-default' : 'button').'" style="width: 100px;" />
							</td>
						</tr>
					</table>
				'.($ps_version < 1.6 ? '</fieldset>': '</div>').'

				'.($ps_version >= 1.6?'<div class="panel">':
				'<fieldset class="inner_fieldset" style="width:900px;">').
					($ps_version < 1.6 ? '<legend class="validated">': '<h3 class="validated">').'
						<img src="'.$this->_path.'logo.gif" style="'.($this->getPSV() == 1.6 ? 'margin: 0 10px 0 0;' : '').'" /> '.$this->l('Step 2: Shipping Preferences').'
					'.($ps_version < 1.6 ? '</legend>': '</h3>').'

				<table width="95%" cellpadding="0" cellspacing="0">
						<tr height="31">
							<td align="right" width="20%">
								'.$this->l('Units').':&nbsp;
							</td>
							<td align="left" width="25%">
								<select name="dhl_unit" id="dhl_unit" style="'.($this->getPSV() < 1.6 ? 'width:200px;padding: 1px;' : 'width:190px;').' float: left;margin: 0 10px 0 0;">
									<option value="LBS" '.(Tools::getValue('dhl_unit', $this->_dhl_unit) == "LBS"?"selected":"").'>'.$this->l('LBS / IN').'</option>
									<option value="KGS" '.(Tools::getValue('dhl_unit', $this->_dhl_unit) == "KGS"?"selected":"").'>'.$this->l('KGS / CM').'</option>
								</select>
							</td>
							
							<td align="right" width="20%">
								'.$this->l('Dropoff Point').':&nbsp;
							</td>
							<td align="left">
								<select name="dhl_dropoff" id="dhl_dropoff" style="'.($this->getPSV() < 1.6 ? 'width:200px;padding: 1px;' : 'width:190px;').' float: left;margin: 0 10px 0 0;">';
								foreach ($dropoff_types as $code => $type) {
									$this->_html .= '<option value="'.$code.'" '.(Tools::getValue('dhl_dropoff', $this->_dhl_dropoff) == $code ? "selected" : "").'>'.$type.'</option>';
								}
								$this->_html .= '
								</select>
							</td>
						</tr>
					</table>
				'.($ps_version < 1.6 ? '</fieldset>': '</div>');  
				
					if (($this->_dhl_package_size == "product" && is_array($this->_dhl_boxes) && !count($this->_dhl_boxes)))
						$packaging_validated = false;
					elseif ($this->_dhl_weight < 0) 
						$packaging_validated = false;
					else
						$packaging_validated = true;
						
				$this->_html .= '
				'.($ps_version >= 1.6?'<div class="panel">':
				'<fieldset class="inner_fieldset" style="width:900px;">').
					($ps_version < 1.6 ? '<legend class="'.($packaging_validated ? 'validated' : 'not_validated').'">': '<h3 class="'.($packaging_validated ? 'validated' : 'not_validated').'">').'
						<img src="'.$this->_path.'logo.gif" style="'.($this->getPSV() == 1.6 ? 'margin: 0 10px 0 0;' : '').'" /> '.$this->l('Step 3: Packaging').'
					'.($ps_version < 1.6 ? '</legend>': '</h3>').'

					<p>'.$this->l('The default and most basic shipping configuration is by weight only: select "Multiple Boxes", "Fixed Size", leave "Width/Height/Depth" as zero, enter a "Max Weight" and leave "Maximum product per box" as zero.').'</p>
					<p style="padding-bottom:15px;">'.$this->l('Or, to let this module determine the number of boxes needed based on product dimensions, select "Multiple Boxes" and "Product Dimensions" and add each box size you use, one at a time. Be sure all of your products have width/height/depth defined.').'</p>
					<b style="float: left;">'.$this->l('Packaging Type').':&nbsp;</b>
					<select name="dhl_pack" id="dhl_pack"  style="'.($this->getPSV() < 1.6 ? 'width:200px;padding: 1px;' : 'width:190px;').' float: left;margin: 0 10px 0 0;">';
						foreach ($package_types as $code => $type) {
							$this->_html .= '<option value="'.$code.'" '.(Tools::getValue('dhl_pack', $this->_dhl_pack) == $code ? "selected" : "").'>'.$type.'</option>';
						}
						$this->_html .= '
					</select>
					
					<span class="info_tooltip" title="
						'.$this->l('Size/type of the package which you generally use for DHL shipments. \'Your Packaging\' requires that you enter box size information (When selected, additional fields appear bellow).').'
					"></span>
					
					<br /><br />
					<div id="dhl_my_pack" style="display:'.($this->_dhl_pack == "" || $this->_dhl_pack == "CP" ? "block" : "none").'">
						<table width="100%" class="packaging_table" style="margin-left:20px;">
							<tr height="31">
								<td align="left" width="35%">
									<span style="vertical-align:middle;margin-right: 40px;font-weight:bold;">'.$this->l('Number of Boxes').':</span>
								</td>
								<td align="left" width="15%">
									<input type="radio" name="dhl_packages" id="dhl_packages_single" onclick="show_package_size();" value="single" '.(Tools::getValue('dhl_packages', $this->_dhl_packages) == "single"?'checked':'').' />
									<label for="dhl_packages_single" style="float:none;display:inline;font-weight:normal;vertical-align:middle;">'.$this->l('Single box').'</label>
								
									<span class="info_tooltip" title="
										'.$this->l('Select \'Single box\' if you always ship each order in only one box.').'
									"></span>
								</td>
								<td align="left" width="5%">
									'.$this->l('or').'
								</td>
								<td align="left">
									<input type="radio" name="dhl_packages" id="dhl_packages_multiple" onclick="show_package_size();" value="multiple" '.(Tools::getValue('dhl_packages', $this->_dhl_packages) == "multiple"?'checked':'').' />
									<label for="dhl_packages_multiple" style="float:none;display:inline;font-weight:normal;vertical-align:middle;">'.$this->l('Multiple boxes').'</label>
									
									<span class="info_tooltip" title="
										'.$this->l('Select \'Multiple boxes\' if your shipments sometimes requires the use of more than one box. If you choose Multiple boxes, you can then choose between \'Fixed Size\' and \'Product Dimensions\'.').'
									"></span>
								</td>
							</tr>
							<tr height="31">
								<td align="left">
									<span style="vertical-align:middle;margin-right: 40px;font-weight:bold;">'.$this->l('Calculate # of Products Per Box By').':</span>
								</td>
								<td align="left">
									<input type="radio" name="dhl_package_size" id="dhl_package_size_fixed" onclick="show_package_size();" value="fixed" '.(Tools::getValue('dhl_package_size', $this->_dhl_package_size) == "fixed"?'checked':'').' />
									<label for="dhl_package_size_fixed" style="float:none;display:inline;font-weight:normal;vertical-align:middle;">'.$this->l('Fixed Size').'</label>

									<span class="info_tooltip" title="
										'.$this->l('Select \'Fixed size\' if you use only one box size. You will be able to select number of products per box, or use maximum weight per box.').'<br />'.$this->l('The number of boxes needed will be determined by weight or max products per box (Requires entering only weight for each product).').'
									"></span>
								</td>
								<td align="left">
									'.$this->l('or').'
								</td>
								<td align="left">
									<input type="radio" name="dhl_package_size" id="dhl_package_size_product" onclick="show_package_size();" value="product" '.(Tools::getValue('dhl_package_size', $this->_dhl_package_size) == "product"?'checked':'').' />
									<label for="dhl_package_size_product" style="float:none;display:inline;font-weight:normal;vertical-align:middle;">'.$this->l('Product Dimensions').'</label>
									
									<span class="info_tooltip" title="
										'.$this->l('Select \'Product dimensions\' if you use more than one size box. The boxes needed for each order will be determined by the box dimentions as well as product dimension (Requires entering dimension for each product).').'<br />'.$this->l('The module will automatically detect the number of products per box. It will attempt to use the smallest / lowest number of boxes based on the cart contents. Be sure all products have dimensions entered.').'
									"></span>
								</td>
							</tr>
							<tr height="31">
								<td align="left" colspan="4">
									<div id="fixed_box" style="display:none;">
										<b>'.$this->l('Box Dimensions').' ('.Configuration::get('PS_WEIGHT_UNIT').'/'.Configuration::get('PS_DIMENSION_UNIT').')</b>:<br/>
										
										<div style="float: left; margin: 5px 2% 15px 0;">
											'.($this->getPSV() != 1.6 ? '&nbsp;&nbsp;' : '').'<small>'.$this->l('Width').':</small>
											'.($this->getPSV() == 1.6 ? '<br />' : '').'
											<input type="text" name="dhl_width" class="needValidate" style="'.($this->getPSV() == 1.6 ? 'float: left; width: 50px; margin: 0 5px 0 0;' : 'width:20px;').'" id="dhl_width" value="'.Tools::getValue('dhl_width', $this->_dhl_width, 10).'" />
											'.($this->getPSV() != 1.6 ? '&nbsp;' : '').'
											
											<span class="info_tooltip" title="
												'.$this->l('Enter the Width of a box which you will use for shipping (Value must be greater than 0). You can enter multiple boxes.').'
											"></span>
										</div>
										<div style="float: left; margin: 5px 2% 15px 0;">
											'.($this->getPSV() != 1.6 ? '&nbsp;&nbsp;' : '').'<small>'.$this->l('Height').':</small>
											'.($this->getPSV() == 1.6 ? '<br />' : '').'
											<input type="text" name="dhl_height" class="needValidate" style="'.($this->getPSV() == 1.6 ? 'float: left; width: 50px; margin: 0 5px 0 0;' : 'width:20px;').'" id="dhl_height" value="'.Tools::getValue('dhl_height', $this->_dhl_height, 10).'" />&nbsp;
											'.($this->getPSV() != 1.6 ? '&nbsp;' : '').'
											
											<span class="info_tooltip" title="
												'.$this->l('Enter the Height of a box which you will use for shipping (Value must be greater than 0). You can enter multiple boxes.').'
											"></span>
										</div>
										<div style="float: left; margin: 5px 2% 15px 0;">
											'.($this->getPSV() != 1.6 ? '&nbsp;&nbsp;' : '').'<small>'.$this->l('Depth / Length').':</small>
											'.($this->getPSV() == 1.6 ? '<br />' : '').'
											<input type="text" name="dhl_depth" class="needValidate" style="'.($this->getPSV() == 1.6 ? 'float: left; width: 50px; margin: 0 5px 0 0;' : 'width:20px;').'" id="dhl_depth" value="'.Tools::getValue('dhl_depth', $this->_dhl_depth, 10).'" />
											'.($this->getPSV() != 1.6 ? '&nbsp;' : '').'
											
											<span class="info_tooltip" title="
												'.$this->l('Enter the Depth/Length of a box which you will use for shipping (Value must be greater than 0). You can enter multiple boxes.').'
											"></span>
										</div>
										<div style="float: left; margin: 5px 2% 15px 0;">
											'.($this->getPSV() != 1.6 ? '&nbsp;&nbsp;' : '').'<small>'.$this->l('Max Weight').':</small>
											'.($this->getPSV() == 1.6 ? '<br />' : '').'
											<input type="text" name="dhl_weight" class="needValidate" style="'.($this->getPSV() == 1.6 ? 'float: left; width: 60px; margin: 0 5px 0 0;' : 'width:26px;').'" id="dhl_weight" value="'.Tools::getValue('dhl_weight', $this->_dhl_weight, 150).'" /> '.$this->l('(Must be greater than 0)').'
											
											<span class="info_tooltip" title="
												'.$this->l('Enter the maximum Weight of a box which you will use for shipping (Value must be greater than 0). You can enter multiple boxes.').'
											"></span>
										</div>                                    
									</div>
									<div id="fixed_box_multiple" style="margin-top:8px;display:none;clear:left;'.($this->getPSV() == 1.6 ? 'float:left;' : '').'">
										'.($this->getPSV() != 1.6 ? '&nbsp;&nbsp;' : '<span style="float: left; margin: 0 5px 0 0;">').$this->l('Maximum products per box: ').($this->getPSV() == 1.6 ? '</span>' : '').'
										<input type="text" name="dhl_packages_per_box" style="'.($this->getPSV() == 1.6 ? 'float: left; width: 50px; margin: 0 5px 0 0;' : 'width:30px;').'" id="dhl_packages_per_box" value="'.Tools::getValue('dhl_packages_per_box', $this->_dhl_packages_per_box).'" />
										
										<span class="info_tooltip" title="
											'.$this->l('Enter the maximum number of product that can fit in a box which you will use for shipping (Enter 0 to determine by Weight)').'
										"></span>
									</div>
									<div id="fixed_multi_box" style="display:none">
										<b style="float: left;  margin: 0 0 10px 0;">'.$this->l('Box Dimension').' ('.Configuration::get('PS_WEIGHT_UNIT').'/'.Configuration::get('PS_DIMENSION_UNIT').'):</b>
										&nbsp;'.$this->l('(Add the dimensions for each of the boxes you use to ship your products, click the').' <img src="'.$this->_path.'img/add.gif" style="cursor:pointer;vertical-align:middle;" /> '.$this->l('to add a new box.').')
										
										<div style="float: left; margin: 5px 2% 15px 0;clear:both">
											'.($this->getPSV() != 1.6 ? '&nbsp;&nbsp;' : '').'<small>'.$this->l('Width').'</small>
											'.($this->getPSV() == 1.6 ? '<br />' : '').'
											<input type="text" name="dhl_box_width" value="10" class="needValidate" style="'.($this->getPSV() == 1.6 ? 'float: left; width: 50px; margin: 0 5px 0 0;' : 'width:20px;').'" id="dhl_box_width" />
											'.($this->getPSV() != 1.6 ? '&nbsp;' : '').'
																						
											<span class="info_tooltip" title="
												'.$this->l('Enter the Width of a box which you will use for shipping (Value must be greater than 0). You can enter multiple boxes.').'
											"></span>							   
										</div>
										
										<div style="float: left; margin: 5px 2% 15px 0;">
											'.($this->getPSV() != 1.6 ? '&nbsp;&nbsp;' : '').'<small>'.$this->l('Height').'</small>
											'.($this->getPSV() == 1.6 ? '<br />' : '').'
											<input type="text" name="dhl_box_height" value="10" class="needValidate" style="'.($this->getPSV() == 1.6 ? 'float: left; width: 50px; margin: 0 5px 0 0;' : 'width:20px;').'" id="dhl_box_height" />
											'.($this->getPSV() != 1.6 ? '&nbsp;' : '').'

											<span class="info_tooltip" title="
												'.$this->l('Enter the Height of a box which you will use for shipping (Value must be greater than 0). You can enter multiple boxes.').'
											"></span>
										</div>

										<div style="float: left; margin: 5px 2% 15px 0;">
											'.($this->getPSV() != 1.6 ? '&nbsp;&nbsp;' : '').'<small>'.$this->l('Depth / Length').'</small>
											'.($this->getPSV() == 1.6 ? '<br />' : '').'
											<input type="text" name="dhl_box_depth" value="10" class="needValidate" style="'.($this->getPSV() == 1.6 ? 'float: left; width: 50px; margin: 0 5px 0 0;' : 'width:20px;').'" id="dhl_box_depth" />

											<span class="info_tooltip" title="
												'.$this->l('Enter the Depth/Length of a box which you will use for shipping (Value must be greater than 0). You can enter multiple boxes.').'
											"></span>
										</div>
										
										<div style="float: left; margin: 5px 2% 15px 0;">
											'.($this->getPSV() != 1.6 ? '&nbsp;&nbsp;' : '').'<small>'.$this->l('Max Weight').'</small>
											'.($this->getPSV() == 1.6 ? '<br />' : '').'
											<input type="text" name="dhl_box_weight" value="150" class="needValidate" style="'.($this->getPSV() == 1.6 ? 'float: left; width: 60px; margin: 0 5px 0 0;' : 'width:26px;').'" id="dhl_box_weight" /> '.$this->l('(Must be greater than 0)').'

											<span class="info_tooltip" title="
												'.$this->l('Enter the maximum Weight of a box which you will use for shipping (Value must be greater than 0). You can enter multiple boxes.').'
											"></span>
										</div>
										
										<div style="float: left; margin: 5px 2% 15px 0;">
											'.($this->getPSV() != 1.6 ? '&nbsp;&nbsp;&nbsp;&nbsp;' : '').'
											<img src="'.$this->_path.'img/add.gif" style="cursor:pointer;vertical-align:middle;'.($this->getPSV() == 1.6 ? 'margin: 20px 0 0 15px;' : '').'" onclick="add_box()" />
										</div>
										
										<div id="dhl_boxes" style="clear:both"></div>
									</div>
									<div id="product_box" style="display:none;margin-top:6px;clear:both;">
										<table width="100%">
											<tr height="31">
												<td align="left">
													<li style="left-margin:10px">
													<i style="color:red">'.$this->l('The detection algorithm does its best to estimate the number of boxes needed to fit all the products, it may over estimate').'.</i>
													</li>
													<li style="left-margin:10px">
													<i style="color:green">'.$this->l('If the product dimension are bigger than the "Box Dimensions" above, a new box the size of the product will be added').'.</i>
													</li>
												</td>
											</tr>
											<tr height="31">
												<td align="left">
													'.($this->getPSV() == 1.6 ? '<span style="float: left; margin: 0 5px 0 0;">' : '').'
														'.$this->l('Debug Mode').':
													'.($this->getPSV() == 1.6 ? '</span>' : '').'
													<select name="dhl_debug_mode" style="width:60px; '.($this->getPSV() == 1.6 ? 'float: left; margin: 0 5px 0 0;' : '').'">
														<option value="0"'.($this->_dhl_debug_mode != 1?' selected':'').'>'.$this->l('Off').'</option>
														<option value="1"'.($this->_dhl_debug_mode == 1?' selected':'').'>'.$this->l('On').'</option>
													</select>
													
													<span class="info_tooltip" title="
														'.$this->l('Will show the number of boxes used and their size / weight (when not getting rates from the cache)').'.
													"></span>
												</td>
											</tr>
										</table>
									</div>
								</td>
							</tr>
						</table>
					</div>
				'.($ps_version < 1.6 ? '</fieldset>': '</div>').'
				
				'.($ps_version >= 1.6?'<div class="panel">':
				'<fieldset class="inner_fieldset" style="width:900px;">').
					($ps_version < 1.6 ? '<legend class="validated">': '<h3 class="validated">').'
						<img src="'.$this->_path.'logo.gif" style="'.($this->getPSV() == 1.6 ? 'margin: 0 10px 0 0;' : '').'" /> '.$this->l('Step 4: Module Settings').'
					'.($ps_version < 1.6 ? '</legend>': '</h3>').'
					
					<table width="100%" cellpadding="0" cellspacing="0">
					<tr height="31">
						<td>
							'.$this->l('"Shipping Rates" Buttons').':&nbsp;
						</td>
						
						<td align="left">
							<select name="ssr" style="'.($this->getPSV() == 1.6 ? ' float: left;width: 90px;margin: 0 5px 0 0;' : '').'">
								<option '.(Configuration::get('DHL_SHIPPING_RATES_BUTTON') == 'show' ? 'selected="selected"' : '').' value="show">'.$this->l('Show').'</option>
								<option '.(Configuration::get('DHL_SHIPPING_RATES_BUTTON') == 'hide' ? 'selected="selected"' : '').' value="hide">'.$this->l('Hide').'</option>
							</select>
							
							<span class="info_tooltip" title="
								'.$this->l('* When using more than one of our shipping modules, only one should be set as \'Show\'').'
								<br /><br />
								<img src=\''.$this->_path.'img/shipping_preview.jpg\' alt=\'?\' />
							"></span>
						</td>
					</tr>
					<tr height="31">
						<td align="left">
							'.$this->l('Shipping Rate Address').':&nbsp;
						</td>
						<td align="left">
							<input type="checkbox" id="dhl_country_display" name="dhl_country_display" value="1" '.(Tools::getValue('dhl_country_display', $this->_dhl_address_display['country']) == '1'?'checked="checked"':'').' />
							'.$this->l('Country').'&nbsp;&nbsp;&nbsp;&nbsp;

							<input type="checkbox" id="dhl_state_display" name="dhl_state_display" value="1" '.(Tools::getValue('dhl_state_display', $this->_dhl_address_display['state']) == '1'?'checked="checked"':'').' />
							'.$this->l('State').'&nbsp;&nbsp;&nbsp;&nbsp;

							<input type="checkbox" id="dhl_city_display" name="dhl_city_display" value="1" '.(Tools::getValue('dhl_city_display', $this->_dhl_address_display['city']) == '1'?'checked="checked"':'').' />
							'.$this->l('City').'&nbsp;&nbsp;&nbsp;&nbsp;

							<input type="checkbox" id="dhl_zip_display" name="dhl_zip_display" value="1" '.(Tools::getValue('dhl_zip_display', $this->_dhl_address_display['zip']) == '1'?'checked="checked"':'').' />
							'.$this->l('Zip').'&nbsp;&nbsp;&nbsp;&nbsp;
							
							<span class="info_tooltip" title="
								'.$this->l('Select the fields you want to display in the cart preview. City is only required for international shipping.').'
							"></span>
						</td>
					</tr>
					<tr height="31">
						<td width="180">
								'.$this->l('Address Override').':&nbsp;
						</td>
						<td align="left">
							<input type="checkbox" id="dhl_override_address" name="dhl_override_address" value="1" '.($this->_dhl_override_address == 1?'checked':'').' />
							<label for="dhl_override_address" style="float:none;display:inline;font-weight:normal;">'.$this->l('Update the customer\'s account address with the shipping preview address').'</label>
						</td>
					</tr>
					<tr height="31">
						<td align="left">
							'.$this->l('XML Log').':&nbsp;
						</td>
						<td align="left">
							<input type="checkbox" id="dhl_xml_log" name="dhl_xml_log" value="1" '.(Tools::getValue('dhl_xml_log', $this->_dhl_xml_log) == '1'?'checked="checked"':'').' />
							<label for="dhl_xml_log" style="float:none;display:inline;font-weight:normal;">'.$this->l('Create a log of the request and response from DHL').'</label>
							
							<span class="info_tooltip" title="
								'.$this->l('The XML log will show the request and response from DHL, you can use it to see exactly which shipping options are being returned, and refer to it if you think the rates you are getting back are different that the rates you get on dhl.com. The log will only show new requests that are not already cached (clear the cache if needed).').'
								<br /><br />
								'.$this->l('The log is saved in').'
								<br />
								'.__PS_BASE_URI__.'modules/dhl/logs/'.$this->_dhl_log_filename.'
							"></span>
							
							'.(file_exists(dirname(__FILE__).'/logs/'.$this->_dhl_log_filename)?'(<a href="'.__PS_BASE_URI__.'modules/dhl/logs/'.$this->_dhl_log_filename.'" target="_index" style="color:blue;">View Log</a>)':'').'
						</td>
					</tr>
					<tr height="31">
						<td align="left">
							'.$this->l('Currency Used:').'
						</td>
						
						<td align="left" colspan="4">
							<select name="dhl_currency_used" style="'.($this->getPSV() < 1.6 ? 'width:200px;padding: 1px;' : 'width:190px;').' float: left;margin: 0 10px 0 0;">
								<option value="BILLCU" '.(Tools::getValue('dhl_currency_used', $this->_dhl_currency_used) == 'BILLCU' ? 'selected="selected"' : '').'>'.$this->l('Billing Currency').'</option>
								<option value="PULCL" '.(Tools::getValue('dhl_currency_used', $this->_dhl_currency_used) == 'PULCL' ? 'selected="selected"' : '').'>'.$this->l('Country of Pickup Local Currency').'</option>
								<option value="BASEC" '.(Tools::getValue('dhl_currency_used', $this->_dhl_currency_used) == 'BASEC' ? 'selected="selected"' : '').'>'.$this->l('Base Currency').'</option>
							</select>
							
							<span class="info_tooltip" title="
								'.$this->l('The currency of the response must be installed, otherwise will be used default currency').'
							"></span>
						</td>
					</tr>
						<tr height="6">
							<td align="left"></td>
						</tr>
						'.($this->_dhl_enable_discount?'<tr>
							<td align="left">
								'.$this->l('Discount Rate:').'
							</td>
							<td align="left" colspan="4">
								<b>0.</b><input type="text" name="dhl_discount_rate" value="'.Tools::getValue('dhl_discount_rate', $this->_dhl_discount_rate).'" style="width:80px;">
							</td>
						</tr>' : '').'
						<tr height="10">
							<td align="left"></td>
						</tr>
						<tr>
							<td colspan="5" align="left">
								<input onclick="return confirm(\''.$this->l('Are you sure you want to delete the cache').'?\');" type="submit" value="'.$this->l('Delete Rate Cache').'" name="deleteCache" class="'.($this->getPSV() == 1.6 ? 'btn btn-default' : 'button').'" />
							</td>
						</tr>
					</table>
				'.($ps_version < 1.6 ? '</fieldset>': '</div>').'
				<div style="text-align:center;">
					<input onclick="return validate_as()" type="submit" value="'.$this->l('Update').'" name="updateSettings" class="'.($this->getPSV() == 1.6 ? 'btn btn-default' : 'button').'" style="width: 100px;" />
				</div>
			'.($ps_version < 1.6 ? '</fieldset>': '</div>').'
			<br />

		'.($ps_version >= 1.6?'<div class="panel">':
		'<fieldset class="width3" style="width:900px;">').
			($ps_version < 1.6 ? '<legend>': '<h3>').'
				<img src="'.$this->_path.'logo.gif" style="'.($this->getPSV() == 1.6 ? 'margin: 0 10px 0 0;' : '').'" /> '.$this->l('Shipping Options (Carriers)').'
			'.($ps_version < 1.6 ? '</legend>': '</h3>').'
			
			'.($ps_version >= 1.6?'<div class="panel">':
			'<fieldset class="inner_fieldset" style="width:900px;">').
				($ps_version < 1.6 ? '<legend class="'.(sizeof($carriers) ? 'validated' : 'not_validated').'">': '<h3 class="'.(sizeof($carriers) ? 'validated' : 'not_validated').'">').'
					<img src="'.$this->_path.'logo.gif" style="'.($this->getPSV() == 1.6 ? 'margin: 0 10px 0 0;' : '').'" /> '.$this->l('Step 5: Add Carriers -').' <span style="font-weight:normal;">'.$this->l('Add at least one carrier here to ensure your shipping option(s) appear in the front office.').'</span>
				'.($ps_version < 1.6 ? '</legend>': '</h3>').'
				
				<table border="0" width="100%">
						<tr height="31>
							<td align="left" colspan="10">
								<p style="color:red;font-weight:normal">
									'.$this->l('Note: before adding carriers, be sure that your PrestaShop zones are setup correctly.').'
									
									<span class="info_tooltip" title="
										'.$this->l('Any Country and State that you ship to must be linked to the same zone as the carrier you add in this module. You can check this from').' '.($ps_version < 1.5?$this->l('Shipping'):$this->l('Localization')).$this->l('->Countries / States').'
									"></span>
								</p>
							</td>
						</tr>
						<tr height="31">
							<td align="left" style="width:15%;">
								'.$this->l('Name').':&nbsp;<sup>*</sup>
							</td>
							<td align="left" style="width:30%;">
								<input type="text" name="dhl_carrier_name" style="width:190px;'.($this->getPSV() == 1.6 ? 'margin: 0 5px 0 0;float: left;' : '').'" id="dhl_carrier_name" value="" />
								
								<span class="info_tooltip" title="
									'.$this->l('Assign a Name to the carrier (the DHL shipping method), for example, DHL Express Worldwide.').'
									<br >
									'.$this->l('Use PrestaShop\'s Shipping > Carriers to edit tax rules, zones or delete a carrier.').'
								"></span>
							</td>
							<td align="left" style="width:1%;"></td>
							<td align="left" style="width:16%;">
								'.$this->l('Free Shipping From').':&nbsp;
							</td>
							<td align="left" style="width:38%;">
								'.($this->getPSV() == 1.6 ? '<span style="float: left; margin: 3px 3px 0 0;">' : '').
									$currency->sign.
								($this->getPSV() == 1.6 ? '</span>' : '').'
								<input type="text" name="dhl_free_shipping" style="width:40px;'.($this->getPSV() == 1.6 ? 'margin: 0 5px 0 0;float: left;' : '').'" id="dhl_free_shipping" value="0" />
								
								<span class="info_tooltip" title="
									'.$this->l('The shipping method will be free when the cart subtotal is above this amount. Leave as zero if you do not want to offer free shipping. You will be able to add free shipping by Product/Category/Manufacturer/Supplier below after you add the carrier.').'
								"></span>
							</td>
						</tr>
						<tr height="31">
							<td align="left">
								'.$this->l('Transit Time').':&nbsp;<sup>*</sup>
							</td>
							<td align="left">
								<input type="text" name="dhl_transit_time" style="width:190px;'.($this->getPSV() == 1.6 ? 'margin: 0 5px 0 0;float: left;' : '').'" id="dhl_transit_time" value="" />
							
								<span class="info_tooltip" title="
									'.$this->l('Appears in the checkout page next to the carrier name, for example, you can use \'3-5 Days\'').'
								"></span>
							</td>
							<td></td>
							<td align="left">
								'.$this->l('Extra Charge').':&nbsp;
							</td>
							<td align="left">
								<select name="dhl_extra_charge" id="dhl_extra_charge" style="width: 140px;'.($this->getPSV() == 1.6 ? 'margin: 0 5px 0 0;float: left;' : '').'" onchange="if ($(\'#dhl_extra_charge\').val() == \'0\') { $(\'#dhl_extra_amount\').fadeOut(1200); $(\'#dhl_extra_sign\').html(\'\');} else if ($(\'#dhl_extra_charge\').val() != \'2\'){$(\'#dhl_extra_amount\').fadeIn(1200);$(\'#dhl_extra_sign\').html(\'%\');}  else {$(\'#dhl_extra_amount\').fadeIn(1200);$(\'#dhl_extra_sign\').html(\''.$currency->sign.'\');}">
									<option value="0">'.$this->l('None').'</option>
									<option value="1">'.$this->l('% of Order Total').'</option>
									<option value="2">'.$this->l('Fixed Amount').'</option>
									<option value="3">'.$this->l('% of Shipping Total').'</option>
								</select>
							
								<span class="info_tooltip" title="
									'.$this->l('Add a shipping cost price markup (optional) to this shipping method. You will be able to edit this later below.').'
								"></span>
							
								&nbsp;&nbsp;
								<span id="dhl_extra_sign" style="'.($this->getPSV() == 1.6 ? 'float: left; margin: 3px 3px 0 0;' : '').'"></span>
								<input type="text" name="dhl_extra_amount" style="padding: 0 5px;'.($this->getPSV() == 1.6 ? 'float: left;width:45px;margin: 0 3px 0 0;' : 'width:30px;').' display: none;" id="dhl_extra_amount" value="" />
							</td>
						</tr>
						<tr height="31">
							<td align="left">
								'.$this->l('Type').':&nbsp;
							</td>
							<td align="left">
								<select name="dhl_method" style="'.($this->getPSV() < 1.6 ? 'width:200px;padding: 1px;' : 'width:190px;').' float: left;margin: 0 10px 0 0;">';
									foreach ($ship_meth as $id => $meth) 
									{
										$this->_html .= '<option value="'.$id.'">'.$meth.'</option>';
									}
								$this->_html .= '</select>
							</td>
							<td></td>
							<td align="left">
								'.$this->l('Insurance').':&nbsp;
							</td>
							<td align="left">
								<select name="dhl_insurance_charge" style="width: 140px;'.($this->getPSV() == 1.6 ? 'margin: 0 5px 0 0;float: left;' : '').'" id="dhl_insurance_charge" onchange="if ($(\'#dhl_insurance_charge\').val() == \'0\') { $(\'.dhl_insurance\').fadeOut(1200); } else if ($(\'#dhl_insurance_charge\').val() == \'1\') { $(\'.dhl_insurance\').fadeOut(100);$(\'.dhl_insurance_whole\').fadeIn(1200); } else { $(\'.dhl_insurance\').fadeIn(1200);}">
									<option value="0">'.$this->l('None').'</option>
									<option value="1">'.$this->l('Wholesale Price').'</option>
									<option value="2">'.$this->l('% of Retail Price').'</option>
								</select>
								
								<span class="info_tooltip" title="
								'.$this->l('Add insurance (optional) to this shipping method. You will be able to edit this later below.').'
								"></span>
								
								&nbsp;&nbsp;
								<span style="display:none" class="dhl_insurance">
									'.($this->getPSV() == 1.6 ? '<span style="float: left; margin: 3px 3px 0 0;">' : '').'
										%
									'.($this->getPSV() == 1.6 ? '</span>' : '').'
									<input type="text" name="dhl_insurance_amount" style="padding: 0 5px;'.($this->getPSV() == 1.6 ? 'float: left;width:45px;margin: 0 3px 0 0;' : 'width:30px;').'" id="dhl_insurance_amount" value="" />
								</span>
							</td>
							<td align="left" colspan="2">
								<span style="display:none; '.($this->getPSV() == 1.6 ? 'width: 40px; float: left;' : '').'" class="dhl_insurance dhl_insurance_whole">'.$this->l('Min').':  '.$currency->sign.'</span>
							</td>
							<td align="left">
								<span style="display:none;" class="dhl_insurance dhl_insurance_whole"><input type="text" name="dhl_minimum_insurance_amount" style="width:30px;" id="dhl_minimum_insurance_amount" value="" /></span>
							</td>
						</tr>
						<tr height="31">
							<td align="left">
								'.$this->l('Zone').':&nbsp;
							</td>
							<td align="left">
								<select id="dhl_zone" name="dhl_zone" style="'.($this->getPSV() < 1.6 ? 'width:200px;padding: 1px;' : 'width:190px;').' float: left;margin: 0 10px 0 0;">';
								foreach($zones AS $zone) 
								{
									$this->_html .= '<option value="' . $zone['id_zone'] . '">' . $zone['name'] . '</option>';
								}
								$this->_html .= '
								</select>
								
								<span class="info_tooltip" title="
									'.$this->l('Not all shipping methods are available for all delivery addresses; if an option is not offered by a carrier for a particular address, it will be automatically disabled.').'
								"></span>
							</td>
						</tr>
						<tr height="31">
							<td align="center" colspan="15">
								<input style="'.($this->getPSV() == 1.6 ? 'margin: 20px 0;' : 'margin-bottom: 20px;').'" onclick="if ($(\'#dhl_carrier_name\').val() == \'\') { alert(\''.$this->l('You must enter a name for the shipping method').'\');return false;};'.($verified != 1?'alert(\''.$this->l('Your account information is incorrect, please make sure you enter the correct information before adding new shipping options').'\');return false;':'').'" type="submit" value="'.$this->l('Add New Shipping Option (Carrier)').'" name="addMethod" class="'.($this->getPSV() == 1.6 ? 'btn btn-default' : 'button').'" />
							</td>
						</tr>
					</table>';
					if (is_array($carriers) && sizeof($carriers) > 0)
					{
						$this->_html .= '
						<table cellspacing="0" cellpadding="0" border="0" class="table widthfull">
							<tr height="31">
								<th>'.$this->l('Name').'</th>
								<th>'.$this->l('Type').'</th>
								<th>'.$this->l('Zone').'</th>
								<th>
									'.$this->l('FS').'
									
									<span class="info_tooltip" title="
										'.$this->l('The shipping method will be free when the cart subtotal is above this amount. Leave as zero if you do not want to offer free shipping for this shipping method.').'
									"></span>
								</th>
								<th>
									'.$this->l('FS Product').'
									
									<span class="info_tooltip" title="
										'.$this->l('You can make this shipping method free based on your catalog, any products that are added will not be included in the shipping calculation. Enter the Product ID (\'P\'), Category ID (\'C\'), Manufacturer ID (\'M\') and/or Supplier ID (\'S\') you wish to apply free shipping to. Separate multiple entires with a comma (no spaces).').'
									"></span>
								</th>
								<th>
									'.$this->l('Extra / Insurance').'
							
									<span class="info_tooltip" title="
										'.$this->l('Use \'Add\' to apply a shipping price markup, and \'Insure\' to add insurance, to orders using this method.').'
									"></span>
								</th>
							</tr> ';
						$irow = 0;
						foreach ($carriers as $carrier)
						{
							$this->_html .= '<tr '.($irow++ % 2 ? 'class="alt_row"' : '').' height="31">
									<td align="left">
										'.$carrier['name'].'
										<input type="hidden" name="id_'.$irow.'" value="'.$carrier['id_carrier'].'" />
									</td>
									<td align="left"><small>'.(isset($ship_meth[$carrier['method']]) ? $ship_meth[$carrier['method']] : $this->l('Invalid')).'</small></td>
									<td align="left">';
							foreach($zones AS $zone)
								if ($zone['id_zone'] == $carrier['id_zone'])
									$this->_html .= $zone['name'];
							$this->_html .= '</td>
									<td align="left" width="'.($this->getPSV() == 1.6 ? '70' : '60').'">
									'.($this->getPSV() == 1.6 ? '<span style="float: left; margin: 3px 3px 0 0;">' : '').
										$currency->sign.
									($this->getPSV() == 1.6 ? '</span>' : '').'
										
										<input type="text" name="dhl_free_shipping_'.$irow.'" style="'.($this->getPSV() == 1.6 ? 'float: left; width:40px; padding: 0 5px;' : 'width:30px;').'" id="dhl_free_shipping_'.$irow.'" value="'.$carrier['free_shipping'].'" />
									</td>
									<td align="left" width="130">
										'.($this->getPSV() == 1.6 ? '<span style="float: left; width: 15px; margin: 3px 0 0 0;">' : '').$this->l('P').($this->getPSV() == 1.6 ? '</span>' : '').'
										<input type="text" name="dhl_free_shipping_product_'.$irow.'" style="'.($this->getPSV() == 1.6 ? 'width:90px; padding: 0 5px; margin: 0 0 5px 0;' : 'width:80px; margin: 5px 0;').'" id="dhl_free_shipping_product_'.$irow.'" value="'.$carrier['free_shipping_product'].'" />
										'.($this->getPSV() != 1.6 ? '<br />' : '').'
										'.($this->getPSV() == 1.6 ? '<span style="float: left; width: 15px; margin: 3px 0 0 0;">' : '').$this->l('C').($this->getPSV() == 1.6 ? '</span>' : '').' 
										<input type="text" name="dhl_free_shipping_category_'.$irow.'" style="'.($this->getPSV() == 1.6 ? 'width:90px; padding: 0 5px; margin: 0 0 5px 0;' : 'width:80px; margin: 5px 0;').'" id="dhl_free_shipping_category_'.$irow.'" value="'.$carrier['free_shipping_category'].'" />
										'.($this->getPSV() != 1.6 ? '<br />' : '').'
										'.($this->getPSV() == 1.6 ? '<span style="float: left; width: 15px; margin: 3px 0 0 0;">' : '').$this->l('M').($this->getPSV() == 1.6 ? '</span>' : '').' 
										<input type="text" name="dhl_free_shipping_manufacturer_'.$irow.'" style="'.($this->getPSV() == 1.6 ? 'width:90px; padding: 0 5px; margin: 0 0 5px 0;' : 'width:80px; margin: 5px 0;').'" id="dhl_free_shipping_manufacturer_'.$irow.'" value="'.$carrier['free_shipping_manufacturer'].'" />
										'.($this->getPSV() != 1.6 ? '<br />' : '').'
										'.($this->getPSV() == 1.6 ? '<span style="float: left; width: 15px; margin: 3px 0 0 0;">' : '').$this->l('S').($this->getPSV() == 1.6 ? '</span>' : '').'  
										<input type="text" name="dhl_free_shipping_supplier_'.$irow.'" style="'.($this->getPSV() == 1.6 ? 'width:90px; padding: 0 5px; margin: 0 0 5px 0;' : 'width:80px; margin: 5px 0;').'" id="dhl_free_shipping_supplier_'.$irow.'" value="'.$carrier['free_shipping_supplier'].'" />
									</td>
									<td align="left">
										<table width="100%">
										<tr height="31">
											<td align="left">
												'.$this->l('Add').':
											</td>
											<td align="left" colspan="4">
												<select name="dhl_extra_charge_'.$irow.'" id="dhl_extra_charge_'.$irow.'" style="width: 130px;'.($this->getPSV() == 1.6 ? 'float: left;margin: 0 3px 0 0;' : '').'" onchange="if ($(\'#dhl_extra_charge_'.$irow.'\').val() == \'0\') { $(\'#dhl_extra_charge_container_'.$irow.'\').fadeOut(1200); $(\'#dhl_extra_sign_'.$irow.'\').html(\'\');} else if ($(\'#dhl_extra_charge_'.$irow.'\').val() != \'2\'){$(\'#dhl_extra_charge_container_'.$irow.'\').fadeIn(1200);$(\'#dhl_extra_sign_'.$irow.'\').html(\'%\');}  else {$(\'#dhl_extra_charge_container_'.$irow.'\').fadeIn(1200);$(\'#dhl_extra_sign_'.$irow.'\').html(\''.$currency->sign.'\');}">
													<option value="0" '.($carrier['extra_shipping_type'] == 0?'selected':'').'>'.$this->l('None').'</option>
													<option value="1" '.($carrier['extra_shipping_type'] == 1?'selected':'').'>'.$this->l('% of Order Total').'</option>
													<option value="2" '.($carrier['extra_shipping_type'] == 2?'selected':'').'>'.$this->l('Fixed Amount').'</option>
													<option value="3" '.($carrier['extra_shipping_type'] == 3?'selected':'').'>'.$this->l('% of Shipping Total').'</option>
												</select>
												
												<span id="dhl_extra_charge_container_'.$irow.'" style="display: '.($carrier['extra_shipping_type'] == 0?'none':'').'">&nbsp;
													<span id="dhl_extra_sign_'.$irow.'" style="'.($this->getPSV() == 1.6 ? 'float: left; margin: 3px 3px 0 0;' : '').'">
														'.($carrier['extra_shipping_type'] != 2 ? '%' : $currency->sign).'
													</span>
													<input type="text" name="dhl_extra_amount_'.$irow.'" style="padding: 0 5px;'.($this->getPSV() == 1.6 ? 'float: left;width:45px;' : 'width:30px;').'" id="dhl_extra_amount_'.$irow.'" value="'.$carrier['extra_shipping_amount'].'" />
												</span>
											</td>
										</tr>
										<tr height="31">
											<td align="left">
												'.$this->l('Insure').':
											</td>
											<td align="left">
												<select name="dhl_insurance_charge_'.$irow.'" style="width: 130px;'.($this->getPSV() == 1.6 ? 'float: left;' : '').'" id="dhl_insurance_charge_'.$irow.'" onchange="if ($(\'#dhl_insurance_charge_'.$irow.'\').val() == \'0\') { $(\'.dhl_insurance_'.$irow.'\').fadeOut(1200); } else if ($(\'#dhl_insurance_charge_'.$irow.'\').val() == \'1\') { $(\'.dhl_insurance_'.$irow.'\').fadeOut(100);$(\'.dhl_insurance_whole_'.$irow.'\').fadeIn(1200); } else { $(\'.dhl_insurance_'.$irow.'\').fadeIn(1200);}">
													<option value="0" '.($carrier['insurance_type'] == 0?'selected':'').'>'.$this->l('None').'</option>
													<option value="1" '.($carrier['insurance_type'] == 1?'selected':'').'>'.$this->l('Wholesale Price').'</option>
													<option value="2" '.($carrier['insurance_type'] == 2?'selected':'').'>'.$this->l('% of Retail Price').'</option>
												</select>
											</td>
											<td align="left">
												<input type="text" class="dhl_insurance_'.$irow.'" name="dhl_insurance_amount_'.$irow.'" style="padding: 0 5px;'.($this->getPSV() == 1.6 ? 'float: left;width:45px;' : 'width:30px;').' display:'.($carrier['insurance_type'] != 2?'none':'block').';" id="dhl_insurance_amount_'.$irow.'" value="'.$carrier['insurance_amount'].'" />
											</td>
											<td align="left">
												<span class="dhl_insurance_'.$irow.' dhl_insurance_whole_'.$irow.'" style="'.($carrier['insurance_type'] == 0?'display:none':'').'">'.$this->l('Min').': '.$currency->sign.'</span>
											</td>
											<td align="left">
												<input type="text" class="dhl_insurance_'.$irow.' dhl_insurance_whole_'.$irow.'" name="dhl_minimum_insurance_amount_'.$irow.'"  style="padding: 0 5px;'.($this->getPSV() == 1.6 ? 'float: left;width:45px;' : 'width:30px;').' display:'.($carrier['insurance_type'] == 0?'none':'block').';" id="dhl_minimum_insurance_amount_'.$irow.'" value="'.$carrier['insurance_minimum'].'" />
											</td>
										</tr>
										</table>
									</td>
								</tr>';
						}
						$this->_html .= '
								<tr height="31">
									<td align="center" colspan="15">
										<input type="submit" value="'.$this->l('Update').'" name="updateShipping" class="'.($this->getPSV() == 1.6 ? 'btn btn-default' : 'button').'" style="margin: 10px 0;" />
									</td>
								</tr>
							</table>';
					}
				$this->_html .= '
				'.($ps_version < 1.6 ? '</fieldset>': '</div>').' 
			'.($ps_version < 1.6 ? '</fieldset>': '</div>').'
		';
		

		/** LABEL PRINTING */
		$countries = Country::getCountries($this->context->language->id, true);
		$states = array();
		$country = Country::getByIso($this->_dhl_shipper_country);
		$states = isset($countries[$country]['states'])?$countries[$country]['states']:array();
		$order_statuses = OrderState::getOrderStates($this->context->language->id);

		if(!$this->_dhl_shipper_shop_name || 
		   !$this->_dhl_shipper_attention_name || 
		   !$this->_dhl_shipper_phone || 
		   !$this->_dhl_shipper_addr1 || 
		   !$this->_dhl_shipper_city || 
		   !$this->_dhl_shipper_country || 
		   (sizeof($states) > 0 && !$this->_dhl_shipper_state) || 
		   !$this->_dhl_shipper_postcode
		){ 
			$label_validated = false; 
		}
		else
			$label_validated = true;
				
		$this->_html .= '
		<br/>
			
		'.($ps_version >= 1.6?'<div class="panel">':
		'<fieldset class="width3" style="width:900px;">').
			($ps_version < 1.6 ? '<legend>': '<h3>').'
				<img src="'.$this->_path.'logo.gif" style="'.($this->getPSV() == 1.6 ? 'margin: 0 10px 0 0;' : '').'" /> '.$this->l('Label Printing').'
			'.($ps_version < 1.6 ? '</legend>': '</h3>').'
			
			'.($ps_version >= 1.6?'<div class="panel">':
			'<fieldset class="inner_fieldset" style="width:900px;">').
				($ps_version < 1.6 ? '<legend class="'.($label_validated ? 'validated' : 'not_validated').'">': '<h3 class="'.($label_validated ? 'validated' : 'not_validated').'">').'
					<img src="'.$this->_path.'logo.gif" style="'.($this->getPSV() == 1.6 ? 'margin: 0 10px 0 0;' : '').'" /> '.$this->l('Step 6: Label Printing Default Settings').'
				'.($ps_version < 1.6 ? '</legend>': '</h3>').'
				
					<div style="margin: 5px 0; float:left; width: 100%;">
						<label for="dhl_enable_labels_dhl" style="width:110px;font-weight:normal;text-align:left; float:left;">'.$this->l('Enable Labels').': <sup>*</sup></label>
						<label style="float: none; width: 100%"><input type="radio" name="dhl_enable_labels" id="dhl_enable_labels_dhl" value="dhl" '.($this->_dhl_enable_labels == 'dhl' ? 'checked="checked"' : '').' /> '.$this->l('Only for orders placed using DHL').'</label>
						<br />
						<label style="float: none; width: 100%"><input type="radio" name="dhl_enable_labels" id="dhl_enable_labels_all" value="all" '.($this->_dhl_enable_labels == 'all' ? 'checked="checked"' : '').' /> '.$this->l('For all orders').'</label>
					</div>    
					
					<div style="margin: 5px 0; float:left; width: 100%;">
						<label for="label_order_status" style="width:110px;font-weight:normal;text-align:left; float:left;">'.$this->l('Order Status:').'</label>
						<select name="label_order_status" style="'.($this->getPSV() < 1.6 ? 'width:200px;padding: 1px;' : 'width:190px;').' float: left;margin: 0 10px 0 0;">
							<option value="0">'.$this->l('Do not change order status').'</option>
		'; 
				if(is_array($order_statuses) && count($order_statuses))
				{
						foreach ($order_statuses as $status)
						{
							$this->_html .= '<option value="'.$status['id_order_state'].'" '.($this->_dhl_label_order_status == $status['id_order_state']?"selected":"").'>'.$status['name'].'</option>';
						}
				}
					
			$this->_html .= '
						</select> ('.$this->l('After printing a label, order status will be changed to your selection').')
					</div>

					<hr style="width: 100%;height: 1px;background-color: #CCCED7;margin: 5px 0; float: left;" />
					
					<p><b>'.$this->l('Default shipper information').'</b> '.$this->l('(Can be changed on each order)').'</p>
					
					<div style="margin: 5px 0; float:left; width: 100%;">
						<label for="shipper_shop_name" style="width:110px;font-weight:normal;text-align:left; float:left;">'.$this->l('Shop Name').': <sup>*</sup></label>
						<input type="text" name="shipper_shop_name" id="shipper_shop_name" value="'.$this->_dhl_shipper_shop_name.'" style="width:190px;float: left;margin: 0 10px 0 0;">
					</div>

					<div style="margin: 5px 0; float:left; width: 100%;">
						<label for="shipper_attention_name" style="width:110px;font-weight:normal;text-align:left; float:left;">'.$this->l('Your Name').': <sup>*</sup></label>
						<input type="text" name="shipper_attention_name" id="shipper_attention_name" value="'.$this->_dhl_shipper_attention_name.'" style="width:190px;float: left;margin: 0 10px 0 0;">
					</div>

					<div style="margin: 5px 0; float:left; width: 100%;">
						<label for="shipper_phone" style="width:110px;font-weight:normal;text-align:left; float:left;">'.$this->l('Phone Number').': <sup>*</sup></label>
						<input type="text" name="shipper_phone" id="shipper_phone" value="'.$this->_dhl_shipper_phone.'" style="width:190px;float: left;margin: 0 10px 0 0;">
					</div>

					<div style="margin: 5px 0; float:left; width: 100%;">
						<label for="shipper_address1" style="width:110px;font-weight:normal;text-align:left; float:left;">'.$this->l('Address Line 1').': <sup>*</sup></label>
						<input type="text" name="shipper_address1" id="shipper_address1" value="'.$this->_dhl_shipper_addr1.'" style="width:190px;float: left;margin: 0 10px 0 0;">
					</div>

					<div style="margin: 5px 0; float:left; width: 100%;">
						<label for="shipper_address2" style="width:110px;font-weight:normal;text-align:left; float:left;">'.$this->l('Address Line 2').':</label>
						<input type="text" name="shipper_address2" id="shipper_address2" value="'.$this->_dhl_shipper_addr2.'" style="width:190px;float: left;margin: 0 10px 0 0;">
					</div>

					<div style="margin: 5px 0; float:left; width: 100%;">
						<label for="shipper_city" style="width:110px;font-weight:normal;text-align:left; float:left;">'.$this->l('Shop City').': <sup>*</sup></label>
						<input type="text" name="shipper_city" id="shipper_city" value="'.$this->_dhl_shipper_city.'" style="width:190px;float: left;margin: 0 10px 0 0;">
					</div>

					<div style="margin: 5px 0; float:left; width: 100%;">
						<label style="width:110px;font-weight:normal;text-align:left; float:left;">'.$this->l('Shop Country').': <sup>*</sup></label>
						<select name="shipper_country" style="'.($this->getPSV() < 1.6 ? 'width:200px;padding: 1px;' : 'width:190px;').' float: left;margin: 0 10px 0 0;">
							<option value="0">-- '.$this->l('Select Country').' --</option>
			';
						if (is_array($countries) && count($countries))
						{
							foreach ($countries as $country)
							{
								$this->_html .= '<option value="'.$country['iso_code'].'" '.($this->_dhl_shipper_country == $country['iso_code'] ? 'selected="selected"' : "").'>'.$country['name'].'</option>';
							}
						}
						
			$this->_html .= '
						</select>
					</div>

					<div style="margin: 5px 0; float:left; width: 100%;">
						<label style="width:110px;font-weight:normal;text-align:left; float:left;">'.$this->l('Shop State').': '.(is_array($states) && count($states) ? '<sup>*</sup>' : '').'</label>
						<select name="shipper_state" style="'.($this->getPSV() < 1.6 ? 'width:200px;padding: 1px;' : 'width:190px;').' float: left;margin: 0 10px 0 0;">
							<option value="0">-- '.$this->l('Select State').' --</option>
			';
						if (is_array($states) && count($states))
						{
							foreach ($states as $state)
							{
								$this->_html .= '<option value="'.$state['iso_code'].'" '.($this->_dhl_shipper_state == $state['iso_code']? 'selected="selected"' : "").'>'.$state['name'].'</option>';
							}
						}
						
			$this->_html .= '
						</select>
					</div>

					<div style="margin: 5px 0; float:left; width: 100%;">
						<label for="shipper_postcode" style="width:110px;font-weight:normal;text-align:left; float:left;">'.$this->l('Shop Zipcode').': <sup>*</sup></label>
						<input type="text" name="shipper_postcode" id="shipper_postcode" value="'.$this->_dhl_shipper_postcode.'" style="width:190px;float: left;margin: 0 10px 0 0;">
					</div>

					<div style="margin: 10px 0;text-align: center;width: 100%;">
						<input type="submit" value="'.$this->l('Update').'" name="updateShipperInfo" class="'.($this->getPSV() == 1.6 ? 'btn btn-default' : 'button').'" />
					</div>
					
				'.($ps_version < 1.6 ? '</fieldset>': '</div>').'
				
			'.($ps_version < 1.6 ? '</fieldset>': '</div>').'
			
		</form>'.($ps_version >= 1.5?'</div>':'');
	}




	protected function _postProcess()
	{
		$languages = Language::getLanguages();
		$result = Db::getInstance()->ExecuteS('SHOW TABLES');
		$existing_tables = array();
		foreach ($result AS $row)
			foreach ($row AS $key => $table)
				array_push($existing_tables, $table);

		//hide or display installation instructions
		if (Tools::getValue('dhl_shi') != "")
		{
			if (Tools::getValue('dhl_shi') == "inline")
				Configuration::updateValue('DHL_INSTALL',"none");
			else
				Configuration::updateValue('DHL_INSTALL',"inline");
		}

		if (Tools::isSubmit('updateSettings'))
		{
			if (Tools::getValue('dhl_pack') != $this->_dhl_pack)
			{
				$query = 'TRUNCATE `'._DB_PREFIX_.'fe_dhl_invalid_dest`';
				Db::getInstance()->Execute($query);
			}

			if (!Configuration::updateValue('DHL_SITE_ID', str_replace(' ', '', Tools::getValue('dhl_site_id')))
				|| !Configuration::updateValue('DHL_PASS', str_replace(' ', '', Tools::getValue('dhl_pass')))
				|| !Configuration::updateValue('DHL_ACCOUNT_NUMBER', str_replace(' ', '', Tools::getValue('dhl_account_number')))
				|| !Configuration::updateValue('DHL_DROPOFF', Tools::getValue('dhl_dropoff'))
				|| !Configuration::updateValue('DHL_UNIT', Tools::getValue('dhl_unit'))
				|| !Configuration::updateValue('DHL_PACK', Tools::getValue('dhl_pack'))
				|| !Configuration::updateValue('DHL_WIDTH', Tools::getValue('dhl_width'))
				|| !Configuration::updateValue('DHL_HEIGHT', Tools::getValue('dhl_height'))
				|| !Configuration::updateValue('DHL_DEPTH', Tools::getValue('dhl_depth'))
				|| !Configuration::updateValue('DHL_WEIGHT', Tools::getValue('dhl_weight'))
				|| !Configuration::updateValue('DHL_ORIGIN_ZIP', Tools::getValue('dhl_origin_zip'))
				|| !Configuration::updateValue('DHL_ORIGIN_COUNTRY', Tools::getValue('dhl_origin_country'))
				|| !Configuration::updateValue('DHL_ORIGIN_CITY', Tools::getValue('dhl_origin_city'))
				|| !Configuration::updateValue('DHL_TYPE', Tools::getValue('dhl_type'))
				|| !Configuration::updateValue('DHL_PACKAGES', Tools::getValue('dhl_packages'))
				|| !Configuration::updateValue('DHL_PACKAGE_SIZE', Tools::getValue('dhl_package_size'))
				|| !Configuration::updateValue('DHL_PACKAGES_PER_BOX', Tools::getValue('dhl_packages_per_box'))
				|| !Configuration::updateValue('DHL_OVERRIDE_ADDRESS', Tools::getValue('dhl_override_address'))
				|| !Configuration::updateValue('DHL_DEBUG_MODE', Tools::getValue('dhl_debug_mode'))
				|| !Configuration::updateValue('DHL_ADDRESS_DISPLAY', serialize(array('country' => Tools::getValue('dhl_country_display'), 'state' => Tools::getValue('dhl_state_display'), 'city' => Tools::getValue('dhl_city_display'), 'zip' => Tools::getValue('dhl_zip_display'))))
				|| !Configuration::updateValue('DHL_XML_LOG', Tools::getValue('dhl_xml_log'))
				|| !Configuration::updateValue('DHL_CURRENCY_USED', Tools::getValue('dhl_currency_used'))
				|| !Configuration::updateValue('DHL_DISCOUNT_RATE', Tools::getValue('dhl_discount_rate'))
				|| !Configuration::updateValue('DHL_MODE', Tools::getValue('dhl_mode'))
			){
				if($this->getPSV() == 1.6)
					$this->_html .= $this->displayError($this->l('Cannot update settings'));
				else
					$this->_html .= '<div class="alert error">'.$this->l('Cannot update settings').'</div>';
			}
			else
			{
				if($this->getPSV() == 1.6)
					$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
				else
					$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
			}
		}

		if (Tools::getValue('ssr') == 'show') //show shipping rates button
		{
			$this->registerHook($this->getPSV() >= 1.5 ? 'displayProductButtons' : 'productActions');
			$this->registerHook('cartShippingPreview');
			Configuration::updateValue('DHL_SHIPPING_RATES_BUTTON', 'show');
		}
		elseif(Tools::getValue('ssr') == 'hide') //hide shipping rates button
		{
			$this->unregisterHook(($this->getPSV() >= 1.5 ? Hook::getIdByName('displayProductButtons') : Hook::get('productActions')));
			$this->unregisterHook(($this->getPSV() >= 1.5 ? Hook::getIdByName('cartShippingPreview') : Hook::get('cartShippingPreview')));
			Configuration::updateValue('DHL_SHIPPING_RATES_BUTTON', 'hide');
		}


		if (Tools::isSubmit('addMethod'))
		{
			$query = 'INSERT INTO `'._DB_PREFIX_.'carrier` (name, url, active, is_module, shipping_external, need_range, shipping_method, external_module_name) VALUES("'.(Tools::getValue('dhl_carrier_name') != ""?Tools::getValue('dhl_carrier_name'):" ").'", "http://www.dhl.com/content/g0/en/express/tracking.shtml?brand=DHL&AWB=@",  "1","1","1","1","1","dhl")';
			Db::getInstance()->Execute($query);
			$id_carrier = Db::getInstance()->Insert_ID();
			if ($this->getPSV() >= 1.5)
				Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'carrier set id_reference = '.$id_carrier.' WHERE id_carrier = '.$id_carrier);
			if (in_array(_DB_PREFIX_."carrier_group",$existing_tables))
			{
				$id_groups = Db::getInstance()->executeS('SELECT id_group FROM '._DB_PREFIX_.'group GROUP BY id_group');
				foreach ($id_groups as $group)
				{
					$query  = 'INSERT INTO `'._DB_PREFIX_.'carrier_group` (id_carrier, id_group) VALUES("'.$id_carrier.'", "'.$group['id_group'].'")';
					Db::getInstance()->Execute($query);
				}
			}

            if($this->getPSV() >= 1.5)
            {
                /** $this->context->shop is not accurate */
                $id_shop_group = Shop::getContextShopGroupID(true);
                $id_shop = Shop::getContextShopID(true);
                
                /** if shop group is selected */
                if(!$id_shop && $id_shop_group)
                    $shops = Shop::getShops(false, $id_shop_group);
                /** if all shops is selected */
                elseif(!$id_shop && !$id_shop_group)
                    $shops = Shop::getShops(false);
                /** if a shop is selected */
                else
                {
                    $shops = array(
                        0 => array(
                            'id_shop' => $id_shop
                        )
                    );
                }

                if(is_array($shops) && count($shops))
                {
                    foreach($shops as $shop)
                    {
                        if (in_array(_DB_PREFIX_."carrier_tax_rules_group_shop",$existing_tables))
                        {
                            $query  = 'INSERT INTO `'._DB_PREFIX_.'carrier_tax_rules_group_shop` (id_carrier, id_tax_rules_group, id_shop) VALUES("'.$id_carrier.'", "0","'.$shop['id_shop'].'")';
                            Db::getInstance()->Execute($query);
                        }
                        
                        if (in_array(_DB_PREFIX_."carrier_shop",$existing_tables))
                        {
                            $query  = 'INSERT INTO `'._DB_PREFIX_.'carrier_shop` (id_carrier, id_shop) VALUES("'.$id_carrier.'", "'.$shop['id_shop'].'")';
                            Db::getInstance()->Execute($query);
                        }
                        
                        /** insert carrier language for the store */
                        foreach ($languages as $language)
                        {
                            $query  = 'INSERT INTO `'._DB_PREFIX_.'carrier_lang` (id_carrier, id_shop, id_lang, delay) VALUES("'.(int)$id_carrier.'", "'.$shop['id_shop'].'", "'.(int)$language['id_lang'].'", "'.(Tools::getValue('dhl_transit_time') != "" ? Tools::getValue('dhl_transit_time') : " ").'")';
                            Db::getInstance()->Execute($query);
                        }
                    }
                }
                /** FATAL ERROR? */
                else
                    die('fatal error: could not create carrier, reload the page and try again');
            }
            else
            {
                /** insert carrier language for the store */
                foreach ($languages as $language)
                {
                    $query  = 'INSERT INTO `'._DB_PREFIX_.'carrier_lang` (id_carrier, id_lang, delay) VALUES("'.(int)$id_carrier.'", "'.(int)$language['id_lang'].'", "'.(Tools::getValue('dhl_transit_time') != "" ? Tools::getValue('dhl_transit_time') : " ").'")';
                    Db::getInstance()->Execute($query);
                }
            }

			$query  = 'INSERT INTO `'._DB_PREFIX_.'fe_dhl_method` (id_carrier, method, free_shipping, extra_shipping_type, extra_shipping_amount, insurance_minimum, insurance_type, insurance_amount, free_shipping_product, free_shipping_category, free_shipping_manufacturer, free_shipping_supplier) VALUES
				("'.$id_carrier.'","'.Tools::getValue('dhl_method').'","'.intval(Tools::getValue('dhl_free_shipping')).'",
				"'.intval(Tools::getValue('dhl_extra_charge')).'","'.intval(Tools::getValue('dhl_extra_amount')).'",
				"'.intval(Tools::getValue('dhl_minimum_insurance_amount')).'","'.intval(Tools::getValue('dhl_insurance_charge')).'",
				"'.intval(Tools::getValue('dhl_insurance_amount')).'","","","","")';
			Db::getInstance()->Execute($query);
			$query = 'INSERT INTO `'._DB_PREFIX_.'carrier_zone` (id_carrier, id_zone) VALUES("'.$id_carrier.'", "'.Tools::getValue('dhl_zone').'")';
			Db::getInstance()->Execute($query);

			$query = 'INSERT INTO `'._DB_PREFIX_.'range_weight` (id_carrier, delimiter1, delimiter2) VALUES("'.$id_carrier.'", "0.00", "100000.00")';
			Db::getInstance()->Execute($query);
			$id_range_weight = Db::getInstance()->Insert_ID();

			$query = 'INSERT INTO `'._DB_PREFIX_.'delivery` (id_carrier, id_range_weight, id_zone, price) VALUES("'.$id_carrier.'", "'.$id_range_weight.'", "'.Tools::getValue('dhl_zone').'","0")';
			Db::getInstance()->Execute($query);
			
			if($this->getPSV() == 1.6)
				$this->_html .= $this->displayConfirmation($this->l('Carrier created successfully'));
			else
				$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Carrier created successfully').'</div>';

		}

		if (Tools::isSubmit('updateShipping') || Tools::isSubmit('deleteCache') || Tools::isSubmit('updateSettings'))
		{
			$query = 'TRUNCATE `'._DB_PREFIX_.'fe_dhl_rate_cache`';
			Db::getInstance()->Execute($query);
			$query = 'TRUNCATE `'._DB_PREFIX_.'fe_dhl_hash_cache`';
			Db::getInstance()->Execute($query);
			$query = 'TRUNCATE `'._DB_PREFIX_.'fe_dhl_package_rate_cache`';
			Db::getInstance()->Execute($query);
			$query = 'TRUNCATE `'._DB_PREFIX_.'fe_dhl_invalid_dest`';
			Db::getInstance()->Execute($query);
			Configuration::updateValue('DHL_DOWN_TIME','');
			//delete logs:
			$files = glob(dirname(__FILE__).'/logs/*'); // get all file names
			foreach($files as $file)
			{ // iterate files
				if(is_file($file) AND !strstr($file, 'index.php'))
					unlink($file); // delete file
			}
			
			if($this->getPSV() == 1.6 && Tools::isSubmit('deleteCache'))
				$this->_html .= $this->displayConfirmation($this->l('Shipping cache deleted successfully'));
			elseif(Tools::isSubmit('deleteCache'))
				$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Shipping cache deleted successfully').'</div>';

		}

		if (Tools::isSubmit('updateShipping'))
		{
			$i = 1;
			while (isset($_POST['id_'.$i]) && $_POST['id_'.$i])
			{
				$query = 'UPDATE `'._DB_PREFIX_.'fe_dhl_method` SET free_shipping = "'.$_POST['dhl_free_shipping_'.$i].'",free_shipping_product = "'.$_POST['dhl_free_shipping_product_'.$i].'",
						free_shipping_category = "'.$_POST['dhl_free_shipping_category_'.$i].'",free_shipping_manufacturer = "'.$_POST['dhl_free_shipping_manufacturer_'.$i].'",
						free_shipping_supplier = "'.$_POST['dhl_free_shipping_supplier_'.$i].'",extra_shipping_type = "'.$_POST['dhl_extra_charge_'.$i].'", extra_shipping_amount = "'.$_POST['dhl_extra_amount_'.$i].'",
						insurance_minimum = "'.$_POST['dhl_minimum_insurance_amount_'.$i].'", insurance_type = "'.$_POST['dhl_insurance_charge_'.$i].'",
						insurance_amount = "'.$_POST['dhl_insurance_amount_'.$i].'" WHERE id_carrier = "'.$_POST['id_'.$i].'"';
				Db::getInstance()->Execute($query);
				$i++;
			}
			
			if($this->getPSV() == 1.6)
				$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
			else
				$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';

		}
		
		if(Tools::isSubmit('updateShipperInfo'))
		{
			Configuration::updateValue('DHL_SHIPPER_SHOP_NAME', Tools::getValue('shipper_shop_name'));
			Configuration::updateValue('DHL_SHIPPER_ATTENTION_NAME', Tools::getValue('shipper_attention_name'));
			Configuration::updateValue('DHL_SHIPPER_PHONE', Tools::getValue('shipper_phone'));
			Configuration::updateValue('DHL_SHIPPER_ADDR1', Tools::getValue('shipper_address1'));
			Configuration::updateValue('DHL_SHIPPER_ADDR2', Tools::getValue('shipper_address2'));
			Configuration::updateValue('DHL_SHIPPER_CITY', Tools::getValue('shipper_city'));
			Configuration::updateValue('DHL_SHIPPER_COUNTRY', Tools::getValue('shipper_country'));
			Configuration::updateValue('DHL_SHIPPER_STATE', Tools::getValue('shipper_state'));
			Configuration::updateValue('DHL_SHIPPER_POSTCODE', Tools::getValue('shipper_postcode'));
			Configuration::updateValue('DHL_ENABLE_LABELS', Tools::getValue('dhl_enable_labels'));
			Configuration::updateValue('DHL_LABEL_ORDER_STATUS', Tools::getValue('label_order_status'));
			
			
			if($this->getPSV() == 1.6)
				$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
			else
				$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
		}

		$this->_refreshProperties();
	}

	protected function checkDHLSettings()
	{
		$log = false;
		$site_id = $this->_dhl_site_id != "" ? $this->_dhl_site_id : "0";
		$password = $this->_dhl_pass != "" ? $this->_dhl_pass : "0";
		$message_time = date('c');
		$message_reference = $this->generateMessageReference(30);
		$date = date('Y-m-d');
		$post_url = $this->getServerURL();

		$error_code = null;
		do
		{
			$post_string ='
			<?xml version="1.0" encoding="UTF-8"?>
			<p:DCTRequest xmlns:p="http://www.dhl.com" xmlns:p1="http://www.dhl.com/datatypes" xmlns:p2="http://www.dhl.com/DCTRequestdatatypes" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com DCT-req.xsd ">
				<GetCapability>
					<Request>
						<ServiceHeader>
							<MessageTime>'.$message_time.'</MessageTime>
							<MessageReference>'.$message_reference.'</MessageReference>
							<SiteID>'.$site_id.'</SiteID>
							<Password>'.$password.'</Password>
						</ServiceHeader>
					</Request>
					<From>
						<CountryCode>US</CountryCode>
						<Postalcode>20500</Postalcode>
					</From>
					<BkgDetails>
						<PaymentCountryCode>US</PaymentCountryCode>
						<Date>'.$date.'</Date>
						<ReadyTime>PT10H21M</ReadyTime>
						<ReadyTimeGMTOffset>+01:00</ReadyTimeGMTOffset>
						<DimensionUnit>CM</DimensionUnit>
						<WeightUnit>KG</WeightUnit>
						<Pieces>
							<Piece>
								<PieceID>1</PieceID>
								<Height>30</Height>
								<Depth>20</Depth>
								<Width>10</Width>
								<Weight>10.0</Weight>
							</Piece>
						</Pieces>
						<IsDutiable>N</IsDutiable>
						<NetworkTypeCode>AL</NetworkTypeCode>
					</BkgDetails>
					<To>
						<CountryCode>US</CountryCode>
						<Postalcode>10001</Postalcode>
					</To>
				</GetCapability>
			</p:DCTRequest>
			';
		
			$post_response = $this->curl_post($post_url, $post_string);
			$xml = @simplexml_load_string($post_response);

			$error_code = @$xml->GetCapabilityResponse->Note->Condition->ConditionCode;
			$date = date('Y-m-d', strtotime('+1 day', strtotime($date)));

		} while($error_code == '1003'); //do while error will be non "Pick-up service is not provided on this day."

        if(@file_exists(dirname(__FILE__).'/logs/'.$this->name.'_error_report.html'))
            @unlink(dirname(__FILE__).'/logs/'.$this->name.'_error_report.html');
        
		/** CURL_ERROR */
		if($xml === false)
		{
			$error_message = '['.date('m/d/Y H:i:s').'] '.$this->l('An error occured while trying to validate your account').'<br><br>';
			$error_message .= '// ------------------------ '.$this->l('Error message').':'.'<br>';
			$error_message .= $post_response."\n\r";
			
			$this->saveLog($this->name.'_error_report.html', $error_message, true);
			
			if ($this->getPSV() == 1.6)
				$this->_html .= $this->displayError($this->l('An error occured while trying to validate your account').' (<a href="'.__PS_BASE_URI__.'modules/'.$this->name.'/logs/'.$this->name.'_error_report.html" target="_blank">'.$this->l('click here to see full report').'</a>)');
			else
				$this->_html .= '<div class="alert error">'.$this->l('An error occured while trying to validate your account').' (<a href="'.__PS_BASE_URI__.'modules/'.$this->name.'/logs/'.$this->name.'_error_report.html" target="_blank">'.$this->l('click here to see full report').'</a>)'.'</div>';
			
			return 0;
		}
		/** CARRIER RETURNS ERROR */
		elseif (isset($xml->Response->Status->ActionStatus) && $xml->Response->Status->ActionStatus == 'Error')
		{
			$error_message = '<pre>['.date('m/d/Y H:i:s').'] '.$this->l('An error occured while trying to validate your account').'<br><br>';
			$error_message .= '// ------------------------ '.$this->l('Following is our module\'s request').':'.'<br>';
			$error_message .= $this->l('Requested to').': '.$post_url.'<br>';
			$error_message .= print_r(htmlspecialchars($post_string), true).'<br><br>';
			
			$error_message .= '// ------------------------ '.$this->l('Following is Carrier\'s message(s)').':'.'<br>';
			$error_message .= '['.$this->l('Error').' #'.$xml->Response->Status->Condition->ConditionCode.'] '.$xml->Response->Status->Condition->ConditionData.'<br><br>';
			
			$error_message .= '// ------------------------ '.$this->l('Following is Carrier\'s full response').':'.'<br>';
			$error_message .= print_r($xml, true).'<br><br></pre>';
			
			$this->saveLog($this->name.'_error_report.html', $error_message, true);
			
			if ($this->getPSV() == 1.6)
				$this->_html .= $this->displayError($this->l('An error occured while trying to validate your account').' (<a href="'.__PS_BASE_URI__.'modules/'.$this->name.'/logs/'.$this->name.'_error_report.html" target="_blank">'.$this->l('click here to see full report').'</a>)');
			else
				$this->_html .= '<div class="alert error">'.$this->l('An error occured while trying to validate your account').' (<a href="'.__PS_BASE_URI__.'modules/'.$this->name.'/logs/'.$this->name.'_error_report.html" target="_blank">'.$this->l('click here to see full report').'</a>)'.'</div>';
			
			return 0;
		}
		else
			return 1;
	}

	public function getServerURL()
	{
			return $this->_dhl_mode == 'live' ? 'https://xmlpi-ea.dhl.com/XMLShippingServlet' : 'https://xmlpitest-ea.dhl.com/XMLShippingServlet';
	}

	public function generateMessageReference($length = 30){
		$chars = 'abcdeffhijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		$numChars = strlen($chars);
		$string = '';
		for ($i = 0; $i < $length; $i++) {
			$string .= substr($chars, rand(1, $numChars) - 1, 1);
		}
		return $string;
	}

	public function getHash($id_carrier, $products, $id_product, $id_product_attribute, $qty, $dest_country, $dest_state, $dest_zip, $get_rate = false)
	{
		$log = false;
		
		/** ERROR: http://screencast.com/t/MIb6AoDUI
		$this->saveLog('1h_log.txt', "1) $id_carrier, $products, $id_product, $qty, $get_rate\n\r", $log);
		*/ $this->saveLog('1h_log.txt', "1) $id_carrier, $id_product, $qty, $get_rate\n\r", $log);

		$fs_arr = Db::getInstance()->getRow('SELECT free_shipping_product, free_shipping_category, free_shipping_manufacturer, free_shipping_supplier FROM '._DB_PREFIX_.'fe_dhl_method fdm, '._DB_PREFIX_.'carrier c WHERE fdm.id_carrier = "'.$id_carrier.'" AND fdm.id_carrier = c.id_carrier AND c.active = 1 AND c.deleted = 0');

		if (!is_array($fs_arr) || sizeof($fs_arr) == 0)
			return 0;

		$origin_country = $this->_dhl_origin_country;
		$origin_zip = $this->_dhl_origin_zip;

		$hash = "$id_carrier, $origin_country, $origin_zip, $dest_country, $dest_zip, ";
		$is_fs = false;
		if ($id_product > 0)
		{
			if ($this->is_free_ship_product($id_product, $fs_arr))
				$is_fs = true;
			$hash .= " $id_product, $id_product_attribute, ".(int)$qty;
		}
		else
		{
			$is_fs = true;
			foreach ($products as $product)
			{
				if (!$this->is_free_ship_product($product['id_product'], $fs_arr))
					$is_fs = false;
				$hash .= " ".$product['id_product'].", ".$product['id_product_attribute'].", ".$product['quantity'];
			}
		}

		$md5 = md5($hash);
		$this->saveLog('1h_log.txt', "2) hash ($md5) $hash\n\r", $log);

		if ($get_rate)
		{
			if ($is_fs)
				return 0;

			$query = 'SELECT drc.rate,drc.currency,drc.packages, dm.* FROM `'._DB_PREFIX_.'fe_dhl_hash_cache` dhc, `'._DB_PREFIX_.'fe_dhl_rate_cache` drc, `'._DB_PREFIX_.'fe_dhl_method` dm WHERE dhc.hash = "'.$md5.'" AND dhc.id_dhl_rate = drc.id_dhl_rate AND hash_date > '.(time() - 86400).' AND drc.id_carrier = dm.id_carrier LIMIT 1';
			$res = Db::getInstance()->executeS($query);

			$this->saveLog('1h_log.txt', "3) $query \n\r", $log);

			if (is_array($res) && sizeof($res) == 1)
			{
				$this->saveLog('1h_log.txt', print_r($res,true)."\n\r"."getOrderTotal \n\r", $log);
				$orderTotal = $this->getOrderTotal($id_product, NULL, $qty);
				$this->saveLog('1h_log.txt', "$query\n\norderTotal ($id_product) $orderTotal\n\r", $log);
				$base_rate = $res[0]['rate'];

				$rate_currency = Currency::getIdByIsoCode($res[0]['currency']);
				/** IF RATE CURRENCY IS NOT THE DEFAULT STORE'S CURRENCY */
				if($rate_currency && $rate_currency != Configuration::get('PS_CURRENCY_DEFAULT'))
				{
					$return_currency = new Currency($rate_currency);
					$base_rate = $base_rate / $return_currency->conversion_rate;
				}

				if ($res[0]['extra_shipping_type'] == 2)
					$base_rate += $res[0]['extra_shipping_amount'];
				elseif ($res[0]['extra_shipping_type'] == 1)
					$base_rate += $res[0]['extra_shipping_amount'] * $orderTotal / 100;
				elseif ($res[0]['extra_shipping_type'] == 3)
					$base_rate += $res[0]['extra_shipping_amount'] * $base_rate / 100;
				$ret_amount =  number_format($base_rate,2,".","");
				if ($res[0]['free_shipping'] > 0 && $res[0]['free_shipping'] <= $orderTotal)
					$ret_amount = 0;
				$this->saveLog('1h_log.txt', "returning $ret_amount \n\r", $log);

				return $ret_amount;
			}
			else
			{
				$this->saveLog('1h_log.txt', "4) Not found in cache\n\r", $log);
				return false;
			}
		}

		return $md5;
	}

	protected function is_box_exception($id)
	{
		foreach ($this->_dhl_boxes as $box)
			if (isset($box[5]) && in_array($id, explode(",",$box[5])))
				return $box[5];
		return false;
	}

	protected function get_boxes_no_exception()
	{
		$no_exceptions = array();
		foreach ($this->_dhl_boxes as $box)
			if (!isset($box[5]) || $box[5] == "")
				$no_exceptions[] = $box;
		return $no_exceptions;
	}


	public function getShippingMethods()
	{
		$ship_meth = array(
			'4' => $this->l('Jetline'),
			'5' => $this->l('Sprintline'),
            '6' => $this->l('Secureline'),
			'S' => $this->l('Same Day'),
			'7' => $this->l('Express Easy'),
			'K' => $this->l('Express 9:00'),
			'L' => $this->l('Express 10:30'),
            'T' => $this->l('Express 12:00'),
            'O' => $this->l('Domestic Express 10:30'),
			'1' => $this->l('Domestic Express 12:00'),
			'N' => $this->l('Domestic Express 18:00'),
			'G' => $this->l('Domestic Economy Select'),
			'W' => $this->l('Economy Select EU'),
			'D' => $this->l('Express Worldwide'),
			'U' => $this->l('Express Worldwide EU'),
			'X' => $this->l('Express Envelope'),
            'C' => $this->l('Medical Express'),
            '2' => $this->l('B2C'),
            '9' => $this->l('Europack'),
            'B' => $this->l('Break Bulk Express'),
            'I' => $this->l('Break Bulk Economy'),
            'R' => $this->l('Globalmail Business'),
            'J' => $this->l('Jumbo Box'),
            'F' => $this->l('Freight Worldwide'),
		);

		return $ship_meth;
	}

	public function getPackageTypes()
	{
		$types = array(
			'CP' => $this->l('Your Packaging'),
			'DC' => $this->l('DHL Document'),
			'DM' => $this->l('DHL Domestic'),
			'PA' => $this->l('DHL Parcel'),
			'DF' => $this->l('DHL Flyer'),
			'ED' => $this->l('DHL Express Document'),
			'EE' => $this->l('DHL Express Envelope'), 
			'FR' => $this->l('DHL Freight'),
			'JB' => $this->l('DHL Junior Box'),
			'BD' => $this->l('DHL Jumbo Document'),
			'BP' => $this->l('DHL Jumbo Parcel'),
			'JD' => $this->l('DHL Jumbo Junior Document'),
			'JP' => $this->l('DHL Jumbo Junior Parcel'),
			'JJ' => $this->l('DHL Jumbo Junior Box'),
			'OD' => $this->l('DHL Other Packaging'),
			
			/** NOT IN THE DOC
			'EJ' => $this->l('DHL Express Box - Junior'),
			'ES' => $this->l('DHL Express Box - Small'),
			'EM' => $this->l('DHL Express Box - Medium'),
			'EL' => $this->l('DHL Express Box - Large'),
			'PS' => $this->l('DHL Express Pack - Small'),
			'PL' => $this->l('DHL Express Pack - Large'),
			'PD' => $this->l('DHL Padded Pouch - Small'),
			'DL' => $this->l('DHL Padded Pouch - Large'),
			*/
		);

		return $types;
	}
	
	public function _getValidPackageTypeByDHLZone($zone)
	{
		$ValidPackageType = array();
		
		$ValidPackageType['EA'] = array(
			"CP" => $this->l('Your Packaging'),
			"EE" => $this->l('DHL Express Envelope'),
			"OD" => $this->l('DHL Other Packaging'),
			"DC" => $this->l('DHL Document'),
			"DM" => $this->l('DHL Domestic'),
			"ED" => $this->l('DHL Express Document'),
			"FR" => $this->l('DHL Freight'),
			"BD" => $this->l('DHL Jumbo Document'),
			"BP" => $this->l('DHL Jumbo Parcel'),
			"JD" => $this->l('DHL Jumbo Document'),
			"JP" => $this->l('DHL Jumbo Junior Parcel'),
			"PA" => $this->l('DHL Parcel'),
			"DF" => $this->l('DHL Flyer'),
		);  
		  
		$ValidPackageType['AP'] = array(
			"CP" => $this->l('Your Packaging'),
			"EE" => $this->l('DHL Express Envelope'),
			"OD" => $this->l('DHL Other Packaging'),
			"JB" => $this->l('DHL Junior Box'),
			"JJ" => $this->l('DHL Jumbo Junior Box'),
			"DF" => $this->l('DHL Flyer'),
		);        
		  
		$ValidPackageType['AM'] = array(
			"CP" => $this->l('Your Packaging'),
			"EE" => $this->l('DHL Express Envelope'),
			"OD" => $this->l('DHL Other Packaging'),
		);	
		
		return (isset($ValidPackageType[$zone]) ? $ValidPackageType[$zone] : array());	
	}

	public function getDimensionsByType($type)
	{
		//inches
		$dimensions = array(                               
			'CP' => array('w' => '1', 'h' => '1', 'd' => '1'),  
			'EE' => array('w' => '12.6', 'h' => '9.4', 'd' => '5'),
			'OD' => array('w' => '1', 'h' => '1', 'd' => '1'),
			'JJ' => array('w' => '16.93', 'h' => '13.38', 'd' => '10.24'),
			'JB' => array('w' => '16.93', 'h' => '13.78', 'd' => '17.72'),
			'DF' => array('w' => '12.20', 'h' => '15.75', 'd' => '5'),    
			'DC' => array('w' => '1', 'h' => '1', 'd' => '1'),    
			'DM' => array('w' => '1', 'h' => '1', 'd' => '1'),    
			'ED' => array('w' => '1', 'h' => '1', 'd' => '1'),    
			'FR' => array('w' => '1', 'h' => '1', 'd' => '1'),    
			'BD' => array('w' => '1', 'h' => '1', 'd' => '1'),    
			'BP' => array('w' => '1', 'h' => '1', 'd' => '1'),    
			'JD' => array('w' => '1', 'h' => '1', 'd' => '1'),    
			'JP' => array('w' => '16', 'h' => '13', 'd' => '10'),
			'PA' => array('w' => '1', 'h' => '1', 'd' => '1'),
			
		);

		return $dimensions[$type];
	}


	public function getDropoffTypes()
	{
		$types = array(
			'' => $this->l('Default'),
			'Q' => $this->l('Non Standard Pickup - surcharges'),
			'QA' => $this->l('Non Standard Pickup - extra charges'),
			'QB' => $this->l('Early Pickup'),
			'QC' => $this->l('Time Window Pickup'),
			'QD' => $this->l('Late Pickup'),
			'QE' => $this->l('Residential Pickup'),
			'QF' => $this->l('Loading/Waiting'),
			'QH' => $this->l('Bypass Injection'),
			'QI' => $this->l('Direct Injection'),
			'QY' => $this->l('Drop Off at Facility'),
		);

		return $types;
	}

	protected function applyUpdates()
	{
		//labels update
		$this->registerHook('adminOrder');
		Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fe_dhl_labels_info` (
				`id_label_info` int(11) NOT NULL AUTO_INCREMENT,
				`id_order` int(11) NOT NULL,
				`info` TEXT NOT NULL,
				`tracking_id_type` TINYTEXT NOT NULL,
				`tracking_numbers` TEXT NOT NULL,
				PRIMARY KEY (`id_label_info`)
			) ENGINE = '._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		');
		
		if(!Configuration::get('DHL_ENABLE_LABELS'))
			Configuration::updateValue('DHL_ENABLE_LABELS', 'dhl');
			
		/** VERIFY IF ALL TABLES ARE CREATED PROPERLY */
		$tables = $this->_getTables();
		foreach($tables as $table => $query)
		{
			$showTable = Db::getInstance()->ExecuteS('SHOW TABLES LIKE "'.$table.'"'); 
			if(is_array($showTable) && !count($showTable))
			{
				/** CLEAN RATE CACHE */
				Db::getInstance()->Execute('TRUNCATE `'._DB_PREFIX_.'fe_dhl_rate_cache`');
				Db::getInstance()->Execute('TRUNCATE `'._DB_PREFIX_.'fe_dhl_hash_cache`');
				Db::getInstance()->Execute('TRUNCATE `'._DB_PREFIX_.'fe_dhl_package_rate_cache`');
				Db::getInstance()->Execute('TRUNCATE `'._DB_PREFIX_.'fe_dhl_invalid_dest`');
				/** CREATE TABLE */
				Db::getInstance()->Execute($query);
			}    
		} 
	}

	protected function sortBoxes()
	{
		if (sizeof($this->_dhl_boxes) > 0)
		{
			foreach($this->_dhl_boxes as $box)
			{
				$tot = round($box[0] * $box[1] * $box[2]);
				while (isset($arr_tmp[$tot]))
					$tot++;
				$box[0] = round($box[0],2);
				$box[1] = round($box[1],2);
				$box[2] = round($box[2],2);
				$arr_tmp[$tot] = $box;
			}
			if (!isset($arr_tmp) || !is_array($arr_tmp))
				$arr_tmp = array();
			ksort($arr_tmp);
			$sorted = array();
			foreach ($arr_tmp as $box)
				$sorted[] = $box;
			$this->_dhl_boxes = $sorted;
		}
	}

	public function curl_post($post_url, $post_string)
	{
		$request = curl_init($post_url); // initiate curl object
		curl_setopt($request, CURLOPT_TIMEOUT, 30); //timeout 10 sec
		curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
		curl_setopt($request, CURLOPT_POSTFIELDS, $post_string); // use HTTP POST to send form data
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response.
		$post_response = curl_exec($request); // execute curl post and store results in $post_response
		// 	additional options may be required depending upon your server configuration
		// 	you can find documentation on curl options at http://www.php.net/curl_setopt
		// DHL sometimes returns an SSL error, if we get an error, try the plain http URL
		if (curl_errno($request) == 28) //if timeout
		{
			curl_close ($request); // close curl object
			return 28;
		}
		curl_close ($request); // close curl object
		return $post_response;
	}
        
        //HOOK_BEFORECARRIER
        
        public function hookDisplayBeforeCarrier($params){
//            return $this->hookAdminOrder($params);
        }
	
	public function hookAdminOrder($params)
	{
		$this->applyUpdates();

		$html = '';
		$ps_version  = floatval(substr(_PS_VERSION_,0,3));

		$order = new Order($params['id_order']);
		$carrier = new Carrier($order->id_carrier);
		
		$ShippingModules = $this->_getPrestoChangeoShippingModulesForOrder($carrier);
		
		/** SAVE/LOAD PREDEFINED CARRIER */
		if(Tools::getValue('shipping_label_carrier'))
		{
			$shipping_label_carrier = Tools::getValue('shipping_label_carrier'); 
			$predefined = @unserialize(Configuration::get('PC_SHIPPING_CARRIERS'));
			$predefined[Tools::getValue('id_order')] = $shipping_label_carrier;
			Configuration::updateValue('PC_SHIPPING_CARRIERS', serialize($predefined)); 
		}
		else
		{                                           
			$predefined = @unserialize(Configuration::get('PC_SHIPPING_CARRIERS')); 
			if(isset($predefined[Tools::getValue('id_order')]))
				$shipping_label_carrier = $predefined[Tools::getValue('id_order')];
			else
				$shipping_label_carrier = false;
		}
		/***/
		
		$package_types = $this->getPackageTypes();

		if(!$shipping_label_carrier && is_array($ShippingModules) && count($ShippingModules) > 1 && !defined('PC_SHIPPING_LABEL_LOADED'))
		{
			define('PC_SHIPPING_LABEL_LOADED', true);
			
			$html .= '
				<br/>
				<form action="'.$_SERVER['REQUEST_URI'].'" method="POST" id="LabelPrintingForm" target="_self">
			';
				
			if($ps_version == 1.6)
			{
				$html .= '
				<div class="row">
					<div class="col-lg-12">
						<!-- Shipping Label -->
						<div class="panel">
							<h3><span style="float:left;">'.$this->l('Generate shipping label').'</h3>
				';
			}
			else
			{
				$html .= '
					<fieldset>
						<legend>'.$this->l('Generate shipping label').'</legend>                
				';

			}
			
			/** INSERT CARRIER SELECTION */
			$html .= '     
						<p>
							<label for="shipping_label_carrier" style="float: left; width: auto; '.($this->getPSV() == 1.6 ? 'line-height: 27px;' : '').'">'.$this->l('Select Carrier').':</label> 
							<select name="shipping_label_carrier" id="shipping_label_carrier" style="float: left; width: 190px; margin: 0 1%;">
			';
			
			foreach($ShippingModules as $shipping_module)
			{
				if($shipping_module == 'usps' || $shipping_module == 'ups' || $shipping_module == 'dhl')
					$shipping_module_name = strtoupper($shipping_module);
				elseif($shipping_module == 'fedex')
					$shipping_module_name = 'FedEx';
				elseif($shipping_module == 'canadapost')
					$shipping_module_name = 'Canada Post';
				
				$html .= '
							<option value="'.$shipping_module.'" '.($shipping_label_carrier == $shipping_module ? 'selected="selected"' : '').'>'.$shipping_module_name.'</option>
				';
			}

			$html .= '
							</select>
							
							<input type="submit" value="'.$this->l('Show Settings').'" id="selectShippingLabelCarrier" name="selectShippingLabelCarrier" class="'.($ps_version == 1.6 ? 'btn btn-default' : 'button').'" />
						</p>
			';
			
			if($ps_version == 1.6)
			{
				$html .= '
						</div>
					</div>
				</div>
				';
			}
			else
			{
				$html .= '      
					</fieldset>
				';

			} 

			$html .= '
				</form>
				<br/>
			';
		}
		elseif($shipping_label_carrier == $this->name || (is_array($ShippingModules) && count($ShippingModules) == 1 && $ShippingModules[0] == $this->name))
		{    
			/* VERIFY IF THERE IS A LABEL PREVIOUSLY PRIINTED 
			* 
			*  if(yes){ @return true; }
			*  else{ @return false; }
			*/
			$files = glob(dirname(__FILE__).'/labels/'.$params['id_order'].'/*');
			$hasPrintedLabel = false;
			if(is_array($files) && count($files) > 0)
			{
				foreach ($files as $file)
				{
					$file = explode('/', $file);
					$file = $file[count($file)-1];
					if($file != 'index.php'){
						$hasPrintedLabel = true;
					}
				}
			}
			
			$html .= '
				<br/>
			';
			
			if($ps_version == 1.6)
			{
				$html .= '
				<div class="row">
					<div class="col-lg-12">
						<!-- DHL Shipping Label -->
						<div class="panel">
							<h3><span style="float:left;">'.$this->l('Generate DHL shipping label').' ('.($ps_version == 1.6 ? '</span>' : '').'<a href="#" id="expandLabelForm" class="expand" style="color:blue;text-decoration:underline;'.($ps_version == 1.6 ? 'display: inherit;float: left;' : '').'">'.$this->l('Expand').'</a>)</h3>
				';
			}
			else
			{
				$html .= '
					<fieldset>
						<legend>'.$this->l('Generate DHL shipping label').' (<a href="#" id="expandLabelForm" class="expand" style="color:blue;text-decoration:underline;">'.$this->l('Expand').'</a>)</legend>                
				';
			}
			
			/** INSERT CARRIER SELECTION */
			if(is_array($ShippingModules) && count($ShippingModules) > 1)
			{
				$html .= '
						<form action="'.$_SERVER['REQUEST_URI'].'" method="POST" id="LabelPrintingForm" target="_self">
							<p>
								<label for="shipping_label_carrier" style="float: left; width: auto; '.($this->getPSV() == 1.6 ? 'line-height: 27px;' : '').'">'.$this->l('Select Carrier').':</label> 
								<select name="shipping_label_carrier" id="shipping_label_carrier" style="float: left; width: 190px; margin: 0 1%;">
				';
				
				foreach($ShippingModules as $shipping_module)
				{
					if($shipping_module == 'usps' || $shipping_module == 'ups' || $shipping_module == 'dhl')
						$shipping_module_name = strtoupper($shipping_module);
					elseif($shipping_module == 'fedex')
						$shipping_module_name = 'FedEx';
					elseif($shipping_module == 'canadapost')
						$shipping_module_name = 'Canada Post';
					
					$html .= '
								<option value="'.$shipping_module.'" '.($shipping_label_carrier == $shipping_module ? 'selected="selected"' : '').'>'.$shipping_module_name.'</option>
					';
				}

				$html .= '
								</select>
								
								<input type="submit" value="'.$this->l('Show Settings').'" id="selectShippingLabelCarrier" name="selectShippingLabelCarrier" class="'.($ps_version == 1.6 ? 'btn btn-default' : 'button').'" />
							</p>
						</form>
				';
			}
			/***/
			
			$html .= '
					<form action="'.$this->_path.'display_label.php?shippingLabel=1&id_order='.$params['id_order'].'" method="POST" id="labelForm" target="_blank">
						<div id="labelFormContent"></div>
					</form>
			';
				
			if($ps_version == 1.6)
			{
				$html .= '
						</div>
					</div>
				</div>
				';
			}
			else
			{                
				$html .= '
					</fieldset>
				';
			}
			
			$html .= '
				<br/>

				<script type="text/javascript">
					var psv = '.$this->getPSV().';
					$(document).ready(function() {

						$("#labelForm input, #labelForm select, #labelForm textarea").live("change", function(){
							saveLabelInfo();
						});

						$("#expandLabelForm").click(function(){
							if($(this).hasClass("expand")) {
								$.ajax({
									type: "POST",
									url: "'.(isset($this->context->shop->virtual_uri) ? substr($this->context->shop->virtual_uri,0, -1) : '').$this->_path.'ajaxLabelSettings.php",
									async: false,
									cache: false,
									data: {dhl_random: "'.$this->_dhl_random.'", load: 1, id_order: '.$params['id_order'].'},
									success: function(html) {
										$("#labelFormContent").html(html);
										$("#expandLabelForm").text("'.$this->l('Collapse').'").removeClass("expand").addClass("collapse");
										if(psv < 1.5)
											$("#labelForm").parent("div").css("float", "none").next("div").css("margin-left", "0");
									}
								});
							}
							else {
								$("#labelFormContent").html("");
								$("#expandLabelForm").text("'.$this->l('Expand').'").removeClass("collapse").addClass("expand");
								if(psv < 1.5)
									$("#labelForm").parent("div").css("float", "left").next("div").css("margin-left", "40px");
							}
						})'.(!$hasPrintedLabel || Tools::getValue('shipping_label_carrier') ? '.trigger("click")' : '').';

						$("#add_pack").live("click", function(){
							var pack_type = $("#dhl_pack").val();
							var pack_width = $("#dhl_width").val();
							var pack_height = $("#dhl_height").val();
							var pack_depth = $("#dhl_depth").val();
							var pack_weight = $("#dhl_weight").val();
							var pack_insurance = $("#dhl_insurance").val();
							var pack_cod_code = $("#dhl_cod_type").val();
							var pack_cod_amount = $("#dhl_cod_amount").val();

							//validation
							if(pack_weight <= 0)
							{
								alert("'.$this->l('Please enter a weight greater than 0.').'");
								return false;
							}

							var id = $("#packages_list .package_item").length;
							while($("#pack_width_" + id).length)
								id = id + 1;

							var pack_html = \'\
							<p class="package_item" style="float: left; width: 100%; \' + (id > 0 ? "margin: 20px 0 10px 0;border-top: 1px solid #cccccc;padding: 16px 0 0 0;" : "") + \'">\
								<span style="float:left;margin: 0 2% 0 0;">\
									'.$this->l('Package Type').': <sup>*</sup>\
									<br />\
									<select class="pack_type_select" name="pack[\' + id + \'][type]" id="dhl_pack\' + id + \'" style="width:180px;">';
									
									if(is_array($package_types) && count($package_types))
									{
										foreach ($package_types as $code => $package_name)
										{
											$html .= '<option value="'.$code.'" \' + (pack_type == "'.$code.'" ? "selected" : "") + \'>'.$package_name.'</option>';
										}
									}
									
						$html .= '\
									</select>\
								</span>\
								\
								<span style="float:left;margin: 0 2% 0 0;">\
									'.$this->l('Width').': <sup>*</sup>\
									<br />\
									<input type="text" class="pack_dimensions" id="pack_width_\' + id + \'" name="pack[\' + id + \'][w]" style="width:40px;" value="\' + pack_width + \'">\
								</span>\
								\
								<span style="float:left;margin: 0 2% 0 0;">\
									'.$this->l('Height').': <sup>*</sup>\
									<br />\
									<input type="text" class="pack_dimensions" id="pack_height_\' + id + \'" name="pack[\' + id + \'][h]" style="width:40px;" value="\' + pack_height + \'">\
								</span>\
								\
								<span style="float:left;margin: 0 2% 0 0;">\
									'.$this->l('Depth').': <sup>*</sup>\
									<br />\
									<input type="text" class="pack_dimensions" id="pack_depth_\' + id + \'" name="pack[\' + id + \'][d]" style="width:40px;" value="\' + pack_depth + \'">\
								</span>\
								\
								<span style="float:left;margin: 0 2% 0 0;">\
									'.$this->l('Weight').': <sup>*</sup>\
									<br />\
									<input type="text" id="pack_weight_\' + id + \'" name="pack[\' + id + \'][weight]" style="width:55px;" value="\' + pack_weight + \'" class="package_weight">\
								</span>\
								\
								<span style="float:left;margin: 0 2% 0 0;">\
									'.$this->l('Insured Value').':\
									<br />\
									<input type="text" id="pack_insurance_\' + id + \'" name="pack[\' + id + \'][insurance]" style="width:85px;" value="\' + pack_insurance + \'" class="package_insurance">\
								</span>\
								\
								<span style="float:left;margin: 20px 0 0 0;">\
									<img src="'._MODULE_DIR_.'dhl/img/delete.gif" id="pack_delete_\' + id + \'" rel="\' + id + \'" class="pack_delete" style="cursor:pointer;">  \
								</span>\
							</p>\
							\';  
							
							$("#packages_list #package_list_body").append(pack_html);
							$("select[class=\'pack_type_select\']").change();
							saveLabelInfo();
						});   
						
						$("img[class=\'pack_delete\']").live("click", function(){ 
							/** REMOVE PACKAGE LABELS LINE */
							$(this).parents("p").remove();    
											   
							saveLabelInfo();
						}); 

						$("#labelForm").live("submit", function(){
							var errorsCount = 0;
							$(".package_weight:visible").each(function(){
								if($(this).val() <= 0)
								{
									//if this error is first
									if(errorsCount < 1)
										alert("'.$this->l('Please enter a weight greater than 0.').'");
									$(this).css("outline","1px solid red");
									setTimeout(function(){
										$(".package_weight").css("outline","");
									}, 1500);
									errorsCount++;
								}
							});
							if(errorsCount > 0) 
								return false;

							var labelFormat = $("#label_format").val();
							var formdata = $("#labelForm").serialize() + "&id_employee='.$this->context->cookie->id_employee.'&dhl_random='.$this->_dhl_random.'&shippingLabel=1&id_order='.(int)$params['id_order'].'&ajax=1";
							$("#labels_links").html("");
							$("#labelLoader").show();
							$("#void_response").html("");
							$("#generateLabel").attr("disabled","disabled");

							$.ajax({
								type: "POST",
								url: "'.$this->_path.'display_label.php",
								async: true,
								cache: false,
								data: formdata,
								success: function(json) {
									json = $.parseJSON(json);
									
									if(json.labels != 0)
									{                                       
										var arr = json.labels.split(",");
										var length = arr.length;
										
										for (var i = 0; i < length; i++) {
										  window.open("'._MODULE_DIR_.$this->name.'/labels/download_file.php?file=" + arr[i], "_blank");
										}
										
										setTimeout(function(){ location.reload(); }, 3000);
									}
										
									$("#labelLoader").hide();
									$("#labels_links").html(json.html);
									$("#generateLabel").removeAttr("disabled");
									
									if(json.labels != 0)
									{
										$.ajax({
											type: "POST",
											url: "'.$this->_path.'display_label.php",
											async: true,
											cache: false,
											data: {id_employee: "'.$this->context->cookie->id_employee.'", dhl_random: "'.$this->_dhl_random.'", "id_order": '.(int)$params['id_order'].', "show_existing": 1},
											success: function(data) {
												$("#previous_labels_list").html(data);
											}
										});
									}
								}
							});

							return false;
						});

						$("#delete_labels").live("click", function(){
							if(confirm("'.$this->l('Are you sure you want to delete the labels from your server? It will not cancel of affect the shipment').'")) {
								$.ajax({
									type: "POST",
									url: "'.$this->_path.'display_label.php",
									async: true,
									cache: false,
									data: {id_employee: "'.$this->context->cookie->id_employee.'", dhl_random: "'.$this->_dhl_random.'", "id_order": '.(int)$params['id_order'].', "delete_labels": 1},
									success: function(html) {
										$("#previous_labels_list").html("");
										$("#labels_links").html("");
									}
								});
							}
						});

						$("#shop_country, #shipto_country").live("change", function(){
							var id_country = $(this).val();
							var type = $(this).attr("id");
							$.ajax({
								type: "POST",
								url: "'.$this->_path.'ajaxLabelSettings.php",
								data: {get_states: 1, id_country: id_country, id_order: '.$params['id_order'].', dhl_random: "'.$this->_dhl_random.'", type: type},
								success: function(html) {
									if(type == "shop_country")
										$("#shop_state").html(html);
									else if(type == "shipto_country")
										$("#shipto_state").html(html);
								}
							});
						});
					});

					function saveLabelInfo() {
						if ($("#labelForm").serialize() == "" || $("#labelForm").serialize().indexOf("attention_name=&") > 0)
							return;
						var data = $("#labelForm").serialize() + "&dhl_random='.$this->_dhl_random.'&save=1&id_order='.$params['id_order'].'";
						$.ajax({
							type: "POST",
							url: "'.$this->_path.'ajaxLabelSettings.php",
							async: false,
							cache: false,
							data: data,
							success: function(html) {
								//console.log(html);
							}
						});
					}

					function in_array(needle, haystack, strict) {	// Checks if a value exists in an array
						var found = false, key, strict = !!strict;
						for (key in haystack) {
							if ((strict && haystack[key] === needle) || (!strict && haystack[key] == needle)) {
								found = true;
								break;
							}
						}

						return found;
					}


				</script>
			';
			return $html;
		}
	}

	public function getLabelTypes()
	{
		$types = array('pdf', 'epl2', 'lp2', 'zpl2');

		return $types;
	}
	
	/**
	* defines method that will be used (AP/AM/EA) for label printing request
	*/
	public function _DHLRegionByCountryIso($country_iso)
	{
		$regions = array(
			/** APEM Region */
			'AE' => 'ap',
			'AF' => 'ap',
			'AL' => 'ap',
			'AM' => 'ap',
			'AO' => 'ap',
			'AU' => 'ap',
			'BA' => 'ap',
			'BD' => 'ap',
			'BH' => 'ap',
			'BN' => 'ap',
			'BW' => 'ap',
			'BY' => 'ap',
			'CD' => 'ap',
			'CG' => 'ap',
			'CI' => 'ap',
			'CM' => 'ap',
			'CN' => 'ap',
			'CY' => 'ap',
			'DZ' => 'ap',
			'EG' => 'ap',
			'ET' => 'ap',
			'FJ' => 'ap',
			'GA' => 'ap',
			'GH' => 'ap',
			'HK' => 'ap',
			'ID' => 'ap',
			'IN' => 'ap',
			'IQ' => 'ap',
			'IR' => 'ap',
			'JO' => 'ap',
			'JP' => 'ap',
			'KE' => 'ap',
			'KG' => 'ap',
			'KH' => 'ap',
			'KR' => 'ap',
			'KW' => 'ap',
			'KZ' => 'ap',
			'LA' => 'ap',
			'LB' => 'ap',
			'LK' => 'ap',
			'LS' => 'ap',
			'MA' => 'ap',
			'MD' => 'ap',
			'MG' => 'ap',
			'MK' => 'ap',
			'ML' => 'ap',
			'MM' => 'ap',
			'MO' => 'ap',
			'MT' => 'ap',
			'MU' => 'ap',
			'MW' => 'ap',
			'MY' => 'ap',
			'MZ' => 'ap',
			'NA' => 'ap',
			'NE' => 'ap',
			'NG' => 'ap',
			'NP' => 'ap',
			'NZ' => 'ap',
			'OM' => 'ap',
			'PG' => 'ap',
			'PH' => 'ap',
			'PK' => 'ap',
			'QA' => 'ap',
			'RE' => 'ap',
			'RS' => 'ap',
			'RU' => 'ap',
			'SA' => 'ap',
			'SD' => 'ap',
			'SG' => 'ap',
			'SL' => 'ap',
			'SN' => 'ap',
			'SY' => 'ap',
			'SZ' => 'ap',
			'TG' => 'ap',
			'TH' => 'ap',
			'TJ' => 'ap',
			'TM' => 'ap',
			'TR' => 'ap',
			'TW' => 'ap',
			'TZ' => 'ap',
			'UA' => 'ap',
			'UG' => 'ap',
			'UZ' => 'ap',
			'VN' => 'ap',
			'YE' => 'ap',
			'ZA' => 'ap',
			'ZM' => 'ap',
			'ZW' => 'ap',
			/** EU Region */
			'AT' => 'ea',
			'BE' => 'ea',
			'BG' => 'ea',
			'CH' => 'ea',
			'CZ' => 'ea',
			'DE' => 'ea',
			'DK' => 'ea',
			'EE' => 'ea',
			'ES' => 'ea',
			'FI' => 'ea',
			'FR' => 'ea',
			'GB' => 'ea',
			'GR' => 'ea',
			'HU' => 'ea',
			'IE' => 'ea',
			'IS' => 'ea',
			'IT' => 'ea',
			'LT' => 'ea',
			'LU' => 'ea',
			'LV' => 'ea',
			'NL' => 'ea',
			'NO' => 'ea',
			'PL' => 'ea',
			'PT' => 'ea',
			'RO' => 'ea',
			'SE' => 'ea',
			'SI' => 'ea',
			'SK' => 'ea',
			/** AM Region  */
			'AG' => 'am',
			'AI' => 'am',
			'AR' => 'am',
			'AW' => 'am',
			'BB' => 'am',
			'BM' => 'am',
			'BO' => 'am',
			'BR' => 'am',
			'BS' => 'am',
			'BZ' => 'am',
			'CA' => 'am',
			'CL' => 'am',
			'CO' => 'am',
			'CR' => 'am',
			'DM' => 'am',
			'DO' => 'am',
			'EC' => 'am',
			'GF' => 'am',
			'GP' => 'am',
			'GT' => 'am',
			'GY' => 'am',
			'HN' => 'am',
			'JM' => 'am',
			'KN' => 'am',
			'KY' => 'am',
			'LC' => 'am',
			'MQ' => 'am',
			'MX' => 'am',
			'NI' => 'am',
			'PA' => 'am',
			'PE' => 'am',
			'PY' => 'am',
			'SR' => 'am',
			'SV' => 'am',
			'TC' => 'am',
			'TT' => 'am',
			'US' => 'am',
			'UY' => 'am',
			'VC' => 'am',
			'VE' => 'am',
			'VG' => 'am',
			'VI' => 'am',
			'XC' => 'am',
			'XM' => 'am',
			'XN' => 'am',
			'XY' => 'am'
		);
		
		return $regions[$country_iso];
	}
	
	public function dutiablePacks()
	{
		return array('CP', 'PA', 'FR', 'JB', 'BP', 'JP', 'JJ', 'OD');
	}
	
	public function dutiableCarrierMethods($method)
	{
		/** ARRAY SCAM ---> [DOC] => NON-DOC < --- > [NON-DUTIABLE] => DUTIABLE */
		$dutiableMethods = array(
            '2' => '3',
			'4' => '4',
			'5' => '5',
			'6' => '6',
			'8' => '7',
            '9' => 'V',
			'K' => 'E',
			'L' => 'M',
			'T' => 'Y',
			'N' => 'N',
			'G' => 'G',
			'W' => 'H',
			'D' => 'P',
			'U' => 'P',
			'X' => 'X',
            'C' => 'Q',
		);
		
		if($dutiableMethods[$method])
			return $dutiableMethods[$method];
		else
			return $method;
	}
	
	public function EUCountries()
	{
		$EUCountries = array(
			'AT',
			'BE',
			'BG',
			'HR',
			'CZ',
			'CY',
			'DK',
			'EE',
			'FI',
			'FR',
			'DE',
			'GR',
			'HU',
			'IE',
			'IT',
			'LV',
			'LT',
			'LU',
			'MT',
			'NL',
			'PL',
			'PT',
			'RO',
			'SK',
			'SI',
			'ES',
			'SE',
			'UK'           
		);
		
		return $EUCountries;
	}
	
	private function _getTables()
	{
		return array(
			_DB_PREFIX_.'fe_dhl_hash_cache' =>
			'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fe_dhl_hash_cache` (
			  `id_hash` int(11) NOT NULL AUTO_INCREMENT,
			  `id_dhl_rate` int(11) NOT NULL,
			  `hash` varchar(40) NOT NULL,
			  `hash_date` int(11) NOT NULL,
			  PRIMARY KEY (`id_hash`),
			  KEY `hash` (`hash`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;',
			
			_DB_PREFIX_.'fe_dhl_invalid_dest' =>
			'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fe_dhl_invalid_dest` (
			  `id_invalid` int(11) NOT NULL AUTO_INCREMENT,
			  `method` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			  `zip` varchar(11) COLLATE utf8_unicode_ci NOT NULL,
			  `country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
			  `ondate` int(11) NOT NULL,
			  PRIMARY KEY (`id_invalid`),
			  KEY `zip` (`zip`,`country`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;',
			
			_DB_PREFIX_.'fe_dhl_labels_info' =>
			'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fe_dhl_labels_info` (
			  `id_label_info` int(11) NOT NULL AUTO_INCREMENT,
			  `id_order` int(11) NOT NULL,
			  `info` text COLLATE utf8_unicode_ci NOT NULL,
			  `tracking_id_type` tinytext COLLATE utf8_unicode_ci NOT NULL,
			  `tracking_numbers` text COLLATE utf8_unicode_ci NOT NULL,
			  PRIMARY KEY (`id_label_info`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;',
			
			_DB_PREFIX_.'fe_dhl_method' =>
			'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fe_dhl_method` (
			  `id_dhl_method` int(11) NOT NULL AUTO_INCREMENT,
			  `id_carrier` int(11) NOT NULL,
			  `method` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `free_shipping` decimal(9,2) NOT NULL,
			  `free_shipping_product` text COLLATE utf8_unicode_ci NOT NULL,
			  `free_shipping_category` text COLLATE utf8_unicode_ci NOT NULL,
			  `free_shipping_manufacturer` text COLLATE utf8_unicode_ci NOT NULL,
			  `free_shipping_supplier` text COLLATE utf8_unicode_ci NOT NULL,
			  `extra_shipping_type` int(1) NOT NULL,
			  `extra_shipping_amount` decimal(9,2) NOT NULL,
			  `insurance_minimum` int(11) NOT NULL,
			  `insurance_type` int(1) NOT NULL,
			  `insurance_amount` decimal(9,2) NOT NULL,
			  PRIMARY KEY (`id_dhl_method`),
			  KEY `id_carrier` (`id_carrier`),
			  KEY `method` (`method`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;',
			
			_DB_PREFIX_.'fe_dhl_package_rate_cache' =>
			'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fe_dhl_package_rate_cache` (
			  `id_package` int(11) NOT NULL AUTO_INCREMENT,
			  `id_dhl_rate` int(11) NOT NULL,
			  `weight` decimal(17,2) NOT NULL,
			  `width` decimal(17,2) NOT NULL,
			  `height` decimal(17,2) NOT NULL,
			  `depth` decimal(17,2) NOT NULL,
			  PRIMARY KEY (`id_package`),
			  KEY `weight` (`weight`,`width`,`height`,`depth`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;',
			
			_DB_PREFIX_.'fe_dhl_rate_cache' =>
			'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fe_dhl_rate_cache` (
			  `id_dhl_rate` int(11) NOT NULL AUTO_INCREMENT,
			  `id_carrier` int(11) NOT NULL,
			  `origin_zip` varchar(11) COLLATE utf8_unicode_ci NOT NULL,
			  `origin_country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
			  `dest_zip` varchar(11) COLLATE utf8_unicode_ci NOT NULL,
			  `dest_country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
			  `method` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			  `insurance` int(11) NOT NULL,
			  `dropoff` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			  `packing` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			  `packages` decimal(17,2) NOT NULL,
			  `weight` decimal(17,2) NOT NULL,
			  `rate` decimal(17,2) NOT NULL,
			  `currency` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
			  `quote_date` int(11) NOT NULL,
			  PRIMARY KEY (`id_dhl_rate`),
			  KEY `origin_zip` (`origin_zip`,`origin_country`,`dest_zip`,`dest_country`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;',
			
		);
	}    
	
	public function getPSV()
	{
		return parent::getPSV();
	}
}