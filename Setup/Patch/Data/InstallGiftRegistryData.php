<?php

namespace Magenest\GiftRegistry\Setup\Patch\Data;


use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallGiftRegistryData implements DataPatchInterface, PatchVersionInterface
{
    private $setup;
    public function __construct(SchemaSetupInterface $setup)
    {
        $this->setup = $setup;
    }

    public static function getDependencies()
    {
       return [
         InstallGiftRegistrySchema::class
       ];
    }

    public function getAliases()
    {
       return [];
    }

    public function apply()
    {
        $setup = $this->setup;
        $setup->startSetup();
        $connection = $setup->getConnection();

        $connection->insert(
            $setup->getTable('magenest_giftregistry_event_type'),
            ['event_type' => 'babygift','event_title'=> 'Baby Gift', 'status' => 1]
        );
        $connection->insert(
            $setup->getTable('magenest_giftregistry_event_type'),
            ['event_type' => 'weddinggift','event_title'=> 'Wedding Gift','status' => 1]
        );
        $connection->insert(
            $setup->getTable('magenest_giftregistry_event_type'),
            ['event_type' => 'birthdaygift','event_title'=> 'Birthday Gift', 'status' => 1]
        );
        $connection->insert(
            $setup->getTable('magenest_giftregistry_event_type'),
            ['event_type' => 'christmasgift','event_title'=> 'Christmas Gift', 'status' => 1]
        );
        $setup->endSetup();
    }

    public static function getVersion()
    {
        return "103.2.1";
    }

}
