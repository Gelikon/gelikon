<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Product extends ProductCore
{

	/**
	* Price calculation / Get product price
	*
	* @param integer $id_shop Shop id
	* @param integer $id_product Product id
	* @param integer $id_product_attribute Product attribute id
	* @param integer $id_country Country id
	* @param integer $id_state State id
	* @param integer $id_currency Currency id
	* @param integer $id_group Group id
	* @param integer $quantity Quantity Required for Specific prices : quantity discount application
	* @param boolean $use_tax with (1) or without (0) tax
	* @param integer $decimals Number of decimals returned
	* @param boolean $only_reduc Returns only the reduction amount
	* @param boolean $use_reduc Set if the returned amount will include reduction
	* @param boolean $with_ecotax insert ecotax in price output.
	* @param variable_reference $specific_price_output
	* 	If a specific price applies regarding the previous parameters, this variable is filled with the corresponding SpecificPrice object
	* @return float Product price
	**/
	public static function priceCalculation($id_shop, $id_product, $id_product_attribute, $id_country, $id_state, $zipcode, $id_currency,
		$id_group, $quantity, $use_tax, $decimals, $only_reduc, $use_reduc, $with_ecotax, &$specific_price, $use_group_reduction,
		$id_customer = 0, $use_customer_price = true, $id_cart = 0, $real_quantity = 0)
	{
		static $address = null;
		static $context = null;

		if ($address === null)
			$address = new Address();
		
		if ($context == null)
			$context = Context::getContext()->cloneContext();

		if ($id_shop !== null && $context->shop->id != (int)$id_shop)
			$context->shop = new Shop((int)$id_shop);
		
		if (!$use_customer_price)
			$id_customer = 0;

		if ($id_product_attribute === null)
			$id_product_attribute = Product::getDefaultAttribute($id_product);

		$cache_id = $id_product.'-'.$id_shop.'-'.$id_currency.'-'.$id_country.'-'.$id_state.'-'.$zipcode.'-'.$id_group.
			'-'.$quantity.'-'.$id_product_attribute.'-'.($use_tax?'1':'0').'-'.$decimals.'-'.($only_reduc?'1':'0').
			'-'.($use_reduc?'1':'0').'-'.$with_ecotax.'-'.$id_customer.'-'.(int)$use_group_reduction.'-'.(int)$id_cart.'-'.(int)$real_quantity;

		// reference parameter is filled before any returns
		$specific_price = SpecificPrice::getSpecificPrice(
			(int)$id_product,
			$id_shop,
			$id_currency,
			$id_country,
			$id_group,
			$quantity,
			$id_product_attribute,
			$id_customer,
			$id_cart,
			$real_quantity
		);
                
		if (isset(self::$_prices[$cache_id]))
			return self::$_prices[$cache_id];

		// fetch price & attribute price
		$cache_id_2 = $id_product.'-'.$id_shop;
		if (!isset(self::$_pricesLevel2[$cache_id_2]))
		{
			$sql = new DbQuery();
			$sql->select('product_shop.`price`, product_shop.`ecotax`');
			$sql->from('product', 'p');
			$sql->innerJoin('product_shop', 'product_shop', '(product_shop.id_product=p.id_product AND product_shop.id_shop = '.(int)$id_shop.')');
			$sql->where('p.`id_product` = '.(int)$id_product);
			if (Combination::isFeatureActive())
			{
				$sql->select('product_attribute_shop.id_product_attribute, product_attribute_shop.`price` AS attribute_price, product_attribute_shop.default_on');
				$sql->leftJoin('product_attribute', 'pa', 'pa.`id_product` = p.`id_product`');
				$sql->leftJoin('product_attribute_shop', 'product_attribute_shop', '(product_attribute_shop.id_product_attribute = pa.id_product_attribute AND product_attribute_shop.id_shop = '.(int)$id_shop.')');
			}
			else
				$sql->select('0 as id_product_attribute');

			$res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

			foreach ($res as $row)
			{
				$array_tmp = array(
					'price' => $row['price'], 
					'ecotax' => $row['ecotax'],
					'attribute_price' => (isset($row['attribute_price']) ? $row['attribute_price'] : null)
				);
				self::$_pricesLevel2[$cache_id_2][(int)$row['id_product_attribute']] = $array_tmp;
				
				if (isset($row['default_on']) && $row['default_on'] == 1)
					self::$_pricesLevel2[$cache_id_2][0] = $array_tmp;
			}
		}
		if (!isset(self::$_pricesLevel2[$cache_id_2][(int)$id_product_attribute]))
			return;

		$result = self::$_pricesLevel2[$cache_id_2][(int)$id_product_attribute];

		if (!$specific_price || $specific_price['price'] < 0)
			$price = (float)$result['price'];
		else
			$price = (float)$specific_price['price'];
		// convert only if the specific price is in the default currency (id_currency = 0)
		if (!$specific_price || !($specific_price['price'] >= 0 && $specific_price['id_currency']))
			$price = Tools::convertPrice($price, $id_currency);

		// Attribute price
		if (is_array($result) && (!$specific_price || !$specific_price['id_product_attribute'] || $specific_price['price'] < 0))
		{
			$attribute_price = Tools::convertPrice($result['attribute_price'] !== null ? (float)$result['attribute_price'] : 0, $id_currency);
			// If you want the default combination, please use NULL value instead
			if ($id_product_attribute !== false)
				$price += $attribute_price;
		}

		// Tax
		$address->id_country = $id_country;
		$address->id_state = $id_state;
		$address->postcode = $zipcode;

		$tax_manager = TaxManagerFactory::getManager($address, Product::getIdTaxRulesGroupByIdProduct((int)$id_product, $context));
		$product_tax_calculator = $tax_manager->getTaxCalculator();

		// Add Tax
		if ($use_tax)
			$price = $product_tax_calculator->addTaxes($price);

		// Reduction
		$specific_price_reduction = 0;
		if (($only_reduc || $use_reduc) && $specific_price)
		{
			if ($specific_price['reduction_type'] == 'amount')
			{
				$reduction_amount = $specific_price['reduction'];

				if (!$specific_price['id_currency'])
					$reduction_amount = Tools::convertPrice($reduction_amount, $id_currency);
				$specific_price_reduction = !$use_tax ? $product_tax_calculator->removeTaxes($reduction_amount) : $reduction_amount;
			}
			else
				$specific_price_reduction = $price * $specific_price['reduction'];
		}

		if ($use_reduc)
			$price -= $specific_price_reduction;

		// Group reduction
		if ($use_group_reduction)
		{
			$reduction_from_category = GroupReduction::getValueForProduct($id_product, $id_group);
                        if($context->customer->id){
                            $id_group = array();
                            $customer_groups = Db::getInstance()->executeS("SELECT id_group FROM ps_customer_group WHERE id_customer={$context->customer->id}"); 
                            foreach ($customer_groups as $row_group)
                                $id_group[] = $row_group['id_group'];
                            
                        }else
                            $id_group = array($id_group);
			if ($reduction_from_category !== false)
				$group_reduction = $price * (float)$reduction_from_category;
			else // apply group reduction if there is no group reduction for this category
				$group_reduction = (($reduc = Group::getReductionByIdGroup($id_group)) != 0) ? ($price * $reduc / 100) : 0;
		}
		else
			$group_reduction = 0;
		if ($only_reduc)
			return Tools::ps_round($group_reduction + $specific_price_reduction, $decimals);

		if ($use_reduc)
			$price -= $group_reduction;

		// Eco Tax
		if (($result['ecotax'] || isset($result['attribute_ecotax'])) && $with_ecotax)
		{
			$ecotax = $result['ecotax'];
			if (isset($result['attribute_ecotax']) && $result['attribute_ecotax'] > 0)
				$ecotax = $result['attribute_ecotax'];

			if ($id_currency)
				$ecotax = Tools::convertPrice($ecotax, $id_currency);
			if ($use_tax)
			{
				// reinit the tax manager for ecotax handling
				$tax_manager = TaxManagerFactory::getManager(
					$address,
					(int)Configuration::get('PS_ECOTAX_TAX_RULES_GROUP_ID')
				);
				$ecotax_tax_calculator = $tax_manager->getTaxCalculator();
				$price += $ecotax_tax_calculator->addTaxes($ecotax);
			}
			else
				$price += $ecotax;
		}
		$price = Tools::ps_round($price, $decimals);
		if ($price < 0)
			$price = 0;

		self::$_prices[$cache_id] = $price;
		return self::$_prices[$cache_id];
	}
}
?>
