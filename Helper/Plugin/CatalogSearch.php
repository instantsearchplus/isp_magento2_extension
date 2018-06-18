<?php
/**
 * CatalogSearch.php File
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
 * @copyright 2016 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

namespace Autocompleteplus\Autosuggest\Helper\Plugin;

use Magento\Framework\App\Helper;
/**
 * CatalogSearch
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
 * @copyright ${YEAR} Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */
class CatalogSearch extends \Magento\Search\Helper\Data
{
    protected $is_mini_form_rewrite_enabled = false;
    
    protected $helper;
    
    protected $logger;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
    
    /**
     * CatalogSearch constructor.
     * @param Helper\Data $helper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->helper = $helper;
        $this->logger = $context->getLogger();

        $is_mini_form_rewrite_enabledTmp = $this->helper->canUseMiniFormRewrite();

        if (isset($is_mini_form_rewrite_enabledTmp) && $is_mini_form_rewrite_enabledTmp == '1') {
            $this->is_mini_form_rewrite_enabled = true;
        }


    }

    /**
     * aroundGetResultUrl changes mini.form url to instantsearch route
     * if enabled
     *
     * @param \Magento\Search\Helper\Data\Interceptor $subject
     * @param \Closure $proceed
     *
     * @return string
     */
    public function aroundGetResultUrl(
        \Magento\Search\Helper\Data\Interceptor $subject,
        \Closure $proceed
    ) {
        if ($this->is_mini_form_rewrite_enabled) {
            return $this->_urlBuilder->getUrl('instantsearchplus/result', []);
        } else {
            return $proceed();
        }
    }
}