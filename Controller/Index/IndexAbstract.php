<?php
namespace Magenest\GiftRegistry\Controller\Index;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\Item\OptionFactory;
use Magenest\GiftRegistry\Model\ItemFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry;
use Magenest\GiftRegistry\Model\ResourceModel\Item;
use Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory;
use Magenest\GiftRegistry\Model\ResourceModel\Item\Option;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\PageCache\Model\Cache\Type;
use Psr\Log\LoggerInterface;

/**
 * Class IndexAbstract
 * @package Magenest\GiftRegistry\Controller\Index
 */
abstract class IndexAbstract extends AbstractAction
{
    /** @var Session $_customerSession */
    protected $_customerSession;

    /** @var GiftRegistryFactory $_giftregistryFactory */
    protected $_giftregistryFactory;

    /** @var GiftRegistry $_giftregistryResource */
    protected $_giftregistryResource;

    /** @var ItemFactory $_itemFactory */
    protected $_itemFactory;

    /** @var Item $_itemResource */
    protected $_itemResource;

    /** @var CollectionFactory $_itemCollection */
    protected $_itemCollection;

    /** @var OptionFactory $_itemOptionFactory */
    protected $_itemOptionFactory;

    /** @var Option $_itemOptionResource */
    protected $_itemOptionResource;

    /** @var Option\CollectionFactory $_itemOptionCollection */
    protected $_itemOptionCollection;

    /**
     * @var LoggerInterface
     */
    protected $_logger;
    protected $_cacheType;

    /**
     * IndexAbstract constructor.
     * @param Session $session
     * @param GiftRegistryFactory $giftRegistryFactory
     * @param GiftRegistry $giftRegistryResource
     * @param ItemFactory $itemFactory
     * @param Item $itemResource
     * @param CollectionFactory $itemCollection
     * @param OptionFactory $optionFactory
     * @param Option $optionResource
     * @param Option\CollectionFactory $optionCollection
     * @param LoggerInterface $logger
     * @param Context $context
     * @param Data $data
     */
    public function __construct(
        Session $session,
        GiftRegistryFactory $giftRegistryFactory,
        GiftRegistry $giftRegistryResource,
        ItemFactory $itemFactory,
        Item $itemResource,
        CollectionFactory $itemCollection,
        OptionFactory $optionFactory,
        Option $optionResource,
        Option\CollectionFactory $optionCollection,
        LoggerInterface $logger,
        Context $context,
        Data $data,
        Type $cacheType
    ) {
        $this->_customerSession = $session;
        $this->_giftregistryFactory = $giftRegistryFactory;
        $this->_giftregistryResource = $giftRegistryResource;
        $this->_itemFactory = $itemFactory;
        $this->_itemResource = $itemResource;
        $this->_itemCollection = $itemCollection;
        $this->_itemOptionFactory = $optionFactory;
        $this->_itemOptionResource = $optionResource;
        $this->_itemOptionCollection = $optionCollection;
        $this->_logger = $logger;
        $this->_cacheType = $cacheType;
        parent::__construct($context, $data);
    }
}
