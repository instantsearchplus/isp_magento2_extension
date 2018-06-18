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

    public function getSearchResults()
    {
        $template = $this->cacheManager->load('autocomplete_template_serp');

        if (!$template) {
            $template = $this->helper->fetchProductListingData();
            
            $this->cacheManager->save(
                $template,
                'autocomplete_template_serp',
                array("autocomplete_cache"),
                self::SERP_PAGE_TEMPLATE_LIFETIME
            );
        }

        return $template;
    }
}
