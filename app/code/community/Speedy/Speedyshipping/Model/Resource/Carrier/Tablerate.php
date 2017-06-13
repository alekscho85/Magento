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
 * Shipping table rates
 *
 * @category   Mage
 * @package    Mage_Shipping
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Speedy_Speedyshipping_Model_Resource_Carrier_Tablerate extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Import table rates website ID
     *
     * @var int
     */
    protected $_importWebsiteId     = 0;

    /**
     * Errors in import process
     *
     * @var array
     */
    protected $_importErrors        = array();

    /**
     * Count of imported table rates
     *
     * @var int
     */
    protected $_importedRows        = 0;

    /**
     * Array of unique table rate keys to protect from duplicates
     *
     * @var array
     */
    protected $_importUniqueHash    = array();

    /**
     * Define main table and id field name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('speedyshippingmodule/tablerate', 'pk');
    }

    protected $_fileColumnsIndexes = array();

    /**
     * Upload table rate file and import data from it
     *
     * @param Varien_Object $object
     * @throws Mage_Core_Exception
     * @return Mage_Shipping_Model_Resource_Carrier_Tablerate
     */
    public function uploadAndImport(Varien_Object $object)
    {
        if (empty($_FILES['groups']['tmp_name']['speedyshippingmodule']['fields']['tableRate']['value'])) {
            return $this;
        }

        $csvFile = $_FILES['groups']['tmp_name']['speedyshippingmodule']['fields']['tableRate']['value'];
        $website = Mage::app()->getWebsite($object->getScopeId());

        $this->_importWebsiteId     = (int)$website->getId();
        $this->_importUniqueHash    = array();
        $this->_importErrors        = array();
        $this->_importedRows        = 0;

        $io     = new Varien_Io_File();
        $info   = pathinfo($csvFile);
        $io->open(array('path' => $info['dirname']));
        $io->streamOpen($info['basename'], 'r');

        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();

        try {
            $rowNumber  = 0;
            $importData = array();

            // delete old data by website
            $condition = array(
                'website_id = ?'     => $this->_importWebsiteId
            );
            $adapter->delete($this->getMainTable(), $condition);

            while (false !== ($csvLine = $io->streamReadCsv())) {
                $rowNumber ++;

                if (empty($csvLine)) {
                    continue;
                }

                $row = $this->_getImportRow($csvLine, $rowNumber);
                if ($row !== false) {
                    $importData[] = $row;
                }

                if (count($importData) == 5000) {
                    $this->_saveImportData($importData);
                    $importData = array();
                }
            }
            $this->_saveImportData($importData);
            $io->streamClose();
        } catch (Mage_Core_Exception $e) {
            $adapter->rollback();
            $io->streamClose();
            Mage::throwException($e->getMessage());
        } catch (Exception $e) {
            $adapter->rollback();
            $io->streamClose();
            Mage::logException($e);
            Mage::throwException(Mage::helper('shipping')->__('An error occurred while import table rates.'));
        }

        $adapter->commit();

        if ($this->_importErrors) {
            $error = Mage::helper('shipping')->__('File has not been imported. See the following list of errors: %s', implode(" \n", $this->_importErrors));
            Mage::throwException($error);
        }

        return $this;
    }

    /**
     * Validate row for import and return table rate array or false
     * Error will be add to _importErrors array
     *
     * @param array $row
     * @param int $rowNumber
     * @return array|false
     */
    protected function _getImportRow($row, $rowNumber = 0)
    {
        $file_columns = array(
            'ServiceID',
            'TakeFromOffice',
            'Weight',
            'OrderTotal',
            'PriceWithoutVAT',
            'FixedTimeDelivery',
        );

        if ($rowNumber == 1) {
            foreach($row as $index => $columnName) {
                $this->_fileColumnsIndexes[$columnName] = array_search($columnName, $row);
            }

            sort($row);
            sort($file_columns);

            // validate row
            if ($row != $file_columns) {
                $this->_importErrors[] = Mage::helper('shipping')->__('Invalid Speedy Tablerates format in the Row #%s', $rowNumber);
                return false;
            }

            return false;
        }

        // strip whitespace from the beginning and end of each row
        foreach ($row as $k => $v) {
            $row[$k] = trim($v);
        }

        // validate Service ID
        if (!(int)$row[$this->_fileColumnsIndexes['ServiceID']]) {
            $this->_importErrors[] = Mage::helper('shipping')->__('Invalid ServiceID "%d" in the Row #%s', $row[$this->_fileColumnsIndexes['ServiceID']], $rowNumber);
            return false;
        } else {
            $ServiceID = (int)$row[$this->_fileColumnsIndexes['ServiceID']];
        }

        $TakeFromOffice = (int)$row[$this->_fileColumnsIndexes['TakeFromOffice']];

        // validate weight
        $Weight = $this->_parseDecimalValue($row[$this->_fileColumnsIndexes['Weight']]);
        if ($Weight === false) {
            $this->_importErrors[] = Mage::helper('shipping')->__('Invalid Weight "%s" in the Row #%s.', $row[$this->_fileColumnsIndexes['Weight']], $rowNumber);
            return false;
        }

        // validate ordertotal
        $OrderTotal = $this->_parseDecimalValue($row[$this->_fileColumnsIndexes['OrderTotal']]);
        if ($OrderTotal === false) {
            $this->_importErrors[] = Mage::helper('shipping')->__('Invalid OrderTotal "%s" in the Row #%s.', $row[$this->_fileColumnsIndexes['OrderTotal']], $rowNumber);
            return false;
        }

        // validate price without vat
        $PriceWithoutVAT = $this->_parseDecimalValue($row[$this->_fileColumnsIndexes['PriceWithoutVAT']]);
        if ($PriceWithoutVAT === false) {
            $this->_importErrors[] = Mage::helper('shipping')->__('Invalid PriceWithoutVAT "%s" in the Row #%s.', $row[$this->_fileColumnsIndexes['PriceWithoutVAT']], $rowNumber);
            return false;
        }

        $FixedTimeDelivery = (int)$row[$this->_fileColumnsIndexes['FixedTimeDelivery']];

        // protect from duplicate
        $hash = sprintf("%d-%d-%F-%F-%F-%d", $ServiceID, $TakeFromOffice, $Weight, $OrderTotal, $PriceWithoutVAT, $FixedTimeDelivery);
        if (isset($this->_importUniqueHash[$hash])) {
            $this->_importErrors[] = Mage::helper('shipping')->__('Duplicate Row #%s (ServiceID "%d", TakeFromOffice "%s", Weight "%F", OrderTotal "%F", PriceWithoutVAT "%F" and FixedTimeDelivery "%d").', $rowNumber, $ServiceID, $TakeFromOffice, $Weight, $OrderTotal, $PriceWithoutVAT, $FixedTimeDelivery);
            return false;
        }
        $this->_importUniqueHash[$hash] = true;

        return array(
            $ServiceID,                 // Service ID
            $TakeFromOffice,            // Take From Office,
            $Weight,                    // Weight
            $OrderTotal,                // Order Total
            $PriceWithoutVAT,           // Price Without VAT
            $FixedTimeDelivery,         // Fixed Time Delivery
            $this->_importWebsiteId     // Website Id
        );
    }

    /**
     * Save import data batch
     *
     * @param array $data
     * @return Mage_Shipping_Model_Resource_Carrier_Tablerate
     */
    protected function _saveImportData(array $data)
    {
        if (!empty($data)) {
            $columns = array('service_id', 'take_from_office', 'weight', 'order_total', 'price_without_vat', 'fixed_time_delivery', 'website_id',);
            $this->_getWriteAdapter()->insertArray($this->getMainTable(), $columns, $data);
            $this->_importedRows += count($data);
        }

        return $this;
    }

    /**
     * Parse and validate positive decimal value
     * Return false if value is not decimal or is not positive
     *
     * @param string $value
     * @return bool|float
     */
    protected function _parseDecimalValue($value)
    {
        $value = str_replace(',', '.', $value);

        if (!is_numeric($value)) {
            return false;
        }
        $value = (float)sprintf('%.4F', $value);
        if ($value < 0.0000) {
            return false;
        }
        return $value;
    }
}
