<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_ReturnloadingController extends Mage_Sales_Controller_Abstract
{
    public function indexAction()
    {
        if (!$this->_loadValidOrder()) {
            return;
        }

        $loading = Mage::getModel('extensa_econt/loading')->load($this->getOrder()->getId(), 'order_id');

        $results_data['error'] = false;
        $results_data['success'] = false;

        if ($loading->getId() && strtotime($loading->getReceiverTime()) > 0) {
            $data = array();
            $data['system']['validate'] = 0;
            $data['system']['response_type'] = 'XML';
            $data['system']['only_calculate'] = 0;

            $data['client']['username'] = Mage::helper('extensa_econt')->getStoreConfig('username');
            $data['client']['password'] = Mage::helper('extensa_econt')->getStoreConfig('password');
            $data['client_software'] = 'ExtensaMagento';
            $data['loadings']['row']['returned_loading']['first_loading_num'] = $loading->getLoadingNum();
            $data['loadings']['row']['returned_loading']['first_loading_receiver_phone'] = $loading->getReceiverPersonPhone();

            $results = Mage::helper('extensa_econt')->parcelImport($data);

            if ($results) {
                if (!empty($results->result->e->error)) {
                    $results_data['error'] = true;
                    $results_data['message'] = (string)$results->result->e->error;
                } elseif (isset($results->pdf) && !empty($results->pdf->blank_yes)) {
                    $loading->setIsReturned(true)->setReturnedBlankYes($results->pdf->blank_yes)->save();
                    $results_data['loading_return'] = trim($results->pdf->blank_yes);
                } else {
                    $loading->setIsReturned(true)->save();
                    $results_data['success'] = true;
                    $results_data['message'] = Mage::helper('extensa_econt')->__('Успешно върнахте пратката!');
                }
            } else {
                $results_data['error'] = true;
                $results_data['message'] = Mage::helper('extensa_econt')->__('Възникна грешка при връзката с Еконт! Моля, опитайте отново!');
            }
        } else {
            $results_data['error'] = true;
            $results_data['message'] = Mage::helper('extensa_econt')->__('Пратката не е доставена, затова не може да я върнете!');
        }

        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode($results_data)
        );
    }

    public function getOrder()
    {
        return Mage::registry('current_order');
    }
}
