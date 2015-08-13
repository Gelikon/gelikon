<?php

/*
TODO

добавиьт дату обновления order
менять статус (order, order_status_history)
добавить загрузку файла
распознавать дату в платежку
все echo и var_dump в лог как в вебсервисе
*/

//Предотвращаем загрузку модуля при прямом обращении к файлу
if (!defined('_CAN_LOAD_FILES_'))
	exit;
//Класс нашего модуля "gelikoncheckbankpayments" является потомком базового класса для модулей "Module"
class Gelikoncheckbankpayments extends Module
{
	private $tmp_url = '';
//Конструктор класса __construct () позволяет идентифицировать модуль
//и отобразить его в списке модулей
	public function __construct()
	{
//Свойства класса, заполняемые в методе __construct, соответсвуют тем,
//что были определены в файле config.xml
		$this->name = 'gelikoncheckbankpayments';
		$this->tab = 'Catalog';
		$this->version = '0.1';
		$this->author = 'Gelikon';
 
		parent::__construct();
//После вызова конструктора родительского класса мы можем использовать
//предоставляемые им методы, а именно l(), который отвечает за локализацию строки
		$this->displayName = $this->l('Проверка оплаты заказов через банк');
		$this->description = $this->l('Проверка оплаты заказов со способом оплаты банковским переводом по выписке из банка в формате CSV.');
		$this->confirmUninstall = $this->l('Действительно хотите удалить модуль?');
	}

	/* Админка модуля */
	//в эту переменную будем записывать выводимый в админке текст
	private $_html = '';
	private $downloadLink = '';

	//список неоплаченных заказов
	private $unpaidOrders = array();
	//список платежей из клиент-банка
	private $paymentsRaw = array();
	//список расползнанных платежек
	private $payments = array();
	private $referencePrefix = "OrderNum_";
	private $invoicePrefix = "GELINV";
	private $paymentMethod = "Банковский перевод";
	private $paymentsSubmitted = 0;
	private $id_currency = 1;

	 
	//функция getContent() вызывается при нажатии ссылки "настройки"
	public function getContent()
	{
		//Обработка отправленной формы
		//$this->_postProcess();
		//Создаем список номеров печатных каталогов и выводим форму
		//$this->_printDateForm();
		//Возвращаем отображаемое содержимое
		
		$this->_get_bank_payments();
		var_dump($this->payments);
		$this->getLostNums();
		$this->checkPaymentsNeeded();
		var_dump($this->payments);
		$this->showBankPayments();
		$this->processPayments();
		$this->_get_unpaid_orders();
		$this->showUnpaidOrders();
		$this->showBankPaymentsRaw();
		
		return $this->_html;
	}

