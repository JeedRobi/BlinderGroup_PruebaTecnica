<?php
/**
 * Tablas del módulo productbadges.
 * Se ejecuta desde productbadges::install().
 * Devuelve true si todo va bien, false en el primer fallo.
 */

$sql = [];

// Tabla principal de badges
// position: 'top-left' | 'top-right'
// bg_color / text_color: hex 7 chars (#rrggbb), validados server-side antes de guardar
// active: 0 | 1
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'productbadges_badge` (
    `id_badge`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `position`   ENUM("top-left","top-right") NOT NULL DEFAULT "top-left",
    `bg_color`   VARCHAR(7)  NOT NULL DEFAULT "#000000",
    `text_color` VARCHAR(7)  NOT NULL DEFAULT "#ffffff",
    `active`     TINYINT(1)  NOT NULL DEFAULT 1,
    PRIMARY KEY (`id_badge`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

// Tabla de traducciones del texto de la badge
// Relación 1:N con productbadges_badge (una fila por idioma activo)
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'productbadges_badge_lang` (
    `id_badge`   INT UNSIGNED NOT NULL,
    `id_lang`    INT UNSIGNED NOT NULL,
    `label`      VARCHAR(64)  NOT NULL DEFAULT "",
    PRIMARY KEY (`id_badge`, `id_lang`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

// Tabla pivote badge <-> producto (relación N:M)
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'productbadges_badge_product` (
    `id_badge`   INT UNSIGNED NOT NULL,
    `id_product` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id_badge`, `id_product`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

foreach ($sql as $query) {
    if (!Db::getInstance()->execute($query)) {
        return false;
    }
}

return true;