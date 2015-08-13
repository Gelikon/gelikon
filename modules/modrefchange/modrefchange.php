<?php
/**
 * modrefchange module
 *
 * @category Prestashop
 * @category Module
 * @author PrestadevNL <support@prestadev.nl>
 * @copyright PrestadevNL
 * @license commercial license see license.txt
 * @license logo http://creativecommons.org/licenses/by/3.0/deed.nl Creative Commons
 * @author logo http://prestadev.nl/
 * @version 1.5.0.0
**/

if (!defined('_PS_VERSION_'))
	exit;

class Modrefchange extends Module
{
	private $_html = '';
	private $_postErrors = array();
	protected $_errors = array();

	public $ref_orderid;
	public $ref_cartid;
	public $ref_prefnulo;
	public $ref_prefnulnro;
	public $ref_prefnulnrc;
	public $ref_prefsigno;
	public $ref_prefnulc;
	public $ref_prefsignc;
	public $ref_prefsign;
	
	public $inv_orderid;
	public $inv_cartid;
	public $inv_prefsign;
	
	public function __construct()
	{
		$this->name = 'modrefchange';

		$this->tab = 'administration';

		$this->version = '1.5.3';
		$this->author = 'PrestadevNL';

		$config = Configuration::getMultiple(array('ORD_REF_ORDERID', 'ORD_REF_PREFIXNULO', 'ORD_REF_PREFIXNULNRO', 'ORD_REF_PREFIXSIGNO', 'ORD_REF_CARTID', 'ORD_REF_PREFIXNULC', 'ORD_REF_PREFIXNULNRC', 'ORD_REF_PREFIXSIGNC', 'ORD_REF_PREFIXSIGN', 'INV_REF_ORDERID', 'INV_REF_CARTID', 'INV_REF_PREFIXSIGN'));
		if (isset($config['ORD_REF_ORDERID']))
			$this->ref_orderid = $config['ORD_REF_ORDERID'];
		if (isset($config['ORD_REF_CARTID']))
			$this->ref_cartid = $config['ORD_REF_CARTID'];
		if (isset($config['ORD_REF_PREFIXNULO']))
			$this->ref_prefnulo = $config['ORD_REF_PREFIXNULO'];
		if (isset($config['ORD_REF_PREFIXNULNRO']))
			$this->ref_prefnulnro = $config['ORD_REF_PREFIXNULNRO'];
		if (isset($config['ORD_REF_PREFIXSIGNO']))
			$this->ref_prefsigno = $config['ORD_REF_PREFIXSIGNO'];
		if (isset($config['ORD_REF_PREFIXNULC']))
			$this->ref_prefnulc = $config['ORD_REF_PREFIXNULC'];
		if (isset($config['ORD_REF_PREFIXNULNRC']))
			$this->ref_prefnulnrc = $config['ORD_REF_PREFIXNULNRC'];
		if (isset($config['ORD_REF_PREFIXSIGNC']))
			$this->ref_prefsignc = $config['ORD_REF_PREFIXSIGNC'];
		if (isset($config['ORD_REF_PREFIXSIGN']))
			$this->ref_prefsign = $config['ORD_REF_PREFIXSIGN'];
		
		if (isset($config['INV_REF_ORDERID']))
			$this->inv_orderid = $config['INV_REF_ORDERID'];
		if (isset($config['INV_REF_CARTID']))
			$this->inv_cartid = $config['INV_REF_CARTID'];
		if (isset($config['INV_REF_PREFIXSIGN']))
			$this->inv_prefsign = $config['INV_REF_PREFIXSIGN'];
		parent::__construct();

		$this->displayName = $this->l('Order reference change mod');
		$this->description = $this->l('Mod to change the order reference');
	}
	
	/**
	 * Install overrides files for the module
	 *
	 * @return bool
	 */
	public function installXtraOverrides()
	{
		if (!is_dir(_PS_MODULE_DIR_.$this->name.'/_overrider/classes/order'))
			return true;
			
		if (file_exists(_PS_ROOT_DIR_.'/override/classes/order/OrderPayment.php') && is_writable(_PS_ROOT_DIR_.'/override/classes/order/OrderPayment.php'))
		{
			$searchfor = 'public static $definition = array(';
			// get the file contents, assuming the file to be readable (and exist)
			$contents = file_get_contents(_PS_ROOT_DIR_.'/override/classes/order/OrderPayment.php');
			// escape special characters in the query
			$pattern = preg_quote($searchfor, '/');
			// finalise the regular expression, matching the whole line
			$pattern = "/^.*$pattern.*\$/m";
			// search, and store all matching occurences in $matches
			if(preg_match_all($pattern, $contents, $matches))
				if(count($matches) >= 2)
					copy(_PS_MODULE_DIR_.$this->name.'/override/classes/order/OrderPayment.php', _PS_ROOT_DIR_.'/override/classes/order/OrderPayment.php');
				else
					return true;
		}

		$result = true;
		foreach (Tools::scandir(_PS_MODULE_DIR_.$this->name.'/_overrider/classes/order', 'php', '', true) as $file)
		{
			$class = basename($file, '.php');
			if (Autoload::getInstance()->getClassPath($class.'Core'))
				$result &= $this->addXtraOverride($class);
		}

		return $result;
	}

	/**
	 * Uninstall overrides files for the module
	 *
	 * @return bool
	 */
	public function uninstallXtraOverrides()
	{
		return copy(_PS_MODULE_DIR_.$this->name.'/override/classes/order/OrderPayment.php', _PS_ROOT_DIR_.'/override/classes/order/OrderPayment.php');
	}

