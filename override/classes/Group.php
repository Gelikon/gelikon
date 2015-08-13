<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Group extends GroupCore
{
    public static function getReductionByIdGroup($id_group)
    {
        if(!is_array($id_group))
            $id_group = array($id_group);
        $return = array();
        foreach ($id_group as $id){
            if (!isset(self::$cache_reduction['group'][$id]))
            {
                    self::$cache_reduction['group'][$id] = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                    SELECT `reduction`
                    FROM `'._DB_PREFIX_.'group`
                    WHERE `id_group` = '.(int)$id);
            }
            $return[] = self::$cache_reduction['group'][$id];
        }
        return count($return) ? max($return) : 0;
    }
}


?>
