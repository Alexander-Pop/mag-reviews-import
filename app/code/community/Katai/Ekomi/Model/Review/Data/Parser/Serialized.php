<?php
/**
 * File: Serialized.php
 *
 * User: Mitch Vanderlinden
 * Email: mangeot@mitchvdl.be
 * Date: 18.08.15
 * Time: 16:00
 * Package: Katai_Ekomi
 */

class Katai_Ekomi_Model_Review_Data_Parser_Serialized extends Katai_Ekomi_Model_Review_Data_Parser_Abstract
{
    /**
     * Post process data, this would need to be overwritten if different field values are being used.
     * Easy to extend this parent class and change these mappings.
     * @param $file
     * @return array
     */
    protected function _postProcessRatingData($file)
    {
        $data = [];
        /** @var Katai_Ekomi_Helper_Data $helper */
        $helper = Mage::helper('katai_ekomi');

        $map = [
            $helper->getAdvancedSerializedMap('timestamp', $this->storeId) => 'timestamp',
            $helper->getAdvancedSerializedMap('client_id', $this->storeId) => 'client_id',
            $helper->getAdvancedSerializedMap('product_id', $this->storeId) => 'product_id',
            $helper->getAdvancedSerializedMap('rating', $this->storeId) => 'rating',
            $helper->getAdvancedSerializedMap('review', $this->storeId) => 'review',
        ];

        $fp = fopen($file, 'r');
        $data = fread($fp, filesize($file));
        fclose($fp);

        // Corrupted data. An empty array is set, no data is processed.
        // if serialized data is corrupted a notice is thrown
        $data = unserialize($data);
        if ( !is_array($data) ) {
            $data = [];
        }

        foreach ( $data as $_idx => &$line ) {
            $result = new Varien_Object();
            $line = Varien_Object_Mapper::accumulateByMap($line, $result, $map);
        }

        return $data;
    }
}