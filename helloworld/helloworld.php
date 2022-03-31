<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . '/helloworld/classes/Hello.php';

class helloworld extends Module
{

    public function __construct()
    {
        $this->name = 'helloworld';
        $this->author = 'kch.software';
        $this->version = '1.0.0';
        $this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        Shop::addTableAssociation('hello', ['type' => 'shop']);

        $this->displayName = $this->trans('Hello world', [], 'Modules.HelloWorld.Admin');
        $this->description = $this->trans('Lorem ipsum', [], 'Modules.HelloWorld.Admin');

        $this->ps_versions_compliancy = ['min' => '1.7.7.0', 'max' => _PS_VERSION_];

    }

    public function install()
    {
        return $this->runInstallSteps()
            && $this->installFixtures();
    }

    public function runInstallSteps()
    {
        return parent::install()
            && $this->installDB()
            && $this->registerHook('moduleRoutes');
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->uninstallDB();
    }

    public function installDB()
    {
        $return = true;
        $return &= Db::getInstance()->execute('
                CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hello` (
                `id_hello` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`id_hello`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;'
        );

        $return &= Db::getInstance()->execute('
                CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hello_shop` (
                `id_hello` INT(10) UNSIGNED NOT NULL,
                `id_shop` INT(10) UNSIGNED NOT NULL,
                PRIMARY KEY (`id_hello`, `id_shop`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;'
        );

        $return &= Db::getInstance()->execute('
                CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hello_lang` (
                `id_hello` INT UNSIGNED NOT NULL,
                `id_shop` INT(10) UNSIGNED NOT NULL,
                `id_lang` INT(10) UNSIGNED NOT NULL ,
                `title` varchar(250) NOT NULL,
                `text` text NOT NULL,
                `link_rewrite` varchar(150) NOT NULL,
                PRIMARY KEY (`id_hello`, `id_lang`, `id_shop`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;'
        );

        return $return;
    }

    public function uninstallDB($drop_table = true)
    {
        $ret = true;
        if ($drop_table) {
            $ret &= Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'hello`')
                && Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'hello_shop`')
                && Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'hello_lang`');
        }

        return $ret;
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('saveHelloWorld')) {
            foreach (Language::getLanguages(false) as $lang) {
                if (!Tools::getValue('title_' . $lang['id_lang'])) {
                    $output = $this->displayError($this->trans('Please fill out all fields.', [], 'Admin.Notifications.Error'));
                    break;
                } else if (!empty(Tools::getValue('link_rewrite_' . $lang['id_lang'])) && !Validate::isLinkRewrite(Tools::getValue('link_rewrite_' . $lang['id_lang']))) {
                    $output = $this->displayError($this->trans('Wprowadzono błędny adres url', [], 'Admin.Notifications.Error'));
                    break;
                }
            }
            if(empty($output)){

                $update = $this->processSave();

                if (!$update) {
                    $output = '<div class="alert alert-danger conf error">'
                        . $this->trans('An error occurred on saving.', [], 'Admin.Notifications.Error')
                        . '</div>';
                }

            }

        }

