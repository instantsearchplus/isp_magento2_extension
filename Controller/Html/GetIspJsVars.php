<?php

/**
 * GetIspJsVars.php File
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
 * @copyright 2017 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */
namespace Autocompleteplus\Autosuggest\Controller\Html;

/**
 * Getvars
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
 * @copyright 2017 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */
class GetIspJsVars extends \Autocompleteplus\Autosuggest\Controller\Html
{
    protected $injectorHelper;

    /**
     * @var Magento\Framework\App\ResponseInterface
     */
    protected $response;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Autocompleteplus\Autosuggest\Helper\Html\Injector $injectorHelper
    ) {
        $this->injectorHelper = $injectorHelper;
        $this->response = $context->getResponse();
        parent::__construct($context);
    }

    /**
     * Method execute
     *
     * @return mixed
     */
    public function execute()
    {
        $vars = $this->injectorHelper->getAdditionalParameters();
        $jsContent = 'var acp_magento_qvars = ' . json_encode($vars) . ';';
        $jsContent .= '
        if (typeof m2_assign_js_vars == "function")
                        { m2_assign_js_vars(); }';
        return $this->response->setHeader('Content-Type', 'text/javascript;')
            ->setContent($jsContent);
    }
}
