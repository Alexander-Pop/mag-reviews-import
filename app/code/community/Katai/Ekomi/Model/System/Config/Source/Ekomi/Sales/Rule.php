<?php
/**
 * File: Rule.php
 *
 * User: Mitch Vanderlinden
 * Email: magento@mitchvdl.be
 * Date: 20.08.15
 * Time: 18:21
 * Package: Katai_Ekomi
 */

class Katai_Ekomi_Model_System_Config_Source_Ekomi_Sales_Rule extends Varien_Object
{
    public function toOptionArray()
    {
        $rules = Mage::getModel('salesrule/rule')->getCollection();

        $data = [];
        foreach ( $rules as $rule ) {
            $data[] = ['value' => $rule->getId(), 'label' => $rule->getId() . ' - ' . $rule->getName()];
        }

        return $data;
    }
}