<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Model_System_Config_Source_Instruction
{
    /**
     * Returns array to be used in select on back-end
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('label' => Mage::helper('extensa_econt')->__('за приемане'),
                'value' => 'take'),
            array('label' => Mage::helper('extensa_econt')->__('за предаване'),
                'value' => 'give'),
            array('label' => Mage::helper('extensa_econt')->__('за връщане'),
                'value' => 'return'),
            array('label' => Mage::helper('extensa_econt')->__('за услуги'),
                'value' => 'services'),
        );
    }
}
