<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Block_Adminhtml_System_Config_Form_Array_Shippingpayment extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected function _prepareToRender()
    {
        $this->addColumn('order_amount', array(
            'label' => Mage::helper('extensa_econt')->__('Сума на поръчката над стойност'),
            'style' => 'width:150px',
        ));
        
        $this->addColumn('receiver_amount', array(
            'label' => Mage::helper('extensa_econt')->__('За сметка на получател до врата'),
            'style' => 'width:150px',
        ));

        $this->addColumn('receiver_amount_office', array(
            'label' => Mage::helper('extensa_econt')->__('За сметка на получател до офис'),
            'style' => 'width:150px',
        ));

        $this->_addAfter = false;
        //$this->_addButtonLabel = Mage::helper('extensa_econt')->__('Добавяне');
    }
}
