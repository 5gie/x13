<?php

class Hello extends ObjectModel
{
   
    public $id_hello;
    public $text;
    public $title;
    public $link_rewrite;

    public static $definition = [
        'table' => 'hello',
        'primary' => 'id_hello',
        'multilang' => true,
        'multilang_shop' => true,
        'fields' => [
            'id_hello' => ['type' => self::TYPE_NOTHING, 'validate' => 'isUnsignedId'],
            // Lang fields
            'title' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 250],
            'text' => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'],
            'link_rewrite' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isLinkRewrite',
                'required' => true,
                'size' => 128,
                'ws_modifier' => [
                    'http_method' => WebserviceRequest::HTTP_POST,
                    'modifier' => 'modifierWsLinkRewrite',
                ],
            ],
        ],
    ];


    public static function getHelloLinkRewrite($langId, $shopId)
    {
        $sql = 'SELECT hl.`link_rewrite` FROM `' . _DB_PREFIX_ . 'hello` h
		LEFT JOIN `' . _DB_PREFIX_ . 'hello_lang` hl ON hl.`id_hello` = h.`id_hello` AND hl.`id_lang` = '. (int) $langId . '
		WHERE hl.`id_shop` = ' . (int) $shopId;

        return Db::getInstance()->getValue($sql);
    }

    public static function getHelloByLinkRewrite($link_rewrite)
    {
        $sql = 'SELECT title, text FROM `' . _DB_PREFIX_ . 'hello_lang` WHERE link_rewrite = "'.$link_rewrite.'"';
        return Db::getInstance()->getRow($sql);
    }

    public static function getHelloByLang($id_lang)
    {
        $sql = 'SELECT title, text FROM `' . _DB_PREFIX_ . 'hello_lang` WHERE id_lang = "'. $id_lang.'"';
        return Db::getInstance()->getRow($sql);
    }

    public static function getHelloIdByShop($shopId)
    {
        $sql = 'SELECT i.`id_hello` FROM `' . _DB_PREFIX_ . 'hello` i
		LEFT JOIN `' . _DB_PREFIX_ . 'hello_lang` ish ON ish.`id_hello` = i.`id_hello`
		WHERE ish.`id_shop` = ' . (int) $shopId;

        return Db::getInstance()->getValue($sql);
    }

    public static function getHelloByShop($shopId)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'hello` i
		LEFT JOIN `' . _DB_PREFIX_ . 'hello_lang` ish ON ish.`id_hello` = i.`id_hello`
		WHERE ish.`id_shop` = ' . (int) $shopId;

        return Db::getInstance()->executeS($sql);
    }
}
