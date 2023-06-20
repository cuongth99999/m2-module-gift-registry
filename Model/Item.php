<?php
/*
 * Created by Magenest
 * User: Nguyen Duc Canh
 * Date: 1/12/2015
 * Time: 10:26
 */

namespace Magenest\GiftRegistry\Model;

use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\ResourceModel\Item as ResourceModel;
use Magenest\GiftRegistry\Model\ResourceModel\Item\Collection;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

/**
 * Class Item
 * @package Magenest\GiftRegistry\Model
 */
class Item extends AbstractModel implements ItemInterface, IdentityInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'giftregistry_item';

    const CACHE_TAG = 'magenest_giftregistry_item';

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Item constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ResourceModel $resource
     * @param Collection $resourceCollection
     * @param Data $helper
     * @param ProductRepositoryInterface $productRepositoryInterface
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ResourceModel $resource,
        Collection $resourceCollection,
        Data $helper,
        ProductRepositoryInterface $productRepositoryInterface,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->productRepository = $productRepositoryInterface;
        $this->helper = $helper;
    }

    /**
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getProduct()
    {
        $productId = $this->getData('product_id');

        $product = $this->productRepository->getById($productId);

        $customOptionsArr = $this->helper->getCustomOptionAsArr($this->getId());
        $product->setCustomOptions($customOptionsArr);
        return $product;
    }

    /**
     * Get item option by code
     *
     * @param string $code
     * @return array
     */
    public function getOptionByCode($code)
    {
        return $this->helper->getOptionByCode($this->getId(), $code);
    }

    /**
     * Returns special download params (if needed) for custom option with type = 'file''
     * Return null, if not special params needed'
     * Or return \Magento\Framework\DataObject with any of the following indexes:
     *  - 'url' - url of controller to give the file
     *  - 'urlParams' - additional parameters for url (custom option id, or item id, for example)
     *
     * @return void
     */
    public function getFileDownloadParams()
    {
        // TODO: Implement getFileDownloadParams() method.
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG];
    }
}
