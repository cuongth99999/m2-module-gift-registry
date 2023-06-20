<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 29/12/2015
 * Time: 11:41
 */

namespace Magenest\GiftRegistry\Controller\Index;

use Magenest\GiftRegistry\Controller\GiftRegistryProviderInterface;
use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\GiftRegistry;
use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Validator\Exception;
use Magenest\GiftRegistry\Controller\AbstractAction;

/**
 * Class Add
 * @package Magenest\GiftRegistry\Controller\Index
 */
class Add extends AbstractAction
{
    /**
     * @var Context
     */
    protected $_context;

    /**
     * @var GiftRegistryProviderInterface
     */
    protected $_giftRegistryProviderProvider;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var CurrentCustomer
     */
    protected $_currentCustomer;

    /**
     * @var GiftRegistryFactory
     */
    protected $_registryFactory;

    /**
     * Add constructor.
     * @param GiftRegistryFactory $giftRegistryFactory
     * @param Context $context
     * @param Data $data
     * @param ForwardFactory $resultForwardFactory
     * @param Session $customerSession
     * @param GiftRegistryProviderInterface $giftRegistryProvider
     * @param CurrentCustomer $currentCustomer
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        GiftRegistryFactory $giftRegistryFactory,
        Context $context,
        Data $data,
        ForwardFactory $resultForwardFactory,
        Session $customerSession,
        GiftRegistryProviderInterface $giftRegistryProvider,
        CurrentCustomer $currentCustomer,
        ProductRepositoryInterface $productRepository
    ) {
        $this->_registryFactory = $giftRegistryFactory;
        $this->_context = $context;
        $this->_customerSession = $customerSession;
        $this->_giftRegistryProviderProvider = $giftRegistryProvider;
        $this->_productRepository = $productRepository;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_currentCustomer = $currentCustomer;
        parent::__construct($context, $data);
    }

    /**
     * @return Forward|Redirect
     * @throws NotFoundException
     */
    public function execute()
    {
        /** @var  GiftRegistry $giftRegistry */
        $giftRegistry = $this->_giftRegistryProviderProvider->getGiftRegistry();
        if (!$giftRegistry) {
            throw new NotFoundException(__('Page not found.'));
        }
        $params = $this->getRequest()->getParams();
        if (!isset($params['product'])) {
            $this->messageManager->addErrorMessage(__("It looks like you're trying to add a product to a GiftRegistry but no product specified."));
            return $this->resultRedirectFactory->create()
                ->setRefererOrBaseUrl();
        }
        if (isset($params['giftregistry'])) {
            $giftRegistryId = intval($params['giftregistry']);
            if (!is_numeric($giftRegistryId)) {
                //forward the customer to customer account page
                /** @var Forward $resultForward */
                $resultForward = $this->resultForwardFactory->create();
                $resultForward->setModule('customer');
                $resultForward->setController('account');
                return $resultForward->forward('create');
            }
        }
        $productId = (int)$params['product'];
        try {
            $product = $this->_productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $product = null;
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($product != null) {
            $product_type = $product->getTypeId();
            if ($product_type == 'configurable' || $product_type == 'simple') {
                $buyRequest = new \Magento\Framework\DataObject($params);
                $resultRedirect = $this->resultRedirectFactory->create();
                $checkGuest = $this->_currentCustomer->getCustomerId();
                if ($checkGuest) {
                    try {
                        $giftRegistry->addNewItem($product, $buyRequest);
                        $this->messageManager->addSuccessMessage(__('The item is added to your gift registry.'));
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                    }
                    $resultRedirect->setPath(
                        'gift-registry/manage/items/id/',
                        [
                            'id' => $giftRegistryId
                        ]
                    );
                } else {
                    $resultRedirect->setPath('customer/account/login');
                }
            } else {
                $this->messageManager->addErrorMessage(__("This product type don't support. Please try other product"));
                $resultRedirect->setPath('customer/account/login');
            }
        }
        return $resultRedirect;
    }
}
