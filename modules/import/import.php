<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
define('BOOKS_CATEGORY', 168); //в файле csv bookUnit
define("digitalUnit", 223); //в файле csv digitalUnit
define('subscriptionUnit',222); 
define('otherUnit',172);
if (!defined('_PS_VERSION_'))
    exit;

class Import extends Module {
    public function __construct()
    {
        $this->name = 'import';
        $this->tab = 'migration_tools';
        $this->version = '1.0.0';
        $this->author = 'George Lemish';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Import');
        $this->description = $this->l('Import module');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function install() {
        if (!parent::install()) {
            return false;
        }

        return true;
    }

    public function uninstall() {
        if (!parent::uninstall()) {
            return false;
        }

        return false;
    }

    public function getContent() {
        $content = '<div class="bootstrap panel">';
        if (Tools::isSubmit('submitSetAllProdsASM')) {
            $this->set_all_products_asm();
            $content .= '<h2>Всем товарам установлен Advanced Stock Management</h2>';
        }
        if (Tools::isSubmit('submitImport')) {
            $this->import();
            $content .= '<h2>Импорт завершен</h2>';
        }
        if (Tools::isSubmit('submitStockFactory')) {
            $this->submitStockFactory();
            $content .= '<h2>Импортирован через StockManagerFactory</h2>';
        }
        if (Tools::isSubmit('get_current_stock')) {
            $this->get_current_stock();
            $content .= '<h2>Взяли количество на складе</h2>';
        }
        
        $content .= '<form action="" method="post">';
        $content .= '<input type="submit" class="button" name="submitImport" id="import-button" value="Import">';
        $content .= '<input type="submit" class="button" name="submitSetAllProdsASM" id="import-button" value="Set all product ASM">';
        $content .= '<input type="submit" class="button" name="submitStockFactory" id="import-button" value="Import via Stock Manager Factory">';
        $content .= '<input type="submit" class="button" name="get_current_stock" id="import-button" value="Получить остаток на складе">';
        $content .= '</form>';
        /*
        $content .= '<progress id="import-progress" value="0" max="100" style="margin-left: 20px; display: none;"></progress>';
        $content .= '
            <script>
                function ajaxImport(pos) {
                    $.ajax({
                        url: "/modules/import/ajax.php",
                        type: "post",
                        data: {
                            position: pos
                        },
                        dataType: "text",
                        success: function(data) {
                            if (data < 100) {
                                $("#import-progress").val(data).text(data + "%");
                            } else {
                                $("#import-progress").val(0).text("").hide();
                                $("#import-button").removeAttr("disabled");
                                alert("Import complete");
                            }
                        }
                    });
                }

                $(function() {
                    $("#import-button").click(function() {
                        $(this).attr("disabled", "disabled");
                        $("#import-progress").show();
                        ajaxImport(0);
                    });
                });
            </script>
        ';
        */
        $content .= '</div>';
        return $content;
    }

    private function prepareCategory($name, $type) {
        switch($type) {
            case 'bookUnit' :
                $id_category = DB::getInstance()->getValue("
                    SELECT `cl`.`id_category`
                    FROM `" . _DB_PREFIX_ . "category_lang` AS `cl`
                    LEFT JOIN `"._DB_PREFIX_."category` AS `c` ON `c`.`id_category`=`cl`.`id_category`
                    WHERE `cl`.`name` LIKE '%$name%' AND `c`.`id_parent` = ".BOOKS_CATEGORY."
                ");
                if ($id_category) {
                    return $id_category;
                } else {
                    $category = new Category();
                    $category->name = array(
                        '1' => $name,
                        '2' => $name,
                        '3' => $name
                    );
                    $category->link_rewrite = array(
                        '1' => Tools::link_rewrite($name),
                        '2' => Tools::link_rewrite($name),
                        '3' => Tools::link_rewrite($name)
                    );
                    $category->id_parent = BOOKS_CATEGORY;
                    $category->save();
                    return $category->id;
                }
            case 'digitalUnit' :
                $id_category = DB::getInstance()->getValue("
                    SELECT `cl`.`id_category`
                    FROM `" . _DB_PREFIX_ . "category_lang` AS `cl`
                    LEFT JOIN `" . _DB_PREFIX_ . "category` AS `c` ON `c`.`id_category` = `cl`.`id_category`
                    WHERE `cl`.`name` LIKE '%$name%' AND `c`.`id_parent` = ".digitalUnit."
                ");
                if ($id_category) {
                    return $id_category;
                } else {
                    $category = new Category();
                    $category->name = array(
                        '1' => $name,
                        '2' => $name,
                        '3' => $name
                    );
                    $category->link_rewrite = array(
                        '1' => Tools::link_rewrite($name),
                        '2' => Tools::link_rewrite($name),
                        '3' => Tools::link_rewrite($name)
                    );
                    $category->id_parent = digitalUnit;
                    $category->save();
                    return $category->id;
                }
            case 'subscriptionUnit' :
                $id_category = DB::getInstance()->getValue("
                    SELECT `cl`.`id_category`
                    FROM `" . _DB_PREFIX_ . "category_lang` AS `cl`
                    LEFT JOIN `" . _DB_PREFIX_ . "category` AS `c` ON `c`.`id_category` = `cl`.`id_category`
                    WHERE `cl`.`name` LIKE '%$name%' AND `c`.`id_parent` = ".subscriptionUnit."
                ");
                if ($id_category) {
                    return $id_category;
                } else {
                    $category = new Category();
                    $category->name = array(
                        '1' => $name,
                        '2' => $name,
                        '3' => $name
                    );
                    $category->link_rewrite = array(
                        '1' => Tools::link_rewrite($name),
                        '2' => Tools::link_rewrite($name),
                        '3' => Tools::link_rewrite($name)
                    );
                    $category->id_parent = subscriptionUnit;
                    $category->save();
                    return $category->id;
                }
            case 'otherUnit' :
                $id_category = DB::getInstance()->getValue("
                    SELECT `cl`.`id_category`
                    FROM `" . _DB_PREFIX_ . "category_lang` AS `cl`
                    LEFT JOIN `" . _DB_PREFIX_ . "category` AS `c` ON `c`.`id_category` = `cl`.`id_category`
                    WHERE `cl`.`name` LIKE '%$name%' AND `c`.`id_parent` = ".otherUnit."
                ");
                if ($id_category) {
                    return $id_category;
                } else {
                    $category = new Category();
                    $category->name = array(
                        '1' => $name,
                        '2' => $name,
                        '3' => $name
                    );
                    $category->link_rewrite = array(
                        '1' => Tools::link_rewrite($name),
                        '2' => Tools::link_rewrite($name),
                        '3' => Tools::link_rewrite($name)
                    );
                    $category->id_parent = otherUnit;
                    $category->save();
                    return $category->id;
                }
        }
    }

    private function prepareArray($line) {
        $result = array();

        if (!in_array($line[0], array('bookUnit', 'digitalUnit', 'subscriptionUnit', 'otherUnit'))) return null;

        $line[1] = trim($line[1]);
        if (is_numeric($line[1])) $result['reference'] = sprintf("%09s", $line[1]); else return null;

        $line[2] = $this->prepareCategory(trim($line[2]), $line[0]);
        if (empty($line[2])) return null; else $result['category'] = $line[2];

        $line[3] = trim($line[3]);
        if (empty($line[3])) return null; else {
            $result['name'] = str_replace('\"', '"', $line[3]);
            }
        $line[4] = trim($line[4]);
        $line[4] = str_replace('\"', '"', $line[4]);
        $result['description'] = $line[4];
        if (mb_strlen($result['description']) > 400) {
            $lpart = mb_substr($result['description'], 0, mb_strrpos(mb_substr($result['description'], 0, 400), ' ')) . '...';
            $result['description_short'] = $lpart;
        } else {
            $result['description_short'] = $result['description'];
        }

        $line[5] = str_replace(',', '.', trim($line[5]));
        if (is_numeric($line[5])) $result['price'] = round(($line[0] == 'bookUnit') ? ($line[5] / 1.07) : ($line[5] / 1.19), 9); else return null;

        $result['author'] = trim($line[6]);

        $line[7] = trim($line[7]);
        if (!empty($line[7])) $line[7] = substr($line[7], 0, 4);
        if (is_numeric($line[7]) && strlen($line[7]) == 4) $result['year'] = $line[7];  //в файле csv формат 2013-01-01 00:00:00

        $result['paperback'] = trim($line[8]);

        $line[9] = trim($line[9]);
        if (is_numeric($line[9])) $result['pages'] = $line[9];

        $line[10] = trim($line[10]);
        if (is_numeric($line[10])) $result['weight'] = $line[10];

        $result['isbn'] = trim($line[11]);

        $result['publishing'] = trim($line[12]);

        $result['size'] = trim($line[13]);

        $line[18] = trim($line[18]);
        if (is_numeric($line[18])) $result['count'] = $line[18]; else $result['count'] = 0;

        $line[19] = trim($line[19]);
        switch ($line[19]) {
            case 'Русский дом' : $result['warehouse'] = 2; break;
            case 'Berlin' : $result['warehouse'] = 1; break;
            default : $result['warehouse'] = 1; break;//return null;
        }

        if ($line[0] == 'bookUnit') {
            $result['id_tax'] = 4;
        } else {
            $result['id_tax'] = 1;
        }
        //var_dump($result);
        //echo "<br/><br/>";
        return $result;
    }

    public function ajaxImport($position) {
        if (file_exists(_PS_ROOT_DIR_ . '/upload/import/import.csv')) {
            $file = fopen(_PS_ROOT_DIR_ . '/upload/import/import.csv', 'r');
            $size = filesize(_PS_ROOT_DIR_ . '/upload/import/import.csv');
            $time = time();

            if ($position) {
                fseek($file, $position);
            } else {
                fgetcsv($file);
            }

            while(time() < $time + 25 && $line = fgetcsv($file)) {
                if (count($line) == 20 && $line = $this->prepareArray($line)) {
                    $this->upsert($line);
                }
                $position = ftell($file);
            }

            if ($position < $size) {
                $percent = round($position * 100 / $size);
            } else {
                $percent = 100;
            }

            echo $percent; die();
        }
    }

    public function import() {
        set_time_limit(10000000000);
        
        if (file_exists(_PS_ROOT_DIR_ . '/upload/import/csv/import.csv')) {
            $file = fopen(_PS_ROOT_DIR_ . '/upload/import/csv/import.csv', 'r');
            fgetcsv($file);
            //$counter = 0;
            $escape = "\\";
            $imported_lines = 0;
            $read_lines = 0;
       
            while( $line = fgetcsv($file)) {
                
                if (count($line) == 20 && $line = $this->prepareArray($line)) {
                    
                    $this->upsert($line);
                    $imported_lines ++;
                }
                
                $read_lines++;
            }
            $content .= "Импортировано строк: ".$imported_lines."</br>Прочитано строк: ".$read_lines;
        }
        else {
            $content .= "Файл не найден";
        }
        ddd($content);
    }

    public function submitStockFactory(){
        //$this->set_all_products_asm();
        //получить количества товаров на складах из временной таблицы
        if ($temp_stocks = DB::getInstance()->ExecuteS("
                   
                     SELECT p.`id_product`, `id_warehouse`, `f_quantity`, `u_quantity`, `d_quantity` 
                     FROM `" . _DB_PREFIX_ . "temp_stock` as ts right join " . _DB_PREFIX_ . "product as p on p.id_product = ts.`id_product`
                ")){
            foreach ($temp_stocks as $row) {
                $id_product = $row['id_product'];
                $id_product_attribute = 0;
                $id_stock_mvt_reason = 4;
                $price = 1;
                $id_currency = 1;
                $id_warehouse = $row['id_warehouse'];
                $warehouse = new Warehouse($id_warehouse);
                $stock_manager = StockManagerFactory::getManager();
                
                //сначала добавить юзабл, поптому что оно меньше, потом, если есть разница, добавить дельту как неюзабл, чтобы чисто в физические товары попало
                if ($row['d_quantity'] !=0) {
                    $is_usable = true;
                    $quantity = $row['u_quantity'];
                    // add stock
                    if ($stock_manager->addProduct($id_product, $id_product_attribute, $warehouse, $quantity, $id_stock_mvt_reason, $price, $is_usable))
                    {
                        StockAvailable::synchronize($id_product);
                    }
                    else
                        $errors[] = Tools::displayError('An error occurred. No stock was added.');

                    $is_usable = false;
                    $quantity = $row['d_quantity'];
                     // add stock
                    if ($stock_manager->addProduct($id_product, $id_product_attribute, $warehouse, $quantity, $id_stock_mvt_reason, $price, $is_usable))
                    {
                        StockAvailable::synchronize($id_product);
                    }
                    else
                        $errors[] = Tools::displayError('An error occurred. No stock was added.');
                }

                else {
                    //добавляем товар для продажи на склад
                    $is_usable = true;
                    $quantity = $row['f_quantity'];
                      // add stock
                    if ($stock_manager->addProduct($id_product, $id_product_attribute, $warehouse, $quantity, $id_stock_mvt_reason, $price, $is_usable))
                    {
                        StockAvailable::synchronize($id_product);
                    }
                    else
                        $errors[] = Tools::displayError('An error occurred. No stock was added.');
                }
            }
        }
        if (isset($errors))
        return $errors;
    }

    public function get_current_stock() {
        
        
        $PhysicalQuantities = New StockManager;
        // Получить список товаров в магазине
        $all_products = DB::getInstance()->executeS("SELECT `id_product` FROM `" . _DB_PREFIX_ . "product`");
        if (!is_null($all_products)){
            foreach ($all_products as $product) {
                $id_product = $product["id_product"];
                $id_product_attribute = 0;
                //  к-во товара на первом складе
                $id_warehouse = 1;
                $usable = false; //physical
                $f_quantity = $PhysicalQuantities->getProductPhysicalQuantities($id_product, $id_product_attribute, $id_warehouse, $usable);
                $usable = true; // usable
                $u_quantity = $PhysicalQuantities->getProductPhysicalQuantities($id_product, $id_product_attribute, $id_warehouse, $usable);
                $d_quantity = $f_quantity-$u_quantity;

                //сохранять в временную таблицу, если есть физ кол-во на этом складе
                if ($f_quantity>0){
                    Db::getInstance()->insert('temp_stock', array(
                        'id_product' => $id_product,
                        'id_warehouse'=> $id_warehouse,
                        'f_quantity' =>  $f_quantity,
                        'u_quantity' =>  $u_quantity,
                        'd_quantity' =>  $d_quantity,
                    ));
                }
                
                // к-во товароа на втором складе
                $id_warehouse = 2;
                $usable = false; //physical
                $f_quantity = $PhysicalQuantities->getProductPhysicalQuantities($id_product, $id_product_attribute, $id_warehouse, $usable);
                $usable = true; // usable
                $u_quantity = $PhysicalQuantities->getProductPhysicalQuantities($id_product, $id_product_attribute, $id_warehouse, $usable);
                $d_quantity = $f_quantity-$u_quantity;

                //сохранять в временную таблицу, если есть физ кол-во на этом складе
                if ($f_quantity>0){
                    Db::getInstance()->insert('temp_stock', array(
                        'id_product' => $id_product,
                        'id_warehouse'=> $id_warehouse,
                        'f_quantity' =>  $f_quantity,
                        'u_quantity' =>  $u_quantity,
                        'd_quantity' =>  $d_quantity,
                    ));
                }
            }
        }
    }

    public function set_all_products_asm(){
        $all_products = DB::getInstance()->executeS("SELECT `id_product` FROM `" . _DB_PREFIX_ . "product`");
        if (!is_null($all_products)){
            foreach ($all_products as $product) {
                $id_product = $product["id_product"];
                //$product = new Product($product["id_product"]);   
                //установка ASM=1 для текущего магазина (в PS устанавливается в таблице product и product_shop)
                //$product->advanced_stock_management = 1; //использовать Advanced Stock management 
                //$product->save();
                $depends_on_stock = true;
                $out_of_stock = 1; //2 - как в Preferences product. 1 - allow (ставь 1, т.к. 2 (как в Preferences) не дает заказать товар на сайте)  
                for ($id_shop = 1; $id_shop<=4; $id_shop++){
                    StockAvailable::setProductDependsOnStock($id_product, $depends_on_stock, $id_shop);   
                }
                /*  для магазина 2,3 запретить продажу, если нет в наличии. out_of_stock = 0
                    2   Second shop Gelikon 
                    3   First shop Gelikon
                */
                $out_of_stock = 0;
                StockAvailable::setProductOutOfStock($id_product, $out_of_stock, 2);
                StockAvailable::setProductOutOfStock($id_product, $out_of_stock, 3);
                /*
                    Для online и заказов по телефону разрешить заказ товара, которого нет в наличии
                    1 Gelikon DE online
                    4 Заказы по телефону
                */
                $out_of_stock = 1;
                StockAvailable::setProductOutOfStock($id_product, $out_of_stock, 1);
                StockAvailable::setProductOutOfStock($id_product, $out_of_stock, 4);
            }
        }
    }

    private function upsert($line) {
        $product = DB::getInstance()->getValue("SELECT `id_product` FROM `" . _DB_PREFIX_ . "product` WHERE `reference` LIKE '{$line['reference']}'");
        $product = new Product($product);


        $product->reference = $line['reference'];
        $product->name = array(
            '1' => $line['name'],
            '2' => $line['name'],
            '3' => $line['name']
        );
        $product->description = array(
            '1' => $line['description'],
            '2' => $line['description'],
            '3' => $line['description']
        );
        $product->description_short = array(
            '1' => $line['description_short'],
            '2' => $line['description_short'],
            '3' => $line['description_short']
        );
        $product->link_rewrite = array(
            '1' => Tools::link_rewrite($line['name']),
            '2' => Tools::link_rewrite($line['name']),
            '3' => Tools::link_rewrite($line['name'])
        );
        $product->available_now = array(
            '1' => "Есть в наличии",
            '2' => "Есть в наличии",
            '3' => "Есть в наличии"
        );
        $product->id_category_default = $line['category'];
        $product->quantity = (int)$line['count'];

        $product->advanced_stock_management = 1; //использовать Advanced Stock management
        $product->depends_on_stock = 1; //1 - доступное количество на основе ASM. 0 - указывается вручную
        $product->out_of_stock = 1; //2 - как в Preferences product. 1 - allow (Как в Preferences - не дает заказать товар на сайте)

        $product->price = $line['price'];
        $product->weight = $line['weight'] / 1000;
        $product->id_tax_rules_group = $line['id_tax'];

        $product->save();

        $product->updateCategories(array($line['category']));

        $product->deleteFeatures();
        if ($line['author']) {
            $id_feature_value = FeatureValue::addFeatureValueImport(9, $line['author'], null, Configuration::get('PS_LANG_DEFAULT'));
            Product::addFeatureProductImport($product->id, 9, $id_feature_value);
        }

        if ($line['year']) {
            $id_feature_value = FeatureValue::addFeatureValueImport(10, $line['year'], null, Configuration::get('PS_LANG_DEFAULT'));
            Product::addFeatureProductImport($product->id, 10, $id_feature_value);
        }

        if ($line['paperback']) {
            if ($line['paperback'] == 1) $id_feature_value =  1;
            else $id_feature_value = 2;
            //$id_feature_value = FeatureValue::addFeatureValueImport(11, $line['paperback'], null, Configuration::get('PS_LANG_DEFAULT'));
            Product::addFeatureProductImport($product->id, 11, $id_feature_value); //1 - id значения "твёрдый переплёт" у харакатеристики "Переплёт", 149226 - мягкая обложка
        }

        if ($line['pages']) {
            $id_feature_value = FeatureValue::addFeatureValueImport(12, $line['pages'], null, Configuration::get('PS_LANG_DEFAULT'), true);
            Product::addFeatureProductImport($product->id, 12, $id_feature_value);
        }

        /*
        if ($line['weight']) {
            $id_feature_value = FeatureValue::addFeatureValueImport(4, $line['weight'], null, Configuration::get('PS_LANG_DEFAULT'), true);
            Product::addFeatureProductImport($product->id, 4, $id_feature_value);
        }
        */

        if ($line['isbn']) {
            $id_feature_value = FeatureValue::addFeatureValueImport(13, $line['isbn'], null, Configuration::get('PS_LANG_DEFAULT'), true);
            Product::addFeatureProductImport($product->id, 13, $id_feature_value);
        }

        if ($line['publishing']) {
            $id_feature_value = FeatureValue::addFeatureValueImport(14, $line['publishing'], null, Configuration::get('PS_LANG_DEFAULT'), true);
            Product::addFeatureProductImport($product->id, 14, $id_feature_value);
        }

        $location = WarehouseProductLocation::getIdByProductAndWarehouse($product->id, 0, $line['warehouse']);
        $location = new WarehouseProductLocation($location);
        $location->id_product = $product->id;
        $location->id_product_attribute = 0;
        $location->id_warehouse = $line['warehouse'];
        $location->save();

        $stock = DB::getInstance()->getValue("SELECT `id_stock` FROM `" . _DB_PREFIX_ . "stock` WHERE `id_product` = {$product->id} AND `id_warehouse` = {$line['warehouse']}");
        $stock = new Stock($stock);
        $stock->id_product = $product->id;
        $stock->id_product_attribute = 0;
        $stock->id_warehouse = $line['warehouse'];
        $stock->physical_quantity = $line['count'];
        $stock->usable_quantity = $line['count'];
        $stock->price_te = 0;
        $stock->save();

        $available = DB::getInstance()->getValue("SELECT `id_stock_available` FROM `". _DB_PREFIX_ . "stock_available` WHERE `id_product` = {$product->id} AND `id_shop` = " . Context::getContext()->shop->id);
        $available = new StockAvailable($available);
        $available->id_product = $product->id;
        $available->id_product_attribute = 0;
        //$available->id_shop = Context::getContext()->shop->id;
        $available->quantity = StockManagerFactory::getManager()->getProductPhysicalQuantities($product->id, 0);
        $available->save();

        StockAvailable::setProductDependsOnStock($product->id, true, null);
        StockAvailable::setProductOutOfStock($product->id, 1, null); //allow

        while(strlen($line['reference']) < 9) {
            $line['reference'] = '0' . $line['reference'];
        }

        if (file_exists(_PS_ROOT_DIR_ . '/upload/import/' . $line['reference'] . '.jpg')) {
            $product->deleteImages();

            $image = new Image();
            $image->id_product = $product->id;
            $image->cover = 1;
            $image->position = 0;
            $image->save();

            $name = $image->getPathForCreation();

            copy(_PS_ROOT_DIR_ . '/upload/import/' . $line['reference'] . '.jpg', $name.'.'.$image->image_format);

            $types = ImageType::getImagesTypes('products');
            foreach ($types as $type) {
                ImageManager::resize(_PS_ROOT_DIR_ . '/upload/import/' . $line['reference'] . '.jpg', $name . '-' . $type['name'] . '.' . $image->image_format, $type['width'], $type['height'], $image->image_format);
            }
        }

        /*
        Db::getInstance()->update('stock_available', array(
           'depends_on_stock' => (int)1, //1 - доступное количество на основе ASM. 0 - указывается вручную
            'out_of_stock' => (int)1, //1-allow
        ), 'id_product='.$product->id.'');
         $affrows = Db::getInstance()->Affected_Rows();
         var_dump($affrows);
         */

        //var_dump($product->reference);
        //echo "<br/><br/><br/><br/>";
    }
}