	/**
	 * Add all methods in a module override to the override class
	 *
	 * @param string $classname
	 * @return bool
	 */
	public function addXtraOverride($classname)
	{
		$path = Autoload::getInstance()->getClassPath($classname.'Core');

		// Check if there is already an override file, if not, we just need to copy the file
		if (Autoload::getInstance()->getClassPath($classname))
		{
			// Check if override file is writable
			$override_path = _PS_ROOT_DIR_.'/'.Autoload::getInstance()->getClassPath($classname);
			if ((!file_exists($override_path) && !is_writable(dirname($override_path))) || (file_exists($override_path) && !is_writable($override_path)))
				throw new Exception(sprintf(Tools::displayError('file (%s) not writable'), $override_path));

			// Get a uniq id for the class, because you can override a class (or remove the override) twice in the same session and we need to avoid redeclaration
			do $uniq = uniqid();
			while (class_exists($classname.'OverrideOriginal_remove', false));
				
			// Make a reflection of the override class and the module override class
			$override_file = file($override_path);
			eval(preg_replace(array('#^\s*<\?php#', '#class\s+'.$classname.'\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?#i'), array('', 'class '.$classname.'OverrideOriginal'.$uniq), implode('', $override_file)));
			$override_class = new ReflectionClass($classname.'OverrideOriginal'.$uniq);

			$module_file = file($this->getLocalPath().'_overrider'.DIRECTORY_SEPARATOR.$path);
			eval(preg_replace(array('#^\s*<\?php#', '#class\s+'.$classname.'(\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?)?#i'), array('', 'class '.$classname.'Override'.$uniq), implode('', $module_file)));
			$module_class = new ReflectionClass($classname.'Override'.$uniq);

			// Check if none of the methods already exists in the override class
			foreach ($module_class->getMethods() as $method)
				if ($override_class->hasMethod($method->getName()))
					throw new Exception(sprintf(Tools::displayError('The method %1$s in the class %2$s is already overriden.'), $method->getName(), $classname));

			// Check if none of the properties already exists in the override class
//			foreach ($module_class->getProperties() as $property)
//				if ($override_class->hasProperty($property->getName()))
//					throw new Exception(sprintf(Tools::displayError('The property %1$s in the class %2$s is already defined.'), $property->getName(), $classname));

			// Insert the methods from module override in override
			$copy_from = array_slice($module_file, $module_class->getStartLine() + 1, $module_class->getEndLine() - $module_class->getStartLine() - 2);
			array_splice($override_file, $override_class->getEndLine() - 1, 0, $copy_from);
			$code = implode('', $override_file);
			file_put_contents($override_path, $code);
		}
		else
		{
			$override_src = _PS_MODULE_DIR_.$this->name.'/_overrider/'.$path;
			$override_dest = _PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'/override'.DIRECTORY_SEPARATOR.$path;
			if (!is_writable(dirname($override_dest)))
				throw new Exception(sprintf(Tools::displayError('directory (%s) not writable'), dirname($override_dest)));
			copy($override_src, $override_dest);
			// Re-generate the class index
			Autoload::getInstance()->generateIndex();
		}
		return true;
	}

	public function patchOverrides()
	{
		if (!is_dir($this->getLocalPath().'override'))
			return true;

		$result = true;
		foreach (Tools::scandir($this->getLocalPath().'override', 'php', '', true) as $file)
		{
			$class = basename($file, '.php');
			if (Autoload::getInstance()->getClassPath($class.'Core'))
				if(!$this->IsPatchedOverrides($class))
					$result &= $this->addOverride($class);
				else
					$result &= true;
		}
		return $result;
	}
	
