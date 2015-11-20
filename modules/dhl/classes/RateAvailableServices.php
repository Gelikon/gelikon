<?php

require_once(dirname(__FILE__) . '/../dhl.php');

class DHLRate extends DHL
{
	public function getRate($id_carrier, $id_zone, $totalWeight, $dest_zip = "", $dest_state = "", $dest_country = "", $dest_city = "", $product_price = 0, $id_product = 0, $id_product_attribute = 0, $qty = 1, $params = "", $products = NULL)
	{
		$cart = $this->context->cart;
		$log = true;
		
		if ($params != "")
			$cart = $params;

		$this->saveLog('call_log.txt', "getDHLRate\n\r", $log);

		$fs_arr = Db::getInstance()->getRow('SELECT free_shipping_product, free_shipping_category, free_shipping_manufacturer, free_shipping_supplier FROM '._DB_PREFIX_.'fe_dhl_method WHERE id_carrier = "'.(int)$id_carrier.'"');

		// Get customer info //
		$customerInfo = $this->getCustomerInfo($id_zone, $dest_zip, $dest_country, $dest_city, $cart);
		if(!$customerInfo)
			return false;
		$dest_zip = $customerInfo['dest_zip'];
		$dest_country = $customerInfo['dest_country'];
		$dest_city = $customerInfo['dest_city'];
		$id_zone = $customerInfo['id_zone'];

		if (($dest_zip == "" && $this->_dhl_address_display['zip'] == 1) OR (int)$id_zone == 0)
			return false;

		$st = time();

		$this->saveLog('dhl_rate_log.txt',"1) $id_carrier, $id_zone, $totalWeight, $id_product (p $product_price) (qty $qty) , $dest_zip, $dest_country, $dest_city\n\r\n", $log);

		$totalWeight = $this->getOrderTotalWeight($cart->id, $id_product, $qty, $totalWeight);
		$orderTotal = $this->getTotal($product_price, $qty, $cart);

		if(!$this->checkShippingRanges($id_carrier, $totalWeight, $orderTotal))
			return false;

		// Check to see if it's dhl shipping
		$carrier = $this->getCarrier($id_carrier, $id_zone);  

		if (!$carrier)
			return false;
			
		/** CONVERT VALUES IF CURRENT_CURRENCY != DEFAULT_CURRENCY */
		if($this->context->cookie->id_currency != Configuration::get('PS_CURRENCY_DEFAULT'))
		{
			$currency = new Currency((int) $this->context->cookie->id_currency);
			$carrier['free_shipping'] = Tools::convertPrice($carrier['free_shipping'], $currency); 
		}

		$this->saveLog('dhl_rate_log.txt', "3) 2 ($orderTotal) $id_carrier, $id_zone, $totalWeight, $dest_zip , $dest_country, $dest_city\n\r ", $log);

		$this->saveLog('dhl_rate_log.txt', "6) Hashcache check: $dest_country, $dest_zip\n\r", $log);
		$this->saveLog('dhl_rate_log.txt', "Carrier::  ".print_r($carrier, true)."\n\r", $log);

		$products = $products ? $products : $cart->getProducts();

		$hash_rate = $this->getHash($id_carrier, $products, $id_product, $id_product_attribute, $qty, $dest_country, "", $dest_zip, true);

		if ($hash_rate !== false)
		{
			$this->saveLog('dhl_rate_log.txt',"7) Cache\n\r"."hashcahsh = $hash_rate\n\r",$log);
			return $hash_rate <= -1 ? false : $hash_rate;
		}

		// Check Invalid Destination cache
		$invalid_dest = $this->checkInvalidDestination($carrier, $dest_zip, $dest_country, $log);
		$this->saveLog('dhl_rate_log.txt', "9.5) \n\r invalid_dest".print_r($invalid_dest,true), $log);

		if (is_array($invalid_dest) && sizeof($invalid_dest) >= 1)
		{
			$this->saveLog('dhl_rate_log.txt', "10) \n\r result".print_r($invalid_dest,true), $log);
			return false;
		}

		// Calculate insurance (if needed)
		$iamount = $this->calculateInsurance($carrier, $orderTotal, $id_product, $products, $qty, $cart);

		$this->saveLog('dhl_rate_log.txt', "\n\r11) TIME1: ".(time() - $st)."\nChecking cache $totalWeight,  $id_product, $qty", $log);

		$pack_dim = $this->getBoxes($id_carrier, $totalWeight, $cart, $id_product, $id_product_attribute, $qty, "CP");
		if (is_array($pack_dim) && sizeof($pack_dim) == 0)
		{
			$this->saveLog('dhl_rate_log.txt', "\n\r12) Package Dim problem, ".print_r($pack_dim, true), $log);
			return false;
		}

		// Free Shipping per product;
		if ($pack_dim == false)
			return 0;
		$this->saveLog('dhl_rate_log.txt', "13) TIME2: ".(time() - $st)."\n", $log);

		// More than maximum rate, return and don't cache //
		if ((sizeof($pack_dim) * 150 < $totalWeight && !$this->is_free_ship_cart($products, $fs_arr)) || sizeof($pack_dim) > 50)
		{
			$this->saveLog('dhl_rate_log.txt', "\n\r14)  Weight / Package Dim problem", $log);
			return false;
		}

		// Check for invalid US zipcode
		if ($dest_country == 'US' && !$this->validateUSZip($dest_zip, $this))
		{
			$this->saveInvalidDestination($carrier, $dest_zip, $dest_country);
			return false;
		}
		
		$dhl_carriers = $this->getCarriers($id_zone, $cart, Tools::getValue($this->name.'_is_cart', false), $id_product);
		$eachCarrierRequest = array();
		if(is_array($dhl_carriers) && count($dhl_carriers))
		{
			foreach($dhl_carriers as $newCarrier)
			{
				$iamount = $this->calculateInsurance($newCarrier, $orderTotal, $id_product, $products, $qty, $cart);
				if($iamount > 0 && $newCarrier['insurance_type'])
					$eachCarrierRequest[$iamount][$newCarrier['id_carrier']] = $newCarrier['method'];
				else
					$eachCarrierRequest['STANDARD'][$newCarrier['id_carrier']] = $newCarrier['method'];
			}
		}  
		
		/** IF SEPARATELY REQUEST IS NECESSARY */      
		if(is_array($eachCarrierRequest) && count($eachCarrierRequest) > 1)
		{
			$xml = new stdClass();
			$xml->GetQuoteResponse = new stdClass();
			$xml->GetQuoteResponse->BkgDetails = new stdClass();
			$xml->GetQuoteResponse->BkgDetails->QtdShp = array();
			
			foreach($eachCarrierRequest as $group => $eachCarrier)
			{                          
				$requests[$group] = $this->requestRate($pack_dim, ($group == 'STANDARD' ? 0 : $group), $dest_country, $dest_zip, $dest_city, $log, false, true);  
			}
			
			if(is_array($requests) && count($requests))
			{
				$requestResponses = $this->multiRequest($requests, $this->getServerURL());
				
				$duatiablePacks = $this->dutiablePacks();
				$EUCountries = $this->EUCountries();
				
				if(!strlen($dest_country))
					$isDutiable = 'N';
				elseif($dest_country != $this->_dhl_origin_country && in_array($this->_dhl_pack, $duatiablePacks) === true)
				{
					if(in_array($this->_dhl_origin_country, $EUCountries) === true)
					{
						if(in_array($dest_country, $EUCountries) === true)
							$isDutiable = 'N';
						else
							$isDutiable = 'Y';						
					}
					else
						$isDutiable = 'Y';
				}
				else
					$isDutiable = 'N';
				
				if(is_array($requestResponses) && count($requestResponses))
				{
					foreach($requestResponses as $group => $post_response)
					{
						$requestResponse = @simplexml_load_string($post_response);
						
                        if($requestResponse !== false)
                        {
						    $this->saveLog(
							    $this->_dhl_log_filename,
                                '// ------- Request: '.$requests[$group]."\n\r".
                                '// ------- Response: '."\n\r".print_r($requestResponse, true)."\n\r
                                \n\r", 
							    $this->_dhl_xml_log
						    );
                        }
                        else
                        {
                            $this->saveLog($this->_dhl_log_filename, 
                                '// ------- Server Error: '.$post_response.' - Please contact your hosting provider for more details'."\n\r",
                            $this->_dhl_xml_log);
                        }
							
						foreach($dhl_carriers as $newCarrier)
						{
							if(in_array($newCarrier['method'], $eachCarrierRequest[$group]))
							{					
								$newCarrierID = array_search($newCarrier['method'], $eachCarrierRequest[$group]);
								
								if($isDutiable == 'Y')
									$newCarrier['method'] = $this->dutiableCarrierMethods($newCarrier['method']); 
									
								if (@$requestResponse->GetQuoteResponse->BkgDetails->QtdShp)
								{
									foreach ($requestResponse->GetQuoteResponse->BkgDetails->QtdShp as $rateReply)
									{                               							
										if ($newCarrier['method'] == $rateReply->GlobalProductCode)
										{
											$rateReply->id_carrier = $newCarrierID;
											$xml->GetQuoteResponse->BkgDetails->QtdShp[] = $rateReply;	
										}										
									}
								}
							}
						} 	
					}
				}
			}  
		}
		else           
			$xml = $this->requestRate($pack_dim, $iamount, $dest_country, $dest_zip, $dest_city, $log);

		if(!$xml) //timeout
			return false;

		if (@$xml->GetQuoteResponse->BkgDetails->QtdShp)
		{
			$ret_amount = false;
			$this_carriers = $this->getCarriers($id_zone, $cart, Tools::getValue($this->name.'_is_cart', false), $id_product);
			
			$duatiablePacks = $this->dutiablePacks();
			$EUCountries = $this->EUCountries();
			
			if(!strlen($dest_country))
				$isDutiable = 'N';
			elseif($dest_country != $this->_dhl_origin_country && in_array($this->_dhl_pack, $duatiablePacks) === true)
			{
				if(in_array($this->_dhl_origin_country, $EUCountries) === true)
				{
					if(in_array($dest_country, $EUCountries) === true)
						$isDutiable = 'N';
					else
						$isDutiable = 'Y';                        
				}
				else
					$isDutiable = 'Y';
			}
			else
				$isDutiable = 'N';
				
			foreach ($this_carriers as $this_carrier)
			{        
				$this->saveLog('dhl_rate_log.txt', "23) TIME4: ".(time() - $st)."\nGot rates, comparing to (".print_r($this_carrier, true).")\n\r", $log); 
				$method_found = false;
				$method_valid = false;
				foreach ($xml->GetQuoteResponse->BkgDetails->QtdShp as $rateReply)
				{                     
					$serviceType = $rateReply->GlobalProductCode;
					$this->saveLog('dhl_rate_log.txt', "!!!!!". $this_carrier['method']." == $serviceType ---  (".print_r($rateReply, true).")\n\r", $log);

					$amount_currency = $this->calculateAmount($rateReply);
					$amount = $amount_currency[0];
					if ($this->_dhl_enable_discount && (float)$this->_dhl_discount_rate > 0)
						$amount *= (float)$this->_dhl_discount_rate/100;
					$currency = $amount_currency[1];
					if (!$amount)
					{
						$this->saveLog('dhl_rate_log.txt', "23.5) NO AMOUNT BACK)\n\r", $log);
						continue;
					}
					
					$this_carrier['methodDutiable'] = $this_carrier['method'];
					if($isDutiable == 'Y')
						$this_carrier['methodDutiable'] = $this->dutiableCarrierMethods($this_carrier['method']);  

					// Check it there are no free shipping exceptions
					if ($this_carrier['methodDutiable'] == $serviceType)
					{
						if(isset($rateReply->id_carrier) && $rateReply->id_carrier != $this_carrier['id_carrier'])
							continue;
						
						$method_valid = true;
						if ($this_carrier['id_carrier'] == $id_carrier || ($fs_arr['free_shipping_product'] == '' && $fs_arr['free_shipping_category'] == '' &&
							$fs_arr['free_shipping_manufacturer'] == '' && $fs_arr['free_shipping_supplier'] == '' &&
							$this_carrier['free_shipping_product'] == '' && $this_carrier['free_shipping_category'] == '' &&
							$this_carrier['free_shipping_manufacturer'] == '' && $this_carrier['free_shipping_supplier'] == ''))
						{
							if ($this_carrier['id_carrier'] == $id_carrier)
								$method_found = true;
						}
						else
							continue;
					}
					else
						continue;
						
					$rate_currency = Currency::getIdByIsoCode($currency);
					$this->saveLog('dhl_rate_log.txt', "return currency id = $rate_currency\n\r", $log);
					/** IF RATE CURRENCY IS NOT THE DEFAULT STORE'S CURRENCY */
					if($rate_currency && $rate_currency != Configuration::get('PS_CURRENCY_DEFAULT'))
					{
						$return_currency = new Currency($rate_currency);
						$amount = $amount / $return_currency->conversion_rate;
						
						$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
						$currency = $currency->iso_code;
					}
					$this->saveLog('dhl_rate_log.txt', "amount in default currency = $amount\n\r", $log);

					$this->saveLog('dhl_rate_log.txt', "Writing to cache ".$this_carrier['id_carrier']."\n\r", $log);

					// Write to cache //
					$query = 'INSERT INTO `'._DB_PREFIX_.'fe_dhl_rate_cache` (id_carrier, origin_zip, origin_country, dest_zip, dest_country, method, insurance, dropoff, packing, packages, weight, rate, currency, quote_date) VALUES
					("'.$this_carrier['id_carrier'].'","'.$this->_dhl_origin_zip.'","'.$this->_dhl_origin_country.'","'.$dest_zip.'","'.$dest_country.'","'.$serviceType.'","'.$iamount.'","'.$this->_dhl_dropoff.'","'.$this->_dhl_pack.'","'.sizeof($pack_dim).'","'.$totalWeight.'","'.$amount.'", "'.$currency.'", "'.time().'")';
					Db::getInstance()->execute($query);
					$id_rate = Db::getInstance()->Insert_ID();

					// New hash cache
					$query = 'INSERT INTO `'._DB_PREFIX_.'fe_dhl_hash_cache` (id_dhl_rate, hash, hash_date) VALUES
					("'.$id_rate.'","'.$this->getHash($this_carrier['id_carrier'], $products, $id_product, $id_product_attribute, $qty, $dest_country, "", $dest_zip).'","'.time().'")';
					Db::getInstance()->execute($query);

					foreach ($pack_dim as $package)
					{
						$query = 'INSERT INTO `'._DB_PREFIX_.'fe_dhl_package_rate_cache` (id_dhl_rate, weight, width, height, depth) VALUES
						("'.$id_rate.'","'.$package['weight'].'","'.$package['w'].'","'.$package['h'].'","'.$package['d'].'")';
						Db::getInstance()->execute($query);
					}

					$this->saveLog('dhl_rate_log.txt', "24) $query\n\r", $log);
					$this->saveLog('dhl_rate_log.txt', "24.1) if (".$carrier['method']." == $serviceType && ".$this_carrier['id_carrier']." == $id_carrier)\n\r", $log);					
					// Only calculate ret_amount for the selected carrier, for all other matches, we're only caching
					if (($isDutiable == 'Y' ? $this->dutiableCarrierMethods($carrier['method']) : $carrier['method']) == $serviceType && $this_carrier['id_carrier'] == $id_carrier)
					{							
						if ($carrier['extra_shipping_type'] == 2)
							$amount += $carrier['extra_shipping_amount'];
						elseif ($carrier['extra_shipping_type'] == 1)
							$amount += $carrier['extra_shipping_amount'] * $orderTotal / 100;
						elseif ($carrier['extra_shipping_type'] == 3)
							$amount += $carrier['extra_shipping_amount'] * $amount / 100;
						$this->saveLog('dhl_rate_log.txt', "ret_amount === $ret_amount\n\r", $log);
						$ret_amount =  number_format($amount,2,".","");
					}
				}              
				if (!$method_found && !$method_valid)
				{
					$this->saveLog('dhl_rate_log.txt', "\n 24.5) no method found ".print_r($this_carrier, true).", $dest_zip, $dest_country \n\r", $log);
					$this->saveInvalidDestination($this_carrier, $dest_zip, $dest_country);
				}
			}
			// Check free shipping
			if ($carrier['free_shipping'] > 0 && $carrier['free_shipping'] <= $orderTotal)
			{
				return 0;
			}

			$this->saveLog('dhl_rate_log.txt', "\n 25) (== $id_carrier) ret_amount $ret_amount TOTAL TIME: ".(time() - $st)."\n\r", $log);

			return $ret_amount;
		}
		else
		{
                    echo '<pre>';
                    print_r($xml);
                    echo '</pre>';
                    die();
                    
			$this->saveInvalidDestination($carrier, $dest_zip, $dest_country);
			return false;
		}
	}

