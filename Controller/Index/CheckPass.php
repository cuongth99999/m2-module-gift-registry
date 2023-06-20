<?php
/**
 * Created by Magenest.
 * User: trongpq
 * Date: 4/23/18
 * Time: 12:38
 * Email: trongpq@magenest.com
 */

namespace Magenest\GiftRegistry\Controller\Index;

use Magenest\GiftRegistry\Controller\AbstractAction;
use Magenest\GiftRegistry\Helper\Data;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\View\Result\Page;

class CheckPass extends AbstractAction
{
    /**
     * CheckPass constructor.
     * @param Context $context
     * @param Data $data
     */
    public function __construct(
        Context $context,
        Data $data
    ) {
        return parent::__construct($context, $data);
    }

    /**
     * @return ResultInterface|Layout|Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getPostValue();
        $password = str_replace(' ', '', isset($params['pass1']) ? $params['pass1'] : "");
        $information = [];
        $pass = sha1($password);
        if ($pass == $params['pass2']) {
            $information['check'] = true;
        } else {
            $information['check'] = false;
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($information);
        return $resultJson;
    }
}
