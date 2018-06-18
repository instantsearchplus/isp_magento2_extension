<?php
/**
 * Generator File
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

namespace Autocompleteplus\Autosuggest\Helper\Category\Json;

use Magento\Framework\App;

/**
 * Generator
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
class Generator extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $storeManager;
    protected $categoryFactory;
    protected $categoryTree;
    protected $xmlGenerator;
    protected $helper;
    protected $categoryRepository;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
    ) {
        $this->storeManager = $storeManagerInterface;
        $this->categoryFactory = $categoryFactory;
        $this->categoryTree = $categoryTree;
        $this->helper = $helper;
        $this->categoryRepository = $categoryRepository;
        parent::__construct($context);
    }

    public function getContextStore($storeId)
    {
        return $this->storeManager->getStore($storeId);
    }

    public function getRootCategoryId()
    {
        return $this->storeManager->getStore()->getRootCategoryId();
    }

    public function nodeToArray($node, $mediaUrl, $baseUrl, $store)
    {
        $thumbnail = '';

        $category = $this->categoryRepository->get($node->getId(), $store);

        $result = [
            'category_id' => $node->getId(),
            'image' => sprintf('%scatalog/category/%s', $mediaUrl, $node->getImage()),
            'thumbnail' => $thumbnail,
            'description' => strip_tags($node->getDescription()),
            'parent_id'   => $node->getParentId(),
            'name'        => $node->getName(),
            'url_path'    => $category->getUrl(),
            'is_active'   => $node->getIsActive(),
            'children'    => [],
        ];

        foreach ($node->getChildren() as $child) {
            $result['children'][] = $this->nodeToArray($child, $mediaUrl, $baseUrl, $store);
        }

        return $result;
    }

    public function loadTree($storeId)
    {
        $tree = $this->categoryTree->load();
        $parentId = $this->getContextStore($storeId)->getRootCategoryId();

        $root = $tree->getNodeById($parentId);

        if ($root && $root->getId() == 1) {
            $root->setName(__('Root'));
        }

        $collection = $this->categoryFactory->create()->getCollection();
        $collection
            ->setStoreId($storeId)
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('is_active', ['eq'  =>  true]);

        $tree->addCollectionData($collection, true);
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        return $this->nodeToArray($root, $mediaUrl, $baseUrl, $storeId);
    }

    public function getJson($storeId)
    {
        return json_encode($this->loadTree($storeId));
    }
}
