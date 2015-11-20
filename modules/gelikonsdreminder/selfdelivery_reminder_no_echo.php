<?php
/* Напоминание об истечении срока хранения заказа на самовывозе - обновление статуса и отправка e-mail
* Скромно писал Таников Алексей atanikov@gmail.com
* 2007-2013 PrestaShop
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
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
* PrestaShop Webservice Library
* @package PrestaShopWebservice
*/
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
/* всё, что выводится в stdout собираем в буфер*/
	ob_start();
$log_str = "=============\r\n";
$msg = "Обновление статусов заказов начато ".date("Y-m-d H:i:s");
//echo $msg." <br/>";
$log_str .= $msg."\r\n";
// Here we define constants /!\ You need to replace this parameters
define('DEBUG', true);											// Debug mode
define('PS_SHOP_PATH', 'http://'.$_SERVER['SERVER_NAME'].'/');		// Root path of your PrestaShop store
define('PS_WS_AUTH_KEY', 'EIY3ASRCW6A7UNHVE6PC7JREMS8IT1TJ'); 	// Auth key (Get it in your Back Office)
define('READY_FOR_SD_STATE_ID',14);								// Статус заказа "Ваш заказ гото для самовывоза"
define('SD_EXPIRES_STATE_ID',15);								// Статус заказа "Истекает срок хранения заказа для самовывоза"
define('N',14); 													// Через сколько дней высылать напоминание о самовывозе
define('ID_EMPLOYEE',0);										// Сотрудник, от чьего имени обновляется статус
define('LOG_FILENAME', 'selfdelivery_state_change.log');

require_once('./PSWebServiceLibrary.php');
$n = (isset($_GET['n'])&&!empty($_GET['n'])&&is_numeric($_GET['n']))?$_GET['n']:N;
$log_str .= "Установлен срок хранения ".$n." дней\r\n";
// Here we make the WebService Call
try
{
	$webService = new PrestaShopWebservice(PS_SHOP_PATH, PS_WS_AUTH_KEY, DEBUG);
	
	// Here we set the option array for the Webservice : we want orders resources
	$opt['resource'] = 'orders';
	
	// Call
	$xml = $webService->get($opt);

	// Here we get the elements from children of customers markup "orders"
	$orders_resources = $xml->orders->children();
}
catch (PrestaShopWebserviceException $e)
{
	// Here we are dealing with errors
	$trace = $e->getTrace();
	if ($trace[0]['args'][0] == 404) echo 'Bad ID';
	else if ($trace[0]['args'][0] == 401) echo 'Bad auth key';
	else echo 'Other error';
}

// if $resources is set we can lists element in it otherwise do nothing cause there's an error
if (isset($orders_resources))
{
	/* Перебираем все заказы, полученные от PS вебсервиса */
	foreach ($orders_resources as $resource)
	{
		// Iterates on the found IDs
		$order_id = (int)$resource->attributes();
		/* Получаем детали текущего заказа от вебсервиса /orders/id */
		try
		{
			//$webService = new PrestaShopWebservice(PS_SHOP_PATH, PS_WS_AUTH_KEY, DEBUG);
			// Here we set the option array for the Webservice : we want orders resources
			$opt['resource'] = 'orders';
			// We set an id if we want to retrieve infos from a order
			$opt['id'] = $order_id;
			// Call
			$xml = $webService->get($opt);
			// Here we get the elements from children of order markup which is children of prestashop root markup
			$orderdetails = $xml->children()->children();
		}
		catch (PrestaShopWebserviceException $e)
		{
			// Here we are dealing with errors
			$trace = $e->getTrace();
			if ($trace[0]['args'][0] == 404) echo 'Bad ID';
			else if ($trace[0]['args'][0] == 401) echo 'Bad auth key';
			else echo 'Other error';
		}

		/* Определяем, что статус заказа READY_FOR_SD_STATE_ID */
		if ($orderdetails->current_state==READY_FOR_SD_STATE_ID) {
			/* Вычисляем, сколько дней прошло со дня установления статуса */
			$date_upd = date_parse_from_format("Y-m-d H:i:s", $orderdetails->date_upd);
			$date_upd = mktime (0, 0, 0, $date_upd['month'], $date_upd['day'], $date_upd['year']); //дата обновления статуса с точностью до дня
			//echo date('c',$date_upd).' '.date('c',time()).' '.date('c',strtotime('today midnight'));
			$today_midnight = strtotime('today midnight'); //сегодняшняя дата с точностью до дня (полночь)
			//if (($today_midnight - $date_upd) >= 60*60*24*$n) {
			if (1){
				/* прошло больше N дней со дня установки статуса EADY_FOR_SD_STATE_ID. надо обновить статус этого заказа на SD_EXPIRES_STATE_ID */
				// Here we use the WebService to get the schema of "order histories" resource
				try
				{
					$xml = $webService->get(array('url' => PS_SHOP_PATH.'/api/order_histories?schema=blank'));
					$resources = $xml->children()->children();
				}
				catch (PrestaShopWebserviceException $e)
				{
					// Here we are dealing with errors
					$trace = $e->getTrace();
					if ($trace[0]['args'][0] == 404) echo 'Bad ID';
					else if ($trace[0]['args'][0] == 401) echo 'Bad auth key';
					else echo 'Other error';
				}

				// Here we have XML before update, lets update XML
				$resources->id_order_state = SD_EXPIRES_STATE_ID;
				$resources->id_order = $order_id;
				$resources->id_employee = ID_EMPLOYEE;
				try
				{
					$opt = array('resource' => 'order_histories?sendemail=1');
					$opt['postXml'] = $xml->asXML();
					$xml = $webService->add($opt);
					$msg = "Заказ id ".$order_id.' был статус '.READY_FOR_SD_STATE_ID.', обновлен '.$orderdetails->date_upd.'. Стал '.SD_EXPIRES_STATE_ID.', '.date("Y-m-d H:i:s");
					//echo $msg." <br/>";
					$log_str .= $msg."\r\n";
				}
				catch (PrestaShopWebserviceException $ex)
				{
					// Here we are dealing with errors
					$trace = $ex->getTrace();
					if ($trace[0]['args'][0] == 404) echo 'Bad ID';
					else if ($trace[0]['args'][0] == 401) echo 'Bad auth key';
					else echo 'Other error<br />'.$ex->getMessage();
				}
			}
			//else {echo "Заказ id ".$order_id.". Статус ".READY_FOR_SD_STATE_ID.". Прошло меньше ".N." дней </br>";}
		}
	}
}
$msg = "Обновление статусов заказов закончено ".date("Y-m-d H:i:s");
//echo $msg." <br/>";
$log_str .= $msg."\r\n";
$log_str .= ob_get_contents();
//ob_end_clean();
file_put_contents ( LOG_FILENAME , $log_str, FILE_APPEND);
?>