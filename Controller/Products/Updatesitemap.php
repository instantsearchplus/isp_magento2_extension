<?php

namespace Autocompleteplus\Autosuggest\Controller\Products;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Updatesitemap
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category Mage
 *
 * @package   Instantsearchplus
 * @author    Fast Simon <info@instantsearchplus.com>
 * @copyright Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */
class Updatesitemap extends \Autocompleteplus\Autosuggest\Controller\Products
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Data
     */
    protected $helper;

    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Api
     */
    protected $apiHelper;

    protected $directory;

    protected $_file;

    protected $_fileFullPath;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Autocompleteplus\Autosuggest\Helper\Api $apiHelper,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        $this->apiHelper = $apiHelper;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->_file = 'robots.txt';
        $this->_fileFullPath = sprintf('%s%s%s', $directoryList->getRoot(), DIRECTORY_SEPARATOR, $this->_file);
        parent::__construct($context);
    }

    public function execute()
    {
        $response = array();
        $result = $this->resultJsonFactory->create();

        $storeId = $this->getRequest()->getParam('store_id', 1);
        $uuid = $this->getRequest()->getParam('uuid');
        $key = $this->getRequest()->getParam('authentication_key');
        $state = $this->getRequest()->getParam('state', 'on');

        $siteUrl = $this->helper->getStoreUrl();

        if ($this->isValid($uuid, $key)) {
            $sitemapUrl = 'Sitemap:http://magento.instantsearchplus.com/ext_sitemap?u=' . $uuid . '&store_id=' . $storeId;
            if ($state == 'on') {
                $response = $this->insertSiteMap($sitemapUrl);
            } else {
                $response = $this->removeSiteMap($sitemapUrl);
            }

        } else {
            $response = [
                'success' => false,
                'error' => 'Authentication failed'
            ];
        }
        $result->setData($response);
        return $result;
    }

    public function isValid($uuid, $authKey)
    {
        if ($this->apiHelper->getApiUUID() == $uuid
            && $this->apiHelper->getApiAuthenticationKey() == $authKey
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param $sitemapUrl
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function removeSiteMap($sitemapUrl)
    {
        $write = false;
        if ($this->directory->isFile($this->_file)) {
            if (strpos($this->directory->readFile($this->_file), $sitemapUrl) === true) {
                $write = true;
            } else {
                $response = [
                    'success' => true
                ];
            }
        } else {
            $response = [
                'success' => true
            ];
        }
        //UPDATE EXISTING ROBOTS.TXT
        if ($write) {
            if ($this->directory->isWritable($this->_file)) {

                $oldContent = $this->directory->readFile($this->_file);
                $newContent = str_replace($sitemapUrl, '', $oldContent);
                $this->directory->writeFile($this->_file, $newContent, 'w');
                $response = [
                    'success' => true
                ];
            } else {
                //write message that file is not writteble
                $response = [
                    'success' => false,
                    'error' => 'File ' . $this->_fileFullPath . ' is not writable.'
                ];
            }
        }
        return $response;
    }

    /**
     * @param $sitemapUrl
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function insertSiteMap($sitemapUrl)
    {
        $write = false;
        if ($this->directory->isFile($this->_file)) {
            if (strpos($this->directory->readFile($this->_file), $sitemapUrl) === false) {
                $write = true;
            } else {
                $response = [
                    'success' => true
                ];
            }
        } else {
            if ($this->directory->isWritable()) {
                //create robots sitemap
                $this->directory->writeFile($this->_file, $sitemapUrl);
                $response = [
                    'success' => true
                ];
            } else {
                $response = [
                    'success' => false,
                    'error' => 'File ' . $this->_fileFullPath . ' is not writable.'
                ];
            }
        }
        //UPDATE EXISTING ROBOTS.TXT
        if ($write) {
            if ($this->directory->isWritable($this->_file)) {
                /**
                 * Append sitemap
                 */
                $sitemapUrl = "\n\r" . $sitemapUrl;
                $this->directory->writeFile($this->_file, $sitemapUrl, 'a');
                $response = [
                    'success' => true
                ];
            } else {
                //write message that file is not writteble
                $response = [
                    'success' => false,
                    'error' => 'File ' . $this->_fileFullPath . ' is not writable.'
                ];
            }
        }
        return $response;
    }
}
