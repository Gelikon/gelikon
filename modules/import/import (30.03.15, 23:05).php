<?php
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
        if (Tools::isSubmit('submitImport')) {
            $this->import();
            $content .= '<h2>Импорт завершен</h2>';
        }

        $content .= '<form action="" method="post">';
        $content .= '<input type="submit" class="button" name="submitImport" id="import-button" value="Import">';
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
                    WHERE `cl`.`name` LIKE '%$name%' AND `c`.`id_parent` = 12
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
                    $category->id_parent = 12;
                    $category->save();
                    return $category->id;
                }
            case 'digitalUnit' :
                $id_category = DB::getInstance()->getValue("
                    SELECT `cl`.`id_category`
                    FROM `" . _DB_PREFIX_ . "category_lang` AS `cl`
                    LEFT JOIN `" . _DB_PREFIX_ . "category` AS `c` ON `c`.`id_category` = `cl`.`id_category`
                    WHERE `cl`.`name` LIKE '%$name%' AND `c`.`id_parent` = 52
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
                    $category->id_parent = 52;
                    $category->save();
                    return $category->id;
                }
            case 'subscriptionUnit' :
                $id_category = DB::getInstance()->getValue("
                    SELECT `cl`.`id_category`
                    FROM `" . _DB_PREFIX_ . "category_lang` AS `cl`
                    LEFT JOIN `" . _DB_PREFIX_ . "category` AS `c` ON `c`.`id_category` = `cl`.`id_category`
                    WHERE `cl`.`name` LIKE '%$name%' AND `c`.`id_parent` = 50
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
                    $category->id_parent = 50;
                    $category->save();
                    return $category->id;
                }
            case 'otherUnit' :
                $id_category = DB::getInstance()->getValue("
                    SELECT `cl`.`id_category`
                    FROM `" . _DB_PREFIX_ . "category_lang` AS `cl`
                    LEFT JOIN `" . _DB_PREFIX_ . "category` AS `c` ON `c`.`id_category` = `cl`.`id_category`
                    WHERE `cl`.`name` LIKE '%$name%' AND `c`.`id_parent` = 54
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
                    $category->id_parent = 54;
                    $category->save();
                    return $category->id;
                }
        }
    }

    private function prepareArray($line) {
        $result = array();

        if (!in_array($line[0], array('bookUnit', 'digitalUnit', 'subscriptionUnit', 'otherUnit'))) return null;

        $line[1] = trim($line[1]);
        if (is_numeric($line[1])) $result['reference'] = $line[1]; else return null;

        $line[2] = $this->prepareCategory(trim($line[2]), $line[0]);
        if (empty($line[2])) return null; else $result['category'] = $line[2];

        $line[3] = trim($line[3]);
        if (empty($line[3])) return null; else $result['name'] = $line[3];

        $result['description'] = trim($line[4]);
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
        if (is_numeric($line[7]) && strlen($line[7]) == 4) $result['year'] = $line[7];

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
            case 'Русский дом' : $result['warehouse'] = 1; break;
            case 'Berlin' : $result['warehouse'] = 2; break;
            default : return null;
        }

        if ($line[0] == 'bookUnit') {
            $result['id_tax'] = 4;
        } else {
            $result['id_tax'] = 1;
        }

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
        if (file_exists(_PS_ROOT_DIR_ . '/upload/import/import.csv')) {
            $file = fopen(_PS_ROOT_DIR_ . '/upload/import/import.csv', 'r');
            fgetcsv($file);

            while($line = fgetcsv($file)) {
                if (count($line) == 20 && $line = $this->prepareArray($line)) {
                    $this->upsert($line);
                }
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
        $product->quantity = $line['count'];
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
            Product::addFeatureProductImport($product->id, 11, 70);
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
        $available->id_shop = Context::getContext()->shop->id;
        $available->quantity = $line['count'];
        $available->save();

        $location = WarehouseProductLocation::getIdByProductAndWarehouse($product->id, 0, $line['warehouse']);
        $location = new WarehouseProductLocation($location);
        $location->id_product = $product->id;
        $location->id_product_attribute = 0;
        $location->id_warehouse = $line['warehouse'];
        $location->save();

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
    }
}