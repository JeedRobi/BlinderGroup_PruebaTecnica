<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminProductBadgesController extends ModuleAdminController
{
    public function __construct()
    {
        // 1. Llamar al padre primero para evitar el error de Symfony "trans() on null"
        parent::__construct();

        // 2. Definición básica del controlador
        $this->table       = 'productbadges_badge';
        $this->className   = 'Badge';
        $this->lang        = true;
        $this->bootstrap   = true;

        // 3. SOLUCIÓN AL ERROR SQL: Definimos el ID y forzamos el orden
        // Esto evita que PrestaShop busque 'id_configuration'
        $this->identifier       = 'id_badge';
        $this->_defaultOrderBy  = 'id_badge';
        $this->_defaultOrderWay = 'ASC';

        // Acciones de fila
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        // Forzar el join de la tabla lang para que el listado use el idioma activo
        $this->_select = 'al.label';
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'productbadges_badge_lang` al'
            . ' ON (a.id_badge = al.id_badge AND al.id_lang = ' . (int) $this->context->language->id . ')';

        // Acciones masivas
        $this->bulk_actions = [
            'delete' => [
                'text'    => $this->l('Eliminar seleccionados'),
                'confirm' => $this->l('¿Eliminar los elementos seleccionados?'),
                'icon'    => 'icon-trash',
            ],
        ];

        // Definición de las columnas del listado (HelperList)
        $this->fields_list = [
            'id_badge' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'label' => [
                'title' => $this->l('Texto'),
                'width' => 'auto',
                'lang'  => true,
            ],
            'position' => [
                'title' => $this->l('Posición'),
                'type'  => 'select',
                'list'  => [
                    'top-left'  => $this->l('Superior izquierda'),
                    'top-right' => $this->l('Superior derecha'),
                ],
                'filter_key' => 'a!position',
            ],
            'bg_color' => [
                'title'    => $this->l('Color fondo'),
                'callback' => 'renderColor',
                'search'   => false,
            ],
            'text_color' => [
                'title'    => $this->l('Color texto'),
                'callback' => 'renderColor',
                'search'   => false,
            ],
            'active' => [
                'title'   => $this->l('Activo'),
                'active'  => 'status',
                'type'    => 'bool',
                'align'   => 'center',
                'class'   => 'fixed-width-sm',
                'orderby' => false,
            ],
        ];
    }

    /**
     * Callback para mostrar el color visualmente en el listado.
     */
    public function renderColor($value, $row)
    {
        $safeValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        return '<span style="display:inline-block;width:18px;height:18px;background:'
            . $safeValue
            . ';border:1px solid #ccc;border-radius:3px;vertical-align:middle;margin-right:5px;"></span> '
            . $safeValue;
    }

    /**
     * Formulario de creación/edición de una badge (HelperForm)
     */
    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Configuración de la Badge'),
                'icon'  => 'icon-tag',
            ],
            'input' => [
                [
                    'type'     => 'text',
                    'label'    => $this->l('Texto de la badge'),
                    'name'     => 'label',
                    'lang'     => true,
                    'required' => true,
                    'hint'     => $this->l('Máximo 64 caracteres.'),
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Posición'),
                    'name'    => 'position',
                    'options' => [
                        'query' => [
                            ['id' => 'top-left',  'name' => $this->l('Superior izquierda')],
                            ['id' => 'top-right', 'name' => $this->l('Superior derecha')],
                        ],
                        'id'   => 'id',
                        'name' => 'name',
                    ],
                ],
                [
                    'type'  => 'color',
                    'label' => $this->l('Color de fondo'),
                    'name'  => 'bg_color',
                ],
                [
                    'type'  => 'color',
                    'label' => $this->l('Color del texto'),
                    'name'  => 'text_color',
                ],
                [
                    'type'   => 'switch',
                    'label'  => $this->l('Activo'),
                    'name'   => 'active',
                    'is_bool' => true,
                    'values' => [
                        ['id' => 'active_on',  'value' => 1, 'label' => $this->l('Sí')],
                        ['id' => 'active_off', 'value' => 0, 'label' => $this->l('No')],
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->l('Guardar'),
            ],
        ];

        // Bloque personalizado para asignar productos
        $this->tpl_form_vars['products_block'] = $this->renderProductsAssignBlock();

        return parent::renderForm();
    }

    /**
     * Genera el HTML del bloque de asignación de productos.
     */
    private function renderProductsAssignBlock()
    {
        $idBadge     = (int) Tools::getValue('id_badge');
        $assignedIds = $idBadge ? Badge::getProductIdsByBadge($idBadge) : [];

        $products = Product::getProducts(
            (int) $this->context->language->id,
            0,
            100, // Límite de seguridad
            'name',
            'ASC'
        );

        $this->context->smarty->assign([
            'products'    => $products,
            'assignedIds' => $assignedIds,
        ]);

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'productbadges/views/templates/admin/assign.tpl'
        );
    }

    /**
     * Gestión del guardado de relaciones N:M
     */
    public function postProcess()
    {
        parent::postProcess();

        if (Tools::isSubmit('submitAddproductbadges_badge') || Tools::isSubmit('submitAddproductbadges_badgeAndStay')) {
            $idBadge = (int) Tools::getValue('id_badge');
            
            // Si es nuevo, recuperamos el ID del objeto recién creado
            if (!$idBadge && isset($this->object->id)) {
                $idBadge = (int) $this->object->id;
            }

            if ($idBadge) {
                $productIds = Tools::getValue('product_ids');
                $cleanIds = is_array($productIds) ? array_map('intval', $productIds) : [];
                Badge::saveProductAssignments($idBadge, $cleanIds);
            }
        }
    }

    /**
     * Validación server-side
     */
    public function validateRules($class_name = false)
    {
        $bgColor   = Tools::getValue('bg_color');
        $textColor = Tools::getValue('text_color');
        $position  = Tools::getValue('position');

        $hexPattern = '/^#[0-9A-Fa-f]{6}$/';

        if ($bgColor && !preg_match($hexPattern, $bgColor)) {
            $this->errors[] = $this->l('El color de fondo no tiene un formato válido (#rrggbb).');
        }

        if ($textColor && !preg_match($hexPattern, $textColor)) {
            $this->errors[] = $this->l('El color del texto no tiene un formato válido (#rrggbb).');
        }

        if ($position && !in_array($position, ['top-left', 'top-right'], true)) {
            $this->errors[] = $this->l('La posición no es válida.');
        }

        return parent::validateRules($class_name);
    }
}