	protected function validateUSZip($zip)
	{
		$log = false;
		$date = date('Y-m-d');

		$error_code = null;
		do
		{
			$post_string =
		'<?xml version="1.0" encoding="UTF-8"?>
		<p:DCTRequest xmlns:p="http://www.dhl.com" xmlns:p1="http://www.dhl.com/datatypes" xmlns:p2="http://www.dhl.com/DCTRequestdatatypes" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com DCT-req.xsd ">
			<GetCapability>
				<Request>
					<ServiceHeader>
						<MessageTime>'.date('c').'</MessageTime>
						<MessageReference>'.$this->generateMessageReference().'</MessageReference>
						<SiteID>'.$this->_dhl_site_id.'</SiteID>
						<Password>'.$this->_dhl_pass.'</Password>
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
					<Postalcode>'.$zip.'</Postalcode>
				</To>
			</GetCapability>
		</p:DCTRequest>
		';

			$post_response = $this->curl_post($this->getServerURL(), $post_string);
			$xml = simplexml_load_string($post_response);

			$error_code = @$xml->GetCapabilityResponse->Note->Condition->ConditionCode;
			$date = date('Y-m-d', strtotime('+1 day', strtotime($date)));

		} while($error_code == '1003');

//		$this->saveLog(
//			'dhl_xml_log.txt',
//			date("D M j G:i:s").") Validate US Zip Request:\n$post_string\n------------------------------------------------------------------------------------------------\n\nResponse:\n ".print_r($xml,true).'\n\n\n',
//			$this->_dhl_xml_log
//		);
		$this->saveLog('dhl_validate_zip.txt', "\n 1) $post_response", $log);

		if (isset($xml->GetCapabilityResponse->Note->Condition->ConditionCode))
			return false;
		else
			return true;
	}

