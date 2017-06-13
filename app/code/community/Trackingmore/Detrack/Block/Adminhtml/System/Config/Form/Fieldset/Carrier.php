<?php

class Trackingmore_Detrack_Block_Adminhtml_System_Config_Form_Fieldset_Carrier extends
    Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected $_setFieldValue;
    protected $_sourceData;
    protected $_conceal = '';

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $adminCarrierTpl = $this->_getHeaderHtml($element);
        $carriers = Mage::getModel('detrack/carrier')->getList(true);
        $adminCarrierTpl .= '<tr><td class="label"></td><td>';
        $adminCarrierTpl .= $this->_getAdminHtmlCheckAllButton('$$(\'.tr_carrier\').forEach(function(el){el.writeAttribute(\'checked\', true)});', $this->helper('detrack')->__('Select All'));
        $adminCarrierTpl .= ' ';
        $adminCarrierTpl .= $this->_getAdminHtmlCheckAllButton('$$(\'.tr_carrier\').forEach(function(el){el.writeAttribute(\'checked\', false)});', $this->helper('detrack')->__('Unselect All'));
        $adminCarrierTpl .= '</td></tr>';

        $fields = '';
        foreach ($carriers as $carrier) {
            $fields .= $this->_getCarriersFieldHtml($element, $carrier);
        }
        $adminCarrierTpl .= $fields;
        $adminCarrierTpl .= $this->_getFooterHtml($element);

        return $this->_conceal . $adminCarrierTpl;
    }

    protected function _getConfigFormFieldRender()
    {
        if (empty($this->_setFieldValue)) {
            $this->_setFieldValue = Mage::getBlockSingleton('adminhtml/system_config_form_field');
        }
        return $this->_setFieldValue;
    }

    protected function _getValues()
    {
        if (!$this->_sourceData)
            $this->_sourceData = Mage::getSingleton('adminhtml/system_config_source_enabledisable')->toOptionArray();

        return $this->_sourceData;
    }

    protected function _getCarriersFieldHtml($fieldset, $carrier)
    {
        $name = 'groups[carriers][fields][' . $carrier->getCode() . '][value]';
        $this->_conceal .= '<input type="hidden" name="'. $name .'" value="0">';

        $field = $fieldset->addField($carrier->getCode(), 'checkbox',
            array(
                'name' => $name,
                'label' => $carrier->getName(),
                'class' => 'tr_carrier',
                'value' => 1,
                'checked' => $carrier->getEnabled(),
                'can_use_default_value' => 0,
                'can_use_website_value' => 0,
            ))->setRenderer($this->_getConfigFormFieldRender());

        return $field->toHtml();
    }

    protected function _getAdminHtmlCheckAllButton($action, $label)
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'     => $label,
                'onclick'   => 'javascript:'. $action .'; return false;'
            ));

        return $button->toHtml();
    }
}