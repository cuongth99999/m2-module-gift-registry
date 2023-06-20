<?php
/**
 * Created by PhpStorm.
 * User: canhnd
 * Date: 23/06/2017
 * Time: 10:51
 */
namespace Magenest\GiftRegistry\Controller\Customer;

use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\Item\OptionFactory;
use Magenest\GiftRegistry\Model\ItemFactory;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry;
use Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory;
use Magenest\GiftRegistry\Model\ResourceModel\Item\Option;
use Magenest\GiftRegistry\Model\ResourceModel\Registrant;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Layout;
use Psr\Log\LoggerInterface;

/***
 * Class Delete
 * @package Magenest\GiftRegistry\Controller\Customer
 */
class Delete extends AbstractAccount
{
    /**
     * @var ResultJsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /** @var RegistrantFactory $_registrantFactory */
    protected $_registrantFactory;

    /** @var Registrant $_registrantResource */
    protected $_registrantResource;

    /** @var Registrant\CollectionFactory $_registrantCollection */
    protected $_registrantCollection;

    /** @var ItemFactory $_itemFactory */
    protected $_itemFactory;

    /** @var CollectionFactory $_itemCollection */
    protected $_itemCollection;

    /** @var \Magenest\GiftRegistry\Model\ResourceModel\Item $_itemResouce */
    protected $_itemResouce;

    /** @var GiftRegistryFactory $_giftregistryFactory */
    protected $_giftregistryFactory;

    /** @var GiftRegistry\CollectionFactory $_giftregistryCollection */
    protected $_giftregistryCollection;

    /** @var GiftRegistry $_giftregistryResource */
    protected $_giftregistryResource;

    /** @var OptionFactory $_optionFactory */
    protected $_optionFactory;

    /** @var Option $_optionResource */
    protected $_optionResource;

    /** @var Option\CollectionFactory $_optionCollection */
    protected $_optionCollection;

    /**
     * @var Data
     */
    protected $data;

    /**
     * Delete constructor.
     *
     * @param Context $context
     * @param ResultJsonFactory $resultJsonFactory
     * @param LoggerInterface $loggerInterface
     * @param RegistrantFactory $registrantFactory
     * @param Registrant $registrantResource
     * @param Registrant\CollectionFactory $registrantCollection
     * @param GiftRegistry\CollectionFactory $collectionFactory
     * @param GiftRegistry $giftregistryResource
     * @param ItemFactory $itemFactory
     * @param CollectionFactory $itemCollection
     * @param \Magenest\GiftRegistry\Model\ResourceModel\Item $itemResouce
     * @param GiftRegistryFactory $giftRegistryFactory
     * @param OptionFactory $optionFactory
     * @param Option $optionResource
     * @param Option\CollectionFactory $optionCollection
     * @param Data $data
     */
    public function __construct(
        Context $context,
        ResultJsonFactory $resultJsonFactory,
        LoggerInterface $loggerInterface,
        RegistrantFactory $registrantFactory,
        Registrant $registrantResource,
        Registrant\CollectionFactory $registrantCollection,
        GiftRegistry\CollectionFactory $collectionFactory,
        GiftRegistry $giftregistryResource,
        ItemFactory $itemFactory,
        CollectionFactory $itemCollection,
        \Magenest\GiftRegistry\Model\ResourceModel\Item $itemResouce,
        GiftRegistryFactory $giftRegistryFactory,
        OptionFactory $optionFactory,
        Option $optionResource,
        Option\CollectionFactory $optionCollection,
        Data $data
    ) {
        $this->_registrantFactory = $registrantFactory;
        $this->_registrantResource = $registrantResource;
        $this->_registrantCollection = $registrantCollection;
        $this->_giftregistryFactory = $giftRegistryFactory;
        $this->_giftregistryCollection = $collectionFactory;
        $this->_giftregistryResource = $giftregistryResource;
        $this->_itemFactory = $itemFactory;
        $this->_itemCollection = $itemCollection;
        $this->_itemResouce = $itemResouce;
        $this->_logger=$loggerInterface;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_optionFactory = $optionFactory;
        $this->_optionResource = $optionResource;
        $this->_optionCollection = $optionCollection;
        $this->data = $data;
        parent::__construct($context);
    }

    /**
     * Blog Index, shows a list of recent blog posts.
     *
     * @return ResultInterface|Layout|Json
     */
    public function execute()
    {
        $data = $this->getDataJson();
        if ($data['error']) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('gift_registry/customer/mygiftregistry/');
            return $resultRedirect;
        } else {
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($data);
        }
    }

    /**
     * send mail registry to all friends
     *
     * @return array
     */
    public function getDataJson()
    {
        $data = $this->getRequest()->getPostValue();

        $description = 'Done';
        $error = true;
        $giftregistry = $this->_giftregistryCollection->create()->addFieldToFilter('gift_id', $data['gift_id'])->getFirstItem();
        if ($giftregistry->getGiftId()) {
            try {
                $items = $this->_itemCollection->create()
                    ->addFieldToFilter('gift_id', $giftregistry->getGiftId())
                    ->getItems();
                if (is_array($items)) {
                    foreach ($items as $item) {
                        $itemModel = $this->_itemFactory->create();
                        $this->_itemResouce->load($itemModel, $item['gift_item_id']);
                        $options = $this->_optionCollection->create()->addFieldToFilter('gift_item_id', $item['gift_item_id']);
                        foreach ($options as $option) {
                            $option->delete();
                        }
                        $this->_itemResouce->delete($itemModel);
                    }
                }
                $registrants = $this->_registrantCollection->create()
                    ->addFieldToFilter('giftregistry_id', $giftregistry->getGiftId())
                    ->getFirstItem();
                if ($registrants->getRegistrantId()) {
                    $this->_registrantResource->delete($registrants);
                }
                $this->_giftregistryResource->delete($giftregistry);
                $this->messageManager->addSuccessMessage(__('Delete gift registry complete.'));
            } catch (\Exception $exception) {
                $error = false;
                $description = $exception;
                $this->messageManager->addErrorMessage(__('Something went wrong while saving the database. Please refresh the page and try again.'));
            }
        }
        return [
            'description' => $description,
            'error' => $error,
        ];
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->data->isEnableExtension()) {
            $this->_redirect('noroute');
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }
}
