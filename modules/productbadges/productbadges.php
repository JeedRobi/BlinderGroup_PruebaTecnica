<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

// Cargamos el ObjectModel manualmente porque PrestaShop
// no autoloads clases de módulos fuera de su propio autoloader
require_once __DIR__ . '/classes/Badge.php';

class ProductBadges extends Module
{
    public function __construct()
    {
        $this->name          = 'productbadges';
        $this->tab           = 'front_office_features';
        $this->version       = '1.0.0';
        $this->author        = 'Jesus Bilbao';
        $this->need_instance = 0;
        $this->bootstrap     = true;

        parent::__construct();

        $this->displayName = $this->l('Product Badges');
        $this->description = $this->l('Gestión de etiquetas visuales reutilizables para productos del catálogo.');
        $this->confirmUninstall = $this->l('¿Seguro que quieres desinstalar el módulo? Se perderán todas las badges.');

        // Asegurar que hooks comunes están registrados (útil si el tema usa hooks diferentes)
        $additionalHooks = [
            'displayProductListFunctionalButtons',
            'displayProductListReviews',
            'displayProductList',
            'displayProductListItem',
            'displayProductImage',
        ];

        foreach ($additionalHooks as $hookName) {
            if (!Hook::getIdByName($hookName) || !$this->isRegisteredInHook($hookName)) {
                // registerHook silente: si ya está registrado no duplica
                @$this->registerHook($hookName);
            }
        }
    }

    /**
     * Instalación: tablas SQL + hooks + tab de menú
     */
    public function install(): bool
    {
        if (!parent::install()) {
            return false;
        }

        // Ejecutar SQL de instalación
        if (!$this->executeSqlScript('install')) {
            return false;
        }

        // Registrar hooks
        $hooks = [
            'displayProductListingProductItem', // listado de categoría y búsqueda
            'displayProductAdditionalInfo',     // ficha de producto
            'displayHeader',                    // para cargar CSS en front
            'displayBackOfficeHeader',          // para cargar CSS/JS en back
        ];

        foreach ($hooks as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }

        // Valores por defecto de configuración
        Configuration::updateValue('PRODUCTBADGES_ENABLED', 1);
        Configuration::updateValue('PRODUCTBADGES_SHOW_LISTING', 1);
        Configuration::updateValue('PRODUCTBADGES_SHOW_PRODUCT', 1);
        Configuration::updateValue('PRODUCTBADGES_MAX_BADGES', 3);

        // Registrar tab en el menú del back office
        return $this->installTab();
    }

    /**
     * Desinstalación: tablas + hooks + tab + configuración
     */
    public function uninstall(): bool
    {
        if (!parent::uninstall()) {
            return false;
        }

        if (!$this->executeSqlScript('uninstall')) {
            return false;
        }

        // Eliminar configuración
        Configuration::deleteByName('PRODUCTBADGES_ENABLED');
        Configuration::deleteByName('PRODUCTBADGES_SHOW_LISTING');
        Configuration::deleteByName('PRODUCTBADGES_SHOW_PRODUCT');
        Configuration::deleteByName('PRODUCTBADGES_MAX_BADGES');

        return $this->uninstallTab();
    }

