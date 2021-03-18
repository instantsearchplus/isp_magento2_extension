<?php
/**
 * UpgradeData File
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

namespace Autocompleteplus\Autosuggest\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;


/**
 * UpgradeData
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
class UpgradeSchema implements UpgradeSchemaInterface
{
    protected $resourceConfig;
    protected $scopeConfig;
    protected $helper;
    protected $api;

    public function _construct(
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Autocompleteplus\Autosuggest\Helper\Api $api
    ) {
        $this->resourceConfig = $resourceConfig;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->api = $api;
    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if(version_compare($context->getVersion(), '4.7.22', '<')) {
            $batchTable = $setup->getConnection()
                ->newTable($setup->getTable('autosuggest_batch'))
                ->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Product ID'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Store ID'
                )
                ->addColumn(
                    'update_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Update Time'
                )
                ->addColumn(
                    'action',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    250,
                    ['nullable' => false],
                    'Batch Action'
                )
                ->addColumn(
                    'sku',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    250,
                    ['nullable' => false],
                    'Product SKU'
                )
                ->addIndex(
                    $setup->getIdxName('autosuggest_batch_index', ['product_id', 'store_id']),
                    ['product_id', 'store_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_PRIMARY]
                )
                ->addIndex(
                    $setup->getIdxName('autosuggest_batch_update_dt_index', ['update_date', 'store_id']),
                    ['update_date', 'store_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                )
                ->setComment('InstantSearch+ Batches');
            $setup->run(sprintf('DROP TABLE IF EXISTS %s;', $setup->getTable('autosuggest_batch')));
            $setup->getConnection()->createTable($batchTable);
            $err_msg = '';
            try {
                if (!$this->api) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager
                    $this->api = $objectManager->get('\Autocompleteplus\Autosuggest\Helper\Api');
                    $auth_key = $this->api->getApiAuthenticationKey();
                    $uuid = $this->api->getApiUUID();
                    if ($auth_key != null && $uuid != null) {
                        $web_hook_url = $this->api->getApiEndpoint() . '/reindex_after_update_catalog';
                        $this->api->setUrl($web_hook_url);

                        $params = [
                            'isp_platform' => 'magento',
                            'auth_key' => $auth_key,
                            'uuid' => $uuid,
                        ];

                        $response = $this->api->buildRequest($params);
                    }

                }

            } catch (\Exception $e) {
                $err_msg = $e->getMessage();
            }

        } elseif (version_compare($context->getVersion(), '4.7.29', '<')) {
            $batchTable = $setup->getConnection()
                ->newTable($setup->getTable('autosuggest_price'))
                ->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Product ID'
                )
                ->addColumn(
                    'website_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Website ID'
                )
                ->addColumn(
                    'final_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Final Price'
                )
                ->addColumn(
                    'update_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Update Time'
                )
                ->addColumn(
                    'is_updated',
                    \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false],
                    'Is updated'
                )
                ->addIndex(
                    $setup->getIdxName('autosuggest_price_index', ['product_id', 'website_id']),
                    ['product_id', 'website_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_PRIMARY]
                )
                ->setComment('InstantSearch+ Prices');
            $setup->getConnection()->createTable($batchTable);
        }
        $setup->endSetup();
    }
}
