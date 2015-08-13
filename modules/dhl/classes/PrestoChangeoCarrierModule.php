<?php

class PrestoChangeoCarrierModule extends PrestoChangeoModule
{
	public function hookCartShippingPreview($params)
	{
		return $this->hookProductActions($params, '_cart');
	}

	public function getOrderShippingCostExternal($params)
	{
		return $this->getOrderShippingCost($params, 0);
	}

	public function getPackageShippingCost($params, $shipping_cost, $products)
	{
		return $this->getOrderShippingCost($params, $shipping_cost, $products);
	}

    public function getOrderShippingCost($params, $shipping_cost, $products = null)
    {
        $log = false;
        if (!$this->active)
            return false;

        if($this->context->cookie->postcode && $this->context->cookie->id_country && $this->context->cookie->id_state)
            $cookie_state = $this->context->cookie->id_state;
        else
            $cookie_state = $this->context->cookie->pc_dest_state;
        $cookie_zip = $this->context->cookie->postcode ? $this->context->cookie->postcode :$this->context->cookie->pc_dest_zip;
        $cookie_country = $this->context->cookie->id_country ? $this->context->cookie->id_country :$this->context->cookie->pc_dest_country;
        // When placing an order from the backoffice, context->cart is not set
        if (!is_object($this->context->cart) || $this->context->cart->id != $params->id)
            $this->context->cart = $params;
        $address = new Address($params->id_address_delivery); // for guest checkout
        if ($this->context->cart->id_address_delivery > 0 && is_object($this->context->customer) && $this->context->customer->logged)
        {
            $this->saveLog('PCCMLog.txt', "\n\r"."\n\r".'1. Entered: if ($this->context->cart->id_address_delivery > 0 && is_object($this->context->customer) && $this->context->customer->logged)', $log);
            $address = new Address(intval($this->context->cart->id_address_delivery));            
            if (!Validate::isLoadedObject($address))
            {
                $id_address = Address::getFirstCustomerAddressId($this->context->cart->id_customer, true);
                if ($id_address > 0)
                    $address = new Address(intval($id_address));
            }

            if($address)
            {
                $dest_zip = $address->postcode;
                $country = new Country($address->id_country);
                $dest_country = $country->iso_code;
                $dest_state = $address->id_state;
                $dest_city = $address->city;
            }
            else
                return false;
            $state = new State($dest_state);
            $id_zone =  $state->id_zone>0?$state->id_zone:$country->id_zone;
        }
        elseif (Validate::isLoadedObject($address)) // Guest checkout.
        {
            $this->saveLog('PCCMLog.txt', "\n\r"."\n\r".'1. Entered: elseif (Validate::isLoadedObject($address))', $log);
                
            $dest_zip = $address->postcode;
            $country = new Country($address->id_country);
            $dest_country = $country->iso_code;
            $dest_state = $address->id_state;
            $dest_city = $address->city;
            $state = new State($dest_state);
            $id_zone =  $state->id_zone>0?$state->id_zone:$country->id_zone;
        }
        elseif ($cookie_zip || $cookie_country || $cookie_state)
        {
            $this->saveLog('PCCMLog.txt', "\n\r"."\n\r".'1. Entered: elseif ($cookie_zip || $cookie_country || $cookie_state)', $log);
            
            $dest_zip = $cookie_zip;
            $dest_country = $cookie_country;
            $country = new Country($dest_country);
            $dest_country = $country->iso_code;
            $dest_state = $cookie_state;
            $dest_city = $this->context->cookie->pc_dest_city;
            $state = new State($dest_state);
            $id_zone =  $state->id_zone>0?$state->id_zone:$country->id_zone;
        }
        // if the order creation is triggered from an exernal site and there is no customer but cart id was passed
        elseif ($this->context->cart->id_address_delivery > 0 && (!is_object($this->context->customer) || !$this->context->customer->logged))
        {
            $this->saveLog('PCCMLog.txt', "\n\r"."\n\r".'1. Entered: elseif ($this->context->cart->id_address_delivery > 0 && (!is_object($this->context->customer) || !$this->context->customer->logged))', $log);
            
            $address = new Address(intval($this->context->cart->id_address_delivery));
            if (!Validate::isLoadedObject($address))
            {
                $id_address = Address::getFirstCustomerAddressId($this->context->cart->id_customer, true);
                if ($id_address > 0)
                    $address = new Address(intval($id_address));
            }

            if($address)
            {
                $dest_zip = $address->postcode;
                $country = new Country($address->id_country);
                $dest_country = $country->iso_code;
                $dest_state = $address->id_state;
                $dest_city = $address->city;
            }
            else
                return false;
            $state = new State($dest_state);
            $id_zone =  $state->id_zone>0?$state->id_zone:$country->id_zone;
        }
        else
        {
            $this->saveLog('PCCMLog.txt', "\n\r"."\n\r".'1. Failed!', $log);
            return false;
        }

        include_once(_PS_MODULE_DIR_.$this->name.'/classes/RateAvailableServices.php');

        $rateName = $this->getRateName();
        $this->saveLog('PCCMLog.txt', "\n\r".'2. Got "'.$rateName.'" as the module\'s class name (to get it\'s shipping rate)', $log);
        
        $rate = new $rateName();
        $products = $products ? $products : $this->context->cart->getProducts();
        $rate = $rate->getRate((int)$this->id_carrier, $id_zone, $params->getTotalWeight(), $dest_zip, $dest_state, $dest_country, $dest_city, 0, 0, 0, 0, $params, $products);
        $this->saveLog('PCCMLog.txt', "\n\r".'3. getRate function returned: '.$rate, $log);

        $handling = Configuration::get('PS_SHIPPING_HANDLING');
        $carrier = $this->getCarrier($this->id_carrier, $id_zone);
        $this->saveLog('PCCMLog.txt', "\n\r".'4. Returned for carrier: '.print_r($carrier, true), $log);
        
        if ($rate > 0)
            $rate += $this->getExtraShippingCost($carrier, $handling, $products, 0, 0);
        $this->saveLog('PCCMLog.txt', "\n\r".'5. Shipping rate after extra costs (if rate > 0): '.$rate, $log);
        
        $rate = $rate ? Tools::convertPrice($rate, $this->context->cart->id_currency) : $rate;
        $this->saveLog('PCCMLog.txt', "\n\r".'6. Shipping rate after Tools::convertPrice: '.$rate, $log);
        
        return $rate;
    }

	protected function getRateName()
	{
		if($this->name == 'localizedshipping')
			$rateName = 'LSRate';
		elseif($this->name == 'fedex')
			$rateName = 'FedexRate';
		elseif($this->name == 'ups')
			$rateName = 'UPSRate';
		elseif($this->name == 'usps')
			$rateName = 'USPSRate';
		elseif($this->name == 'dhl')
			$rateName = 'DHLRate';
		elseif($this->name == 'auspost')
			$rateName = 'AusPostRate';
		elseif($this->name == 'canadapost')
			$rateName = 'CanadaPostRate';
		elseif($this->name == 'royalmail')
			$rateName = 'RoyalMailRate';

		return $rateName;
	}

	public function is_free_ship_cart($products, $fs_arr)
	{
		$fs_cart = true;

		foreach ($products as $product)
		{
			if(!$this->is_free_ship_product($product['id_product'], $fs_arr))
				$fs_cart = false;
		}

		return $fs_cart;
	}

	public function is_free_ship_product($id_product, $fs_arr)
	{
		if ($fs_arr['free_shipping_product'] != '')
		{
			$id_products = explode(",",$fs_arr['free_shipping_product']);
			if (in_array($id_product, $id_products))
				return true;
		}

		if ($fs_arr['free_shipping_category'] != '')
		{
			$res = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'category_product` WHERE id_product = '.(int)$id_product.' AND id_category IN ('.pSQL($fs_arr['free_shipping_category']).')');
			if (is_array($res) && sizeof($res) > 0)
				return true;
		}

		if ($fs_arr['free_shipping_manufacturer'] != '' || $fs_arr['free_shipping_supplier'] != '')
		{
			$res = Db::getInstance()->getRow('SELECT id_manufacturer, id_supplier FROM `'._DB_PREFIX_.'product` WHERE id_product = '.(int)$id_product);
			$man_arr = explode(",",$fs_arr['free_shipping_manufacturer']);
			if (sizeof($man_arr) > 0 && in_array($res['id_manufacturer'], $man_arr))
				return true;
			$sup_arr = explode(",",$fs_arr['free_shipping_supplier']);
			if (sizeof($sup_arr) > 0 && in_array($res['id_supplier'], $sup_arr))
				return true;
		}

