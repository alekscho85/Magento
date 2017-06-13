<?php


class Trackingmore_Detrack_Model_Track extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('detrack/track');
    }

    const STATUS_PENDING = 'pending';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_EXCEPTION = 'exception';
    const STATUS_NO_INFO = 'no_info';
 

   
    const TYPE_SUBMITTED_PENDING = 0;

 
    const TYPE_SUBMITTED_SENT = 1;

 
    const TYPE_SUBMITTED_REMOVE_PENDING = 2;

    
    public static $mapCols = array(
        'status',
    );


   
    protected $_shippingInfo;

   
    protected $_carrier;

   
    public static function getOrderedStatusList()
    {
        return array(
            self::STATUS_PENDING => 10,
            self::STATUS_NO_INFO => 10,
            self::STATUS_IN_TRANSIT => 30,
            self::STATUS_EXCEPTION => 30,
            self::STATUS_DELIVERED => 60,
        );
    }

   
    protected function _beforeSave()
    {
       
        if (!$this->getId()) {
            $this->created_at = time();
        }else{
            $this->updated_at = time();
		}
        return parent::_beforeSave();
    }

 
 
    public function updateInfo($data)
    {   
        foreach (self::$mapCols as $field) {
            if (isset($data[$field]))
                $this->$field = $data[$field];
        }
       
        $this->save();
    }

    public function getOrderDataByIdAndCode($orderId, $code, $fetchData = true)
    {
        $fromDbData = Mage::getModel('detrack/track')
            ->getCollection()
            ->addFieldToFilter('code', array('eq' => $code))
            ->addFieldToFilter('order_id', array('eq' => $orderId));
        $trackModel = $fromDbData->getFirstItem();
        $this->setData($trackModel->getData());
        return $this;
    }

 
    public function getDataByExpressAndCode($carrier, $code)
    {
        $fromDbData = Mage::getModel('detrack/track')
            ->getCollection()
            ->addFieldToFilter('code', array('eq' => $code))
            ->addFieldToFilter('carrier_code', array('eq' => $carrier));
        $trackModel = $fromDbData->getFirstItem();
        $this->setData($trackModel->getData());

        return $this;
    }

 
    public function loadInfoByHash($hash, $fetchData = true)
    {
        $this->load($hash, 'hash');

    }

}
