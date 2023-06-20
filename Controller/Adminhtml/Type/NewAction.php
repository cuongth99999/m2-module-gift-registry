<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 24/12/2015
 * Time: 11:51
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml\Type;

use Magento\Backend\Model\View\Result\Forward;

/**
 * Class NewAction
 * @package Magenest\GiftRegistry\Controller\Adminhtml\Type
 */
class NewAction extends Type
{
    /**
     * Forward to edit
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
