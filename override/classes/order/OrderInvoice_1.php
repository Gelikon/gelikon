<?php

class OrderInvoice extends OrderInvoiceCore
{
	public function add($autodate = true, $null_values = true)
	{
		$order = new Order($this->id_order);
		$cart = new Cart($order->id_cart);
		Hook::exec('actionBeforeAddOrderInvoice', array('order_invoice'=>$this, 'order'=>$order,'cart'=>$cart));

		return parent::add($autodate, $null_values);
	}
}