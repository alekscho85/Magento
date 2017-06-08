<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Block_Adminhtml_System_Config_Form_Checkbox_Disposition extends Mage_Adminhtml_Block_System_Config_Form_Field
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

        $html  = '<ul id="' . $element->getHtmlId() . '" class="checkboxes">';
        $html .= '    <li><input type="checkbox" value="1" name="' . $this->getElement()->getName() . '[pay_after_accept]" id="' . $this->getElement()->getHtmlId() . '_pay_after_accept" ' . $this->_getChecked('pay_after_accept', 1) . ' onclick="$(\'' . $this->getElement()->getHtmlId() . '_pay_after_test\').checked = false;" ' . $this->_getDisabled() . '/>';
        $html .= '        <label for="' . $this->getElement()->getHtmlId() . '_pay_after_accept">' . Mage::helper('extensa_econt')->__('разпореждам пратката да се прегледа от получателя и да плати наложения платеж само ако приеме стоката ми') . '</label></li>';
        $html .= '    <li><input type="checkbox" value="1" name="' . $this->getElement()->getName() . '[pay_after_test]" id="' . $this->getElement()->getHtmlId() . '_pay_after_test" ' . $this->_getChecked('pay_after_test', 1) . ' onclick="$(\'' . $this->getElement()->getHtmlId() . '_pay_after_accept\').checked = false;" ' . $this->_getDisabled() . '/>';
        $html .= '        <label for="' . $this->getElement()->getHtmlId() . '_pay_after_test">' . Mage::helper('extensa_econt')->__('разпореждам пратката да се прегледа и тества от получателя и да плати наложения платеж само ако приеме стоката ми') . '</label></li>';
        $html .= '        <input type="hidden" value="empty" name="' . $this->getElement()->getName() . '[empty]" id="' . $this->getElement()->getHtmlId() . '_empty" /></li>';
        $html .= '</ul>';

        return $html;
    }

    protected function _getChecked($key, $value)
    {
        return $this->getElement()->getData('value/' . $key) == $value ? 'checked="checked"' : '';
    }

    protected function _getDisabled()
    {
        return $this->getElement()->getDisabled() ? ' disabled' : '';
    }
}
