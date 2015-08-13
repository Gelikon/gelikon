<?php
if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_5($object, $install = false)
{
	// Install overrides
	try {
		$object->installOverrides();
	} catch (Exception $e) {
		Tools::displayError(sprintf(Tools::displayError('Unable to install override: %s'), $e->getMessage()));
		//$this->uninstallOverrides(); remove this line because if module a install an override, then module b install same override, this line will remove override of module a (if you find a bug related to this line please don't forget what i say before)
		return false;
	}
	$classindex = realpath(dirname(__FILE__).'/../..').'/cache/class_index.php';
	unset($classindex);
	if(version_compare(_PS_VERSION_, '1.6', '>='))
		PrestashopAutoload::getInstance()->generateIndex();
	else
		Autoload::getInstance()->generateIndex();
	
	if(!Hook::getIdByName('actionBeforeAddOrder')){
		$hook = new Hook();
		$hook->name = 'actionBeforeAddOrder';
		$hook->title = 'Execute actions before order is added to database';
		$hook->description = 'Custom hook for Order Add function';
		$hook->position = true;
		$hook->live_edit = false;
		$hook->add();
	}
	if(!Hook::getIdByName('actionBeforeAddOrderInvoice')){
		$hook2 = new Hook();
		$hook2->name = 'actionBeforeAddOrderInvoice';
		$hook2->title = 'Execute actions before invoice is added to database';
		$hook2->description = 'Custom hook for Order setLastInvoiceNumber function';
		$hook2->position = true;
		$hook2->live_edit = false;
		$hook2->add();
	}
	if(!Hook::getIdByName('actionBeforeAddDeliveryNumber')){
		$hook3 = new Hook();
		$hook3->name = 'actionBeforeAddDeliveryNumber';
		$hook3->title = 'Execute actions before delivery number is added to database';
		$hook3->description = 'Custom hook for Order setDeliveryNumber function';
		$hook3->position = true;
		$hook3->live_edit = false;
		$hook3->add();
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
		$object->registerHook('actionBeforeAddOrder');
	if(empty($mod_new_hook2))
		$object->registerHook('actionBeforeAddOrderInvoice');
	if(empty($mod_new_hook3))
		$object->registerHook('actionBeforeAddDeliveryNumber');
	if(!empty($mod_old_hook))
		$object->unregisterHook('actionValidateOrder');
	
	return true;
}