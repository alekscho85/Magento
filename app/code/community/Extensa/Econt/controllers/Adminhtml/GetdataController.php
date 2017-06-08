<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Adminhtml_GetdataController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', 3600);

        $data = array();

        $results_data = array();
        $results_data['error'] = false;
        $results_data['success'] = false;

        $step = Mage::app()->getRequest()->getPost('step', 0);

        if (!$results_data['error'] && !$step) {
            $data['type'] = 'countries';

            $results = Mage::helper('extensa_econt')->serviceTool($data);

            if ($results) {
                if (isset($results->error)) {
                    $results_data['error'] = true;
                    $results_data['message'] = (string)$results->error->message;
                } else {
                    if (isset($results->e)) {
                        $model = Mage::getModel('extensa_econt/country');
                        $model->getCollection()->truncate();

                        foreach ($results->e as $country) {
                            $country_data = array(
                                'name'    => (string)$country->country_name,
                                'name_en' => (string)$country->country_name_en,
                                'zone_id' => (int)$country->id_zone,
                            );

                            $model->setData($country_data)->save();
                        }
                    }

                    $results_data['step'] = $step + 1;
                }
            } else {
                $results_data['error'] = true;
                $results_data['message'] = Mage::helper('extensa_econt')->__('Възникна грешка при връзката с Еконт! Моля, опитайте отново!');
            }
        }

        if (!$results_data['error'] && $step == 1) {
            $data['type'] = 'cities_zones';

            $results = Mage::helper('extensa_econt')->serviceTool($data);

            if ($results) {
                if (isset($results->error)) {
                    $results_data['error'] = true;
                    $results_data['message'] = (string)$results->error->message;
                } else {
                    if (isset($results->zones)) {
                        $model = Mage::getModel('extensa_econt/zone');
                        $model->getCollection()->truncate();
                        $model->getResource()->setPkAutoIncrement(false);

                        foreach ($results->zones->e as $zone) {
                            $zone_data = array(
                                'zone_id'  => (int)$zone->id,
                                'name'     => (string)$zone->name,
                                'name_en'  => (string)$zone->name_en,
                                'national' => (int)$zone->national,
                                'is_ee'    => (int)$zone->is_ee,
                            );

                            $model->setData($zone_data)->save();
                        }
                    }

                    $results_data['step'] = $step + 1;
                }
            } else {
                $results_data['error'] = true;
                $results_data['message'] = Mage::helper('extensa_econt')->__('Възникна грешка при връзката с Еконт! Моля, опитайте отново!');
            }
        }

        if (!$results_data['error'] && $step == 2) {
            $data['type'] = 'cities_regions';

            $results = Mage::helper('extensa_econt')->serviceTool($data);

            if ($results) {
                if (isset($results->error)) {
                    $results_data['error'] = true;
                    $results_data['message'] = (string)$results->error->message;
                } else {
                    if (isset($results->cities_regions)) {
                        $model = Mage::getModel('extensa_econt/region');
                        $model->getCollection()->truncate();
                        $model->getResource()->setPkAutoIncrement(false);

                        foreach ($results->cities_regions->e as $region) {
                            $region_data = array(
                                'region_id' => (int)$region->id,
                                'name'      => (string)$region->name,
                                'name_en'   => (string)$region->name_en,
                                'city_id'   => (int)$region->id_city,
                            );

                            $model->setData($region_data)->save();
                        }
                    }

                    $results_data['step'] = $step + 1;
                }
            } else {
                $results_data['error'] = true;
                $results_data['message'] = Mage::helper('extensa_econt')->__('Възникна грешка при връзката с Еконт! Моля, опитайте отново!');
            }
        }

        if (!$results_data['error'] && $step == 3) {
            $data['type'] = 'cities_quarters';

            $results = Mage::helper('extensa_econt')->serviceTool($data);

            if ($results) {
                if (isset($results->error)) {
                    $results_data['error'] = true;
                    $results_data['message'] = (string)$results->error->message;
                } else {
                    if (isset($results->cities_quarters)) {
                        $model = Mage::getModel('extensa_econt/quarter');
                        $model->getCollection()->truncate();
                        $model->getResource()->setPkAutoIncrement(false);

                        foreach ($results->cities_quarters->e as $quarter) {
                            $quarter_data = array(
                                'quarter_id' => (int)$quarter->id,
                                'name'       => (string)$quarter->name,
                                'name_en'    => (string)$quarter->name_en,
                                'city_id'    => (int)$quarter->id_city,
                            );

                            $model->setData($quarter_data)->save();
                        }
                    }

                    $results_data['step'] = $step + 1;
                }
            } else {
                $results_data['error'] = true;
                $results_data['message'] = Mage::helper('extensa_econt')->__('Възникна грешка при връзката с Еконт! Моля, опитайте отново!');
            }
        }

        if (!$results_data['error'] && $step == 4) {
            $data['type'] = 'cities_streets';

            $results = Mage::helper('extensa_econt')->serviceTool($data);

            if ($results) {
                if (isset($results->error)) {
                    $results_data['error'] = true;
                    $results_data['message'] = (string)$results->error->message;
                } else {
                    if (isset($results->cities_street)) {
                        $model = Mage::getModel('extensa_econt/street');
                        $model->getCollection()->truncate();
                        $model->getResource()->setPkAutoIncrement(false);

                        foreach ($results->cities_street->e as $street) {
                            $street_data = array(
                                'street_id' => (int)$street->id,
                                'name'      => (string)$street->name,
                                'name_en'   => (string)$street->name_en,
                                'city_id'   => (int)$street->id_city,
                            );

                            $model->setData($street_data)->save();
                        }
                    }

                    $results_data['step'] = $step + 1;
                }
            } else {
                $results_data['error'] = true;
                $results_data['message'] = Mage::helper('extensa_econt')->__('Възникна грешка при връзката с Еконт! Моля, опитайте отново!');
            }
        }

        if (!$results_data['error'] && $step == 5) {
            $data['type'] = 'offices';

            $results = Mage::helper('extensa_econt')->serviceTool($data);

            if ($results) {
                if (isset($results->error)) {
                    $results_data['error'] = true;
                    $results_data['message'] = (string)$results->error->message;
                } else {
                    if (isset($results->offices)) {
                        $model = Mage::getModel('extensa_econt/office');
                        $model->getCollection()->truncate();
                        $model->getResource()->setPkAutoIncrement(false);

                        foreach ($results->offices->e as $office) {
                            $office_data = array(
                                'office_id'           => (int)$office->id,
                                'name'                => (string)$office->name,
                                'name_en'             => (string)$office->name_en,
                                'office_code'         => (string)$office->office_code,
                                'is_machine'          => (int)$office->is_machine,
                                'address'             => (string)$office->address,
                                'address_en'          => (string)$office->address_en,
                                'phone'               => (string)$office->phone,
                                'work_begin'          => Varien_Date::now(true) . ' ' . (string)$office->work_begin,
                                'work_end'            => Varien_Date::now(true) . ' ' . (string)$office->work_end,
                                'work_begin_saturday' => Varien_Date::now(true) . ' ' . (string)$office->work_begin_saturday,
                                'work_end_saturday'   => Varien_Date::now(true) . ' ' . (string)$office->work_end_saturday,
                                'time_priority'       => Varien_Date::now(true) . ' ' . (string)$office->time_priority,
                                'city_id'             => (int)$office->id_city,
                            );

                            //$office_data = $this->_filterDateTime($office_data, array('work_begin', 'work_end', 'work_begin_saturday', 'work_end_saturday', 'time_priority'));

                            $model->setData($office_data)->save();
                        }
                    }

                    $results_data['step'] = $step + 1;
                }
            } else {
                $results_data['error'] = true;
                $results_data['message'] = Mage::helper('extensa_econt')->__('Възникна грешка при връзката с Еконт! Моля, опитайте отново!');
            }
        }

        if (!$results_data['error'] && $step == 6) {
            $data['type'] = 'cities';

            $results = Mage::helper('extensa_econt')->serviceTool($data);

            if ($results) {
                if (isset($results->error)) {
                    $results_data['error'] = true;
                    $results_data['message'] = (string)$results->error->message;
                } else {
                    if (isset($results->cities)) {
                        $model = Mage::getModel('extensa_econt/city');
                        $model->getCollection()->truncate();
                        $model->getResource()->setPkAutoIncrement(false);

                        $model2 = Mage::getModel('extensa_econt/cityoffice');
                        $model2->getCollection()->truncate();

                        //$attach_offices = array(); //workaround because the xml is very large

                        foreach ($results->cities->e as $city) {
                            $city_data = array(
                                'city_id'    => (int)$city->id,
                                'post_code'  => (string)$city->post_code,
                                'type'       => (string)$city->type,
                                'name'       => (string)$city->name,
                                'name_en'    => (string)$city->name_en,
                                'zone_id'    => (int)$city->id_zone,
                                'country_id' => (int)$city->id_country,
                                'office_id'  => (int)$city->id_office,
                            );

                            $model->setData($city_data)->save();

                            if (isset($city->attach_offices)) {
                                foreach ($city->attach_offices->children() as $shipment_type) {
                                    foreach ($shipment_type->children() as $delivery_type) {
                                        foreach ($delivery_type->office_code as $office_code) {
                                            //if (!isset($attach_offices[(int)$office_code][(int)$city->id])) { //workaround because the xml is very large
                                                $city_office_data = array(
                                                    'office_code'   => (string)$office_code,
                                                    'shipment_type' => (string)$shipment_type->getName(), //is's not using for now
                                                    'delivery_type' => (string)$delivery_type->getName(), //is's not using for now
                                                    'city_id'       => (int)$city->id,
                                                );

                                                $model2->setData($city_office_data)->save();

                                                //$attach_offices[(int)$office_code][(int)$city->id] = true; //workaround because the xml is very large
                                            //}
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $results_data['success'] = true;
                    $results_data['message'] = Mage::helper('extensa_econt')->__('Успешно обновихте информацията!');
                }
            } else {
                $results_data['error'] = true;
                $results_data['message'] = Mage::helper('extensa_econt')->__('Възникна грешка при връзката с Еконт! Моля, опитайте отново!');
            }
        }

        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode($results_data)
        );
    }
}
