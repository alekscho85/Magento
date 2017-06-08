<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Block_Adminhtml_System_Config_Form_Apsinfo extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $text = '';
        $text .= Mage::helper('extensa_econt')->__('Услугата НП следва да е активна при посочено споразумение за изплащане на НП:');
        $text .= '<br />';
        $text .= Mage::helper('extensa_econt')->__('- обратна разписка;');
        $text .= '<br />';
        $text .= Mage::helper('extensa_econt')->__('- двупосочна пратка;');
        $text .= '<br />';
        $text .= Mage::helper('extensa_econt')->__('- Час за приоритет;');
        $text .= '<br />';
        $text .= Mage::helper('extensa_econt')->__('- Преглед;');
        $text .= '<br />';
        $text .= Mage::helper('extensa_econt')->__('- Преглед и тест;');
        $text .= '<br />';
        $text .= Mage::helper('extensa_econt')->__('- Преглед, тест и избор;');

        $element->setText($text);

        return parent::_getElementHtml($element);
    }
}
