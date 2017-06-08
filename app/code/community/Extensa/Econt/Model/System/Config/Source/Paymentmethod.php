<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Model_System_Config_Source_Paymentmethod
{
    /**
     * Returns array to be used in select on back-end
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('label' => Mage::helper('extensa_econt')->__('в брой'),
                'value' => 'CASH'),
            array('label' => Mage::helper('extensa_econt')->__('на кредит'),
                'value' => 'CREDIT'),
            array('label' => Mage::helper('extensa_econt')->__('с бонус точки'),
                'value' => 'BONUS'),
            array('label' => Mage::helper('extensa_econt')->__('с ваучери'),
                'value' => 'VOUCHER'),
        );
    }
}
