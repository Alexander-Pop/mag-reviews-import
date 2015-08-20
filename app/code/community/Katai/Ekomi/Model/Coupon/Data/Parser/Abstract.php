<?php
/**
 * File: Abstract.php
 *
 * User: Mitch Vanderlinden
 * Email: mangeot@mitchvdl.be
 * Date: 18.08.15
 * Time: 16:00
 * Package: Katai_Ekomi
 */

abstract class Katai_Ekomi_Model_Coupon_Data_Parser_Abstract extends Katai_Ekomi_Model_Ekomi_Data_Parser_Abstract
{

    /**
     * Return parse URL
     * @return string
     */
    public function getUrl()
    {
        /** @var Katai_Ekomi_Helper_Data $helper */
        $helper = Mage::helper('katai_ekomi');

        return $helper->getInterfaceScheme($this->storeId) . '://' . $this->getHost() . '?' . http_build_query([
            'interface_id' => $helper->getInterfaceId($this->storeId),
            'interface_pw' => $helper->getInterfacePass($this->storeId),
            'type' => 'csv',
            'charset' => 'utf-8',
        ]);
    }

    /**
     * Retrieve the domain url for the parser
     * @return String
     */
    public function getHost()
    {
        return Mage::helper('katai_ekomi')->getProductFeedbackHost();
    }
}