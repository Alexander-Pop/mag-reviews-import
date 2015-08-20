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

    const ENTITY = 'katai_ekomi_coupon';
    const EMAIL_EVENT_NAME_NEW_COUPON = 'katai_ekomi_new_coupon';

    /** @var Mage_Core_Model_Store */
    protected $_store = null;

    protected $_enableLogging = false;

    /** @var Mage_Cron_Model_Schedule */
    protected $_schedule = null;

    protected $_salesRule = null;
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
        $storeCode = (string)$jobConfig->store_code;

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
        return $this->_store != null && $this->getHelper()->isEnabled($this->_store->getId());
    }

    /**
     * Run the schedule
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @return $this
     */
    public function run($schedule)
    {
        if (!$this->init($schedule)->canRun()) {
            return $this;
        }

        $orderReviews = $this->getHelper()->getCouponDataParser(true, $this->_store->getId())->fetch();

        if (count($orderReviews) == 0) {
            return $this;
        }

        $yesterday = time() - (1 * 24 * 60 * 60);
        $orders = [];
        foreach ($orderReviews as $data) {
            if ($data->getTimestamp() < $yesterday) continue;

            $orders[$data->getOrderId()] = $data;
        }

        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($this->_store->getId());

        foreach (array_chunk($orders, 100) as $_idx => $orderChunk) {
            $this->_processCoupons($orderChunk, $_idx);
        }

        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        return $this;
    }

    /**
     * @param $orderData
     * @param $chunkId
     */
    protected function _processCoupons($orderData, $chunkId)
    {
        /** @var Mage_Sales_Model_Resource_Order_Collection $products */
        $orders = Mage::getModel('sales/order')
            ->getCollection()
            ->addFieldToFilter('increment_id', array_keys($orderData));
        $this->log((String)$products->getSelect()->assemble(), Zend_Log::DEBUG);


        // Get the destination email addresses to send copies to
        $copyTo = $this->getHelper()->getEmailCopyTo($this->getStoreId());
        $copyMethod = $this->getHelper()->getEmailCopyMethod($this->getStoreId());


        /** @var Mage_Sales_Model_Order $order */
        foreach ($orders as $order) {

            /** @var $mailer Mage_Core_Model_Email_Template_Mailer */
            $mailer = Mage::getModel('core/email_template_mailer');
            /** @var $emailInfo Mage_Core_Model_Email_Info */
            $emailInfo = Mage::getModel('core/email_info');
            $emailInfo->addTo($order->getCustomerEmail(), $order->getCustomerName());

            if ($copyTo && $copyMethod == 'bcc') {
                // Add bcc to customer email
                foreach ($copyTo as $email) {
                    $emailInfo->addBcc($email);
                }
            }
            $mailer->addEmailInfo($emailInfo);

            // Email copies are sent as separated emails if their copy method is 'copy'
            if ($copyTo && $copyMethod == 'copy') {
                foreach ($copyTo as $email) {
                    $emailInfo = Mage::getModel('core/email_info');
                    $emailInfo->addTo($email);
                    $mailer->addEmailInfo($emailInfo);
                }
            }

            $coupon = $this->getSalesRule()->acquireCoupon()->getCode();
            $this->log(sprintf('%s sent to %s for order %s', $coupon, $order->getCustomerEmail(), $order->getIncrementId()), Zend_Log::INFO);

            // Set all required params and send emails
            $mailer->setSender($this->getHelper()->getEmailIdentity($this->getStoreId()));
            $mailer->setStoreId($this->getStoreId());
            $mailer->setTemplateId($this->getHelper()->getEmailTemplate($this->getStoreId()));
            $mailer->setTemplateParams(array(
                'order' => $order,
                'coupon_code' => $coupon
            ));

            /** @var $emailQueue Mage_Core_Model_Email_Queue */
            $emailQueue = Mage::getModel('core/email_queue');
            $emailQueue->setEntityId($this->getId())
                ->setEntityType(self::ENTITY)
                ->setEventType(self::EMAIL_EVENT_NAME_NEW_COUPON)
                ->setIsForceCheck(false);

            $mailer->setQueue($emailQueue)->send();
        }
    }

    /**
     * @return Mage_SalesRule_Model_Rule
     */
    protected function getSalesRule()
    {
        if ( null === $this->_salesRule ) {
            $this->_salesRule = Mage::getSingleton('salesrule/rule')->load($this->getHelper()->getSalesRuleId($this->getStoreId()));
        }
        return $this->_salesRule;
    }

    /**
     * @return Katai_Ekomi_Helper_Coupon
     */
    public function getHelper()
    {
        return Mage::helper('katai_ekomi/coupon');
    }

    /**
     * @param $message
     * @param int $level Zend_Log debugging level
     * @return $this
     */
    protected function log($message, $level = Zend_Log::DEBUG)
    {
        if (true === $this->_enableLogging) {
            Mage::log($message, $level, 'cron_' . $this->_schedule->getJobCode() . '.log');
        }
        return $this;
    }

    /**
     * easy use wrapper
     *
     * @return int
     */
    protected function getStoreId()
    {
        return $this->_store->getId();
    }
}