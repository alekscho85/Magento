<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Block_Adminhtml_System_Config_Form_Array_Address extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $_cityRenderer;
    protected $_quarterRenderer;
    protected $_streetRenderer;

    public function _construct()
    {
        parent::_construct();

        $this->setHtmlId('_' . uniqid());
    }

    protected function _getCityRenderer()
    {
        if (!$this->_cityRenderer) {
            $this->_cityRenderer = $this->getLayout()->createBlock(
                'extensa_econt/adminhtml_system_config_form_array_address_city', '',
                array('is_render_to_js_template' => true)
            );
        }
        return $this->_cityRenderer;
    }

    protected function _getQuarterRenderer()
    {
        if (!$this->_quarterRenderer) {
            $this->_quarterRenderer = $this->getLayout()->createBlock(
                'extensa_econt/adminhtml_system_config_form_array_address_quarter', '',
                array('is_render_to_js_template' => true)
            );
        }
        return $this->_quarterRenderer;
    }

    protected function _getStreetRenderer()
    {
        if (!$this->_streetRenderer) {
            $this->_streetRenderer = $this->getLayout()->createBlock(
                'extensa_econt/adminhtml_system_config_form_array_address_street', '',
                array('is_render_to_js_template' => true)
            );
        }
        return $this->_streetRenderer;
    }

    protected function _prepareToRender()
    {
        $this->addColumn('post_code', array(
            'label'    => Mage::helper('extensa_econt')->__('Пощенски код'),
            'style'    => 'width:50px',
            'class'    => 'input-text disabled',
        ));

        $this->addColumn('city', array(
            'label'    => Mage::helper('extensa_econt')->__('Населено място'),
            'style'    => 'width:80px',
            'class'    => 'input-text carriers_extensa_econt_address_city',
            'renderer' => $this->_getCityRenderer(),
        ));

        $this->addColumn('quarter', array(
            'label'    => Mage::helper('extensa_econt')->__('Квартал'),
            'style'    => 'width:100px',
            'class'    => 'input-text carriers_extensa_econt_address_quarter',
            'renderer' => $this->_getQuarterRenderer(),
        ));

        $this->addColumn('street', array(
            'label'    => Mage::helper('extensa_econt')->__('Улица'),
            'style'    => 'width:100px',
            'class'    => 'input-text carriers_extensa_econt_address_street',
            'renderer' => $this->_getStreetRenderer(),
        ));

        $this->addColumn('street_num', array(
            'label'    => Mage::helper('extensa_econt')->__('Номер'),
            'style'    => 'width:30px',
            'class'    => 'input-text carriers_extensa_econt_address_street_num',
        ));

        $this->addColumn('other', array(
            'label'    => Mage::helper('extensa_econt')->__('Друго'),
            'style'    => 'width:100px',
            'class'    => 'input-text carriers_extensa_econt_address_other',
        ));

        $this->_addAfter = false;
        //$this->_addButtonLabel = Mage::helper('extensa_econt')->__('Добавяне');

        /*$block = $this->getLayout()
                ->createBlock('adminhtml/template')
                ->setTemplate('extensa/econt/system/config/address.phtml');
        $this->getLayout()->getBlock('js')->append($block);*/
    }

    protected function _toHtml()
    {
        return parent::_toHtml() . $this->setTemplate('extensa/econt/system/config/address.phtml')->renderView();
    }
}
