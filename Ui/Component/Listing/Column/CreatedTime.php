<?php
namespace Magenest\GiftRegistry\Ui\Component\Listing\Column;

use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class CreatedTime
 * @package Magenest\GiftRegistry\Ui\Component\Listing\Column
 */
class CreatedTime extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param TimezoneInterface $timezone
     * @param BooleanUtils $booleanUtils
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        TimezoneInterface $timezone,
        BooleanUtils $booleanUtils,
        array $components = [],
        array $data = []
    ) {
        $this->timezone = $timezone;
        $this->booleanUtils = $booleanUtils;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['created_time'])
                    && $item['created_time'] !== "0000-00-00 00:00:00"
                ) {
                    $date = $this->timezone->date(new \DateTime($item['created_time']));
                    $timezone = isset($this->getConfiguration()['timezone'])
                        ? $this->booleanUtils->convert($this->getConfiguration()['timezone'])
                        : true;
                    if (!$timezone) {
                        $date = new \DateTime($item['created_time']);
                    }
                    $item['created_time'] = $date->format('Y-m-d');
                }
            }
        }

        return $dataSource;
    }
}
