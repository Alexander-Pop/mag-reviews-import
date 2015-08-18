<?php
/**
 * File: Title.php
 *
 * User: Mitch Vanderlinden
 * Email: magento@mitchvdl.be
 * Date: 14.08.15
 * Time: 17:37
 * Package: Katai_Ekomi
 */
class Katai_Ekomi_Block_System_Config_Adminhtml_Form_Field_Title extends Mage_Core_Block_Html_Select
{
    public function _toHtml()
    {
        $options = Mage::helper('katai_ekomi')->getRatingOptionDefaultTitles();
        foreach ($options as $value => $label) {
            $this->addOption($value, $label);
        }
        return parent::_toHtml();
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }
}