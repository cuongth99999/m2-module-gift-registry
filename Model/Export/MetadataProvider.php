<?php
namespace Magenest\GiftRegistry\Model\Export;

use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Ui\Component\MassAction\Filter;

class MetadataProvider extends \Magento\Ui\Model\Export\MetadataProvider
{

    public function __construct(Filter $filter, TimezoneInterface $localeDate, ResolverInterface $localeResolver, $dateFormat = 'M j, Y h:i:s A', array $data = [])
    {
        parent::__construct($filter, $localeDate, $localeResolver, $dateFormat, $data);
    }

    /**
     * @param DocumentInterface $document
     * @param array $fields
     * @param array $options
     * @return array
     */
    public function getRowData(DocumentInterface $document, $fields, $options): array
    {
        $row = [];
        foreach ($fields as $column) {
            if (isset($options[$column])) {
                $key = $document->getCustomAttribute($column)->getValue();
                if (isset($options[$column][$key])) {
                    $row[] = $options[$column][$key];
                } else {
                    $row[] = '';
                }
            } else {
                $row[] = $document->getCustomAttribute($column)->getValue();
                if($column == 'status'){
                    $key = array_search ('status', $fields);
                    switch ($row[$key]){
                        case 1;
                            $row[$key] = __('Enable');
                            break;
                        case 2;
                            $row[$key] = __('Disable');
                            break;
                    }
                }
            }
        }
        return $row;
    }

}
