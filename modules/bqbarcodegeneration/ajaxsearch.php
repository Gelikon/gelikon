<?php
/**
* Bgbarcodegeneration module
*
* @short description    generate and print UPC and EAN13 barcode products
*
* @author    Boutiquet.net
*
* @copyright Boutiquet.net
*
* @version    1.1
*
* @license   copyright Boutiquet.net
*/

include( '../../config/config.inc.php' );
include_once('../../init.php');
$context = Context::getContext();
$id_lang = $context->language->id;
$destination_id = Tools::getValue('destination_id');
if ($destination_id)
{
	$json = Product::getProducts($id_lang, 0, 3000, 'id_product', 'DESC', $destination_id, false, $context);
	echo	Tools::jsonEncode($json);
}
else return false;
?>