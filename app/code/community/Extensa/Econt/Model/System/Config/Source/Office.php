<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Model_System_Config_Source_Office
{
    /**
     * Returns array to be used in select on back-end
     *
     * @return array
     */
    protected $_options = array();

    public function toOptionArray($isMultiselect=false)
    {
        if (!$this->_options && Mage::helper('extensa_econt')->getStoreConfig('office_city_id')) {
            $offices = Mage::getModel('extensa_econt/office')
                ->getCollection()
                ->setCityId(Mage::helper('extensa_econt')->getStoreConfig('office_city_id'))
                ->setDeliveryType('from_office')
                ->setAps(0)
                ->getData();

            foreach ($offices as $office) {
                $this->_options[] = array('value'=>$office['office_id'], 'label'=> ($office['office_code'] . ', ' . $office['name'] . ', ' . $office['address']));
            }
        }

        $options = $this->_options;
        if(!$isMultiselect){
            array_unshift($options, array('value'=>'', 'label'=> Mage::helper('extensa_econt')->__('--Please Select--')));
        }

        return $options;
    }
}
