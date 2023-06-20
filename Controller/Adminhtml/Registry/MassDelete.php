<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/12/2015
 * Time: 23:02
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml\Registry;

use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\ItemFactory;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry;
use Magenest\GiftRegistry\Model\ResourceModel\Item;
use Magenest\GiftRegistry\Model\ResourceModel\Registrant;
use Magenest\GiftRegistry\Model\ResourceModel\Registrant\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassDelete
 * @package Magenest\GiftRegistry\Controller\Adminhtml\Registry
 */
class MassDelete extends Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ItemFactory
     */
    protected $_itemFactory;

    /**
     * @var RegistrantFactory
     */
    protected $_registrantFactory;

    /**
     * @var Item\Option\CollectionFactory
     */
    protected $optionCollectionFactory;

    /**
     * @var GiftRegistryFactory
     */
    protected $giftRegistryFactory;

    /**
     * @var GiftRegistry
     */
    protected $giftRegistryResource;

    /**
     * @var Registrant
     */
    protected $registrantResource;

    /**
     * @var Item
     */
    protected $itemResource;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ItemFactory $itemFactory
     * @param RegistrantFactory $registrantFactory
     * @param Item\Option\CollectionFactory $optionCollectionFactory
     * @param GiftRegistryFactory $giftRegistryFactory
     * @param GiftRegistry $giftRegistryResource
     * @param Registrant $registrantResource
     * @param Item $itemResource
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ItemFactory $itemFactory,
        RegistrantFactory $registrantFactory,
        Item\Option\CollectionFactory $optionCollectionFactory,
        GiftRegistryFactory $giftRegistryFactory,
        GiftRegistry $giftRegistryResource,
        Registrant $registrantResource,
        Item $itemResource
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->_itemFactory = $itemFactory;
        $this->_registrantFactory = $registrantFactory;
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->giftRegistryFactory = $giftRegistryFactory;
        $this->giftRegistryResource = $giftRegistryResource;
        $this->registrantResource = $registrantResource;
        $this->itemResource = $itemResource;
        parent::__construct($context);
    }
    /**
     * Execute action
     *
     * @return Redirect
     * @throws LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        foreach ($collection as $item) {
            $registrant_id = $item->getRegistrantId();
            $this->deleteRegistry($registrant_id);
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * delete gift registry
     *
     * @param $id
     * @throws \Exception
     */
    public function deleteRegistry($id)
    {
        $registration = $this->_registrantFactory->create();
        $this->registrantResource->load($registration, $id)->delete($registration);
        if ($registration['giftregistry_id']) {
            $registry = $this->giftRegistryFactory->create();
            $this->giftRegistryResource->load($registry, $registration['giftregistry_id'])->delete($registry);
            if ($registry) {
                $items = $this->_itemFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('gift_id', $registration['giftregistry_id'])
                    ->getData();
                foreach ($items as $item) {
                    $item = $this->_itemFactory->create();
                    $this->itemResource->load($item, $item['gift_item_id'])->delete($item);
                    $options = $this->optionCollectionFactory->create()->addFieldToFilter('gift_item_id', $item['gift_item_id']);
                    foreach ($options as $option) {
                        $option->delete();
                    }
                }
            }
        }
        return;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_GiftRegistry::manager');
    }
}
