<?php
/**
 * Created by Magenest.
 * User: trongpq
 * Date: 4/23/18
 * Time: 12:38
 * Email: trongpq@magenest.com
 */

namespace Magenest\GiftRegistry\Controller\Index;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry;
use Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\View\Result\Page;

/**
 * Class SortData
 * @package Magenest\GiftRegistry\Controller\Index
 */
class SortData extends AbstractAction
{
    /**
     * @var null
     */
    protected $_giftregistryModel = null;

    /**
     * @var GiftRegistryFactory
     */
    protected $_giftregistryFactory;

    /**
     * @var GiftRegistry
     */
    protected $_giftregistryResource;

    /**
     * @var CollectionFactory
     */
    protected $_itemFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * SortData constructor.
     * @param Context $context
     * @param GiftRegistryFactory $giftRegistryFactory
     * @param GiftRegistry $giftRegistryResource
     * @param CollectionFactory $itemFactory
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        GiftRegistryFactory $giftRegistryFactory,
        GiftRegistry $giftRegistryResource,
        CollectionFactory $itemFactory,
        Data $helper
    ) {
        $this->_giftregistryFactory = $giftRegistryFactory;
        $this->_giftregistryResource = $giftRegistryResource;
        $this->_itemFactory = $itemFactory;
        $this->helper = $helper;
        return parent::__construct($context, $helper);
    }

    /**
     * @return ResultInterface|Layout|Page
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $data = $this->helper->getGiftRegistryItem($params);
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($data);
        return $resultJson;
    }
}
