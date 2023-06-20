<?php
namespace Magenest\GiftRegistry\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class GiftRegistryTmp
 * @package Magenest\GiftRegistry\Model
 */
class GiftRegistryTmp extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'magenest_giftregistry_tmp';

    public function _construct()
    {
        $this->_init('Magenest\GiftRegistry\Model\ResourceModel\GiftRegistryTmp');
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
