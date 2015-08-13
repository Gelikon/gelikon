<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Cart extends CartCore
{
    	/**
	 * Get products grouped by package and by addresses to be sent individualy (one package = one shipping cost).
	 *
	 * @return array array(
	 *                   0 => array( // First address
	 *                       0 => array(  // First package
	 *                           'product_list' => array(...),
	 *                           'carrier_list' => array(...),
	 *                           'id_warehouse' => array(...),
	 *                       ),
	 *                   ),
	 *               );
	 * @todo Add avaibility check
	 */

    /**
	* Return cart weight
	* добавляет 50 грамм на упаковку
	* @return float Cart weight
	*/
	public function getTotalWeight($products = null)
	{

		if (!is_null($products))
		{
			$total_weight = 0;
			foreach ($products as $product)
			{
				if (!isset($product['weight_attribute']) || is_null($product['weight_attribute']))
					$total_weight += $product['weight'] * $product['cart_quantity'];
				else
					$total_weight += $product['weight_attribute'] * $product['cart_quantity'];
			}
			$total_weight+=0.05;
			return $total_weight;
		}

		if (!isset(self::$_totalWeight[$this->id]))
		{
			if (Combination::isFeatureActive())
				$weight_product_with_attribute = Db::getInstance()->getValue('
				SELECT SUM((p.`weight` + pa.`weight`) * cp.`quantity`) as nb
				FROM `'._DB_PREFIX_.'cart_product` cp
				LEFT JOIN `'._DB_PREFIX_.'product` p ON (cp.`id_product` = p.`id_product`)
				LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (cp.`id_product_attribute` = pa.`id_product_attribute`)
				WHERE (cp.`id_product_attribute` IS NOT NULL AND cp.`id_product_attribute` != 0)
				AND cp.`id_cart` = '.(int)$this->id);
			else
				$weight_product_with_attribute = 0;

			$weight_product_without_attribute = Db::getInstance()->getValue('
			SELECT SUM(p.`weight` * cp.`quantity`) as nb
			FROM `'._DB_PREFIX_.'cart_product` cp
			LEFT JOIN `'._DB_PREFIX_.'product` p ON (cp.`id_product` = p.`id_product`)
			WHERE (cp.`id_product_attribute` IS NULL OR cp.`id_product_attribute` = 0)
			AND cp.`id_cart` = '.(int)$this->id);

			self::$_totalWeight[$this->id] = round((float)$weight_product_with_attribute + (float)$weight_product_without_attribute, 3);
		}
		$result = self::$_totalWeight[$this->id]+0.05; //вес упаковки
		return $result;
	}
        
        public function getProducts($refresh = false, $id_product = false, $id_country = null)
	{
		if (!$this->id)
			return array();
		// Product cache must be strictly compared to NULL, or else an empty cart will add dozens of queries
		if ($this->_products !== null && !$refresh)
		{
			// Return product row with specified ID if it exists
			if (is_int($id_product))
			{
				foreach ($this->_products as $product)
					if ($product['id_product'] == $id_product)
						return array($product);
				return array();
			}
			return $this->_products;
		}

		// Build query
		$sql = new DbQuery();

		// Build SELECT
		$sql->select('cp.`id_product_attribute`, cp.`id_product`, cp.`quantity` AS cart_quantity, cp.id_shop, pl.`name`, p.`is_virtual`,
						pl.`description_short`, pl.`available_now`, pl.`available_later`, product_shop.`id_category_default`, p.`id_supplier`,
						p.`id_manufacturer`, product_shop.`on_sale`, product_shop.`ecotax`, product_shop.`additional_shipping_cost`,
						product_shop.`available_for_order`, product_shop.`price`, product_shop.`active`, product_shop.`unity`, product_shop.`unit_price_ratio`, 
						stock.`quantity` AS quantity_available, p.`width`, p.`height`, p.`depth`, stock.`out_of_stock`, p.`weight`,
						p.`date_add`, p.`date_upd`, IFNULL(stock.quantity, 0) as quantity, pl.`link_rewrite`, cl.`link_rewrite` AS category,
						CONCAT(LPAD(cp.`id_product`, 10, 0), LPAD(IFNULL(cp.`id_product_attribute`, 0), 10, 0), IFNULL(cp.`id_address_delivery`, 0)) AS unique_id, cp.id_address_delivery,
						product_shop.`wholesale_price`, product_shop.advanced_stock_management, ps.product_supplier_reference supplier_reference');

		// Build FROM
		$sql->from('cart_product', 'cp');

		// Build JOIN
		$sql->leftJoin('product', 'p', 'p.`id_product` = cp.`id_product`');
		$sql->innerJoin('product_shop', 'product_shop', '(product_shop.`id_shop` = cp.`id_shop` AND product_shop.`id_product` = p.`id_product`)');
		$sql->leftJoin('product_lang', 'pl', '
			p.`id_product` = pl.`id_product`
			AND pl.`id_lang` = '.(int)$this->id_lang.Shop::addSqlRestrictionOnLang('pl', 'cp.id_shop')
		);

		$sql->leftJoin('category_lang', 'cl', '
			product_shop.`id_category_default` = cl.`id_category`
			AND cl.`id_lang` = '.(int)$this->id_lang.Shop::addSqlRestrictionOnLang('cl', 'cp.id_shop')
		);

		$sql->leftJoin('product_supplier', 'ps', 'ps.`id_product` = cp.`id_product` AND ps.`id_product_attribute` = cp.`id_product_attribute` AND ps.`id_supplier` = p.`id_supplier`');

		// @todo test if everything is ok, then refactorise call of this method
		$sql->join(Product::sqlStock('cp', 'cp'));

		// Build WHERE clauses
		$sql->where('cp.`id_cart` = '.(int)$this->id);
		if ($id_product)
			$sql->where('cp.`id_product` = '.(int)$id_product);
		$sql->where('p.`id_product` IS NOT NULL');

		// Build GROUP BY
		$sql->groupBy('unique_id');

		// Build ORDER BY
		$sql->orderBy('p.`id_product`, cp.`id_product_attribute`, cp.`date_add` ASC');

		if (Customization::isFeatureActive())
		{
			$sql->select('cu.`id_customization`, cu.`quantity` AS customization_quantity');
			$sql->leftJoin('customization', 'cu',
				'p.`id_product` = cu.`id_product` AND cp.`id_product_attribute` = cu.`id_product_attribute` AND cu.`id_cart` = '.(int)$this->id);
		}
		else
			$sql->select('NULL AS customization_quantity, NULL AS id_customization');

		if (Combination::isFeatureActive())
		{
			$sql->select('
				product_attribute_shop.`price` AS price_attribute, product_attribute_shop.`ecotax` AS ecotax_attr,
				IF (IFNULL(pa.`reference`, \'\') = \'\', p.`reference`, pa.`reference`) AS reference,
				(p.`weight`+ pa.`weight`) weight_attribute,
				IF (IFNULL(pa.`ean13`, \'\') = \'\', p.`ean13`, pa.`ean13`) AS ean13,
				IF (IFNULL(pa.`upc`, \'\') = \'\', p.`upc`, pa.`upc`) AS upc,
				pai.`id_image` as pai_id_image, il.`legend` as pai_legend,
				IFNULL(product_attribute_shop.`minimal_quantity`, product_shop.`minimal_quantity`) as minimal_quantity
			');

			$sql->leftJoin('product_attribute', 'pa', 'pa.`id_product_attribute` = cp.`id_product_attribute`');
			$sql->leftJoin('product_attribute_shop', 'product_attribute_shop', '(product_attribute_shop.`id_shop` = cp.`id_shop` AND product_attribute_shop.`id_product_attribute` = pa.`id_product_attribute`)');
			$sql->leftJoin('product_attribute_image', 'pai', 'pai.`id_product_attribute` = pa.`id_product_attribute`');
			$sql->leftJoin('image_lang', 'il', 'il.`id_image` = pai.`id_image` AND il.`id_lang` = '.(int)$this->id_lang);
		}
		else
			$sql->select(
				'p.`reference` AS reference, p.`ean13`,
				p.`upc` AS upc, product_shop.`minimal_quantity` AS minimal_quantity'
			);
		$result = Db::getInstance()->executeS($sql);

		// Reset the cache before the following return, or else an empty cart will add dozens of queries
		$products_ids = array();
		$pa_ids = array();
		if ($result)
			foreach ($result as $row)
			{
				$products_ids[] = $row['id_product'];
				$pa_ids[] = $row['id_product_attribute'];
			}
		// Thus you can avoid one query per product, because there will be only one query for all the products of the cart
		Product::cacheProductsFeatures($products_ids);
		Cart::cacheSomeAttributesLists($pa_ids, $this->id_lang);

		$this->_products = array();
		if (empty($result))
			return array();

		$cart_shop_context = Context::getContext()->cloneContext();
		foreach ($result as &$row)
		{
                    
//                        $quantityDiscount = SpecificPrice::getQuantityDiscount((int)$row['id_product'], $row['id_shop'],
//			(int)$cart->id_currency, (int)$this->vat_address->id_country,
//			(int)$this->customer->id_default_group, (int)$row['cart_quantity'], false, null, null, $null, true, true, $this->context);
//                        
//                        echo '<pre>';
//                        print_r($quantityDiscount);
//                        echo '</pre>';
                     
                    
                    
			if (isset($row['ecotax_attr']) && $row['ecotax_attr'] > 0)
				$row['ecotax'] = (float)$row['ecotax_attr'];

			$row['stock_quantity'] = (int)$row['quantity'];
			// for compatibility with 1.2 themes
			$row['quantity'] = (int)$row['cart_quantity'];

			if (isset($row['id_product_attribute']) && (int)$row['id_product_attribute'] && isset($row['weight_attribute']))
				$row['weight'] = (float)$row['weight_attribute'];

			if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_invoice')
				$address_id = (int)$this->id_address_invoice;
			else
				$address_id = (int)$row['id_address_delivery'];
			if (!Address::addressExists($address_id))
				$address_id = null;

			if ($cart_shop_context->shop->id != $row['id_shop'])
				$cart_shop_context->shop = new Shop((int)$row['id_shop']);

			if ($this->_taxCalculationMethod == PS_TAX_EXC)
			{
				$row['price'] = Product::getPriceStatic(
					(int)$row['id_product'],
					false,
					isset($row['id_product_attribute']) ? (int)$row['id_product_attribute'] : null,
					2,
					null,
					false,
					true,
					(int)$row['cart_quantity'],
					false,
					((int)$this->id_customer ? (int)$this->id_customer : null),
					(int)$this->id,
					((int)$address_id ? (int)$address_id : null),
					$specific_price_output,
					true,
					true,
					$cart_shop_context
				); // Here taxes are computed only once the quantity has been applied to the product price

				$row['price_wt'] = Product::getPriceStatic(
					(int)$row['id_product'],
					true,
					isset($row['id_product_attribute']) ? (int)$row['id_product_attribute'] : null,
					2,
					null,
					false,
					true,
					(int)$row['cart_quantity'],
					false,
					((int)$this->id_customer ? (int)$this->id_customer : null),
					(int)$this->id,
					((int)$address_id ? (int)$address_id : null),
					$null,
					true,
					true,
					$cart_shop_context
				);

				$tax_rate = Tax::getProductTaxRate((int)$row['id_product'], (int)$address_id);

				$row['total_wt'] = Tools::ps_round($row['price'] * (float)$row['cart_quantity'] * (1 + (float)$tax_rate / 100), 2);
				$row['total'] = $row['price'] * (int)$row['cart_quantity'];
			}
			else
			{
				$row['price'] = Product::getPriceStatic(
					(int)$row['id_product'],
					false,
					(int)$row['id_product_attribute'],
					2,
					null,
					false,
					true,
					$row['cart_quantity'],
					false,
					((int)$this->id_customer ? (int)$this->id_customer : null),
					(int)$this->id,
					((int)$address_id ? (int)$address_id : null),
					$specific_price_output,
					true,
					true,
					$cart_shop_context
				);

				$row['price_wt'] = Product::getPriceStatic(
					(int)$row['id_product'],
					true,
					(int)$row['id_product_attribute'],
					2,
					null,
					false,
					true,
					$row['cart_quantity'],
					false,
					((int)$this->id_customer ? (int)$this->id_customer : null),
					(int)$this->id,
					((int)$address_id ? (int)$address_id : null),
					$null,
					true,
					true,
					$cart_shop_context
				);
				
				// In case when you use QuantityDiscount, getPriceStatic() can be return more of 2 decimals
				$row['price_wt'] = Tools::ps_round($row['price_wt'], 2);
				$row['total_wt'] = $row['price_wt'] * (int)$row['cart_quantity'];
				$row['total'] = Tools::ps_round($row['price'] * (int)$row['cart_quantity'], 2);
				$row['description_short'] = Tools::nl2br($row['description_short']);
			}

			if (!isset($row['pai_id_image']) || $row['pai_id_image'] == 0)
			{
				$cache_id = 'Cart::getProducts_'.'-pai_id_image-'.(int)$row['id_product'].'-'.(int)$this->id_lang.'-'.(int)$row['id_shop'];
				if (!Cache::isStored($cache_id))
				{ 
					$row2 = Db::getInstance()->getRow('
						SELECT image_shop.`id_image` id_image, il.`legend`
						FROM `'._DB_PREFIX_.'image` i
						JOIN `'._DB_PREFIX_.'image_shop` image_shop ON (i.id_image = image_shop.id_image AND image_shop.cover=1 AND image_shop.id_shop='.(int)$row['id_shop'].')
						LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$this->id_lang.')
						WHERE i.`id_product` = '.(int)$row['id_product'].' AND image_shop.`cover` = 1'
					);
					Cache::store($cache_id, $row2);
				}
				$row2 = Cache::retrieve($cache_id);
				if (!$row2)
					$row2 = array('id_image' => false, 'legend' => false);
				else
					$row = array_merge($row, $row2);
			}
			else
			{
				$row['id_image'] = $row['pai_id_image'];
				$row['legend'] = $row['pai_legend'];
			}

			$row['reduction_applies'] = ($specific_price_output && (float)$specific_price_output['reduction']);
			$row['quantity_discount_applies'] = ($specific_price_output && $row['cart_quantity'] >= (int)$specific_price_output['from_quantity']);
			$row['id_image'] = Product::defineProductImage($row, $this->id_lang);
			$row['allow_oosp'] = Product::isAvailableWhenOutOfStock($row['out_of_stock']);
			$row['features'] = Product::getFeaturesStatic((int)$row['id_product']);

			if (array_key_exists($row['id_product_attribute'].'-'.$this->id_lang, self::$_attributesLists))
				$row = array_merge($row, self::$_attributesLists[$row['id_product_attribute'].'-'.$this->id_lang]);
                        
                        if(Context::getContext()->language->id != 1)
                            $row['name'] =  Tools::rus2translit($row['name']);

			$row = Product::getTaxesInformations($row, $cart_shop_context);
			$this->_products[] = $row;
		}

		return $this->_products;
	}

	public function getPackageList($flush = false)
	{
		static $cache = array();
		if (isset($cache[(int)$this->id.'_'.(int)$this->id_address_delivery]) && $cache[(int)$this->id.'_'.(int)$this->id_address_delivery] !== false && !$flush)
			return $cache[(int)$this->id.'_'.(int)$this->id_address_delivery];

		$product_list = $this->getProducts();
		// Step 1 : Get product informations (warehouse_list and carrier_list), count warehouse
		// Determine the best warehouse to determine the packages
		// For that we count the number of time we can use a warehouse for a specific delivery address
		$warehouse_count_by_address = array();
		$warehouse_carrier_list = array();

		$stock_management_active = Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT');

		foreach ($product_list as &$product)
		{
			if ((int)$product['id_address_delivery'] == 0)
				$product['id_address_delivery'] = (int)$this->id_address_delivery;

			if (!isset($warehouse_count_by_address[$product['id_address_delivery']]))
				$warehouse_count_by_address[$product['id_address_delivery']] = array();

			$product['warehouse_list'] = array();

			if ($stock_management_active &&
				((int)$product['advanced_stock_management'] == 1 || Pack::usesAdvancedStockManagement((int)$product['id_product'])))
			{
				$warehouse_list = Warehouse::getProductWarehouseList($product['id_product'], $product['id_product_attribute'], $this->id_shop);
				if (count($warehouse_list) == 0)
					$warehouse_list = Warehouse::getProductWarehouseList($product['id_product'], $product['id_product_attribute']);
				// Does the product is in stock ?
				// If yes, get only warehouse where the product is in stock

				$warehouse_in_stock = array();
				$manager = StockManagerFactory::getManager();

				foreach ($warehouse_list as $key => $warehouse)
				{
					$product_real_quantities = $manager->getProductRealQuantities(
						$product['id_product'],
						$product['id_product_attribute'],
						array($warehouse['id_warehouse']),
						true
					);

					if ($product_real_quantities > 0 || Pack::isPack((int)$product['id_product']))
						$warehouse_in_stock[] = $warehouse;
				}

				if (!empty($warehouse_in_stock))
				{
					$warehouse_list = $warehouse_in_stock;
					$product['in_stock'] = true;
				}
				else
					$product['in_stock'] = false;
			}
			else
			{
				//simulate default warehouse
				$warehouse_list = array(0);
				$product['in_stock'] = StockAvailable::getQuantityAvailableByProduct($product['id_product'], $product['id_product_attribute']) > 0;
			}

			foreach ($warehouse_list as $warehouse)
			{
				if (!isset($warehouse_carrier_list[$warehouse['id_warehouse']]))
				{
					$warehouse_object = new Warehouse($warehouse['id_warehouse']);
					$warehouse_carrier_list[$warehouse['id_warehouse']] = $warehouse_object->getCarriers();
				}

				$product['warehouse_list'][] = $warehouse['id_warehouse'];
				if (!isset($warehouse_count_by_address[$product['id_address_delivery']][$warehouse['id_warehouse']]))
					$warehouse_count_by_address[$product['id_address_delivery']][$warehouse['id_warehouse']] = 0;

				$warehouse_count_by_address[$product['id_address_delivery']][$warehouse['id_warehouse']]++;
			}
		}
		unset($product);

		arsort($warehouse_count_by_address);

		// Step 2 : Group product by warehouse
		$grouped_by_warehouse = array();
		foreach ($product_list as &$product)
		{
			if (!isset($grouped_by_warehouse[$product['id_address_delivery']]))
				$grouped_by_warehouse[$product['id_address_delivery']] = array(
					'in_stock' => array(),
					'out_of_stock' => array(),
				);
			
			$product['carrier_list'] = array();
			$id_warehouse = 0;
			foreach ($warehouse_count_by_address[$product['id_address_delivery']] as $id_war => $val)
			{
				if (in_array((int)$id_war, $product['warehouse_list']))
				{
					$product['carrier_list'] = array_merge($product['carrier_list'], Carrier::getAvailableCarrierList(new Product($product['id_product']), $id_war, $product['id_address_delivery'], null, $this));
					if (!$id_warehouse)
						$id_warehouse = (int)$id_war;
				}
			}

			if (!isset($grouped_by_warehouse[$product['id_address_delivery']]['in_stock'][$id_warehouse]))
			{
				$grouped_by_warehouse[$product['id_address_delivery']]['in_stock'][$id_warehouse] = array();
				$grouped_by_warehouse[$product['id_address_delivery']]['out_of_stock'][$id_warehouse] = array();
			}

			if (!$this->allow_seperated_package)
				$key = 'in_stock';
			else
				$key = $product['in_stock'] ? 'in_stock' : 'out_of_stock';

			if (empty($product['carrier_list']))
				$product['carrier_list'] = array(0);

			$grouped_by_warehouse[$product['id_address_delivery']][$key][$id_warehouse][] = $product;
		}
		unset($product);

		// Step 3 : grouped product from grouped_by_warehouse by available carriers
		$grouped_by_carriers = array();
		foreach ($grouped_by_warehouse as $id_address_delivery => $products_in_stock_list)
		{
			if (!isset($grouped_by_carriers[$id_address_delivery]))
				$grouped_by_carriers[$id_address_delivery] = array(
					'in_stock' => array(),
					'out_of_stock' => array(),
				);
			foreach ($products_in_stock_list as $key => $warehouse_list)
			{
				if (!isset($grouped_by_carriers[$id_address_delivery][$key]))
					$grouped_by_carriers[$id_address_delivery][$key] = array();
				foreach ($warehouse_list as $id_warehouse => $product_list)
				{
					if (!isset($grouped_by_carriers[$id_address_delivery][$key][$id_warehouse]))
						$grouped_by_carriers[$id_address_delivery][$key][$id_warehouse] = array();
					foreach ($product_list as $product)
					{
						$package_carriers_key = implode(',', $product['carrier_list']);

						if (!isset($grouped_by_carriers[$id_address_delivery][$key][$id_warehouse][$package_carriers_key]))
							$grouped_by_carriers[$id_address_delivery][$key][$id_warehouse][$package_carriers_key] = array(
								'product_list' => array(),
								'carrier_list' => $product['carrier_list'],
								'warehouse_list' => $product['warehouse_list']
							);

						$grouped_by_carriers[$id_address_delivery][$key][$id_warehouse][$package_carriers_key]['product_list'][] = $product;
					}
				}
			}
		}

		$package_list = array();
		// Step 4 : merge product from grouped_by_carriers into $package to minimize the number of package
		foreach ($grouped_by_carriers as $id_address_delivery => $products_in_stock_list)
		{
			if (!isset($package_list[$id_address_delivery]))
				$package_list[$id_address_delivery] = array(
					'in_stock' => array(),
					'out_of_stock' => array(),
				);

			foreach ($products_in_stock_list as $key => $warehouse_list)
			{
				if (!isset($package_list[$id_address_delivery][$key]))
					$package_list[$id_address_delivery][$key] = array();
				// Count occurance of each carriers to minimize the number of packages
				$carrier_count = array();
				foreach ($warehouse_list as $id_warehouse => $products_grouped_by_carriers)
				{
					foreach ($products_grouped_by_carriers as $data)
					{
						foreach ($data['carrier_list'] as $id_carrier)
						{
							if (!isset($carrier_count[$id_carrier]))
								$carrier_count[$id_carrier] = 0;
							$carrier_count[$id_carrier]++;
						}
					}
				}
				arsort($carrier_count);
				foreach ($warehouse_list as $id_warehouse => $products_grouped_by_carriers)
				{
					if (!isset($package_list[$id_address_delivery][$key][$id_warehouse]))
						$package_list[$id_address_delivery][$key][$id_warehouse] = array();
					foreach ($products_grouped_by_carriers as $data)
					{
						foreach ($carrier_count as $id_carrier => $rate)
						{
							if (in_array($id_carrier, $data['carrier_list']))
							{
								if (!isset($package_list[$id_address_delivery][$key][$id_warehouse][$id_carrier]))
									$package_list[$id_address_delivery][$key][$id_warehouse][$id_carrier] = array(
										'carrier_list' => $data['carrier_list'],
										'warehouse_list' => $data['warehouse_list'],
										'product_list' => array(),
									);
								$package_list[$id_address_delivery][$key][$id_warehouse][$id_carrier]['carrier_list'] =
									array_intersect($package_list[$id_address_delivery][$key][$id_warehouse][$id_carrier]['carrier_list'], $data['carrier_list']);
								$package_list[$id_address_delivery][$key][$id_warehouse][$id_carrier]['product_list'] =
									array_merge($package_list[$id_address_delivery][$key][$id_warehouse][$id_carrier]['product_list'], $data['product_list']);

								break;
							}
						}
					}
				}
			}
		}

		// Step 5 : Reduce depth of $package_list
		$final_package_list = array();
		foreach ($package_list as $id_address_delivery => $products_in_stock_list)
		{
			if (!isset($final_package_list[$id_address_delivery]))
				$final_package_list[$id_address_delivery] = array();

			foreach ($products_in_stock_list as $key => $warehouse_list)
				foreach ($warehouse_list as $id_warehouse => $products_grouped_by_carriers)
					foreach ($products_grouped_by_carriers as $data)
					{
						$final_package_list[$id_address_delivery][] = array(
							'product_list' => $data['product_list'],
							'carrier_list' => $data['carrier_list'],
							'warehouse_list' => $data['warehouse_list'],
							'id_warehouse' => $id_warehouse,
						);
					}
		}
                
                //принудительно все на 1 склад чтоб не создавалось 2 заказа
                if(count($final_package_list[$this->id_address_delivery]) > 1){
                    foreach($final_package_list[$this->id_address_delivery] as $k => &$part){
                        if($k == 0)continue;
                        foreach($final_package_list[$this->id_address_delivery][$k]['product_list'] as $product_list_item)
                            $final_package_list[$this->id_address_delivery][0]['product_list'][] = $product_list_item;
                        unset($final_package_list[$this->id_address_delivery][$k]);
                       
                    }
                }
		$cache[(int)$this->id] = $final_package_list;
		return $final_package_list;
	}
        
        public function getDeliveryOptionList(Country $default_country = null, $flush = false)
	{
		static $cache = null;
		if ($cache !== null && !$flush)
			return $cache;

		$delivery_option_list = array();
		$carriers_price = array();
		$carrier_collection = array();
		$package_list = $this->getPackageList();
		// Foreach addresses
		foreach ($package_list as $id_address => $packages)
		{
			// Initialize vars
			$delivery_option_list[$id_address] = array();
			$carriers_price[$id_address] = array();
			$common_carriers = null;
			$best_price_carriers = array();
			$best_grade_carriers = array();
			$carriers_instance = array();
			
			// Get country
			if ($id_address)
			{
				$address = new Address($id_address);
				$country = new Country($address->id_country);
			}
			else
				$country = $default_country;

			// Foreach packages, get the carriers with best price, best position and best grade
			foreach ($packages as $id_package => $package)
			{
 
                            
				// No carriers available
				if (count($package['carrier_list']) == 1 && current($package['carrier_list']) == 0)
				{
                                    $package['carrier_list'] = array(103,96,97);
                                   
                                    
//					$cache = array();
//					return $cache;
				}

				$carriers_price[$id_address][$id_package] = array();

				// Get all common carriers for each packages to the same address
				if (is_null($common_carriers))
					$common_carriers = $package['carrier_list'];
				else
					$common_carriers = array_intersect($common_carriers, $package['carrier_list']);

				$best_price = null;
				$best_price_carrier = null;
				$best_grade = null;
				$best_grade_carrier = null;

				// Foreach carriers of the package, calculate his price, check if it the best price, position and grade
				foreach ($package['carrier_list'] as $id_carrier)
				{
					if (!isset($carriers_instance[$id_carrier]))
						$carriers_instance[$id_carrier] = new Carrier($id_carrier);

					$price_with_tax = $this->getPackageShippingCost($id_carrier, true, $country, $package['product_list']);
					$price_without_tax = $this->getPackageShippingCost($id_carrier, false, $country, $package['product_list']);
					if (is_null($best_price) || $price_with_tax < $best_price)
					{
						$best_price = $price_with_tax;
						$best_price_carrier = $id_carrier;
					}
					$carriers_price[$id_address][$id_package][$id_carrier] = array(
						'without_tax' => $price_without_tax,
						'with_tax' => $price_with_tax);

					$grade = $carriers_instance[$id_carrier]->grade;
					if (is_null($best_grade) || $grade > $best_grade)
					{
						$best_grade = $grade;
						$best_grade_carrier = $id_carrier;
					}
				}

				$best_price_carriers[$id_package] = $best_price_carrier;
				$best_grade_carriers[$id_package] = $best_grade_carrier;
			}

			// Reset $best_price_carrier, it's now an array
			$best_price_carrier = array();
			$key = '';

			// Get the delivery option with the lower price
			foreach ($best_price_carriers as $id_package => $id_carrier)
			{
				$key .= $id_carrier.',';
				if (!isset($best_price_carrier[$id_carrier]))
					$best_price_carrier[$id_carrier] = array(
						'price_with_tax' => 0,
						'price_without_tax' => 0,
						'package_list' => array(),
						'product_list' => array(),
					);
				$best_price_carrier[$id_carrier]['price_with_tax'] += $carriers_price[$id_address][$id_package][$id_carrier]['with_tax'];
				$best_price_carrier[$id_carrier]['price_without_tax'] += $carriers_price[$id_address][$id_package][$id_carrier]['without_tax'];
				$best_price_carrier[$id_carrier]['package_list'][] = $id_package;
				$best_price_carrier[$id_carrier]['product_list'] = array_merge($best_price_carrier[$id_carrier]['product_list'], $packages[$id_package]['product_list']);
				$best_price_carrier[$id_carrier]['instance'] = $carriers_instance[$id_carrier];
			}

			// Add the delivery option with best price as best price
			$delivery_option_list[$id_address][$key] = array(
				'carrier_list' => $best_price_carrier,
				'is_best_price' => true,
				'is_best_grade' => false,
				'unique_carrier' => (count($best_price_carrier) <= 1)
			);

			// Reset $best_grade_carrier, it's now an array
			$best_grade_carrier = array();
			$key = '';

			// Get the delivery option with the best grade
			foreach ($best_grade_carriers as $id_package => $id_carrier)
			{
				$key .= $id_carrier.',';
				if (!isset($best_grade_carrier[$id_carrier]))
					$best_grade_carrier[$id_carrier] = array(
						'price_with_tax' => 0,
						'price_without_tax' => 0,
						'package_list' => array(),
						'product_list' => array(),
					);
				$best_grade_carrier[$id_carrier]['price_with_tax'] += $carriers_price[$id_address][$id_package][$id_carrier]['with_tax'];
				$best_grade_carrier[$id_carrier]['price_without_tax'] += $carriers_price[$id_address][$id_package][$id_carrier]['without_tax'];
				$best_grade_carrier[$id_carrier]['package_list'][] = $id_package;
				$best_grade_carrier[$id_carrier]['product_list'] = array_merge($best_grade_carrier[$id_carrier]['product_list'], $packages[$id_package]['product_list']);
				$best_grade_carrier[$id_carrier]['instance'] = $carriers_instance[$id_carrier];
			}
			
			// Add the delivery option with best grade as best grade
			if (!isset($delivery_option_list[$id_address][$key]))
				$delivery_option_list[$id_address][$key] = array(
					'carrier_list' => $best_grade_carrier,
					'is_best_price' => false,
					'unique_carrier' => (count($best_grade_carrier) <= 1)
				);
			$delivery_option_list[$id_address][$key]['is_best_grade'] = true;

			// Get all delivery options with a unique carrier
			foreach ($common_carriers as $id_carrier)
			{
				$key = '';
				$package_list = array();
				$product_list = array();
				$price_with_tax = 0;
				$price_without_tax = 0;

				foreach ($packages as $id_package => $package)
				{
					$key .= $id_carrier.',';
					$price_with_tax += $carriers_price[$id_address][$id_package][$id_carrier]['with_tax'];
					$price_without_tax += $carriers_price[$id_address][$id_package][$id_carrier]['without_tax'];
					$package_list[] = $id_package;
					$product_list = array_merge($product_list, $package['product_list']);
				}

				if (!isset($delivery_option_list[$id_address][$key]))
					$delivery_option_list[$id_address][$key] = array(
						'is_best_price' => false,
						'is_best_grade' => false,
						'unique_carrier' => true,
						'carrier_list' => array(
							$id_carrier => array(
								'price_with_tax' => $price_with_tax,
								'price_without_tax' => $price_without_tax,
								'instance' => $carriers_instance[$id_carrier],
								'package_list' => $package_list,
								'product_list' => $product_list,
							)
						)
					);
				else
					$delivery_option_list[$id_address][$key]['unique_carrier'] = (count($delivery_option_list[$id_address][$key]['carrier_list']) <= 1);
			}
		}

		$cart_rules = CartRule::getCustomerCartRules(Context::getContext()->cookie->id_lang, Context::getContext()->cookie->id_customer, true);

		$free_carriers_rules = array();
		foreach ($cart_rules as $cart_rule)
		{
			if ($cart_rule['free_shipping'] && $cart_rule['carrier_restriction'])
			{
				$cr = new CartRule((int)$cart_rule['id_cart_rule']);
				if (Validate::isLoadedObject($cr))
				{
					$carriers = $cr->getAssociatedRestrictions('carrier', true, false);
					if (is_array($carriers) && count($carriers) && isset($carriers['selected']))
						foreach($carriers['selected'] as $carrier)
							if (isset($carrier['id_carrier']) && $carrier['id_carrier'])
								$free_carriers_rules[] = (int)$carrier['id_carrier'];
				}
			}
		}
                
                
                $product_stock_1 = false;$product_stock_2 = false;$custom = false;
                
                $stock_management_active = Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT');
                if($stock_management_active){
                     $products = $this->getProducts();
                     foreach ($products as $product){
                        $flag = false;
                        $warehouse_list = Warehouse::getProductWarehouseList($product['id_product'], $product['id_product_attribute']);
                        $manager = StockManagerFactory::getManager();
                        foreach ($warehouse_list as $key => $warehouse)
                        {
                            $product_real_quantities = $manager->getProductRealQuantities(
                                    $product['id_product'],
                                    $product['id_product_attribute'],
                                    array($warehouse['id_warehouse']),
                                    true
                            );
                            if($key == 0 && $product_real_quantities > 0){
                                $flag = true;
                                $product_stock_1 = true;
                            }
                            if($key == 1 && $product_real_quantities > 0){
                                $product_stock_2 = true;
                                $flag = true;
                            }
                        }
                        if(!$flag){
                            $custom = true;
                            break;
                        }
                    }
                }
                $langs = Language::getLanguages();

		// For each delivery options :
		//    - Set the carrier list
		//    - Calculate the price
		//    - Calculate the average position
		foreach ($delivery_option_list as $id_address => $delivery_option)
			foreach ($delivery_option as $key => $value)
			{
				$total_price_with_tax = 0;
				$total_price_without_tax = 0;
				$position = 0;
				foreach ($value['carrier_list'] as $id_carrier => $data)
				{
					$total_price_with_tax += $data['price_with_tax'];
					$total_price_without_tax += $data['price_without_tax'];
					$total_price_without_tax_with_rules = (in_array($id_carrier, $free_carriers_rules)) ? 0 : $total_price_without_tax ;

					if (!isset($carrier_collection[$id_carrier]))
						$carrier_collection[$id_carrier] = new Carrier($id_carrier);
                                        switch(true){
                                            case ($custom):
                                                if($carrier_collection[$id_carrier]->name == 'Самовывоз в Шарлотенбурге'){
                                                    foreach ($langs as $lang){
                                                        $carrier_collection[$id_carrier]->delay[$lang['id_lang']] = '2-3 недели';
                                                    }
                                                }
                                                if($carrier_collection[$id_carrier]->name == 'Самовывоз в Русском доме'){
                                                    foreach ($langs as $lang){
                                                        $carrier_collection[$id_carrier]->delay[$lang['id_lang']] = '2-3 недели';
                                                    }
                                                }
                                                break;
                                            case (!$custom && ($product_stock_1 && $product_stock_2)):
                                                if($carrier_collection[$id_carrier]->name == 'Самовывоз в Шарлотенбурге'){
                                                    foreach ($langs as $lang){
                                                        $carrier_collection[$id_carrier]->delay[$lang['id_lang']] = '2 дня';
                                                    }
                                                }
                                                if($carrier_collection[$id_carrier]->name == 'Самовывоз в Русском доме'){
                                                    foreach ($langs as $lang){
                                                        $carrier_collection[$id_carrier]->delay[$lang['id_lang']] = '2 дня';
                                                    }
                                                }
                                                break;
                                            case (!$custom && (!$product_stock_1 && $product_stock_2)):
                                                if($carrier_collection[$id_carrier]->name == 'Самовывоз в Шарлотенбурге'){
                                                    foreach ($langs as $lang){
                                                        $carrier_collection[$id_carrier]->delay[$lang['id_lang']] = '2 дня';
                                                    }
                                                }
                                                if($carrier_collection[$id_carrier]->name == 'Самовывоз в Русском доме'){
                                                    foreach ($langs as $lang){
                                                        $carrier_collection[$id_carrier]->delay[$lang['id_lang']] = '1 день';
                                                    }
                                                }
                                                break;
                                            case (!$custom && ($product_stock_1 && !$product_stock_2)):
                                                if($carrier_collection[$id_carrier]->name == 'Самовывоз в Шарлотенбурге'){
                                                    foreach ($langs as $lang){
                                                        $carrier_collection[$id_carrier]->delay[$lang['id_lang']] = '1 день';
                                                    }
                                                }
                                                if($carrier_collection[$id_carrier]->name == 'Самовывоз в Русском доме'){
                                                    foreach ($langs as $lang){
                                                        $carrier_collection[$id_carrier]->delay[$lang['id_lang']] = '2 дня';
                                                    }
                                                }
                                                break;
                                        }

                                        
					$delivery_option_list[$id_address][$key]['carrier_list'][$id_carrier]['instance'] = $carrier_collection[$id_carrier];

					if (file_exists(_PS_SHIP_IMG_DIR_.$id_carrier.'.jpg'))
						$delivery_option_list[$id_address][$key]['carrier_list'][$id_carrier]['logo'] = _THEME_SHIP_DIR_.$id_carrier.'.jpg';
					else
						$delivery_option_list[$id_address][$key]['carrier_list'][$id_carrier]['logo'] = false;
					
					$position += $carrier_collection[$id_carrier]->position;
				}
				$delivery_option_list[$id_address][$key]['total_price_with_tax'] = $total_price_with_tax;
				$delivery_option_list[$id_address][$key]['total_price_without_tax'] = $total_price_without_tax;
				$delivery_option_list[$id_address][$key]['is_free'] = !$total_price_without_tax_with_rules ? true : false;
				$delivery_option_list[$id_address][$key]['position'] = $position / count($value['carrier_list']);
			}

		// Sort delivery option list
		foreach ($delivery_option_list as &$array)
			uasort ($array, array('Cart', 'sortDeliveryOptionList'));

		$cache = $delivery_option_list;
		return $delivery_option_list;
	}
        
        
        public function simulateCarriersOutput(Country $default_country = null, $flush = false)
	{
		static $cache = false;
		if ($cache !== false && !$flush)
			return $cache;

		$delivery_option_list = $this->getDeliveryOptionList($default_country, $flush);

		// This method cannot work if there is multiple address delivery
		if (count($delivery_option_list) > 1 || empty($delivery_option_list))
			return array();

		$carriers = array();
		foreach (reset($delivery_option_list) as $key => $option)
		{
			$price = $option['total_price_with_tax'];
			$price_tax_exc = $option['total_price_without_tax'];

			if ($option['unique_carrier'])
			{
				$carrier = reset($option['carrier_list']);
				$name = $carrier['instance']->name;
				$img = $carrier['logo'];
				$delay = $carrier['instance']->delay;
				$delay = isset($delay[Context::getContext()->language->id]) ? $delay[Context::getContext()->language->id] : $delay[(int)Configuration::get('PS_LANG_DEFAULT')];
			}
			else
			{
				$nameList = array();
				foreach ($option['carrier_list'] as $carrier)
					$nameList[] = $carrier['instance']->name;
				$name = join(' -', $nameList);
				$img = ''; // No images if multiple carriers
				$delay = '';
			}
			$carriers[] = array(
				'name' => $name,
				'img' => $img,
				'delay' => $delay,
				'price' => $price,
				'price_tax_exc' => $price_tax_exc,
				'id_carrier' => Cart::intifier($key), // Need to translate to an integer for retrocompatibility reason, in 1.4 template we used intval
				'is_module' => false,
			);
		}
		return $carriers;
	}
    
}

?>
