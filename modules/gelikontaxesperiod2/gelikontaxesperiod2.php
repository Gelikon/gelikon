<?php
//Предотвращаем загрузку модуля при прямом обращении к файлу
if (!defined('_CAN_LOAD_FILES_'))
	exit;
//Класс нашего модуля "gelikontaxesperiod" является потомком базового класса для модулей "Module"
class Gelikontaxesperiod2 extends Module
{
	private $tmp_url = '';
//Конструктор класса __construct () позволяет идентифицировать модуль
//и отобразить его в списке модулей
	public function __construct()
	{
//Свойства класса, заполняемые в методе __construct, соответсвуют тем,
//что были определены в файле config.xml
		$this->name = 'gelikontaxesperiod2';
		$this->tab = 'Catalog';
		$this->version = '0.1';
		$this->author = 'Gelikon';
 
		parent::__construct();
//После вызова конструктора родительского класса мы можем использовать
//предоставляемые им методы, а именно l(), который отвечает за локализацию строки
		$this->displayName = $this->l('Таблица с налогами для бухгалтера2');
		$this->description = $this->l('Позволяет выгрузить все онлайн заказы за период с налогами в формате CSV.');
		$this->confirmUninstall = $this->l('Действительно хотите удалить модуль?');
	}

	/* Админка модуля */
	//в эту переменную будем записывать выводимый в админке текст
	private $_html = '';
	private $downloadLink = '';
	 
	//функция getContent() вызывается при нажатии ссылки "настройки"
	public function getContent()
	{
		//Обработка отправленной формы
		$this->_postProcess();
		//Создаем список номеров печатных каталогов и выводим форму
		$this->_printDateForm();
		//Возвращаем отображаемое содержимое
		$this->_makeTar();
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

	private function _makeTar()
	{

	/* PHP FILE NAME: create-my-zip.php */

	$this->_html .= exec('tar zcf my-backup.tar.gz ./');
	$this->_html .= '...Done!...';


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
	
	private function exportOrder_taxes ($from_date=NULL, $till_date=NULL)
	{
		$sql = "
		SELECT date_add, reference, id_customer, firstname, lastname, 7_products_tax+7_shipping_tax as 7_tax, 19_products_tax+19_shipping_tax as 19_tax, total_paid_tax_incl FROM (
SELECT DATE(o.date_add) as date_add, o.id_order, o.reference, o.id_customer, c.firstname, c.lastname, od.id_order_detail, odt.id_tax, t.rate, odt.total_amount, if (t.rate=7,odt.total_amount,0) as 7_products_tax, if (t.rate=19,odt.total_amount,0) as 19_products_tax  ,o.carrier_tax_rate, o.total_shipping_tax_incl, o.total_shipping_tax_excl, if(o.carrier_tax_rate=7, (o.total_shipping_tax_incl-o.total_shipping_tax_excl), 0) as 7_shipping_tax, if(o.carrier_tax_rate=19, (o.total_shipping_tax_incl-o.total_shipping_tax_excl), 0) as 19_shipping_tax, o.total_paid_tax_incl FROM
`"._DB_PREFIX_."orders` as o 
LEFT JOIN `"._DB_PREFIX_."customer` as c ON o.id_customer = c.id_customer
LEFT JOIN
`"._DB_PREFIX_."order_detail` as od ON o.id_order = od.id_order LEFT JOIN `"._DB_PREFIX_."order_detail_tax` as odt ON od.id_order_detail = odt.id_order_detail
LEFT JOIN `"._DB_PREFIX_."tax` as t ON odt.id_tax = t.id_tax
WHERE o.id_shop=1 AND
o.date_add BETWEEN CAST('".$from_date."' AS DATE) AND CAST('".$till_date."' AS DATE)
GROUP BY odt.`id_order_detail`
ORDER BY od.`id_order_detail` ASC ) as tax_report";

		if ($results = Db::getInstance()->ExecuteS($sql))
		{
			//Формируем CSV файл и выводим в браузер
			$delimiter = ";";
			$tmp_file = dirname(__FILE__)."/orders_taxes.csv";
			$tmp_url = "http".(!empty($_SERVER['HTTPS'])?"s":"")."://".$_SERVER['SERVER_NAME']."/modules/".$this->name."/orders_taxes.csv";
			$f = fopen ($tmp_file,'w');
			if ($f===false)
			{
				$this->_html .= $this->displayError($this->l('Не удалось записать файл.'));
				//break;
			}
			else 
			{	
				$this->downloadLink = '<a href="'.$tmp_url.'">Скачать</a>';
				$num_orders = 0;
				//require_once __DIR__.'/html2text/lib/Html2Text/Html2Text.php';
				foreach ($results as $line)
		    	{	

		    		//$line['description'] = convert_html_to_text($line['description']);
		    		fputcsv($f, $line, $delimiter);
		    		$num_orders++;
		    	}
		   		fclose($f);
		   		//Выводим сообщение об успешном сохранении
				$this->_html .= $this->displayConfirmation($this->l('Отчет о налогах с '.$from_date.' по '.$till_date.' выгружен. '.$num_orders.' заказов.'));
			}
		}
		else 
		{
			$this->_html .= $this->displayError($this->l('Не найдено ни одного заказа с указанными параметрами.'));
				//break;
		}
	}	
}



