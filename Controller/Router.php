<?php
/**
 * Router.php File
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
namespace Autocompleteplus\Autosuggest\Controller;

/**
 * Router
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
class Router implements \Magento\Framework\App\RouterInterface
{

    /**
     * @var \Magento\Framework\App\ActionFactory provides redirect
     */
    protected $actionFactory;

    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->actionFactory = $actionFactory;
        $this->_response = $response;
    }

    /**
     * Validate and Match
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {

        $identifier = trim($request->getPathInfo(), '/');
        
        if (strpos($identifier, 'instantsearchplus') === false) {
            return null;
        }

        /*
         * We will search “instantsearchplus/result” words
         * and make forward sending third
         * parameter as query
         */
        if (strpos($identifier, 'instantsearchplus/result') !== false) {
        
            $matches = array();

            if (preg_match(
                '/^(?:[^\/]+)\/(?:[^\/]+)\/?([^\/]+)?$/',
                $identifier,
                $matches
            ) !== false) {
                $request->setModuleName('instantsearchplus')
                    ->setControllerName('result')
                    ->setActionName('index')
                    ->setParam('q', $matches[1]);
            } else {
                $request->setModuleName('instantsearchplus')
                    ->setControllerName('result')
                    ->setActionName('index');
            }
        } else {
            //There is no match
            return null;
        }

        /*
         * We have match and now we will forward action
         */
        return $this->actionFactory->create(
            'Magento\Framework\App\Action\Forward',
            ['request' => $request]
        );
    }
}