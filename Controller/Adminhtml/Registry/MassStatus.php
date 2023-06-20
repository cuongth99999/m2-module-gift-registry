<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/12/2015
 * Time: 23:02
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml\Registry;

use Magenest\GiftRegistry\Model\EventFactory;
use Magenest\GiftRegistry\Model\ResourceModel\Event;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Class MassStatus
 * @package Magenest\GiftRegistry\Controller\Adminhtml\Registry
 */
class MassStatus extends Action
{
    /**
     * @var EventFactory
     */
    protected $eventFactory;

    /**
     * @var Event
     */
    protected $eventResource;

    /**
     * MassStatus constructor.
     * @param Action\Context $context
     * @param EventFactory $eventFactory
     * @param Event $eventResource
     */
    public function __construct(
        Action\Context $context,
        EventFactory $eventFactory,
        Event $eventResource
    ) {
        $this->eventFactory = $eventFactory;
        $this->eventResource = $eventResource;
        parent::__construct($context);
    }

    /**
     * Update blog post(s) status action
     *
     * @return Redirect
     * @throws \Exception
     */
    public function execute()
    {
        $testIds = $this->getRequest()->getParam('registry');
        if (!is_array($testIds) || empty($testIds)) {
            $this->messageManager->addErrorMessage(__('Please select product(s).'));
        } else {
            try {
                $status = (int) $this->getRequest()->getParam('active');
                foreach ($testIds as $testId) {
                    $post = $this->eventFactory->create();
                    $this->eventResource->load($post, $testId);
                    $post->setStatus($status);
                    $this->eventResource->save($post);
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been updated.', count($testId))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_GiftRegistry::manager');
    }
}
