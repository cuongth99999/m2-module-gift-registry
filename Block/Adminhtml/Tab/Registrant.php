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

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Registrant
 * @package Magenest\GiftRegistry\Block\Adminhtml\Tab
 */
class Registrant extends AbstractRenderer
{

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;

    /**
     * Registrant constructor.
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepositoryInterface,
        Context $context,
        $data = []
    ) {
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        parent::__construct($context, $data);
    }

    /**
     * @param DataObject $row
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function render(DataObject $row)
    {
        $customerId = $row->getData('customer_id');
        $customerModel = $this->_customerRepositoryInterface->getById($customerId);
        return $customerModel->getFirstname() . ' ' . $customerModel->getLastname();
    }
}
