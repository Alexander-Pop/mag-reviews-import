<?php
/**
 * File: Rating.php
 *
 * User: Mitch Vanderlinden
 * Email: magento@mitchvdl.be
 * Date: 14.08.15
 * Time: 17:51
 * Package: Katai_Ekomi
 */

class Katai_Ekomi_Model_System_Config_Source_Ekomi_Rating extends Varien_Object
{

    public function toOptionArray()
    {
        $ratings = Mage::getModel('rating/rating')->getCollection();

        $data = [];
        foreach ( $ratings as $rating ) {
            $data[] = ['value' => $rating->getId(), 'label' => Mage::helper('katai_ekomi')->__($rating->getRatingCode())];
        }

        return $data;
    }
}