	//Получим список номеров печатных каталогов
	private function _printDateForm() 
	{	

			$this->_html .= '
			<style>
			.date_field_wrapper {
			display: inline-block;
			margin-right: 12px;
			</style>
			<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
				<fieldset>
					<legend>'.$this->l('Налоги в заказах').'</legend>
					<div class="panel-heading">
						<i class="icon-calendar"></i>'.$this->l('Выберите период').'
					</div>
					<label for="from_date">'.$this->l('Начало периода').'</label>
					<div class="margin-form">
						<div class="date_field_wrapper">
							<select name="from_day" id="from_day">';
							for ($i=1; $i<=31; $i++)
							{
								$selected = "";
								if (Tools::getValue('from_day')==$i) 
									$selected = " selected";
								$this->_html .= ' <option value="'.$i.'"'.$selected.'>'.$i.'</option>';
							}
							$this->_html .= '
							</select>
							<p class="clear">'.$this->l('День').'</p>
						</div>
						<div class="date_field_wrapper">
							<select name="from_month" id="from_month">';
							for ($i=1; $i<=12; $i++)
							{
								$selected = "";
								if (Tools::getValue('from_month')==$i) 
									$selected = " selected";
								$this->_html .= ' <option value="'.$i.'"'.$selected.'>'.$i.'</option>';
							}
								
							$this->_html .= '
							</select>
							<p class="clear">'.$this->l('Месяц').'</p>
						</div>
						<div class="date_field_wrapper">
							<select name="from_year" id="from_year">';
							for ($i=2014; $i<=2030; $i++)
							{
								$selected = "";
								if (Tools::getValue('from_year')==$i) 
									$selected = " selected";
								$this->_html .= ' <option value="'.$i.'"'.$selected.'>'.$i.'</option>';
							}
							$this->_html .= '
							</select>
							<p class="clear">'.$this->l('Год').'</p>
						</div>
					</div>
					<label for="till_date">'.$this->l('Конец периода').'</label>
					<div class="margin-form">
						<div class="date_field_wrapper">
							<select name="till_day" id="till_day">';
							for ($i=1; $i<=31; $i++)
							{
								$selected = "";
								if (Tools::getValue('till_day')==$i) 
									$selected = " selected";
								$this->_html .= ' <option value="'.$i.'"'.$selected.'>'.$i.'</option>';
							}
							$this->_html .= '
							</select>
							<p class="clear">'.$this->l('День').'</p>
						</div>
						<div class="date_field_wrapper">
							<select name="till_month" id="till_month">';
							for ($i=1; $i<=12; $i++)
							{
								$selected = "";
								if (Tools::getValue('till_month')==$i) 
									$selected = " selected";
								$this->_html .= ' <option value="'.$i.'"'.$selected.'>'.$i.'</option>';
							}
								
							$this->_html .= '
							</select>
							<p class="clear">'.$this->l('Месяц').'</p>
						</div>
						<div class="date_field_wrapper">
							<select name="till_year" id="till_year">';
							for ($i=2014; $i<=2030; $i++)
							{
								$selected = "";
								if (Tools::getValue('till_year')==$i) 
									$selected = " selected";
								$this->_html .= ' <option value="'.$i.'"'.$selected.'>'.$i.'</option>';
							}
							$this->_html .= '
							</select>
							<p class="clear">'.$this->l('Год').'</p>
						</div>
					</div>
					<label ></label>'.$this->downloadLink.
					'<p class="center">
						<input class="button" type="submit" name="exportOrder_taxes" value="'.$this->l('Выгрузить').'"/>

					</p>
				</fieldset>
			</form>';
	}


	//Для обработки отправленной формы создадим функцию _postProcess():

	private function _postProcess()
	{
		//Проверяем отправлена ли форма
		if(Tools::isSubmit('exportOrder_taxes'))
		{
			//Получаем значение поля формы с датами
			
			$from_day = sprintf("%02d", Tools::getValue('from_day')); 
			$from_month = sprintf("%02d", Tools::getValue('from_month'));
			$from_year = Tools::getValue('from_year');
			$till_day = sprintf("%02d",Tools::getValue('till_day'));
			$till_month = sprintf("%02d",Tools::getValue('till_month'));
			$till_year = Tools::getValue('till_year');
			$from_date = $from_year.'-'.$from_month.'-'.$from_day;
			$till_date = $till_year.'-'.$till_month.'-'.$till_day;
			//Проверяем валидность даты
			if(Validate::isDateFormat($from_date) && Validate::isDateFormat($till_date))
			{
				//$this->_html .= $this->displayConfirmation($this->l('Дата верная.'));
				//Сохраняем настройку
				//Configuration::updateValue('FROM_DATE', $from_date);
				$this->exportOrder_taxes ($from_date, $till_date);
				
			} else
				//Выводим сообщение об ошибке
				$this->_html .= $this->displayError($this->l('Неверная дата.'));
		}
	}

	private function _get_bank_payments ()
	{
		$delimiter = ";";
		$enclosure = '"';
		$escape = "\\";
		$payments= array();
		$bank_file = dirname(__FILE__)."/bank_export.csv";

		$row = 1;
		$payment_row = 0;
		$paymentRaw_row = 0;
		if (($handle = fopen($bank_file, "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 1000, $delimiter,$enclosure,$escape)) !== FALSE) {
		        if ($row>9)
		        {
		        	$data_filtered = array_filter($data); 
			        if (count($data_filtered)>0) //не добавляем пустые строки
			        {
			        	$this->paymentsRaw[$paymentRaw_row]['date'] = $data_filtered[1];
			        	$this->paymentsRaw[$paymentRaw_row]['ref_str'] = $data_filtered[3]; //TODO добавить поиск значения регулярным выражением См. validate адрес
				        $this->paymentsRaw[$paymentRaw_row]['name'] = $data_filtered[4];
				        $this->paymentsRaw[$paymentRaw_row]['sum'] = $data_filtered[6];//TODO преобразовать в число

				        $reference = array();
				        $invoice_num  = array();
				        $pattern = '/OrderNum_[0-9]{9}/';
				        $matches = '';
				        preg_match($pattern, $data_filtered[3], $matches);
				        $reference = empty($matches[0])? '' : $matches[0];

				        $pattern = '/GELINV[0-9]{6}/';
				        $matches = '';
						preg_match($pattern, $data_filtered[3], $matches);
						$invoice_num = empty($matches[0])? '' : $matches[0];
						if(!empty($reference) || !empty($invoice_num))
						{	
							$this->payments[$payment_row]['date'] = '2014-09-02 00:00:07';//$data_filtered[1]; //TODO преобразовать в php дату с ч м с
					        $this->payments[$payment_row]['reference'] = $reference;
					        $this->payments[$payment_row]['invoice_num'] = $invoice_num; 
					        $this->payments[$payment_row]['name'] = $data_filtered[4];
					        $this->payments[$payment_row]['sum'] = $this->Getfloat($data_filtered[6]);
					        $this->payments[$payment_row]['raw_ref'] = $data_filtered[3];
					        $payment_row++;
						}
						$paymentRaw_row++;
			        }
		      	}
		      	$row++;
		    }
			fclose($handle);
			$this->_html .= $this->displayConfirmation($this->l('Найдено '.$payment_row.' платежей в выписке из банк-клиента.'));
		}
		else 
		{
			$this->_html .= $this->displayError($this->l('Не удалось открыть банковскую выписку.'));
		}
	}

	private function showBankPayments ()
	{
		if (!empty($this->payments))
		{
			//Выводим таблицу с платежами
			$results = $this->payments;
			$titles = array("Дата платежа","Номер заказа","Номер счёта","Имя плательщика","Сумма <br/>платежа","Исходная строка","Заказ <br/>не <br/>оплачен","Добавить <br/>платеж");
			array_unshift($results,$titles);
			$this->_html .= '<h3>Распознанные платежи в выписке из банк-клиента</h3>';
			$this->_html .= 
			'
			<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<table style="border: 1px solid grey; font-size:12px;">';
			$chet = 0;
			$payment_needed = true;
			foreach($results as $num_row => $row) {
				if ($num_row == 0) {$bg_color = "#D1D1D1"; $red_found = true;}
				else 
				{
					$bg_color = ( $chet & 1 ) == 0? '#dff0d8' : 'rgb(202, 202, 255)'; //чередуем цвета строк
					if ($row['payment_needed']==false) $bg_color = '#FFEEEE';
				}
				$this->_html .='<tr style="border: 1px solid grey; background-color: '.$bg_color.';"><td style="border-right: 1px solid green; padding: 0 5px 0 5px;">';
				if ($num_row!=0)
				$row['raw_ref'] = '<span style="font-size:10px;max-width:200px;display:block;">'.$row['raw_ref'].'</span>';
				$this->_html .= implode('</td><td style="border-right: 1px solid green; padding: 0 5px 0 5px;">', $row);
				$this->_html .='</td>';
				if ($num_row!=0){
					if ($row['payment_needed']==true)
						$this->_html .='<td style="text-align:center;"><input type="checkbox" name="rows[]" value="'.($num_row-1).'" checked></td>';
					else 
						$this->_html .='<td style="text-align:center;"><input type="checkbox" name="rows[]" value="'.($num_row-1).'" ></td>';
				}
					
				$this->_html .='</tr>';
				$chet++;
			}
			$this->_html .= 
			'</table>';
			if ($red_found == true)
			$this->_html .= 'Красным выделены платежи, заказы которых уже оплачены или не найдены.<br/>';
			$this->_html .= '			
			<input class="button" style="margin:15px 0 0 0" type="submit" name="submitVerifiedPayments"value="Добавить платежи">
			</form>
			';
			$this->_html .= "<br/><br/>";
		}
	}

	private function getLostNums()
	{
		//находим номер заказа по номеру счёта
		foreach ($this->payments as $num_row => $payment)
		{
			//Если нет номера заказа но есть номер счёта, находим номер заказа по номеру счёта
			if (empty($this->payments[$num_row]['reference']) AND !empty($this->payments[$num_row]['invoice_num']))
			{
				//переводим номер счёта в число (без префикса)
				$invoice_num=(int)(str_replace($this->invoicePrefix, "", $this->payments[$num_row]['invoice_num']));
				$sql = "SELECT o.reference FROM  `ps_order_invoice` AS oi LEFT JOIN  `ps_orders` AS o ON oi.id_order = o.id_order WHERE oi.number =".$invoice_num;
				if ($results = Db::getInstance()->ExecuteS($sql)) 
				{
					$this->payments[$num_row]['reference']=$results[0]['reference'];
				}
				else {
					$this->_html .= $this->displayError($this->l('По счёту '.$this->payments[$num_row]['invoice_num'].' не найден заказ.'));
				}
			}
			
		}
	}

	private function checkPaymentsNeeded()
	{
		//по-умолчанию заказ у платежа оплачен
		//проверяем есть ли заказ, для которого нужен этот платеж
		$this->_get_unpaid_orders();
		foreach ($this->payments as $num_row => $payment){
			$this->payments[$num_row]['payment_needed'] = false;
			foreach ($this->unpaidOrders as $key => $unpaidOrder) {
				if ($unpaidOrder['reference'] == $payment['reference'])
				{
					$this->payments[$num_row]['payment_needed'] = true;
					break;
				}
			}
		}	
	}

	private function processPayments()
	{
		//Проверяем отправлена ли форма с проверенными платежами	
		if(Tools::isSubmit('submitVerifiedPayments'))
		{
			$checked_rows = array();
			//Получаем значение поля формы с проверенными платежами
			$checked_rows = Tools::getValue('rows');	
			foreach ($this->payments as $num_row => $payment) {
				if ($payment['payment_needed'] == true)
				{
					var_dump("платёж для неоплаченного заказа");
					//добавляем платеж в order_payment, orders
					$id_order_payment 	= $this->addOrderPayment($payment);
					$id_order 			= $this->updateOrderRealPayed($payment);
					if (($id_order_payment==true) AND ($id_order == true ))
					{
						$this->paymentsSubmitted++;
						echo "Заказ ".$payment['reference']." сохранена оплата";
						echo "Добавляем оплату счёта ".$payment['invoice_num']."<br/>";
						$this->addOrderInvoicepayment($payment,$id_order_payment, $id_order);
					}
				}

			}
					
			$this->_html .= $this->displayConfirmation($this->l('Сохранено '.$this->paymentsSubmitted.' платежей.'));			
		}	




	}

	private function addOrderPayment($payment)
	{
		$sql = "INSERT INTO  `"._DB_NAME_."`.`"._DB_PREFIX_."order_payment` (
		`id_order_payment` ,
		`order_reference` ,
		`id_currency` ,
		`amount` ,
		`payment_method` ,
		`conversion_rate` ,
		`transaction_id` ,
		`card_number` ,
		`card_brand` ,
		`card_expiration` ,
		`card_holder` ,
		`date_add`
		)
		VALUES (
		NULL , 
		'".pSQL($payment['reference'])."' ,  
		'".$this->id_currency."',  
		'".$payment['sum']."',  
		'".$this->paymentMethod."',  
		'1.000000',
		NULL , 
		NULL , 
		NULL , 
		NULL , 
		NULL ,  
		'".$payment['date']."'
		)";
		$db = Db::getInstance();
		if ($db->execute($sql))
		{
			echo "в order_payment добавлена строка ".$db->Insert_ID();
			return $db->Insert_ID();
		}
		else
		{
			$dbErrorMsg = Db::getInstance()->getMsgError();
			$this->_html .= $this->displayError($this->l('Ошибка добавления платежа. Заказ: '.$this->payments[$num_row]['reference'].', счёт : '.$this->payments[$num_row]['reference'].'. '.$dbErrorMsg));
			return false;
		}
	}

	private function updateOrderRealPayed($payment)
	{
		//получить по номеру заказа оплаченную ранее сумму, добавить сумму в платежке и записать в тот же заказа. Вернуть id заказа
		$db = Db::getInstance();
		$sql = "SELECT `id_order`,`total_paid_real` FROM `"._DB_PREFIX_."orders` WHERE `"._DB_PREFIX_."orders`.`reference` ='". pSQL($payment['reference'])."'";
		if ($row = $db->getRow($sql))
		{
			$id_order = $row['id_order'];
			$add_sum = $row['total_paid_real']+$payment['sum'];
			//писать в лог (см как я сделал в webservice для обнвления статусов)
			echo "Найден заказ ".$id_order." для обновления суммы оплаты ".$add_sum."<br/>";
			$sql = "UPDATE  `"._DB_NAME_."`.`"._DB_PREFIX_."orders` SET  `total_paid_real` =  '".$add_sum."' WHERE  `"._DB_PREFIX_."orders`.`reference` ='". pSQL($payment['reference'])."'";
			if ($db->execute($sql))
			{
				//писать в лог
				echo "Обновлен заказ ".$id_order." номер ".$payment['reference'].". Оплачено действительно ".$add_sum.". Обновлено заказов: ".$db->Affected_Rows().".<br/>";
				return $id_order;
			}
			else
				$this->_html .= $this->displayError($this->l('Ошибка обновления заказа. Не найден заказ '.$payment['reference'].', счёт : '.$this->payments[$num_row]['reference'].'. '.$dbErrorMsg));
			return false;
		}
		else
			$this->_html .= $this->displayError($this->l('Ошибка обновления заказа. Не наден заказ по номеру: '.$this->payments[$num_row]['reference'].', счёт : '.$this->payments[$num_row]['reference'].'. '.$dbErrorMsg));
			return false;
	}

	private function addOrderInvoicepayment($payment,$id_order_payment, $id_order)
	{
		echo "добавляем 2";
		var_dump($payment['invoice_num']);
		if (!empty($payment['invoice_num']))
		{
			$invoice_num=(int)(str_replace($this->invoicePrefix, "", $payment['invoice_num']));
			$sql = "SELECT `id_order_invoice` FROM  `"._DB_PREFIX_."order_invoice` WHERE `number` ='".$invoice_num."'";
			echo "находим id инвойс ".$sql;
			$db = Db::getInstance();
			if ($row = $db->getRow($sql))
			{
				$id_order_invoice = $row['id_order_invoice'];
				echo "Найден id_order_invoice ".$id_order_invoice. "по номеру счёта ".$payment['invoice_num']."<br/>";
				$id_order_invoice = $row['id_order_invoice'];
				$sql = "INSERT INTO  `"._DB_NAME_."`.`"._DB_PREFIX_."order_invoice_payment` (
					`id_order_invoice` ,
					`id_order_payment` ,
					`id_order`
					)
					VALUES (
					'".$id_order_invoice."',  '".$id_order_payment."',  '".$id_order."'
					)";
				echo "Добавляем строку в ордер-инвойс=пэймент ".$sql;
				if ($db->execute($sql))
				{
					echo "в order_invoice_payment добавлена строка ".$db->Insert_ID();
					return $db->Insert_ID();
				}
				else 
				{
					echo "При добавлении записи об оплате счёта возникла ошибка ".$db->getMsgError();
					return false;
				}
			}
		}
		else 
		{
			echo "В платеже к заказу ".$payment." не указан номер счёта. Запись об оплате счёта не добавлена.<br/>";
			return false;
		}
	}

