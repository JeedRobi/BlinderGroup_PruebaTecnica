<?php
/**
 * Limpieza completa al desinstalar el módulo.
 * El orden importa: primero las tablas dependientes, luego la principal.
 */

$sql = [];

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'productbadges_badge_product`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'productbadges_badge_lang`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'productbadges_badge`';

foreach ($sql as $query) {
    if (!Db::getInstance()->execute($query)) {
        return false;
    }
}

return true;