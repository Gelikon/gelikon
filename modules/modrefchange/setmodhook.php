<?php
require(dirname(__FILE__).'/../../config/config.inc.php');
$controller = new FrontController();
$controller->init();
require_once(dirname(__FILE__).'/modrefchange.php');
$object = new Modrefchange();
$return = true;
$errors = array();
if(!Hook::getIdByName('actionBeforeAddOrder')){
	$hook = new Hook();
	$hook->name = 'actionBeforeAddOrder';
	$hook->title = 'Execute actions before order is added to database';
	$hook->description = 'Custom hook for Order Add function';
	$hook->position = true;
	$hook->live_edit = false;
	if(!$hook->add()){
		$errors[] = 'Unable to add hook actionBeforeAddOrder';
		$return &= false;
	}
}
if(!Hook::getIdByName('actionBeforeAddOrderInvoice')){
	$hook2 = new Hook();
	$hook2->name = 'actionBeforeAddOrderInvoice';
	$hook2->title = 'Execute actions before invoice is added to database';
	$hook2->description = 'Custom hook for Order setLastInvoiceNumber function';
	$hook2->position = true;
	$hook2->live_edit = false;
	if(!$hook2->add()){
		$errors[] = 'Unable to add hook actionBeforeAddOrderInvoice';
		$return &= false;
	}
}
if(!Hook::getIdByName('actionBeforeAddDeliveryNumber')){
	$hook3 = new Hook();
	$hook3->name = 'actionBeforeAddDeliveryNumber';
	$hook3->title = 'Execute actions before delivery number is added to database';
	$hook3->description = 'Custom hook for Order setDeliveryNumber function';
	$hook3->position = true;
	$hook3->live_edit = false;
	if(!$hook3->add()){
		$errors[] = 'Unable to add hook actionBeforeAddDeliveryNumber';
		$return &= false;
	}
}
$id_new_hook = Hook::getIdByName('actionBeforeAddOrder');
$id_new_hook2 = Hook::getIdByName('actionBeforeAddOrderInvoice');
$id_new_hook3 = Hook::getIdByName('actionBeforeAddDeliveryNumber');
$id_old_hook = Hook::getIdByName('actionValidateOrder');
$mod_new_hook = Hook::getModulesFromHook($id_new_hook, $object->id);
$mod_new_hook2 = Hook::getModulesFromHook($id_new_hook2, $object->id);
$mod_new_hook3 = Hook::getModulesFromHook($id_new_hook3, $object->id);
$mod_old_hook = Hook::getModulesFromHook($id_old_hook, $object->id);
if(empty($mod_new_hook))
	$return &= $object->registerHook('actionBeforeAddOrder');
if(empty($mod_new_hook2))
	$return &= $object->registerHook('actionBeforeAddOrderInvoice');
if(empty($mod_new_hook3))
	$return &= $object->registerHook('actionBeforeAddDeliveryNumber');
if(!empty($mod_old_hook))
	$return &= $object->unregisterHook('actionValidateOrder');
if(!$return){
	print_r($errors);
	die();
} else
	print('Hooks set and registered');