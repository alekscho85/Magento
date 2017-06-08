<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Model_System_Config_Source_Shippingfrom
{
    /**
     * Returns array to be used in select on back-end
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('label' => Mage::helper('extensa_econt')->__('от офис'),
                'value' => 'OFFICE'),
            array('label' => Mage::helper('extensa_econt')->__('от врата'),
                'value' => 'DOOR'),
            array('label' => Mage::helper('extensa_econt')->__('от АПС'),
                'value' => 'APS'),
        );
    }
}
