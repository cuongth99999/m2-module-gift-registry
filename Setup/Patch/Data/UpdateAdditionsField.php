<?php

namespace Magenest\GiftRegistry\Setup\Patch\Data;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Setup\Model\ModuleContext;

class UpdateAdditionsField implements DataPatchInterface, PatchVersionInterface
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
     * UpdateAdditionsField constructor.
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

        $this->updateEventTypeData($setup);

        $setup->endSetup();
    }

    /**
     * @param $setup
     */
    protected function updateEventTypeData($setup)
    {
        $connection = $setup->getConnection();
        $eventTypeTable = $setup->getTable('magenest_giftregistry_event_type');
        $eventTypeDatas = $connection->fetchAll('SELECT * from ' . $eventTypeTable);

        if (!empty($eventTypeDatas)){
            foreach ($eventTypeDatas as $eventTypeData)
            {
                $newAdditionsField = $this->updateAdditionsField($eventTypeData);
                if( $eventTypeData['additions_field'] != $newAdditionsField){
                    $connection->update(
                        $eventTypeTable,
                        ['additions_field' => $newAdditionsField],
                        "`event_type` LIKE '" . $eventTypeData['event_type'] . "'"
                    );
                }
            }
        }
    }

    protected function updateAdditionsField($eventTypeData)
    {
        if(!empty($eventTypeData['additions_field'])){
            $additionFields = $this->json->unserialize($eventTypeData['additions_field']);

            if (!empty($additionFields)){
                $newAdditionFields = [];
                foreach ($additionFields as $additionField){
                    if(!isset($additionField['group'])){
                        $additionField['group'] = 'additional_section';
                        $additionField['require'] = '0';
                        $additionField['id'] = 'field_' . $additionField['id'];
                        $newAdditionFields[$additionField['id']] = $additionField;
                    }
                }

                if(!empty($newAdditionFields)){
                    $additionFields = $newAdditionFields;
                }
            }
            return $this->json->serialize($additionFields);
        }
    }

    public static function getVersion()
    {
        return '103.2.0';
    }
}
