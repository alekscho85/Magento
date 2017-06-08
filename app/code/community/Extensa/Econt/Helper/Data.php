<?php
/**
 * Econt Data helper
 *
 * @author Extensa <support@extensadev.com>
 */
class Extensa_Econt_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Path to store config
     *
     * @var string
     */
    const XML_PATH = 'carriers/extensa_econt/';

    /**
     * Retrieve config value for store by module path
     *
     * @param string $path
     * @param integer|string|Mage_Core_Model_Store $store
     * @return mixed
     */
    public function getStoreConfig($path, $store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH . $path, $store);
    }

    /**
     * Retrieve config flag for store by module path
     *
     * @param string $path
     * @param integer|string|Mage_Core_Model_Store $store
     * @return boolean
     */
    public function getStoreConfigFlag($path, $store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH . $path, $store);
    }

    public function setDefaultData($data)
    {
        if (!isset($data['test'])) {
            $data['test'] = Mage::app()->getRequest()->getPost('test', $this->getStoreConfigFlag('test'));
        }
        if (!isset($data['username'])) {
            $data['username'] = Mage::app()->getRequest()->getPost('username', $this->getStoreConfig('username'));
        }
        if (!isset($data['password'])) {
            $data['password'] = Mage::app()->getRequest()->getPost('password', $this->getStoreConfig('password'));
        }

        return $data;
    }

    public function serviceTool($data)
    {
        $data = $this->setDefaultData($data);

        if (!$data['test']) {
            $url = 'http://www.econt.com/e-econt/xml_service_tool.php';
        } else {
            $url = 'http://demo.econt.com/e-econt/xml_service_tool.php';
        }

        $request = '<?xml version="1.0" ?>
                    <request>
                        <client>
                            <username>' . htmlspecialchars($data['username'], ENT_COMPAT, 'UTF-8') . '</username>
                            <password>' . htmlspecialchars($data['password'], ENT_COMPAT, 'UTF-8') . '</password>
                        </client>
                        <client_software>ExtensaMagento</client_software>
                        <request_type>' . $data['type'] . '</request_type>
                        <mediator>extensa</mediator>';

        if (isset($data['xml'])) {
            $request .= $data['xml'];
        }

        $request .= '</request>';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('xml' => $request));

        $response = curl_exec($ch);

        curl_close($ch);

        libxml_use_internal_errors(true);
        return simplexml_load_string($response);
    }

    public function parcelImport($data) {
        if (!$this->getStoreConfigFlag('test')) {
            $url = 'http://www.econt.com/e-econt/xml_parcel_import2.php';
        } else {
            $url = 'http://demo.econt.com/e-econt/xml_parcel_import2.php';
        }

        foreach ($data['loadings'] as $key => $row) {
            $data['loadings'][$key]['row']['mediator'] = 'extensa';
        }

        $request = '<?xml version="1.0" ?>';
        $request .= '<parcels>';
        $request .= $this->_prepareXML($data);
        $request .= '</parcels>';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('xml' => $request));

        $response = curl_exec($ch);

        curl_close($ch);

        libxml_use_internal_errors(true);
        return simplexml_load_string($response);
    }

    protected function _prepareXML($data) {
        $xml = '';

        foreach ($data as $key => $value) {
            if ($key && $key == 'error') {
                continue;
            }

            if ($key && ($key == 'p' || $key == 'cd')) {
                $xml .= '<' . $key . ' type="' . $value['type'] . '">' . $value['value'] . '</' . $key . '>' . "\r\n";
            } else {
                if (!is_numeric($key) && $key != 'to_door' && $key != 'to_office' && $key != 'to_aps') {
                    $xml .= '<' . $key . '>';
                }

                if (is_array($value)) {
                    $xml .= "\r\n" . $this->_prepareXML($value);
                } else {
                    $xml .= Mage::helper('core')->htmlEscape($value);
                }

                if (!is_numeric($key) && $key != 'to_door' && $key != 'to_office' && $key != 'to_aps') {
                    $xml .= '</' . $key . '>' . "\r\n";
                }
            }
        }

        return $xml;
    }

    public function getLanguage()
    {
        return Mage::app()->getLocale()->getLocaleCode();
    }

    public function getAutocompleteCityUrl()
    {
        return Mage::getUrl('extensa_econt/autocomplete/city', array('_secure' => true));
    }

    public function getAutocompleteQuarterUrl()
    {
        return Mage::getUrl('extensa_econt/autocomplete/quarter', array('_secure' => true));
    }

    public function getAutocompleteStreetUrl()
    {
        return Mage::getUrl('extensa_econt/autocomplete/street', array('_secure' => true));
    }

    public function getOfficesUrl()
    {
        return Mage::getUrl('extensa_econt/office/list', array('_secure' => true));
    }

    public function getOfficeUrl()
    {
        return Mage::getUrl('extensa_econt/office', array('_secure' => true));
    }

    public function getOfficeByCodeUrl()
    {
        return Mage::getUrl('extensa_econt/office/bycode', array('_secure' => true));
    }

    public function getOfficeLocatorUrl()
    {
        return 'https://www.bgmaps.com/templates/econt?theme=orange';
    }

    public function getOfficeLocatorDomain()
    {
        return 'https://www.bgmaps.com';
    }

    public function getApsHelpUrl()
    {
        return 'http://www.econt.com/24-chasa-econt-aps/';
    }

    public function getInstructionsFormUrl($cliendId)
    {
        $data = $this->setDefaultData(array());

        if (!$data['test']) {
            $url = 'http://ee.econt.com/load_direct.php?target=EeLoadingInstructions';
        } else {
            $url = 'http://demo.econt.com/ee/load_direct.php?target=EeLoadingInstructions';
        }

        return $url . '&login_username=' . $data['username'] . '&login_password=' . md5($data['password']) . '&target_type=client&id_target=' . $cliendId;
    }

    public function getCourierUrl()
    {
        return 'http://ee.econt.com/?target=EeRequestOfCourier&eshop=1';
    }

    public function getDayName($day)
    {
        $days = array(
            0 => $this->__('неделя'),
            1 => $this->__('понеделник'),
            2 => $this->__('вторник'),
            3 => $this->__('сряда'),
            4 => $this->__('четвъртък'),
            5 => $this->__('петък'),
            6 => $this->__('събота'),
        );
        return $days[$day];
    }

    public function getPriorityTimeTypes()
    {
        return array(
            array(
                'id'    => 'BEFORE',
                'name'  => $this->__('преди'),
                'hours' => array(10, 11, 12, 13, 14, 15, 16, 17, 18),
            ),
            array(
                'id'    => 'IN',
                'name'  => $this->__('в'),
                'hours' => array(9, 10, 11, 12, 13, 14, 15, 16, 17, 18),
            ),
            array(
                'id'    => 'AFTER',
                'name'  => $this->__('след'),
                'hours' => array(9, 10, 11, 12, 13, 14, 15, 16, 17),
            ),
        );
    }

    public function getTrackingEvent($event)
    {
        $events = array(
            'client'            => $this->__('предаване към клиент'),
            'courier'           => $this->__('предаване към куриер'),
            'courier_direction' => $this->__('предаване към маршрутна линия'),
            'office'            => $this->__('предаване в офис'),
            'first_try'         => $this->__('първи опит за доставка'),
            'second_try'        => $this->__('последващ опит за доставка'),
        );
        return $events[(string)$event];
    }

    public function getDeliveryDays($results_data)
    {
        $data = array(
            'type' => 'delivery_days',
            'xml'  => '<delivery_days>' . date('Y-m-d') . '</delivery_days>',
        );

        $results = $this->serviceTool($data);

        if ($results) {
            if (isset($results->error)) {
                $results_data['error'] = true;
                $results_data['message'] = (string)$results->error->message;
            } else {
                if (isset($results->delivery_days)) {
                    foreach ($results->delivery_days->e as $delivery_day) {
                        $day = date('w', strtotime($delivery_day->date));
                        $results_data['delivery_days'][] = array(
                            'id'   => $delivery_day->date,
                            'day'  => $day,
                            'name' => $this->getDayName($day),
                        );

                        if ($day == 6) {
                            $results_data['priority_date'] = $delivery_day->date;
                        } elseif (!$results_data['delivery_day_id']) {
                            $results_data['delivery_day_id'] = $delivery_day->date;
                        }
                    }
                }
            }
        } else {
            $results_data['error'] = true;
            $results_data['message'] = $this->__('Възникна грешка при връзката с Еконт! Моля, опитайте отново!');
        }

        return $results_data;
    }

    /**
     * Convert weight in different measure types
     *
     * @param  mixed $value
     * @param  string $sourceWeightMeasure
     * @param  string $toWeightMeasure
     * @return int|null|string
     */
    public function convertMeasureWeight($value, $sourceWeightMeasure, $toWeightMeasure)
    {
        if ($value) {
            $locale = Mage::app()->getLocale()->getLocale();
            $unitWeight = new Zend_Measure_Weight($value, $sourceWeightMeasure, $locale);
            $unitWeight->setType($toWeightMeasure);
            return $unitWeight->getValue();
        }
        return null;
    }
}
