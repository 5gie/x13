<?php

class Category extends CategoryCore
{

    public $is_special;

    public function __construct($idCategory = null, $idLang = null, $idShop = null)
    {
        self::$definition['fields']['is_special'] = array('type' => self::TYPE_BOOL, 'validate' => 'isBool');

        parent::__construct($idCategory, $idLang, $idShop);
    }

}
