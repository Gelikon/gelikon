<?php
/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class Search extends SearchCore
{
    	public static function find($id_lang, $expr, $page_number = 1, $page_size = 1, $order_by = 'quantity',
		$order_way = 'desc', $ajax = false, $use_cookie = true, Context $context = null)
	{
                if(isset($_GET['id_category_search']) && $_GET['id_category_search'] != 'all')
                    $id_category_search = $_GET['id_category_search'];
                else if($_GET['id_category_search'] == 'authors')
                    $id_category_search  = 'authors';
                else
                    $id_category_search = null;

		if (!$context)
			$context = Context::getContext();
		$db = Db::getInstance(_PS_USE_SQL_SLAVE_);

		// TODO : smart page management
		if ($page_number < 1) $page_number = 1;
		if ($page_size < 1) $page_size = 1;

		if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way))
			return false;

                if($id_category_search != 'authors'){

                    $intersect_array = array();
                    $score_array = array();
                    $words = explode(' ', Search::sanitize($expr, $id_lang, false, $context->language->iso_code));

                    foreach ($words as $key => $word)
                            if (!empty($word) && strlen($word) >= (int)Configuration::get('PS_SEARCH_MINWORDLEN'))
                            {
                                    $word = str_replace('%', '\\%', $word);
                                    $word = str_replace('_', '\\_', $word);
                                    $intersect_array[] = 'SELECT si.id_product
                                            FROM '._DB_PREFIX_.'search_word sw
                                            LEFT JOIN '._DB_PREFIX_.'search_index si ON sw.id_word = si.id_word
                                            WHERE sw.id_lang = '.(int)$id_lang.'
                                                    AND sw.id_shop = '.$context->shop->id.'
                                                    AND sw.word LIKE
                                            '.($word[0] == '-'
                                                    ? ' \''.pSQL(Tools::substr($word, 1, PS_SEARCH_MAX_WORD_LENGTH)).'%\''
                                                    : '\''.pSQL(Tools::substr($word, 0, PS_SEARCH_MAX_WORD_LENGTH)).'%\''
                                            );

                                    if ($word[0] != '-')
                                            $score_array[] = 'sw.word LIKE \''.pSQL(Tools::substr($word, 0, PS_SEARCH_MAX_WORD_LENGTH)).'%\'';
                            }
                            else
                                    unset($words[$key]);

                    if (!count($words))
                            return ($ajax ? array() : array('total' => 0, 'result' => array()));

                    $score = '';
                    if (count($score_array))
                            $score = ',(
                                    SELECT SUM(weight)
                                    FROM '._DB_PREFIX_.'search_word sw
                                    LEFT JOIN '._DB_PREFIX_.'search_index si ON sw.id_word = si.id_word
                                    WHERE sw.id_lang = '.(int)$id_lang.'
                                            AND sw.id_shop = '.$context->shop->id.'
                                            AND si.id_product = p.id_product
                                            AND ('.implode(' OR ', $score_array).')
                            ) quantity';

                    $sql_groups = '';
                    if (Group::isFeatureActive())
                    {
                            $groups = FrontController::getCurrentCustomerGroups();
                            $sql_groups = 'AND cg.`id_group` '.(count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1');
                    }

                    $results = $db->executeS('
                    SELECT cp.`id_product`
                    FROM `'._DB_PREFIX_.'category_product` cp
                    '.(Group::isFeatureActive() ? 'INNER JOIN `'._DB_PREFIX_.'category_group` cg ON cp.`id_category` = cg.`id_category`' : '').'
                    INNER JOIN `'._DB_PREFIX_.'category` c ON cp.`id_category` = c.`id_category`
                    INNER JOIN `'._DB_PREFIX_.'product` p ON cp.`id_product` = p.`id_product`
                    '.Shop::addSqlAssociation('product', 'p', false).'
                    WHERE c.`active` = 1
                    AND product_shop.`active` = 1
                    AND product_shop.`visibility` IN ("both", "search")
                    AND product_shop.indexed = 1
                    '.$sql_groups);
                }else{
                    $ids_feature_value = array();
                    $search_query = mysql_escape_string($_GET['search_query']);
                    $sql = "SELECT id_feature_value FROM "._DB_PREFIX_."feature_value_lang WHERE value LIKE '%{$search_query}%' AND id_lang={$context->cookie->id_lang}";
                    $results_feature = $db->executeS($sql);
                    if(!is_array($results_feature) && count($results_feature) > 0)
                        return '';
                    
                    foreach ($results_feature as $row)
                        $ids_feature_value[] = $row['id_feature_value'];
                   
                    $results = $db->executeS('
                    SELECT cp.`id_product`
                    FROM `'._DB_PREFIX_.'category_product` cp
                    LEFT JOIN '._DB_PREFIX_.'feature_product fp ON(fp.id_product=cp.id_product)    
                    '.(Group::isFeatureActive() ? 'INNER JOIN `'._DB_PREFIX_.'category_group` cg ON cp.`id_category` = cg.`id_category`' : '').'
                    INNER JOIN `'._DB_PREFIX_.'category` c ON cp.`id_category` = c.`id_category`
                    INNER JOIN `'._DB_PREFIX_.'product` p ON cp.`id_product` = p.`id_product`
                    '.Shop::addSqlAssociation('product', 'p', false).'
                    WHERE c.`active` = 1
                    AND product_shop.`active` = 1
                    AND product_shop.`visibility` IN ("both", "search")
                    AND product_shop.indexed = 1
                    AND fp.id_feature_value IN('.implode(',',$ids_feature_value).')
                    '.$sql_groups);
                }

		$eligible_products = array();
		foreach ($results as $row)
			$eligible_products[] = $row['id_product'];
		foreach ($intersect_array as $query)
		{
			$eligible_products2 = array();
			foreach ($db->executeS($query) as $row)
				$eligible_products2[] = $row['id_product'];

			$eligible_products = array_intersect($eligible_products, $eligible_products2);
			if (!count($eligible_products))
				return ($ajax ? array() : array('total' => 0, 'result' => array()));
		}

		$eligible_products = array_unique($eligible_products);

		$product_pool = '';
		foreach ($eligible_products as $id_product)
			if ($id_product)
				$product_pool .= (int)$id_product.',';
		if (empty($product_pool))
			return ($ajax ? array() : array('total' => 0, 'result' => array()));
		$product_pool = ((strpos($product_pool, ',') === false) ? (' = '.(int)$product_pool.' ') : (' IN ('.rtrim($product_pool, ',').') '));

		if ($ajax)
		{
			$sql = 'SELECT DISTINCT p.id_product, pl.name pname, cl.name cname,
						cl.link_rewrite crewrite, pl.link_rewrite prewrite '.$score.'
					FROM '._DB_PREFIX_.'product p
					INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON (
						p.`id_product` = pl.`id_product`
						AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
					)
					'.Shop::addSqlAssociation('product', 'p').'
					INNER JOIN `'._DB_PREFIX_.'category_lang` cl ON (
						product_shop.`id_category_default` = cl.`id_category`
						AND cl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('cl').'
					)
					WHERE p.`id_product` '.$product_pool.'
					ORDER BY quantity DESC LIMIT 10';
			return $db->executeS($sql);
		}

		if (strpos($order_by, '.') > 0)
		{
			$order_by = explode('.', $order_by);
			$order_by = pSQL($order_by[0]).'.`'.pSQL($order_by[1]).'`';
		}
		$alias = '';
		if ($order_by == 'price')
			$alias = 'product_shop.';
		else if ($order_by == 'date_upd')
			$alias = 'p.';
                if($id_category_search && $id_category_search != 'authors')
                    $product_pool.= ' AND cp.id_category='.$id_category_search;
                
		$sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity, 
				pl.`description_short`, pl.`available_now`, pl.`available_later`, pl.`link_rewrite`, pl.`name`,
			 MAX(image_shop.`id_image`) id_image, il.`legend`, m.`name` manufacturer_name '.$score.', MAX(product_attribute_shop.`id_product_attribute`) id_product_attribute,
				DATEDIFF(
					p.`date_add`,
					DATE_SUB(
						NOW(),
						INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY
					)
				) > 0 new
				FROM '._DB_PREFIX_.'product p
				'.Shop::addSqlAssociation('product', 'p').'
				INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON (
					p.`id_product` = pl.`id_product`
					AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
				)
                                LEFT JOIN `'._DB_PREFIX_.'category_product` cp	ON (p.`id_product` = cp.`id_product`)
				LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa	ON (p.`id_product` = pa.`id_product`)
				'.Shop::addSqlAssociation('product_attribute', 'pa', false, 'product_attribute_shop.`default_on` = 1').'
				'.Product::sqlStock('p', 'product_attribute_shop', false, $context->shop).'
				LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
				LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product`)'.
				Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
				LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
				WHERE p.`id_product` '.$product_pool.'
				GROUP BY product_shop.id_product
				
                                ORDER BY `quantity` DESC 
				 LIMIT '.(int)(($page_number - 1) * $page_size).','.(int)$page_size;
   
                //'.($order_by ? 'ORDER BY  '.$alias.$order_by : '').($order_way ? ' '.$order_way : '').'
		$result = $db->executeS($sql);

		$sql = 'SELECT COUNT(*)
				FROM '._DB_PREFIX_.'product p
				'.Shop::addSqlAssociation('product', 'p').'
                                LEFT JOIN `'._DB_PREFIX_.'category_product` cp	ON (p.`id_product` = cp.`id_product`)    
				INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON (
					p.`id_product` = pl.`id_product`
					AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
				)
				LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
				WHERE p.`id_product` '.$product_pool;
		$total = $db->getValue($sql);

		if (!$result)
			$result_properties = false;
		else
			$result_properties = Product::getProductsProperties((int)$id_lang, $result);

		return array('total' => $total,'result' => $result_properties);
	}

}