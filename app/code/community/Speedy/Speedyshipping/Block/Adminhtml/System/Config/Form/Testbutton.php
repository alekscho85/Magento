<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Testbutton
 *
 * @author killer
 */
class Speedy_Speedyshipping_Block_Adminhtml_System_Config_Form_Testbutton extends Mage_Adminhtml_Block_System_Config_Form_Field {

    public function _construct() {
        parent::_construct();


        parent::_construct();
        $this->setTemplate('speedy_speedyshipping/system/config/testbutton.phtml');
    }

    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for button
     *
     * @return string
     */
    public function getAjaxCheckUrl() {
       //return Mage::helper('adminhtml')->getUrl('adminhtml/adminhtml_atwixtweaks/check');
        //return $this->getUrl('speedyshipping/checkcredentials/checkCredentials');
        return Mage::helper("adminhtml")->getUrl("speedyshipping/checkcredentials/checkCredentials");
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml() {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
            'id' => 'speedy_testcredentials_button',
            'label' => $this->helper('adminhtml')->__('check_credentials_btn_label'),
            'onclick' => 'javascript:checkSpeedyCredentials(); return false;'
        ));

        return $button->toHtml();
    }

}
