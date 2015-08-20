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

    const XPATH_EMAIL_IDENTITY = 'katai_ekomi/coupon/identity';
    const XPATH_EMAIL_TEMPLATE = 'katai_ekomi/coupon/template';
    const XPATH_EMAIL_COPY_TO = 'katai_ekomi/coupon/copy_to';
    const XPATH_EMAIL_COPY_METHOD = 'katai_ekomi/coupon/copy_method';


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

    /**
     * Return email addresses to send a copy to
     * @param int $storeId
     * @return array
     */
    public function getEmailCopyTo($storeId = 0)
    {
        $data = Mage::getStoreConfig(self::XPATH_EMAIL_COPY_TO, $storeId);
        if (!empty($data)) {
            return explode(',', $data);
        }
        return [];
    }

    /**
     * Return the copy method, bcc, cc
     * @param int $storeId
     * @return string
     */
    public function getEmailCopyMethod($storeId = 0)
    {
        return Mage::getStoreConfig(self::XPATH_EMAIL_COPY_METHOD, $storeId);
    }

    /**
     * Get email identity for sender
     * @param int $storeId
     * @return string
     */
    public function getEmailIdentity($storeId = 0)
    {
        return Mage::getStoreConfig(self::XPATH_EMAIL_IDENTITY, $storeId);
    }

    /**
     * Get the correct email template to sent
     *
     * @param int $storeId
     * @return string
     */
    public function getEmailTemplate($storeId = 0)
    {
        return Mage::getStoreConfig(self::XPATH_EMAIL_TEMPLATE, $storeId);
    }
}