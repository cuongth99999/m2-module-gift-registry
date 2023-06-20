<?php

namespace Magenest\GiftRegistry\Controller\Adminhtml\Index;

use Magento\Customer\Controller\Adminhtml\Index;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Layout;

/**
 * Class TransactionHistory
 * @package Magenest\RewardPoints\Controller\Adminhtml\Index
 */
class TransactionHistory extends Index
{

    /**
     * @return ResponseInterface|ResultInterface|Layout
     */
    public function execute()
    {
        $customerId   = $this->initCurrentCustomer();
        $resultLayout = $this->resultLayoutFactory->create();
        $block        = $resultLayout->getLayout()->getBlock('admin.transaction.history');
        $block->setCustomerId($customerId)->setUseAjax(true);

        return $resultLayout;
    }
}
