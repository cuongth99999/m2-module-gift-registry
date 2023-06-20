<?php
namespace Magenest\GiftRegistry\Controller\Index;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;

class CheckGRItem extends AbstractAction
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
     * CheckGRItem constructor.
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
                        'data' => $exception->getMessage()
                    ];
                }
            }
        }
        if (empty($data)) {
            $data = [
                'isShow' => false,
                'data' => 'empty'
            ];
        }
        $resultJson->setData($data);
        return $resultJson;
    }
    public function getProductOption($gift_id, $productId)
    {
        $collections = $this->_itemCollection->create()->addFieldToFilter('gift_id', $gift_id)->addFieldToFilter('product_id', $productId);
        $options = [];
        foreach ($collections as $collection) {
            $option = $this->getItemOptions($collection);
            $giftItemId = $collection->getData('gift_item_id');
            if (!empty($option)) {
                $options[] = [
                    'gift_item_id' => $giftItemId,
                    'option' => $option
                ];
            }
        }
        return $options;
    }
    public function getItemOptions($item)
    {
        return $this->_helperData->getOptions($item);
    }
}
