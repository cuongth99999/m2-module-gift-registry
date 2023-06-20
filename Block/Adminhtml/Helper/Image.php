<?php
/**
 * Created by PhpStorm.
 * User: trongpq
 * Date: 8/4/17
 * Time: 1:40 PM
 */

namespace Magenest\GiftRegistry\Block\Adminhtml\Helper;

use Magento\Framework\Data\Form\Element\CollectionFactory as ElementCollectionFactory;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;
use Magento\Framework\Data\Form\Element\Image as ImageField;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;

/**
 * Class Image
 * @package Magenest\GiftRegistry\Block\Adminhtml\Helper
 */
class Image extends ImageField
{
    /**
     * @var Image
     */
    protected $imageModel;

    /**
     * Image constructor.
     * @param \Magenest\GiftRegistry\Model\Theme\Image $imageModel
     * @param ElementFactory $factoryElement
     * @param ElementCollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        \Magenest\GiftRegistry\Model\Theme\Image $imageModel,
        ElementFactory $factoryElement,
        ElementCollectionFactory $factoryCollection,
        Escaper $escaper,
        UrlInterface $urlBuilder,
        $data = []
    ) {
        $this->imageModel = $imageModel;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $urlBuilder, $data);
    }

    /**
     * Get image preview url
     *
     * @return string
     */
    protected function _getUrl()
    {
        $url = false;
        if ($this->getValue()) {
            $url = $this->imageModel->getBaseUrl() . $this->getValue();
        }
        return $url;
    }
}
