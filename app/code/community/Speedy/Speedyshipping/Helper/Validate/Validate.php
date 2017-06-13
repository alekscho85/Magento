<?php

class Speedy_Speedyshipping_Helper_Validate_Validate extends Mage_Directory_Helper_Data
{
    public function getCountriesWithOptionalZip($asJson = false)
    {
        if (null === $this->_optionalZipCountries) {
            $this->_optionalZipCountries = preg_split('/\,/',
                Mage::getStoreConfig('general/country/allow'), 0, PREG_SPLIT_NO_EMPTY);
        }
        if ($asJson) {
            return Mage::helper('core')->jsonEncode($this->_optionalZipCountries);
        }
        return $this->_optionalZipCountries;
    }
}