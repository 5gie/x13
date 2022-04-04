<?php

class helloworldPageModuleFrontController extends ModuleFrontController
{

    public function initContent()
    {

        parent::initContent();

        $this->context->smarty->assign([
            'hello' => Hello::getHelloByLang($this->context->language->id)
        ]);

        $this->setTemplate('module:helloworld/helloworld.tpl');
    }

}
