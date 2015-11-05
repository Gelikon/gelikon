<?php
//Предотвращаем загрузку модуля при прямом обращении к файлу
if (!defined('_CAN_LOAD_FILES_'))
	exit;
//Класс нашего модуля "gelikonprintcatalog" является потомком базового класса для модулей "Module"
class Gelikonprintcatalog extends Module
{
	private $tmp_url = '';
//Конструктор класса __construct () позволяет идентифицировать модуль
//и отобразить его в списке модулей
	public function __construct()
	{
//Свойства класса, заполняемые в методе __construct, соответсвуют тем,
//что были определены в файле config.xml
		$this->name = 'gelikonprintcatalog';
		$this->tab = 'Catalog';
		$this->version = '0.1';
		$this->author = 'Gelikon';
 
		parent::__construct();
//После вызова конструктора родительского класса мы можем использовать
//предоставляемые им методы, а именно l(), который отвечает за локализацию строки
		$this->displayName = $this->l('Выгрузка печатного каталога');
		$this->description = $this->l('Позволяет выбрать номер каталога и выгрузить данные для печатного каталога в формате CSV.');
		$this->confirmUninstall = $this->l('Действительно хотите удалить модуль?');
	}

	/* Админка модуля */
	//в эту переменную будем записывать выводимый в админке текст
	private $_html = '';
	 
	//функция getContent() вызывается при нажатии ссылки "настройки"
	public function getContent()
	{
		//Обработка отправленной формы
		$this->_postProcess();
		//Создаем список номеров печатных каталогов и выводим форму
		$this->_printCatalogNums();
		//Возвращаем отображаемое содержимое
		return $this->_html;
	}

	//Получим список номеров печатных каталогов
	private function _printCatalogNums()
	{	
		$sql = 'SELECT fvl.value AS num_catalog
				FROM 
				'._DB_PREFIX_.'feature_value as fv
				INNER JOIN 
				'._DB_PREFIX_.'feature_value_lang AS fvl 
				ON fv.id_feature_value = fvl.id_feature_value
				WHERE fvl.id_lang =1 AND fv.id_feature = 8
				ORDER BY fvl.value DESC';
		//$sql = 'SELECT * FROM '._DB_PREFIX_.'shop';
		//$this->_html .= $sql;
		//$sql = 'SELECT * FROM '._DB_PREFIX_.'shop';
		if ($results = Db::getInstance()->ExecuteS($sql))
		{
			$this->_html .= '
			<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
				<fieldset>
					<legend>'.$this->l('Выгрузка печатного каталога').'</legend>
					<label for="num_catalog">'.$this->l('Номер каталога').'</label>
					<div class="margin-form">
						<select name="num_catalog" id="num_catalog">';
							/*
							$results = array (
								array('num_catalog' => 40),
								array('num_catalog' => 60),
								array('num_catalog' => 61),
								array('num_catalog' => 55),
								);
							*/
							foreach ($results as $row)
								$this->_html .= ' <option value="'.$row['num_catalog'].'">'.$row['num_catalog'].'</option>';
						$this->_html .= '
						</select>
						<p class="clear">'.$this->l('Выберите номер каталога для выгрузки').'</p>
						'.$this->tmp_url.'
					</div>
					<p class="center">
						<input class="button" type="submit" name="exportPrintCatalog" value="'.$this->l('Выгрузить').'"/>
					</p>
				</fieldset>
			</form>';
		}
		else 
		{
			$this->_html .= $this->displayError($this->l('Не найдены печатные каталоги.'));
			$this->_html .= Db::getInstance()->getMsgError();
		}
			
	}


	//Для обработки отправленной формы создадим функцию _postProcess():

