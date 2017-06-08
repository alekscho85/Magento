<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Model_System_Config_Source_City
{
    /**
     * Returns array to be used in select on back-end
     *
     * @return array
     */
    protected $_options = array();

    public function toOptionArray($isMultiselect=false)
    {
        if (!$this->_options) {
            $cities = Mage::getModel('extensa_econt/city')->getCollection()->addOffices()->setDeliveryType('from_office')->setAps(0);

            foreach ($cities as $city) {
                $this->_options[] = array(
                    'label' => $city->getName(),
                    'value' => $city->getCityId(),
                );
            }
        }

        $options = $this->_options;
        if(!$isMultiselect){
            array_unshift($options, array('value'=>'', 'label'=> Mage::helper('extensa_econt')->__('--Please Select--')));
        }

        return $options;
    }
}
