<?php

namespace Magenest\GiftRegistry\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Ui\Component\Layout\Tabs\TabWrapper;

/**
 * Class TransactionTab
 * @package Magenest\GiftRegistry\Block\Adminhtml
 */
class TransactionTab extends TabWrapper
{

    /**
     * @var Registry|null
     */
    protected $coreRegistry = null;

    /**
     * @var bool
     */
    protected $isAjaxLoaded = true;

    /**
     * TransactionTab constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(Context $context, Registry $registry, array $data = [])
    {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|mixed
     */
    public function canShowTab()
    {
        return $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * @return Phrase|string
     */
    public function getTabLabel()
    {
        return __('Gift Registry');
    }

    /**
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('giftregistry/index/transactionHistory', ['_current' => true]);
    }
}
