<?php
/**
 * File: Title.php
 *
 * User: Mitch Vanderlinden
 * Email: magento@mitchvdl.be
 * Date: 14.08.15
 * Time: 17:33
 * Package: Katai_Ekomi
 */

class Katai_Ekomi_Block_System_Config_Source_Ekomi_Title extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $_ratingIdRenderer = null;

    public function _prepareToRender()
    {
        $this->addColumn('rating_id', array(
            'label' => Mage::helper('katai_ekomi')->__('Rating Identifier'),
//            'style' => 'width:100px',
            'renderer' => $this->_getRatingIdRenderer()
        ));
        $this->addColumn('title_label', array(
            'label' => Mage::helper('katai_ekomi')->__('Title Label'),

        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('katai_ekomi')->__('Add');
    }

    protected function  _getRatingIdRenderer()
    {
        if (!$this->_ratingIdRenderer) {
            $this->_ratingIdRenderer = $this->getLayout()->createBlock('katai_ekomi/system_config_adminhtml_form_field_title', '',array('is_render_to_js_template' => true));
        }
        return $this->_ratingIdRenderer;
    }

    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getRatingIdRenderer()
                ->calcOptionHash($row->getData('rating_id')),
            'selected="selected"'
        );
    }
}
