<?php
/**
 * SearchResultApplier.php
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
 * @copyright 2020 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

namespace Autocompleteplus\Autosuggest\Model\Plugin\CatalogSearch;

use Magento\Framework\Data\Collection;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory;
use Magento\Framework\Api\Search\SearchResultInterface;

/**
 * Resolve specific attributes for search criteria.
 */
class SearchResultApplier
    implements \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierInterface
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var SearchResultInterface
     */
    private $searchResult;

    /**
     * @var TemporaryStorageFactory
     */
    private $temporaryStorageFactory;

    /**
     * @var array
     */
    private $orders;

    protected $registry;

    protected $catalogSession;

    /**
     * SearchResultApplier constructor.
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Framework\Registry $registry
     * @param Collection $collection
     * @param SearchResultInterface $searchResult
     * @param TemporaryStorageFactory $temporaryStorageFactory
     * @param array $orders
     */
    public function __construct(
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Framework\Registry $registry,
        Collection $collection,
        SearchResultInterface $searchResult,
        TemporaryStorageFactory $temporaryStorageFactory,
        array $orders
    ) {
        $this->collection = $collection;
        $this->searchResult = $searchResult;
        $this->registry = $registry;
        $this->temporaryStorageFactory = $temporaryStorageFactory;
        $this->catalogSession = $catalogSession;
        $this->orders = $orders;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $temporaryStorage = $this->temporaryStorageFactory->create();
        $isp_basic_ids = $this->registry->registry('isp_basic_ids');
        $addOrder = true;

        if (filter_var($this->catalogSession->getIsFullTextEnable(), FILTER_VALIDATE_BOOLEAN) && $isp_basic_ids && count($isp_basic_ids) > 0) {
            $scoredItems = [];
            $isp_basic_ids = array_reverse($isp_basic_ids);
            foreach ($this->searchResult->getItems() as $prod) {
                if ($prod->getId() != null && is_numeric($prod->getId()) && !in_array($prod->getId(), $scoredItems)) {
                    $score = array_search($prod->getId(), $isp_basic_ids);
                    $scoredItems[] = new ItemWithScore($prod->getId(), $score);
                }
            }
            $table = $temporaryStorage->storeApiDocuments($scoredItems);

            $searchOrderPresent = false;
            foreach ($this->collection->getSelect()->getPart('ORDER') as $order) {
                if ($order[0] == 'search_result.score') {
                    $searchOrderPresent = true;
                }
            }
            if (!$searchOrderPresent) {
                $ordersNew = [['search_result.score', 'DESC']];
                $this->collection->getSelect()->setPart('ORDER', $ordersNew);
                $addOrder = false;
            }
        } else {
            $table = $temporaryStorage->storeApiDocuments($this->searchResult->getItems());
        }

        $joinFroms = $this->collection->getSelect()->getPart('FROM');

        if (!array_key_exists('search_result', $joinFroms)) {
            $this->collection->getSelect()->joinInner(
                [
                    'search_result' => $table->getName(),
                ],
                'e.entity_id = search_result.' . TemporaryStorage::FIELD_ENTITY_ID,
                []
            );
        }

        if (array_key_exists('relevance', $this->orders) && isset($this->orders['relevance']) && $addOrder) {
            $this->collection->getSelect()->order(
                'search_result.' . TemporaryStorage::FIELD_SCORE . ' ' . $this->orders['relevance']
            );
        }
    }
}