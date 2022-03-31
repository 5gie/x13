<?php

class helloworldPageModuleFrontController extends ModuleFrontController
{

    private $link_rewrite;

    public function init()
    {
        parent::init();
        $this->link_rewrite = Tools::getValue('link_rewrite');

        if ($this->ajax) return;

        if (!$this->link_rewrite) {

            $this->errors[] = Tools::displayError($this->trans('Not Found'));
            Tools::redirect('index.php?controller=404');

        } 
    }

    public function initContent()
    {

        parent::initContent();

        $this->context->smarty->assign([
            'hello' => Hello::getHelloByLinkRewrite($this->link_rewrite)
        ]);

        $this->setTemplate('module:helloworld/helloworld.tpl');
    }

    public function getTemplateVarPage()
    {
        $vars = parent::getTemplateVarPage();
        $vars['meta']['title'] = 'Blog';
        return $vars;
    }

}
