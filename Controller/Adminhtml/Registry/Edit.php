<?php
/**
 * Created by PhpStorm.
 * User: canh
 * Date: 25/12/2015
 * Time: 15:00
 */
namespace Magenest\GiftRegistry\Controller\Adminhtml\Registry;

use Magenest\GiftRegistry\Model\GiftRegistryFactory;
use Magenest\GiftRegistry\Model\RegistrantFactory;
use Magenest\GiftRegistry\Model\ResourceModel\GiftRegistry;
use Magenest\GiftRegistry\Model\ResourceModel\Registrant;
use Magenest\GiftRegistry\Model\ResourceModel\Type;
use Magenest\GiftRegistry\Model\TypeFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Page;
use Magento\Customer\Model\AddressFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Edit
 * @package Magenest\GiftRegistry\Controller\Adminhtml\Registry
 */
class Edit extends Action
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var RegistrantFactory
     */
    protected $registrantFactory;

    /**
     * @var GiftRegistryFactory
     */
    protected $giftRegistryFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var GiftRegistry
     */
    protected $giftRegistryResource;

    /**
     * @var Registrant
     */
    protected $registrantResource;

    /** @var TypeFactory $_typeFactory */
    protected $_typeFactory;

    /** @var \Magenest\GiftRegistry\Model\ResourceModel\Type $_typeResource */
    protected $_typeResource;

    /**
     * @var Json
     *
     */
    protected $json;

    /**
     * @var AddressFactory
     *
     */
    protected $addressFactory;

    /**
     * Edit constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param LoggerInterface $logger
     * @param Registry $registry
     * @param RegistrantFactory $registrantFactory
     * @param GiftRegistryFactory $giftRegistryFactory
     * @param Session $session
     * @param GiftRegistry $giftRegistryResource
     * @param Registrant $registrantResource
     * @param TypeFactory $typeFactory
     * @param Type $typeResource
     * @param Json $json
     * @param AddressFactory $addressFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        LoggerInterface $logger,
        Registry $registry,
        RegistrantFactory $registrantFactory,
        GiftRegistryFactory $giftRegistryFactory,
        Session $session,
        GiftRegistry $giftRegistryResource,
        Registrant $registrantResource,
        TypeFactory $typeFactory,
        Type $typeResource,
        Json $json,
        AddressFactory $addressFactory
//        Collection $registrantCollection
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_logger = $logger;
        $this->registrantFactory = $registrantFactory;
        $this->giftRegistryFactory = $giftRegistryFactory;
        $this->session = $session;
        $this->giftRegistryResource = $giftRegistryResource;
        $this->registrantResource = $registrantResource;
        $this->_typeFactory = $typeFactory;
        $this->_typeResource = $typeResource;
        $this->json = $json;
        $this->addressFactory = $addressFactory;
//        $this->registrantCollection = $registrantCollection;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_GiftRegistry::manager');
    }

    /**
     * Init actions
     *
     * @return Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /**
         * @var Page $resultPage
         */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magenest_GiftRegistry::registry')
            ->addBreadcrumb(__('Manage Event Registry'), __('Manage Event Registry'));
        return $resultPage;
    }

    /**
     * Edit Mapping
     *
     * @return Redirect|Page
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $params = $this->getRequest()->getParams();

        // 1.2 check registrant id exist
        if (!isset($params['registrant_id'])) {
            $this->messageManager->addErrorMessage(__('This registry no longer exists.'));
            /**
             * \Magento\Backend\Model\View\Result\Redirect $resultRedirect
             */
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }
        $model = $this->registrantFactory->create();
        $this->registrantResource->load($model, $params['registrant_id']);
        $modelRegistry = $this->giftRegistryFactory->create();
        $this->giftRegistryResource->load($modelRegistry, $model->getGiftregistryId());
        $modelEventType = $this->_typeFactory->create();
        $typeEvent = $modelRegistry->getData('type');
        $this->_typeResource->load($modelEventType, $typeEvent, 'event_type');
        $additions_fields = $modelEventType->getData('additions_field');
        $allAdditionalFields = [
            'general_information' => [],
            'registrant_information' => [],
            'privacy' => [],
            'shipping_address_information' => [],
            'additional_section' => []
        ];
        if ($additions_fields != "") {
            $aboutEvents = $this->json->unserialize($additions_fields);
            foreach ($aboutEvents as $field) {
                if (!empty($field['group'])) {
                    $allAdditionalFields[$field['group']][] = $field;
                }
            }
        }

        $giftOptions = $modelRegistry->getData('gift_options');
        if (!empty($giftOptions)) {
            $giftOptions = $this->json->unserialize($giftOptions);
            foreach ($giftOptions as $type => $giftOption) {
                foreach ($giftOption as $id => $value) {
                    if ($type == 'date') {
                        $value = date_create_from_format('m-d-Y', $value)->format('Y-m-d');
                    }
                    $modelRegistry[$type . '_' . $id] = $value;
                }
            }
        }

        if (isset($modelRegistry['shipping_address'])) {
            $addressData = $this->addressFactory->create()->load($modelRegistry['shipping_address']);
            $modelRegistry['shipping_address'] = $addressData->getName() . ' ' . $addressData->getStreetFull() . ' ' .
                $addressData->getRegion() . ' ' . $addressData->getCountry();
        }

        // 2. Initial checking
        if ($params['registrant_id']) {
            $this->registrantResource->load($model, $params['registrant_id']);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This registry no longer exists.'));
                /**
                 * \Magento\Backend\Model\View\Result\Redirect $resultRedirect
                 */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        // 3. Set entered data if was error when we do save
        $data = $this->session->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->_coreRegistry->register('registry', $model);
        $this->_coreRegistry->register('information', $modelRegistry);
        $this->_coreRegistry->register('all_addition_fields', $allAdditionalFields);

        // 5. Build edit form
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()
            ->prepend(__('Manage Gift Registry'));

        return $resultPage;
    }
}
