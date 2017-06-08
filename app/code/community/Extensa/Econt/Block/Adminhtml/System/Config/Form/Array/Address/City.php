<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Block_Adminhtml_System_Config_Form_Array_Address_City extends Mage_Core_Block_Abstract
{
    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        $inputName = $this->getInputName();
        $columnName = $this->getColumnName();
        $column = $this->getColumn();
        $inputId = 'carriers_extensa_econt_address_' . $columnName . '#{_id}';

        $html  = '<input id="' . $inputId . '" type="text" name="' . $inputName . '" value="#{' . $columnName . '}" ' .
            ($column['size'] ? 'size="' . $column['size'] . '"' : '') . ' class="' .
            (isset($column['class']) ? $column['class'] . ' ' : '') . '"'.
            (isset($column['style']) ? ' style="'.$column['style'] . '"' : '') . '/>';
        $html .= '<input id="carriers_extensa_econt_address_' . $columnName . '_id#{_id}" type="hidden" name="' . str_replace($columnName, $columnName . '_id', $inputName) . '" value="#{' . $columnName . '_id}" />';
        $html .= '<span id="' . $inputId . '_indicator" class="autocomplete-indicator" style="display: none; position: absolute; margin-left: -17px;">';
        $html .= '  <img src="' . $this->getSkinUrl('images/ajax-loader.gif') . '" alt="' . Mage::helper('extensa_econt')->__('Loading...') . '" class="v-middle" />';
        $html .= '</span>';
        $html .= '<div id="' . $inputId . '_autocomplete" class="autocomplete" style="display: none;"></div>';

        return $html;
    }
}
