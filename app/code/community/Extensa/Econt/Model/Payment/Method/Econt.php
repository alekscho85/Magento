<?php
/**
 * Payment method module adapter
 *
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Model_Payment_Method_Econt extends Mage_Payment_Model_Method_Abstract
{
    /**
     * unique internal payment method identifier
     *
     * @var string [a-z0-9_]
     */
    protected $_code = 'extensa_econt';

    /**
     * Retrieve payment method title
     *
     * @return string
     */
    public function getTitle()
    {
        //return $this->getConfigData('title');
        return Mage::helper('extensa_econt')->__('Еконт Експрес наложен платеж');
    }
}
