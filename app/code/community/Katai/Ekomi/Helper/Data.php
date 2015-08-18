<?php
/**
 * File: Data.php
 *
 * User: Mitch Vanderlinden
 * Email: magento@mitchvdl.be
 * Package: Katai_Ekomi
 */
class Katai_Ekomi_Helper_Data extends Mage_Core_Helper_Abstract
{

    const XPATH_GENERAL_IS_ENABLED = 'katai_ekomi/general/is_enabled';
    const XPATH_GENERAL_IS_LOGGING_ENABLED = 'katai_ekomi/general/is_logging_enabled';
    const XPATH_GENERAL_INTERFACE_SCHEME = 'katai_ekomi/general/interface_scheme';
    const XPATH_GENERAL_INTERFACE_ID = 'katai_ekomi/general/interface_id';
    const XPATH_GENERAL_INTERFACE_PASS = 'katai_ekomi/general/interface_pass';
    const XPATH_GENERAL_INTERFACE_RANGE = 'katai_ekomi/general/interface_range';

    const XPATH_DEFAULT_NICKNAME = 'katai_ekomi/rating/default_nickname';
    const XPATH_APPLY_RATING = 'katai_ekomi/rating/apply_rating';
    const XPATH_IS_REIMPORT_ALL = 'katai_ekomi/rating/reimport_all';
    const XPATH_DEFAULT_TITLE = 'katai_ekomi/rating/default_title';

    const XPATH_ADVANCED_DATA_PARSER = 'katai_ekomi/advanced/data_parser';
    const XPATH_ADVANCED_SERIALIZED_MAP= 'katai_ekomi/advanced/serialised_map_%s';


    /**
     * 'static' lookup cache
     * @var array
     */
    protected $ratingOptions = [];

    /**
     * * 'static' lookup cache
     * @var array
     */
    protected $ratingTitles = [];

    /**
     * @param int $storeId
     * @return bool
     */
    public function isEnabled($storeId = 0)
    {
        return Mage::getStoreConfigFlag(self::XPATH_GENERAL_IS_ENABLED, $storeId);
    }

    /**
     * @param int $storeId
     * @return mixed
     */
    public function isLoggingEnabled($storeId = 0)
    {
        return Mage::getStoreConfig(self::XPATH_GENERAL_IS_LOGGING_ENABLED, $storeId);
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function importAllReviews($storeId = 0)
    {
        return Mage::getStoreConfigFlag(self::XPATH_IS_REIMPORT_ALL, $storeId);
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function disabledImportAllReviews($storeId = 0)
    {
        Mage::app()->getConfig()->deleteConfig(self::XPATH_IS_REIMPORT_ALL,'stores', $storeId);
        Mage::app()->getConfig()->saveConfig(self::XPATH_IS_REIMPORT_ALL, 0, 'websites', $storeId);
        return $this;
    }

    /**
     * Return default nickname
     * @param int $storeId
     * @return string
     */
    public function getDefaultNickname($storeId = 0)
    {
        return Mage::getStoreConfig(self::XPATH_DEFAULT_NICKNAME, $storeId);
    }

    /**
     * @param int $storeId
     * @return mixed
     */
    public function getInterfaceScheme($storeId = 0)
    {
        return Mage::getStoreConfig(self::XPATH_GENERAL_INTERFACE_SCHEME, $storeId);
    }

    /**
     * @param int $storeId
     * @return mixed
     */
    public function getInterfaceId($storeId = 0)
    {
        return Mage::getStoreConfig(self::XPATH_GENERAL_INTERFACE_ID, $storeId);
    }

    /**
     * @param int $storeId
     * @return mixed
     */
    public function getInterfacePass($storeId = 0)
    {
        return Mage::getStoreConfig(self::XPATH_GENERAL_INTERFACE_PASS, $storeId);
    }

    /**
     * @param int $storeId
     * @return mixed
     */
    public function getInterfaceRange($storeId = 0)
    {
        return Mage::getStoreConfig(self::XPATH_GENERAL_INTERFACE_RANGE, $storeId);
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getAdvancedDataParserType($storeId = 0)
    {
        return strtolower(Mage::getStoreConfig(self::XPATH_ADVANCED_DATA_PARSER, $storeId));
    }

    /**
     * @param bool|false $singleton
     * @param int $storeId
     * @return Katai_Ekomi_Model_Review_Data_Parser_Abstract
     */
    public function getAdvancedDataParser($singleton = false, $storeId = 0)
    {
        $class = 'katai_ekomi/review_data_parser_' . $this->getAdvancedDataParserType($storeId);

        if ( $singleton ) {
            return Mage::getSingleton($class)->setStoreId($storeId);
        }

        return Mage::getModel($class)->setStoreId($storeId);
    }

    /**
     * @param $key
     * @param int $storeId
     * @return string
     */
    public function getAdvancedSerializedMap($key, $storeId = 0)
    {
        return Mage::getStoreConfig(sprintf(self::XPATH_ADVANCED_SERIALIZED_MAP, $key), $storeId);
    }

    /**
     * Apply the imported rating to specified rating ID
     *
     * @param int $storeId
     * @return mixed
     */
    public function applyRatingTo($storeId = 0)
    {
        return Mage::getStoreConfig(self::XPATH_APPLY_RATING, $storeId);
    }

    /**
     * Return rating title based on rating value [1-5]
     *
     * @param int $ratingValue
     * @param int $storeId
     * @return array
     */
    public function getRatingOptionTitle($ratingValue, $storeId = 0)
    {
        $_t = $this->getRatingOptionTitles($storeId); // PHP 5.4
        return $_t[$ratingValue];
    }

    /**
     * Return array with all the rating titles.
     * @param int $storeId
     * @return array
     */
    public function getRatingOptionTitles($storeId = 0)
    {
        if ( !isset($this->ratingTitles[$storeId]) ) {
            $data = Mage::getStoreConfig(self::XPATH_DEFAULT_TITLE, $storeId);
            $data = unserialize($data);
            if ( !is_array($data)) {
                return [];
            }
            $labels = $this->getRatingOptionDefaultTitles();
            foreach ( $data as $_idx => $rating ) {
                $labels[$rating['rating_id']] = $rating['title_label'];
            }
            $this->ratingTitles[$storeId] = $labels;
        }
        return $this->ratingTitles[$storeId];
    }

    /**
     * Default rating option titles
     * @return array
     */
    public function getRatingOptionDefaultTitles()
    {
        return [
            '0' => $this->__('Void Response'),
            '1' => $this->__('Very Dissatisfied'),
            '2' => $this->__('Dissatified'),
            '3' => $this->__('Neutral'),
            '4' => $this->__('Satisfied'),
            '5' => $this->__('Very Satisfied'),
        ];
    }


    /**
     * Return Rating options by $ratingId, if no $ratingId is supplied default configured rating id is used
     *
     * @param int $ratingId
     * @param $storeId
     * @return array
     */
    public function getRatingOptions($ratingId = null, $storeId = 0)
    {
        $key = $storeId . '_' .$ratingId;

        if ( !isset($this->ratingOptions[$key]) ) {
            $ratingId = $ratingId ?: $this->applyRatingTo($storeId);

            $options = [];
            $collection = Mage::getModel('rating/rating')->load($ratingId)->getOptions();
            $options[] = '_empty_';    // To work with the regular array indexes for ratings, we create an empty key on 0 [1..5]
            foreach ( $collection as $e ) {
                $options[] = $e->getOptionId();
            }

            $this->ratingOptions[$key] = $options;
        }

        return $this->ratingOptions[$key];
    }

}