<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Shipping
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shipping table rates collection
 *
 * @category   Mage
 * @package    Mage_Shipping
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Speedy_Speedyshipping_Model_Resource_Carrier_Tablerate_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Define resource model and item
     *
     */
    protected function _construct()
    {
        $this->_init('speedyshippingmodule/carrier_tablerate');
        // $this->_shipTable       = $this->getMainTable();
    }

    /**
     * Add website filter to collection
     *
     * @param int $websiteId
     * @return Mage_Shipping_Model_Resource_Carrier_Tablerate_Collection
     */
    public function setWebsiteFilter($websiteId)
    {
        return $this->addFieldToFilter('website_id', $websiteId);
    }

    /**
     * Add service filter to collection
     *
     * @param string $serviceId
     * @return Mage_Shipping_Model_Resource_Carrier_Tablerate_Collection
     */
    public function setServiceIdFilter($serviceId)
    {
        return $this->addFieldToFilter('service_id', $serviceId);
    }

    /**
     * Add service filter to collection
     *
     * @param string $serviceId
     * @return Mage_Shipping_Model_Resource_Carrier_Tablerate_Collection
     */
    public function setOrderField($field)
    {
        return $this->addOrder($field, self::SORT_ORDER_ASC);
    }

    /**
     * Add take from office filter to collection
     *
     * @param string $serviceId
     * @return Mage_Shipping_Model_Resource_Carrier_Tablerate_Collection
     */
    public function setTakeFromOfficeFilter($takeFromOffice)
    {
        return $this->addFieldToFilter('take_from_office', $takeFromOffice);
    }

    /**
     * Add weight filter to collection
     *
     * @param string $serviceId
     * @return Mage_Shipping_Model_Resource_Carrier_Tablerate_Collection
     */
    public function setWeightFilter($weight)
    {
        return $this->addFieldToFilter('weight', array('gteq' => (float)$weight));
    }

    /**
     * Add order total filter to collection
     *
     * @param string $serviceId
     * @return Mage_Shipping_Model_Resource_Carrier_Tablerate_Collection
     */
    public function setTotalFilter($total)
    {
        return $this->addFieldToFilter('order_total', array('gteq' => (float)$total));
    }

    /**
     * Add fixed time delivery filter to collection
     *
     * @param string $fixedTimeDelivery
     * @return Mage_Shipping_Model_Resource_Carrier_Tablerate_Collection
     */
    public function setFixedTimeDeliveryFilter($fixedTimeDelivery)
    {
        return $this->addFieldToFilter('fixed_time_delivery', $fixedTimeDelivery);
    }
}
