<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Model_System_Config_Source_Cdagreementnum
{
    /**
     * Returns array to be used in select on back-end
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('label' => Mage::helper('extensa_econt')->__('--Кликнете Вземете уникален номер--'),
                'value' => ''),
        );
    }
}
