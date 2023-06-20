<?php

namespace Magenest\GiftRegistry\Setup\Patch\Data;


use Magento\Framework\DB\Ddl\Table as Table;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallGiftRegistrySchema implements DataPatchInterface, PatchVersionInterface
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
        return [];
    }

    public function getAliases()
    {
       return [];
    }

    public function apply()
    {
        $installer = $this->setup;

        $installer->startSetup();

        $table = $installer->getConnection()->newTable(
            $installer->getTable('magenest_giftregistry')
        )->addColumn(
            'gift_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Gift Registry ID'
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Customer ID'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            '64k',
            [],
            'Title'
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
            'privacy',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Privacy'
        )->addColumn(
            'password',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Password'
        )->addColumn(
            'show_in_search',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Show In Search'
        )->addColumn(
            'shipping_address',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Shipping Address'
        )->addColumn(
            'gift_options',
            Table::TYPE_TEXT,
            null,
            [],
            'Gift Options'
        )->setComment(
            'Gift Registry main Table'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('magenest_giftregistry_item')
        )->addColumn(
            'gift_item_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Gift Registry item ID'
        )->addColumn(
            'gift_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Gift Registry ID'
        )->addColumn(
            'product_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => '0', 'nullable' => false],
            'Product ID'
        )->addColumn(
            'product_name',
            Table::TYPE_TEXT,
            '64k',
            [],
            'Product Name'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => true],
            'Store ID'
        )->addColumn(
            'added_at',
            Table::TYPE_TIMESTAMP,
            null,
            [],
            'Add date and time'
        )->addColumn(
            'description',
            Table::TYPE_TEXT,
            '64k',
            [],
            'Short description of  item'
        )->addColumn(
            'qty',
            Table::TYPE_INTEGER,
            '11',
            ['nullable' => false,'unsigned' => true],
            'Qty'
        )->addColumn(
            'duplicate',
            Table::TYPE_INTEGER,
            '11',
            ['nullable' => false,'unsigned' => true, 'default' => 0],
            'Duplicate'
        )->addColumn(
            'received_qty',
            Table::TYPE_INTEGER,
            '11',
            ['nullable' => false,'unsigned' => true],
            'Received Qty'
        )->addColumn(
            'invoiced_qty',
            Table::TYPE_INTEGER,
            '11',
            ['nullable' => false,'unsigned' => true],
            'Invoiced Qty'
        )->addColumn(
            'priority',
            Table::TYPE_INTEGER,
            '11',
            ['nullable' => false,'unsigned' => true],
            'Priority'
        )->addColumn(
            'note',
            Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Note'
        )
            ->addColumn(
                'final_price',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false],
                'Price'
            )
            ->addColumn(
                'buy_request',
                Table::TYPE_TEXT,
                '64k',
                ['nullable' => false],
                'Buyer Request'
            )
            ->setComment(
                'Gift Registry items'
            );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'giftregistry_item_option'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('magenest_giftregistry_item_option')
        )->addColumn(
            'option_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Option Id'
        )->addColumn(
            'gift_item_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Item Id'
        )->addColumn(
            'product_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Product Id of gift registry item'
        )->addColumn(
            'code',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Code of the option'
        )->addColumn(
            'value',
            Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Value Of the Option'
        )->setComment(
            'Gift Registry Item Option Table'
        );
        $installer->getConnection()->createTable($table);

        /* create table magenest_registry_registrant */
        $table = $installer->getConnection()->newTable($installer->getTable('magenest_giftregistry_registrant'))
            ->addColumn(
                'registrant_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Owner ID'
            )
            ->addColumn(
                'email',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Registrant Email'
            )->addColumn(
                'firstname',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'First Name'
            )->addColumn(
                'lastname',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Last Name'
            )->addColumn(
                'giftregistry_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Gift Registry Id which is foreign key of the table'
            )
            ->addColumn(
                'created_time',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Time created'
            )
            ->addColumn(
                'updated_time',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Time update'
            )
            ->setComment('Table Gift Registrant');

        $installer->getConnection()->createTable($table);

        /* add table magenest_registry_event_type */
        $table = $installer->getConnection()->newTable($installer->getTable('magenest_giftregistry_event_type'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id Event Type'
            )
            ->addColumn(
                'event_type',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Event Type'
            )
            ->addColumn(
                'event_title',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Event Title'
            )
            ->addColumn(
                'languages',
                Table::TYPE_TEXT,
                '64k',
                ['nullable' => true],
                'For multiple stores'
            )
            ->addColumn(
                'image',
                Table::TYPE_TEXT,
                '64k',
                ['nullable' => true],
                'Background Image'
            )
            ->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Status'
            )
            ->setComment('Table Gift Registry Type');
        $installer->getConnection()->createTable($table);

        /* add table magenest_registry_order */
        $table = $installer->getConnection()->newTable($installer->getTable('magenest_giftregistry_order'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id Order Registry'
            )
            ->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Id Increment'
            )->addColumn(
                'status',
                Table::TYPE_TEXT,
                null,
                [ 'nullable' => true],
                'Status'
            )
            ->addColumn(
                'giftregistry_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Hold Foreign Key'
            )
            ->setComment('Table Order Registry');
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }

    public static function getVersion()
    {
        return "103.2.1";
    }

}
