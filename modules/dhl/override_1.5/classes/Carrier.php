<?php

class Carrier extends CarrierCore
{
	public static function getAvailableCarrierList(Product $product, $id_warehouse, $id_address_delivery = null, $id_shop = null, $cart = null)
	{
		if (is_null($id_shop))
			$id_shop = Context::getContext()->shop->id;
		if (is_null($cart))
			$cart = Context::getContext()->cart;

		$id_address = (int)((!is_null($id_address_delivery) && $id_address_delivery != 0) ? $id_address_delivery :  $cart->id_address_delivery);
		if ($id_address)
		{
			$address = new Address($id_address);
			$id_zone = Address::getZoneById($address->id);

			// Check the country of the address is activated
			if (!Address::isCountryActiveById($address->id))
				return array();
		}
		else
		{
			// changed for Presto-Changeo carrier modules --->
			$cookie = Context::getContext()->cookie;
			$cookie_country = $cookie->id_country ? $cookie->id_country : $cookie->pc_dest_country;
			$country = new Country((isset($cookie_country) && strlen($cookie_country) ? $cookie_country : Configuration::get('PS_COUNTRY_DEFAULT')));
			// <--- changed for Presto-Changeo carrier modules
			$id_zone = $country->id_zone;
		}

		// Does the product is linked with carriers?
		$query = new DbQuery();
		$query->select('id_carrier');
		$query->from('product_carrier', 'pc');
		$query->innerJoin('carrier', 'c', 'c.id_reference = pc.id_carrier_reference AND c.deleted = 0');
		$query->where('pc.id_product = '.(int)$product->id);
		$query->where('pc.id_shop = '.(int)$id_shop);

		$cache_id = 'Carrier::getAvailableCarrierList_'.(int)$product->id.'-'.(int)$id_shop;
		if (!Cache::isStored($cache_id))
		{
			$carriers_for_product = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
			Cache::store($cache_id, $carriers_for_product);
		}
		$carriers_for_product = Cache::retrieve($cache_id);
		$carrier_list = array();
		if (!empty($carriers_for_product))
		{
			//the product is linked with carriers
			foreach ($carriers_for_product as $carrier) //check if the linked carriers are available in current zone
				if (Carrier::checkCarrierZone($carrier['id_carrier'], $id_zone))
					$carrier_list[] = $carrier['id_carrier'];
			if (empty($carrier_list))
				return array();//no linked carrier are available for this zone
		}

		// The product is not dirrectly linked with a carrier
		// Get all the carriers linked to a warehouse
		if ($id_warehouse)
		{
			$warehouse = new Warehouse($id_warehouse);
			$warehouse_carrier_list = $warehouse->getCarriers();
		}

		$available_carrier_list = array();
		$customer = new Customer($cart->id_customer);
		$carriers = Carrier::getCarriersForOrder($id_zone, $customer->getGroups(), $cart);

		foreach ($carriers as $carrier)
			$available_carrier_list[] = $carrier['id_carrier'];

		if ($carrier_list)
			$carrier_list = array_intersect($available_carrier_list, $carrier_list);
		else
			$carrier_list = $available_carrier_list;

		if (isset($warehouse_carrier_list))
			$carrier_list = array_intersect($carrier_list, $warehouse_carrier_list);

		if ($product->width > 0 || $product->height > 0 || $product->depth > 0 || $product->weight > 0)
		{
			foreach ($carrier_list as $key => $id_carrier)
			{
				$carrier = new Carrier($id_carrier);
				if (($carrier->max_width > 0 && $carrier->max_width < $product->width)
					|| ($carrier->max_height > 0 && $carrier->max_height < $product->height)
					|| ($carrier->max_depth > 0 && $carrier->max_depth < $product->depth)
					|| ($carrier->max_weight > 0 && $carrier->max_weight < $product->weight))
					unset($carrier_list[$key]);
			}
		}
		return $carrier_list;
	}
}