	private function showBankPaymentsRaw ()
	{
		if (!empty($this->paymentsRaw))
		{
			//Выводим таблицу с платежами
			$results = $this->paymentsRaw;
			$titles = array("Дата платежа","Номер заказа и счёта","Имя плательщика","Сумма платежа");
			array_unshift($results,$titles);
			$this->_html .= '<h3>Исходная выписка из банк-клиента</h3>';
			$this->_html .= 
			'<table style="border: 1px solid grey">';
			$chet = 0;
			foreach($results as $row) {
				$bg_color = ( $chet & 1 ) == 0? '#dff0d8' : 'rgb(202, 202, 255)'; //чередуем цвета строк 
				$this->_html .='<tr style="border: 1px solid grey; background-color: '.$bg_color.';"><td style="border-right: 1px solid green; padding: 0 5px 0 5px;">';
				$this->_html .= implode('</td><td style="border-left: 1px solid green; padding: 0 5px 0 5px; max-width:500px;">', $row);
				$this->_html .='</td>';
				$this->_html .='</tr>';
				$chet++;
			}
			$this->_html .= 
			"</table>";
			$this->_html .= "<br/><br/>";
		}
	}

	private function Getfloat($str) 
	{ 
		if(strstr($str, ",")) { 
		    $str = str_replace(".", "", $str); // replace dots (thousand seps) with blancs 
		    $str = str_replace(",", ".", $str); // replace ',' with '.' 
		} 
		  
		if(preg_match("#([0-9\.\-]+)#", $str, $match)) { // search for number that may contain '.' и '-'
		    return floatval($match[0]); 
		} else { 
		    return floatval($str); // take some last chances with floatval 
		} 
	} 
		
