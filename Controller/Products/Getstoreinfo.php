<?php
/**
 * Getstoreinfo.php
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
 * @copyright 2019 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

namespace Autocompleteplus\Autosuggest\Controller\Products;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Getstoreinfo
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
class Getstoreinfo extends \Autocompleteplus\Autosuggest\Controller\Products
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollection;

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

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadataInterface;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Module\ModuleList
     */
    protected $moduleList;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Autocompleteplus\Autosuggest\Helper\Api $apiHelper,
        \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\Module\ModuleList $moduleList,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        $this->apiHelper = $apiHelper;
        $this->storeManager = $storeManagerInterface;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        $authKey = $this->getRequest()->getParam('authentication_key');
        $uuid = $this->getRequest()->getParam('uuid');
        $storeId = $this->getRequest()->getParam('store', false);

        if (!$this->isValid($uuid, $authKey)) {
            $response = [
                'status' => 'error: Authentication failed'
            ];
            $result->setData($response);
            return $result;
        }

        if (!$storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $responseData = $this->helper->getStoreInformation($storeId);
        return $result->setData($responseData);
    }

    /**
     * @param  $uuid
     * @param  $authKey
     * @return bool
     */
    public function isValid($uuid, $authKey)
    {
        if ($this->apiHelper->getApiUUID() == $uuid 
            && $this->apiHelper->getApiAuthenticationKey() == $authKey
        ) {
            return true;
        }

        return false;
    }
}
