<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Model_System_Config_Source_Disposition
{
    /**
     * Returns array to be used in select on back-end
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('label' => Mage::helper('extensa_econt')->__('разпореждам пратката да се прегледа от получателя и да плати наложения платеж само ако приеме стоката ми'),
                'value' => 'pay_after_accept'),
            array('label' => Mage::helper('extensa_econt')->__('разпореждам пратката да се прегледа и тества от получателя и да плати наложения платеж само ако приеме стоката ми'),
                'value' => 'pay_after_test'),
        );
    }
}
