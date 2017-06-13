<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 
 *
 * @author killer
 */
class Speedy_Speedyshipping_Block_Adminhtml_Requestcourier_Grid extends
Mage_Adminhtml_Block_Widget_Grid {

    //put your code here

    public function __construct() {
        parent::__construct();
        $this->setId('BolGrid');
        $this->setDefaultSort('bol_id');
        $this->setDefaultDir('DESC');
        // $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {

        $dateInfo = getdate(time());

        $filter = $this->getParam('filter');
        $filter_data = Mage::helper('adminhtml')->prepareFilterString($filter);

        $skipDefaultFilter = false;

        if (array_key_exists('bol_takingdate', $filter_data)) {
            if (is_array($filter_data['bol_takingdate'])) {
                if (array_key_exists('from', $filter_data['bol_takingdate']) ||
                        array_key_exists('to', $filter_data['bol_takingdate'])) {
                    $skipDefaultFilter = TRUE;
                }
            }
        }

        if (array_key_exists('bol_id', $filter_data)) {
            $skipDefaultFilter = TRUE;
        }

        $collection = Mage::getModel('speedyshippingmodule/saveorder')->getCollection();

        

        /*
          if (!$skipDefaultFilter) {
          $collection->addFieldToFilter('bol_created_day', $dateInfo['mday']);
          $collection->addFieldToFilter('bol_created_month', $dateInfo['mon']);
          $collection->addFieldToFilter('bol_created_year', $dateInfo['year']);
          }
         * 
         */
        $collection->addFieldToFilter('bol_id', array("notnull" => true));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {



        $this->addColumn('bol_id', array(
            'header' => $this->__("View Bill of lading number"),
            'align' => 'center',
            'width' => '10px',
            'index' => 'bol_id',
            'type' => 'number'
        ));

        $this->addColumn('bol_created_at', array(
            'header' => $this->__("Taking time courier"),
            'align' => 'center',
            'index' => 'bol_created_at',
            'width' => '50px',
            'type' => 'date',
            'filter' => false,
            'renderer' => 'speedyshippingmodule/adminhtml_requestcourier_renderer_created'
        ));

        $this->addColumn('bol_takingdate', array(
            'header' => $this->__("Time and creation date"),
            'align' => 'center',
            'index' => 'bol_datetime',
            'type' => 'datetime',
            'width' => '50px',
            'renderer' => 'speedyshippingmodule/adminhtml_requestcourier_renderer_datecreated'
        ));

        $this->addColumn('image', array(
            'header' => '',
            'align' => 'center',
            'index' => 'image',
            'renderer' => 'speedyshippingmodule/adminhtml_requestcourier_renderer_cancelbutton',
            'filter' => false
        ));

        if (!Mage::getStoreConfig('carriers/speedyshippingmodule/bring_to_office') ||
                !Mage::getStoreConfig('carriers/speedyshippingmodule/choose_office')) {
            $this->addColumn('images', array(
                'header' => '',
                'align' => 'center',
                'index' => 'images',
                'renderer' => 'speedyshippingmodule/adminhtml_requestcourier_renderer_requestbutton',
                'filter' => false
            ));
        }

        $this->addColumn('view_bol', array(
            'header' => '',
            'align' => 'center',
            'index' => 'view_bol',
            'renderer' => 'speedyshippingmodule/adminhtml_requestcourier_renderer_viewbol',
            'filter' => false
        ));



        $this->addColumn('order', array(
            'header' => '',
            'align' => 'center',
            'index' => 'order',
            'renderer' => 'speedyshippingmodule/adminhtml_requestcourier_renderer_vieworder',
            'filter' => false
        ));

        $this->addColumn('view_print_voucher', array(
            'header' => '',
            'align' => 'center',
            'index' => 'view_print_voucher',
            'renderer' => 'speedyshippingmodule/adminhtml_requestcourier_renderer_viewprintvoucher',
            'filter' => false
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {

        if (!Mage::getStoreConfig('carriers/speedyshippingmodule/bring_to_office') ||
                !Mage::getStoreConfig('carriers/speedyshippingmodule/choose_office')) {

            $this->setMassactionIdField('speedy_order_id');
            $this->getMassactionBlock()->setFormFieldName('speedy_order_id');

            $this->getMassactionBlock()->addItem('requestcourier', array(
                'label' => $this->__("Request a courier"),
                'url' => $this->getUrl('*/*/massRequest'),
                'confirm' => $this->__("Are you sure, that you want to make a couriter request")
            ));
        }
        return $this;
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();

        //Remove unneeded buttons
        //$this->unsetChild('search_button');
        // $this->unsetChild('reset_filter_button');
    }

    protected function _filterCategoriesCondition($collection, $column) {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $dateParts = explode('.', $value['orig_from']);
        //UNIX_TIMESTAMP(CONCAT(`bol_created_year`,'-',LPAD(`bol_created_month`,2,'00'),'-',LPAD(`bol_created_day`,2,'00')))
       // $this->getCollection()->addFieldToFilter('categories', array('finset' => $value));
        $this->getCollection()->addExpressionAttributeToSelect('bol_created_at', 
                'UNIX_TIMESTAMP(CONCAT(`bol_created_year`,'-',`bol_created_month`,'-',`bol_created_day`)) as bol_created_at'
                , array('bol_created_day','bol_created_month','bol_created_year'));
        
         // $this->getCollection()->addFieldToFilter('bol_created_day', $dateParts[0]);
          //$this->getCollection()->addFieldToFilter('bol_created_month', $dateParts[1]);
          //$this->getCollection()->addFieldToFilter('bol_created_year', $dateParts[2]);
    }

}

?>