	private function _get_unpaid_orders ()
	{
		/* Находим заказы (номер заказа, номер счёта), у которых:
		*  способ оплаты "Банковский перевод"
		*  выставлен счёт (invoice)
		*  статусы:
		*  3 - В процессе подготовки
		*  4 - Отправлено
		*  5 - Доставлено
		*  9 - Данного товара нет на складе
		* 10 - В ожидании оплаты банком
		* 14 - Готов для самовывоза
		* 15 - Истекает время хранения заказа для самовывоза
		* И
		* (стомость оплаты меньше стомости заказа или нет ни одной оплаты)
		*/
		//o.id_order, o.reference, op.order_reference, o.total_paid, o.total_paid_real, os.id_order_state, op.*, oi.number as invoice_num
		$sql = "
		SELECT o.date_add as order_date_add, o.reference, oi.number as invoice_num, c.firstname, c.lastname, o.total_paid, o.total_paid_real, osl.name, op.amount, op.date_add FROM `"._DB_PREFIX_."orders` as o
		LEFT JOIN (SELECT oh.id_order, oh.id_order_state FROM "._DB_PREFIX_."order_history oh
		INNER JOIN (
		SELECT oh.id_order, max(date_add) maxdate FROM "._DB_PREFIX_."order_history as oh group by id_order) max_oh
		ON oh.id_order = max_oh.id_order AND oh.date_add = max_oh.maxdate ) as os
		ON o.id_order = os.id_order
		LEFT JOIN (
		SELECT * FROM "._DB_PREFIX_."order_state_lang WHERE id_lang=1
		) as osl
		ON os.id_order_state = osl.id_order_state
		LEFT JOIN "._DB_PREFIX_."customer as c
		ON o.id_customer = c.id_customer
		LEFT JOIN "._DB_PREFIX_."order_payment as op
		ON o.reference=op.order_reference
		LEFT JOIN "._DB_PREFIX_."order_invoice as oi
		ON o.id_order = oi.id_order
		where module = 'bankwire'
		AND oi.number is not null
		AND os.id_order_state IN (3,4,5,9,10,14,15)
		AND
		(o.total_paid > o.total_paid_real
		OR
		op.order_reference is null)
		ORDER BY o. `id_order` DESC";
		var_dump($sql);
		if ($results = Db::getInstance()->ExecuteS($sql)) 
		{
			$this->unpaidOrders=$results;
			//$this->_html .= $this->displayConfirmation($this->l('Найдено '.count($this->unpaidOrders).' неоплаченных заказов.'));	
		}
		else {
			$this->_html .= $this->displayConfirmation($this->l('Не найдено неоплаченных заказов.'));
		}
	}	