    /**
     * Registra el tab en Módulos > Product Badges en el menú lateral del back office.
     */
    private function installTab(): bool
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminProductBadges';
        $tab->module = $this->name;
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminCatalog');
        $tab->icon = 'label';

        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[$lang['id_lang']] = 'Product Badges';
        }

        return $tab->add();
    }

    /**
     * Elimina el tab del menú al desinstalar.
     */
    private function uninstallTab(): bool
    {
        $idTab = (int) Tab::getIdFromClassName('AdminProductBadges');

        if ($idTab) {
            $tab = new Tab($idTab);
            return $tab->delete();
        }

        return true;
    }

    /**
     * Ejecuta un archivo SQL de sql/install.php o sql/uninstall.php
     */
    private function executeSqlScript(string $script): bool
    {
        $file = __DIR__ . '/sql/' . $script . '.php';

        if (!file_exists($file)) {
            return false;
        }

        return (bool) include $file;
    }

    /**
     * Página de configuración del módulo (botón "Configurar" en la lista de módulos)
     */
    public function getContent(): string
    {
        $output = '';

        if (Tools::isSubmit('submit_productbadges_config')) {
            $enabled    = (int) Tools::getValue('PRODUCTBADGES_ENABLED');
            $showListing = (int) Tools::getValue('PRODUCTBADGES_SHOW_LISTING');
            $showProduct = (int) Tools::getValue('PRODUCTBADGES_SHOW_PRODUCT');
            $maxBadges  = (int) Tools::getValue('PRODUCTBADGES_MAX_BADGES');

            // Validación server-side
            if ($maxBadges < 1 || $maxBadges > 20) {
                $output .= $this->displayError($this->l('El número máximo de badges debe estar entre 1 y 20.'));
            } else {
                Configuration::updateValue('PRODUCTBADGES_ENABLED', $enabled);
                Configuration::updateValue('PRODUCTBADGES_SHOW_LISTING', $showListing);
                Configuration::updateValue('PRODUCTBADGES_SHOW_PRODUCT', $showProduct);
                Configuration::updateValue('PRODUCTBADGES_MAX_BADGES', $maxBadges);

                $output .= $this->displayConfirmation($this->l('Configuración guardada correctamente.'));
            }
        }

        return $output . $this->renderConfigForm();
    }

    /**
     * Construye el formulario de configuración con HelperForm.
     */
    private function renderConfigForm(): string
    {
        $fields = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Configuración general'),
                    'icon'  => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type'    => 'switch',
                        'label'   => $this->l('Activar módulo'),
                        'name'    => 'PRODUCTBADGES_ENABLED',
                        'values'  => [
                            ['id' => 'enabled_on',  'value' => 1, 'label' => $this->l('Sí')],
                            ['id' => 'enabled_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type'    => 'switch',
                        'label'   => $this->l('Mostrar en listados'),
                        'name'    => 'PRODUCTBADGES_SHOW_LISTING',
                        'values'  => [
                            ['id' => 'listing_on',  'value' => 1, 'label' => $this->l('Sí')],
                            ['id' => 'listing_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type'    => 'switch',
                        'label'   => $this->l('Mostrar en ficha de producto'),
                        'name'    => 'PRODUCTBADGES_SHOW_PRODUCT',
                        'values'  => [
                            ['id' => 'product_on',  'value' => 1, 'label' => $this->l('Sí')],
                            ['id' => 'product_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type'    => 'text',
                        'label'   => $this->l('Número máximo de badges por producto'),
                        'name'    => 'PRODUCTBADGES_MAX_BADGES',
                        'class'   => 'fixed-width-xs',
                        'desc'    => $this->l('Entre 1 y 20.'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Guardar'),
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->module          = $this;
        $helper->name_controller = $this->name;
        $helper->token           = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex    = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language    = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->title    = $this->displayName;
        $helper->submit_action = 'submit_productbadges_config';

        $helper->fields_value['PRODUCTBADGES_ENABLED']      = Configuration::get('PRODUCTBADGES_ENABLED');
        $helper->fields_value['PRODUCTBADGES_SHOW_LISTING'] = Configuration::get('PRODUCTBADGES_SHOW_LISTING');
        $helper->fields_value['PRODUCTBADGES_SHOW_PRODUCT'] = Configuration::get('PRODUCTBADGES_SHOW_PRODUCT');
        $helper->fields_value['PRODUCTBADGES_MAX_BADGES']   = Configuration::get('PRODUCTBADGES_MAX_BADGES');

        return $helper->generateForm([$fields]);
    }

    // -------------------------------------------------------------------------
    // HOOKS DE FRONTEND
    // -------------------------------------------------------------------------

    /**
     * Carga el CSS de frontend solo en páginas que lo necesitan.
     */
    public function hookDisplayHeader(): void
    {
        if (!Configuration::get('PRODUCTBADGES_ENABLED')) {
            return;
        }

        $this->context->controller->registerStylesheet(
            'productbadges-front',
            'modules/' . $this->name . '/views/css/front.css',
            ['media' => 'all', 'priority' => 150]
        );

        // Registrar pequeño JS que reposiciona badges dentro de la imagen en ficha
        $this->context->controller->registerJavascript(
            'productbadges-front-js',
            'modules/' . $this->name . '/views/js/front.js',
            ['position' => 'bottom', 'priority' => 150]
        );
    }

    /**
     * Carga CSS/JS del back office solo en el controller de nuestro módulo.
     */
    public function hookDisplayBackOfficeHeader(): void
    {
        if (Tools::getValue('controller') !== 'AdminProductBadges') {
            return;
        }

        $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        $this->context->controller->addJS($this->_path . 'views/js/back.js');
    }

    /**
     * Hook para listado de categoría y resultados de búsqueda.
     */
public function hookDisplayProductListingProductItem(array $params): string
{
    // 1. Verificación básica
    if (!Configuration::get('PRODUCTBADGES_ENABLED') || !Configuration::get('PRODUCTBADGES_SHOW_LISTING')) {
        return '';
    }
    

    // 2. Detección robusta del ID del producto (varía según el tema/hook)
    $idProduct = 0;
    if (is_array($params) && isset($params['product'])) {
        if (isset($params['product']['id_product'])) {
            $idProduct = (int) $params['product']['id_product'];
        } elseif (isset($params['product']['id'])) {
            $idProduct = (int) $params['product']['id'];
        }
    } elseif (is_object($params) && isset($params->product)) {
        if (is_object($params->product) && property_exists($params->product, 'id')) {
            $idProduct = (int) $params->product->id;
        }
    }

    if (!$idProduct) {
        return ''; // Sin ID no podemos continuar
    }

    $idLang = (int) $this->context->language->id;
    $max = (int) Configuration::get('PRODUCTBADGES_MAX_BADGES');

    // 3. Obtener badges
    $badges = Badge::getBadgesForProduct($idProduct, $idLang, $max);

    if (empty($badges)) {
        return '';
    }

    $this->context->smarty->assign(['badges' => $badges]);

    // 4. Renderizar la plantilla del módulo
    return $this->display(__FILE__, 'views/templates/front/badge.tpl');
}
    /**
     * Compatibilidad con hooks de listados alternativos que usan algunos temas.
     * Todos reenvían al método principal para evitar duplicación.
     */
    public function hookDisplayProductList(array $params): string
    {
        return $this->hookDisplayProductListingProductItem($params);
    }

    public function hookDisplayProductListItem(array $params): string
    {
        return $this->hookDisplayProductListingProductItem($params);
    }

    public function hookDisplayProductListFunctionalButtons(array $params): string
    {
        return $this->hookDisplayProductListingProductItem($params);
    }

    public function hookDisplayProductImage(array $params): string
    {
        return $this->hookDisplayProductListingProductItem($params);
    }

    public function hookDisplayProductListReviews(array $params): string
    {
        return $this->hookDisplayProductListingProductItem($params);
    }
    public function hookDisplayProductAdditionalInfo(array $params): string
    {
        if (!Configuration::get('PRODUCTBADGES_ENABLED')) {
            return '';
        }

        if (!Configuration::get('PRODUCTBADGES_SHOW_PRODUCT')) {
            return '';
        }

        // Detección robusta del id del producto en la ficha
        $idProduct = 0;
        if (is_array($params) && isset($params['product'])) {
            if (isset($params['product']['id'])) {
                $idProduct = (int) $params['product']['id'];
            } elseif (isset($params['product']['id_product'])) {
                $idProduct = (int) $params['product']['id_product'];
            }
        } elseif (is_object($params) && isset($params->product)) {
            if (is_object($params->product) && property_exists($params->product, 'id')) {
                $idProduct = (int) $params->product->id;
            } elseif (is_object($params->product) && method_exists($params->product, 'getId')) {
                $idProduct = (int) $params->product->getId();
            }
        }

        if (!$idProduct) {
            return '';
        }

        $idLang    = (int) $this->context->language->id;
        $maxBadges = (int) Configuration::get('PRODUCTBADGES_MAX_BADGES');

        $badges = Badge::getBadgesForProduct($idProduct, $idLang, $maxBadges);

        if (empty($badges)) {
            return '';
        }

        $this->context->smarty->assign(['badges' => $badges]);

        return $this->display(__FILE__, 'views/templates/front/badge.tpl');
    }
}