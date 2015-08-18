<?php
/**
 * File: Parser.php
 *
 * User: Mitch Vanderlinden
 * Email: magento@mitchvdl.be
 * Date: 14.08.15
 * Time: 16:37
 * Package: Katai_Ekomi
 */

class Katai_Ekomi_Model_System_Config_Source_Ekomi_Data_Parser extends Varien_Object
{
    const PARSER_CSV = 'CSV';
    const PARSER_SERIALIZED = 'SERIALIZED';

    public function toOptionArray()
    {
        return array(
            array('value' => self::PARSER_CSV, 'label'=>Mage::helper('katai_ekomi')->__(self::PARSER_CSV)),
            array('value' => self::PARSER_SERIALIZED, 'label'=>Mage::helper('katai_ekomi')->__(self::PARSER_SERIALIZED)),
        );
    }
}