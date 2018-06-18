<?php
/**
 * InstallSchema File
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

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * InstallSchema File
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
class InstallSchema implements InstallSchemaInterface
{

    protected $scopeConfig;
    protected $api;
    protected $helper;
    protected $logger;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Autocompleteplus\Autosuggest\Helper\Api $api,
        \Psr\Log\LoggerInterface $logger,
        \Autocompleteplus\Autosuggest\Helper\Data $helper)
    {
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->api = $api;
        $this->logger = $logger;
    }

    public function install(SchemaSetupInterface $setup,
                            ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $batchTable = $installer->getConnection()
            ->newTable($installer-> getTable('autosuggest_batch'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Batch ID'
            )
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
                $installer->getIdxName('autosuggest_batch_index', ['product_id', 'store_id']),
                ['product_id', 'store_id']
            )
            ->setComment('InstantSearch+ Batches');
        $installer->getConnection()->createTable($batchTable);

        $pusherTable = $installer->getConnection()
            ->newTable($installer-> getTable('autosuggest_pusher'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Push ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store ID'
            )
            ->addColumn(
                'to_send',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Amount to send'
            )
            ->addColumn(
                'offset',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Collection Offset'
            )
            ->addColumn(
                'total_batches',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Total Batches'
            )
            ->addColumn(
                'batch_number',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Batch Number'
            )
            ->addColumn(
                'sent',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Total Sent'
            )
            ->addIndex(
                $installer->getIdxName('instantsearch_pusher_index', ['entity_id', 'store_id']),
                ['entity_id', 'store_id']
            )
            ->setComment('InstantSearch+ Pusher');
        $installer->getConnection()->createTable($pusherTable);

        $notificationsTable = $installer->getConnection()
            ->newTable($installer-> getTable('instantsearch_notification'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Push ID'
            )
            ->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Notification type'
            )
            ->addColumn(
                'subject',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Notification Subject'
            )
            ->addColumn(
                'message',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Notification Message'
            )
            ->addColumn(
                'timestamp',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Total Batches'
            )
            ->addColumn(
                'is_active',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Is Notification Active'
            )
            ->addIndex(
                $setup->getIdxName(
                    $installer->getTable('autocompleteplus_notifications'),
                    ['type'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['type'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT]
            )
            ->addIndex(
                $installer->getIdxName('IDX_IS_ACTIVE', ['is_active']),
                ['is_active']
            )
            ->setComment('InstantSearch+ Notifications');
        $installer->getConnection()->createTable($notificationsTable);

        $installer->endSetup();

        $params = [
            'site'       => $this->scopeConfig->getValue(
                                'web/unsecure/base_url',
                                ScopeInterface::SCOPE_STORE
                                ),
            'email'      => $this->scopeConfig->getValue(
                                'trans_email/ident_support/email',
                                ScopeInterface::SCOPE_STORE
                                ),
            'f'          => $this->helper->getVersion(),
            'multistore' => json_encode($this->helper->getMultiStoreData())
        ];

        $uuid = $this->scopeConfig->getValue('autosuggest/api/uuid',ScopeInterface::SCOPE_STORE);

        if ($uuid != null) {
            $params['uuid'] = $uuid;
        }

        $this->api->setUrl($this->api->getApiEndpoint() . '/install');
        $this->api->setRequestType(\Zend_Http_Client::POST);

        try {
            $response = $this->api->buildRequest($params);

            $responseData = json_decode($response->getBody());
            if ($responseData) {
                $this->api->setApiUUID($responseData->uuid);
                $this->api->setApiAuthenticationKey($responseData->authentication_key);
            }

        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

    }
}
