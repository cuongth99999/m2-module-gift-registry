<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 21/03/2016
 * Time: 11:50
 */
namespace Magenest\GiftRegistry\Block\Guest;

use Magenest\GiftRegistry\Block\Customer\Registry\ViewRegistry;
use Magenest\GiftRegistry\Model\GiftRegistry;
use Magenest\GiftRegistry\Model\Item;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class View
 * @package Magenest\GiftRegistry\Block\Guest
 */
class View extends ViewRegistry implements IdentityInterface
{
    /**
     * Get value pr
     *
     * @param $priority
     * @return mixed
     */
    public function getPriority($priority)
    {
        $config = $this->priority->toOptionArray();
        foreach ($config as $key => $value) {
            if ($key == $priority) {
                return $value;
            }
        }
        return null;
    }

    /**
     * @return bool
     */
    public function checkCustomer()
    {
        $customerId = $this->currentCustomer->getCustomerId();
        $giftRegistry = $this->getGiftRegistry();
        $id = $giftRegistry['customer_id'];
        return !($id == $customerId);
    }

    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    /**
     * @return \Magenest\GiftRegistry\Model\GiftRegistry
     */
    public function getGiftRegistry()
    {
        if ($this->_giftregistryModel == null) {
            $pathInfo = $this->getRequest()->getPathInfo();
            $identifier = trim($pathInfo, '/');
            if (strpos($identifier, 'gift-registry/view') !== false) {
                $arr = explode('gift-registry/view/', $identifier);
                if (isset($arr[1])) {
                    $gift_id = explode('/', $arr[1]);
                    $giftregistryModel = $this->_giftregistryFactory->create();
                    $this->_giftregistryResource->load($giftregistryModel, $gift_id[1]);
                    $this->_giftregistryModel = $giftregistryModel;
                }
            }
        }
        return $this->_giftregistryModel;
    }

    /**
     * @return DataObject
     */
    public function getRegistrantModel()
    {
        $giftRegistryId = $this->getGiftRegistry()->getId();
        return $this->registrantFactory->create()->getCollection()
            ->addFieldToFilter("giftregistry_id", $giftRegistryId)
            ->getFirstItem();
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getGiftRegistryItem()
    {
        $params = $this->getRequest()->getParams();
        return $this->helper->getGiftRegistryItem($params);
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getFormKey()
    {
        return  $this->_formKey->getFormKey();
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->getGiftRegistry()->getData('type');
    }

    /**
     * @param int $productId
     * @return string
     */
    public function addToCart($productId = 0)
    {
        if ($productId) {
            return $this->getUrl('gift_registry/cart/add', ['product_id' =>$productId]);
        } else {
            return $this->getUrl('gift_registry/cart/add');
        }
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        $registry = $this->getGiftRegistry();
        return $registry->getData('password');
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->getUrl('customer/account/login');
    }

    /**
     * @param $item
     * @param $product
     * @return float|null
     */
    public function getPrice($item, $product)
    {
        $price = null;
        if ($product->getTypeId() == "configurable") {
            $request = $item->getData('buy_request');
            $request = $this->serialize->unserialize($request);
            $products = $product->getTypeInstance()->getUsedProducts($product);
            $options =[];
            foreach ($request['super_attribute'] as $key => $value) {
                $options[$key] = $value;
            }
            $attributes=[];
            foreach ($options as $key =>$value) {
                $eavAttribute = $this->eavAttribute->load($key);
                $attributes[$eavAttribute->getAttributeCode()] = $value;
            }

            foreach ($products as $product) {
                $check = true;
                $data =$product->getData();
                foreach ($attributes as $key => $attribute) {
                    if (!isset($data[$key]) || $data[$key]!==$attribute) {
                        $check = false;
                        continue;
                    }
                }
                if ($check==true) {
                    $id = $data['entity_id'];
                    $currentProduct = $this->_productFactory->create();
                    $this->_productResources->load($currentProduct, $id);
                    if ($currentProduct) {
                        $price =$currentProduct->getFinalPrice();
                    }
                }
            }
        } else {
            $price = $product->getFinalPrice();
        }
        return $price;
    }

    /**
     * @param $path
     * @return string
     */
    public function getImageGRUrl($path)
    {
        return $this->imageModel->getBaseUrl() . $path;
    }

    /**
     * @return mixed|string
     */
    public function getCurrentUrl()
    {
        $currentUrl = $this->_urlBuilder->getCurrentUrl();
        $arr = explode('?', $currentUrl);
        if (is_array($arr)&&isset($arr[1])) {
            $currentUrl = $arr[0];
        }
        return $currentUrl;
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [Item::CACHE_TAG, GiftRegistry::CACHE_TAG];
    }

    /**
     * @return \Magenest\GiftRegistry\Model\Type
     */
    public function getEventTypeModel()
    {
        $type = $this->getEventType();
        $typeModel = $this->_typeFactory->create();
        $this->_resourceType->load($typeModel, $type, 'event_type');
        return $typeModel;
    }

    /**
     * @return mixed
     * Get event type
     */
    public function getEventType()
    {
        return $this->getGiftRegistry()->getData('type');
    }
}
