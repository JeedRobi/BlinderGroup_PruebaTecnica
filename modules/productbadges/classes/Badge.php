<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Badge extends ObjectModel
{
    /** @var string Posición de la badge sobre la imagen */
    public $position;

    /** @var string Color de fondo en hex (#rrggbb) */
    public $bg_color;

    /** @var string Color del texto en hex (#rrggbb) */
    public $text_color;

    /** @var bool Estado activo/inactivo */
    public $active;

    /** @var string Texto de la badge (multilenguaje) */
    public $label;

    public static $definition = [
        'table'     => 'productbadges_badge',
        'primary'   => 'id_badge',
        'multilang' => true,
        'fields'    => [
            'position'   => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'size'     => 16,
            ],
            'bg_color'   => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isColor',
                'required' => true,
                'size'     => 7,
            ],
            'text_color' => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isColor',
                'required' => true,
                'size'     => 7,
            ],
            'active'     => [
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            // lang: true indica que este campo va a la tabla _lang
            'label'      => [
                'type'     => self::TYPE_STRING,
                'lang'     => true,
                'validate' => 'isCleanHtml',
                'required' => true,
                'size'     => 64,
            ],
        ],
    ];

    /**
     * Devuelve todas las badges activas con su label en el idioma dado.
     */
    public static function getActiveBadges(int $idLang): array
    {
        $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');

        $sql = new DbQuery();
        $sql->select('b.*, COALESCE(bl.label, bll.label) AS label');
        $sql->from('productbadges_badge', 'b');
        $sql->leftJoin(
            'productbadges_badge_lang',
            'bl',
            'b.id_badge = bl.id_badge AND bl.id_lang = ' . $idLang
        );
        $sql->leftJoin(
            'productbadges_badge_lang',
            'bll',
            'b.id_badge = bll.id_badge AND bll.id_lang = ' . $defaultLang
        );
        $sql->where('b.active = 1');

        return Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Devuelve las badges activas de un producto con su label traducida.
     * Es el método que usan los hooks de frontend.
     */
    public static function getBadgesForProduct(int $idProduct, int $idLang, int $maxBadges): array
    {
        $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');

        $sql = new DbQuery();
        $sql->select('b.*, COALESCE(bl.label, bll.label) AS label');
        $sql->from('productbadges_badge', 'b');
        $sql->innerJoin(
            'productbadges_badge_product',
            'bp',
            'b.id_badge = bp.id_badge AND bp.id_product = ' . (int) $idProduct
        );
        $sql->leftJoin(
            'productbadges_badge_lang',
            'bl',
            'b.id_badge = bl.id_badge AND bl.id_lang = ' . $idLang
        );
        $sql->leftJoin(
            'productbadges_badge_lang',
            'bll',
            'b.id_badge = bll.id_badge AND bll.id_lang = ' . $defaultLang
        );
        $sql->where('b.active = 1');
        $sql->limit((int) $maxBadges);

        return Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Devuelve los ids de productos asignados a una badge.
     */
    public static function getProductIdsByBadge(int $idBadge): array
    {
        $sql = new DbQuery();
        $sql->select('id_product');
        $sql->from('productbadges_badge_product');
        $sql->where('id_badge = ' . (int) $idBadge);

        $rows = Db::getInstance()->executeS($sql) ?: [];

        return array_column($rows, 'id_product');
    }

    /**
     * Sincroniza los productos asignados a una badge.
     * Borra las asignaciones anteriores y guarda las nuevas.
     */
    public static function saveProductAssignments(int $idBadge, array $productIds): bool
    {
        // Borrar asignaciones anteriores
        Db::getInstance()->delete(
            'productbadges_badge_product',
            'id_badge = ' . (int) $idBadge
        );

        if (empty($productIds)) {
            return true;
        }

        $rows = [];
        foreach ($productIds as $idProduct) {
            $rows[] = [
                'id_badge'   => (int) $idBadge,
                'id_product' => (int) $idProduct,
            ];
        }

        return Db::getInstance()->insert('productbadges_badge_product', $rows);
    }
}