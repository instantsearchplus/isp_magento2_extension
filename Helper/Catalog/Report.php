<?php
/**
 * Report File
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
 * @copyright 2014 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

namespace Autocompleteplus\Autosuggest\Helper\Catalog;

/**
 * Report
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
 * @copyright 2014 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */
class Report extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $productVisibility;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $productCollection;

    /**
     * @var int
     */
    protected $storeId;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Catalog\Model\Product\Visibility $productVisibility
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $storeManagerInterface;
        $this->productVisibility = $productVisibility;
        parent::__construct($context);
    }

    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
    }

    public function getCurrentStoreId()
    {
        if ($this->storeId) {
            return $this->storeId;
        }
        return $this->storeManager->getStore()->getId();
    }

    public function getProductCollection()
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addStoreFilter($this->getCurrentStoreId());
        return $productCollection;
    }

    public function getEnabledProducts()
    {
        return $this->getProductCollection()->addAttributeToFilter('status',
            ['eq'  =>  \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED]);
    }

    public function getDisabledProducts()
    {
        return $this->getProductCollection()->addAttributeToFilter('status',
            ['eq'  =>  \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED]);
    }

    public function getVisibleInCatalogProducts()
    {
        return $this->getProductCollection()->setVisibility($this->productVisibility->getVisibleInCatalogIds());
    }

    public function getVisibleInSearchProducts()
    {
        return $this->getEnabledProducts()->setVisibility($this->productVisibility->getVisibleInSearchIds());
    }

    public function getDisabledProductsCount()
    {
        $collection = $this->getDisabledProducts();
        return $collection->getSize();
    }

    public function getEnabledProductsCount()
    {
        $collection = $this->getEnabledProducts();
        return $collection->getSize();
    }

    public function getSearchableProductsCount()
    {
        $collection = $this->getVisibleInSearchProducts();
        return $collection->getSize();
    }

    public function getSecondarySearchableProductsCount()
    {
        $collection = $this->getEnabledProducts();
        $collection->addAttributeToFilter('visibility', [
                ['finset'  =>  3],
                ['finset'  =>  4]
            ]);
        return $collection->getSize();
    }
}