	private function _postProcess()
	{
		//Проверяем отправлена ли форма
		if(Tools::isSubmit('submitTutorial'))
		{
			//Получаем значение поля формы tutorial_url
			$tutorial_url=Tools::getValue('tutorial_url');
			//Проверяем валидность ссылки
			if(Validate::isUrl($tutorial_url))
			{
				//Сохраняем настройку
				Configuration::updateValue('TUTORIAL_URL', $tutorial_url);
				//Выводим сообщение об успешном сохранении
				$this->_html .= $this->displayConfirmation($this->l('Настройки обновлены.'));
			} else
				//Выводим сообщение об ошибке
				$this->_html .= $this->displayError($this->l('Неверная ссылка.'));
		}
		//Проверяем отправлена ли форма с номером каталога 
		if(Tools::isSubmit('exportPrintCatalog'))
		{	
			//Получаем значение поля формы num_catalog
			$num_catalog=Tools::getValue('num_catalog');
			//Проверяем валидность номера
			if(Validate::isInt($num_catalog))
			{
				//Выполняем запрос
				//SELECT p.id_product, f9.author, pl.name, pl.description, CONCAT_WS(' ',f14.izdat, f10.god, f11.pereplet, f12.stranits, IFNULL(CONCAT('€ ',ROUND(p.price,2)), NULL)) as info, p.reference, /*lcp.id_category, lcp.level_depth,*/ cl.name, /*fp.id_feature, fl.name, fvl.value */
				$sql = "
					SELECT p.id_product, f9.author, pl.name as prod_name, pl.description, CONCAT_WS(' ',f14.izdat, f10.god, f11.pereplet, f12.stranits, IFNULL(CONCAT('€ ',ROUND(p.price,2)), NULL)) as info, p.reference, cl.name as cat_name
					
					FROM "._DB_PREFIX_."product as p
					INNER JOIN
					"._DB_PREFIX_."feature_product AS fp
					ON fp.id_product = p.id_product
					LEFT JOIN
					"._DB_PREFIX_."feature_lang AS fl 
					ON fl.id_feature = fp.id_feature
					INNER JOIN 
					"._DB_PREFIX_."feature_value_lang AS fvl 
					ON fp.id_feature_value = fvl.id_feature_value


					LEFT JOIN
					(SELECT fp.id_product, fvl.id_feature_value, IFNULL(CONCAT(fvl.value, ','), NULL) as izdat FROM
					"._DB_PREFIX_."feature_product AS fp
					LEFT JOIN "._DB_PREFIX_."feature_value_lang AS fvl ON fp.id_feature_value = fvl.id_feature_value
					WHERE fvl.id_lang =1 AND fp.id_feature = 14) as f14
					ON p.id_product = f14.id_product

					LEFT JOIN
					(SELECT id_product, fvl.id_feature_value, IFNULL(CONCAT(fvl.value, '.'), NULL) as god FROM
					"._DB_PREFIX_."feature_product AS fp
					LEFT JOIN "._DB_PREFIX_."feature_value_lang AS fvl ON fp.id_feature_value = fvl.id_feature_value
					WHERE fvl.id_lang =1 AND fp.id_feature = 10) as f10
					ON p.id_product = f10.id_product

					LEFT JOIN
					(SELECT id_product, fvl.id_feature_value, IFNULL(CONCAT(SUBSTRING(fvl.value,1, 3), '.'), NULL)  as pereplet FROM
					"._DB_PREFIX_."feature_product AS fp
					LEFT JOIN "._DB_PREFIX_."feature_value_lang AS fvl ON fp.id_feature_value = fvl.id_feature_value
					WHERE fvl.id_lang =1 AND fp.id_feature = 11) as f11
					ON p.id_product = f11.id_product

					LEFT JOIN
					(SELECT id_product, fvl.id_feature_value, IFNULL(CONCAT(fvl.value, ' с.'), NULL) as stranits FROM
					"._DB_PREFIX_."feature_product AS fp
					LEFT JOIN "._DB_PREFIX_."feature_value_lang AS fvl ON fp.id_feature_value = fvl.id_feature_value
					WHERE fvl.id_lang =1 AND fp.id_feature = 12) as f12
					ON p.id_product = f12.id_product

					LEFT JOIN
					(SELECT id_product, fvl.id_feature_value, fvl.value as author FROM
					"._DB_PREFIX_."feature_product AS fp
					LEFT JOIN "._DB_PREFIX_."feature_value_lang AS fvl ON fp.id_feature_value = fvl.id_feature_value
					WHERE fvl.id_lang =1 AND fp.id_feature = 9) as f9
					ON p.id_product = f9.id_product

					LEFT JOIN
					"._DB_PREFIX_."product_lang as pl
					ON p.id_product = pl.id_product 

					LEFT JOIN
					"._DB_PREFIX_."category_product as cp
					ON p.id_product = cp.id_product

					LEFT JOIN
					(SELECT *
					FROM (
					SELECT c.level_depth, c.id_category, cp.id_product
					    FROM "._DB_PREFIX_."category as c
					    LEFT JOIN "._DB_PREFIX_."category_product as cp 
					    ON cp.id_category=c.id_category
					ORDER BY level_depth desc
					) as lcp
					GROUP BY id_product
					)as lcp
					ON cp.id_product = lcp.id_product

					LEFT JOIN
					"._DB_PREFIX_."category_lang as cl
					ON lcp.id_category = cl.id_category

					WHERE pl.id_lang = 1 AND pl.id_shop = 1 AND fvl.id_lang =1 AND fp.id_feature = 8 AND fvl.value='".$num_catalog."'
					GROUP BY p.id_product
				";
				if ($results = Db::getInstance()->ExecuteS($sql))
				{
					//Формируем CSV файл и выводим в браузер
					$delimiter = ";";
					$tmp_file = dirname(__FILE__)."/Print_catalog_export.csv";
					$tmp_url = "http".(!empty($_SERVER['HTTPS'])?"s":"")."://".$_SERVER['SERVER_NAME']."/modules/".$this->name."/Print_catalog_export.csv";
					$f = fopen ($tmp_file,'w');
					if ($f===false)
					{
						$this->_html .= $this->displayError($this->l('Не удалось записать файл.'));
						break;
					}
					else 
					{	
						$this->tmp_url = '<a href="'.$tmp_url.'">Скачать</a>';
						$num_products = 0;
						require_once __DIR__.'/html2text/lib/Html2Text/Html2Text.php';
						foreach ($results as $line)
				    	{	

				    		//$line['description'] = convert_html_to_text($line['description']);
				    		$html2text = new \Html2Text\Html2Text($line['description'], false, array('do_links' => 'none'));
        					$line['description'] = $html2text->get_text();
				    		fputcsv($f, $line, $delimiter);
				    		$num_products++;
				    	}
				   		fclose($f);
					} 
				    //Выводим сообщение об успешном сохранении
					$this->_html .= $this->displayConfirmation($this->l('Печатный каталог '.$num_catalog.' выгружен. '.$num_products.' товаров.'));

				}
				else
					//Выводим сообщение об ошибке
					$this->_html .= $this->displayError($this->l('Не найдены товары для каталога № '.$num_catalog.'.'));
			} 
			else
				//Выводим сообщение об ошибке
				$this->_html .= $this->displayError($this->l('Неверный номер каталога.'));
		}
	}	
}



