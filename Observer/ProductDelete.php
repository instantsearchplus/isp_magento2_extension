<?php

namespace Autocompleteplus\Autosuggest\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductDelete implements ObserverInterface
{
    /**
     * Autocompleteplus helper
     *
     * @var \Autocompleteplus\Autosuggest\Helper\Data
     */
    protected $helper;

    /**
     * Magento configurable product type model
     *
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurable;

    /**
     * Logging interface
     *
     * @var \Psr\Logger\LoggerInterface
     */
    protected $logger;

    protected $batchCollection;

    /**
     * @param \Autocompleteplus\Autosuggest\Helper\Data $helper
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable
     * @param \Psr\Logger\LoggerInterface $logger
     */
    public function __construct(
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->helper = $helper;
        $this->configurable = $configurable;
        $this->logger = $logger;
    }

    /**
     * Mark product for deletion
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $storeId = $product->getStoreId();
        $productId = $product->getId();
        $sku = $product->getSku();

        $this->helper->writeProductDeletion($sku, $productId, $storeId, $product);
        return $this;
    }
}