	public function IspatchedOverrides($classname)
	{
		switch($classname){
			case 'Order':
				$path = Autoload::getInstance()->getClassPath($classname.'Core');

				// Check if there is already an override file, if not, we just need to copy the file
				if (Autoload::getInstance()->getClassPath($classname))
				{
					// Check if override file is writable
					$override_path = _PS_ROOT_DIR_.'/'.Autoload::getInstance()->getClassPath($classname);
					if (file_exists($override_path) && !is_writable($override_path))
						throw new Exception(sprintf(Tools::displayError('file (%s) not writable'), $override_path));

					// Get a uniq id for the class, because you can override a class (or remove the override) twice in the same session and we need to avoid redeclaration
					do $uniq = uniqid();
					while (class_exists($classname.'OverrideOriginal_remove', false));
						
					// Make a reflection of the override class and the module override class
					$override_file = file($override_path);
					eval(preg_replace(array('#^\s*<\?php#', '#class\s+'.$classname.'\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?#i'), array('', 'class '.$classname.'OverrideOriginal'.$uniq), implode('', $override_file)));
					$override_class = new ReflectionClass($classname.'OverrideOriginal'.$uniq);

					$module_file = file($this->getLocalPath().'override'.DIRECTORY_SEPARATOR.$path);
					eval(preg_replace(array('#^\s*<\?php#', '#class\s+'.$classname.'(\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?)?#i'), array('', 'class '.$classname.'Override'.$uniq), implode('', $module_file)));
					$module_class = new ReflectionClass($classname.'Override'.$uniq);

					// Check if none of the methods already exists in the override class
					$methodpatched = array(
						'add'=>array(
							'patched'=>false,
							'method'=>'
	public function add($autodate = true, $null_values = true)
	{
		$cart = new Cart($this->id_cart);
		Hook::exec(\'actionBeforeAddOrder\', array(\'order\'=>$this,\'cart\'=>$cart));

		if (ObjectModel::add($autodate, $null_values))
			return SpecificPrice::deleteByIdCart($this->id_cart);
		return false;
	}
'
						),
						'setLastInvoiceNumber'=>array(
							'patched'=>false,
							'method'=>'
	public static function setLastInvoiceNumber($order_invoice_id, $id_shop)
	{
		if (!$order_invoice_id)
			return false;

		$number = Configuration::get(\'PS_INVOICE_START_NUMBER\', null, null, $id_shop);
		// If invoice start number has been set, you clean the value of this configuration
		if ($number)
			Configuration::updateValue(\'PS_INVOICE_START_NUMBER\', false, false, null, $id_shop);
			
		$order_invoice = new OrderInvoice($order_invoice_id);
		$order = new Order($order_invoice->id_order);
		$cart = new Cart($order->id_cart);
		
		if($ref = Hook::exec(\'actionBeforeAddOrderInvoice\', array(\'order_invoice\'=>$order_invoice,\'order\'=>$order,\'cart\'=>$cart)))
			$number = $ref;

		$sql = \'UPDATE `\'._DB_PREFIX_.\'order_invoice` SET number =\';

		if ($number)
			$sql .= (int)$number;
		else
			$sql .= \'(SELECT new_number FROM (SELECT (MAX(`number`) + 1) AS new_number
			FROM `\'._DB_PREFIX_.\'order_invoice`) AS result)\';

		$sql .=\' WHERE `id_order_invoice` = \'.(int)$order_invoice_id;

		return Db::getInstance()->execute($sql);
	}
							'
						),
						'setDeliveryNumber'=>array(
							'patched'=>false,
							'method'=>'
	public function setDeliveryNumber($order_invoice_id, $id_shop)
	{
		if (!$order_invoice_id)
			return false;

		$number = Configuration::get(\'PS_DELIVERY_NUMBER\', null, null, $id_shop);
		// If invoice start number has been set, you clean the value of this configuration
		if ($number)
			Configuration::updateValue(\'PS_DELIVERY_NUMBER\', false, false, null, $id_shop);
			
		$order_invoice = new OrderInvoice($order_invoice_id);
		$order = new Order($order_invoice->id_order);
		$cart = new Cart($order->id_cart);
		
		if($ref = Hook::exec(\'actionBeforeAddDeliveryNumber\', array(\'order\'=>$order,\'cart\'=>$cart,\'number\'=>$number)))
			$number = $ref;

		$sql = \'UPDATE `\'._DB_PREFIX_.\'order_invoice` SET delivery_number =\';

		if ($number)
			$sql .= (int)$number;
		else
			$sql .= \'(SELECT new_number FROM (SELECT (MAX(`delivery_number`) + 1) AS new_number
			FROM `\'._DB_PREFIX_.\'order_invoice`) AS result)\';

		$sql .=\' WHERE `id_order_invoice` = \'.(int)$order_invoice_id;

		return Db::getInstance()->execute($sql);
	}
							'
						)
					);
					foreach ($module_class->getMethods() as $method)
						if ($override_class->hasMethod($method->getName()))
							$methodpatched[$method->getName()]['patched'] = true;
					

					// Insert the methods from module override in override
					foreach($methodpatched as $patched){
						if(!$patched['patched'])
							$copy_from[] = $patched['method'];
					}
					array_splice($override_file, $override_class->getEndLine() - 1, 0, $copy_from);
					$code = implode('', $override_file);
					file_put_contents($override_path, $code);
					return true;
				}
				else
					return false;
			break;
			case 'OrderInvoice':
				if(file_exists(_PS_OVERRIDE_DIR_.'classes/Order/OrderInvoice.php')){
					$cont = file_get_contents(_PS_OVERRIDE_DIR_.'classes/Order/OrderInvoice.php');
					if(strpos($cont, 'actionBeforeAddOrderInvoice') === false)
						return false;
					else
						return true;
				} else
					return false;
			break;
			case 'OrderPayment':
				if(file_exists(_PS_OVERRIDE_DIR_.'classes/Order/OrderPayment.php')){
					$cont = file_get_contents(_PS_OVERRIDE_DIR_.'classes/Order/OrderPayment.php');
					if(strpos($cont, '\'order_reference\' => 	array(\'type\' => self::TYPE_STRING, \'validate\' => \'isAnything\', \'size\' => 100),') === false)
						return false;
					else
						return true;
				} else
					return false;
			break;
			default:
				return false;
		}
	}
	
	public function install()
	{
		$hook = new Hook(Hook::getIdByName('actionBeforeAddOrder'));
		$hook->delete();
		unset($hook);
		$hook2 = new Hook(Hook::getIdByName('actionBeforeAddOrderInvoice'));
		$hook2->delete();
		unset($hook2);
		$hook3 = new Hook(Hook::getIdByName('actionBeforeAddDeliveryNumber'));
		$hook3->delete();
		unset($hook3);
		
		if(!Hook::getIdByName('actionBeforeAddOrder')){
			$hook = new Hook();
			$hook->name = 'actionBeforeAddOrder';
			$hook->title = 'Execute actions before order is added to database';
			$hook->description = 'Custom hook for Order Add function';
			$hook->position = true;
			$hook->live_edit = false;		
			if(!$hook->add())
				return $this->_abortInstall('Error while adding hook actionBeforeAddOrder', 0);
			unset ($hook);
		}
		if(!Hook::getIdByName('actionBeforeAddOrderInvoice')){
			$hook2 = new Hook();
			$hook2->name = 'actionBeforeAddOrderInvoice';
			$hook2->title = 'Execute actions before invoice is added to database';
			$hook2->description = 'Custom hook for OrderInvoice Add function';
			$hook2->position = true;
			$hook2->live_edit = false;
			if(!$hook2->add())
				return $this->_abortInstall('Error while adding hook actionBeforeAddOrderInvoice', 1);
			unset ($hook2);
		}
		if(!Hook::getIdByName('actionBeforeAddDeliveryNumber')){
			$hook3 = new Hook();
			$hook3->name = 'actionBeforeAddDeliveryNumber';
			$hook3->title = 'Execute actions before delivery number is added to database';
			$hook3->description = 'Custom hook for Order setDeliveryNumber function';
			$hook3->position = true;
			$hook3->live_edit = false;
			if(!$hook3->add())
				return $this->_abortInstall('Error while adding hook actionBeforeAddDeliveryNumber', 2);
		}	
		if(!$this->installDB())
			return $this->_abortInstall('Error while installing module database settings', 3);
		if(!parent::install())
			return $this->_abortInstall('Error while installing module class', 4);
		if(!$this->installXtraOverrides())
			return $this->_abortInstall('Error while installing OrderPayment definition override', 5);
		if(!$this->registerHook('actionBeforeAddOrder'))
			return $this->_abortInstall('Error while adding module to hook actionBeforeAddOrder', 6);
		if(!$this->registerHook('actionBeforeAddOrderInvoice'))
			return $this->_abortInstall('Error while adding module to hook actionBeforeAddOrderInvoice', 7);
		if(!$this->registerHook('actionBeforeAddDeliveryNumber'))
			return $this->_abortInstall('Error while adding module to hook actionBeforeAddDeliveryNumber', 8);
		if(!Configuration::updateValue('ORD_REF_ORDERID', 0))
			return $this->_abortInstall('Error while adding configuration setting ORD_REF_ORDERID', 9);
		if(!Configuration::updateValue('ORD_REF_PREFIXNULO', 0))
			return $this->_abortInstall('Error while adding configuration setting ORD_REF_PREFIXNULO', 10);
		if(!Configuration::updateValue('ORD_REF_PREFIXNULNRO', 9))
			return $this->_abortInstall('Error while adding configuration setting ORD_REF_PREFIXNULNRO', 11);
		if(!Configuration::updateValue('ORD_REF_PREFIXNULNRC', 9))
			return $this->_abortInstall('Error while adding configuration setting ORD_REF_PREFIXNULNRC', 12);
		if(!Configuration::updateValue('ORD_REF_PREFIXSIGNO', ''))
			return $this->_abortInstall('Error while adding configuration setting ORD_REF_PREFIXSIGNO', 13);
		if(!Configuration::updateValue('ORD_REF_CARTID', 0))
			return $this->_abortInstall('Error while adding configuration setting ORD_REF_PREFIX_CARTID', 14);
		if(!Configuration::updateValue('ORD_REF_PREFIXNULC', 0))
			return $this->_abortInstall('Error while adding configuration setting ORD_REF_PREFIXNULC', 15);
		if(!Configuration::updateValue('ORD_REF_PREFIXSIGNC', ''))
			return $this->_abortInstall('Error while adding configuration setting ORD_REF_PREFIXSIGNC', 16);
		if(!Configuration::updateValue('ORD_REF_PREFIXSIGN', ''))
			return $this->_abortInstall('Error while adding configuration setting ORD_REF_PREFIXSIGN', 17);
		if(!Configuration::updateValue('INV_REF_ORDERID', 0))
			return $this->_abortInstall('Error while adding configuration setting INV_REF_ORDERID', 18);
		if(!Configuration::updateValue('INV_REF_CARTID', 0))
			return $this->_abortInstall('Error while adding configuration setting INV_REF_CARTID', 19);
		if(!Configuration::updateValue('INV_REF_PREFIXSIGN', 0))
			return $this->_abortInstall('Error while adding configuration setting INV_REF_PREFIXSIGN', 20);
		
		$classindex = realpath(dirname(__FILE__).'/../..').'/cache/class_index.php';
		unset($classindex);
		if(version_compare(_PS_VERSION_, '1.6', '>='))
			PrestashopAutoload::getInstance()->generateIndex();
		else
			Autoload::getInstance()->generateIndex();
		
		return true;
	}

	public function installDB(){
		$return = true;
		if(!Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'orders` CHANGE `reference` `reference` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL '))
			$return &= $this->_abortInstall('Error while altering `'._DB_PREFIX_.'orders`');
		if(!Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'order_payment` CHANGE `order_reference` `order_reference` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL '))
			$return &= $this->_abortInstall('Error while altering `'._DB_PREFIX_.'order_payments`');
		return $return;
	}
	
	public function uninstall()
	{
		$return = true;
		
		copy(_PS_MODULE_DIR_.$object->name.'/override/classes/order/OrderPayment.php', _PS_ROOT_DIR_.'/override/classes/order/OrderPayment.php');
		if(!parent::uninstall())
			$return &= $this->_abortInstall('Error while uninstalling class from modules');
		$hook = new Hook(Hook::getIdByName('actionBeforeAddOrder'));
		if(!$hook->delete())
			$return &= $this->_abortInstall('Error while removing hook actionBeforeAddOrder');
		unset($hook);
		$hook2 = new Hook(Hook::getIdByName('actionBeforeAddOrderInvoice'));
		if(!$hook2->delete())
			$return &= $this->_abortInstall('Error while removing hook actionBeforeAddOrderInvoice');
		unset($hook2);
		$hook3 = new Hook(Hook::getIdByName('actionBeforeAddDeliveryNumber'));
		if(!$hook3->delete())
			$return &= $this->_abortInstall('Error while removing hook actionBeforeAddDeliveryNumber');
		unset($hook3);
		if(!Configuration::deleteByName('ORD_REF_ORDERID'))
			$return &= $this->_abortInstall('Error while removing configuration setting ORD_REF_ORDERID');
		if(!Configuration::deleteByName('ORD_REF_PREFIXNULO'))
			$return &= $this->_abortInstall('Error while removing configuration setting ORD_REF_PREFIXNULO');
		if(!Configuration::deleteByName('ORD_REF_PREFIXNULNRO'))
			$return &= $this->_abortInstall('Error while removing configuration setting ORD_REF_PREFIXNULNRO');
		if(!Configuration::deleteByName('ORD_REF_PREFIXNULNRC'))
			$return &= $this->_abortInstall('Error while removing configuration setting ORD_REF_PREFIXNULNRC');
		if(!Configuration::deleteByName('ORD_REF_PREFIXSIGNO'))
			$return &= $this->_abortInstall('Error while removing configuration setting ORD_REF_PREFIXSIGNO');
		if(!Configuration::deleteByName('ORD_REF_CARTID'))
			$return &= $this->_abortInstall('Error while removing configuration setting ORD_REF_PREFIXNULO');
		if(!Configuration::deleteByName('ORD_REF_PREFIXNULC'))
			$return &= $this->_abortInstall('Error while removing configuration setting ORD_REF_PREFIXNULC');
		if(!Configuration::deleteByName('ORD_REF_PREFIXSIGNC'))
			$return &= $this->_abortInstall('Error while removing configuration setting ORD_REF_PREFIXSIGNC');
		if(!Configuration::deleteByName('ORD_REF_PREFIXSIGN'))
			$return &= $this->_abortInstall('Error while removing configuration setting ORD_REF_PREFIXSIGN');
		if(!Configuration::deleteByName('INV_REF_ORDERID'))
			$return &= $this->_abortInstall('Error while removing configuration setting INV_REF_ORDERID');
		if(!Configuration::deleteByName('INV_REF_CARTID'))
			$return &= $this->_abortInstall('Error while removing configuration setting INV_REF_ORDERID');
		if(!Configuration::deleteByName('INV_REF_PREFIXSIGN'))
			$return &= $this->_abortInstall('Error while removing configuration setting INV_REF_ORDERID');

		return $return;
	}
	
	protected function _revertInstall($step)
	{
		if($step >= 1){
			$hook = new Hook(Hook::getIdByName('actionBeforeAddOrder'));
			$hook->delete();
			unset($hook);
		}
		if($step >= 2){
			$hook2 = new Hook(Hook::getIdByName('actionBeforeAddOrderInvoice'));
			$hook2->delete();
			unset($hook2);
		}
		if($step >= 3){
			$hook3 = new Hook(Hook::getIdByName('actionBeforeAddDeliveryNumber'));
			$hook3->delete();
			unset($hook3);
		}
		// if($step >= 4)
			// Uninstall DB changes
		if($step >= 5)
			parent::uninstall;
		if($step >= 6)
			$this->uninstallXtraOverrides();
		if($step >= 7)
			$this->unregisterHook('actionBeforeAddOrder');
		if($step >= 8)
			$this->unregisterHook('actionBeforeAddOrderInvoice');
		if($step >= 9)
			$this->unregisterHook('actionBeforeAddDeliveryNumber');
		if($step >= 10)
			Configuration::deleteByName('ORD_REF_ORDERID');
		if($step >= 11)
			Configuration::deleteByName('ORD_REF_PREFIXNULO');
		if($step >= 12)
			Configuration::deleteByName('ORD_REF_PREFIXNULNRO');
		if($step >= 13)
			Configuration::deleteByName('ORD_REF_PREFIXNULNRC');
		if($step >= 14)
			Configuration::deleteByName('ORD_REF_PREFIXSIGNO');
		if($step >= 15)
			Configuration::deleteByName('ORD_REF_CARTID');
		if($step >= 16)
			Configuration::deleteByName('ORD_REF_PREFIXNULC');
		if($step >= 17)
			Configuration::deleteByName('ORD_REF_PREFIXSIGNC');
		if($step >= 18)
			Configuration::deleteByName('ORD_REF_PREFIXSIGN');
		if($step >= 19)
			Configuration::deleteByName('INV_REF_ORDERID');
		if($step >= 20)
			Configuration::deleteByName('INV_REF_CARTID');
	}
	
	/**
	* Set installation errors and return false
	*
	* @param string $error Installation abortion reason
	* @return boolean Always false
	*/
	protected function _abortInstall($error, $step = 0)
	{
		$this->_revertInstall($step);
		
		if (version_compare(_PS_VERSION_, '1.5.0.0 ', '>='))
			$this->_errors[] = $error;
		else
			echo '<div class="error">'.strip_tags($error).'</div>';

		return false;
	}
	
	private function _postProcess()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			$inv_prefixsign = Tools::getValue('inv_prefixsign');
			$dtpattern = '/(%[a-zA-Z]*)/';
			preg_match_all($dtpattern, Tools::getValue('inv_prefixsign'), $matches);      	
			if(!Configuration::updateValue('ORD_REF_ORDERID', Tools::getValue('ref_orderid')))
				$this->_errors[] = $this->l('Error while updating setting ref_orderid');
			if(!Configuration::updateValue('ORD_REF_PREFIXNULO', Tools::getValue('ref_prefixnulo')))
				$this->_errors[] = $this->l('Error while updating setting ref_prefixnulo');
			if(!Configuration::updateValue('ORD_REF_PREFIXNULNRO', Tools::getValue('ref_prefixnulnro')))
				$this->_errors[] = $this->l('Error while updating setting ref_prefixnulnro');
			if(!Configuration::updateValue('ORD_REF_PREFIXSIGNO', Tools::getValue('ref_prefixsigno')))
				$this->_errors[] = $this->l('Error while updating setting ref_prefixsigno');
			if(!Configuration::updateValue('ORD_REF_CARTID', Tools::getValue('ref_cartid')))
				$this->_errors[] = $this->l('Error while updating setting ref_cartid');
			if(!Configuration::updateValue('ORD_REF_PREFIXNULC', Tools::getValue('ref_prefixnulc')))
				$this->_errors[] = $this->l('Error while updating setting ref_prefixnulc');
			if(!Configuration::updateValue('ORD_REF_PREFIXNULNRC', Tools::getValue('ref_prefixnulnrc')))
				$this->_errors[] = $this->l('Error while updating setting ref_prefixnulnrc');
			if(!Configuration::updateValue('ORD_REF_PREFIXSIGNC', Tools::getValue('ref_prefixsignc')))
				$this->_errors[] = $this->l('Error while updating setting ref_prefixsignc');
			if(!Configuration::updateValue('ORD_REF_PREFIXSIGN', Tools::getValue('ref_prefixsign')))
				$this->_errors[] = $this->l('Error while updating setting ref_prefixsign');
			if(!Configuration::updateValue('INV_REF_ORDERID', Tools::getValue('inv_orderid')))
				$this->_errors[] = $this->l('Error while updating setting inv_orderid');
			if(!Configuration::updateValue('INV_REF_CARTID', Tools::getValue('inv_cartid')))
				$this->_errors[] = $this->l('Error while updating setting inv_cartid');
			if(!empty($matches[1]) || empty($inv_prefixsign)){
				if(!Configuration::updateValue('INV_REF_PREFIXSIGN', Tools::getValue('inv_prefixsign')))
					$this->_errors[] = $this->l('Error while updating setting inv_prefixsign');
			} else {
				$this->_errors[] = $this->l('Invalid date/time format for setting prefix on invoice and delivery slip number');
			}
		}
		if(empty($this->_errors))
			$this->_html .= '<div class="conf confirm"> '.$this->l('Settings updated').'</div>';
	}

	private function _displayForm()
	{
		$this->_html .= '<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post">
			<fieldset>
			<legend><img src="../img/admin/cog.gif" />'.$this->l('Order reference settings').'</legend>
				<table border="0" width="700" cellpadding="0" cellspacing="0" id="form">
					<tr>
						<td colspan="2">
							'.$this->l('Please specify the settings for the order reference change').'.<br /><br />
						</td>
					</tr>
					<tr>
						<td width="300" style="height: 35px;">
							'.$this->l('Use Order ID').'
						</td>
						<td>
							&nbsp;&nbsp;
							<input type="radio" name="ref_orderid" id="ref_orderid" value="1" '. ((Tools::getValue('ref_orderid', $this->ref_orderid)) ? 'checked="checked"' : '') .' />
							<label class="t" for="active_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" style="cursor:pointer" /></label>
							&nbsp;&nbsp;
							<input type="radio" name="ref_orderid" id="ref_orderid" value="0" '. ((Tools::getValue('ref_orderid', $this->ref_orderid)) ? '' : 'checked="checked"') .' />
							<label class="t" for="active_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" style="cursor:pointer" /></label>
							<p class="preference_description">'.$this->l('Use the Order ID instead of the random characters as Order reference').'.</p>
						</td>
					</tr>
					<tr>
						<td width="300" style="height: 35px;">
							'.$this->l('Use Zeros to prefix Order ID').'
						</td>
						<td>
							&nbsp;&nbsp;
							<input type="radio" name="ref_prefixnulo" id="ref_prefixnulo" value="1" '. ((Tools::getValue('ref_prefixnulo', $this->ref_prefnulo)) ? 'checked="checked"' : '') .' />
							<label class="t" for="active_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" style="cursor:pointer" /></label>
							&nbsp;&nbsp;
							<input type="radio"name="ref_prefixnulo" id="ref_prefixnulo" value="0" '. ((Tools::getValue('ref_prefixnulo', $this->ref_prefnulo)) ? '' : 'checked="checked"') .' />
							<label class="t" for="active_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" style="cursor:pointer" /></label>
							<p class="preference_description">'.$this->l('Prefix the Order ID with zeros (e.g. \'000000001\', \'000000010\', \'00000000[ORDER_ID]\')').'.</p>
						</td>
					</tr>
					<tr>
						<td width="300" style="height: 35px;">
							'.$this->l('Number of zeros to prefix Order ID').'
						</td>
						<td>
							<input type="text" name="ref_prefixnulnro" value="'.htmlentities(Tools::getValue('ref_prefixnulnro', $this->ref_prefnulnro), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" />
							<p class="preference_description">'.$this->l('Number of zeros to use as padding. Must be between 1 and 10').'.</p>
						</td>
					</tr>
					<tr>
						<td width="300" style="height: 35px;">
							'.$this->l('Use Character(s) to prefix Order ID').'
						</td>
						<td>
							<input type="text" name="ref_prefixsigno" value="'.htmlentities(Tools::getValue('ref_prefixsigno', $this->ref_prefsigno), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" />
							<p class="preference_description">'.$this->l('Prefix the Order ID with one or more characters (e.g. \'O1\', \'ORD_10\')').'<br>'.$this->l('Leave empty to not use prefix.').'<br>'.$this->l('You can also use date/time format (e.g. %Y) See ').' <a href="www.php.net/manual/function.strftime.php" target="_blank">strftime</a> '.$this->l('for format.').'.</p>
						</td>
					</tr>
					<tr>
						<td width="300" style="height: 35px;">
							'.$this->l('Use Cart ID').'
						</td>
						<td>
							&nbsp;&nbsp;
							<input type="radio" name="ref_cartid" id="ref_cartid" value="1" '. ((Tools::getValue('ref_cartid', $this->ref_cartid)) ? 'checked="checked"' : '') .' />
							<label class="t" for="active_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" style="cursor:pointer" /></label>
							&nbsp;&nbsp;
							<input type="radio" name="ref_cartid" id="ref_cartid" value="0" '. ((Tools::getValue('ref_cartid', $this->ref_cartid)) ? '' : 'checked="checked"') .' />
							<label class="t" for="active_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" style="cursor:pointer" /></label>
							<p class="preference_description">'.$this->l('Use the Cart ID instead of the random characters as Order reference').'.</p>
						</td>
					</tr>
					<tr>
						<td width="300" style="height: 35px;">
							'.$this->l('Use Zeros to prefix Cart ID').'
						</td>
						<td>
							&nbsp;&nbsp;
							<input type="radio" name="ref_prefixnulc" id="ref_prefixnulc" value="1" '. ((Tools::getValue('ref_prefixnulc', $this->ref_prefnulc)) ? 'checked="checked"' : '') .' />
							<label class="t" for="active_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" style="cursor:pointer" /></label>
							&nbsp;&nbsp;
							<input type="radio"name="ref_prefixnulc" id="ref_prefixnulc" value="0" '. ((Tools::getValue('ref_ref_prefixnulc', $this->ref_prefnulc)) ? '' : 'checked="checked"') .' />
							<label class="t" for="active_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" style="cursor:pointer" /></label>
							<p class="preference_description">'.$this->l('Prefix the Cart ID with zeros (e.g. \'000000001\', \'000000010\', \'00000000[CART_ID]\')').'.</p>
						</td>
					</tr>
					<tr>
						<td width="300" style="height: 35px;">
							'.$this->l('Number of zeros to prefix Cart ID').'
						</td>
						<td>
							<input type="text" name="ref_prefixnulnrc" value="'.htmlentities(Tools::getValue('ref_prefixnulnrc', $this->ref_prefnulnrc), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" />
							<p class="preference_description">'.$this->l('Number of zeros to use as padding. Must be between 1 and 10').'.</p>
						</td>
					</tr>
					<tr>
						<td width="300" style="height: 35px;">
							'.$this->l('Use Character(s) to prefix Cart ID').'
						</td>
						<td>
							<input type="text" name="ref_prefixsignc" value="'.htmlentities(Tools::getValue('ref_prefixsignc', $this->ref_prefsignc), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" />
							<p class="preference_description">'.$this->l('Prefix the Cart ID with one or more characters (e.g. \'C1\', \'CID_10\')').'<br>'.$this->l('Leave empty to not use prefix').'<br>'.$this->l('You can also use date/time format (e.g. %Y) See ').' <a href="www.php.net/manual/function.strftime.php" target="_blank">strftime</a> '.$this->l('for format.').'.</p>
						</td>
					</tr>
					<tr>
						<td width="300" style="height: 35px;">
							'.$this->l('Use Characters to prefix Order Reference').'
						</td>
						<td>
							<input type="text" name="ref_prefixsign" value="'.htmlentities(Tools::getValue('ref_prefixsign', $this->ref_prefsign), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" />
							<p class="preference_description">'.$this->l('Prefix the Order Reference with one or more characters (e.g. \'O1\', \'ORD_10\')').'<br />'.$this->l('Leave empty to not use prefix').'<br>'.$this->l('You can also use date/time format (e.g. %Y) See ').' <a href="www.php.net/manual/function.strftime.php" target="_blank">strftime</a> '.$this->l('for format.').'</p>
						</td>
					</tr>
					<tr>
						<td colspan="2" align="center">
							<hr><br />
							'.$this->l('Please specify the settings for the invoice and delivery slip change').'.<br /><br />
						</td>
					</tr>
					<tr>
						<td width="300" style="height: 35px;">
							'.$this->l('Use Order ID').'
						</td>
						<td>
							&nbsp;&nbsp;
							<input type="radio" name="inv_orderid" id="inv_orderid" value="1" '. ((Tools::getValue('inv_orderid', $this->inv_orderid)) ? 'checked="checked"' : '') .' />
							<label class="t" for="active_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" style="cursor:pointer" /></label>
							&nbsp;&nbsp;
							<input type="radio" name="inv_orderid" id="inv_orderid" value="0" '. ((Tools::getValue('inv_orderid', $this->inv_orderid)) ? '' : 'checked="checked"') .' />
							<label class="t" for="active_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" style="cursor:pointer" /></label>
							<p class="preference_description">'.$this->l('Use the Order ID instead of the invoice/deliveryslip sequence number as invoice or slip number.').'<br />'.$this->l('Note: If an order contains more than one invoice a sequence number will be added to the generated new invoice number.').'.</p>
						</td>
					</tr>
					<tr>
						<td width="300" style="height: 35px;">
							'.$this->l('Use Cart ID').'
						</td>
						<td>
							&nbsp;&nbsp;
							<input type="radio" name="inv_cartid" id="inv_cartid" value="1" '. ((Tools::getValue('inv_cartid', $this->inv_cartid)) ? 'checked="checked"' : '') .' />
							<label class="t" for="active_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" style="cursor:pointer" /></label>
							&nbsp;&nbsp;
							<input type="radio" name="inv_cartid" id="inv_cartid" value="0" '. ((Tools::getValue('inv_cartid', $this->inv_cartid)) ? '' : 'checked="checked"') .' />
							<label class="t" for="active_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" style="cursor:pointer" /></label>
							<p class="preference_description">'.$this->l('Use the Cart ID instead of the invoice/deliveryslip sequence number as invoice or slip number.').'.</p>
						</td>
					</tr>
					<tr>
						<td width="300" style="height: 35px;">
							'.$this->l('Use Date and/or Time formatting to prefix invoice and delivery slip').'
						</td>
						<td>
							<input type="text" name="inv_prefixsign" value="'.htmlentities(Tools::getValue('inv_prefixsign', $this->inv_prefsign), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" />
							<p class="preference_description">'.$this->l('Prefix the invoice and delivery slip with date/time format (e.g. %Y) See').' <a href="www.php.net/manual/function.strftime.php" target="_blank">strftime</a> '.$this->l('for format.').'<br>'.$this->l('Leave empty to not use prefix').'.</p>
						</td>
					</tr>
					<tr>
						<td colspan="2" align="center">
							<input class="button" name="btnSubmit" value="'.$this->l('Update settings').'" type="submit" />
						</td>
					</tr>
				</table>
			</fieldset>
		</form>
		<div style="position:absolute; text-align: center; top:150px; right:40px; width: 350px; height: 60px; background: rgba(200, 238, 238, 0.5); -webkit-border-radius: 8px; -moz-border-radius: 8px; border-radius: 8px; border: 2px solid #000;">
			'.$this->l('Please make a small donation<br>if you love this module and want to support future development.').'
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="hosted_button_id" value="LKUDB4H9DAXG6">
			<input type="image" src="https://www.paypalobjects.com/'.$this->context->language->iso_code.'_'.strtoupper($this->context->language->iso_code).'/'.strtoupper($this->context->language->iso_code).'/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="'.$this->l('PayPal, the complete and safe way of paying online').'">
			<img alt="" border="0" src="https://www.paypalobjects.com/'.$this->context->language->iso_code.'_'.strtoupper($this->context->language->iso_code).'/i/scr/pixel.gif" width="1" height="1">
			</form>
		</div>';
	}

	public function getContent()
	{
		$this->_html = '<h2>'.$this->displayName.'</h2>';

		if (Tools::isSubmit('btnSubmit'))
		{
			$this->_postProcess();
		}
		else
			$this->_html .= '<br />';

		if(!empty($this->_errors)){
			$this->_html .= '<div class="module_error alert error">';
			foreach($this->_errors as $error)
				$this->_html .= $error.'<br />';
			$this->_html .= '</div>';
		}
		
		$this->_displayForm();

		return $this->_html;
	}
	
	private function _getLastOrder()
	{
		return Db::getInstance()->getValue('
			SELECT MAX(`id_order`)
			FROM `'._DB_PREFIX_.'orders`
		');
	}
	
	public function generateReferenceFromID($id_order = NULL, $id_cart = NULL, $reference = NULL, $sequence = 0)
	{
		$reforder = '';
		$refcart = '';
		$ref = '';
		$dtpattern = '/(\w*)(%[a-zA-Z]*)(\w*)/';

		if(!$id_order)
			$id_order = (int)$this->_getLastOrder()+1;
			
		if($this->ref_orderid){
			$reforder = $id_order;
			if($this->ref_prefnulo)
				$reforder = sprintf('%0'.$this->ref_prefnulnro.'d', $reforder);
			if($this->ref_prefsigno){
				if(preg_match($dtpattern, $this->ref_prefsigno))
				{
					preg_match_all($dtpattern, $this->ref_prefsigno, $matches);
					$pat = '';
					foreach($matches[2] as $match)
						$pat.= $match;
					$datesign = strftime($pat, time());
					$str = '';
					foreach($matches[1] as $match)
						$str .= $match;
					$str .= $datesign;
					foreach($matches[3] as $match)
						$str .= $match;
					$reforder = $str.''.$reforder;
				}
				$reforder = $this->ref_prefsigno.$reforder;
			}
		}
		if($this->ref_cartid){
			$refcart = $id_cart;
			if($this->ref_prefnulc)
				$refcart = sprintf('%0'.$this->ref_prefnulnrc.'d', $refcart);
			if($this->ref_prefsignc){
				if(preg_match($dtpattern, $this->ref_prefsignc))
				{
					preg_match_all($dtpattern, $this->ref_prefsignc, $matches);
					$pat = '';
					foreach($matches[2] as $match)
						$pat.= $match;
					$datesign = strftime($pat, time());
					$str = '';
					foreach($matches[1] as $match)
						$str .= $match;
					$str .= $datesign;
					foreach($matches[3] as $match)
						$str .= $match;
					$refcart = $str.''.$refcart;
				} else
					$refcart = $this->ref_prefsignc.''.$refcart;
			}
		}

		if($reforder && !$refcart){
			$ref = $reforder;
		} elseif($reforder && $refcart){
			$ref = $reforder.''.$refcart;
		} elseif(!$reforder && $refcart){
			$ref = $refcart;
		} elseif(!$reforder && !$refcart){
			$ref = $reference;
		}

		if($this->ref_prefsign){
			print(preg_match($dtpattern, $this->ref_prefsign).'<br>');
			if(preg_match($dtpattern, $this->ref_prefsign))
			{
				preg_match_all($dtpattern, $this->ref_prefsign, $matches);
				print('preg matches:<pre>');print_r($matches);print('</pre>');
				$pat = '';
				foreach($matches[2] as $match)
					$pat.= $match;
				$datesign = strftime($pat, time());
				$str = '';
				foreach($matches[1] as $match)
					$str .= $match;
				$str .= $datesign;
				foreach($matches[3] as $match)
					$str .= $match;
				$ref = $str.''.$ref;
			} else
				$ref = $this->ref_prefsign.''.$ref;
		}

		if($sequence)
			$ref = $ref.'_'.$sequence;
			
		$sequence++;
			
		// First find if an order reference with the defined Order ID
		if($result = Db::getInstance()->getValue('SELECT reference FROM '._DB_PREFIX_.'orders WHERE reference = \''.$ref.'\' ORDER BY id_order DESC'))
			return $this->generateReferenceFromID($ref, $id_cart, $reference, $sequence);
		else
			return $ref;
	}
	
	public function generateInvFromID($id_order = NULL, $id_cart = NULL, $reference = NULL, $type = 'invoice', $sequence = 0)
	{
		$invorder = '';
		$invcart = '';
		$inv = '';
		$dtpattern = '/(%[dejuwUVWmCgGyYHkIMSs]*)/';

		if(!$id_order)
			$id_order = (int)$this->_getLastOrder()+1;
			
		if($this->inv_orderid)
			$invorder = $id_order;
		if($this->inv_cartid)
			$invcart = $id_cart;

		if($invorder && !$invcart){
			$inv = $invorder;
		} elseif($invorder && $invcart){
			$inv = $invorder.$invcart;
		} elseif(!$invorder && $invcart){
			$inv = $invcart;
		} elseif(!$invorder && !$invcart){
			$inv = $reference;
		}

		if($this->inv_prefsign){
			if(preg_match($dtpattern, $this->inv_prefsign))
			{
				preg_match_all($dtpattern, $this->inv_prefsign, $matches);
				$pat = '';
				foreach($matches[1] as $match)
					$pat.= $match;
				$inv = strftime($pat, time()).$inv;
				
			}
		}

		if($sequence)
			$inv = $inv.''.$sequence;
			
		$sequence++;
			
		// First find if an order reference with the defined Order ID already exists, if so repeat with sequence. Else return generated ref.
		switch($type){
			case 'invoice':
				if($result = Db::getInstance()->getValue('SELECT invoice_number FROM '._DB_PREFIX_.'orders WHERE invoice_number = \''.$inv.'\' ORDER BY id_order DESC'))
					return $this->generateInvFromID($inv, $id_cart, $reference, $type, $sequence);
				else
					return $inv;
			break;
			case 'slip':
				return $inv;
			break;
		}
	}
	
	public function hookactionBeforeAddOrder($params)
	{
		if (!$this->active)
			return false;
		
		if($this->ref_orderid OR $this->ref_cartid OR $this->ref_prefsign){
			$order_payment = new OrderPayment();
			$transaction_id = $order_payment->getByOrderReference($params['order']->reference);
			if($ref = $this->generateReferenceFromID($params['order']->id, $params['cart']->id, $params['order']->reference))
				$params['order']->reference = $ref;
			
			if(count($transaction_id)){
				foreach($transaction_id as $transaction){
					$transaction->order_reference = $params['order']->reference;
					$transaction->update();
				}
			}
			return $ref;
		} else {
			return false;
		}
	}
	
	public function hookactionBeforeAddOrderInvoice($params)
	{
		if (!$this->active)
			return false;
		
		if($this->inv_orderid OR $this->inv_cartid OR $this->inv_prefsign){
			if($ref = $this->generateInvFromID($params['order']->id, $params['cart']->id, $params['order_invoice']->number)){
				return $ref;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	public function hookactionBeforeAddDeliveryNumber($params)
	{
		if (!$this->active)
			return false;
		
		if($this->inv_orderid OR $this->inv_cartid OR $this->inv_prefsign){
			if($ref = $this->generateInvFromID($params['order']->id, $params['cart']->id, $params['number'], 'slip')){
				return $ref;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * Uninstall overrides files for the module
	 *
	 * @return bool
	 */
	public function uninstallOldOverrides()
	{
		if (!is_dir(_PS_MODULE_DIR_.$this->name.'/old_override/classes'))
			return true;

		$result = true;
		foreach (Tools::scandir(_PS_MODULE_DIR_.$this->name.'/old/classes', 'php', '', true) as $file)
		{
			$class = basename($file, '.php');
			if (Autoload::getInstance()->getClassPath($class.'Core'))
				$result &= $this->removeOldOverride($class);
		}
		return $result;
	}
	
	/**
	 * Remove all methods in a module override from the override class
	 *
	 * @param string $classname
	 * @return bool
	 */
	public function removeOldOverride($classname)
	{
		$path = Autoload::getInstance()->getClassPath($classname.'Core');

		if (!Autoload::getInstance()->getClassPath($classname))
			return true;

		// Check if override file is writable
		$override_path = _PS_ROOT_DIR_.'/'.Autoload::getInstance()->getClassPath($classname);
		if (!is_writable($override_path))
			return false;

		// Get a uniq id for the class, because you can override a class (or remove the override) twice in the same session and we need to avoid redeclaration
		do $uniq = uniqid();
		while (class_exists($classname.'OverrideOriginal_remove', false));
			
		// Make a reflection of the override class and the module override class
		$override_file = file($override_path);
		eval(preg_replace(array('#^\s*<\?php#', '#class\s+'.$classname.'\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?#i'), array('', 'class '.$classname.'OverrideOriginal_remove'.$uniq), implode('', $override_file)));
		$override_class = new ReflectionClass($classname.'OverrideOriginal_remove'.$uniq);

		$module_file = file($this->getLocalPath().'old/classes'.$path);
		eval(preg_replace(array('#^\s*<\?php#', '#class\s+'.$classname.'(\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?)?#i'), array('', 'class '.$classname.'Override_remove'.$uniq), implode('', $module_file)));
		$module_class = new ReflectionClass($classname.'Override_remove'.$uniq);

		// Remove methods from override file
		$override_file = file($override_path);
		foreach ($module_class->getMethods() as $method)
		{
			if (!$override_class->hasMethod($method->getName()))
				continue;

			$method = $override_class->getMethod($method->getName());
			$length = $method->getEndLine() - $method->getStartLine() + 1;
			array_splice($override_file, $method->getStartLine() - 1, $length, array_pad(array(), $length, '#--remove--#'));
		}

		// Remove properties from override file
		foreach ($module_class->getProperties() as $property)
		{
			if (!$override_class->hasProperty($property->getName()))
				continue;

			// Remplacer la ligne de declaration par "remove"
			foreach ($override_file as $line_number => &$line_content)
				if (preg_match('/(public|private|protected|const)\s+(static\s+)?(\$)?'.$property->getName().'/i', $line_content))
				{
					$line_content = '#--remove--#';
					break;
				}
		}

		// Rewrite nice code
		$code = '';
		foreach ($override_file as $line)
		{
			if ($line == '#--remove--#')
				continue;

			$code .= $line;
		}
		file_put_contents($override_path, $code);

		return true;
	}
}