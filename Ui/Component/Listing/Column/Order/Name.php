<?php
/**
 * Created by PhpStorm.
 * User: ninhvu
 * Date: 08/08/2018
 * Time: 15:26
 */

namespace Magenest\GiftRegistry\Ui\Component\Listing\Column\Order;

use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class ViewGift
 * @package Magenest\GiftRegistry\Ui\Component\Listing\Column\Order
 */
class Name extends Column
{
    /**
     * @var UrlBuilder
     */
    protected $actionUrlBuilder;
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var string
     */
    private $_editUrl = 'giftregistry/registry/edit';

    /**
     * Name constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlBuilder $actionUrlBuilder
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlBuilder $actionUrlBuilder,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->actionUrlBuilder = $actionUrlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                $billingName = $item['billing_name'];
                $billingName = explode(" ", $billingName);
                if ($name=="first_name") {
                    $item['first_name']=$billingName[0];
                } else {
                    $item['last_name']=$billingName[1];
                }
            }
        }
        return $dataSource;
    }
}
