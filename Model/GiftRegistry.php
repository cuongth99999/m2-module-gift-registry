<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 25/05/2016
 * Time: 23:50
 */

namespace Magenest\GiftRegistry\Model;

use Magenest\GiftRegistry\Model\Item\OptionFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\Collection;
use Magenest\GiftRegistry\Model\ResourceModel\Item;
use Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory;
use Magenest\GiftRegistry\Model\ResourceModel\Item\Option;
use Magenest\GiftRegistry\Model\ResourceModel\Item\Option\CollectionFactory as OptionCollection;
use Magento\Catalog\Block\Product\Image;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class GiftRegistry
 * @package Magenest\GiftRegistry\Model
 */
class GiftRegistry extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'magenest_giftregistry';

    /**
     * @var string
     */
    private static $incognito = 'An unnamed friend';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var Item
     */
    protected $_itemResource;

    /**
     * @var CollectionFactory
     */
    protected $_itemCollection;

    /**
     * @var Item\OptionFactory
     */
    protected $optionFactory;

    /**
     * @var Option
     */
    protected $_optionResource;

    /**
     * @var OptionCollection
     */
    protected $_optionCollection;

    /**
     * @var TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var ImageBuilder
     */
    protected $_imageBuilder;

    /**
     * @var string
     */
    protected $_eventObject = "gift_registry";

    /**
     * @var string
     */
    protected $_eventPrefix = "gift_registry";

    /**
     * @var Serialize
     */
    protected $serialize;

    /**
     * GiftRegistry constructor.
     * @param ImageBuilder $imageBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param Context $context
     * @param Registry $registry
     * @param ResourceModel\GiftRegistry $resource
     * @param ResourceModel\GiftRegistry\Collection $resourceCollection
     * @param ItemFactory $itemFactory
     * @param Item $itemResource
     * @param CollectionFactory $itemCollection
     * @param OptionFactory $optionFactory
     * @param Option $optionResource
     * @param OptionCollection $optionCollection
     * @param StoreManagerInterface $storeManager
     * @param StateInterface $inlineTranslation
     * @param Serialize $serialize
     * @param array $data
     */
    public function __construct(
        ImageBuilder $imageBuilder,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        Context $context,
        Registry $registry,
        ResourceModel\GiftRegistry $resource,
        Collection $resourceCollection,
        ItemFactory $itemFactory,
        Item $itemResource,
        CollectionFactory $itemCollection,
        OptionFactory $optionFactory,
        Option $optionResource,
        OptionCollection $optionCollection,
        StoreManagerInterface $storeManager,
        StateInterface $inlineTranslation,
        Serialize $serialize,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_imageBuilder = $imageBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->itemFactory = $itemFactory;
        $this->_itemResource = $itemResource;
        $this->_itemCollection = $itemCollection;
        $this->optionFactory = $optionFactory;
        $this->_optionResource = $optionResource;
        $this->_optionCollection = $optionCollection;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->serialize = $serialize;
    }

    /**
     * @param $product
     * @param $request
     *
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    public function addNewItem($product, $request)
    {
        $cartCandidates = $product->getTypeInstance()->processConfiguration($request, clone $product);
        if ($request['selected_configurable_option']!="") {
            $idProduct = $request['selected_configurable_option'];
        }
        $flag = true;
        $superAttribute = $request->getSuperAttribute();
        if (!empty($superAttribute)) {
            foreach ($superAttribute as $attr) {
                if (empty($attr)) {
                    $flag = false;
                    break;
                }
            }
        }

        if (!$flag && $product->getTypeId() == 'configurable') {
            throw new \Exception(__('You need to choose options for your item.'));
        }
        foreach ($cartCandidates as $candidate) {
            if (isset($idProduct) && $idProduct!="") {
                $candidateId = $candidate->getEntityId();
                if ($idProduct==$candidateId) {
                    continue;
                }
            }
            $productPrice = $candidate->getPrice();
            $price = $candidate->getFinalPrice();
            $parentProductId = $candidate->getParentProductId();

            if (!$productPrice || !$price || $parentProductId) {
                continue;
            }

            if ($candidate->getQty()) {
                $qty = $candidate->getQty();
            } else {
                $qty =1;
            }
            //check if the item has ben selected
            $giftId = $request->getGiftregistry();
            $productId = $candidate->getId();
            $_items = $this->_itemCollection->create()
                ->addFieldToFilter('gift_id', $giftId)->addFieldToFilter('product_id', $productId);
            $hasDuplicate = false;
            foreach ($_items as $_item) {
                $_itemId = $_item->getGiftItemId();
                $_options = $this->_optionCollection->create()->addFieldToFilter('gift_item_id', $_itemId);
                $i = 0;
                foreach ($_options as $_option) {
                    $option1[$i] = $_option->getData('value');
                    $i++;
                }
                $i = 0;
                foreach ($candidate->getCustomOptions() as $optionCode => $value) {
                    $option2[$i] = $value->getValue();
                    $i++;
                }
                $check = true;
                if (count($option1) > 1) {
                    if (count($option2) == 1) {
                        $check = false;
                    }
                }
                for ($tmp = 1; $tmp < $i; $tmp++) {
                    try {
                        if (strcmp($option1[$tmp], $option2[$tmp])!=0) {
                            $check = false;
                            break;
                        }
                    } catch (\Exception $e) {
                        $check = false;
                        break;
                    }
                }
                if ($check == true) {
                    $hasDuplicate = true;
                    $_item->setData('qty', $_item->getData('qty')+$qty);
                    $_item->save();
                }
            }
            //add the item to the gift registry item
            if (!$hasDuplicate) {
                $price = $candidate->getFinalPrice();

                $itemData = [
                    'product_id'=> $candidate->getId(),
                    'product_name' => $candidate->getName(),
                    'store_id'  =>$this->storeManager->getStore()->getId(),
                    'gift_id'   =>$request->getGiftregistry(),
                    'qty' => $qty,
                    'final_price' =>$price,
                    'buy_request' => $request->getData()
                ];

                /**
                 * @var  $options array
                 */
                $options = $candidate->getCustomOptions();
                $itemData['buy_request'] = $this->serialize->serialize($request->getData());

                $giftRegistryItem = $this->itemFactory->create();

                $giftRegistryItem->setData($itemData);
                $this->_itemResource->save($giftRegistryItem);

                //save the option of the item in the table
                if ($giftRegistryItem->getId()) {
                    if (is_array($options) && !empty($options)) {
                        foreach ($options as $optionCode => $option) {
                            $optionModel = $this->optionFactory->create();

                            $optionModel->setData('gift_item_id', $giftRegistryItem->getId());
                            $optionModel->setData('code', $optionCode);
                            $optionModel->setData('product_id', $option->getProductId());
                            $optionModel->setData('value', $option->getValue());
                            $this->_optionResource->save($optionModel);
                        }
                    }
                }
                $items[] = $giftRegistryItem;
            }
        }
    }

    /**
     * @param null $customerId
     *
     * @return AbstractCollection
     * @throws LocalizedException
     */
    public function getAllGiftRegistryByCustomerId($customerId = null)
    {
        if ($customerId) {
            $collection =  $this->getResourceCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('is_expired', 0);
            return $collection;
        }
    }

    /**
     * @param $product
     * @param $imageId
     * @return Image
     */
    public function getImage($product, $imageId)
    {
        return $this->_imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->create();
    }

    /**
     * @param $order
     * @param $params
     * @param $registrant
     * @return $this
     * @throws LocalizedException
     */
    public function sendEmail($order, $params, $registrant)
    {
        if ($params != null) {
            $templateId = $this->_scopeConfig->getValue(
                'giftregistry/email/template',
                ScopeInterface::SCOPE_STORE
            );
            $email = $params['recipient_email'];
            $name = $params['recipient_name'];
            $url = $params['url'];
            $message = $params['message'];
            if ($params['incognito'] == 0) {
                $giverEmail = $params['giver_email'];
            } else {
                $giverEmail = self::$incognito;
            }
        } else {
            $templateId = $this->_scopeConfig->getValue(
                'giftregistry/email/gift_confirmation_email_form',
                ScopeInterface::SCOPE_STORE
            );
            $name = $order->getData('customer_firstname') . ' ' . $order->getData('customer_lastname');
            $email = $order->getData('customer_email');
            $giverEmail = $registrant->getData('email');
        }
        $totalProduct = count($order->getData('items'));
        $orderId = $order->getData('increment_id');
        $grandTotal = $order->getData('grand_total');
        $createdAt = $order->getData('created_at');
        $status = $order->getData('status');
        $from = $this->_scopeConfig->getValue(
            'giftregistry/email/sender',
            ScopeInterface::SCOPE_STORE
        );
        $this->inlineTranslation->suspend();
        $storeId = $order->getData('store_id');
        $transport = $this->_transportBuilder->setTemplateIdentifier($templateId)->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId,
            ]
        )->setTemplateVars(
            [
                'recipient_name' => $name,
                'giver_email' => $giverEmail,
                'totalProduct' => $totalProduct,
                'message' => isset($message) ? $message : "No message",
                'url' => isset($url) ? $url : null,
                'order_id' => $orderId,
                'grand_total' => $grandTotal,
                'status' => $status,
                'created_at' => $createdAt
            ]
        )->setFromByScope(
            $from
        )->addTo(
            $email,
            $name
        )->getTransport();
        try {
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Magento\Framework\Exception\MailException $e) {
            $this->_logger->critical($e->getMessage());
        }
        return $this;
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG];
    }
}
