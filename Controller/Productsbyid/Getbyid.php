<?php

namespace Autocompleteplus\Autosuggest\Controller\Productsbyid;

class Getbyid extends \Autocompleteplus\Autosuggest\Controller\Productsbyid
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Product\Xml\Generator
     */
    protected $xmlGenerator;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $responseInterface;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Autocompleteplus\Autosuggest\Helper\Product\Xml\Generator $xmlGenerator
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->xmlGenerator = $xmlGenerator;
        $this->responseInterface = $context->getResponse();
        parent::__construct($context);
    }

    public function execute()
    {
        $request = $this->getRequest();
        $storeId = $request->getParam('store', 1);
        $id = $request->getParam('id', 1);

        if (!$id) {
            $returnArr = [
                'status' => self::STATUS_FAILURE,
                'error_code' => self::MISSING_PARAMETER,
                'error_details' => __('The "id" parameter is mandatory'),
            ];
            $result = $this->resultJsonFactory->create();
            return $result->setData($returnArr);
        }

        $productIds = explode(',', $id);
        $xml = $this->xmlGenerator->renderCatalogByIds($productIds, $storeId);
        $this->responseInterface->setHeader('Content-type', 'text/xml');
        $this->responseInterface->setBody($xml);
    }
}
