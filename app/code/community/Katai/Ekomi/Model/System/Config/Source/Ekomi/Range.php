<?php
/**
 * File: Range.php
 *
 * User: Mitch Vanderlinden
 * Email: magento@mitchvdl.be
 * Date: 14.08.15
 * Time: 16:37
 * Package: Katai_Ekomi
 */

class Katai_Ekomi_Model_System_Config_Source_Ekomi_Range extends Varien_Object
{
    const RANGE_ALL = 'all';

    public function toOptionArray()
    {
        return array(
            array('value' => self::RANGE_ALL, 'label'=>Mage::helper('katai_ekomi')->__(self::RANGE_ALL)),
        );
    }
}