<?php

namespace Autocompleteplus\Autosuggest\Controller\Products;

class Checkdeleted extends \Autocompleteplus\Autosuggest\Controller\Products
{
    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Data
     */
    protected $helper;

    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Api
     */
    protected $apiHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Autocompleteplus\Autosuggest\Model\ResourceModel\Checksum\CollectionFactory
     */
    protected $checksumCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Iterator
     */
    protected $resourceIterator;

    /**
     * @var \Autocompleteplus\Autosuggest\Model\ChecksumFactory
     */
    protected $checksumFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Checkdeleted constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Autocompleteplus\Autosuggest\Helper\Data $helper
     * @param \Autocompleteplus\Autosuggest\Helper\Api $apiHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Autocompleteplus\Autosuggest\Model\ResourceModel\Checksum\CollectionFactory $checksumCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator
     * @param \Autocompleteplus\Autosuggest\Model\ChecksumFactory $checksumFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Autocompleteplus\Autosuggest\Helper\Api $apiHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Autocompleteplus\Autosuggest\Model\ResourceModel\Checksum\CollectionFactory $checksumCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator,
        \Autocompleteplus\Autosuggest\Model\ChecksumFactory $checksumFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->helper = $helper;
        $this->apiHelper = $apiHelper;
        $this->date = $date;
        $this->storeManager = $storeManagerInterface;
        $this->checksumCollectionFactory = $checksumCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->resourceIterator = $resourceIterator;
        $this->checksumFactory = $checksumFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function execute()
    {
        $timeStamp = $this->date->gmtTimestamp();
        $request = $this->getRequest();

        $storeId = $request->getParam('store_id', $this->storeManager->getStore()->getId());

        $params = $request->getParams();
        
        $collection = $this->checksumCollectionFactory->create();
        $collection->addFieldToSelect(\Autocompleteplus\Autosuggest\Api\Data\ChecksumInterface::PRODUCT_ID);
        $collection->addFieldToFilter(\Autocompleteplus\Autosuggest\Api\Data\ChecksumInterface::STORE_ID, $storeId);

        $productUpdates = array_values($collection->getColumnValues(\Autocompleteplus\Autosuggest\Api\Data\ChecksumInterface::PRODUCT_ID));

        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addFieldToFilter('entity_id', ['in' => $productUpdates]);
        $foundProducts = $productCollection->getAllIds();

        $difference = array_diff($productUpdates, $foundProducts);

        foreach ($difference as $productId) {
            $this->helper->writeProductDeletion(null, $productId, $storeId);
        }

        $responseData = ['removed_ids' => $difference,
            'uuid' => $this->apiHelper->getApiUUID(),
            'store_id' => $storeId,
            'latency' => time() - $timeStamp
        ];

        $result = $this->resultJsonFactory->create();
        return $result->setData($responseData);
    }
}
