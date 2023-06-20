<?php

namespace Magenest\GiftRegistry\Setup\Patch\Data;


use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeGiftRegistryData implements DataPatchInterface, PatchVersionInterface
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
            \Magenest\GiftRegistry\Setup\Patch\Schema\UpgradeGiftRegistrySchema::class
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
            $this->updateEventTypeData($setup);
            $this->setOldRegistryExpired($setup);
        }
        $setup->endSetup();
    }

    /**
     * @param $setup
     */
    private function updateEventTypeData($setup)
    {
        $connection = $setup->getConnection();
        $connection->update(
            $setup->getTable('magenest_giftregistry_event_type'),
            ['fields' => '["about_event","general_info","registrant_info","privacy","shipping_address_info"]']
        );
        $connection->update(
            $setup->getTable('magenest_giftregistry_event_type'),
            ['additions_field' => '[{"type":"field","id":"0","label":"Baby\u0027s Name"}]'],
            "`event_type` LIKE 'babygift'"
        );
        $connection->update(
            $setup->getTable('magenest_giftregistry_event_type'),
            ['additions_field' => '[{"type":"field","id":"0","label":"Husband\u0027s Name"},{"type":"field","id":"1","label":"Wife\u0027s Name"}]'],
            "`event_type` LIKE 'weddinggift'"
        );
        $connection->update(
            $setup->getTable('magenest_giftregistry_event_type'),
            ['additions_field' => '[{"type":"date","id":"0","label":"Born Date"},{"type":"field","id":"1","label":"Name Birthday"}]'],
            "`event_type` LIKE 'birthdaygift'"
        );
        $connection->update(
            $setup->getTable('magenest_giftregistry_event_type'),
            ['additions_field' => '[{"type":"area","id":"0","label":"Add a greeting to friends and family at the top of your registry"}]'],
            "`event_type` LIKE 'christmasgift'"
        );
    }

    /**
     * @param $setup
     */
    private function setOldRegistryExpired($setup)
    {
        $connection = $setup->getConnection();
        $yesterday = date("Y-m-d 23:59:59", time() - 60 * 60 * 24);
        $connection->update(
            $setup->getTable('magenest_giftregistry'),
            ['is_expired' => 1]
        );
        $connection->update(
            $setup->getTable('magenest_giftregistry'),
            ['is_expired' => 0],
            "`date` > '$yesterday'"
        );
    }

    public static function getVersion()
    {
        return "103.2.1";
    }

}
