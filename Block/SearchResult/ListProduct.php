<?php
/**
 * ListProduct File
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

namespace Autocompleteplus\Autosuggest\Block\SearchResult;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Cache;

/**
 * ListProduct
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
class ListProduct extends Template
{
    const SERP_PAGE_TEMPLATE_LIFETIME = 60;

    protected $helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $response;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    protected $storeManager;
    protected $formKey;
    protected $cacheManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Response\Http $response,
        \Autocompleteplus\Autosuggest\Helper\Api $helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = []
    ) {
        $this->storeManager = $context->getStoreManager();
        $this->request = $request;
        $this->response = $response;
        $this->helper = $helper;
        $this->formKey = $formKey;
        $this->cacheManager = $context->getCache();
        $this->_isScopePrivate = true;
        $this->logger = $context->getLogger();
        parent::__construct($context, $data);
    }

    public function getSearchQuery()
    {
        return $this->request->getParam('q');
    }

    public function getApiUUID()
    {
        return $this->helper->getApiUUID();
    }

    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Returns template of serp
     * getSearchResults
     *
     * @param int $trials not more then 3 trials
     *
     * @return bool
     */
    public function getSearchResults($trials=1)
    {
        $template = $this->cacheManager->load('autocomplete_template_serp');

        $template_age = $this->cacheManager->load('autocomplete_template_serp_age');
        $template_age = floatval($template_age);
        $now = strtotime('now');

        if (($now - $template_age) > self::SERP_PAGE_TEMPLATE_LIFETIME
            || !$template
        ) {
            try {
                $template = $this->helper->fetchProductListingData();

                $this->cacheManager->save(
                    $template,
                    'autocomplete_template_serp',
                    array("autocomplete_cache")
                );

                $this->cacheManager->save(
                    (string)$now,
                    'autocomplete_template_serp_age',
                    array("autocomplete_cache")
                );
                /**
                 * Successefully brought template from acp.magento
                 */
                return $template;

            } catch (\Zend_Http_Client_Exception $e) {
                $this->logger->critical($e);
                if (!$template) {
                    if ($trials < 4) {
                        $trials++;
                        /**
                         * Could not bring template from acp.magento
                         * trying to get 2 more times
                         */
                        return $this->getSearchResults($trials);
                    }
                }
                /**
                 * Got an exception but there is an old template
                 * or trials are over
                 */
                return $template;
            }
        } else {
            /**
             * Cached template is still fresh
             * or it does not exists at all
             */
            return $template;
        }

    }
}
