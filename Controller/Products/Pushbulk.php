<?php

namespace Autocompleteplus\Autosuggest\Controller\Products;

use Magento\Store\Model\ScopeInterface;

class Pushbulk extends \Autocompleteplus\Autosuggest\Controller\Products
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
     * @var \Autocompleteplus\Autosuggest\Model\Pusher
     */
    protected $pusher;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Product\Xml\Generator
     */
    protected $xmlGenerator;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Autocompleteplus\Autosuggest\Helper\Api $apiHelper,
        \Autocompleteplus\Autosuggest\Model\Pusher $pusher,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Autocompleteplus\Autosuggest\Helper\Product\Xml\Generator $xmlGenerator,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->helper = $helper;
        $this->apiHelper = $apiHelper;
        $this->pusher = $pusher;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManagerInterface;
        $this->xmlGenerator = $xmlGenerator;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $request  = $this->getRequest();
        $response = $this->getResponse();
        $pushId   = $request->getParam('pushid', false);
        $data     = [];

        if (!$pushId) {
            $responseArr = [
                'success'   =>  false,
                'message'   =>  'Missing pushid!'
            ];
            $result = $this->resultJsonFactory->create();
            return $result->setData($responseArr);
        }

        $pusher = $this->pusher->load($pushId);
        $sent = $pusher->getSent();
        
        if ($pusher->isProcessing()) {
            $responseArr = [
                'success'   =>  false,
                'message'   =>  'push is in process'
            ];
            $result = $this->resultJsonFactory->create();
            return $result->setData($responseArr);
        } elseif ($pusher->isSent()) {
            $responseArr = [
                'success'   =>  false,
                'message'   =>  'push was already sent'
            ];
            $result = $this->resultJsonFactory->create();
            return $result->setData($responseArr);
        } else {
            $pusher->setSent(1)
                ->save();
        }

        $offset = $pusher->getOffset();
        $count = 100;
        $storeId = $pusher->getStoreId();
        $toSend = $pusher->getToSend();
        $totalBatches = $pusher->getTotalBatches();
        $siteUrl = $this->scopeConfig->getValue(
            'web/unsecure/base_url',
            ScopeInterface::SCOPE_STORE
        );

        $data = [
            'uuid'  =>  $this->apiHelper->getApiUUID(),
            'site_url'  =>  $siteUrl,
            'store_id'  =>  $storeId,
            'authentication_key'    =>  $this->apiHelper->getApiAuthenticationKey(),
            'total_batches' =>  $totalBatches,
            'batch_number'  =>  $pusher->getBatchNumber(),
            'products'  =>  $this->xmlGenerator->renderCatalogXml($offset, $count, $storeId, '', '', '')
        ];

        if (($offset + $count) > $toSend) {
            $data['is_last'] = 1;
            $count = ($toSend - $offset);
        }

        $apiRequest = $this->apiHelper;
        $apiRequest->setUrl($apiRequest->getApiEndpoint() . '/magento_fetch_products');
        $apiRequest->setRequestType(\Zend_Http_Client::POST);
        $response = $apiRequest->buildRequest($data);
        unset($data['products']);
        
        $responseData = $response->getBody();
        if ($responseData !== 'ok') {
            $responseArr = [
                'success'   =>  false,
                'message'   =>  $responseData
            ];
            $result = $this->resultJsonFactory->create();
            return $result->setData($responseArr);
        }

        $pusher->setSent(2)
            ->save();

        $nextPush = $pusher->getNext();
        $totalPushes = $pusher->getCollection()->getSize();

        $responseArr = [
            'success'              => true,
            'updatedStatus'        => '',
            'updatedSuccessStatus' => '',
            'message'              => '',
            'nextPushUrl'          => '',
            'count'                => $count
        ];

        if ($nextPush) {
            $nextPushId = $nextPush
                ->getId();
            $nextPushUrl = $pusher
                ->getAbsoluteUrl();
            $updatedStatus = 'Syncing: push ' . $nextPushId . '/' . $totalPushes;
            $updatedSuccessStatus = 'Successfully synced '. $count .' products';

            $nextPushValues = [
                'nextPushUrl'   =>  $nextPushUrl,
                'updatedStatus' =>  $updatedStatus,
                'updatedSuccessStatus'  =>  $updatedSuccessStatus
            ];

            $responseArr = array_replace($initialResponse, $nextPushValues);
        }

        $result = $this->resultJsonFactory->create();
        return $result->setData($responseArr);
    }
}