        return $output . $this->renderForm();
    }

    public function processSave()
    {
        $shops = Tools::getValue('checkBoxShopAsso_configuration', [$this->context->shop->id]);
        $title = [];
        $text = [];
        $link_rewrite = [];

        foreach (Language::getLanguages(false) as $lang) {
            $title[$lang['id_lang']] = (string) Tools::getValue('title_' . $lang['id_lang']);
            $text[$lang['id_lang']] = (string) Tools::getValue('text_' . $lang['id_lang']);
            $link_rewrite[$lang['id_lang']] = (string) Tools::getValue('link_rewrite_' . $lang['id_lang']);
            if(empty($link_rewrite[$lang['id_lang']])){

                $link_rewrite[$lang['id_lang']] = Tools::link_rewrite(Tools::getValue('title_' . $lang['id_lang']));

            }
        }

        $saved = true;
        foreach ($shops as $shop) {
            Shop::setContext(Shop::CONTEXT_SHOP, $shop);
            $hello = new Hello(Tools::getValue('id_hello', 1));
            $hello->title = $title;
            $hello->text = $text;
            $hello->link_rewrite = $link_rewrite;
            $saved &= $hello->save();
        }

        return $saved;
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
                'id_hello' => [
                    'type' => 'hidden',
                    'name' => 'id_hello',
                ],
                'title' => [
                    'type' => 'text',
                    'label' => $this->trans('Tytuł', [], 'Modules.HelloWorld.Admin'),
                    'lang' => true,
                    'name' => 'title'

                ],
                'content' => [
                    'type' => 'textarea',
                    'label' => $this->trans('Tekst', [], 'Modules.HelloWorld.Admin'),
                    'lang' => true,
                    'name' => 'text',
                    'class' => 'rte',
                    'autoload_rte' => true,
                ],
                'link_rewrite' => [
                    'type' => 'text',
                    'label' => $this->trans('Adres url', [], 'Modules.HelloWorld.Admin'),
                    'lang' => true,
                    'name' => 'link_rewrite',
                    'desc' => $this->trans('Pozostaw puste pole w przypadku gdy ma zostać uzupełnione automatycznie', [], 'Modules.HelloWorld.Admin')
                ],
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

        if (Shop::isFeatureActive() && Tools::getValue('id_hello') == false) {
            $fields_form['input'][] = [
                'type' => 'shop',
                'label' => $this->trans('Shop association', [], 'Admin.Global'),
                'name' => 'checkBoxShopAsso_theme',
            ];
        }

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = 'helloworld';
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        foreach (Language::getLanguages(false) as $lang) {
            $helper->languages[] = [
                'id_lang' => $lang['id_lang'],
                'iso_code' => $lang['iso_code'],
                'name' => $lang['name'],
                'is_default' => ($default_lang == $lang['id_lang'] ? 1 : 0),
            ];
        }

        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        $helper->toolbar_scroll = true;
        $helper->title = $this->displayName;
        $helper->submit_action = 'saveHelloWorld';

        $helper->fields_value = $this->getFormValues();

        return $helper->generateForm([['form' => $fields_form]]);
    }

    public function getFormValues()
    {
        $fields_value = [];
        $idShop = $this->context->shop->id;
        $idHello = Hello::getHelloIdByShop($idShop);

        Shop::setContext(Shop::CONTEXT_SHOP, $idShop);
        $hello = new Hello((int) $idHello);

        $fields_value['title'] = $hello->title;
        $fields_value['text'] = $hello->text;
        $fields_value['link_rewrite'] = $hello->link_rewrite;
        $fields_value['id_hello'] = $idHello;

        return $fields_value;
    }

    public function installFixtures()
    {
        $return = true;

        $title = [];
        $text = [];
        $link_rewrite = [];
        
        foreach(Language::getLanguages(false) as $lang){

            if($lang['iso_code'] == 'pl'){

                $title[$lang['id_lang']] = 'Cześć X13';
                $text[$lang['id_lang']] = '';
                $link_rewrite[$lang['id_lang']] = 'czesc-x13';

            } else {

                $title[$lang['id_lang']] = 'Hello X13';
                $text[$lang['id_lang']] = '';
                $link_rewrite[$lang['id_lang']] = 'hello-x13';

            }

        }

        $shops = Shop::getShops(true, null, true);

        $hello = new Hello();
        $hello->title = $title;
        $hello->text = $text;
        $hello->link_rewrite = $link_rewrite;
        $return &= $hello->add();

        if ($return && count($shops) > 1) {
            foreach ($shops as $idShop) {
                Shop::setContext(Shop::CONTEXT_SHOP, $idShop);
                $return &= $hello->save();
            }
        }

        return $return;
    }

    public function hookModuleRoutes(array $params)
    {

        $link_rewrite = Hello::getHelloLinkRewrite($this->context->language->id, $this->context->shop->id);

        if(empty($link_rewrite)) return [];

        $page = array(
            'controller' =>  'Page',
            'rule' => $link_rewrite,
            'keywords' => array(),
            'params' => array(
                'link_rewrite' => $link_rewrite,
                'fc' => 'module',
                'module' => $this->name
            )
        );

        $return['module-' . $this->name . '-page'] = $page;

        return $return;


    }

}
