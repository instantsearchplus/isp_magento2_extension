<?php
/**
 * ProductCollection File
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

namespace Autocompleteplus\Autosuggest\Model\Plugin\CatalogSearch;

use Magento\Store\Model\ScopeInterface;

use Magento\Framework\DB\Select;

/**
 * ProductCollection
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
class ProductCollection extends \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
{

    protected $scopeConfig;

    protected $is_layered_enabled = false;

    protected $is_fulltext_enabled = false;

    protected $helper;

    protected $api;

    protected $logger;

    protected $catalogSession;

    protected $list_ids = [];

    protected $_totalRecords = null;

    protected $registry;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    protected $product_list_order;

    protected $product_list_dir;

    /**
     * ProductCollection constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * retrieves config values
     * @param \Autocompleteplus\Autosuggest\Helper\Data          $helper
     * retrieves ext version
     * @param \Autocompleteplus\Autosuggest\Helper\Api           $api
     * performs isp server calls
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager   retrieves
     *                                                                           store
     *                                                                           info

     * @param \Psr\Log\LoggerInterface                           $logger         logs errors
     * @param \Magento\Catalog\Model\Session                     $catalogSession session manager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Autocompleteplus\Autosuggest\Helper\Api $api,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request
    ) {

        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->api = $api;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/isp_basic_debug.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
        $this->catalogSession = $catalogSession;

        $this->product_list_order = $request->getParam('product_list_order');
        $this->product_list_dir = $request->getParam('product_list_dir');

        $laeyeredTmp = $this->helper->canUseSearchLayered();

        if (isset($laeyeredTmp) && $laeyeredTmp == '1') {
            $this->is_layered_enabled = true;
        }
    }

    /**
     * ClearSessionData
     * unset relevant session vars
     *
     * @return $this
     */
    public function clearSessionData()
    {
        $this->catalogSession->unsIsFullTextEnable();
        $this->catalogSession->unsIspSearchAlternatives();
        $this->catalogSession->unsIspSearchResultsFor();

        return $this;
    }

    /**
     * SetSessionData set relevant sessions vars
     *
     * @param Object $responseData json decoded returned from isp
     * @param string $query        query
     *
     * @return void
     */
    public function setSessionData($responseData, $query)
    {
        /**
         * InstantSearch+ js file will be injected to the search result page
         */
        $this->catalogSession->setIsFullTextEnable(true);
        /**
         * Recording the query for the current 'core/session'
         * to check it when injecting the magento_full_text.js
         */
        $this->catalogSession->setIspUrlEncodeQuery(urlencode($query));

        if (array_key_exists('alternatives', $responseData) && $responseData->alternatives) {
            $this->catalogSession->setIspSearchAlternatives($responseData->alternatives);
        } else {
            $this->catalogSession->setIspSearchAlternatives(false);
        }

        if (array_key_exists('results_for', $responseData) && $responseData->results_for) {
            $this->catalogSession->setIspSearchResultsFor($responseData->results_for);
        } else {
            $this->catalogSession->setIspSearchResultsFor(false);
        }
    }

    public function before_loadEntities($subject) {
        // fix magento missing pagination on mysql search engine
        if ($this->is_fulltext_enabled && $subject->getPageSize()) {
            $subject->getSelect()->limitPage($subject->getCurPage(), $subject->getPageSize());
        }
    }

    /**
     * AroundAddSearchFilter
     * Wraps the original addSearchFilter, checks in instantsearch
     * server if website has basic subscription. If yes
     * list of ids relevant for query is returned
     *
     * @param \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $subject
     * original class that we override the function from
     * @param Closure                                                        $proceed original function
     * @param string                                                         $query   parameter of the original function
     *
     * @return mixed
     */
    public function aroundAddSearchFilter(
        $subject,
        \Closure $proceed,
        $query
    ) {
        if (!$this->is_layered_enabled) {

            $uuid = $this->scopeConfig->getValue('autosuggest/api/uuid', ScopeInterface::SCOPE_STORE);
            $storeId = $this->storeManager->getStore()->getId();

            $extension_version = $this->helper->getVersion();
            $site_url = $this->scopeConfig->getValue(
                'web/unsecure/base_url',
                ScopeInterface::SCOPE_STORE
            );

            list($enabledFulltext, $responseData) = $this->getIdsOnly($query, $extension_version, $storeId, $uuid, $site_url);
            $product_ids = [];

            $this->clearSessionData();

            if (!$enabledFulltext) {
                $this->catalogSession->setIsFullTextEnable(false);
            } else {

                $this->is_fulltext_enabled = true;

                $this->setSessionData($responseData, $query);

                if ($responseData->total_results) {
                    $id_list = $responseData->id_list;
                    /**
                     * Validate received ids
                     */
                    foreach ($id_list as $id) {
                        if ($id != null && is_numeric($id)) {
                            $product_ids[] = $id;
                        }
                    }
                    $product_ids = array_unique($product_ids);
                    $this->list_ids = $product_ids;
                    if (count($product_ids) > 0) {
                        $this->registry->register('isp_basic_ids', $product_ids);
                    }
                    $this->helper->setBasicEnabled(1, 'stores', $this->storeManager->getStore()->getId());
                    $idStr = (count($product_ids) > 0) ? implode(',', $product_ids) : '0';
                } else {
                    $idStr = '0';
                }

                /**
                 * if (array_key_exists('server_endpoint', $responseData)) {
                 *   if ($server_end_point != $responseData->server_endpoint) {
                 *    $helper->setServerEndPoint($responseData->server_endpoint);
                 *   }
                 * }
                 */

                if (count($product_ids) > 0) {
                    $subject->getSelect()->where('e.entity_id IN (' . $idStr . ')');
                }
            }
        }

        $proceed($query);

        return $subject;
    }

    public function aroundGetSize($subject, \Closure $proceed)
    {
        if (!$this->is_layered_enabled && $this->is_fulltext_enabled) {
            if ($this->_totalRecords === null) {
                $sql = $subject->getSelectCountSql();
                $this->_totalRecords = $subject->getConnection()->fetchOne($sql, []);
            }
            return (int)$this->_totalRecords;
        } else {
            return $proceed();
        }
    }

    public function aroundLoad($subject, \Closure $proceed)
    {
        if (!$this->is_layered_enabled && $this->is_fulltext_enabled) {
            if (!$subject->isLoaded()) {
                $subject->getSize();
            }
        }
        return $proceed();
    }

    public function aroundSetOrder($subject, \Closure $proceed, $attribute, $dir = Select::SQL_DESC) {
        if (!$this->is_layered_enabled && $this->is_fulltext_enabled && $attribute == 'entity_id') {
            return $subject;
        }
        return $proceed($attribute, $dir);
    }

    /**
     * @param $sql
     */
    private function removeMagentoSearchFilter($sql)
    {
        $joinFroms = $sql->getPart('FROM');
        if (array_key_exists('search_result', $joinFroms)) {
            unset($joinFroms['search_result']);
            $sql->setPart('FROM', $joinFroms);
        }
    }

    /**
     * @param $query
     * @param $extension_version
     * @param $storeId
     * @param $uuid
     * @param $site_url
     * @return array
     */
    protected function getIdsOnly($query, $extension_version, $storeId, $uuid, $site_url, $secure=true)
    {
        $endPoint = $this->api->getApiEndpoint();
        if (!$secure) {
            $endPoint = $this->api->getApiEndpointUnsecure();
        }
        $url_domain = $endPoint . '/ma_search';

        $url = $url_domain . '?q=' . urlencode($query)
            . '&p=1&products_per_page=1000&v='
            . $extension_version . '&store_id='
            . $storeId . '&UUID=' . $uuid;

        if ($this->product_list_order) {
            $sort_by = '';
            switch ($this->product_list_order) {
                case 'price': $sort_by = $this->product_list_dir == 'asc' ? 'price_min_to_max' : 'price_max_to_min';
                    break;
                case 'name': $sort_by = $this->product_list_dir == 'asc' ? 'a_to_z' : 'z_to_a';
                    break;
                default:
                    break;
            }

            if ($sort_by != '') {
                $url = $url . '&sort_by=' . $sort_by;
            }
        }

        $url .=  '&h=' . $site_url;
        $this->api->setUrl($url);

        $enabledFulltext = false;
        $responseData = [];

        try {
            $response = $this->api->buildRequest();

            $responseData = json_decode($response->getBody());
            if ($responseData) {
                $enabledFulltext = array_key_exists('fulltext_disabled', $responseData) ?
                    !$responseData->fulltext_disabled : false;
            }

            if ($enabledFulltext) {
                $enabledFulltext = ((array_key_exists('id_list', $responseData)) &&
                    (array_key_exists('total_results', $responseData))) ? true : false;
            }

        } catch (\Exception $e) {
            $this->logger->err($e);
            if ($secure)
                return $this->getIdsOnly($query, $extension_version, $storeId, $uuid, $site_url, false);
        }
        return array($enabledFulltext, $responseData);
    }
}
