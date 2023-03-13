<?php

namespace Autocompleteplus\Autosuggest\Controller\Products;

use Magento\Store\Model\ScopeInterface;

class Getstoreinventorysources extends \Autocompleteplus\Autosuggest\Controller\Products
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
     * @var \Magento\InventoryApi\Api\SourceRepositoryInterface
     */
    protected $sourceRepositoryInterface;

    /**
     * @var \Magento\InventoryApi\Api\Data\SourceInterface
     */
    protected $sourceInterface;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Api
     */
    protected $apiHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context                      $context,
        \Magento\Framework\Controller\Result\JsonFactory           $resultJsonFactory,
        \Autocompleteplus\Autosuggest\Helper\Product\Xml\Generator $xmlGenerator,
        \Magento\InventoryApi\Api\SourceRepositoryInterface        $sourceRepositoryInterface,
        \Magento\InventoryApi\Api\Data\SourceInterface             $sourceInterface,
        \Magento\Framework\Api\SearchCriteriaBuilder               $searchCriteriaBuilder,
        \Autocompleteplus\Autosuggest\Helper\Api                   $apiHelper
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->xmlGenerator = $xmlGenerator;
        $this->sourceRepositoryInterface = $sourceRepositoryInterface;
        $this->sourceInterface = $sourceInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->apiHelper = $apiHelper;

        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $authKey = $this->getRequest()->getParam('authentication_key');

        if ($this->apiHelper->getApiAuthenticationKey() != $authKey) {
            $responseData = ['status' => 'error: Authentication failed'];
        } else {
            try {
                $searchCriteria = $this->searchCriteriaBuilder->addFilter($this->sourceInterface::ENABLED, '1')->create();
                $responseData = [];
                foreach ($this->sourceRepositoryInterface->getList($searchCriteria)->getItems() as $source) {
                    $responseData[] = ["source_code" => $source->getSourceCode(), "name" => $source->getName(), "country_id" => $source->getCountryId(), "region_id" => $source->getRegionId(),
                        "region" => $source->getRegion(), "city" => $source->getCity(), "street" => $source->getStreet()];
                }
            } catch (\Exception $e) {
                $responseData[] = $e->getTraceAsString();
            }
        }

        return $result->setData($responseData);
    }
}
