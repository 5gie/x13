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
                    'is_special',
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

}
