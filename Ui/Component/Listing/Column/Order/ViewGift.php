<?php
/**
 * Created by Magenest.
 * User: trongpq
 * Date: 3/2/18
 * Time: 10:26
 * Email: trongpq@magenest.com
 */

namespace Magenest\GiftRegistry\Ui\Component\Listing\Column\Order;

use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry\CollectionFactory;
use Magenest\GiftRegistry\Model\ResourceModel\Registrant\CollectionFactory as RegistrantFactory;
use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class ViewGift
 * @package Magenest\GiftRegistry\Ui\Component\Listing\Column\Order
 */
class ViewGift extends Column
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
     * @var RegistrantFactory
     */
    protected $registrantFactory;

    /**
     * @var CollectionFactory
     */
    protected $giftregistryCollection;

    /**
     * @var string
     */
    private $_editUrl = 'giftregistry/registry/edit';

    /**
     * ViewGift constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlBuilder $actionUrlBuilder
     * @param UrlInterface $urlBuilder
     * @param RegistrantFactory $registrantFactory
     * @param CollectionFactory $giftregistryCollection
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlBuilder $actionUrlBuilder,
        UrlInterface $urlBuilder,
        RegistrantFactory $registrantFactory,
        CollectionFactory $giftregistryCollection,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->registrantFactory = $registrantFactory;
        $this->giftregistryCollection = $giftregistryCollection;
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
                if (isset($item['giftregistry_id'])&&$this->checkExistGR($item['giftregistry_id'])) {
                    $registryType = $this->getRegistryType($item['giftregistry_id']);
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl($this->_editUrl, ['registrant_id' => $this->getRegistrantId($item['giftregistry_id'])]),
                        'label' => $registryType,
                        'hidden' => false,
                    ];
                }
            }
        }
        return $dataSource;
    }

    /**
     * @param $giftregistryId
     * @return array|mixed
     */
    public function getRegistryType($giftregistryId)
    {
        $giftRegistryModel = $this->giftregistryCollection->create()->addFieldToFilter('gift_id', $giftregistryId)->getFirstItem();
        $type = $giftRegistryModel->getData('type');
        if (!empty($type)) {
            return $type;
        }
    }

    /**
     * @param $giftregistryId
     * @return mixed
     */
    private function getRegistrantId($giftregistryId)
    {
        $registrantId = "0";
        $registrant = $this->registrantFactory->create();
        $registrantData = $registrant->addFieldToFilter('giftregistry_id', $giftregistryId)->getFirstItem()->getData();
        if (isset($registrantData['registrant_id'])) {
            return $registrantData['registrant_id'];
        }
    }

    /**
     * @param $giftregistryId
     * @return bool
     */
    public function checkExistGR($giftregistryId)
    {
        $giftRegistryModel = $this->giftregistryCollection->create()->addFieldToFilter('gift_id', $giftregistryId)->getFirstItem();
        if ($giftRegistryModel->getId()) {
            return true;
        } else {
            return false;
        }
    }
}
