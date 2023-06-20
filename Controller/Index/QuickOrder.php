<?php
/**
 * Created by PhpStorm.
 * Date: 12/05/2020
 * Time: 16:19
 */

namespace Magenest\GiftRegistry\Controller\Index;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Layout;
use Magento\Search\Model\QueryFactory;

/**
 * Class QuickOrder
 * @package Magenest\GiftRegistry\Controller\Index
 */
class QuickOrder extends AbstractAction
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var QueryFactory
     */
    protected $queryFactory;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $helper;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var Product
     */
    protected $productResource;

    /**
     * QuickOrder constructor.
     * @param Context $context
     * @param QueryFactory $queryFactory
     * @param JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Pricing\Helper\Data $helper
     * @param ProductFactory $productFactory
     * @param Product $productResource
     * @param Image $imageHelper
     * @param Data $dataHelper
     */
    public function __construct(
        Context $context,
        QueryFactory $queryFactory,
        JsonFactory $resultJsonFactory,
        \Magento\Framework\Pricing\Helper\Data $helper,
        ProductFactory $productFactory,
        Product $productResource,
        Image $imageHelper,
        Data $dataHelper
    ) {
        $this->queryFactory      = $queryFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dataHelper        = $dataHelper;
        $this->helper            = $helper;
        $this->productFactory    = $productFactory;
        $this->imageHelper = $imageHelper;
        $this->productResource = $productResource;
        parent::__construct($context, $dataHelper);
    }

    /**
     * @return ResponseInterface|ResultInterface|Layout
     */
    public function execute()
    {
        $response                 = [];
        $response['product_data'] = [];
        $textInput                = $this->getRequest()->getParam('textInput');
        $giftId                = $this->getRequest()->getParam('giftId');
        $textInput                = trim($textInput);
        $textInput                = strtolower($textInput);
        $products = $this->dataHelper->searchByName($textInput);

        if (!empty($products)) {
            foreach ($products as $product) {
                $displayPrice = $this->helper->currency($product->getData('price'), true, false);
                $productId    = $product->getData('entity_id');
                $product = $this->productFactory->create();
                $this->productResource->load($product, $productId);
                if (!$product->getData('size')) {
                    $type = $product->getData('type_id');
                    $image_url = $this->imageHelper->init($product, 'product_base_image')->getUrl();
                    $data = [
                        'entity_id' => $productId,
                        'amount' => $product->getData('amount'),
                        'name' => $product->getName(),
                        'sku' => $product->getSku(),
                        'price' => $displayPrice,
                        'product_url' => $product->getProductUrl(),
                        'name_highlight' => $product->getName(),
                        'product_img' => $image_url,
                        'giftId' => $giftId,
                    ];
                    $response['product_data'][] = $data;
                }
            }
        }
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData($response);
        return $result;
    }
}
