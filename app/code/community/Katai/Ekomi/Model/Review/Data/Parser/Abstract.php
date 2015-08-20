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

abstract class Katai_Ekomi_Model_Review_Data_Parser_Abstract extends Katai_Ekomi_Model_Ekomi_Data_Parser_Abstract
{
    /**
     * Retrieve the domain url for the parser
     * @return String
     */
    public function getHost()
    {
        return Mage::helper('katai_ekomi')->getProductFeedbackHost();
    }
}