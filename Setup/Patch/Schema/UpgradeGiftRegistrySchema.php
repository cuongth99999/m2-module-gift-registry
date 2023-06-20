<?php

namespace Magenest\GiftRegistry\Setup\Patch\Schema;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeGiftRegistrySchema implements SchemaPatchInterface, PatchVersionInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $setup;
    public function __construct(SchemaSetupInterface $setup)
    {
        $this->setup = $setup;
    }

    public static function getDependencies()
    {
        return [
            \Magenest\GiftRegistry\Setup\Patch\Data\InstallGiftRegistrySchema::class,
            \Magenest\GiftRegistry\Setup\Patch\Data\InstallGiftRegistryData::class
        ];
    }

    public function getAliases()
    {
        return [];
    }

    public function apply()
    {
        $setup = $this->setup;
        $context = $this;
        $setup->startSetup();
        if (version_compare($context->getVersion(), '103.2.1') <= 0) {
            $setup->getConnection()->addForeignKey(
                $setup->getFkName(
                    'magenest_giftregistry_item',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                $setup->getTable('magenest_giftregistry_item'),
                "product_id",
                $setup->getTable('catalog_product_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            );
        }

        if (version_compare($context->getVersion(), '103.2.1') <= 0) {
            $this->updateUpdatedTimeField($setup);
        }
        if (version_compare($context->getVersion(), '103.2.1') <= 0) {
            $this->updateIsExpired($setup);
        }
        if (version_compare($context->getVersion(), '103.2.1') <= 0) {
            $this->updateDateField($setup);
        }
        if (version_compare($context->getVersion(), '103.2.1') <= 0) {
            $this->updateTableGiftRegistryType($setup);
            $this->updateTableGiftRegistryOrder($setup);
            $this->createTableGiftRegistryTmp($setup);
        }
        if (version_compare($context->getVersion(), '103.2.1') <= 0) {
            $this->updateTableOrder($setup);
            $this->createTableGuest($setup);
            $this->updateTableGiftRegistry($setup);
            $this->updateTableGiftRegistryTmp($setup);
            $this->addUniqueConstraintEventType($setup);
            $this->addUniqueConstraintOrderId($setup);
        }
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function updateUpdatedTimeField(SchemaSetupInterface $setup)
    {
        $data = [
            'magenest_giftregistry' => 'updated_at',
            'magenest_giftregistry_registrant' => 'updated_time'
        ];
        foreach ($data as $table => $column) {
            $setup->getConnection()->modifyColumn(
                $setup->getTable($table),
                $column,
                [
                    'type' => Table::TYPE_TIMESTAMP,
                    'default' => Table::TIMESTAMP_INIT_UPDATE
                ]
            );
        }
    }

    /**
     * @param $setup
     */
    private function updateIsExpired($setup)
    {
        //add new column
        $setup->getConnection()->addColumn(
            $setup->getTable('magenest_giftregistry'),
            'is_expired',
            [
                'type' => Table::TYPE_BOOLEAN,
                'nullable' => true,
                'comment' => 'is_gift_registry_expired'
            ]
        );
    }

    /**
     * @param $setup
     */
    private function updateDateField($setup)
    {
        $table = 'magenest_giftregistry';
        $setup->getConnection()->modifyColumn(
            $setup->getTable($table),
            'date',
            [
                'type' => Table::TYPE_DATE,
            ]
        );
    }

    /**
     * @param $setup
     */
    private function updateTableGiftRegistryType($setup)
    {
        $table = $setup->getTable('magenest_giftregistry_event_type');
        $setup->getConnection()->addColumn(
            $table,
            'description',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'description_registry'
            ]
        );
        $setup->getConnection()->addColumn(
            $table,
            'fields',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'list_fields_register_registry'
            ]
        );
        $setup->getConnection()->addColumn(
            $table,
            'additions_field',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'list_fields_register_registry'
            ]
        );
        $setup->getConnection()->addColumn(
            $table,
            'thumnail',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'thumnail_image'
            ]
        );
        $setup->getConnection()->addColumn(
            $table,
            'css_style',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'css_style_view_giftregistry'
            ]
        );
    }

    /**
     * @param $setup
     */
    public function updateTableGiftRegistryOrder($setup)
    {
        $table = $setup->getTable('magenest_giftregistry_order');
        $setup->getConnection()->addColumn(
            $table,
            'message',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'message'
            ]
        );
        $setup->getConnection()->addColumn(
            $table,
            'sender',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'sender'
            ]
        );
        $setup->getConnection()->addColumn(
            $table,
            'quote_id',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'quote_id'
            ]
        );
        $setup->getConnection()->addColumn(
            $table,
            'is_notification',
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'default' => 0,
                'comment' => 'is_notification'
            ]
        );
        $setup->getConnection()->changeColumn(
            $table,
            'order_id',
            'order_id',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'order_id'
            ]
        );
    }

    /**
     * @param $setup
     */
    public function updateTableGiftRegistry($setup)
    {
        $table = $setup->getTable('magenest_giftregistry');
        $setup->getConnection()->addColumn(
            $table,
            'image_location',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'image_location'
            ]
        );
        $setup->getConnection()->addColumn(
            $table,
            'image_joinus',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'image_joinus'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $table,
            'description',
            [
                'type' => Table::TYPE_TEXT,
                'length' => '16M',
                'nullable' => true,
                'comment' => 'Description'
            ]
        );
    }

    /**
     * @param $setup
     */
    public function createTableGiftRegistryTmp($setup)
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable('magenest_giftregistry_tmp')
        )->addColumn(
            'gift_id_tmp',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'Gift Registry Id Tmp'
        )->addColumn(
            'gift_id',
            Table::TYPE_INTEGER,
            null,
            [
                'nullable' => false
            ],
            'Gift Registry Id'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            '64k',
            [],
            'Title'
        )->addColumn(
            'registrant_email',
            Table::TYPE_TEXT,
            '64k',
            [],
            'Registrant Email'
        )->addColumn(
            'type',
            Table::TYPE_TEXT,
            '64k',
            [],
            'Type'
        )->addColumn(
            'image',
            Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Background Image'
        )->addColumn(
            'image_location',
            Table::TYPE_TEXT,
            null,
            [
                'nullable' => true
            ],
            'Image Location'
        )->addColumn(
            'image_joinus',
            Table::TYPE_TEXT,
            null,
            [
                'nullable' => true
            ],
            'Image Join Us'
        )->addColumn(
            'location',
            Table::TYPE_TEXT,
            255,
            [],
            'Location'
        )->addColumn(
            'date',
            Table::TYPE_DATETIME,
            255,
            [],
            'Date'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            [],
            'Last updated date'
        )->addColumn(
            'description',
            Table::TYPE_TEXT,
            '16M',
            [],
            'Description'
        )->addColumn(
            'shipping_address',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Shipping Address'
        )->addColumn(
            'is_expired',
            Table::TYPE_BOOLEAN,
            null,
            [
                'nullable' => true
            ],
            'Is Expired'
        )->addColumn(
            'gift_options',
            Table::TYPE_TEXT,
            null,
            [],
            'Gift Options'
        )->setComment(
            'Gift Registry main Table'
        );
        $setup->getConnection()->createTable($table);
    }

    /**
     * @param $setup
     */
    public function updateTableGiftRegistryTmp($setup)
    {
        $table = $setup->getTable('magenest_giftregistry_tmp');
        $setup->getConnection()->modifyColumn(
            $table,
            'description',
            [
                'type' => Table::TYPE_TEXT,
                'length' => '16M',
                'nullable' => true,
                'comment' => 'Description'
            ]
        );
    }

    /**
     * @param $setup
     */
    public function updateTableOrder($setup)
    {
        $table = $setup->getTable('magenest_giftregistry_order');
        $setup->getConnection()->addColumn(
            $table,
            'check_email',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'check email thanks'
            ]
        );
        $setup->getConnection()->addColumn(
            $table,
            'incognito',
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'default' => '0',
                'comment' => 'Allow customers to give anonymous gifts'
            ]
        );
    }

    /**
     * @param $setup
     */
    public function createTableGuest($setup)
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable('magenest_guest')
        )->addColumn(
            'guest_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Guest ID'
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true],
            'Customer ID'
        )->addColumn(
            'customer_email',
            Table::TYPE_TEXT,
            null,
            ['unsigned' => true],
            'Customer Email'
        )->addColumn(
            'giftregistry_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Gift Registry Id which is foreign key of the table'
        )->addColumn(
            'referral_checker',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true, 'default' => '0'],
            '0->unsent; 1->sent'
        )->setComment(
            'Guest table of gift registrations'
        );
        $setup->getConnection()->createTable($table);
    }

    /**
     * @param $setup
     */
    private function addUniqueConstraintEventType($setup)
    {
        $table = $setup->getTable('magenest_giftregistry_event_type');
        $setup->getConnection()->modifyColumn(
            $table,
            'event_type',
            [
                'type' => Table::TYPE_TEXT,
                'length' => '255',
                'nullable' => false,
                'comment' => 'Event Type'
            ]
        );
        $setup->getConnection()->addIndex(
            $table,
            $setup->getIdxName(
                $table,
                ['event_type'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['event_type'],
            AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }

    /**
     * @param $setup
     */
    private function addUniqueConstraintOrderId($setup)
    {
        $table = $setup->getTable('magenest_giftregistry_order');
        $setup->getConnection()->modifyColumn(
            $table,
            'order_id',
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Order ID'
            ]
        );
        $setup->getConnection()->addIndex(
            $table,
            $setup->getIdxName(
                $table,
                ['order_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['order_id'],
            AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }

    public static function getVersion()
    {
        return "103.2.1";
    }

}
