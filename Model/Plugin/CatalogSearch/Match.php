<?php

namespace Autocompleteplus\Autosuggest\Model\Plugin\CatalogSearch;

use Magento\Framework\DB\Helper\Mysql\Fulltext;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Field\FieldInterface;
use Magento\Framework\Search\Adapter\Mysql\Field\ResolverInterface;
use Magento\Framework\Search\Adapter\Mysql\ScoreBuilder;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\Search\Adapter\Preprocessor\PreprocessorInterface;

/**
 * MySQL search query match.
 *
 * @api
 * @deprecated 102.0.0
 * @see \Magento\ElasticSearch
 * @since 100.0.2
 */
class Match extends \Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match
{
    /**
     * @var string
     */
    const SPECIAL_CHARACTERS = '-+~/\\<>\'":*$#@()!,.?`=%&^';

    const MINIMAL_CHARACTER_LENGTH = 3;

    /**
     * @var string[]
     */
    private $replaceSymbols = [];

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var Fulltext
     */
    private $fulltextHelper;

    /**
     * @var string
     */
    private $fulltextSearchMode;

    /**
     * @var PreprocessorInterface[]
     * @since 100.1.0
     */
    protected $preprocessors;

    private $catalogSession;

    protected $registry;

    public function __construct(
        \Magento\Framework\Registry $registry,
		\Magento\Catalog\Model\Session $catalogSession,
        ResolverInterface $resolver,
        Fulltext $fulltextHelper,
        $fulltextSearchMode = Fulltext::FULLTEXT_MODE_BOOLEAN,
        array $preprocessors = []
    ) {
        $this->registry = $registry;
		$this->catalogSession = $catalogSession;
		$this->resolver = $resolver;
        $this->replaceSymbols = str_split(self::SPECIAL_CHARACTERS, 1);
        $this->fulltextHelper = $fulltextHelper;
        $this->fulltextSearchMode = $fulltextSearchMode;
        $this->preprocessors = $preprocessors;
        parent::__construct($resolver, $fulltextHelper, $fulltextSearchMode, $preprocessors);
    }

    /**
     * @inheritdoc
     */
    public function build(
        ScoreBuilder $scoreBuilder,
        Select $select,
        RequestQueryInterface $query,
        $conditionType
    ) {
        $isp_basic_ids = $this->registry->registry('isp_basic_ids');
        if (filter_var($this->catalogSession->getIsFullTextEnable(), FILTER_VALIDATE_BOOLEAN) && $isp_basic_ids) {
            $idStr = implode(',', $isp_basic_ids);
			$select->where('eav_index.entity_id IN ('.$idStr.')');

            $joinFroms = $select->getPart('FROM');
            if (array_key_exists('search_index', $joinFroms)) {
                unset($joinFroms['search_index']);
            }

            if (array_key_exists('cea', $joinFroms)) {
                unset($joinFroms['cea']);
            }
            $select->setPart('FROM', $joinFroms);

		} else {
			/** @var $query \Magento\Framework\Search\Request\Query\Match */
			$queryValue = $this->prepareQuery($query->getValue(), $conditionType);

			$fieldList = [];
			foreach ($query->getMatches() as $match) {
				$fieldList[] = $match['field'];
			}
			$resolvedFieldList = $this->resolver->resolve($fieldList);

			$fieldIds = [];
			$columns = [];
			foreach ($resolvedFieldList as $field) {
				if ($field->getType() === FieldInterface::TYPE_FULLTEXT && $field->getAttributeId()) {
					$fieldIds[] = $field->getAttributeId();
				}
				$column = $field->getColumn();
				$columns[$column] = $column;
			}

			$matchQuery = $this->fulltextHelper->getMatchQuery(
				$columns,
				$queryValue,
				$this->fulltextSearchMode
			);
			$scoreBuilder->addCondition($matchQuery, true);

			if ($fieldIds) {
				$matchQuery = sprintf('(%s AND search_index.attribute_id IN (%s))', $matchQuery, implode(',', $fieldIds));
			}

			$select->where($matchQuery);
		}


        return $select;
    }

}
