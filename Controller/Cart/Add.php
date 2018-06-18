<?php
/**
 * Add.php File
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
namespace Autocompleteplus\Autosuggest\Controller\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Exception\NoSuchEntityException;
/**
 * Add
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
class Add extends \Magento\Checkout\Controller\Cart\Add
{

    /**
     * Outputs response
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Add constructor.
     *
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $productRepository
        );
    }

    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $responseData = [
                'success' => false,
                'message' => 'Form keys do not match!'
            ];
            $this->messageManager->addErrorMessage('Form keys do not match!');
            return $result->setData($responseData);
        }

        $params = $this->getRequest()->getParams();
        $productUrl = '';
        try {
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    [
                        'locale' => $this->_objectManager
                            ->get('Magento\Framework\Locale\ResolverInterface')
                            ->getLocale()
                    ]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');
            $productUrl = $product->getProductUrl(true);
            /**
             * Check product availability
             */
            if (!$product) {
                $responseData = [
                    'success' => false,
                    'message' => 'Product is not valid!'
                ];
                $this->messageManager->addErrorMessage('Product is not valid!');
                return $result->setData($responseData);
            }

            $this->cart->addProduct($product, $params);
            if (!empty($related)) {
                $this->cart->addProductsByIds(explode(',', $related));
            }

            $this->cart->save();

            /**
             * @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
             */
            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                [
                    'product' => $product,
                    'request' => $this->getRequest(),
                    'response' => $this->getResponse()
                ]
            );
            $message = __(
                'You added %1 to your shopping cart.',
                $product->getName()
            );
            $responseData = [
                'success' => true,
                'message' => $message
            ];
            $this->messageManager->addSuccessMessage($message);
            return $result->setData($responseData);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNoticeMessage(
                    $this->_objectManager->get('Magento\Framework\Escaper')
                        ->escapeHtml($e->getMessage())
                );
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addErrorMessage(
                        $this->_objectManager->get('Magento\Framework\Escaper')
                            ->escapeHtml($message)
                    );
                }
            }
            $responseData = [
                'success' => false,
                'message' => $this->_objectManager->get('Magento\Framework\Escaper')
                    ->escapeHtml($e->getMessage())
            ];
            if ($productUrl != '') {
                $responseData['url'] = $productUrl;
            }
            return $result->setData($responseData);
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->messageManager->addErrorMessage(
                'We can\'t add this item to your cart right now.'
            );
            $responseData = [
                'success' => false,
                'message' => $this->_objectManager->get('Magento\Framework\Escaper')
                    ->escapeHtml(
                        __('We can\'t add this item to your cart right now.')
                    )
            ];
            return $result->setData($responseData);
        }
    }
}