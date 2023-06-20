<?php

namespace Magenest\GiftRegistry\Model\Config\Backend\General;

class Enable extends \Magento\Framework\App\Config\Value
{
    /**
     * @inheritDoc
     * @return Enable
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $this->cacheTypeList->invalidate(\Magento\Framework\App\Cache\Type\Block::TYPE_IDENTIFIER);
        }

        return parent::afterSave();
    }
}
