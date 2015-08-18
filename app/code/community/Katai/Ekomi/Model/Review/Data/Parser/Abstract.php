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

abstract class Katai_Ekomi_Model_Review_Data_Parser_Abstract extends Varien_Object
{
    const HOST = 'api.ekomi.de/get_productfeedback.php';

    protected $storeId = null;

    public function fetch() {
        $tmpFile = tempnam(Mage::getBaseDir('tmp'), "ekomi");

        try {

            $fp = fopen($tmpFile, 'w');


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->getUrl());
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_FILE, $fp);

            $data = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fclose($fp);

            if ( $httpcode < 200 || $httpcode > 300 ) {
                Mage::throwException(sprintf('Invalid HTTP/1.1 status code: %s received.', $httpcode));
            }
        } catch ( Exception $ex ) {
            Mage::logException($ex);
            $data = [];
            //throw $ex;
        }

        return $this->_postProcessRatingData($tmpFile);
    }
    abstract protected function _postProcessRatingData($file);

    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * Return parse URL
     * @return string
     */
    public function getUrl()
    {
        /** @var Katai_Ekomi_Helper_Data $helper */
        $helper = Mage::helper('katai_ekomi');

        return $helper->getInterfaceScheme($this->storeId) . '://' . self::HOST . '?' . http_build_query([
                'interface_id' => $helper->getInterfaceId($this->storeId),
                'interface_pw' => $helper->getInterfacePass($this->storeId),
                'type' => $helper->getAdvancedDataParserType($this->storeId),
                'range' => $helper->getInterfaceRange($this->storeId),
                'charset' => 'utf-8',
            ]);
    }
}