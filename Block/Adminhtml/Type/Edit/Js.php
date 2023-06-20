<?php
namespace Magenest\GiftRegistry\Block\Adminhtml\Type\Edit;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\File\Size;

/**
 * Class Js
 * @package Magenest\GiftRegistry\Block\Adminhtml\Type\Edit
 */
class Js extends Template
{
    /**
     * @var Size
     */
    protected $_fileSize;

    /**
     * Js constructor.
     * @param Size $fileSize
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Size $fileSize,
        Context $context,
        array $data = []
    ) {
        $this->_fileSize = $fileSize;
        parent::__construct($context, $data);
    }

    /**
     * @return float
     */
    public function getMaxUploadSize()
    {
        return $this->_fileSize->getMaxFileSizeInMb();
    }
}
