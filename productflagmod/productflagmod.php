<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class productflagmod extends Module
{

    public function __construct()
    {
        $this->name = 'productflagmod';
        $this->author = 'kch.software';
        $this->version = '1.0.0';
        $this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('Product best online price', [], 'Modules.Productflagmod.Admin');
        $this->description = $this->trans('Lorem ipsum', [], 'Modules.Productflagmod.Admin');

        $this->ps_versions_compliancy = ['min' => '1.7.7.0', 'max' => _PS_VERSION_];

    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('actionProductFlagsModifier');
    }

    public function hookActionProductFlagsModifier(array $params)
    {
        if(!isset($params['product']['price_amount'])) return;

        if($params['product']['price_amount'] < 100){

            $params['flags']['best-online-price'] = [
                'type' => 'best-online-price',
                'label' => $this->trans('Best online price', [], 'Modules.Productflagmod.Admin')
            ];

        }

    }

}
