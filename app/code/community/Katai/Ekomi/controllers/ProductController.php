<?php

include_once("Mage/Review/controllers/ProductController.php");

class Katai_Ekomi_ProductController extends Mage_Review_ProductController {

    protected $forceLoad = false;

    public function listAction() {
        $this->forceLoad = true;

        if ($product = $this->_initProduct()) {
            Mage::register('productId', $product->getId());

            $design = Mage::getSingleton('catalog/design');
            $settings = $design->getDesignSettings($product);
            if ($settings->getCustomDesign()) {
                $design->applyCustomDesign($settings->getCustomDesign());
            }
            $this->_initProductLayout($product);

            // update breadcrumbs
            if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                $breadcrumbsBlock->addCrumb('product', array(
                                                            'label'    => $product->getName(),
                                                            'link'     => $product->getProductUrl(),
                                                            'readonly' => true,
                                                       ));
                $breadcrumbsBlock->addCrumb('reviews', array('label' => Mage::helper('review')->__('Product Reviews')));
            }

            $this->renderLayout();
        } elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noRoute');
        }
    }


    protected function _loadProduct($productId) {
        if (!$productId) {
            return false;
        }

        $forceSave = (int) $this->getRequest()->getParam('force_save');

        $product = Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($productId);
        /* @var $product Mage_Catalog_Model_Product */
        if (!$product->getId()) {
            return false;
        }

        if($product->getTypeId() <> "configurable") {
            $parentId = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());

            if(isset($parentId[0])){
                $product = Mage::getModel('catalog/product')->load($parentId[0]);
            }
        }

        if (($this->forceLoad === false && $forceSave != 1) && (!$product->isVisibleInCatalog() || !$product->isVisibleInSiteVisibility())) {
            return false;
        }

        Mage::register('current_product', $product);
        Mage::register('product', $product);

        return $product;
    }
}
