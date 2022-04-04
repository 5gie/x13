<?php

use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ToggleColumn;
use PrestaShopBundle\Form\Admin\Type\SwitchType;

if (!defined('_PS_VERSION_')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

class categoryspecial extends Module
{

    public function __construct()
    {
        $this->name = 'categoryspecial';
        $this->author = 'kch.software';
        $this->version = '1.0.0';
        $this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('Category special', [], 'Modules.Categoryspecial.Admin');
        $this->description = $this->trans('Lorem ipsum', [], 'Modules.Categoryspecial.Admin');

        $this->ps_versions_compliancy = ['min' => '1.7.7.0', 'max' => _PS_VERSION_];

    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('actionCategoryFormBuilderModifier') &&
            $this->registerHook('actionAfterCreateCategoryFormHandler') &&
            $this->registerHook('actionAfterUpdateCategoryFormHandler') &&
            $this->registerHook('actionAdminControllerSetMedia') &&
            $this->registerHook('actionAdminCategoriesListingFieldsModifier') && 
            $this->registerHook('actionCategoryGridDefinitionModifier') && 
            $this->registerHook('actionCategoryGridQueryBuilderModifier') && 
            $this->installSql();
    }

    public function uninstall()
    {
        return $this->uninstallSql() && parent::uninstall();
    }

    public function hookActionCategoryGridDefinitionModifier($params)
    {

        /** @var GridDefinitionInterface $definition */
        $definition = $params['definition'];

        $definition->getColumns()
            ->addAfter('position', (new ToggleColumn('is_special'))
                ->setName($this->trans('Special', [], 'Modules.Categoryspecial.Admin'))
                ->setOptions([
                    'field' => 'is_special',
                    'primary_field' => 'id_category',
                    'route' => 'admin_category_special',
                    'route_param_name' => 'categoryId',
                ])
            );
    }

    public function hookActionCategoryGridQueryBuilderModifier($params)
    {
        /** @var QueryBuilder[] $queryBuilders */
        $queryBuilders = [
            'search' => $params['search_query_builder'],
            'count' => $params['count_query_builder'],
        ];

        foreach ($queryBuilders as $queryBuilder) {
            $queryBuilder
                ->addSelect(
                    'is_special'
                );
        }
    }
    
    public function installSql()
    {
        $sql = 'ALTER TABLE `ps_category` ADD `is_special` TINYINT(1) NOT NULL;';
        return Db::getInstance()->execute($sql);
    }

    public function uninstallSql()
    {
        $sql = 'ALTER TABLE `ps_category` DROP `is_special`;';
        return Db::getInstance()->execute($sql);
    }

    public function hookActionCategoryFormBuilderModifier(array $params)
    {

        /** @var FormBuilderInterface $formBuilder */
        $formBuilder = $params['form_builder'];

        $category = new Category($params['id']);

        $formBuilder
            ->add(
                'is_special',
                SwitchType::class,
                [
                    'required' => false,
                    'label' => 'Kategoria specjalna?',
                ]
            );
           

        $params['data']['is_special'] = $category->is_special;

        $formBuilder->setData($params['data']);
    }

    public function hookActionAdminControllerSetMedia($params)
    {
        if ($this->context->controller->php_self == 'AdminCategories') {

            $this->context->controller->addJs('modules/' . $this->name . '/js/category.js');
            $this->context->controller->addJs('modules/' . $this->name . '/css/category.css');
        }
    }

    public function hookActionAfterUpdateCategoryFormHandler(array $params)
    {
        $this->updateCategoryData($params);
    }

    public function hookActionAfterCreateCategoryFormHandler(array $params)
    {
        $this->updateCategoryData($params);
    }

    private function updateCategoryData(array $params)
    {
        try {
            $category = new Category((int) $params['id']);
            $category->is_special = $params['form_data']['is_special'];
            $category->update();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('saveCategorySpecial')) {
            if (strlen(Tools::getValue('id_cms')) > 250 || !Validate::isInt(Tools::getValue('id_cms'))) {
                $output = $this->displayError($this->trans('Wprowadzono niepoprawną wartość', [], 'Admin.Notifications.Error'));
            } else {

                $update = $this->processSave();
    
                if (!$update) {
                    $output = '<div class="alert alert-danger conf error">'
                    . $this->trans('An error occurred on saving.', [], 'Admin.Notifications.Error')
                    . '</div>';
                } else{
                    $output = $this->displayConfirmation($this->trans('Zaktualizowano pomyślnie', [], 'Admin.Notifications.Success'));
                }
            }

        }

        return $output . $this->renderForm();
    }

    public function processSave()
    {
        return Configuration::updateValue('CATEGORYSPECIAL_ID_CMS', Tools::getValue('id_cms'));
    }

    protected function renderForm()
    {
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        $fields_form = [
            'tinymce' => true,
            'legend' => [
                'title' => $this->trans('Settings', [], 'Modules.HelloWorld.Admin'),
            ],
            'input' => [
            
                'id_cms' => [
                    'type' => 'select',
                    'label' => $this->trans('Strona cms', [], 'Modules.HelloWorld.Admin'),
                    'name' => 'id_cms',
                    'options' => [
                        // 'query' => array_map(function($cms){
                        //     return [
                        //         'id' => $cms['id_cms'],
                        //         'name' => $cms['meta_title']
                        //     ];
                        // }, CMS::listCms()),
                        'query' => CMS::listCms(null, false, false),
                        'id' => 'id_cms',
                        'name' => 'meta_title',
                    ],
                ]
            ],
            'submit' => [
                'title' => $this->trans('Save', [], 'Admin.Actions'),
            ],
            'buttons' => [
                [
                    'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                    'title' => $this->trans('Back to list', [], 'Admin.Actions'),
                    'icon' => 'process-icon-back',
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = 'categoryspecial';
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        // foreach (Language::getLanguages(false) as $lang) {
        //     $helper->languages[] = [
        //         'id_lang' => $lang['id_lang'],
        //         'iso_code' => $lang['iso_code'],
        //         'name' => $lang['name'],
        //         'is_default' => ($default_lang == $lang['id_lang'] ? 1 : 0),
        //     ];
        // }

        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        $helper->toolbar_scroll = true;
        $helper->title = $this->displayName;
        $helper->submit_action = 'saveCategorySpecial';

        $helper->fields_value['id_cms'] = Configuration::get('CATEGORYSPECIAL_ID_CMS');

        return $helper->generateForm([['form' => $fields_form]]);
    }

    // public function getFormValues()
    // {
    //     $fields_value = [];
    //     $idShop = $this->context->shop->id;
    //     $idHello = Hello::getHelloIdByShop($idShop);

    //     Shop::setContext(Shop::CONTEXT_SHOP, $idShop);
    //     $hello = new Hello((int) $idHello);

    //     $fields_value['title'] = $hello->title;
    //     $fields_value['text'] = $hello->text;
    //     $fields_value['link_rewrite'] = $hello->link_rewrite;
    //     $fields_value['id_hello'] = $idHello;

    //     return $fields_value;
    // }

}
