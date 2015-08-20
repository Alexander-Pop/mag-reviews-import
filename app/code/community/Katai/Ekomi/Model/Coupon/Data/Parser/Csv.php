<?php
/**
 * File: Csv.php
 *
 * User: Mitch Vanderlinden
 * Email: mangeot@mitchvdl.be
 * Date: 18.08.15
 * Time: 16:00
 * Package: Katai_Ekomi
 */

class Katai_Ekomi_Model_Coupon_Data_Parser_Csv extends Katai_Ekomi_Model_Coupon_Data_Parser_Abstract
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


        $fp = fopen($file, 'r');

        while (($d = fgetcsv($fp, 2048, ",")) !== FALSE)
        {
            $_t = new Varien_Object();
            $data[] = $_t->setData([
                'timestamp' => $d[0],
                'order_id' => $d[1],
                'rating_id' => $d[2],
                'review' => $d[3]
            ]);
        }
        fclose($fp);

        return $data;
    }

}