<?php

class Order extends OrderCore
{

	public function add($autodate = true, $null_values = true)
	{
		$cart = new Cart($this->id_cart);
		Hook::exec('actionBeforeAddOrder', array('order'=>$this,'cart'=>$cart));

		if (ObjectModel::add($autodate, $null_values))
			return SpecificPrice::deleteByIdCart($this->id_cart);
		return false;
	}
	
	public static function setLastInvoiceNumber($order_invoice_id, $id_shop)
	{
		if (!$order_invoice_id)
			return false;

		$number = Configuration::get('PS_INVOICE_START_NUMBER', null, null, $id_shop);
		// If invoice start number has been set, you clean the value of this configuration
		if ($number)
			Configuration::updateValue('PS_INVOICE_START_NUMBER', false, false, null, $id_shop);
			
		$order_invoice = new OrderInvoice($order_invoice_id);
		$order = new Order($order_invoice->id_order);
		$cart = new Cart($order->id_cart);
		
		if($ref = Hook::exec('actionBeforeAddOrderInvoice', array('order_invoice'=>$order_invoice,'order'=>$order,'cart'=>$cart)))
			$number = $ref;

		$sql = 'UPDATE `'._DB_PREFIX_.'order_invoice` SET number =';

		if ($number)
			$sql .= (int)$number;
		else
			$sql .= '(SELECT new_number FROM (SELECT (MAX(`number`) + 1) AS new_number
			FROM `'._DB_PREFIX_.'order_invoice`) AS result)';

		$sql .=' WHERE `id_order_invoice` = '.(int)$order_invoice_id;

		return Db::getInstance()->execute($sql);
	}
	
	public function setDeliveryNumber($order_invoice_id, $id_shop)
	{
		if (!$order_invoice_id)
			return false;

		$number = Configuration::get('PS_DELIVERY_NUMBER', null, null, $id_shop);
		// If invoice start number has been set, you clean the value of this configuration
		if ($number)
			Configuration::updateValue('PS_DELIVERY_NUMBER', false, false, null, $id_shop);
			
		$order_invoice = new OrderInvoice($order_invoice_id);
		$order = new Order($order_invoice->id_order);
		$cart = new Cart($order->id_cart);
		
		if($ref = Hook::exec('actionBeforeAddDeliveryNumber', array('order'=>$order,'cart'=>$cart,'number'=>$number)))
			$number = $ref;

		$sql = 'UPDATE `'._DB_PREFIX_.'order_invoice` SET delivery_number =';

		if ($number)
			$sql .= (int)$number;
		else
			$sql .= '(SELECT new_number FROM (SELECT (MAX(`delivery_number`) + 1) AS new_number
			FROM `'._DB_PREFIX_.'order_invoice`) AS result)';

		$sql .=' WHERE `id_order_invoice` = '.(int)$order_invoice_id;

		return Db::getInstance()->execute($sql);
	}
}