		return false;
	}
	
	protected function getOrderTotal($id_product, $id_product_attribute = NULL, $qty)
	{
		if ($id_product != 0)
		{
			$p = new Product($id_product);
			return $p->getPrice(true, $id_product_attribute, 6, NULL, false, true, $qty) * $qty;
		}
		else
			return $this->context->cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);
	}
	
	protected function checkHookCartShippingPreview()
	{
		$hooked = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'hook` WHERE name = "cartShippingPreview"');
		if (is_array($hooked) && sizeof($hooked) == 0)
			Db::getInstance()->Execute('
				INSERT INTO `'._DB_PREFIX_.'hook`
				(`id_hook` ,`name` ,`title` ,`description` ,`position`)
				VALUES (NULL , "cartShippingPreview", "Cart Shipping Preview", "Shipping rates preview in block cart", "1");
			');
	}

	public function hookUpdateCarrier($params)
	{
		$old_id = $params['id_carrier'];
		$new_id = $params['carrier']->id;

		$squery = 'SELECT * FROM `'._DB_PREFIX_.'fe_'.$this->name.'_method` WHERE id_carrier = '.(int)$old_id;
		$result = Db::getInstance()->executeS($squery);
		if (sizeof($result) == 1)
		{
			$query  = 'INSERT INTO `'._DB_PREFIX_.'fe_'.$this->name.'_method` (id_carrier, method, '.($this->name == 'usps' ? 'service,' : '').' free_shipping, free_shipping_product, free_shipping_category, free_shipping_manufacturer, free_shipping_supplier, extra_shipping_type, extra_shipping_amount, insurance_minimum, insurance_type, insurance_amount'.($this->name == 'auspost'?',without_gst':'').') VALUES
				("'.(int)$new_id.'","'.$result[0]['method'].'", '.($this->name == 'usps' ? '"'.$result[0]['service'].'",' : '').' "'.$result[0]['free_shipping'].'","'.$result[0]['free_shipping_product'].'",
				"'.$result[0]['free_shipping_category'].'","'.$result[0]['free_shipping_manufacturer'].'","'.$result[0]['free_shipping_supplier'].'",
				"'.$result[0]['extra_shipping_type'].'","'.$result[0]['extra_shipping_amount'].'",
				"'.$result[0]['insurance_minimum'].'","'.$result[0]['insurance_type'].'",
				"'.$result[0]['insurance_amount'].'"'.($this->name == 'auspost'?',"'.$result[0]['without_gst'].'"':'').')';
			Db::getInstance()->Execute($query);
			Configuration::updateValue(''.strtoupper($this->name).'_RELOAD_CARRIERS','1');
		}
	}

	public function hookProductActions($params, $is_cart = '')
	{
		if (!is_object($this->context->cookie))
			return;
            
        $default_country = new Country(Configuration::get('PS_COUNTRY_DEFAULT'));

		$cookie_zip = $this->context->cookie->postcode ? $this->context->cookie->postcode : $this->context->cookie->pc_dest_zip;

		$this->context->smarty->assign(array(
			'this_'.$this->name.'_path' => __PS_BASE_URI__.'modules/'.$this->name.'/',
			$this->name.'_countries' => Country::getCountries($this->context->language->id, true),
			$this->name.'_default_country' => $default_country->iso_code,
			$this->name.'_get_rates' => ($this->context->cart->id_address_delivery || strlen($cookie_zip) ? 1 : 0),
			$this->name.'_address_display' => $this->{'_'.$this->name.'_address_display'},
			$this->name.'_is_cart' => $is_cart,
			'psVersion' => $this->getPSV()
			)
		);
		return ($this->display($this->name, $this->name.'-preview.tpl'));
	}

	public function hookAjaxPreview($rates, $dest_zip, $dest_state = "", $dest_country, $is_downloadable, $is_cart, $dest_city = "")
	{
		$State = new State($dest_state);
        
        $default_country = new Country(Configuration::get('PS_COUNTRY_DEFAULT'));
		
		$this->context->smarty->assign(array(
			'this_'.$this->name.'_path' => __PS_BASE_URI__.'modules/'.$this->name.'/',
			$this->name.'_product_rates' => $rates,
			$this->name.'_countries' => Country::getCountries($this->context->language->id, true),
			$this->name.'_default_country' => $default_country->iso_code,
			$this->name.'_dest_zip' => $dest_zip,
			$this->name.'_dest_state' => $dest_state,
			$this->name.'_dest_state_name' => $State->iso_code,
			$this->name.'_dest_country' => $dest_country,
			$this->name.'_dest_city' => $dest_city ? $dest_city : "",
			$this->name.'_is_cart' => $is_cart,
			$this->name.'_cart_products' => $this->context->cart->nbProducts(),
			$this->name.'_address_display' => $this->{'_'.$this->name.'_address_display'},
			'ps_carrier_default' => $this->context->cart->id_carrier,
			'is_downloadable' => $is_downloadable,
			'psVersion' => $this->getPSV()
		));

		return $this->display($this->name, $this->name.'-preview-json.tpl');
	}

	protected function get_fit_boxes($products_size, $available_boxes)
	{
		if (!class_exists('Boxing',false))
			include_once("Boxing.php");
		$box = new Boxing();
		$pack_dim = array();
		foreach ($products_size AS $ps)
			$box->add_inner_box($ps['w'],$ps['h'], $ps['d'], $ps['weight']);
		// Add larger boxes if needed to fit a product... //
		foreach ($box->inner_boxes as $ib)
			$inner_boxes_master[] = $ib;
		$elp = array();
		foreach ($box->inner_boxes as $inner_box)
		{
			$pd = $inner_box['dimensions'];
			// Do we need to add a custom box because the ones entered are too small.
			$add_new_box = true;
			foreach ($available_boxes as $box_size)
			{
				$lb = $box_size;
				$lb_weight = $lb[3];
				unset($lb[3]);
				if (isset($lb[4]))
				{
					unset($lb[4]);
					unset($lb[5]);
					unset($lb[6]);
					unset($lb[7]);
					unset($lb[8]);
				}
				rsort($lb);
				if ($pd[0] <= $lb[0] && $pd[1] <= $lb[1] && $pd[2] <= $lb[2] && $inner_box['weight'] <= $lb_weight)
				{
					$add_new_box = false;
					break;
				}
			}
			if ($add_new_box)
			{
				$elp[] = array($pd[0], $pd[1], $pd[2], $inner_box['weight']);
				$box->add_outer_box($pd[0], $pd[1], $pd[2], $inner_box['weight']);
			}
		}
		$bs = $available_boxes;
		if (sizeof($box->outer_boxes) == 0)
			$box->add_outer_box($bs[0][0], $bs[0][1], $bs[0][2], $bs[0][3]);
		$i = 0;
		$n = 0;
		$k = 1;
		$bc = sizeof($available_boxes);
		$stt = microtime();
		$bsc = $this->getBoxesCombos($k, $inner_boxes_master, $available_boxes);
		while (!$box->fits())
		{
			if ($k > sizeof($box->inner_boxes))
			{
				$i = -1;
				break;
			}
			if ($n < sizeof($bsc))
			{
				$box->outer_boxes = array();
				$box->inner_boxes = $inner_boxes_master;
				for ($p = 0 ; $p < sizeof($elp) ; $p++)
					$box->add_outer_box($elp[$p][0], $elp[$p][1], $elp[$p][2], $elp[$p][3]);
				for ($p = 0 ; $p < $k ; $p++)
					$box->add_outer_box($bsc[$n][$p][0], $bsc[$n][$p][1], $bsc[$n][$p][2], $bsc[$n][$p][3]);
				$n++;
			}
			else
			{
				$mid = ceil((sizeof($inner_boxes_master) + $k) /2);
				if ($mid != sizeof($inner_boxes_master) && !$this->fast_fit_check($box, 0, 0, $mid, $inner_boxes_master, $available_boxes, $elp))
					$k = $mid;
				// Make sure k is not bigger than it should be
				$k = min($k + 1 ,sizeof($inner_boxes_master));
//				$k++;
				$mt = microtime();
				if ($k < 8)
					$bsc = $this->getBoxesCombos($k, $inner_boxes_master, $available_boxes);
				else
				{
					$last_box = $bsc[sizeof($bsc)-1][sizeof($bsc[sizeof($bsc)-1])-1];
					while (sizeof($bsc[0]) < $k)
					foreach ($bsc as $key => $val)
						array_push($bsc[$key], $last_box);
				}
				$n = 0;
			}
		}
		if ($i >= 0)
			foreach ($box->outer_boxes as $outer_box_id => $outer_box)
				if (!isset($outer_box["parent"]) || $outer_box["parent"] == $outer_box_id)
				{
					$pack_dim[] = array('weight' => $outer_box["used_weight"], 'w' => $outer_box['dimensions'][0],
						'h' => $outer_box['dimensions'][1], 'd' => $outer_box['dimensions'][2]);
				}
		return $pack_dim;
	}

	protected function fast_fit_check($box, $i, $n, $k, $inner_boxes_master, $available_boxes, $elp)
	{
		$bsc = $this->getBoxesCombos($k, $inner_boxes_master, $available_boxes);
		if ($k > sizeof($box->inner_boxes))
		{
			$i = -1;
			return false;
		}
		if ($n < sizeof($bsc))
		{
			$box->outer_boxes = array();
			$box->inner_boxes = $inner_boxes_master;
			for ($p = 0 ; $p < sizeof($elp) ; $p++)
				$box->add_outer_box($elp[$p][0], $elp[$p][1], $elp[$p][2], $elp[$p][3]);
			for ($p = 0 ; $p < $k ; $p++)
				$box->add_outer_box($bsc[$n][$p][0], $bsc[$n][$p][1], $bsc[$n][$p][2], $bsc[$n][$p][3]);
			$n++;
		}
		$ret = $box->fits();
		$box->outer_boxes = array();
		$box->inner_boxes = $inner_boxes_master;
		return $ret;
	}

	protected function sort_size($arr)
	{
		$tmp = array();
		foreach ($arr as $key => $val)
			$arr[$key] = (float)$val;
		arsort($arr);
		foreach ($arr as $key => $val)
			array_push($tmp,$val);
		return $tmp;
	}

	protected function getBoxesCombos($n, $inner = array(), $available_boxes)
	{
		if (!function_exists('generate_box_combos'))
			include_once ('BoxCombo.php');

		$keys = array();
		$trimmed = array();
		foreach($available_boxes as $sizes)
		{
			$key = "";
			foreach ($sizes as $size)
			{
				$key .= $size."_";
			}
			$keys[$key] = $sizes;
		}

		foreach ($inner as $in_box)
		{
			$in_box['dimensions'] = $this->sort_size($in_box['dimensions']);
			$in = $in_box['dimensions'];
			foreach ($keys as $key => $obox)
			{
				// product too heavy //
				if ($in_box['weight'] > $obox[3])
				{
					continue;
				}
				$to = $obox;
				unset($to[3]);
				if (isset($lb[4]))
				{
					unset($lb[4]);
					unset($lb[5]);
					unset($lb[6]);
					unset($lb[7]);
					unset($lb[8]);
				}
				$to = $this->sort_size($to);
				if ($in[0] > $to[0] || $in[1] > $to[1] || $in[2] > $to[2])
					continue;
				$trimmed[$key] = $obox;
			}
		}
		$fits = array();
		foreach ($trimmed as $key)
			array_push($fits, $key);
		$ret = generate_box_combos($fits, $n);

		return $ret;
	}

	function generateBoxesCombos($arr, &$ret, &$codes, $pos)
	{
		if(count($arr))
			for($i=0; $i<count($arr[0]); $i++)
			 {
				$tmp = $arr;
				$codes[$pos] = $arr[0][$i];
				$tarr = array_shift($tmp);
				$pos++;
				$pos = $this->generateBoxesCombos($tmp, $ret, $codes, $pos);

			}
		else
		{
			$skip = false;
			foreach ($ret as $key)
			{
				$tc = $codes;
				foreach ($key as $val)
					foreach($tc as $ck => $cv)
						if ($cv == $val)
						{
							unset($tc[$ck]);
							break;
						}
				if (!is_array($tc) || sizeof($tc) == 0)
				{
					$skip = true;
					break;
				}
			}
			if (!$skip)
				$ret[] = $codes;
		}
		$pos--;
		return $pos;
	}

	public function convertDimensionToIn($dimension)
	{
		$dimensionUnit = strtolower(Configuration::get('PS_DIMENSION_UNIT'));
		if ($dimensionUnit == 'cm')
			$totalDimensionConverted = round(0.393701 * $dimension, 3);
		else
			$totalDimensionConverted = $dimension;

		return max(round($totalDimensionConverted, 3), 0.001);
	}
	
	public function convertDimensionToCm($dimension)
	{
		$dimensionUnit = strtolower(Configuration::get('PS_DIMENSION_UNIT'));
		if ($dimensionUnit == 'in')
			$totalDimensionConverted = round(2.54 * $dimension, 3);
		else
			$totalDimensionConverted = $dimension;

		return max(round($totalDimensionConverted, 3), 0.001);
	}

	public function getWeightInLb($totalWeight)
	{
		$weightUnit = strtolower(Configuration::get('PS_WEIGHT_UNIT'));
		
		if ($weightUnit == 'kg')
			$totalWeightConverted = round(2.204 * $totalWeight,3);
		elseif ($weightUnit == 'oz')
			$totalWeightConverted = round($totalWeight / 16,3);
		elseif ($weightUnit == 'g')
			$totalWeightConverted = round($totalWeight / 453.592,3);
		else
			$totalWeightConverted = $totalWeight;

		return max(round($totalWeightConverted, 3), 0.001);
	}

	public function getWeightInKg($totalWeight)
	{
		$weightUnit = strtolower(Configuration::get('PS_WEIGHT_UNIT'));
		
		if ($weightUnit == 'lb' || $weightUnit == 'lbs')
			$totalWeightConverted = round($totalWeight / 2.204,3);
		elseif ($weightUnit == 'oz')
			$totalWeightConverted = round($totalWeight / 35.273,3);
		elseif ($weightUnit == 'g')
			$totalWeightConverted = round($totalWeight / 1000,3);
		else
			$totalWeightConverted = $totalWeight;
			
		return max(round($totalWeightConverted, 3), 0.001);
	}
	
	public function saveLog($filename, $data, $save = false)
	{
		$atAllCosts = false;
		if($save OR $atAllCosts)
		{
			$myFile = _PS_MODULE_DIR_.$this->name."/logs/".$filename;
			$fh = fopen($myFile, 'a') or die("can't open file");
			fwrite($fh, $data);
			fclose($fh);
		}
	}

	public function getCarrier($id_carrier, $id_zone)
	{
		if($this->name == 'localizedshipping')
			$name = 'shp';
		else
			$name = $this->name;

		$query = 'SELECT * FROM
			`'._DB_PREFIX_.'carrier` c,`'._DB_PREFIX_.'carrier_zone` cz, `'._DB_PREFIX_.'fe_'.pSQL($name).'_method` ffm
			WHERE
			c.id_carrier = ffm.id_carrier AND
			ffm.id_carrier = "'.(int)$id_carrier.'" AND
			c.id_carrier = cz.id_carrier AND cz.id_zone = '.(int)$id_zone.' LIMIT 1';
		$carriers = Db::getInstance()->executeS($query);

		if (is_array($carriers) && sizeof($carriers) != "1" || !sizeof($carriers))
			return false;
		else
			return $carriers[0];
	}

    public function getCarriers($id_zone = NULL, $cart = NULL, $is_cart = false, $product = NULL)
    {         
        if($this->getPSV() >= 1.5)
        {
            /** $this->context->shop is not accurate */
            $id_shop_group = Shop::getContextShopGroupID(true);
            $id_shop = Shop::getContextShopID(true);
            
            /** if shop group is selected */
            if(!$id_shop && $id_shop_group)
                $shops = Shop::getShops(false, $id_shop_group, true);
            /** if all shops is selected */
            elseif(!$id_shop && !$id_shop_group)
                $shops = Shop::getShops(false, null, true);
            else
                $shops[$id_shop] = $id_shop;
        }
        
        // for compatibility
        if($this->name == 'localizedshipping')
            $name = 'shp';
        else
            $name = $this->name;

        $cart_carriers = $this->getProductCarriers($cart, $is_cart, $product);

        $query = '
            SELECT *
            FROM `'._DB_PREFIX_.'carrier` c, `'._DB_PREFIX_.'carrier_zone` cz,`'._DB_PREFIX_.'fe_'.$name.'_method` ffm '.($this->getPSV() >= 1.5 ? ',`'._DB_PREFIX_.'carrier_shop` cs' : '').'
            WHERE c.active = 1 AND c.deleted = 0 AND c.id_carrier = ffm.id_carrier AND c.id_carrier = cz.id_carrier'
            .($id_zone ? ' AND cz.id_zone = "'.(int)$id_zone.'"' : '')
            .(sizeof($cart_carriers) ? ' AND c.id_carrier IN ('.implode(',', $cart_carriers).')' : '')
            .($this->getPSV() >= 1.5 ? ' AND c.id_carrier = cs.id_carrier AND cs.`id_shop` IN ('.implode(',', $shops).')' : '')
            .' GROUP BY c.`id_carrier`'
        ;
        $result = Db::getInstance()->executeS($query);     

        return $result;
    }

	public function curl_post($post_url, $post_string)
	{
		$request = curl_init($post_url); // initiate curl object
		curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
		curl_setopt($request, CURLOPT_POSTFIELDS, $post_string); // use HTTP POST to send form data
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response.
		$post_response = curl_exec($request); // execute curl post and store results in $post_response
		// 	additional options may be required depending upon your server configuration
		// 	you can find documentation on curl options at http://www.php.net/curl_setopt
		
		/** IF curl_exec RETURNS ERROR */
		if($post_response === false)
			$post_response = curl_error($request);
		/***/
		
		curl_close ($request); // close curl object

		return $post_response;
	}
	
	function multiRequest($data, $url, $header = array(), $userPwd = null, $certificate = null, $verifyPeer = false, $verifyHost = 2)
	{
		// array of curl handles
		$curly = array();
		// data to be returned
		$result = array();

		// multi handle
		$mh = curl_multi_init();

		// loop through $data and create curl handles
		// then add them to the multi-handle		
		foreach ($data as $id => $d)
		{					
			$curly[$id] = curl_init();
				
			if(!is_null($userPwd))
			{
				curl_setopt($curly[$id], CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
				curl_setopt($curly[$id], CURLOPT_USERPWD, $userPwd);                    
			}
			
			if(!is_null($certificate))
				curl_setopt($curly[$id], CURLOPT_CAINFO, $certificate); // Signer Certificate in PEM format
				
			if(!is_null($url))
			{
				curl_setopt($curly[$id], CURLOPT_POST, true);
				curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d);
			}
			  
			curl_setopt($curly[$id], CURLOPT_HEADER, 0);
			curl_setopt($curly[$id], CURLOPT_HTTPHEADER, $header);
			curl_setopt($curly[$id], CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curly[$id], CURLOPT_SSL_VERIFYHOST, 2);			
			curl_setopt($curly[$id], CURLOPT_URL, (is_null($url) ? $d : $url));
			curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)

			curl_multi_add_handle($mh, $curly[$id]);
		}

		// execute the handles
		$running = null;
		do {
			curl_multi_exec($mh, $running);
		} while ($running > 0);

		// get content and remove handles
		foreach ($curly as $id => $c) {
			$result[$id] = curl_multi_getcontent($c);
			curl_multi_remove_handle($mh, $c);
			
			if(!$result[$id])
				$result[$id] = $this->curl_post((is_null($url) ? $d : $url), (!is_null($url) ? $data[$id] : null));
		}

		// all done
		curl_multi_close($mh);

		return $result;
	}

	function array2object($array) {
		if (is_array($array))
		{
			$obj = new StdClass();

			foreach ($array as $key => $val)
			{
				$obj->$key = $val;
			}
		}
		else
		{
			$obj = $array;
		}

		return $obj;
	}

	public function getPreviewAddress($log = false)
	{
		// for compatibility
		/** NO LONGER NECESSARY */
		/*
		if($this->name == 'localizedshipping')
			$name = 'shp';
		else
		*/
		$name = $this->name;

		if($this->context->cookie->postcode && $this->context->cookie->id_country && $this->context->cookie->id_state)
			$cookie_state = $this->context->cookie->id_state;
		else
			$cookie_state = $this->context->cookie->pc_dest_state;
		$cookie_zip = $this->context->cookie->postcode ? $this->context->cookie->postcode :$this->context->cookie->pc_dest_zip;
		$cookie_country = $this->context->cookie->id_country ? $this->context->cookie->id_country :$this->context->cookie->pc_dest_country;

		if ($this->context->customer->logged)
		{
			if (
				(isset($_POST[$name.'_dest_zip']) && $_POST[$name.'_dest_zip'] != "")
			||  (isset($_POST[$name.'_dest_country']) && $_POST[$name.'_dest_country'] != "")
			||	(isset($_POST[$name.'_dest_state']) && $_POST[$name.'_dest_state'] != "")
			){
				$dest_zip = Tools::getValue($name.'_dest_zip');
				$dest_state = Tools::getValue($name.'_dest_state');
				$dest_country = Tools::getValue($name.'_dest_country');
				$dest_city = Tools::getValue($name.'_dest_city') != $this->l('City') ? Tools::getValue($name.'_dest_city') : "";
				if ((int)$this->context->cart->id_address_delivery > 0)
				{
					$address = new Address(intval($this->context->cart->id_address_delivery));
					$address->postcode = $dest_zip;
					$address->id_country = $dest_country;
					$address->city = ($dest_city AND strlen($dest_city)) ? $dest_city : 0;
					if ((int)$dest_state > 0)
						$address->id_state = $dest_state;
					$country = new Country($dest_country);
					if (!$address->id_state && $country->contains_states)
					{
						$id_state = 0;
						$states = State::getStates($this->context->language->id, true);
						foreach ($states as $state)
						{
							$id_state = $state['id_state'];
						}
						$address->id_state = $id_state;
					}
					else if ($address->id_state && !$country->contains_states)
						$address->id_state = 0;
					$override_address_name = '_'.$name.'_override_address';
					if ($this->{$override_address_name})
						$address->update();
				}
				$state = new State($dest_state);
				$country = new Country($dest_country);
				$id_zone =  $state->id_zone>0?$state->id_zone:$country->id_zone;
				$dest_country = $country->iso_code;
			}
			else
			{
				if((int)$this->context->cart->id_address_delivery > 0)
				{
					$address = new Address(intval($this->context->cart->id_address_delivery));
					$dest_zip = $address->postcode;
					$country = new Country($address->id_country);
					$dest_country = $country->iso_code;
					$dest_state = $address->id_state;
					$dest_city = $address->city;
					$state = new State($dest_state);
					$id_zone =  $state->id_zone>0?$state->id_zone:$country->id_zone;
				}
				else
				{
					$dest_zip = $cookie_zip;
					$dest_state = $cookie_state;
					$dest_country = $cookie_country;
					$dest_city = $this->context->cookie->pc_dest_city;
					$country = new Country($dest_country);
					$state = new State($dest_state);
					$id_zone =  $state->id_zone>0?$state->id_zone:$country->id_zone;
					$dest_country = $country->iso_code;
				}
			}
		}
		else
		{
			/** IF CARRIER IS NOT ROYAL MAIL CARRIER */
			if($this->name != 'royalmail')
			{
				if (
					(isset($_POST[$name.'_dest_zip']) && $_POST[$name.'_dest_zip'])
				||  (isset($_POST[$name.'_dest_country']) && $_POST[$name.'_dest_country'])
				||  (isset($_POST[$name.'_dest_state']) && $_POST[$name.'_dest_state'])
				){
					$dest_zip = Tools::getValue($name.'_dest_zip');
					$dest_state = Tools::getValue($name.'_dest_state');
					$dest_country = Tools::getValue($name.'_dest_country');
					$dest_city = (isset($_POST[$name.'_dest_city']) && $_POST[$name.'_dest_city'] != $this->l('City')) ? $_POST[$name.'_dest_city'] : "";
				}
				else
				{
					$dest_zip = $cookie_zip;
					$dest_state = $cookie_state;
					$dest_country = $cookie_country;
					$dest_city = $this->context->cookie->pc_dest_city;
				}
			}
			else
			{
				if (isset($_POST[$name.'_dest_country']) && $_POST[$name.'_dest_country'])
				{
					$dest_zip = Tools::getValue($name.'_dest_zip');
					$dest_state = Tools::getValue($name.'_dest_state');
					$dest_country = Tools::getValue($name.'_dest_country');
					$dest_city = (isset($_POST[$name.'_dest_city']) && $_POST[$name.'_dest_city'] != $this->l('City')) ? $_POST[$name.'_dest_city'] : "";
				}
				else
				{
					$dest_zip = $cookie_zip;
					$dest_state = $cookie_state;
					$dest_country = $cookie_country;
					$dest_city = $this->context->cookie->pc_dest_city;
				}
			}
			$country = new Country($dest_country);
			$state = new State($dest_state);
			$id_zone =  $state->id_zone>0?$state->id_zone:$country->id_zone;
			$dest_country = $country->iso_code;
		}

		$this->context->cookie->pc_dest_zip = strlen($dest_zip) ? $dest_zip : $cookie_zip;
		$this->context->cookie->pc_dest_state = strlen($dest_state) ? $dest_state : $cookie_state;
		if(strlen($dest_country))
		{
			$id_country = Country::getByIso($dest_country);
			$this->context->cookie->pc_dest_country = $id_country ? $id_country : $cookie_country;
		}
		$this->context->cookie->pc_dest_city = strlen($dest_city) ? $dest_city : $this->context->cookie->pc_dest_city;
		$this->context->cookie->write();

		return array('dest_zip' => $dest_zip, 'dest_state' => $dest_state, 'dest_country' => $dest_country, 'dest_city' => $dest_city, 'id_zone' => $id_zone);
	}

	/*
	* Get Product specific additional shipping cost + handling fee.
	*/
	public function getExtraShippingCost($carrier, $handling, $products, $product, $qty)
	{
		$shipping_cost = 0;
		// Adding handling charges
		if ($handling > 0 && $carrier['shipping_handling'])
			$shipping_cost += $handling;

		// Additional Shipping Cost per product
		if (sizeof($products) == 0)
		{
			if (!property_exists($product, 'is_virtual') || !$product->is_virtual)
				$shipping_cost += $product->additional_shipping_cost * $qty;
		}
		else
		{
			foreach ($products as $product)
				if (!isset($product['is_virtual']) || !$product['is_virtual'])
					$shipping_cost += $product['additional_shipping_cost'] * $product['cart_quantity'];
		}
		return $shipping_cost;
	}

	public function getFedexRates($id_zone, $is_cart, $cart, $product_weight, $dest_zip, $dest_state, $dest_country, $currency, $product, $id_product_attribute, $qty)
	{
		$rates = array();
		if (file_exists(dirname(__FILE__).'/../../fedex/classes/RateAvailableServices.php'))
		{
			include_once(dirname(__FILE__).'/../../fedex/fedex.php');
			include_once(dirname(__FILE__).'/../../fedex/classes/RateAvailableServices.php');
			$fedex = new Fedex();
			if ($fedex->active)
			{
				// Get handling cost (once)
				$handling = Configuration::get('PS_SHIPPING_HANDLING');
				$carriers = $fedex->getCarriers($id_zone, $cart, $is_cart, $product);
				foreach ($carriers as $carrier)
				{
					//check if Fedex module is new or old
					if(get_parent_class($fedex) == 'PrestoChangeoCarrierModule')
					{
						$fedexRate = new FedexRate();
						$amount = $fedexRate->getRate($carrier['id_carrier'], $carrier['id_zone'], $is_cart != ''?$cart->getTotalWeight():max($product_weight,0.001), $dest_zip, $dest_state, $dest_country, "", $is_cart != ''?0:$product->getPrice(true, $id_product_attribute, 6, NULL, false, true, $qty), $is_cart != ''?0:$product->id, $is_cart != ''?0:$id_product_attribute, $qty);
					}
					else
						$amount = getFedexRate($carrier['id_carrier'], $carrier['id_zone'], $is_cart != ''?$cart->getTotalWeight():max($product_weight,0.001), $dest_zip, $dest_state, $dest_country, $is_cart != ''?0:$product->getPrice(true, $id_product_attribute, 6, NULL, false, true, $qty), $is_cart != ''?0:$product->id, $is_cart != ''?0:$id_product_attribute, $qty);
					// Add product spercific cost + handling fee
					if ($amount > 0)
						$amount += $this->getExtraShippingCost($carrier, $handling, $is_cart != ''?$cart->getProducts():array(), $product, $qty);
					// Apply shipping tax if needed
					if (!Tax::excludeTaxeOption())
						$carrierTax = Tax::getCarrierTaxRate($carrier['id_carrier']);
					if (isset($carrierTax) && $amount !== false)
						$amount *= 1 + ($carrierTax / 100);
					$amount = $amount === false?-1:Tools::convertPrice($amount, $currency);
					if ($amount > 0)
						$rates[$carrier['name']] = array(Tools::displayPrice($amount, $currency, false), $carrier['id_carrier']);
					elseif ($amount !== false && $amount == 0)
						$rates[$carrier['name']] = array($fedex->l('Free!'), $carrier['id_carrier']);
				}
			}
		}

		return $rates;
	}

	public function getUSPSRates($id_zone, $is_cart, $cart, $product_weight, $dest_zip, $dest_state, $dest_country, $currency, $product, $id_product_attribute, $qty)
	{
		$rates = array();
		if (file_exists(dirname(__FILE__).'/../../usps/classes/RateAvailableServices.php'))
		{
			include_once(dirname(__FILE__).'/../../usps/usps.php');
			include_once(dirname(__FILE__).'/../../usps/classes/RateAvailableServices.php');
			$usps = new USPS();
			if ($usps->active)
			{
				// Get handling cost (once)
				$handling = Configuration::get('PS_SHIPPING_HANDLING');
				$carriers = $usps->getCarriers($id_zone, $cart, $is_cart, $product);
				foreach ($carriers as $carrier)
				{
					//check if USPS module is new or old
					if(get_parent_class($usps) == 'PrestoChangeoCarrierModule')
					{
						$USPSRate = new USPSRate();
						$amount = $USPSRate->getRate($carrier['id_carrier'], $carrier['id_zone'], $is_cart != ''?$cart->getTotalWeight():max($product_weight,0.001), $dest_zip, $dest_state, $dest_country, "", $is_cart != ''?0:$product->getPrice(true, $id_product_attribute, 6, NULL, false, true, $qty), $is_cart != ''?0:$product->id, $is_cart != ''?0:$id_product_attribute, $qty);
					}
					else
						$amount = getUSPSRate($carrier['id_carrier'], $carrier['id_zone'], $is_cart != ''?$cart->getTotalWeight():max($product_weight,0.001), $dest_zip, $dest_state, $dest_country, "", $is_cart != ''?0:$product->getPrice(true, $id_product_attribute, 6, NULL, false, true, $qty), $is_cart != ''?0:$product->id, $is_cart != ''?0:$id_product_attribute, $qty);
					// Add product spercific cost + handling fee
					if ($amount > 0)
						$amount += $this->getExtraShippingCost($carrier, $handling, $is_cart != ''?$cart->getProducts():array(), $product, $qty);
					// Apply shipping tax if needed
					if (!Tax::excludeTaxeOption())
						$carrierTax = Tax::getCarrierTaxRate($carrier['id_carrier']);
					if (isset($carrierTax) && $amount !== false)
						$amount *= 1 + ($carrierTax / 100);
					$amount = $amount === false?-1:Tools::convertPrice($amount, $currency);
					if ($amount > 0)
						$rates[$carrier['name']] = array(Tools::displayPrice($amount, $currency, false), $carrier['id_carrier']);
					elseif ($amount !== false && $amount == 0)
						$rates[$carrier['name']] = array($usps->l('Free!'), $carrier['id_carrier']);
				}
			}
		}

		return $rates;
	}

	public function getUPSRates($id_zone, $is_cart, $cart, $product_weight, $dest_zip, $dest_state, $dest_country, $currency, $product, $id_product_attribute, $qty)
	{
		$rates = array();
		if (file_exists(dirname(__FILE__).'/../../ups/classes/RateAvailableServices.php'))
		{
			include_once(dirname(__FILE__).'/../../ups/ups.php');
			include_once(dirname(__FILE__).'/../../ups/classes/RateAvailableServices.php');
			$ups = new UPS();
			if ($ups->active)
			{
				// Get handling cost (once)
				$handling = Configuration::get('PS_SHIPPING_HANDLING');
				$carriers = $ups->getCarriers($id_zone, $cart, $is_cart, $product);
				foreach ($carriers as $carrier)
				{
					//check if UPS module is new or old
					if(get_parent_class($ups) == 'PrestoChangeoCarrierModule')
					{
						$UPSRate = new UPSRate();
						$amount = $UPSRate->getRate($carrier['id_carrier'], $carrier['id_zone'], $is_cart != ''?$cart->getTotalWeight():max($product_weight,0.001), $dest_zip, $dest_state, $dest_country, "", $is_cart != ''?0:$product->getPrice(true, $id_product_attribute, 6, NULL, false, true, $qty), $is_cart != ''?0:$product->id, $is_cart != ''?0:$id_product_attribute, $qty);
					}
					else
						$amount = getUPSRate($carrier['id_carrier'], $carrier['id_zone'], $is_cart != ''?$cart->getTotalWeight():max($product_weight,0.001), $dest_zip, $dest_state, $dest_country, $is_cart != ''?0:$product->getPrice(true, $id_product_attribute, 6, NULL, false, true, $qty), $is_cart != ''?0:$product->id, $is_cart != ''?0:$id_product_attribute, $qty);
					// Add product spercific cost + handling fee
					if ($amount > 0)
						$amount += $this->getExtraShippingCost($carrier, $handling, $is_cart != ''?$cart->getProducts():array(), $product, $qty);
					// Apply shipping tax if needed
					if (!Tax::excludeTaxeOption())
						$carrierTax = Tax::getCarrierTaxRate($carrier['id_carrier']);
					if (isset($carrierTax) && $amount !== false)
						$amount *= 1 + ($carrierTax / 100);
					$amount = $amount === false?-1:Tools::convertPrice($amount, $currency);
					if ($amount > 0)
						$rates[$carrier['name']] = array(Tools::displayPrice($amount, $currency, false), $carrier['id_carrier']);
					elseif ($amount !== false && $amount == 0)
						$rates[$carrier['name']] = array($ups->l('Free!'), $carrier['id_carrier']);
				}
			}
		}

		return $rates;
	}

	public function getDHLRates($id_zone, $is_cart, $cart, $product_weight, $dest_zip, $dest_state, $dest_country, $currency, $product, $id_product_attribute, $qty, $dest_city)
	{
		$rates = array();
		if (file_exists(dirname(__FILE__).'/../../dhl/classes/RateAvailableServices.php'))
		{
			include_once(dirname(__FILE__).'/../../dhl/dhl.php');
			include_once(dirname(__FILE__).'/../../dhl/classes/RateAvailableServices.php');
			$dhl = new DHL();
			if ($dhl->active)
			{
				// Get handling cost (once)
				$handling = Configuration::get('PS_SHIPPING_HANDLING');
				$carriers = $dhl->getCarriers($id_zone, $cart, $is_cart, $product);

				$dhl_rate = new DHLRate();
				foreach ($carriers as $carrier)
				{
					$amount = $dhl_rate->getRate($carrier['id_carrier'], $carrier['id_zone'], $is_cart != ''?$cart->getTotalWeight():max($product_weight,0.001), $dest_zip, "", $dest_country, $dest_city, $is_cart != ''?0:$product->getPrice(true, $id_product_attribute, 6, NULL, false, true, $qty), $is_cart != ''?0:$product->id, $is_cart != ''?0:$id_product_attribute, $qty);
					// Add product spercific cost + handling fee
					if ($amount > 0)
						$amount += $this->getExtraShippingCost($carrier, $handling, $is_cart != ''?$cart->getProducts():array(), $product, $qty);
					// Apply shipping tax if needed
					if (!Tax::excludeTaxeOption())
						$carrierTax = Tax::getCarrierTaxRate($carrier['id_carrier']);
					if (isset($carrierTax) && $amount !== false)
						$amount *= 1 + ($carrierTax / 100);

					$amount = $amount === false?-1:Tools::convertPrice($amount, $currency);
					if ($amount > 0)
						$rates[$carrier['name']] = array(Tools::displayPrice($amount, $currency, false), $carrier['id_carrier']);
					elseif ($amount !== false && $amount == 0)
						$rates[$carrier['name']] = array($dhl->l('Free!'), $carrier['id_carrier']);
				}

			}
		}

		return $rates;
	}

	public function getLSRates($id_zone, $is_cart, $cart, $product_weight, $dest_zip, $dest_state, $dest_country, $currency, $product, $id_product_attribute, $qty, $dest_city)
	{
		$rates = array();
		if (file_exists(dirname(__FILE__).'/../../localizedshipping/classes/RateAvailableServices.php'))
		{
			include_once(dirname(__FILE__).'/../../localizedshipping/localizedshipping.php');
			include_once(dirname(__FILE__).'/../../localizedshipping/classes/RateAvailableServices.php');
			$ls = new LocalizedShipping();
			if ($ls->active)
			{
				$handling = Configuration::get('PS_SHIPPING_HANDLING');
				$carriers = $ls->getCarriers($id_zone, $cart, $is_cart, $product);
				foreach ($carriers as $carrier)
				{
					if(get_parent_class($ls) == 'PrestoChangeoCarrierModule')
					{
						$ls_rate = new LSRate();
						$amount = $ls_rate->getRate($carrier['id_carrier'], $carrier['id_zone'], $is_cart != ''?$cart->getTotalWeight():max($product_weight,0.001), $dest_zip, $dest_state, $dest_country, $dest_city, ($is_cart != '' ? 0 : $product->getPrice(false, $id_product_attribute, 6, NULL, false, true, $qty)), $is_cart != ''?0:$product->id, $is_cart != ''?0:$id_product_attribute, $qty);
					}
					else
						$amount = $ls->getRate($cart, $carrier['id_carrier'], $carrier['id_zone'], $is_cart != ''?$cart->getTotalWeight():max($product_weight,0.001), $dest_zip, $dest_state, $dest_country, $dest_city, ($is_cart != '' ? 0 : $product->getPrice(false, $id_product_attribute, 6, NULL, false, true, $qty)), $is_cart != ''?0:$product->id, $is_cart != ''?0:$id_product_attribute, $qty);
					// Add product spercific cost + handling fee
					if ($amount > 0)
						$amount += $this->getExtraShippingCost($carrier, $handling, $is_cart != ''?$cart->getProducts():array(), $product, $qty);
					// Apply shipping tax if needed
					if (!Tax::excludeTaxeOption())
						$carrierTax = Tax::getCarrierTaxRate($carrier['id_carrier']);
					if (isset($carrierTax) && $amount !== false)
						$amount *= 1 + ($carrierTax / 100);

					$amount = $amount === false?-1:Tools::convertPrice($amount, $currency);
					if ($amount > 0)
						$rates[$carrier['name']] = array(Tools::displayPrice($amount, $currency, false), $carrier['id_carrier']);
					elseif ($amount !== false && $amount == 0)
						$rates[$carrier['name']] = array($ls->l('Free!'), $carrier['id_carrier']);
				}

			}
		}

		return $rates;
	}

	public function getAusPostRates($id_zone, $is_cart, $cart, $product_weight, $dest_zip, $dest_state, $dest_country, $currency, $product, $id_product_attribute, $qty, $dest_city)
	{
		$rates = array();
		if (file_exists(dirname(__FILE__).'/../../auspost/classes/RateAvailableServices.php'))
		{
			include_once(dirname(__FILE__).'/../../auspost/auspost.php');
			include_once(dirname(__FILE__).'/../../auspost/classes/RateAvailableServices.php');
			$ap = new AusPost();
			if ($ap->active)
			{
				$handling = Configuration::get('PS_SHIPPING_HANDLING');
				$carriers = $ap->getCarriers($id_zone, $cart, $is_cart, $product);
				foreach ($carriers as $carrier)
				{
					if(get_parent_class($ap) == 'PrestoChangeoCarrierModule')
					{
						$ap_rate = new AusPostRate();
						$amount = $ap_rate->getRate($carrier['id_carrier'], $carrier['id_zone'], $is_cart != ''?$cart->getTotalWeight():max($product_weight,0.001), $dest_zip, $dest_state, $dest_country, "", $is_cart != ''?0:$product->getPrice(true, $id_product_attribute, 6, NULL, false, true, $qty), $is_cart != ''?0:$product->id, $is_cart != ''?0:$id_product_attribute, $qty);
					}
					else
						$amount = getAusPostRate($carrier['id_carrier'], $carrier['id_zone'], $is_cart != ''?$cart->getTotalWeight():max($product_weight,0.001), $dest_zip, $dest_country, $is_cart != ''?0:$product->getPrice(true, $id_product_attribute, 6, NULL, false, true, $qty), $is_cart != ''?0:$product->id);
					// Add product spercific cost + handling fee
					if ($amount > 0)
						$amount += $this->getExtraShippingCost($carrier, $handling, $is_cart != ''?$cart->getProducts():array(), $product, $qty);
					// Apply shipping tax if needed
					if (!Tax::excludeTaxeOption())
						$carrierTax = Tax::getCarrierTaxRate($carrier['id_carrier']);
					if (isset($carrierTax) && $amount !== false)
						$amount *= 1 + ($carrierTax / 100);

					$amount = $amount === false?-1:Tools::convertPrice($amount, $currency);
					if ($amount > 0)
						$rates[$carrier['name']] = array(Tools::displayPrice($amount, $currency, false), $carrier['id_carrier']);
					elseif ($amount !== false && $amount == 0)
						$rates[$carrier['name']] = array($ap->l('Free!'), $carrier['id_carrier']);
				}

			}
		}

		return $rates;
	}
	
	public function getCanadaPostRates($id_zone, $is_cart, $cart, $product_weight, $dest_zip, $dest_state, $dest_country, $currency, $product, $id_product_attribute, $qty, $dest_city)
	{
		$rates = array();
		if (file_exists(dirname(__FILE__).'/../../canadapost/classes/RateAvailableServices.php'))
		{
			include_once(dirname(__FILE__).'/../../canadapost/canadapost.php');
			include_once(dirname(__FILE__).'/../../canadapost/classes/RateAvailableServices.php');
			$ap = new CanadaPost();
			if ($ap->active)
			{
				$handling = Configuration::get('PS_SHIPPING_HANDLING');
				$carriers = $ap->getCarriers($id_zone, $cart, $is_cart, $product);
				foreach ($carriers as $carrier)
				{
					if(get_parent_class($ap) == 'PrestoChangeoCarrierModule')
					{
						$ap_rate = new CanadaPostRate();
						$amount = $ap_rate->getRate($carrier['id_carrier'], $carrier['id_zone'], $is_cart != ''?$cart->getTotalWeight():max($product_weight,0.001), $dest_zip, $dest_state, $dest_country, "", $is_cart != ''?0:$product->getPrice(true, $id_product_attribute, 6, NULL, false, true, $qty), $is_cart != ''?0:$product->id, $is_cart != ''?0:$id_product_attribute, $qty);
					}
					else
						$amount = getCanadaPostRate($carrier['id_carrier'], $carrier['id_zone'], $is_cart != ''?$cart->getTotalWeight():max($product_weight,0.001), $dest_zip, $dest_country, $is_cart != ''?0:$product->getPrice(true, $id_product_attribute, 6, NULL, false, true, $qty), $is_cart != ''?0:$product->id);
					// Add product spercific cost + handling fee
					if ($amount > 0)
						$amount += $this->getExtraShippingCost($carrier, $handling, $is_cart != ''?$cart->getProducts():array(), $product, $qty);
					// Apply shipping tax if needed
					if (!Tax::excludeTaxeOption())
						$carrierTax = Tax::getCarrierTaxRate($carrier['id_carrier']);
					if (isset($carrierTax) && $amount !== false)
						$amount *= 1 + ($carrierTax / 100);

					$amount = $amount === false?-1:Tools::convertPrice($amount, $currency);
					if ($amount > 0)
						$rates[$carrier['name']] = array(Tools::displayPrice($amount, $currency, false), $carrier['id_carrier']);
					elseif ($amount !== false && $amount == 0)
						$rates[$carrier['name']] = array($ap->l('Free!'), $carrier['id_carrier']);
				}

			}
		}

		return $rates;
	}
	
	public function getRoyalMailRates($id_zone, $is_cart, $cart, $product_weight, $dest_zip, $dest_state, $dest_country, $currency, $product, $id_product_attribute, $qty, $dest_city)
	{
		$rates = array();
		if (file_exists(dirname(__FILE__).'/../../royalmail/classes/RateAvailableServices.php'))
		{
			include_once(dirname(__FILE__).'/../../royalmail/royalmail.php');
			include_once(dirname(__FILE__).'/../../royalmail/classes/RateAvailableServices.php');
			$rm = new RoyalMail();
			if ($rm->active)
			{
				$handling = Configuration::get('PS_SHIPPING_HANDLING');
				$carriers = $rm->getCarriers($id_zone, $cart, $is_cart, $product);
				foreach ($carriers as $carrier)
				{
					if(get_parent_class($rm) == 'PrestoChangeoCarrierModule')
					{
						$rm_rate = new RoyalMailRate();
						$amount = $rm_rate->getRate($carrier['id_carrier'], $carrier['id_zone'], $is_cart != ''?$cart->getTotalWeight():max($product_weight,0.001), $dest_zip, $dest_state, $dest_country, "", $is_cart != ''?0:$product->getPrice(true, $id_product_attribute, 6, NULL, false, true, $qty), $is_cart != ''?0:$product->id, $is_cart != ''?0:$id_product_attribute, $qty);
					}
					else
						$amount = getRoyalMailRate($carrier['id_carrier'], $carrier['id_zone'], $is_cart != ''?$cart->getTotalWeight():max($product_weight,0.001), $dest_zip, $dest_country, $is_cart != ''?0:$product->getPrice(true, $id_product_attribute, 6, NULL, false, true, $qty), $is_cart != ''?0:$product->id);
					// Add product spercific cost + handling fee
					if ($amount > 0)
						$amount += $this->getExtraShippingCost($carrier, $handling, $is_cart != ''?$cart->getProducts():array(), $product, $qty);
					// Apply shipping tax if needed
					if (!Tax::excludeTaxeOption())
						$carrierTax = Tax::getCarrierTaxRate($carrier['id_carrier']);
					if (isset($carrierTax) && $amount !== false)
						$amount *= 1 + ($carrierTax / 100);

					$amount = $amount === false?-1:Tools::convertPrice($amount, $currency);
					if ($amount > 0)
						$rates[$carrier['name']] = array(Tools::displayPrice($amount, $currency, false), $carrier['id_carrier']);
					elseif ($amount !== false && $amount == 0)
						$rates[$carrier['name']] = array($ap->l('Free!'), $carrier['id_carrier']);
				}

			}
		}

		return $rates;
	}

	public function getAllRates($id_zone, $is_cart, $cart, $product_weight, $dest_zip, $dest_state, $dest_country, $currency, $product, $id_product_attribute, $qty, $dest_city)
	{
		$fedexRates = $this->getFedexRates($id_zone, $is_cart, $cart, $product_weight, $dest_zip, $dest_state, $dest_country, $currency, $product, $id_product_attribute, $qty);
		$uspsRates = $this->getUSPSRates($id_zone, $is_cart, $cart, $product_weight, $dest_zip, $dest_state, $dest_country, $currency, $product, $id_product_attribute, $qty);
		$upsRates = $this->getUPSRates($id_zone, $is_cart, $cart, $product_weight, $dest_zip, $dest_state, $dest_country, $currency, $product, $id_product_attribute, $qty);
		$dhlRates = $this->getDHLRates($id_zone, $is_cart, $cart, $product_weight, $dest_zip, $dest_state, $dest_country, $currency, $product, $id_product_attribute, $qty, $dest_city);
		$lsRates = $this->getLSRates($id_zone, $is_cart, $cart, $product_weight, $dest_zip, $dest_state, $dest_country, $currency, $product, $id_product_attribute, $qty, $dest_city);
		$apRates = $this->getAusPostRates($id_zone, $is_cart, $cart, $product_weight, $dest_zip, $dest_state, $dest_country, $currency, $product, $id_product_attribute, $qty, $dest_city);
		$cpRates = $this->getCanadaPostRates($id_zone, $is_cart, $cart, $product_weight, $dest_zip, $dest_state, $dest_country, $currency, $product, $id_product_attribute, $qty, $dest_city);
		$rmRates = $this->getRoyalMailRates($id_zone, $is_cart, $cart, $product_weight, $dest_zip, $dest_state, $dest_country, $currency, $product, $id_product_attribute, $qty, $dest_city);

		return array_merge($fedexRates, $uspsRates, $upsRates, $dhlRates, $lsRates, $apRates, $cpRates, $rmRates);
	}

	public function updateCartWithNewCarrier()
	{
		if (Tools::isSubmit('new_id_carrier'))
		{
			$tp = $this->context->cart->nbProducts();
			if ($tp > 0)
			{
				if ($this->getPSV() >= 1.5)
				{
					$id = $this->context->cart->id_address_delivery;
					$this->context->cart->setDeliveryOption(array($id => $_POST['new_id_carrier'].','));
					$this->context->cart->save();
				}
				else
				{
					$this->context->cart->id_carrier = $_POST['new_id_carrier'];
					$this->context->cart->update();
				}
			}
			print $tp;
			exit;
		}
	}

	public function getBoxes($id_carrier, $totalWeight, $cart, $id_product, $id_product_attribute, $qty, $customPackageName = null)
	{
		$log = false;
		$fs_arr = Db::getInstance()->getRow('SELECT free_shipping_product, free_shipping_category, free_shipping_manufacturer, free_shipping_supplier FROM '._DB_PREFIX_.'fe_'.$this->name.'_method WHERE id_carrier = "'.$id_carrier.'"');

		$this->saveLog('getBoxes_log1.txt', "1) $totalWeight, cart, $id_product, $qty \n", $log);

		$st = time();
		$show_sizes = $this->{'_'.$this->name.'_debug_mode'};
		if (Tools::getValue('ajax') == 'true')
			$show_sizes = false;
		$max_weight = $this->{'_'.$this->name.'_weight'};
		$this->saveLog('getBoxes_log1.txt', "2) $max_weight \n", $log);
		if ($this->{'_'.$this->name.'_unit'} != 'LBS')
			$max_weight = $this->getWeightInKg($max_weight);
		$this->saveLog('getBoxes_log1.txt', "3) $max_weight \n", $log);
		$this->saveLog('getBoxes_log1.txt', "3.5) ".$this->{'_'.$this->name.'_pack'}." ($customPackageName) -> ".$this->{'_'.$this->name.'_packages'}." \n", $log);
		$pack_dim = array();
		$box_exceptions = array();  
		// Custom Package //
		if ($this->{'_'.$this->name.'_pack'} == $customPackageName OR $this->name == 'canadapost' OR $this->name == 'auspost' OR $this->name == 'royalmail' OR $this->name == 'usps')
		{
			// Single package //
			if ($this->{'_'.$this->name.'_packages'} == 'single')
			{
				if ($id_product == 0)
				{
					$products = $cart->getProducts();
					$tw = 0;
					foreach ($products as $product)
					{
						if (!$this->is_free_ship_product($product['id_product'], $fs_arr))
						{
							for ($i = 0 ; $i < $product['quantity']; $i++)
							{
								$product_weight = ($this->{'_'.$this->name.'_unit'} == 'LBS'?$this->getWeightInLb($product['weight']):$this->getWeightInKg($product['weight']));
								if ($product_weight > $max_weight)
								{
									$this->saveLog('getBoxes_log1.txt', "2) $product_weight > $max_weight (array())\n", $log);
									return array();
								}
								$tw += ($this->{'_'.$this->name.'_unit'} == 'LBS'?$this->getWeightInLb($product['weight']):$this->getWeightInKg($product['weight']));
							}
						}
					}
					if ($tw == 0)
					{
						$this->saveLog('getBoxes_log1.txt', "3) tw = 0 (false)\n", $log);
						return false;
					}
					$pack_dim[] = array('weight' => max($tw,0.001), 'w' => $this->{'_'.$this->name.'_width'},
								'h' => $this->{'_'.$this->name.'_height'}, 'd' => $this->{'_'.$this->name.'_depth'});
				}
				// Get Product box
				else
				{
					// Free shipping for this item.
					if ($this->is_free_ship_product($id_product, $fs_arr))
					{
						$this->saveLog('getBoxes_log1.txt', "4) FS product (false)\n", $log);
						return false;
					}
					$pack_dim[] = array('weight' => $totalWeight, 'w' => $this->{'_'.$this->name.'_width'},
						'h' => $this->{'_'.$this->name.'_height'}, 'd' => $this->{'_'.$this->name.'_depth'});
				}
			}
			// Multiple Packages //
			else
			{
				// 	Fixed box size //
				if ($this->{'_'.$this->name.'_package_size'} == 'fixed')
				{
					$n = 0;
					$tw = 0;
					// Get Cart boxes
					if ($id_product == 0)
					{
						$products = $cart->getProducts();
						foreach ($products as $product)
						{
							if (!$this->is_free_ship_product($product['id_product'], $fs_arr))
							{
								for ($i = 0 ; $i < $product['quantity']; $i++)
								{
									$product_weight = ($this->{'_'.$this->name.'_unit'} == 'LBS'?$this->getWeightInLb($product['weight']):$this->getWeightInKg($product['weight']));
									if ($product_weight > $max_weight)
									{
										$this->saveLog('getBoxes_log1.txt', "5) $product_weight > $max_weight (array())\n", $log);
										return array();
									}
									if (($n > 0 && $n == $this->{'_'.$this->name.'_packages_per_box'}) || $tw + $product_weight > $max_weight)
									{
										$pack_dim[] = array('weight' => max($tw,0.001), 'w' => $this->{'_'.$this->name.'_width'},
												'h' => $this->{'_'.$this->name.'_height'}, 'd' => $this->{'_'.$this->name.'_depth'});
										$n = 0;
										$tw = 0;
									}
									$tw += ($this->{'_'.$this->name.'_unit'} == 'LBS'?$this->getWeightInLb($product['weight']):$this->getWeightInKg($product['weight']));
									$n++;
								}
							}
						}
						if ($tw == 0 && sizeof($pack_dim) == 0)
						{
							$this->saveLog('getBoxes_log1.txt', "6) $tw packdim 0 (array())\n", $log);
							return false;
						}
						if ($n > 0)
							$pack_dim[] = array('weight' => max($tw,0.001), 'w' => $this->{'_'.$this->name.'_width'},
								'h' => $this->{'_'.$this->name.'_height'}, 'd' => $this->{'_'.$this->name.'_depth'});
					}
					// Get Product box
					else
					{
						// Free shipping for this item.
						if ($this->is_free_ship_product($id_product, $fs_arr))
							return false;
						for ($j = 0 ; $j < $qty ; $j++)
						{
							$product_weight = $totalWeight / $qty;
							if ($product_weight > $max_weight)
							{
								$this->saveLog('getBoxes_log1.txt', "7) $product_weight > $max_weight (array())\n", $log);
								return array();
							}
							$this->saveLog('getBoxes_log1.txt', "8.$j) tw = $tw > n = $n \n", $log);
							if ($tw > 0 && (($n > 0 && $n == $this->{'_'.$this->name.'_packages_per_box'}) || $tw + $product_weight > $max_weight))
							{
								$this->saveLog('getBoxes_log1.txt', "9) add to pack_dim \n".print_r($pack_dim, true), $log);
								$pack_dim[] = array('weight' => max($tw,0.001), 'w' => $this->{'_'.$this->name.'_width'},
										'h' => $this->{'_'.$this->name.'_height'}, 'd' => $this->{'_'.$this->name.'_depth'});
								$n = 0;
								$tw = 0;
							}
							$tw += $product_weight;
							$n++;
						}
						if ($n > 0)
							$pack_dim[] = array('weight' => max($tw,0.001), 'w' => $this->{'_'.$this->name.'_width'},
								'h' => $this->{'_'.$this->name.'_height'}, 'd' => $this->{'_'.$this->name.'_depth'});
					}

					$this->saveLog('getBoxes_log1.txt', "8) pack_dim\n".print_r($pack_dim, true), $log);

				}
				// Product box size //
				else
				{
					$products_size = array(); 
					$products = $cart->getProducts();
					// Get Cart boxes
					if ($id_product == 0)
					{
						foreach ($products as $product)
						{
							if (!$this->is_free_ship_product($product['id_product'], $fs_arr))
							{
								$product_weight = ($this->{'_'.$this->name.'_unit'} == 'LBS'?$this->getWeightInLb($product['weight']):$this->getWeightInKg($product['weight']));
								$parr['weight'] = round(max($product_weight,0.001),3);
								$parr['w'] = round(max($product['width'],0.001),3);
								$parr['h'] = round(max($product['height'],0.001),3);
								$parr['d'] = round(max($product['depth'],0.001),3);
								//print "parr: ".print_r($parr,true)."<br />";
								if (!isset($parr['w']))
									$parr['w'] = 1;
								if (!isset($parr['h']))
									$parr['h'] = 1;
								if (!isset($parr['d']))
									$parr['d'] = 1;
								if ($pkey = $this->is_box_exception($product['id_product']))
								{
									for ($i = 0 ; $i < $product['quantity']; $i++)
										if (is_array($box_exceptions[$pkey]))
											$box_exceptions[$pkey][] = $parr;
										else
											$box_exceptions[$pkey] = array($parr);
								}
								else
									for ($i = 0 ; $i < $product['quantity']; $i++)
										$products_size[] = $parr;
							}
						}
					}
					// Get Product box
					else
					{
						// Free shipping for this item.
						if ($this->is_free_ship_product($id_product, $fs_arr))
						{
							$this->saveLog('getBoxes_log1.txt', "9) fs (false)\n", $log);
							return false;
						}
						$product = new Product($id_product);
						$attribute_weight = 0;
						$query = '';
						if ($id_product_attribute > 0)
						{
							$query = 'SELECT `weight`
								FROM `'._DB_PREFIX_.'product_attribute`
								WHERE `id_product_attribute` = '.(int)($id_product_attribute);
							$rq = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);
							$attribute_weight = $rq['weight'];
						}
						$this->saveLog('getBoxes_log1.txt', "10) $query $product->weight + $attribute_weight\n", $log);
						$product_weight = ($this->{'_'.$this->name.'_unit'} == 'LBS'?$this->getWeightInLb($product->weight+$attribute_weight):$this->getWeightInKg($product->weight+$attribute_weight));
						$parr['weight'] = round(max($product_weight,0.001),3);
						$parr['w'] = round(max($product->width,0.001),3);
						$parr['h'] = round(max($product->height,0.001),3);
						$parr['d'] = round(max($product->depth,0.001),3);
						if (!isset($parr['w']))
							$parr['w'] = 1;
						if (!isset($parr['h']))
							$parr['h'] = 1;
						if (!isset($parr['d']))
							$parr['d'] = 1;
						if ($pkey = $this->is_box_exception($id_product))
						{
							for ($i = 0 ; $i < $qty; $i++)
								if (is_array($box_exceptions[$pkey]))
									$box_exceptions[$pkey][] = $parr;
								else
									$box_exceptions[$pkey] = array($parr);
						}
						else
							for ($j = 0 ; $j < $qty ; $j++)
								$products_size[] = $parr;
					}
					// Free shipping on all items
					if (sizeof($products_size) == 0 && sizeof($box_exceptions) == 0)
					{
						$this->saveLog('getBoxes_log1.txt', "10) product_size 0 (array())\n", $log);
						return false;
					}

						$this->saveLog('getBoxes_log1.txt', "products_size: ".print_r($products_size, true)." \n", $log);
					if (sizeof($products_size) > 0) 
					{
						if (sizeof($products_size) < 200)
						{
							$pack_dim = $this->get_fit_boxes($products_size, $this->get_boxes_no_exception());
						$this->saveLog('getBoxes_log1.txt', "boxes: ".print_r($this->get_boxes_no_exception(), true)." \n", $log);
						$this->saveLog('getBoxes_log1.txt', "pack_dim: ".print_r($pack_dim, true)." \n", $log);
						}
						else 
						{
							$tmp_exception = $products_size;
							while (sizeof($tmp_exception) > 0)
							{
								$subset_exe = array_slice($tmp_exception, 0, 200);
								$tmp_exception = array_slice($tmp_exception, 200);
								$exception_dim = $this->get_fit_boxes($subset_exe, $this->get_boxes_no_exception());
								foreach ($exception_dim as $exe_box)
									$pack_dim[] = $exe_box;
							}
						}
					}
					foreach ($box_exceptions as $key => $exception)
					{
						$allowed_boxes = array();
						foreach ($this->{'_'.$this->name.'_boxes'} as $box)
							if ($key == $box[5])
								$allowed_boxes[] = $box;

						if (sizeof($exception) <= 200)
						{
							$exception_dim = $this->get_fit_boxes($exception, $allowed_boxes);
							foreach ($exception_dim as $exe_box)
								$pack_dim[] = $exe_box;
						}
						else 
						{
							$tmp_exception = $exception;
							while (sizeof($tmp_exception) > 0)
							{
								$subset_exe = array_slice($tmp_exception, 0, 200);
								$tmp_exception = array_slice($tmp_exception, 200);
								$exception_dim = $this->get_fit_boxes($subset_exe, $allowed_boxes);
								foreach ($exception_dim as $exe_box)
									$pack_dim[] = $exe_box;
							}
						}
					}
				}
			}
			if ($show_sizes)
			{
				print "<br />Boxes used:<b>".sizeof($pack_dim)."</b> (".(time()-$st).")<br />\n";
				foreach ($pack_dim as $pak)
					print "Width: ".$pak['w'].", Height: ".$pak['h'].", Depth: ".$pak['d'].", Weight: ".$pak['weight']."<br />";
			}
		}
		else
		{
			$max_weight = $this->getMaxWeightPackageTypes() ? $this->getMaxWeightPackageTypes() : $max_weight;
			// Get Cart boxes
			if ($id_product == 0)
			{
				$products = $cart->getProducts();
				$tw = 0;
				foreach ($products as $product)
				{
					if (!$this->is_free_ship_product($product['id_product'], $fs_arr))
					{
						for ($i = 0 ; $i < $product['quantity']; $i++)
						{
							$product_weight = ($this->{'_'.$this->name.'_unit'} == 'LBS'?$this->getWeightInLb($product['weight']):$this->getWeightInKg($product['weight']));
							if ($product_weight > $max_weight)
							{
								$this->saveLog('getBoxes_log1.txt', "11) $product_weight > $max_weight (array())\n", $log);
								return array();
							}
							if ($tw + $product_weight > $max_weight)
							{
								$pack_dim[] = array('weight' => max($tw,0.001), 'w' => 0.1, 'h' => 0.1, 'd' => 0.1);
								$tw = 0;
							}
							$tw += ($this->{'_'.$this->name.'_unit'} == 'LBS'?$this->getWeightInLb($product['weight']):$this->getWeightInKg($product['weight']));
						}
					}
				}
				if ($tw > 0)
				{
					$pd = $this->getDimensionsByType($this->{'_'.$this->name.'_pack'});
					$pack_dim[] = array('weight' => max($tw,0.001), 'w' => $pd['w'], 'h' => $pd['h'], 'd' => $pd['d']);
				}
				if (sizeof($pack_dim) == 0)
				{
					$this->saveLog('getBoxes_log1.txt', "12) packdim = 0 (false)\n", $log);
					return false;
				}

			}
			// Get Product box
			else
			{
				if ($this->is_free_ship_product($id_product, $fs_arr))
					return false;
				for ($j = 0 ; $j < $qty ; $j++)
				{
					$pd = $this->getDimensionsByType($this->{'_'.$this->name.'_pack'});
					$pack_dim[] = array('weight' => max($totalWeight,0.001), 'w' => $pd['w'], 'h' => $pd['h'], 'd' => $pd['d']);
				}
			}
		}
		return $pack_dim;		
	}

	public function getMaxWeightPackageTypes()
	{
		//some calculations...
		return false;
	}

	protected function checkShippingRanges($id_carrier, $totalWeight, $orderTotal)
	{
		$result = true;
		$range_exist = false;
		if(Configuration::get('PS_SHIPPING_METHOD') == 1) //if billing is by total weight
		{
			$range_exist = Db::getInstance()->getRow('
				SELECT *
				FROM '._DB_PREFIX_.'range_weight
				WHERE `id_carrier` = '.(int)$id_carrier.'
			');
			$result = Db::getInstance()->getRow('
				SELECT *
				FROM '._DB_PREFIX_.'range_weight
				WHERE '.(float)$totalWeight.' BETWEEN `delimiter1` AND `delimiter2`
				AND `id_carrier` = '.(int)$id_carrier.'
			');
		}
		elseif(Configuration::get('PS_SHIPPING_METHOD') == 0) //if billing is by total price
		{
			$range_exist = Db::getInstance()->getRow('
				SELECT *
				FROM '._DB_PREFIX_.'range_price
				WHERE `id_carrier` = '.(int)$id_carrier.'
			');
			$result = Db::getInstance()->getRow('
				SELECT *
				FROM '._DB_PREFIX_.'range_price
				WHERE '.(float)$orderTotal.' BETWEEN `delimiter1` AND `delimiter2`
				AND `id_carrier` = '.(int)$id_carrier.'
			');
		}

		if($range_exist AND (!$result OR !sizeof($result)))
			return false;
		else
			return true;
	}

	public function getProductCarriers($cart, $is_cart, $product)
	{
		$product_carriers = array();

		if($this->getPSV() >= 1.5 AND ($cart OR $product))
		{
			$products = array();
			if(!$is_cart AND $product)
				$products[] = $product;
			else
				$products = $cart->getProducts();

			foreach ($products as $product)
			{
				if(is_array($product))
					$id_product = $product['id_product'];
				elseif(is_object($product))
					$id_product = $product->id;
				elseif(is_int($product))
					$id_product = $product;
				else
					break;

				$product_carriers_temp = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
					SELECT c.*
					FROM `'._DB_PREFIX_.'product_carrier` pc
					INNER JOIN `'._DB_PREFIX_.'carrier` c
						ON (c.`id_reference` = pc.`id_carrier_reference` AND c.`deleted` = 0)
					WHERE pc.`id_product` = '.(int)$id_product.'
						AND pc.`id_shop` = '.(int)$this->context->shop->id
				);

				//if not all carriers can be used for this product
				if(is_array($product_carriers_temp) AND sizeof($product_carriers_temp))
				{
					foreach ($product_carriers_temp as $product_carrier_temp)
						$product_carriers[] = $product_carrier_temp['id_carrier'];
				}
			}

			$product_carriers = array_unique($product_carriers);
		}

		return $product_carriers;
	}
	
	public function _getPrestoChangeoShippingModulesForOrder($carrier)
	{
		$PrestoChangeoShippingModules = array();
		/** LIST OF SHIPPING MODULE WITH LABEL PRINTING */
		$PCShippingModulesWithLabelPrinting = array(
			'fedex' => 'fedex',
			'dhl' => 'dhl',
			'ups' => 'ups',
			'usps' => 'usps',
			'canadapost' => 'canadapost',
		);
		
		/** GET ALL THE ENABLED MODULES IN adminOrder HOOK */
		if($this->getPSV() == 1.6)
			$modules = Hook::getHookModuleExecList('adminOrder');
		elseif($this->getPSV() == 1.5)    
			$modules = Hook::getHookModuleExecList('displayAdminOrder'); 
		else
		{
			$modules = array();
			foreach($PCShippingModulesWithLabelPrinting as $module)
			{
				$module = Module::getInstanceByName($module);     
				if(isset($module->active) && $module->active)
					$modules[]['module'] = $module->name;
			}
		}   

		if(is_array($modules) && count($modules))
		{
			foreach($modules as $module)
			{                     
				/** VERIFY IF MODULE IS A SHIPPING MODULE WITH LABEL PRINTING */
				if(in_array($module['module'], $PCShippingModulesWithLabelPrinting))
				{
					if($carrier->external_module_name == $module['module'])
						$PrestoChangeoShippingModules[] = $module['module'];
					elseif(Configuration::get(strtoupper($module['module']).'_ENABLE_LABELS') == 'all')  
						$PrestoChangeoShippingModules[] = $module['module'];
				}
			}
		}
		
		return $PrestoChangeoShippingModules;
	}
}
