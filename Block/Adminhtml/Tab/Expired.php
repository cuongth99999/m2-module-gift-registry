<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_RewardPoints
 */

namespace Magenest\GiftRegistry\Block\Adminhtml\Tab;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * Class TitleRenderer
 * @package Magenest\RewardPoints\Block\Adminhtml
 */
class Expired extends AbstractRenderer
{
    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $isExpired = $row->getData('is_expired');

        if ($isExpired == 0) {
            $status = "Active";
        } else {
            $status = "Expired";
        }
        return $status;
    }
}
