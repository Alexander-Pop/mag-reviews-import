<?php
/**
 * File: Range.php
 *
 * User: Mitch Vanderlinden
 * Email: magento@mitchvdl.be
 * Date: 14.08.15
 * Time: 16:37
 * Package: katai_ekomi
 */

class katai_ekomi_Model_Cron_Ekomi_Review extends Varien_Object
{

    const HOST = 'api.ekomi.de/get_productfeedback.php';


    protected $map = [
        'timestamp' => 'abgegeben',
        'client_id' => 'shop_kunden_id',
        'sku' => 'produkt_id',
        'rating' => 'bewertung',
        'review' => 'meinung',
    ];


    /** @var Mage_Core_Model_Store */
    protected $_store = null;

    /** @var array will be used to  */
    protected $_cnf = [];


    protected $_defaultNickname = null;
    protected $_ratingOptions = [];
    protected $_ratingOptionTitles = [];
    protected $_defaultRatingIndex = 4; // Rating
    protected $_enableLogging = false;

    /** @var null Mage_Cron_Model_Schedule */
    protected $_schedule = null;

    protected $_totalRatings = 0;


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

        $this->_defaultNickname = $this->getHelper()->getDefaultNickname($this->_store->getId());
        $this->_defaultRatingIndex = $this->getHelper()->applyRatingTo($this->_store->getId());
        $this->_ratingOptionTitles = $this->getHelper()->getRatingOptionTitles($this->_store->getId());

        $this->_ratingOptions = $this->getHelper()->getRatingOptions($this->_defaultRatingIndex, $this->_store->getId());
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
        if ( !$this->init($schedule)->canRun() ) {
            return $this;
        }

        $ratingsData = $this->getHelper()->getAdvancedDataParser(true, $this->_store->getId())->fetch();

        // Aggregate Ratings by product ID
        $importAll = $this->getHelper()->importAllReviews($this->_store->getId());
        $this->getHelper()->disabledImportAllReviews(); // a one-of setting!

        /// (Messy) default data
        $yesterday = time() - (1 * 24 * 60 * 60);
        $ratings = [];
        foreach ( $ratingsData as $data ) {
            // Add only last 24 hours
            if (!$importAll && $data->getTimestamp() < $yesterday) continue;

            $sku = $data->getProductId();
            $ratings[$sku][] = [
                'ratings' => [
                    $this->_defaultRatingIndex => $this->_ratingOptions[$data->getRating()],
                ],
                'nickname' => $this->_defaultNickname,
                'title' => $this->_ratingOptionTitles[$data->getRating()],
                'detail' => str_replace('\n',' ',$data->getReview()),
                'force_save' => '1',
                'created_at' => date('Y-m-d H:i:s', $data->getTimestamp())
            ];
        }

        // Nothing to do
        if ( 0 == count($ratings)) {
            return $this;
        }


        $this->_totalRatings = count($ratings);
        // Run intervals of 100 to not stress the database or crease a MySQL Package error
        foreach ( array_chunk($ratings, 100, true) as $_idx => $rating_sice) {
            $this->_processRatings($rating_sice, $_idx);
        }
        return $this;
    }

    /**
     * @param $ratings
     * @param $chunkId
     */
    protected function _processRatings($ratings, $chunkId)
    {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $products */
        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->addFieldToFilter('sku', array_keys($ratings))
        ;
        $this->log((String) $products->getSelect()->assemble(), Zend_Log::DEBUG);

        /** @var Mage_Catalog_Model_Product $product */
        $i = 0;
        foreach ( $products as $product ) {
            $productId = $product->getId();
            // We have a simple product - find the related configurable    // Overwrite $productId by the correct configurable product ID
            if ( Mage_Catalog_Model_Product_Type::TYPE_SIMPLE == $product->getTypeId()) {
                $productId = $this->getParentProduct($product, true);
            }
            // Save Review: Our $rating array is a 2D array which is grouped by the products SKU therefor a loop is needed to save all related reviews
            foreach ( $ratings[$product->getSku()] as $_i => $rating) {
                $i++;
                $this->saveReview($rating, $productId);
                $this->log(sprintf("Saving Review %d/%d - Product: %s", ($chunkId*100)+$i, $this->_totalRatings, $productId ), Zend_Log::INFO);
            }
        }
    }

    /**
     * @param $data
     * @param Mage_Catalog_Model_Product|int $product
     * @return mixed
     */
    protected function saveReview($data, $product)
    {
        if ( !is_object($product) ) {
            // Mock
            $product = Mage::getModel('catalog/product')->setId($product);
        }
        $rating = array();
        if (isset($data['ratings']) && is_array($data['ratings'])) {
            $rating = $data['ratings'];
        }


        /** @var Mage_Review_Model_Review $review */
        $review = Mage::getModel('review/review')->setData($data);
        $validate = $review->validate();
        if ($validate === true) {
            $review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
                ->setEntityPkValue($product->getId())
                ->setStatusId(Mage_Review_Model_Review::STATUS_APPROVED)
                ->setCustomerId(null)
                ->setStoreId($this->_store->getId())
                ->setStores(array($this->_store->getId()))
                ;
            $review->save();

            foreach ($rating as $ratingId => $optionId) {
                Mage::getModel('rating/rating')
                    ->setRatingId($ratingId)
                    ->setReviewId($review->getId())
                    ->setCustomerId(null)
                    ->addOptionVote($optionId, $product->getId());
            }
            $review->setCreatedAt($data['created_at']);
            return $review->save()->aggregate();
        } else {
            $this->log('Review-Validation failed', Zend_Log::CRIT);
        }
        return $this;
    }

    /**
     * Retrieve the parent product ID for a given product, if none is found the original is returned.
     *
     * @param $product
     * @param bool|true $idOnly WARNING: This causes a product load - do not use inside loops!
     * @return mixed
     */
    protected function getParentProduct($product, $idOnly = true)
    {

        // Waterfall approach
        /** Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Type_Configurable */
        $parents = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
        if (is_array($parents) && count($parents) > 0) {
            return $idOnly ? $parents[0] : Mage::getModel('catalog/product')->load($parents[0]);
        }
        // check bundled
        $parents = Mage::getModel('bundle/product_type')->getParentIdsByChild($product->getId());
        if (is_array($parents) && count($parents) > 0) {
            return $idOnly ? $parents[0] : Mage::getModel('catalog/product')->load($parents[0]);
        }

        return $idOnly ? $product->getId() : $product;
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