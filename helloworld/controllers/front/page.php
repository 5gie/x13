<?php

class helloworldPageModuleFrontController extends ModuleFrontController
{

    protected $canonicalLangsUrl;
    
    public function init()
    {

        $canonicalURL = $this->getCanonicalURL();
        $this->canonicalRedirection($canonicalURL);

        parent::init();

    }

    public function initContent()
    {

        parent::initContent();

        $this->context->smarty->assign([
            'hello' => Hello::getHelloByLang($this->context->language->id)
        ]);

        $this->setTemplate('module:helloworld/helloworld.tpl');
    }

    protected function canonicalRedirection($canonicalURL = '')
    {
        if ($canonicalURL) {
            $match_url = rawurldecode(Tools::getCurrentUrlProtocolPrefix() . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            if (!preg_match('/^' . Tools::pRegexp(rawurldecode($canonicalURL), '/') . '([&?].*)?$/', $match_url)) {
                $url_params = '';
                if (preg_match('/(?<url_params>([&?].*)+)$/', $match_url, $matches)) {
                    $url_params = $matches['url_params'];
                }

                // Don't send any cookie
                Context::getContext()->cookie->disallowWriting();
                if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_ && __PS_BASE_URI__ != $_SERVER['REQUEST_URI']) {
                    exit('[Debug] This page has moved<br />Please use the following URL instead: <a href="' . $canonicalURL . $url_params . '">' . $canonicalURL . $url_params . '</a>');
                }

                $redirect_type = 2 == Configuration::get('PS_CANONICAL_REDIRECT') ? '301' : '302';
                header('HTTP/1.0 ' . $redirect_type . ' Moved');
                header('Cache-Control: no-cache');
                Tools::redirectLink($canonicalURL . $url_params);
            }
        } else {
            parent::canonicalRedirection($canonicalURL);
        }
    }

    public function getCanonicalURL()
    {
        $this->initCanonicalLangsUrl();

        return $this->canonicalLangsUrl[$this->context->language->language_code];
    }

    protected function initCanonicalLangsUrl()
    {
        $alternativeLangs = array();
        $languages = Language::getLanguages(true, $this->context->shop->id);


        foreach ($languages as $lang) {
            $link_rewrite = Hello::getHelloLinkRewrite($lang['id_lang'], $this->context->shop->id);
            if($link_rewrite){
                $alternativeLangs[$lang['language_code']] = $this->context->link->getPageLink('module-helloworld-page-'.$lang['id_lang'], true, $lang['id_lang']);
            }

        }

        $this->canonicalLangsUrl = $alternativeLangs;
    }


    protected function getCanonicalLangsUrl()
    {
        if (!$this->canonicalLangsUrl) {
            $this->initCanonicalLangsUrl();
        }

        return $this->canonicalLangsUrl;
    }

    protected function getAlternativeLangsUrl()
    {
        $languages = Language::getLanguages(true, $this->context->shop->id);

        if ($languages < 2) {
            // No need to display alternative lang if there is only one enabled
            return array();
        }

        return $this->getCanonicalLangsUrl();
    }

}
