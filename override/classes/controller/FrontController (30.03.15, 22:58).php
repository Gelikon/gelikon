<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class FrontController extends FrontControllerCore
{
        public function productSort()
	{
		// $this->orderBy = Tools::getProductsOrder('by', Tools::getValue('orderby'));
		// $this->orderWay = Tools::getProductsOrder('way', Tools::getValue('orderway'));
		// 'orderbydefault' => Tools::getProductsOrder('by'),
		// 'orderwayposition' => Tools::getProductsOrder('way'), // Deprecated: orderwayposition
		// 'orderwaydefault' => Tools::getProductsOrder('way'),

		$stock_management = Configuration::get('PS_STOCK_MANAGEMENT') ? true : false; // no display quantity order if stock management disabled
		$order_by_values = array(0 => 'name', 1 => 'price', 2 => 'date_add', 3 => 'date_upd', 
                4 => 'position', 5 => 'manufacturer_name', 6 => 'quantity', 7 => 'reference',8=>'bestsale');
		$order_way_values = array(0 => 'asc', 1 => 'desc');
		$this->orderBy = Tools::strtolower(Tools::getValue('orderby', $order_by_values[(int)Configuration::get('PS_PRODUCTS_ORDER_BY')]));
		$this->orderWay = Tools::strtolower(Tools::getValue('orderway', $order_way_values[(int)Configuration::get('PS_PRODUCTS_ORDER_WAY')]));
		if (!in_array($this->orderBy, $order_by_values))
			$this->orderBy = $order_by_values[0];
		if (!in_array($this->orderWay, $order_way_values))
			$this->orderWay = $order_way_values[0];

		$this->context->smarty->assign(array(
			'orderby' => $this->orderBy,
			'orderway' => $this->orderWay,
			'orderbydefault' => $order_by_values[(int)Configuration::get('PS_PRODUCTS_ORDER_BY')],
			'orderwayposition' => $order_way_values[(int)Configuration::get('PS_PRODUCTS_ORDER_WAY')], // Deprecated: orderwayposition
			'orderwaydefault' => $order_way_values[(int)Configuration::get('PS_PRODUCTS_ORDER_WAY')],
			'stock_management' => (int)$stock_management));
	}
    
        public function init()
	{
		/*
		 * Globals are DEPRECATED as of version 1.5.
		 * Use the Context to access objects instead.
		 * Example: $this->context->cart
		 */
		global $useSSL, $cookie, $smarty, $cart, $iso, $defaultCountry, $protocol_link, $protocol_content, $link, $css_files, $js_files, $currency;

		if (self::$initialized)
			return;
		self::$initialized = true;

		parent::init();

		// If current URL use SSL, set it true (used a lot for module redirect)
		if (Tools::usingSecureMode())
			$useSSL = true;

		// For compatibility with globals, DEPRECATED as of version 1.5
		$css_files = $this->css_files;
		$js_files = $this->js_files;

		// If we call a SSL controller without SSL or a non SSL controller with SSL, we redirect with the right protocol
		if (Configuration::get('PS_SSL_ENABLED') && $_SERVER['REQUEST_METHOD'] != 'POST' && $this->ssl != Tools::usingSecureMode())
		{	
			header('HTTP/1.1 301 Moved Permanently');
			header('Cache-Control: no-cache');
			if ($this->ssl)					
				header('Location: '.Tools::getShopDomainSsl(true).$_SERVER['REQUEST_URI']);
			else						
				header('Location: '.Tools::getShopDomain(true).$_SERVER['REQUEST_URI']);
			exit();
		}
		
		if ($this->ajax)
		{
			$this->display_header = false;
			$this->display_footer = false;
		}

		// if account created with the 2 steps register process, remove 'accoun_created' from cookie
		if (isset($this->context->cookie->account_created))
		{
			$this->context->smarty->assign('account_created', 1);
			unset($this->context->cookie->account_created);
		}

		ob_start();

		// Init cookie language
		// @TODO This method must be moved into switchLanguage
		Tools::setCookieLanguage($this->context->cookie);

		$protocol_link = (Configuration::get('PS_SSL_ENABLED') || Tools::usingSecureMode()) ? 'https://' : 'http://';
		$useSSL = ((isset($this->ssl) && $this->ssl && Configuration::get('PS_SSL_ENABLED')) || Tools::usingSecureMode()) ? true : false;
		$protocol_content = ($useSSL) ? 'https://' : 'http://';
		$link = new Link($protocol_link, $protocol_content);
		$this->context->link = $link;

		if ($id_cart = (int)$this->recoverCart())
			$this->context->cookie->id_cart = (int)$id_cart;

		if ($this->auth && !$this->context->customer->isLogged($this->guestAllowed))
			Tools::redirect('index.php?controller=authentication'.($this->authRedirection ? '&back='.$this->authRedirection : ''));

		/* Theme is missing */
		if (!is_dir(_PS_THEME_DIR_))
			throw new PrestaShopException((sprintf(Tools::displayError('Current theme unavailable "%s". Please check your theme directory name and permissions.'), basename(rtrim(_PS_THEME_DIR_, '/\\')))));

		if (Configuration::get('PS_GEOLOCATION_ENABLED'))
			if (($newDefault = $this->geolocationManagement($this->context->country)) && Validate::isLoadedObject($newDefault))
				$this->context->country = $newDefault;

		$currency = Tools::setCurrency($this->context->cookie);

		if (isset($_GET['logout']) || ($this->context->customer->logged && Customer::isBanned($this->context->customer->id)))
		{
			$this->context->customer->logout();

			Tools::redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null);
		}
		elseif (isset($_GET['mylogout']))
		{
			$this->context->customer->mylogout();
			Tools::redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null);
		}

		/* Cart already exists */
		if ((int)$this->context->cookie->id_cart)
		{
			$cart = new Cart($this->context->cookie->id_cart);
			if ($cart->OrderExists())
			{
				unset($this->context->cookie->id_cart, $cart, $this->context->cookie->checkedTOS);
				$this->context->cookie->check_cgv = false;
			}
			/* Delete product of cart, if user can't make an order from his country */
			elseif (intval(Configuration::get('PS_GEOLOCATION_ENABLED')) &&
					!in_array(strtoupper($this->context->cookie->iso_code_country), explode(';', Configuration::get('PS_ALLOWED_COUNTRIES'))) &&
					$cart->nbProducts() && intval(Configuration::get('PS_GEOLOCATION_NA_BEHAVIOR')) != -1 &&
					!FrontController::isInWhitelistForGeolocation() &&
					!in_array($_SERVER['SERVER_NAME'], array('localhost', '127.0.0.1')))
				unset($this->context->cookie->id_cart, $cart);
			// update cart values
			elseif ($this->context->cookie->id_customer != $cart->id_customer || $this->context->cookie->id_lang != $cart->id_lang || $currency->id != $cart->id_currency)
			{
				if ($this->context->cookie->id_customer)
					$cart->id_customer = (int)($this->context->cookie->id_customer);
				$cart->id_lang = (int)($this->context->cookie->id_lang);
				$cart->id_currency = (int)$currency->id;
				$cart->update();
			}
			/* Select an address if not set */
			if (isset($cart) && (!isset($cart->id_address_delivery) || $cart->id_address_delivery == 0 ||
				!isset($cart->id_address_invoice) || $cart->id_address_invoice == 0) && $this->context->cookie->id_customer)
			{
				$to_update = false;
				if (!isset($cart->id_address_delivery) || $cart->id_address_delivery == 0)
				{
					$to_update = true;
					$cart->id_address_delivery = (int)Address::getFirstCustomerAddressId($cart->id_customer);
				}
				if (!isset($cart->id_address_invoice) || $cart->id_address_invoice == 0)
				{
					$to_update = true;
					$cart->id_address_invoice = (int)Address::getFirstCustomerAddressId($cart->id_customer);
				}
				if ($to_update)
					$cart->update();
			}
		}

		if (!isset($cart) || !$cart->id)
		{
			$cart = new Cart();
			$cart->id_lang = (int)($this->context->cookie->id_lang);
			$cart->id_currency = (int)($this->context->cookie->id_currency);
			$cart->id_guest = (int)($this->context->cookie->id_guest);
			$cart->id_shop_group = (int)$this->context->shop->id_shop_group;
			$cart->id_shop = $this->context->shop->id;
			if ($this->context->cookie->id_customer)
			{
				$cart->id_customer = (int)($this->context->cookie->id_customer);
				$cart->id_address_delivery = (int)(Address::getFirstCustomerAddressId($cart->id_customer));
				$cart->id_address_invoice = $cart->id_address_delivery;
			}
			else
			{
				$cart->id_address_delivery = 0;
				$cart->id_address_invoice = 0;
			}

			// Needed if the merchant want to give a free product to every visitors
			$this->context->cart = $cart;
			CartRule::autoAddToCart($this->context);
		}
		else
			$this->context->cart = $cart;	

		/* get page name to display it in body id */

		// Are we in a payment module
		$module_name = '';
		if (Validate::isModuleName(Tools::getValue('module')))
			$module_name = Tools::getValue('module');

		if (!empty($this->page_name))
			$page_name = $this->page_name;
		elseif (!empty($this->php_self))
			$page_name = $this->php_self;
		elseif (Tools::getValue('fc') == 'module' && $module_name != '' && (Module::getInstanceByName($module_name) instanceof PaymentModule))
			$page_name = 'module-payment-submit';
		// @retrocompatibility Are we in a module ?
		elseif (preg_match('#^'.preg_quote($this->context->shop->physical_uri, '#').'modules/([a-zA-Z0-9_-]+?)/(.*)$#', $_SERVER['REQUEST_URI'], $m))
			$page_name = 'module-'.$m[1].'-'.str_replace(array('.php', '/'), array('', '-'), $m[2]);
		else
		{
			$page_name = Dispatcher::getInstance()->getController();
			$page_name = (preg_match('/^[0-9]/', $page_name) ? 'page_'.$page_name : $page_name);
		}

		$this->context->smarty->assign(Meta::getMetaTags($this->context->language->id, $page_name));
		$this->context->smarty->assign('request_uri', Tools::safeOutput(urldecode($_SERVER['REQUEST_URI'])));

		/* Breadcrumb */
		$navigationPipe = (Configuration::get('PS_NAVIGATION_PIPE') ? Configuration::get('PS_NAVIGATION_PIPE') : '>');
		$this->context->smarty->assign('navigationPipe', $navigationPipe);

		// Automatically redirect to the canonical URL if needed
		if (!empty($this->php_self) && !Tools::getValue('ajax'))
			$this->canonicalRedirection($this->context->link->getPageLink($this->php_self, $this->ssl, $this->context->language->id));

		Product::initPricesComputation();

		$display_tax_label = $this->context->country->display_tax_label;
		if (isset($cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}) && $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')})
		{
			$infos = Address::getCountryAndState((int)($cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));
			$country = new Country((int)$infos['id_country']);
			$this->context->country = $country;
			if (Validate::isLoadedObject($country))
				$display_tax_label = $country->display_tax_label;
		}

		$languages = Language::getLanguages(true, $this->context->shop->id);
		$meta_language = array();
		foreach ($languages as $lang)
			$meta_language[] = $lang['iso_code'];

		$compared_products = array();
		if (Configuration::get('PS_COMPARATOR_MAX_ITEM') && isset($this->context->cookie->id_compare))
			$compared_products = CompareProduct::getCompareProducts($this->context->cookie->id_compare);

                $cat_main = new Category(2);
                $tree = $cat_main->recurseLiteCategTree(1);
                $start_cats = array();
                
                foreach ($tree['children'] as $k => $child){
                    $start_cats[$child['id']]['name'] = $child['name'];
                    $start_cats[$child['id']]['id'] = $child['id'];
                }
                $best_presetage = 0;
                if(isset($this->context->customer->id_default_group) && isset($this->context->customer->id) && !empty($this->context->customer->id_default_group)){
                    $customer_groups = Db::getInstance()->executeS("SELECT id_group FROM ps_customer_group WHERE id_customer={$this->context->customer->id}"); 
                    $date_now = new DateTime(date('Y-m-d H:i:s'));
                    if(is_array($customer_groups) && count($customer_groups) > 0)
                        foreach ($customer_groups as $row_group){
                            $id_default_group = $row_group['id_group'];
                            $sql = "SELECT * FROM ps_specific_price_rule WHERE (id_shop={$this->context->shop->id} OR id_shop=0)
                            AND (id_group={$id_default_group} OR id_group=0)
                            AND (id_currency={$this->context->cookie->id_currency} OR id_currency=0)
                            AND (id_country={$this->context->country->id} OR id_country=0)
                            AND reduction_type = 'percentage'
                            ORDER BY reduction DESC";
                            $result = Db::getInstance()->executeS($sql);
                            foreach ($result as $row){
                                if(!empty($row['from'])){
                                    $date_from = new DateTime(date($row['from']));
                                    if($date_from->format('U') > $date_now->format('U'))
                                        continue;
                                }
                                if(!empty($row['to'])){
                                    $date_to = new DateTime(date($row['to']));
                                    if($date_to->format('U') < $date_now->format('U'))
                                        continue;
                                }
                                if($row['reduction'] > $best_presetage)
                                    $best_presetage = $row['reduction'];
                            }
                        }
                        
                    if($best_presetage > 0)
                        $best_presetage = round($best_presetage);
                }
                
                if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
                    if(isset($_GET['translit']) && $_GET['translit'] == 'on'){
                        $this->context->cookie->shut_off_transit = 0;
                    }
                    if(isset($_GET['translit']) && $_GET['translit'] == 'off'){
                        $this->context->cookie->shut_off_transit = 1;
                    }
                    die(Tools::jsonEncode(1));
                }
                
                $mod_translit = false;
                if($this->context->language->id != 1)
                    if(!isset($this->context->cookie->shut_off_transit) || $this->context->cookie->shut_off_transit == 0){
                        $mod_translit = 'yes';
                    }
                    else{
                        $mod_translit = 'no';
                    }
        
                    
                   
                    
		$this->context->smarty->assign(array(
			// Usefull for layout.tpl
                        'mod_translit' =>   $mod_translit,
                        'best_presetage' => $best_presetage > 0 ? $best_presetage : null,
                        'start_cats' => $start_cats,
			'mobile_device' => $this->context->getMobileDevice(),
			'link' => $link,
			'cart' => $cart,
			'currency' => $currency,
			'cookie' => $this->context->cookie,
			'page_name' => $page_name,
			'hide_left_column' => !$this->display_column_left,
			'hide_right_column' => !$this->display_column_right,
			'base_dir' => _PS_BASE_URL_.__PS_BASE_URI__,
			'base_dir_ssl' => $protocol_link.Tools::getShopDomainSsl().__PS_BASE_URI__,
			'content_dir' => $protocol_content.Tools::getHttpHost().__PS_BASE_URI__,
			'base_uri' => $protocol_content.Tools::getHttpHost().__PS_BASE_URI__.(!Configuration::get('PS_REWRITING_SETTINGS') ? 'index.php' : ''),
			'tpl_dir' => _PS_THEME_DIR_,
			'modules_dir' => _MODULE_DIR_,
			'mail_dir' => _MAIL_DIR_,
			'lang_iso' => $this->context->language->iso_code,
			'come_from' => Tools::getHttpHost(true, true).Tools::htmlentitiesUTF8(str_replace(array('\'', '\\'), '', urldecode($_SERVER['REQUEST_URI']))),
			'cart_qties' => (int)$cart->nbProducts(),
			'currencies' => Currency::getCurrencies(),
			'languages' => $languages,
			'meta_language' => implode(',', $meta_language),
			'priceDisplay' => Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer),
			'is_logged' => (bool)$this->context->customer->isLogged(),
			'is_guest' => (bool)$this->context->customer->isGuest(),
			'add_prod_display' => (int)Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
			'shop_name' => Configuration::get('PS_SHOP_NAME'),
			'roundMode' => (int)Configuration::get('PS_PRICE_ROUND_MODE'),
			'use_taxes' => (int)Configuration::get('PS_TAX'),
			'show_taxes' => (int)(Configuration::get('PS_TAX_DISPLAY') == 1 && (int)Configuration::get('PS_TAX')),
			'display_tax_label' => (bool)$display_tax_label,
			'vat_management' => (int)Configuration::get('VATNUMBER_MANAGEMENT'),
			'opc' => (bool)Configuration::get('PS_ORDER_PROCESS_TYPE'),
			'PS_CATALOG_MODE' => (bool)Configuration::get('PS_CATALOG_MODE') || !(bool)Group::getCurrent()->show_prices,
			'b2b_enable' => (bool)Configuration::get('PS_B2B_ENABLE'),
			'request' => $link->getPaginationLink(false, false, false, true),
			'PS_STOCK_MANAGEMENT' => Configuration::get('PS_STOCK_MANAGEMENT'),
			'quick_view' => (bool)Configuration::get('PS_QUICK_VIEW'),
			'shop_phone' => Configuration::get('PS_SHOP_PHONE'),
			'compared_products' => is_array($compared_products) ? $compared_products : array(),
			'comparator_max_item' => (int)Configuration::get('PS_COMPARATOR_MAX_ITEM')
		));

		// Add the tpl files directory for mobile
		if ($this->useMobileTheme())
			$this->context->smarty->assign(array(
				'tpl_mobile_uri' => _PS_THEME_MOBILE_DIR_,
			));

		// Deprecated
		$this->context->smarty->assign(array(
			'id_currency_cookie' => (int)$currency->id,
			'logged' => $this->context->customer->isLogged(),
			'customerName' => ($this->context->customer->logged ? $this->context->cookie->customer_firstname.' '.$this->context->cookie->customer_lastname : false)
		));

		$assign_array = array(
			'img_ps_dir' => _PS_IMG_,
			'img_cat_dir' => _THEME_CAT_DIR_,
			'img_lang_dir' => _THEME_LANG_DIR_,
			'img_prod_dir' => _THEME_PROD_DIR_,
			'img_manu_dir' => _THEME_MANU_DIR_,
			'img_sup_dir' => _THEME_SUP_DIR_,
			'img_ship_dir' => _THEME_SHIP_DIR_,
			'img_store_dir' => _THEME_STORE_DIR_,
			'img_col_dir' => _THEME_COL_DIR_,
			'img_dir' => _THEME_IMG_DIR_,
			'css_dir' => _THEME_CSS_DIR_,
			'js_dir' => _THEME_JS_DIR_,
			'pic_dir' => _THEME_PROD_PIC_DIR_
		);

		// Add the images directory for mobile
		if ($this->useMobileTheme())
			$assign_array['img_mobile_dir'] = _THEME_MOBILE_IMG_DIR_;

		// Add the CSS directory for mobile
		if ($this->useMobileTheme())
			$assign_array['css_mobile_dir'] = _THEME_MOBILE_CSS_DIR_;

		foreach ($assign_array as $assign_key => $assign_value)
			if (substr($assign_value, 0, 1) == '/' || $protocol_content == 'https://')
				$this->context->smarty->assign($assign_key, $protocol_content.Tools::getMediaServer($assign_value).$assign_value);
			else
				$this->context->smarty->assign($assign_key, $assign_value);

		/*
		 * These shortcuts are DEPRECATED as of version 1.5.
		 * Use the Context to access objects instead.
		 * Example: $this->context->cart
		 */
		self::$cookie = $this->context->cookie;
		self::$cart = $cart;
		self::$smarty = $this->context->smarty;
		self::$link = $link;
		$defaultCountry = $this->context->country;

		$this->displayMaintenancePage();
		if ($this->restrictedCountry)
			$this->displayRestrictedCountryPage();

		if (Tools::isSubmit('live_edit') && !$this->checkLiveEditAccess())
			Tools::redirect('index.php?controller=404');

		$this->iso = $iso;

		$this->context->cart = $cart;
		$this->context->currency = $currency;
                
                $orderTotal = $this->context->cart->getOrderTotal();
		$this->context->smarty->assign('global_order_total', $orderTotal);
	}
    
	public function setMedia()
	{
		// if website is accessed by mobile device
		// @see FrontControllerCore::setMobileMedia()
		if ($this->useMobileTheme())
		{
			$this->setMobileMedia();
			return true;
		}

		$this->addCSS(_THEME_CSS_DIR_.'grid_prestashop.css', 'all');  // retro compat themes 1.5
		$this->addCSS(_THEME_CSS_DIR_.'global.css', 'all');
		$this->addjquery();
		$this->addjqueryPlugin('easing');
		$this->addJS(_PS_JS_DIR_.'tools.js');
		$this->addJS(_THEME_JS_DIR_.'global.js');

		// Automatically add js files from js/autoload directory in the template
		if (@filemtime($this->getThemeDir().'js/autoload/'))
			foreach (scandir($this->getThemeDir().'js/autoload/', 0) as $file)
				if (preg_match('/^[^.].*\.js$/', $file))
					$this->addJS($this->getThemeDir().'js/autoload/'.$file);
		// Automatically add css files from css/autoload directory in the template
		if (@filemtime($this->getThemeDir().'css/autoload/'))
			foreach (scandir($this->getThemeDir().'css/autoload', 0) as $file)
				if (preg_match('/^[^.].*\.css$/', $file))
					$this->addCSS($this->getThemeDir().'css/autoload/'.$file);

		if (Tools::isSubmit('live_edit') && Tools::getValue('ad') && Tools::getAdminToken('AdminModulesPositions'.(int)Tab::getIdFromClassName('AdminModulesPositions').(int)Tools::getValue('id_employee')))
		{
			$this->addJqueryUI('ui.sortable');
			$this->addjqueryPlugin('fancybox');
			$this->addJS(_PS_JS_DIR_.'hookLiveEdit.js');
		}

		if (Configuration::get('PS_QUICK_VIEW'))
			$this->addjqueryPlugin('fancybox');

		if (Configuration::get('PS_COMPARATOR_MAX_ITEM') > 0)
			$this->addJS(_THEME_JS_DIR_.'products-comparison.js');
                
                $this->addJS(_THEME_JS_DIR_.'css.js');
                $this->addJS(_THEME_JS_DIR_.'plugins.js');
                $this->addJS(_THEME_JS_DIR_.'main-script.js');
                
		// Execute Hook FrontController SetMedia
		Hook::exec('actionFrontControllerSetMedia', array());
	}
}
?>
