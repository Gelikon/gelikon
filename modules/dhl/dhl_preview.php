<?php

include_once(dirname(__FILE__) . '/../../config/config.inc.php');
include_once(dirname(__FILE__) . '/../../init.php');
include_once(dirname(__FILE__) . '/classes/RateAvailableServices.php');
include_once(dirname(__FILE__) . '/JSON.php');

$log = false;
$sti = microtime();
$ps_version  = floatval(substr(_PS_VERSION_,0,3));
$dhl = new DHL();
$is_cart = Tools::getValue('dhl_is_cart');
$qty = Tools::getValue('qty');

$dhl->updateCartWithNewCarrier();

$dhl->saveLog('dhl_log1.txt', "Starting $sti ".print_r($_POST,true), $log);

// Get Address and zone
$address = $dhl->getPreviewAddress($log);

if (isset($_POST['id_product']))
	$product = new Product($_POST['id_product']);
$id_product_attribute = Tools::getValue('id_product_attribute','0');
$product_weight = $product->weight;
// Add combination weight impact
if ($id_product_attribute != 0)
	$product_weight += Db::getInstance()->getValue('SELECT `weight`	FROM `'._DB_PREFIX_.'product_attribute`	WHERE `id_product_attribute` = '.(int)($id_product_attribute));
$is_downloadable = ProductDownload::getIdFromIdProduct($_POST['id_product']);
if ($is_downloadable)
	$json = array("dhl_rate_tpl"=> $dhl->hookAjaxPreview($rates, $address['dest_zip'], $address['dest_state'], $address['dest_country'], true, $is_cart));
else
{
	$context = $dhl->getContext();
	$currency = new Currency($context->currency->id);
	$rates = $dhl->getAllRates($address['id_zone'], $is_cart, $context->cart, $product_weight, $address['dest_zip'], $address['dest_state'], $address['dest_country'], $currency, $product, $id_product_attribute, $qty, $address['dest_city']);

	$json = array("dhl_rate_tpl"=> $dhl->hookAjaxPreview($rates, $address['dest_zip'], $address['dest_state'], $address['dest_country'], false, $is_cart, $address['dest_city']));
}
if (!function_exists('json_decode') )
{
	$j = new JSON();
	print $j->serialize($dhl->array2object($json));
}
else
	print json_encode($json);

$dhl->saveLog('dhl_log1.txt', "5) = ".(microtime() - $sti), $log);