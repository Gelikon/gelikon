<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Product extends ProductCore
{
    
        public static function getFrontFeaturesStatic($id_lang, $id_product)
	{
		if (!Feature::isFeatureActive())
			return array();
		if (!array_key_exists($id_product.'-'.$id_lang, self::$_frontFeaturesCache))
		{
			self::$_frontFeaturesCache[$id_product.'-'.$id_lang] = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
				SELECT name, value, pf.id_feature
				FROM '._DB_PREFIX_.'feature_product pf
				LEFT JOIN '._DB_PREFIX_.'feature_lang fl ON (fl.id_feature = pf.id_feature AND fl.id_lang = '.(int)$id_lang.')
				LEFT JOIN '._DB_PREFIX_.'feature_value_lang fvl ON (fvl.id_feature_value = pf.id_feature_value AND fvl.id_lang = '.(int)$id_lang.')
				LEFT JOIN '._DB_PREFIX_.'feature f ON (f.id_feature = pf.id_feature AND fl.id_lang = '.(int)$id_lang.')
				'.Shop::addSqlAssociation('feature', 'f').'
				WHERE pf.id_product = '.(int)$id_product.'
				ORDER BY f.position ASC'
			);
		}
                $features = self::$_frontFeaturesCache[$id_product.'-'.$id_lang];
                foreach($features as &$feature)
                    if($feature['id_feature'] == 9 && Context::getContext()->language->id != 1)
                        $feature['value'] = Tools::rus2translit($feature['value']);
                return $features; 
                
		//return self::$_frontFeaturesCache[$id_product.'-'.$id_lang];
	}
    
    	public static function getProductProperties($id_lang, $row, Context $context = null)
	{
            die('234');
		if (!$row['id_product'])
			return false;

		if ($context == null)
			$context = Context::getContext();

		// Product::getDefaultAttribute is only called if id_product_attribute is missing from the SQL query at the origin of it:
		// consider adding it in order to avoid unnecessary queries
		$row['allow_oosp'] = Product::isAvailableWhenOutOfStock($row['out_of_stock']);
		if (Combination::isFeatureActive() && (!isset($row['id_product_attribute']) || !$row['id_product_attribute'])
			&& ((isset($row['cache_default_attribute']) && ($ipa_default = $row['cache_default_attribute']) !== null)
				|| ($ipa_default = Product::getDefaultAttribute($row['id_product'], !$row['allow_oosp']))))
			$row['id_product_attribute'] = $ipa_default;
		if (!Combination::isFeatureActive() || !isset($row['id_product_attribute']))
			$row['id_product_attribute'] = 0;
                
                if($context->language->id != 1){
                    $row['meta_title'] = Tools::rus2translit($row['meta_title']);
                    $row['name'] = Tools::rus2translit($row['name']);
                }

		// Tax
		$usetax = Tax::excludeTaxeOption();

		$cache_key = $row['id_product'].'-'.$row['id_product_attribute'].'-'.$id_lang.'-'.(int)$usetax;
		if (isset($row['id_product_pack']))
			$cache_key .= '-pack'.$row['id_product_pack'];

		if (isset(self::$producPropertiesCache[$cache_key]))
			return array_merge($row, self::$producPropertiesCache[$cache_key]);

		// Datas
		$row['category'] = Category::getLinkRewrite((int)$row['id_category_default'], (int)$id_lang);
		$row['link'] = $context->link->getProductLink((int)$row['id_product'], $row['link_rewrite'], $row['category'], $row['ean13']);

		$row['attribute_price'] = 0;
		if (isset($row['id_product_attribute']) && $row['id_product_attribute'])
			$row['attribute_price'] = (float)Product::getProductAttributePrice($row['id_product_attribute']);

		$row['price_tax_exc'] = Product::getPriceStatic(
			(int)$row['id_product'],
			false,
			((isset($row['id_product_attribute']) && !empty($row['id_product_attribute'])) ? (int)$row['id_product_attribute'] : null),
			(self::$_taxCalculationMethod == PS_TAX_EXC ? 2 : 6)
		);

		if (self::$_taxCalculationMethod == PS_TAX_EXC)
		{
			$row['price_tax_exc'] = Tools::ps_round($row['price_tax_exc'], 2);
			$row['price'] = Product::getPriceStatic(
				(int)$row['id_product'],
				true,
				((isset($row['id_product_attribute']) && !empty($row['id_product_attribute'])) ? (int)$row['id_product_attribute'] : null),
				6
			);
			$row['price_without_reduction'] = Product::getPriceStatic(
				(int)$row['id_product'],
				false,
				((isset($row['id_product_attribute']) && !empty($row['id_product_attribute'])) ? (int)$row['id_product_attribute'] : null),
				2,
				null,
				false,
				false
			);
		}
		else
		{
			$row['price'] = Tools::ps_round(
				Product::getPriceStatic(
					(int)$row['id_product'],
					true,
					((isset($row['id_product_attribute']) && !empty($row['id_product_attribute'])) ? (int)$row['id_product_attribute'] : null),
					2
				),
				2
			);

			$row['price_without_reduction'] = Product::getPriceStatic(
				(int)$row['id_product'],
				true,
				((isset($row['id_product_attribute']) && !empty($row['id_product_attribute'])) ? (int)$row['id_product_attribute'] : null),
				6,
				null,
				false,
				false
			);
		}

		$row['reduction'] = Product::getPriceStatic(
			(int)$row['id_product'],
			(bool)$usetax,
			(int)$row['id_product_attribute'],
			6,
			null,
			true,
			true,
			1,
			true,
			null,
			null,
			null,
			$specific_prices
		);

		$row['specific_prices'] = $specific_prices;

		$row['quantity'] = Product::getQuantity(
			(int)$row['id_product'],
			0,
			isset($row['cache_is_pack']) ? $row['cache_is_pack'] : null
		);

		$row['quantity_all_versions'] = $row['quantity'];

		if ($row['id_product_attribute'])
			$row['quantity'] = Product::getQuantity(
				(int)$row['id_product'],
    			$row['id_product_attribute'],
			   isset($row['cache_is_pack']) ? $row['cache_is_pack'] : null
			);

		$row['id_image'] = Product::defineProductImage($row, $id_lang);
		$row['features'] = Product::getFrontFeaturesStatic((int)$id_lang, $row['id_product']);
                foreach ($row['features'] as &$feature)
                    if($feature['id_feature'] == 9  && $context->language->id != 1)
                        $feature['value'] = $this->rus2translit($feature['value']);
                    
                echo '<pre>';
                print_r($row['features']);
                echo '</pre>';
                die('111');
                    
                
		$row['attachments'] = array();
		if (!isset($row['cache_has_attachments']) || $row['cache_has_attachments'])
			$row['attachments'] = Product::getAttachmentsStatic((int)$id_lang, $row['id_product']);

		$row['virtual'] = ((!isset($row['is_virtual']) || $row['is_virtual']) ? 1 : 0);

		// Pack management
		$row['pack'] = (!isset($row['cache_is_pack']) ? Pack::isPack($row['id_product']) : (int)$row['cache_is_pack']);
		$row['packItems'] = $row['pack'] ? Pack::getItemTable($row['id_product'], $id_lang) : array();
		$row['nopackprice'] = $row['pack'] ? Pack::noPackPrice($row['id_product']) : 0;
		if ($row['pack'] && !Pack::isInStock($row['id_product']))
			$row['quantity'] = 0;

		$row = Product::getTaxesInformations($row, $context);
		self::$producPropertiesCache[$cache_key] = $row;
		return self::$producPropertiesCache[$cache_key];
	}
    
}
?>
