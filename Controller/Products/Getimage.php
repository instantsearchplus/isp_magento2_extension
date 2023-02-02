<?php

namespace Autocompleteplus\Autosuggest\Controller\Products;

use Magento\Store\Model\ScopeInterface;

class Getimage extends \Autocompleteplus\Autosuggest\Controller\Products
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Api
     */
    protected $apiHelper;

    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Product\Xml\Generator
     */
    protected $xmlGenerator;

    public function __construct(
        \Magento\Framework\App\Action\Context                      $context,
        \Magento\Store\Model\StoreManagerInterface                 $storeManagerInterface,
        \Magento\Framework\Controller\Result\JsonFactory           $resultJsonFactory,
        \Magento\Catalog\Helper\Image                              $imageHelper,
        \Autocompleteplus\Autosuggest\Helper\Api                   $apiHelper,
        \Autocompleteplus\Autosuggest\Helper\Product\Xml\Generator $xmlGenerator
    )
    {
        $this->storeManager = $storeManagerInterface;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->imageHelper = $imageHelper;
        $this->apiHelper = $apiHelper;
        $this->xmlGenerator = $xmlGenerator;

        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $authKey = $this->getRequest()->getParam('authentication_key');
        $productId = $this->getRequest()->getParam('product_id');
        $storeId = $this->getRequest()->getParam('store', 1);
        $imageId = $this->getRequest()->getParam('image_id');
        $resize_to_width = $this->getRequest()->getParam('resize_to_width', 500);

        if ($this->apiHelper->getApiAuthenticationKey() != $authKey) {
            $responseData = ['status' => 'error: Authentication failed'];
        } else {
            $product = $this->get_product($storeId, $productId);
            $store = $this->storeManager->getStore();

            $image = $this->imageHelper->init($product, $imageId)->setImageFile($product->getImage());
            $image_url = $this->imageHelper->init($product, $imageId)->setImageFile($product->getImage())->getUrl();
            $resized_image_url = $this->imageHelper->init($product, $imageId)->setImageFile($product->getImage())->resize($resize_to_width)->getUrl();

            $thumbnail = $this->imageHelper->init($product, $imageId)->setImageFile($product->getThumbnail());
            $thumbnail_url = $this->imageHelper->init($product, $imageId)->setImageFile($product->getThumbnail())->getUrl();
            $resized_thumbnail_url = $this->imageHelper->init($product, $imageId)->setImageFile($product->getThumbnail())->resize($resize_to_width)->getUrl();

            $small_image = $this->imageHelper->init($product, $imageId)->setImageFile($product->getSmallImage());
            $small_image_url = $this->imageHelper->init($product, $imageId)->setImageFile($product->getSmallImage())->getUrl();
            $resized_small_image_url = $this->imageHelper->init($product, $imageId)->setImageFile($product->getSmallImage())->resize($resize_to_width)->getUrl();

            $responseData = ['original_image_width' => $image->getWidth(), 'original_image_height' => $image->getHeight(), 'image_url' => $image_url,
                'resized_image_url' => $resized_image_url, 'original_thumbnail_width' => $thumbnail->getWidth(), 'original_thumbnail_height' => $thumbnail->getHeight(),
                'thumbnail_url' => $thumbnail_url, 'resized_thumbnail_url' => $resized_thumbnail_url, 'original_small_image_width' => $small_image->getWidth(),
                'original_small_image_height' => $small_image->getHeight(), 'small_image_url' => $small_image_url, 'resized_small_image_url' => $resized_small_image_url];

            $responseData['image_from_product'] = $product->getImage();
            $responseData['thumbnail_from_product'] = $product->getThumbnail();
            $responseData['small_image_from_product'] = $product->getSmallImage();

            $responseData['generated_image_url'] = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
        }

        return $result->setData($responseData);
    }

    /**
     * @param integer $storeId
     * @param integer $productId
     * @return \Magento\Catalog\Model\Product
     */
    public function get_product($storeId, $productId)
    {
        $productCollection = $this->xmlGenerator->getProductCollection(false);
        $this->xmlGenerator->setStoreId($storeId);
        if (is_numeric($storeId)) {
            $productCollection->addStoreFilter($storeId);
            $productCollection->setStoreId($storeId);
        }

        $productCollection->addAttributeToFilter('entity_id', ['in' => [$productId]]);
        $product = null;

        foreach ($productCollection as $product) {
            $product->getTypeInstance()->setStoreFilter($this->storeManager->getStore(), $product);
            break;
        }

        return $product;
    }
}