	private function showUnpaidOrders ()
	{
		if (!empty($this->unpaidOrders))
		{
			if (class_exists('Context')){
				$context = Context::getContext();
				$token=Tools::getAdminToken('AdminOrders'.(int)(Tab::getIdFromClassName('AdminOrders')).(int)($context->employee->id));
			}
			//Выводим таблицу с неоплаченными заказами
			$results = $this->unpaidOrders;
			$titles = array("Дата создания заказа","Номер заказа","Номер счёта","Имя", "Фамилия", "Сумма <br/>к оплате", "Оплачено <br/>по заказу", "Статус","Сумма <br/> платежа", "Дата платежа");
			array_unshift($results,$titles);
			$this->_html .= '<h3>Неоплаченные заказы</h3>';
			$this->_html .= 
			'<table style="border: 1px solid grey">';
			$chet = 0;
			foreach($results as $row_num => $row) {
				$bg_color = ( $chet & 1 ) == 0? '#dff0d8' : 'rgb(202, 202, 255)'; //чередуем цвета строк 
				$this->_html .='<tr style="border: 1px solid grey; background-color: '.$bg_color.';"><td style="border-right: 1px solid green; padding: 0 5px 0 5px;">';
				if ($row_num!=0)
				{
					$row['reference'] = '<a href="?controller=AdminOrders&id_order='.(int)(str_replace($this->referencePrefix, "", $row["reference"])).'&vieworder&token='.$token.'">'.$row["reference"].'</a>';
					$row['invoice_num'] = $this->invoicePrefix.sprintf("%06d", $row['invoice_num']);;
				}
				$this->_html .= implode('</td><td style="border-left: 1px solid green; padding: 0 5px 0 5px;">', $row);
				$this->_html .='</td>';
				$this->_html .='</tr>';
				$chet++;
			}
			$this->_html .= 
			"</table>";
			

			
			$this->_html .= "<br/><br/>";
		}
	}
}



