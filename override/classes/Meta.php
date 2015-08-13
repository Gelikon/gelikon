<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Meta extends MetaCore
{
    public static function getProductMetas($id_product, $id_lang, $page_name)
    {

            $context = Context::getContext();
            $sql = 'SELECT `name`, `meta_title`, `meta_description`, `meta_keywords`, `description_short`
                            FROM `'._DB_PREFIX_.'product` p
                            LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.`id_product` = p.`id_product`'.Shop::addSqlRestrictionOnLang('pl').')
                            '.Shop::addSqlAssociation('product', 'p').'
                            WHERE pl.id_lang = '.(int)$id_lang.'
                                    AND pl.id_product = '.(int)$id_product.'
                                    AND product_shop.active = 1';
            if ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql))
            {
                    if($context->language->id != 1){
                        $row['meta_title'] = Tools::rus2translit($row['meta_title']);
                        $row['name'] = Tools::rus2translit($row['name']);
                    }

                    if (empty($row['meta_description']))
                            $row['meta_description'] = strip_tags($row['description_short']);
                    return Meta::completeMetaTags($row, $row['name']);
            }

            return Meta::getHomeMetas($id_lang, $page_name);
    }
    
}

?>
