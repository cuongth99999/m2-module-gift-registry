<?php

namespace Magenest\GiftRegistry\Setup\Patch\Data;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class UpdateGiftOptions implements DataPatchInterface, PatchVersionInterface
{

    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var Json
     */
    protected $json;

    /**
     * UpdateGiftOptions constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param Json $json
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Json $json
    ){
        $this->moduleDataSetup = $moduleDataSetup;
        $this->json = $json;
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
        $setup = $this->moduleDataSetup;
        $setup->startSetup();

        $this->updateGiftOptions($setup);

        $setup->endSetup();
    }

    protected function updateGiftOptions($setup)
    {
        $connection = $setup->getConnection();
        $giftRegistryTable = $setup->getTable('magenest_giftregistry');
        $giftRegistries = $connection->fetchAll('SELECT * from ' . $giftRegistryTable);

        if(!empty($giftRegistries)){
            foreach ($giftRegistries as $giftRegistry)
            {
                $newGiftOptions = $this->updateFieldIds($giftRegistry);
                if($giftRegistry['gift_options'] != $newGiftOptions){
                    $connection->update(
                        $giftRegistryTable,
                        ['gift_options' => $newGiftOptions],
                        "`gift_id` LIKE '" . $giftRegistry['gift_id'] . "'"
                    );
                }
            }
        }
    }

    protected function updateFieldIds($giftRegistry)
    {
        if (!empty($giftRegistry['gift_options'])){
            $giftOptions = $this->json->unserialize($giftRegistry['gift_options']);
            if(!empty($giftOptions)){
                foreach ($giftOptions as $type => $giftOption)
                {
                    foreach ($giftOption as $key => $value)
                    {
                        if(substr($key, 0, 6) != 'field_'){
                            unset($giftOptions[$type][$key]);
                            $giftOptions[$type]['field_' . $key] = $value;
                        }
                    }
                }
            }

            return $this->json->serialize($giftOptions);
        }
    }

    public static function getVersion()
    {
        return '103.2.0';
    }
}
