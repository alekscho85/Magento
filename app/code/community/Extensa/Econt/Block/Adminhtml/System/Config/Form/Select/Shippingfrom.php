<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Block_Adminhtml_System_Config_Form_Select_Shippingfrom extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Define params and variables
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        $this->setTemplate('extensa/econt/system/config/shippingfrom.phtml');
    }

    /**
     * Retrieve Element HTML fragment
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return parent::_getElementHtml($element) . $this->renderView();
    }
}
