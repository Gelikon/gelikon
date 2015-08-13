<?php
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
	 
	//функция getContent() вызывается при нажатии ссылки "настройки"
	public function getContent()
	{
		//Обработка отправленной формы
		//$this->_postProcess();
		//Создаем список номеров печатных каталогов и выводим форму
		//$this->_printDateForm();
		//Возвращаем отображаемое содержимое
		get_bank_payments();
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

	private function get_bank_payments ()
	{
		//Формируем CSV файл и выводим в браузер
		$delimiter = ";";
		$enclosure = '"';
		$escape = "\\"
		$bank_file = dirname(__FILE__)."/bank_export.csv";

		$row = 1;
		if (($handle = fopen($bank_file, "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 1000, $delimiter,$enclosure,$escape)) !== FALSE) {
		        $num = count($data);
		        echo "<p> $num fields in line $row: <br /></p>\n";
		        $row++;
		        for ($c=0; $c < $num; $c++) {
		            echo $data[$c] . "<br />\n";
		        }
		    }
		    fclose($handle);
		}
		else 
		{
			$this->_html .= $this->displayError($this->l('Не удалось открыть банковскую выписку.'));
		}
	}	
		/*	
		}
		else 
		{	
			#read file  находим в файле все номера заказов/накладных и суммы платежа array['paym_id']['oreder_ref'] ['paym_id']['sum']  если вдруг по одному заказу несколько платежей 
			$bank = array();
			$bank = fgetcsv ( $f , 0, $delimiter, $enclosure, $escape);
			for ($i=0; $i<9; $i++){
				$bank = array_shift($bank);
			}
			if (!empty($bank))
			{
				$bank_payments = array();
				foreach ($bank as $id => $payment) {
					$bank_payments
				}
			}
			//$bank_payments++;
			fclose($f);
			$num_orders = 0;
			$bank_payments = 0;
			if (!empty($this->unpaidOrders))
			{
				foreach ($this->unpaidOrders as $unpaidOrder) {
					# code...
					foreach ($bank_payments as $bank_payment) {
						# code...
						if (unpaid.ref == bankpaymnet.ref)
						{
							добавляем паумент в ps_order_payment
							добавляем paid real в ps_order
							добавляем в ps_order_invoice_payment
							$num_orders++;
						}
					}
					
				}
			}
			$this->_html .= $this->displayConfirmation($this->l('В банковской выписке найдено '.$bank_payments .' платежек. Обновлено '.$num_orders.' заказов.'));
		}
		*/
	
	
	private function get_unpaid_orders ()
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
		$sql = "
		SELECT o.id_order, o.reference, op.order_reference, o.total_paid, o.total_paid_real, os.id_order_state, op.*, oi.number as invoice_num FROM `"._DB_PREFIX_."orders` as o
		LEFT JOIN (SELECT oh.id_order, oh.id_order_state FROM "._DB_PREFIX_."order_history oh
		INNER JOIN (
		SELECT oh.id_order, max(date_add) maxdate FROM "._DB_PREFIX_."order_history as oh group by id_order) max_oh
		ON oh.id_order = max_oh.id_order AND oh.date_add = max_oh.maxdate ) as os
		ON o.id_order = os.id_order
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
		ORDER BY `id_order` DESC
		";

		if ($results = Db::getInstance()->ExecuteS($sql)) {
			$this->unpaidOrders=$results;
			$this->_html .= $this->displayConfirmation($this->l('Найдено '.count($this->unpaidOrders).' неоплаченных заказов.'));
		}
		else {
			$this->_html .= $this->displayConfirmation($this->l('Не найдено неоплаченных заказов.'));
		}
	}	
}



