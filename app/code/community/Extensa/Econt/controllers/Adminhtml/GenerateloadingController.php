<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Adminhtml_GenerateloadingController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Initialize order model instance
     *
     * @return Mage_Sales_Model_Order || false
     */
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);

        if (!$order->getId()) {
            $this->_getSession()->addError(Mage::helper('extensa_econt')->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);
        return $order;
    }

    public function indexAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $response = false;
                $post = $this->getRequest()->getPost($this->getRequest()->getPost('html_id'));
                $econtOrder = Mage::getModel('extensa_econt/order')->load($post['order_id']);

                if ($econtOrder->getId()) {
                    $data = unserialize($econtOrder->getData('data'));
                    $data['system']['validate'] = 0;
                    $data['system']['only_calculate'] = 0;

                    if ($post['shipping_to'] == 'OFFICE') {
                        $receiver_office_code = $post['office_code'];
                        $city = Mage::getModel('extensa_econt/city')->load($post['office_city_id']);
                        $post['postcode'] = $city->getPostCode();
                        $post['city'] = (Mage::helper('extensa_econt')->getLanguage() == 'bg_BG' ? $city->getName() : $city->getNameEn());
                        $post['quarter'] = '';
                        $post['street'] = '';
                        $post['street_num'] = '';
                        $post['other'] = '';
                    } elseif ($post['shipping_to'] == 'APS') {
                        $receiver_office_code = $post['office_aps_code'];
                        $city_aps = Mage::getModel('extensa_econt/city')->load($post['office_city_aps_id']);
                        $post['postcode'] = $city_aps->getPostCode();
                        $post['city'] = (Mage::helper('extensa_econt')->getLanguage() == 'bg_BG' ? $city_aps->getName() : $city_aps->getNameEn());
                        $post['quarter'] = '';
                        $post['street'] = '';
                        $post['street_num'] = '';
                        $post['other'] = '';
                    } else {
                        $receiver_office_code = '';
                    }

                    $data['loadings']['row']['receiver']['office_code'] = $receiver_office_code;

                    $data['loadings']['row']['receiver']['city'] = $post['city'];
                    $data['loadings']['row']['receiver']['post_code'] = $post['postcode'];
                    $data['loadings']['row']['receiver']['quarter'] = $post['quarter'];
                    $data['loadings']['row']['receiver']['street'] = $post['street'];
                    $data['loadings']['row']['receiver']['street_num'] = $post['street_num'];
                    $data['loadings']['row']['receiver']['street_other'] = $post['other'];

                    $addresses = unserialize(Mage::helper('extensa_econt')->getStoreConfig('address'));

                    if (isset($addresses[$post['address_id']])) {
                        $address = $addresses[$post['address_id']];

                        $data['loadings']['row']['sender']['city'] = $address['city'];
                        $data['loadings']['row']['sender']['post_code'] = $address['post_code'];
                        $data['loadings']['row']['sender']['quarter'] = $address['quarter'];
                        $data['loadings']['row']['sender']['street'] = $address['street'];
                        $data['loadings']['row']['sender']['street_num'] = $address['street_num'];
                        $data['loadings']['row']['sender']['street_other'] = $address['other'];

                        $sender_office_code = '';

                        if (Mage::helper('extensa_econt')->getStoreConfig('shipping_from') == 'OFFICE') {
                            $sender_office_code = Mage::helper('extensa_econt')->getStoreConfig('office_code');
                        } elseif (Mage::helper('extensa_econt')->getStoreConfig('shipping_from') == 'APS') {
                            $sender_office_code = Mage::helper('extensa_econt')->getStoreConfig('office_aps_code');
                        }

                        $data['loadings']['row']['sender']['office_code'] = $sender_office_code;
                    }

                    if (Mage::helper('extensa_econt')->getStoreConfig('shipping_from') != 'APS' && $post['sms']) {
                        $sms_no = $post['sms_no'];
                    } else {
                        $sms_no = '';
                    }

                    $data['loadings']['row']['receiver']['sms_no'] = $sms_no;

                    $weight = 0;
                    $description = array();
                    $product_count = 0;
                    $allItems = $order->getAllItems();
                    $currencyRate = Mage::app()->getStore()->getBaseCurrency()->getRate(Mage::getModel('directory/currency')->load(Mage::helper('extensa_econt')->getStoreConfig('currency')));
                    $total = $order->getBaseGrandTotal() - $order->getBaseShippingAmount();
                    if ($total > 0) {
                        $total = round(($total * $currencyRate), 2);
                    } else {
                        $total = 0;
                    }

                    $productsWeight = array();
                    foreach ($allItems as $item) {
                        $product = Mage::getModel('catalog/product')->load($item->getProductId());
                        if ($product->getId()) {
                            $productsWeight[$product->getId()] = (float)$product->getWeight();
                        }
                    }

                    foreach ($allItems as $item) {
                        $single = true;
                        if (!$item->getParentItemId()) {
                            $description[] = $item->getName();
                            $product_count += $item->getQtyOrdered();

                            foreach ($allItems as $item2) {
                                if ($item2->getParentItemId() == $item->getItemId()) {
                                    $single = false;
                                    break;
                                }
                            }
                        }

                        if ($single) {
                            if (empty($productsWeight[$item->getProductId()])) {
                                Mage::throwException(Mage::helper('extensa_econt')->__('Моля, попълнете тегло за всички продукти!'));
                            } else {
                                $weight += ((isset($productsWeight[$item->getProductId()]) ? $productsWeight[$item->getProductId()] : 0) * $item->getQtyOrdered());
                            }
                        }
                    }

                    $data['loadings']['row']['shipment']['description'] = implode(', ', $description);
                    $data['loadings']['row']['shipment']['weight'] = $weight;

                    if ($order->getPayment()->getMethodInstance()->getCode() == 'extensa_econt') {
                        $cd_type = 'GET';
                        $cd_value = $total;
                        $cd_currency = Mage::helper('extensa_econt')->getStoreConfig('currency');

                        if (Mage::helper('extensa_econt')->getStoreConfigFlag('cd_agreement')) {
                            $cd_agreement_num = Mage::helper('extensa_econt')->getStoreConfig('cd_agreement_num');
                        } else {
                            $cd_agreement_num = '';
                        }
                    } else {
                        $cd_type = '';
                        $cd_value = '';
                        $cd_currency = '';
                        $cd_agreement_num = '';
                    }

                    $data['loadings']['row']['services']['cd'] = array('type' => $cd_type, 'value' => $cd_value);
                    $data['loadings']['row']['services']['cd_currency'] = $cd_currency;
                    $data['loadings']['row']['services']['cd_agreement_num'] = $cd_agreement_num;

                    $data['loadings']['row']['payment']['side'] = Mage::helper('extensa_econt')->getStoreConfig('side');
                    $data['loadings']['row']['payment']['method'] = Mage::helper('extensa_econt')->getStoreConfig('payment_method');

                    $receiver_share_sum_door = '';
                    $receiver_share_sum_office = '';
                    $receiver_share_sum_aps = '';

                    if ((float)Mage::helper('extensa_econt')->getStoreConfig('total_for_free') && ($total >= Mage::helper('extensa_econt')->getStoreConfig('total_for_free')) || (float)Mage::helper('extensa_econt')->getStoreConfig('weight_for_free') && ($weight >= Mage::helper('extensa_econt')->getStoreConfig('weight_for_free')) || (int)Mage::helper('extensa_econt')->getStoreConfig('count_for_free') && ($product_count >= Mage::helper('extensa_econt')->getStoreConfig('count_for_free'))) {
                        $data['loadings']['row']['payment']['side'] = 'SENDER';
                    } elseif (Mage::helper('extensa_econt')->getStoreConfig('shipping_payment')) {
                        $shipping_payments = unserialize(Mage::helper('extensa_econt')->getStoreConfig('shipping_payment'));
                        $order_amount = 0;

                        foreach ($shipping_payments as $shipping_payment) {
                            if ($total >= $shipping_payment['order_amount'] && $shipping_payment['order_amount'] >= $order_amount) {
                                $order_amount = $shipping_payment['order_amount'];
                                $receiver_share_sum_door = $shipping_payment['receiver_amount'];
                                $receiver_share_sum_office = $shipping_payment['receiver_amount_office'];
                                $receiver_share_sum_aps = $shipping_payment['receiver_amount_office'];
                            }
                        }
                    }

                    if ($post['shipping_to'] == 'OFFICE') {
                        $receiver_share_sum = $receiver_share_sum_office;
                    } elseif ($post['shipping_to'] == 'APS') {
                        $receiver_share_sum = $receiver_share_sum_aps;
                    } else {
                        $receiver_share_sum = $receiver_share_sum_door;
                    }

                    if ($receiver_share_sum) {
                        $data['loadings']['row']['payment']['side'] = 'SENDER';
                    }

                    $data['loadings']['row']['payment']['receiver_share_sum'] = $receiver_share_sum;
                    $data['loadings']['row']['payment']['share_percent'] = '';

                    if ($order->getPayment()->getMethodInstance()->getCode() != 'extensa_econt' && (float)$order->getShippingAmount() > 0) {
                        $data['loadings']['row']['payment']['side'] = 'SENDER';
                        $data['loadings']['row']['payment']['receiver_share_sum'] = '';
                        $receiver_share_sum = '';
                    }

                    if ($data['loadings']['row']['payment']['side'] == 'RECEIVER') {
                        $data['loadings']['row']['payment']['method'] = 'CASH';
                    }

                    if ($data['loadings']['row']['payment']['method'] == 'CREDIT') {
                        $key_word = Mage::helper('extensa_econt')->getStoreConfig('key_word');
                    } else {
                        $key_word = '';
                    }

                    $data['loadings']['row']['payment']['key_word'] = $key_word;

                    if (Mage::helper('extensa_econt')->getStoreConfig('shipping_from') != 'APS' && Mage::helper('extensa_econt')->getStoreConfigFlag('oc') && ($total >= Mage::helper('extensa_econt')->getStoreConfig('total_for_oc'))) {
                        $oc = $total;
                        $oc_currency = Mage::helper('extensa_econt')->getStoreConfig('currency');
                    } else {
                        $oc = '';
                        $oc_currency = '';
                    }

                    $data['loadings']['row']['services']['oc'] = $oc;
                    $data['loadings']['row']['services']['oc_currency'] = $oc_currency;

                    if ($post['shipping_to'] != 'APS') {
                        $tariff_sub_code_suffix = $post['shipping_to'];
                    } else {
                        $tariff_sub_code_suffix = 'OFFICE';
                    }

                    if (Mage::helper('extensa_econt')->getStoreConfig('shipping_from') != 'APS') {
                        $tariff_sub_code_prefix = Mage::helper('extensa_econt')->getStoreConfig('shipping_from');
                    } else {
                        $tariff_sub_code_prefix = 'OFFICE';
                    }

                    $tariff_sub_code = $tariff_sub_code_prefix . '_' . $tariff_sub_code_suffix;

                    $tariff_code = 0;

                    if (isset($post['express_city_courier_cb']) && $post['shipping_to'] == 'DOOR') {
                        $tariff_code = 1;
                    } elseif ($tariff_sub_code == 'OFFICE_OFFICE') {
                        $tariff_code = 2;
                    } elseif ($tariff_sub_code == 'OFFICE_DOOR' || $tariff_sub_code == 'DOOR_OFFICE') {
                        $tariff_code = 3;
                    } elseif ($tariff_sub_code == 'DOOR_DOOR') {
                        $tariff_code = 4;
                    }

                    $data['loadings']['row']['shipment']['tariff_code'] = $tariff_code;
                    $data['loadings']['row']['shipment']['tariff_sub_code'] = $tariff_sub_code;

                    if ($data['loadings']['row']['shipment']['weight'] >= 50) {
                        $data['loadings']['row']['shipment']['shipment_type'] = 'CARGO';
                        $data['loadings']['row']['shipment']['cargo_code'] = 81;
                    } elseif ($data['loadings']['row']['shipment']['weight'] <= 20 && $tariff_sub_code == 'OFFICE_OFFICE') {
                        $data['loadings']['row']['shipment']['shipment_type'] = 'POST_PACK';
                    } else {
                        $data['loadings']['row']['shipment']['shipment_type'] = 'PACK';
                    }

                    if ($post['shipping_to'] == 'APS') {
                        if ($weight < 5) {
                            $data['loadings']['row']['shipment']['aps_box_size'] = 'Small';
                        } else if ($weight < 10) {
                            $data['loadings']['row']['shipment']['aps_box_size'] = 'Medium';
                        } else {
                            $data['loadings']['row']['shipment']['aps_box_size'] = 'Large';
                        }
                    } else {
                        if (isset($data['loadings']['row']['shipment']['aps_box_size'])) {
                            unset($data['loadings']['row']['shipment']['aps_box_size']);
                        }
                    }

                    if (Mage::helper('extensa_econt')->getStoreConfig('shipping_from') != 'APS' && $post['invoice_before_cd']) {
                        $invoice_before_cd = (int)$post['invoice_before_cd'];
                    } else {
                        $invoice_before_cd = 0;
                    }

                    $data['loadings']['row']['shipment']['invoice_before_pay_CD'] = $invoice_before_cd;

                    if (isset($post['pay_after_accept'])) {
                        $pay_after_accept = (int)$post['pay_after_accept'];
                    } else {
                        $pay_after_accept = 0;
                    }

                    $data['loadings']['row']['shipment']['pay_after_accept'] = $pay_after_accept;

                    if (isset($post['pay_after_test'])) {
                        $pay_after_test = (int)$post['pay_after_test'];
                    } else {
                        $pay_after_test = 0;
                    }

                    $data['loadings']['row']['shipment']['pay_after_test'] = $pay_after_test;

                    if (Mage::helper('extensa_econt')->getStoreConfig('shipping_from') != 'APS' && isset($post['delivery_day_cb']) && isset($post['delivery_day_id'])) {
                        $delivery_day = $post['delivery_day_id'];
                    } else {
                        $delivery_day = '';
                    }

                    $data['loadings']['row']['shipment']['delivery_day'] = $delivery_day;

                    if (Mage::helper('extensa_econt')->getStoreConfig('shipping_from') == 'APS' || $post['shipping_to'] == 'APS') {
                        $data['loadings']['row']['shipment']['pack_count'] = 1;
                    } else {
                        $data['loadings']['row']['shipment']['pack_count'] = (int)$post['pack_count'];
                    }

                    if (isset($post['priority_time_cb']) && $post['shipping_to'] == 'DOOR') {
                        $priority_time_type = $post['priority_time_type_id'];
                        $priority_time_value = $post['priority_time_hour_id'];
                    } else {
                        $priority_time_type = '';
                        $priority_time_value = '';
                    }

                    $data['loadings']['row']['services']['p'] = array('type' => $priority_time_type, 'value' => $priority_time_value);

                    $city_courier_e1 = '';
                    $city_courier_e2 = '';
                    $city_courier_e3 = '';

                    if (isset($post['express_city_courier_cb']) && $post['shipping_to'] == 'DOOR') {
                        if ($post['express_city_courier_e'] == 'e1') {
                            $city_courier_e1 = 'ON';
                        } elseif ($post['express_city_courier_e'] == 'e2') {
                            $city_courier_e2 = 'ON';
                        } elseif ($post['express_city_courier_e'] == 'e3') {
                            $city_courier_e3 = 'ON';
                        }
                    }

                    $data['loadings']['row']['services']['e1'] = $city_courier_e1;
                    $data['loadings']['row']['services']['e2'] = $city_courier_e2;
                    $data['loadings']['row']['services']['e3'] = $city_courier_e3;

                    if ($post['dc'] && !$post['dc_cp']) {
                        $dc = 'ON';
                    } else {
                        $dc = '';
                    }

                    $data['loadings']['row']['services']['dc'] = $dc;

                    if ($post['dc_cp']) {
                        $dc_cp = 'ON';
                    } else {
                        $dc_cp = '';
                    }

                    $data['loadings']['row']['services']['dc_cp'] = $dc_cp;

                    if (Mage::helper('extensa_econt')->getStoreConfig('shipping_from') != 'APS' && $post['products_count'] > 1 && $post['partial_delivery']) {
                        $data['loadings']['row']['packing_list']['partial_delivery'] = $post['partial_delivery_instruction'];
                    }

                    if (Mage::helper('extensa_econt')->getStoreConfig('shipping_from') != 'APS' && $post['inventory']) {
                        $data['loadings']['row']['packing_list']['type'] = $post['inventory_type'];

                        if ($post['inventory_type'] == 'DIGITAL') {
                            foreach ($post['products'] as $product) {
                                $data['loadings']['row']['packing_list']['row'][]['e'] = array(
                                    'inventory_num' => $product['product_id'],
                                    'description'   => $product['name'],
                                    'weight'        => $product['weight'],
                                    'price'         => $product['price']
                                );
                            }
                        }
                    }

                    if ($post['instruction']) {
                        foreach ($post['instructions'] as $type => $instruction) {
                            if ($instruction != '') {
                                $data['loadings']['row']['instructions'][]['e'] = array(
                                    'type'     => $type,
                                    'template' => $instruction
                                );
                            }
                        }
                    }

                    $results = Mage::helper('extensa_econt')->parcelImport($data);

                    if ($results) {
                        if (!empty($results->result->e->error)) {
                            Mage::throwException((string)$results->result->e->error);
                        } elseif (isset($results->result->e->loading_price->total)) {
                            $loading_data = array(
                                'order_id'    => $order->getId(),
                                'loading_id'  => $results->result->e->loading_id,
                                'loading_num' => $results->result->e->loading_num,
                                'pdf_url'     => $results->result->e->pdf_url
                            );

                            if (isset($results->pdf)) {
                                $loading_data['blank_yes'] = $results->pdf->blank_yes;
                                $loading_data['blank_no'] = $results->pdf->blank_no;
                            } else {
                                $loading_data['blank_yes'] = '';
                                $loading_data['blank_no'] = '';
                            }

                            Mage::getModel('extensa_econt/loading')->addData($loading_data)->save();

                            $currencyShippingRate = Mage::app()->getStore()->getBaseCurrency()->getRate(Mage::getModel('directory/currency')->load(Mage::helper('extensa_econt')->getStoreConfig('currency')));
                            $currencyOrderRate = Mage::app()->getStore()->getBaseCurrency()->getRate(Mage::getModel('directory/currency')->load($order->getOrderCurrencyCode()));
                            $currencyBaseRate = Mage::app()->getStore()->getBaseCurrency()->getRate(Mage::getModel('directory/currency')->load($order->getBaseCurrencyCode()));

                            if ((float)Mage::helper('extensa_econt')->getStoreConfig('total_for_free') && ($total >= Mage::helper('extensa_econt')->getStoreConfig('total_for_free')) || (float)Mage::helper('extensa_econt')->getStoreConfig('weight_for_free') && ($weight >= Mage::helper('extensa_econt')->getStoreConfig('weight_for_free')) || (int)Mage::helper('extensa_econt')->getStoreConfig('count_for_free') && ($product_count >= Mage::helper('extensa_econt')->getStoreConfig('count_for_free'))) {
                                $shippingPrice = 0;
                                $baseShippingPrice = 0;
                            } elseif ($receiver_share_sum) {
                                $shippingPrice = (float)$receiver_share_sum * ($currencyOrderRate / $currencyShippingRate);
                                $baseShippingPrice = (float)$receiver_share_sum * ($currencyBaseRate / $currencyShippingRate);
                            } else {
                                $shippingPrice = (float)$results->result->e->loading_price->total * ($currencyOrderRate / $currencyShippingRate);
                                $baseShippingPrice = (float)$results->result->e->loading_price->total * ($currencyBaseRate / $currencyShippingRate);
                            }

                            $order->setGrandTotal($order->getGrandTotal() - $order->getShippingAmount() + $shippingPrice);
                            $order->setBaseGrandTotal($order->getBaseGrandTotal() - $order->getBaseShippingAmount() + $baseShippingPrice);

                            $order->setShippingAmount($shippingPrice);
                            $order->setShippingInclTax($shippingPrice);
                            $order->setShippingInvoiced($shippingPrice);

                            $order->setBaseShippingAmount($baseShippingPrice);
                            $order->setBaseShippingInclTax($baseShippingPrice);
                            $order->setBaseShippingInvoiced($baseShippingPrice);

                            $comment = Mage::helper('extensa_econt')->__('Shipping & Handling Tax') . ': ' . $order->getShippingAmount() . "\n" . Mage::helper('extensa_econt')->__('Grand Total') . ': ' . $order->getGrandTotal();

                            $order->setState(Mage::helper('extensa_econt')->getStoreConfig('order_status'), true, $comment, true);
                            $order->save();
                            $order->sendOrderUpdateEmail(true, $comment);
                        }
                    } else {
                        Mage::throwException(Mage::helper('extensa_econt')->__('Възникна грешка при връзката с Еконт! Моля, опитайте отново!'));
                    }

                    //$this->_getSession()->addSuccess(Mage::helper('extensa_econt')->__('Успешно генерирахте товарителница!'));

                    $this->getResponse()->setBody(
                        $this->getLayout()->createBlock('extensa_econt/adminhtml_sales_order_view_tab_loading')->toHtml()
                    );
                } else {
                    Mage::throwException(Mage::helper('extensa_econt')->__('This order no longer exists.'));
                }
            }
            catch (Mage_Core_Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => $e->getMessage(),
                );
            }
            catch (Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => Mage::helper('extensa_econt')->__('Не може да се генерира товарителница.')
                );
            }

            if (is_array($response)) {
                $response = Mage::helper('core')->jsonEncode($response);
                $this->getResponse()->setBody($response);
            }
        }
    }
}