	protected function getOrderTotalWeight($id_cart, $id_product, $qty, $totalWeight)
	{
		if ($id_product == 0)
		{
			$result = Db::getInstance()->getRow('
				SELECT SUM((p.`weight` + pa.`weight`) * cp.`quantity`) as nb
				FROM `'._DB_PREFIX_.'cart_product` cp
				LEFT JOIN `'._DB_PREFIX_.'product` p ON cp.`id_product` = p.`id_product`
				LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON cp.`id_product_attribute` = pa.`id_product_attribute`
				WHERE (cp.`id_product_attribute` IS NOT NULL AND cp.`id_product_attribute` != 0)
				AND cp.`id_cart` = '.(int)($id_cart));
			$result2 = Db::getInstance()->getRow('
				SELECT SUM(p.`weight` * cp.`quantity`) as nb
				FROM `'._DB_PREFIX_.'cart_product` cp
				LEFT JOIN `'._DB_PREFIX_.'product` p ON cp.`id_product` = p.`id_product`
				WHERE (cp.`id_product_attribute` IS NULL OR cp.`id_product_attribute` = 0)
				AND cp.`id_cart` = '.(int)($id_cart));
			$totalWeight = round((float)($result['nb']) + (float)($result2['nb']), 3);
		}
		else
			$totalWeight *= $qty;
		// Convert Weight to lbs or kgs based on dhl default
		if ($this->_dhl_unit == 'LBS')
			$totalWeight = $this->getWeightInLb($totalWeight);
		else
			$totalWeight = $this->getWeightInKg($totalWeight);

		return $totalWeight;
	}

	protected function getTotal($product_price, $qty, $cart)
	{
		if ($product_price > 0)
			$orderTotal = $product_price * $qty;
		else
			$orderTotal = $cart->getOrderTotal(true, 7);

		return $orderTotal;
	}

	protected function getCustomerInfo($id_zone, $dest_zip, $dest_country, $dest_city, $cart)
	{
		$cookie_zip = $this->context->cookie->postcode ? $this->context->cookie->postcode :$this->context->cookie->pc_dest_zip;
		$cookie_country = $this->context->cookie->id_country ? $this->context->cookie->id_country :$this->context->cookie->pc_dest_country;
		
		// Check if customer is logged in, and cart has an address selected.
		if ($cart->id_address_delivery > 0 && $this->context->customer->logged)
		{
			$address = new Address(intval($cart->id_address_delivery));
			if (!Validate::isLoadedObject($address))
			{
				$id_address = Address::getFirstCustomerAddressId($cart->id_customer, true);
				if ($id_address > 0)
					$address = new Address(intval($id_address));
				if (!Validate::isLoadedObject($address))
					return false;
			}

			if ($dest_zip == "")
				$dest_zip = $address->postcode;

			$country = new Country($address->id_country);
			if ($dest_country == "")
				$dest_country = $country->iso_code;

			if($dest_city == "")
				$dest_city = $address->city;
		}
		else
		{
			if ($dest_zip == "" && $cookie_zip)
			{
				$dest_zip = $cookie_zip;
				$dest_city = $this->context->cookie->pc_dest_city;
			}
			else if ($dest_zip == "" && $this->_dhl_address_display['zip'] == 1)
				return false;

			if($dest_country == "" && $cookie_country)
			{
				$dest_country = $cookie_country;
				$country = new Country($dest_country);
				$dest_country = $country->iso_code;
			}
		}

		if((int)$id_zone == 0)
		{
			$id_country = $cookie_country;
			if((int)$id_country > 0)
				$id_zone = Country::getIdZone($id_country);
			if((int)$id_country == 0 OR (int)$id_zone == 0)
				return false;
		}

		return array(
			'dest_zip' => $dest_zip,
			'dest_country' => $dest_country,
			'dest_city' => $dest_city,
			'id_zone' => $id_zone,
		);
	}

	protected function checkInvalidDestination($carrier, $dest_zip, $dest_country, $log)
	{
		$cache_timeout = 180; // Seconds.
		$query = '
			SELECT *
			FROM `'._DB_PREFIX_.'fe_dhl_invalid_dest`
			WHERE method = "'.$carrier['method'].'" AND zip = "'.$dest_zip.'" AND country = "'.$dest_country.'" AND ondate > '.(time()-$cache_timeout);

		$this->saveLog('dhl_rate_log.txt', "\n\r9) Invalid query $query\n\r", $log);

		$invalid_dest = Db::getInstance()->executeS($query);

		return $invalid_dest;
	}

	protected function calculateInsurance($carrier, $orderTotal, $id_product, $products, $qty, $cart)
	{
		$iamount = 0;
		if ($carrier['insurance_type'] != 0)
		{
			if ($orderTotal >= $carrier['insurance_minimum'])
			{
				if ($id_product == 0)
				{
					if ($carrier['insurance_type'] == 1)
					{
						$order_total = 0;
						foreach ($products AS $product)
						{
							$pro = new Product($product['id_product']);
							$price = floatval($pro->wholesale_price);
							$total_price = $price * intval($product['cart_quantity']);
							$order_total += $total_price;
						}
						$iamount = $order_total;
					}
					else if ($carrier['insurance_type'] == 2)
						$iamount = floatval($carrier['insurance_amount']) * $orderTotal / 100;
				}
				else
				{
					$pro = new Product($id_product);
					if ($carrier['insurance_type'] == 1)
						$iamount = floatval($pro->wholesale_price) * $qty;
					else if ($carrier['insurance_type'] == 2)
						$iamount = floatval($carrier['insurance_amount']) * (floatval($pro->price) * $qty) / 100;
				}
				$iamount = number_format($iamount, 2, '.', '');
				if ($cart->id_currency != Configuration::get('PS_CURRENCY_DEFAULT'))
				{
					$currency = new Currency($cart->id_currency);
					$iamount = Tools::convertPrice($iamount, $currency);
				}
				if ($carrier['insurance_minimum'] > $orderTotal)
					$iamount = 0;
			}
		}

		return $iamount;
	}

	protected function saveInvalidDestination($carrier, $dest_zip, $dest_country)
	{
		// Delete any old records in cache.
		$query = '
			DELETE
			FROM `'._DB_PREFIX_.'fe_dhl_invalid_dest`
			WHERE method = "'.$carrier['method'].'" AND zip = "'.$dest_zip.'" AND country = "'.$dest_country.'"';
		Db::getInstance()->execute($query);
		// Add invalid zip / carrier to cache.
		$query = '
			INSERT INTO `'._DB_PREFIX_.'fe_dhl_invalid_dest`
			(method, zip, country, ondate)
			VALUES ("'.$carrier['method'].'","'.$dest_zip.'","'.$dest_country.'","'.time().'")
		';
		Db::getInstance()->execute($query);
	}

	protected function requestRate($pack_dim, $iamount, $dest_country, $dest_zip, $dest_city, $log, $carrier_method = false, $noRequest = false)
	{
//            $args = func_get_args();
//            echo '<pre>';
//            print_r($args);
//            echo '</pre>';
//            die();
//            
//            
		$message_reference = $this->generateMessageReference();
		$dimension_unit = $this->_dhl_unit == 'LBS' ? 'IN' : 'CM';
		$weight_unit = $this->_dhl_unit == 'LBS' ? 'LB' : 'KG';
		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
		$date = date('Y-m-d');
		
		$duatiablePacks = $this->dutiablePacks();
		$EUCountries = $this->EUCountries();
		
		if(!strlen($dest_country))
			$isDutiable = 'N';
		elseif($dest_country != $this->_dhl_origin_country && in_array($this->_dhl_pack, $duatiablePacks) === true)
		{
			if(in_array($this->_dhl_origin_country, $EUCountries) === true)
			{
				if(in_array($dest_country, $EUCountries) === true)
					$isDutiable = 'N';
				else
					$isDutiable = 'Y';                        
			}
			else
				$isDutiable = 'Y';
		}
		else
			$isDutiable = 'N';
			
		if($isDutiable == 'Y')
		{
			$cart = $this->context->cart;      
			
			$declaredValue = $cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);
			if(!$declaredValue && Tools::getValue('id_product'))
			{
				$product = new Product(Tools::getValue('id_product'));
				$declaredValue = $product->price;				
			}
			
			$declaredCurrency = new Currency($cart->id_currency);
			$declaredCurrency = $declaredCurrency->iso_code;  
		}

		$error_code = null;
		do
		{
			$post_string = '
            <?xml version="1.0" encoding="UTF-8"?>
			<p:DCTRequest xmlns:p="http://www.dhl.com" xmlns:p1="http://www.dhl.com/datatypes" xmlns:p2="http://www.dhl.com/DCTRequestdatatypes" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com DCT-req.xsd ">
				<GetQuote>
					<Request>
						<ServiceHeader>
							<MessageTime>'.date('c').'</MessageTime>
							<MessageReference>'.$message_reference.'</MessageReference>
							<SiteID>'.$this->_dhl_site_id.'</SiteID>
							<Password>'.$this->_dhl_pass.'</Password>
						</ServiceHeader>
					</Request>
					<From>
						<CountryCode>'.$this->_dhl_origin_country.'</CountryCode>
						<Postalcode>'.$this->_dhl_origin_zip.'</Postalcode>
						<City>'.$this->_dhl_origin_city.'</City>
					</From>
					<BkgDetails>
						<PaymentCountryCode>'.$this->_dhl_origin_country.'</PaymentCountryCode>
						<Date>'.$date.'</Date>
						<ReadyTime>PT10H21M</ReadyTime>
						<DimensionUnit>'.$dimension_unit.'</DimensionUnit>
						<WeightUnit>'.$weight_unit.'</WeightUnit>';
						
						if($carrier_method)
							$post_string .= '<ProductCode>'.$carrier_method.'</ProductCode>';	

						$post_string .= '
						<Pieces>';
						$counter = 1;
						foreach ($pack_dim as $pack) 
						{
							if($this->_dhl_pack == 'EE') //if express letter
							{
								$post_string .= '
								<Piece>
									<PieceID>'.$counter.'</PieceID>
									<Weight>'.$pack['weight'].'</Weight>
								</Piece>';
							}
							else {
								$post_string .= '
								<Piece>
									<PieceID>'.$counter.'</PieceID>
									<Height>'.$pack['h'].'</Height>
									<Depth>'.$pack['d'].'</Depth>
									<Width>'.$pack['w'].'</Width>
									<Weight>'.$pack['weight'].'</Weight>
								</Piece>';
							}
							$counter++;
						}
						$post_string .= '
						</Pieces>';

						if($this->_dhl_account_number AND strlen($this->_dhl_account_number))
						{
							$post_string .= '
								<PaymentAccountNumber>'.$this->_dhl_account_number.'</PaymentAccountNumber>
							';
						}

						$post_string .=
						'<IsDutiable>'.$isDutiable.'</IsDutiable>
						<NetworkTypeCode>AL</NetworkTypeCode>   
						<QtdShp>
						';                 
						if(strlen($this->_dhl_dropoff))
						{
							$post_string .= '
							<QtdShpExChrg>
								<SpecialServiceType>'.$this->_dhl_dropoff.'</SpecialServiceType>
							</QtdShpExChrg>
							';
						}
						$post_string .= '
						</QtdShp>';
						if($iamount > 0)
						{
							$post_string .= '  
							<InsuredValue>'.$iamount.'</InsuredValue>
							<InsuredCurrency>'.$currency->iso_code.'</InsuredCurrency>';
						}
						$post_string .= '
					</BkgDetails>
					<To>
						<CountryCode>'.$dest_country.'</CountryCode>
						<Postalcode>'.$dest_zip.'</Postalcode>
						'.($dest_city ? '<City>'.$dest_city.'</City>' : '').'
					</To>';
					
					if($isDutiable == 'Y')
					{
						$post_string .= '
							<Dutiable>
								<DeclaredCurrency>'.$declaredCurrency.'</DeclaredCurrency>
								<DeclaredValue>'.number_format($declaredValue, 3, '.', '').'</DeclaredValue>
							</Dutiable>
						';
					}
					
				$post_string .= '
				</GetQuote>
			</p:DCTRequest>';

			if($noRequest)
				return $post_string;
			
			$post_response = $this->curl_post($this->getServerURL(), $post_string);
			$this->saveLog('dhl_rate_log.txt', "23) ".$this->getServerURL()." = ".print_r($post_string,true)."\n", $log);
			$this->saveLog('dhl_rate_log.txt', "23) ".print_r($post_response,true)."\n", $log);
			if($post_response == 28) //timeout
			{
				$this->saveLog('dhl_rate_log.txt', "23) ".$this->getServerURL()." = ".print_r($post_string,true)."\n", $log);
				Configuration::updateValue('DHL_DOWN_TIME', time());
				return false;
			}

			$xml = simplexml_load_string($post_response);
			if (isset($xml->GetQuoteResponse))
				$error_code = @$xml->GetQuoteResponse->Note->Condition->ConditionCode;			
			$date = date('Y-m-d', strtotime('+1 day', strtotime($date)));

		} while($error_code == '1003'); //do while error will be non "Pick-up service is not provided on this day."

        if($xml !== false)
        {
            $this->saveLog(
                $this->_dhl_log_filename,
                '// ------- Request: '.$post_string."\n\r".
                '// ------- Response: '."\n\r".print_r($xml, true)."\n\r
                \n\r", 
                $this->_dhl_xml_log
            );
        }
        else
        {
            $this->saveLog($this->_dhl_log_filename, 
                '// ------- Server Error: '.$post_response.' - Please contact your hosting provider for more details'."\n\r",
            $this->_dhl_xml_log);
        }

		return $xml;
	}

	protected function calculateAmount($rateReply)
	{
		if(!$rateReply->ShippingCharge)
			return false;

		if($this->_dhl_currency_used == 'PULCL')
		{
			$amount = floatval($rateReply->QtdSInAdCur[1]->TotalAmount);
			$currency = $rateReply->QtdSInAdCur[1]->CurrencyCode;
		}
		elseif($this->_dhl_currency_used == 'BASEC')
		{
			$amount = floatval($rateReply->QtdSInAdCur[2]->TotalAmount);
			$currency = $rateReply->QtdSInAdCur[2]->CurrencyCode;
		}
		else
		{
			$amount = floatval($rateReply->QtdSInAdCur[0]->TotalAmount);
			$currency = $rateReply->QtdSInAdCur[0]->CurrencyCode;
		}

		return array($amount, $currency);
	}
}