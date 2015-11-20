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

class SpecificPrice extends SpecificPriceCore
{


	public static function getQuantityDiscount($id_product, $id_shop, $id_currency, $id_country, $id_group, $quantity, $id_product_attribute = null, $id_customer = 0)
	{
            $context = Context::getContext();
                if($context->customer->id){
                    $groups = array();
                    $customer_groups = Db::getInstance()->executeS("SELECT id_group FROM ps_customer_group WHERE id_customer={$context->customer->id}"); 
                    foreach ($customer_groups as $row_group){
                        $groups[] = $row_group['id_group'];
                    }
                   
                }else{
                    $groups = array($id_group);
                }
                $groups = implode(',',$groups);
		if (!SpecificPrice::isFeatureActive())
			return array();

		$now = date('Y-m-d H:i:s');
                $sql = '
			SELECT *,
					'.SpecificPrice::_getScoreQuery($id_product, $id_shop, $id_currency, $id_country, $id_group, $id_customer).'
			FROM `'._DB_PREFIX_.'specific_price`
			WHERE
					`id_product` IN(0, '.(int)$id_product.') AND
					`id_product_attribute` IN(0, '.(int)$id_product_attribute.') AND
					`id_shop` IN(0, '.(int)$id_shop.') AND
					`id_currency` IN(0, '.(int)$id_currency.') AND
					`id_country` IN(0, '.(int)$id_country.') AND
					`id_group` IN(0, '.$groups.') AND
					`id_customer` IN(0, '.(int)$id_customer.') AND
					`from_quantity` >= '.(int)$quantity.'
					AND
					(
						(`from` = \'0000-00-00 00:00:00\' OR \''.$now.'\' >= `from`)
						AND
						(`to` = \'0000-00-00 00:00:00\' OR \''.$now.'\' <= `to`)
					)
					ORDER BY `from_quantity` DESC, `score` DESC
		';
                
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
	}

	
}

