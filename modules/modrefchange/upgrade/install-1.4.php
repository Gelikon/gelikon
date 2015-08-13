<?php
if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_4($object, $install = false)
{
	// First uninstall old override
	try {
		$object->uninstallOldOverrides();
	} catch (Exception $e) {
		$object->add_error(sprintf(Tools::displayError('Unable to uninstall old override: %s'), $e->getMessage()));
		//$this->uninstallOverrides(); remove this line because if module a install an override, then module b install same override, this line will remove override of module a (if you find a bug related to this line please don't forget what i say before)
		return false;
	}
	// Install overrides
	try {
		$object->installOverrides();
	} catch (Exception $e) {
		$object->add_error(sprintf(Tools::displayError('Unable to install override: %s'), $e->getMessage()));
		//$this->uninstallOverrides(); remove this line because if module a install an override, then module b install same override, this line will remove override of module a (if you find a bug related to this line please don't forget what i say before)
		return false;
	}
	
	if(!Hook::getIdByName('actionBeforeAddOrder')){
		$hook = new Hook();
		$hook->name = 'actionBeforeAddOrder';
		$hook->title = 'New orders before order is added';
		$hook->description = 'Custom hook for PaymentModule ValidateOrder function';
		$hook->position = true;
		$hook->live_edit = false;
		$hook->add();
	}
	$id_new_hook = Hook::getIdByName('actionBeforeAddOrder');
	$id_old_hook = Hook::getIdByName('actionValidateOrder');
	$mod_new_hook = Hook::getModulesFromHook($id_new_hook, $object->id);
	$mod_old_hook = Hook::getModulesFromHook($id_old_hook, $object->id);
	if(empty($mod_new_hook))
		$object->registerHook('actionBeforeAddOrder');
	if(!empty($mod_old_hook))
		$object->unregisterHook('actionValidateOrder');
	
	return true;
}