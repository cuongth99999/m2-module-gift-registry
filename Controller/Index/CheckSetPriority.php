<?php
namespace Magenest\GiftRegistry\Controller\Index;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Layout;

/**
 * Class CheckSetPriority
 * @package Magenest\GiftRegistry\Controller\Index
 */
class CheckSetPriority extends AbstractAction
{
    /** @var CollectionFactory $_giftregistryCollection */
    protected $_giftregistryCollection;

    /**
     * @var \Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory
     */
    protected $_itemCollection;

    /**
     * @var Data
     */
    protected $_helperData;

    /** @var Session $_sessionModel */
    protected $_sessionModel;

    /** @var ProductRepositoryInterface $_productRepository */
    protected $_productRepository;

    /**
     * CheckSetPriority constructor.
     * @param CollectionFactory $giftregistryCollection
     * @param \Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory $collectionFactory
     * @param Data $helperData
     * @param Context $context
     * @param Session $session
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        CollectionFactory $giftregistryCollection,
        \Magenest\GiftRegistry\Model\ResourceModel\Item\CollectionFactory $collectionFactory,
        Data $helperData,
        Context $context,
        Session $session,
        ProductRepositoryInterface $productRepository
    ) {
        $this->_giftregistryCollection = $giftregistryCollection;
        $this->_itemCollection = $collectionFactory;
        $this->_helperData = $helperData;
        $this->_sessionModel = $session;
        $this->_productRepository = $productRepository;
        parent::__construct($context, $helperData);
    }

    /**
     * @return ResponseInterface|ResultInterface|Layout
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $params = $this->getRequest()->getParams();
        $data = [];
        if (!empty($params)) {
            $productId = $params['productId'];
            /** @var Product $product */
            $product = $this->_productRepository->getById($productId);
            $customer = $this->_sessionModel->getCustomer();
            if ($product->getEntityId() && $customer != null && $customer->getId()) {
                try {
                    $productId = $product->getEntityId();
                    $customerId = $customer->getId();
                    $productInGR = $this->_giftregistryCollection->create()->getProductsInGiftRegistry($productId, $customerId);
                    $result = [];
                    foreach ($productInGR as $item) {
                        $item['title'] = html_entity_decode($item['title'], ENT_QUOTES);
                        $item['product_type'] = $product->getTypeId();
                        $item['options'] = $this->getProductOption($item['gift_id'], $productId);
                        $result[] = $item;
                    }
                    if (count($result)>0) {
                        $data = [
                            'isShow' => true,
                            'data' => $result,
                            'product_type' => $product->getTypeId()
                        ];
                    } else {
                        $data = [
                            'isShow' => false,
                            'data' => ''
                        ];
                    }
                } catch (\Exception $exception) {
                    $data = [
                        'isShow' => false,
                        'data' => ''
                    ];
                }
            }
        }
        if (empty($data)) {
            $data = [
                'isShow' => false,
                'data' => ''
            ];
        }
        $resultJson->setData($data);
        return $resultJson;
    }

    /**
     * @param $gift_id
     * @param $productId
     * @return array
     */
    public function getProductOption($gift_id, $productId)
    {
        $collections = $this->_itemCollection->create()->addFieldToFilter('gift_id', $gift_id)->addFieldToFilter('product_id', $productId);
        $options = [];
        foreach ($collections as $collection) {
            $option = $this->getItemOptions($collection);
            $giftItemId = $collection->getData('gift_item_id');
            $value = $collection->getData('priority');
            $options[] = [
                'gift_item_id' => $giftItemId,
                'option' => $option,
                'priority' => $value,
            ];
        }
        return $options;
    }

    /**
     * @param $item
     * @return array
     */
    public function getItemOptions($item)
    {
        return $this->_helperData->getOptions($item);
    }
}
