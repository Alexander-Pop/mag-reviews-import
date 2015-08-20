<?php
/**
 * File: Coupon.php
 *
 * User: Mitch Vanderlinden
 * Email: magento@mitchvdl.be
 * Date: 20.08.15
 * Time: 17:53
 * Package: Katai_Ekomi
 */

class Katai_Ekomi_Helper_Coupon extends Mage_Core_Helper_Abstract
{
    const XPATH_IS_ENABLED = 'katai_ekomi/coupon/is_enabled';
    const XPATH_SALES_RULE_ID = 'katai_ekomi/coupon/sales_rule_id';


    /**
     * @param int $storeId
     * @return bool
     */
    public function isEnabled($storeId = 0)
    {
        return Mage::getStoreConfigFlag(self::XPATH_IS_ENABLED, $storeId);
    }

    /**
     * Return sales rule ID
     * @param int $storeId
     * @return int
     */
    public function getSalesRuleId($storeId = 0)
    {
        return (int) Mage::getStoreConfig(self::XPATH_SALES_RULE_ID);
    }
}