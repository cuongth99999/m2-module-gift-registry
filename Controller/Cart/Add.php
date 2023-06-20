<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 19/04/2016
 * Time: 14:12
 */
namespace Magenest\GiftRegistry\Controller\Cart;

use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\ItemFactory;
use Magenest\GiftRegistry\Model\ResourceModel\Item;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Controller\Cart;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Checkout\Model\Session;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Quote\Model\ResourceModel\Quote;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Add
 * @package Magenest\GiftRegistry\Controller\Cart
 */
class Add extends Cart
{
    /**
     * @var ItemFactory
     */
    protected $_itemFactory;

    /**
     * @var Item
     */
    protected $_itemResource;

    /**
     * @var Configurable
     */
    protected $_catalogProductTypeConfigurable;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Serialize
     */
    protected $serialize;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var Quote
     */
    protected $quoteResource;

    /**
     * Add constructor.
     * @param ItemFactory $itemFactory
     * @param Item $itemResource
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param Configurable $catalogProductTypeConfigurable
     * @param ProductFactory $productFactory
     * @param Data $helperData
     * @param Serialize $serialize
     * @param ProductMetadataInterface $productMetadata
     * @param Quote $quoteResource
     * @param CustomerCart $cart
     */
    public function __construct(
        ItemFactory $itemFactory,
        Item $itemResource,
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        Configurable $catalogProductTypeConfigurable,
        ProductFactory $productFactory,
        Data $helperData,
        Serialize $serialize,
        ProductMetadataInterface $productMetadata,
        Quote $quoteResource,
        CustomerCart $cart
    ) {
        $this->_itemFactory = $itemFactory;
        $this->_itemResource = $itemResource;
        $this->_catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        $this->productFactory = $productFactory;
        $this->helperData = $helperData;
        $this->serialize = $serialize;
        $this->productMetadata = $productMetadata;
        $this->quoteResource = $quoteResource;
        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $params = $this->getRequest()->getParams();
        $itemId =(isset($params['item'])) ? $params['item'] : 0;
        $qty =(isset($params['qty']) && $params['qty']) ? $params['qty'] : 1;
        $formKey =(isset($params['formKey']) && $params['formKey']) ? $params['formKey'] : '';
        $item = $this->_itemFactory->create();
        $this->_itemResource->load($item, $itemId);
        if (empty($item->getData())) {
            $this->messageManager->addNoticeMessage(__("This item is no longer exist!"));
            $resultData =['type'=>'error', 'message'=>__('This item is no longer exist!') ];
        } else {
            $buyRequestStr =  $item->getBuyRequest();
            $buyRequest = null;
            if ($buyRequestStr) {
                if ($this->checkMagentoVersion()) {
                    $buyRequest = new \Magento\Framework\DataObject($this->serialize->unserialize($buyRequestStr));
                } else {
                    $buyRequest = new \Magento\Framework\DataObject($this->serialize->unserialize($buyRequestStr));
                }
            }
            $buyRequest->setData('qty', $qty)
                ->setData('is_for_gift_registry', '1')
                ->setData('registry', $item->getData('gift_id'))
                ->setData('form_key', $formKey)
                ->setData('item', $itemId);
            $product = $item->getProduct();
            try {
                $gift_id = $item->getData('gift_id');
                if ($this->validationItemInCart($gift_id)) {
                    $itemsInCart = $this->cart->getItems();
                    $this->checkGiftRegistryItemInCart($itemsInCart, $item, $qty);
                    $this->cart->addProduct($product, $buyRequest->toArray());
                    $this->cart->save();
                    $quote = $this->cart->getQuote();
                    $this->quoteResource->save($quote);
                    $quote->setTotalsCollectedFlag(false)->collectTotals();
                    $this->messageManager->addSuccessMessage(__('You have added an item from gift registry to cart successfully.'));
                    $resultData =['error'=>false, 'message'=>__('You have added an item from gift registry to cart successfully.') ];
                } else {
                    $this->messageManager->addNoticeMessage(__("You can't add an item from other gift registry."));
                    $resultData =['error'=>true, 'message'=>__("You can't add an item from other gift registry.") ];
                }
            } catch (\Exception $exception) {
                $resultData =['error'=>true, 'message'=> $exception->getMessage() ];
                $this->messageManager->addErrorMessage($exception->getMessage());
            }
        }
        $resultJson->setData($resultData);
        return $resultJson;
    }

    /**
     * @param $gift_id
     * @return bool
     * @throws \Exception
     */
    public function validationItemInCart($gift_id)
    {
        $cartItems = $this->cart->getItems();
        $flag = true;
        foreach ($cartItems as $cartItem) {
            $buyRequest = $cartItem->getBuyRequest()->getData();
            if (isset($buyRequest['giftregistry'])) {
                if ($buyRequest['giftregistry'] != $gift_id) {
                    $flag = false;
                }
            } else {
                throw new \Exception(__('You must clear the cart before buy gift for friend.'));
            }
        }
        return $flag;
    }

    /**
     * @return bool
     */
    public function checkMagentoVersion()
    {
        $magentoVersion = $this->productMetadata->getVersion();
        if (version_compare($magentoVersion, "2.2.0", ">=")) {
            return true;
        }
        return false;
    }

    /**
     * @param $cartItems
     * @param $item
     * @param $qty
     * @throws \Exception
     */
    public function checkGiftRegistryItemInCart($cartItems, $item, $qty)
    {
        $qtyMode = $this->helperData->getDisplayQtyMode();
        if ($qtyMode == "0" || $qtyMode == null) {
            /** @var \Magento\Framework\Model\Quote\Item $cartItem */
            foreach ($cartItems as $cartItem) {
                $buyRequest = $cartItem->getBuyRequest()->toArray();
                if (isset($buyRequest['item'])) {
                    $itemId = $buyRequest['item'];
                    if ($itemId == $item->getId()) {
                        $desiredQty = $item->getQty();
                        $qtyInCart = $cartItem->getQty();
                        if ($desiredQty < ($qtyInCart+$qty)) {
                            throw new \Exception(__('You can not update cart quantity more than desired quantity of the gift registry'));
                        }
                    }
                }
            }
        }
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->helperData->isEnableExtension()) {
            $this->_redirect('noroute');
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }
}
