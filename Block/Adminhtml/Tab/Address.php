<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * NOTICE OF LICENSE
 *
 * @category Magenest
 */

namespace Magenest\GiftRegistry\Block\Adminhtml\Tab;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class TitleRenderer
 * @package Magenest\RewardPoints\Block\Adminhtml
 */
class Address extends AbstractRenderer
{

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;

    /**
     * Address constructor.
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepositoryInterface,
        Context $context,
        $data =[]
    ) {
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        parent::__construct($context, $data);
    }

    /**
     * @param DataObject $row
     * @return array|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function render(DataObject $row)
    {
        $addressArr = [];
        $customerId = $row->getData('customer_id');
        $customer = $this->_customerRepositoryInterface->getById($customerId);
        $addressCollection = $customer->getAddresses();

        if (!empty($addressCollection)) {
            /**
             * @var  $address \Magento\Customer\Model\Address
             */
            foreach ($addressCollection as $address) {
                $addressArr = $address->getFirstname() . ' ' . $address->getLastname() . ' ' . $address->getStreet()['0'];
            }
        }

        return $addressArr;
    }
}
