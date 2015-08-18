<?php
/**
 * File: Scheme.php
 *
 * User: Mitch Vanderlinden
 * Email: magento@mitchvdl.be
 * Date: 14.08.15
 * Time: 16:37
 * Package: Katai_Ekomi
 */

class Katai_Ekomi_Model_System_Config_Source_Ekomi_Scheme extends Varien_Object
{
    const SCHEME_HTTP = 'http';
    const SCHEME_HTTPS = 'https';

    public function toOptionArray()
    {
        return array(
            array('value' => self::SCHEME_HTTP, 'label'=>Mage::helper('katai_ekomi')->__(self::SCHEME_HTTP)),
            array('value' => self::SCHEME_HTTPS, 'label'=>Mage::helper('katai_ekomi')->__(self::SCHEME_HTTPS)),
        );
    }
}