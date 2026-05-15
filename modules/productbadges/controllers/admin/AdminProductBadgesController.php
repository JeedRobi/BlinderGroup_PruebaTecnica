<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminProductBadgesController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table       = 'productbadges_badge';
        $this->className   = 'Badge';
        $this->lang        = true;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->bulk_actions = [
            'delete' => [
                'text'    => $this->l('Eliminar seleccionados'),
                'confirm' => $this->l('¿Eliminar los elementos seleccionados?'),
                'icon'    => 'icon-trash',
            ],
        ];

        parent::__construct();

        $this->bootstrap = true;
    }

    /**
     * Columnas del listado de badges (HelperList)
     */
    public function initContent(): void
    {
        $this->fields_list = [
            'id_badge' => [
                'title' => $this->l('ID'),
                'width' => 30,
            ],
            'label' => [
                'title' => $this->l('Texto'),
                'width' => 200,
                'lang'  => true,
            ],
            'position' => [
                'title' => $this->l('Posición'),
                'width' => 100,
            ],
            'bg_color' => [
                'title'    => $this->l('Color fondo'),
                'width'    => 80,
                'callback' => 'renderColor',
            ],
            'text_color' => [
                'title'    => $this->l('Color texto'),
                'width'    => 80,
                'callback' => 'renderColor',
            ],
            'active' => [
                'title'   => $this->l('Activo'),
                'active'  => 'status',
                'type'    => 'bool',
                'orderby' => false,
            ],
        ];

        parent::initContent();
    }

    /**
     * Callback para mostrar el color como una pastilla visual en el listado.
     */
    public function renderColor(string $value, array $row): string
    {
        $safeValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        return '<span style="display:inline-block;width:20px;height:20px;background:'
            . $safeValue
            . ';border:1px solid #ccc;border-radius:3px;" title="'
            . $safeValue
            . '"></span> '
            . $safeValue;
    }

    /**
     * Formulario de creación/edición de una badge (HelperForm)
     */
    public function renderForm(): string
    {
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Badge'),
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

        $this->tpl_form_vars['products_block'] = $this->renderProductsAssignBlock();

        return parent::renderForm();
    }

    /**
     * Genera el HTML del bloque de asignación de productos.
     */
    private function renderProductsAssignBlock(): string
    {
        $idBadge     = (int) Tools::getValue('id_badge');
        $assignedIds = $idBadge ? Badge::getProductIdsByBadge($idBadge) : [];

        $products = Product::getProducts(
            (int) $this->context->language->id,
            0,
            0,
            'name',
            'ASC',
            false,
            true
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
     * Sobreescribimos postProcess para guardar también
     * las asignaciones de productos al hacer submit.
     */
    public function postProcess(): void
    {
        parent::postProcess();

        if (Tools::isSubmit('submitAddproductbadges_badge') || Tools::isSubmit('submitAddproductbadges_badgeAndStay')) {
            $idBadge = (int) Tools::getValue('id_badge');

            if ($idBadge) {
                $rawIds     = Tools::getValue('product_ids', '');
                $productIds = $this->sanitizeProductIds($rawIds);
                Badge::saveProductAssignments($idBadge, $productIds);
            }
        }
    }

    /**
     * Valida que los ids de producto sean enteros positivos.
     */
    private function sanitizeProductIds(string $raw): array
    {
        if (empty($raw)) {
            return [];
        }

        $ids   = explode(',', $raw);
        $clean = [];

        foreach ($ids as $id) {
            $id = (int) trim($id);
            if ($id > 0) {
                $clean[] = $id;
            }
        }

        return array_unique($clean);
    }

    /**
     * Validación server-side antes de guardar.
     * Firma compatible con AdminControllerCore::validateRules()
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