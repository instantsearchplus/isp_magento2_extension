<?php
/**
 * Mapper.php
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

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Filter\Builder;
use Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match;
use Magento\Framework\Search\Adapter\Mysql\Query\MatchContainer;
use Magento\Framework\Search\Adapter\Mysql\Query\QueryContainer;
use Magento\Framework\Search\Adapter\Mysql\Query\QueryContainerFactory;
use Magento\Framework\Search\EntityMetadata;
use Magento\Framework\Search\Request\Query\BoolExpression as BoolQuery;
use Magento\Framework\Search\Request\Query\Filter as FilterQuery;
use Magento\Framework\Search\Request\Query\Match as MatchQuery;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Adapter\Mysql;

class Mapper extends \Magento\Framework\Search\Adapter\Mysql\Mapper
{

    /**
     * @var ScoreBuilder
     */
    private $scoreBuilderFactory;

    /**
     * @var Filter\Builder
     */
    private $filterBuilder;

    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @var IndexBuilderInterface[]
     */
    private $indexProviders;

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var EntityMetadata
     */
    private $entityMetadata;

    /**
     * @var QueryContainerFactory
     */
    private $queryContainerFactory;

    /**
     * @var Query\Builder\Match
     */
    private $matchBuilder;

    /**
     * @var TemporaryStorage
     */
    private $temporaryStorage;

    /**
     * @var string
     */
    private $relevanceCalculationMethod;

    /**
     * @var TemporaryStorageFactory
     */
    private $temporaryStorageFactory;

    protected $registry;

    private $catalogSession;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Framework\Search\Adapter\Mysql\ScoreBuilderFactory $scoreBuilderFactory,
        \Magento\Framework\Search\Adapter\Mysql\Filter\Builder $filterBuilder,
        \Magento\Framework\Search\Adapter\Mysql\ConditionManager $conditionManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Search\EntityMetadata $entityMetadata,
        \Magento\Framework\Search\Adapter\Mysql\Query\QueryContainerFactory $queryContainerFactory,
        \Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match $matchBuilder,
        \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory $temporaryStorageFactory,
        array $indexProviders,
        $relevanceCalculationMethod = 'SUM'
    ) {
        $this->registry = $registry;
        $this->catalogSession = $catalogSession;
        $this->scoreBuilderFactory = $scoreBuilderFactory;
        $this->filterBuilder = $filterBuilder;
        $this->conditionManager = $conditionManager;
        $this->resource = $resource;
        $this->entityMetadata = $entityMetadata;
        $this->indexProviders = $indexProviders;
        $this->queryContainerFactory = $queryContainerFactory;
        $this->matchBuilder = $matchBuilder;
        $this->temporaryStorage = $temporaryStorageFactory->create();
        $this->temporaryStorageFactory = $temporaryStorageFactory;
        if (!in_array($relevanceCalculationMethod, ['SUM', 'MAX'], true)) {
            throw new \LogicException('Unsupported relevance calculation method used. Only SUM and MAX are allowed');
        }
        $this->relevanceCalculationMethod = $relevanceCalculationMethod;
        parent::__construct(
            $scoreBuilderFactory,
            $filterBuilder,
            $conditionManager,
            $resource,
            $entityMetadata,
            $queryContainerFactory,
            $matchBuilder,
            $temporaryStorageFactory,
            $indexProviders,
            $relevanceCalculationMethod
        );
    }

    public function buildQuery($request)
    {
        if (!array_key_exists($request->getIndex(), $this->indexProviders)) {
            throw new \LogicException('Index provider not configured');
        }

        $indexBuilder = $this->indexProviders[$request->getIndex()];

        $queryContainer = $this->queryContainerFactory->create(
            [
                'indexBuilder' => $indexBuilder,
                'request' => $request,
            ]
        );
        $select = $indexBuilder->build($request);
        /** @var ScoreBuilder $scoreBuilder */
        $scoreBuilder = $this->scoreBuilderFactory->create();
        $select = $this->processQuery(
            $scoreBuilder,
            $request->getQuery(),
            $select,
            BoolQuery::QUERY_CONDITION_MUST,
            $queryContainer
        );

        $select = $this->addDerivedQueries(
            $request,
            $queryContainer,
            $scoreBuilder,
            $select,
            $indexBuilder
        );

        $isp_basic_ids = $this->registry->registry('isp_basic_ids');
        if (!filter_var($this->catalogSession->getIsFullTextEnable(), FILTER_VALIDATE_BOOLEAN) || !$isp_basic_ids) {
            $select->limit($request->getSize(), $request->getFrom());
            $select->order('relevance ' . Select::SQL_DESC)->order('entity_id ' . Select::SQL_DESC);
        } else {
            $select->order('relevance ' . Select::SQL_DESC);
        }

        return $select;
    }

    private function addDerivedQueries(
        $request,
        $queryContainer,
        $scoreBuilder,
        $select,
        $indexBuilder
    ) {
        $matchQueries = $queryContainer->getMatchQueries();
        if (!$matchQueries) {
            $select->columns($scoreBuilder->build());
            $select = $this->createAroundSelect($select, $scoreBuilder);
        } else {
            $matchContainer = array_shift($matchQueries);
            $this->matchBuilder->build(
                $scoreBuilder,
                $select,
                $matchContainer->getRequest(),
                $matchContainer->getConditionType()
            );
            $select->columns($scoreBuilder->build());
            $select = $this->createAroundSelect($select, $scoreBuilder);
            $select = $this->addMatchQueries($request, $select, $indexBuilder, $matchQueries);
        }

        return $select;
    }

    private function addMatchQueries(
        $request,
        $select,
        $indexBuilder,
        array $matchQueries
    ) {
        $queriesCount = count($matchQueries);
        if ($queriesCount) {
            $table = $this->temporaryStorage->storeDocumentsFromSelect($select);
            foreach ($matchQueries as $matchContainer) {
                $queriesCount--;
                $matchScoreBuilder = $this->scoreBuilderFactory->create();
                $matchSelect = $this->matchBuilder->build(
                    $matchScoreBuilder,
                    $indexBuilder->build($request),
                    $matchContainer->getRequest(),
                    $matchContainer->getConditionType()
                );
                $select = $this->joinPreviousResultToSelect($matchSelect, $table, $matchScoreBuilder);
                if ($queriesCount) {
                    $previousResultTable = $table;
                    $table = $this->temporaryStorage->storeDocumentsFromSelect($select);
                    $this->getConnection()->dropTable($previousResultTable->getName());
                }
            }
        }
        return $select;
    }

    private function joinPreviousResultToSelect($query, $previousResultTable, $scoreBuilder)
    {
        $query->joinInner(
            ['previous_results' => $previousResultTable->getName()],
            'previous_results.entity_id = search_index.entity_id',
            []
        );
        $scoreBuilder->addCondition('previous_results.score', false);
        $query->columns($scoreBuilder->build());

        $query = $this->createAroundSelect($query, $scoreBuilder);

        return $query;
    }

    private function getConnection()
    {
        return $this->resource->getConnection();
    }

    private function createAroundSelect($select, $scoreBuilder)
    {
        $parentSelect = $this->getConnection()->select();
        $parentSelect->from(
            ['main_select' => $select],
            [
                $this->entityMetadata->getEntityId() => 'entity_id',
                'relevance' => sprintf('%s(%s)', $this->relevanceCalculationMethod, $scoreBuilder->getScoreAlias()),
            ]
        )->group($this->entityMetadata->getEntityId());
        return $parentSelect;
    }
}