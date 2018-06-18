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
class ProductCollection
{

    protected $scopeConfig;

    protected $is_layered_enabled = false;

    protected $is_fulltext_enabled = false;

    protected $helper;

    protected $api;

    protected $logger;

    protected $catalogSession;

    protected $list_ids = array();

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ProductCollection constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * retrieves config values
     * @param \Autocompleteplus\Autosuggest\Helper\Data $helper
     * retrieves ext version
     * @param \Autocompleteplus\Autosuggest\Helper\Api $api
     * performs isp server calls
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager retrieves
     * store info
     * @param \Psr\Log\LoggerInterface $logger logs errors
     * @param \Magento\Catalog\Model\Session $catalogSession session manager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Autocompleteplus\Autosuggest\Helper\Api $api,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\Session $catalogSession
    ) {

        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->api = $api;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->catalogSession = $catalogSession;

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
     * @param string $query query
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

    /**
     * AroundAddSearchFilter
     * Wraps the original addSearchFilter, checks in instantsearch
     * server if website has basic subscription. If yes
     * list of ids relevant for query is returned
     *
     * @param Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $subject
     * original class that we override the function from
     * @param Closure $proceed original function
     * @param string $query parameter of the original function
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

            $url_domain = $this->api->getApiEndpoint().'/ma_search';

            $url = $url_domain.'?q='.urlencode($query)
                .'&p=1&products_per_page=1000&v='
                .$extension_version.'&store_id='
                .$storeId.'&UUID='.$uuid.'&h='.$site_url;

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
                $this->logger->critical($e);
            }

            $this->clearSessionData();

            if (!$enabledFulltext) {
                $this->catalogSession->setIsFullTextEnable(false);
            } else {

                $this->is_fulltext_enabled = true;

                $this->setSessionData($responseData, $query);

                if ($responseData->total_results) {
                    $id_list = $responseData->id_list;
                    $product_ids = array();
                    /**
                     * Validate received ids
                     */
                    foreach ($id_list as $id) {
                        if ($id != null && is_numeric($id)) {
                            $product_ids[] = $id;
                        }
                    }
                    $this->list_ids = $product_ids;
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

                $subject->getSelect()->where('e.entity_id IN ('.$idStr.')');
            }
        }

        $proceed($query);

        return $subject;
    }

    /**
     * AroundSetOrder
     * overrides order by relevance, because magento has a defult order
     * for relevance which is desc - we change the direction
     *
     * @param Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $subject
     * original class that we override the function from
     * @param Closure $proceed original function
     * @param string $attribute sort attribute name
     * @param string $dir order direction
     *
     * @return mixed
     */
    public function aroundSetOrder(
        $subject,
        \Closure $proceed,
        $attribute,
        $dir = Select::SQL_ASC
    ) {

        if (!$this->is_layered_enabled) {
            if ($this->is_fulltext_enabled && $attribute == 'relevance') {

                $dir = strtolower($dir) == strtolower(Select::SQL_ASC) ?
                    Select::SQL_DESC : Select::SQL_ASC;

                $id_str = (count($this->list_ids) > 0) ?
                    implode(',', $this->list_ids) : '0';
                if (!empty($id_str)) {
                    $sort = "FIELD(e.entity_id, {$id_str}) {$dir}";
                    $subject->getSelect()->order(new \Zend_Db_Expr($sort));
                }
            } else {
                return $proceed($attribute, $dir);
            }
        }

        return $subject;
    }
}
