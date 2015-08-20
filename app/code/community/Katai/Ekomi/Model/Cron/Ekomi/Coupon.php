<?php
/**
 * File: Coupon.php
 *
 * User: Mitch Vanderlinden
 * Email: magento@mitchvdl.be
 * Date: 14.08.15
 * Time: 16:37
 * Package: katai_ekomi
 */

class katai_ekomi_Model_Cron_Ekomi_Coupon extends Varien_Object
{

    const HOST = 'api.ekomi.de/get_productfeedback.php';

    /** @var Mage_Core_Model_Store */
    protected $_store = null;

    protected $_enableLogging = false;

    /** @var null Mage_Cron_Model_Schedule */
    protected $_schedule = null;


    /**
     * Instantiate the Cron params
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @return $this
     */
    public function init($schedule)
    {
        // Pre-pre run logic
        $jobsRoot = Mage::getConfig()->getNode('crontab/jobs');
        $jobConfig = $jobsRoot->{$schedule->getJobCode()};
        $storeCode = (string) $jobConfig->store_code;

        // This will throw all the exceptions I need when invalid store_code is supplied
        $this->_store = Mage::app()->getStore($storeCode);
        $this->_schedule = $schedule;

        $this->_enableLogging = $this->getHelper()->isLoggingEnabled($this->_store->getId());

        return $this;
    }

    /**
     * Validate is the schedule can be ran
     *
     * @return bool
     */
    public function canRun()
    {
        return $this->_store != null && $this->getHelper()->isProductReviewEnabled($this->_store->getId());
    }

    /**
     * Run the schedule
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @return $this
     */
    public function run($schedule)
    {
        if ( !$this->init($schedule)->canRun() ) {
            return $this;
        }

        $orderReviews = $this->getHelper()->getAdvancedDataParser(true, $this->_store->getId())->fetch();

        return $this;
    }

    /**
     * @param $orders
     * @param $chunkId
     */
    protected function _processCoupons($orders, $chunkId)
    {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $products */
        $products = Mage::getModel('sales/order')
            ->getCollection()
            ->addFieldToFilter('increment_id', array_keys($orders))
        ;
        $this->log((String) $products->getSelect()->assemble(), Zend_Log::DEBUG);

    }

    /**
     * @return Katai_Ekomi_Helper_Data
     */
    public function getHelper()
    {
        return Mage::helper('katai_ekomi');
    }

    /**
     * @param $message
     * @param int $level Zend_Log debugging level
     * @return $this
     */
    protected function log($message, $level = Zend_Log::DEBUG)
    {
        if ( true === $this->_enableLogging ) {
            Mage::log($message, $level, 'cron_' . $this->_schedule->getJobCode() . '.log');
        }
        return $this;
    }

}