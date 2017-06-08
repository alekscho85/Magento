<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_ValidateController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        Mage::getSingleton('checkout/session')->unsExtensaEcont();
        $results_data['error'] = true;

        $post = $this->getRequest()->getPost($this->getRequest()->getPost('shipping_method_name'));

        if (empty($post['next_step'])) {
            $results_data['error'] = false;
        } else {
            if ($post['shipping_to'] == 'OFFICE' && $post['office_id'] && $post['office_code'] && $post['office_city_id']) {
                $results_data['error'] = false;
            } elseif ($post['shipping_to'] == 'APS' && $post['office_aps_id'] && $post['office_aps_code'] && $post['office_city_aps_id']) {
                if ($post['cd_payment']) {
                    $results_data['error'] = false;
                }
            } else {
                if ($post['postcode'] && $post['city'] && ($post['quarter'] && $post['other'] || $post['street'] && $post['street_num'])) {
                    $city = Mage::getModel('extensa_econt/city')
                        ->getCollection()
                        ->setNameFilter($post['city'], false)
                        ->setPostcodeFilter($post['postcode']);
                    if ($post['quarter']) {
                        $city->addQuarters()->setQuarterNameFilter($post['quarter']);
                    }
                    if ($post['street']) {
                        $city->addStreets()->setStreetNameFilter($post['street']);
                    }

                    if ($city && $city->getSize()) {
                        $results_data['error'] = false;
                    }
                }
            }
        }

        if (!$results_data['error']) {
            if (!empty($post['next_step'])) {
                $post['save_shipping_method'] = true;
            } else {
                $post['save_shipping'] = true;
            }

            Mage::getSingleton('checkout/session')->setExtensaEcont($post);

            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $model = Mage::getModel('extensa_econt/customer');
                $model->getResource()->setPkAutoIncrement(false);
                $model->setId(Mage::getSingleton('customer/session')->getCustomer()->getId())
                    ->addData($post)
                    ->save();
            }
        } else {
            $results_data['message'] = Mage::helper('extensa_econt')->__('Адресът е невалиден.');
        }

        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode($results_data)
        );
    }
}
