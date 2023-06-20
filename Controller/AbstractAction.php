<?php

namespace Magenest\GiftRegistry\Controller;

use Magenest\GiftRegistry\Helper\Data;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

abstract class AbstractAction extends Action
{
    /**
     * @var Data
     */
    protected $data;

    /**
     * AbstractAction constructor.
     * @param Context $context
     * @param Data $data
     */
    public function __construct(
        Context $context,
        Data $data
    ){
        $this->data = $data;
        parent::__construct($context);
    }

    public function execute()
    {
        // TODO: Implement execute() method.
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->data->isEnableExtension()) {
            $this->_redirect('noroute');
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }
}
