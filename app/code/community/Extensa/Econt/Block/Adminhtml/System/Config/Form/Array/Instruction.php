<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Block_Adminhtml_System_Config_Form_Array_Instruction extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Retrieve Element HTML fragment
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        $html  = '<div class="grid" id="' . $element->getHtmlId() . '">';
        $html .= '  <table cellpadding="0" cellspacing="0" class="border">';
        $html .= '      <tbody>';
        $html .= '          <tr class="headings">';
        $html .= '              <th>' . Mage::helper('extensa_econt')->__('Tип') . '</th>';
        $html .= '              <th>' . Mage::helper('extensa_econt')->__('Наименование') . '</th>';
        $html .= '              <th>' . Mage::helper('extensa_econt')->__('Списък с указания') . '</th>';
        $html .= '          </tr>';
        $instructions_types = Mage::getModel('extensa_econt/system_config_source_instruction')->toOptionArray();
        foreach ($instructions_types as $instructions_type) {
        $html .= '          <tr>';
        $html .= '              <td>' . $instructions_type['label'] . '</td>';
        $html .= '              <td><input type="text" id="' . $this->getElement()->getHtmlId() . '_' . $instructions_type['value'] . '" name="' . $this->getElement()->getName() . '[text][' . $instructions_type['value'] . ']" value="' . $this->_getValue('text/' . $instructions_type['value']) . '" class="input-text" ' . $this->_getDisabled() . '/></td>';
        $html .= '              <td>';
        $html .= '                  <select id="' . $this->getElement()->getHtmlId() . '_id_' . $instructions_type['value'] . '" name="' . $this->getElement()->getName() . '[select][' . $instructions_type['value'] . ']" class="select" onchange="extensa_econt_fill_instructions(\'' . $instructions_type['value'] . '\');"' . $this->_getDisabled() . '>';
        $html .= '                      <option value="">' . Mage::helper('extensa_econt')->__('--Кликнете Вземете указания--') . '</option>';
        $instructions_types_all = $this->_getValue('all/' . $instructions_type['value']);
            if (isset($instructions_types_all)) {
                foreach ($instructions_types_all as $instructions_id) {
        $html .= '                      <option value="' . $instructions_id . '" ' . $this->_getSelected('text/' . $instructions_type['value'], $instructions_id) . '>' . $instructions_id . '</option>';
                }
            }
        $html .= '                  </select>';
            if (isset($instructions_types_all)) {
                foreach ($instructions_types_all as $instructions_id) {
        $html .= '                  <input type="hidden" name="' . $this->getElement()->getName() . '[all][' . $instructions_type['value'] . '][]" value="' . $instructions_id . '" />';
                }
            }
        $html .= '              </td>';
        $html .= '          </tr>';
        }
        $html .= '      </tbody>';
        $html .= '  </table>';
        $html .= '  <p class="note" style="width: 100%;"><span>' . Mage::helper('extensa_econt')->__('Ако не намирате дадено указание в списъка с указания, моля кликнете Вземете указания.') . '</span></p>';
        $html .= '</div>';

        return $html;
    }

    protected function _getValue($key)
    {
        return $this->getElement()->getData('value/' . $key);
    }

    protected function _getSelected($key, $value)
    {
        return $this->getElement()->getData('value/' . $key) == $value ? 'selected="selected"' : '';
    }

    protected function _getDisabled()
    {
        return $this->getElement()->getDisabled() ? ' disabled' : '';
    }
}
