<?php
/**
 * Econt Model observer
 *
 * @author Extensa <support@extensadev.com>
 */
class Extensa_Econt_Model_Observer
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function restrictPaymentsCd($observer)
    {
        if ($observer->getEvent()->hasQuote()) {
            $shippingMethod = $observer->getEvent()->getQuote()->getShippingAddress()->getShippingMethod();
            $paymentMethod = $observer->getEvent()->getMethodInstance()->getCode();

            if (strpos($shippingMethod, 'extensa_econt') === false && $paymentMethod == 'extensa_econt') {
                $observer->getEvent()->getResult()->isAvailable = false;
            } elseif (Mage::getSingleton('checkout/session')->hasExtensaEcont() && $observer->getEvent()->hasQuote()) {
                $session = Mage::getSingleton('checkout/session')->getExtensaEcont();

                if (strpos($shippingMethod, 'extensa_econt') !== false && !empty($session['cd_payment']) && $paymentMethod != 'extensa_econt' ||
                    strpos($shippingMethod, 'extensa_econt') !== false && empty($session['cd_payment']) && $paymentMethod == 'extensa_econt') {
                    $observer->getEvent()->getResult()->isAvailable = false;
                }
            }
        }
    }

    public function saveOnepageOrder($observer)
    {
        if (Mage::getSingleton('checkout/session')->hasExtensaEcont()) {
            $session = Mage::getSingleton('checkout/session')->getExtensaEcont();
            $order = $observer->getEvent()->getOrder();

            if (strpos($order->getShippingMethod(), 'extensa_econt') !== false) {
                $econt_order = Mage::getModel('extensa_econt/order')->load($session['econt_order_id']);
                $econt_order_data = unserialize($econt_order->getData('data'));

                if (strpos($order->getShippingMethod(), 'office') !== false) {
                    $econt_order_row = $econt_order_data['loadings']['to_office']['row'];
                    unset($econt_order_data['loadings']['to_office'], $econt_order_data['loadings']['to_door'], $econt_order_data['loadings']['to_aps']);
                    $econt_order_data['loadings']['row'] = $econt_order_row;
                    $econt_order_data['shipping_to'] = 'OFFICE';
                } elseif (strpos($order->getShippingMethod(), 'door') !== false) {
                    $econt_order_row = $econt_order_data['loadings']['to_door']['row'];
                    unset($econt_order_data['loadings']['to_office'], $econt_order_data['loadings']['to_door'], $econt_order_data['loadings']['to_aps']);
                    $econt_order_data['loadings']['row'] = $econt_order_row;
                    $econt_order_data['shipping_to'] = 'DOOR';
                } elseif (strpos($order->getShippingMethod(), 'aps') !== false) {
                    $econt_order_row = $econt_order_data['loadings']['to_aps']['row'];
                    unset($econt_order_data['loadings']['to_office'], $econt_order_data['loadings']['to_door'], $econt_order_data['loadings']['to_aps']);
                    $econt_order_data['loadings']['row'] = $econt_order_row;
                    $econt_order_data['shipping_to'] = 'APS';
                }

                if ($order->getPayment()->getMethodInstance()->getCode() != 'extensa_econt' && (float)$order->getShippingAmount() > 0) {
                    $econt_order_data['loadings']['row']['payment']['side'] = 'SENDER';
                    $econt_order_data['loadings']['row']['payment']['receiver_share_sum'] = '';
                }

                $econt_order->setData('data', serialize($econt_order_data));
                $econt_order->setOrderId($observer->getEvent()->getOrder()->getId());
                $econt_order->save();

                $econt_receiver_address = array();

                if (strpos($order->getShippingMethod(), 'office') !== false) {
                    $receiver_office = Mage::getModel('extensa_econt/office')->load($session['office_id']);
                    if ($receiver_office->getId()) {
                        $econt_receiver_address[] = Mage::helper('extensa_econt')->__('До офис:') . ' ' .  (Mage::helper('extensa_econt')->getLanguage() == 'bg_BG' ? $receiver_office->getOfficeCode() . ', ' . $receiver_office->getName() . ', ' . $receiver_office->getAddress() : $receiver_office->getOfficeCode() . ', ' . $receiver_office->getNameEn() . ', ' . $receiver_office->getAddressEn());
                    }
                } elseif (strpos($order->getShippingMethod(), 'aps') !== false) {
                    $receiver_office_aps = Mage::getModel('extensa_econt/office')->load($session['office_aps_id']);
                    if ($receiver_office_aps->getId()) {
                        $econt_receiver_address[] = Mage::helper('extensa_econt')->__('До офис:') . ' ' .  (Mage::helper('extensa_econt')->getLanguage() == 'bg_BG' ? $receiver_office_aps->getOfficeCode() . ', ' . $receiver_office_aps->getName() . ', ' . $receiver_office_aps->getAddress() : $receiver_office_aps->getOfficeCode() . ', ' . $receiver_office_aps->getNameEn() . ', ' . $receiver_office_aps->getAddressEn());
                    }
                } else {
                    if ($econt_order_row['receiver']['quarter']) {
                        $econt_receiver_address[] = $econt_order_row['receiver']['quarter'];
                    }

                    if ($econt_order_row['receiver']['street']) {
                        $econt_receiver_street = $econt_order_row['receiver']['street'];

                        if ($econt_order_row['receiver']['street_num']) {
                            $econt_receiver_street .= ' ' . $econt_order_row['receiver']['street_num'];
                        }

                        $econt_receiver_address[] = $econt_receiver_street;
                    }

                    if ($econt_order_row['receiver']['street_other']) {
                        $econt_receiver_address[] = $econt_order_row['receiver']['street_other'];
                    }
                }

                $order->getShippingAddress()
                    ->setRegionId(null)
                    ->setRegion(null)
                    ->setPostcode($econt_order_row['receiver']['post_code'])
                    ->setCity($econt_order_row['receiver']['city'])
                    ->setStreet(implode(', ', $econt_receiver_address))
                    ->setCountryId('BG');
                if ($econt_order_row['receiver']['name'] != $econt_order_row['receiver']['name_person']) {
                    $order->getShippingAddress()->setCompany($econt_order_row['receiver']['name']);
                }
                $order->getShippingAddress()->save();
            }
        }
        return $this;
    }
}
