<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Adminhtml_GetaddressController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $data = array(
            'type' => 'profile',
        );

        $results_data = array();

        $results = Mage::helper('extensa_econt')->serviceTool($data);

        if ($results) {
            if (isset($results->error)) {
                $results_data['error'] = true;
                $results_data['message'] = (string)$results->error->message;
            } else {
                if (isset($results->client_info)) {
                    if (isset($results->client_info->id)) {
                        $results_data['id'] = (string)$results->client_info->id;
                        $results_data['instructions_form_url'] = Mage::helper('extensa_econt')->getInstructionsFormUrl($results_data['id']);
                    }

                    if (isset($results->client_info->name)) {
                        $results_data['name'] = (string)$results->client_info->name;
                    } elseif (isset($results->client_info->mol)) {
                        $results_data['name'] = (string)$results->client_info->mol;
                    }

                    if (isset($results->client_info->business_phone)) {
                        $results_data['phone'] = (string)$results->client_info->business_phone;
                    }
                }

                if (isset($results->addresses)) {
                    foreach ($results->addresses->e as $address) {
                        if (isset($address->city) && isset($address->city_post_code)) {
                            $address->city_id = Mage::getModel('extensa_econt/city')->getCollection()->setNameFilter($address->city, false)->setPostcodeFilter($address->city_post_code)->getFirstItem()->getCityId();
                        }

                        $results_data['addresses'][] = $address;
                    }
                }
            }
        } else {
            $results_data['error'] = true;
            $results_data['message'] = Mage::helper('extensa_econt')->__('Възникна грешка при връзката с Еконт! Моля, опитайте отново!');
        }

        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode($results_data)
        );
    }
}
