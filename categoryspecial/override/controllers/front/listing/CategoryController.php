<?php

class CategoryController extends CategoryControllerCore {

    public function init()
    {
        parent::init();

        if($this->category->is_special && !$this->checkSpecialCategoryAccess()){

            Tools::redirect('index.php?controller=404');

        }

    }

    public function checkSpecialCategoryAccess()
    {
        if (!$email = $this->context->customer->email) {

            return false;

        } else if(!strpos($email, 'x13.pl')){

            return false;

        } 

        return true;
        
    }

}