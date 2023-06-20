<?php
/**
 * Created by Magenest.
 * User: trongpq
 * Date: 4/23/18
 * Time: 08:02
 * Email: trongpq@magenest.com
 */

namespace Magenest\GiftRegistry\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RouterInterface;

/**
 * Class Router
 * @package Magenest\GiftRegistry\Controller
 */
class Router implements RouterInterface
{
    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * Response
     *
     * @var ResponseInterface
     */
    protected $_response;

    /**
     * @param ActionFactory $actionFactory
     * @param ResponseInterface $response
     */
    public function __construct(
        ActionFactory $actionFactory,
        ResponseInterface $response
    ) {
        $this->actionFactory = $actionFactory;
        $this->_response = $response;
    }

    /**
     * Validate and Match
     *
     * @param RequestInterface $request
     * @return ActionInterface|null
     */
    public function match(RequestInterface $request)
    {
        $pathInfo = $request->getPathInfo();
        $identifier = trim($pathInfo, '/');
        $identifier = str_replace("//", "/", $identifier);
        if (strpos($identifier, 'gift-registry/manage') !== false) {
            $arr = explode('gift-registry/manage/', $identifier);
            if (isset($arr[1])) {
                $type = explode('/', $arr[1]);
                $id = isset($type[2]) ? $type[2] : $type[1];
                if (strpos($identifier, 'add') !== false) {
                    //Add item to GiftRegistry through QuickOrder
                    $product = isset($type[4]) ? $type[4] : null;
                    $request->setModuleName('gift_registry')
                            ->setControllerName('index')
                            ->setActionName('add')
                            ->setParam('giftregistry', $id)
                            ->setParam('product', $product);
                } else {
                    //Manage GiftRegistry
                    $request->setModuleName('gift_registry')
                        ->setControllerName('index')
                        ->setActionName('manageregistry')
                        ->setParam('id', $id);
                }
            }
        } elseif (strpos($identifier, 'gift-registry.html') !== false) {
            //List GiftRegistry
            $request->setModuleName('gift_registry')
                ->setControllerName('index')
                ->setActionName('listgift');
        } elseif (strpos($identifier, 'giftregistry/wedding.html') !== false) {
            //Show WeddingGift
            $request->setModuleName('gift_registry')
                ->setControllerName('index')
                ->setActionName('showgift')
                ->setParam('type', 'weddinggift');
        } elseif (strpos($identifier, 'gift-registry/event') !== false) {
            $arr = explode('gift-registry/event/', $identifier);
            if (isset($arr[1])) {
                $type = explode('.', $arr[1]);
                $decode_url = $giftRegistryType = urldecode($type[0]);
                //Show Gift Registry by Event Type
                $request->setModuleName('gift_registry')
                    ->setControllerName('index')
                    ->setActionName('showgift')
                    ->setParam('type', $decode_url);
            }
        } elseif (strpos($identifier, 'gift-registry/new') !== false) {
            $arr = explode('gift-registry/new/', $identifier);
            if (isset($arr[1])) {
                $type = explode('.', $arr[1]);
                //New GiftRegistry
                $request->setModuleName('gift_registry')
                    ->setControllerName('index')
                    ->setActionName('newgift')
                    ->setParam('type', $type[0]);
            }
        } elseif (strpos($identifier, 'gift-registry/view') !== false) {
            $arr = explode('gift-registry/view/', $identifier);
            if (isset($arr[1])) {
                $type = explode('/', $arr[1]);
                //View GiftRegistry
                $request->setModuleName('gift_registry')
                    ->setControllerName('guest')
                    ->setActionName('view')
                    ->setParam('gift', $type[1]);
            }
        } elseif (strpos($identifier, 'gift-registry/search') !== false) {
            //List Search
            $request->setModuleName('gift_registry')
                ->setControllerName('index')
                ->setActionName('listsearch');
        } elseif (strpos($identifier, 'customer/giftregistry') !== false) {
            //My Gift Registry
            $request->setModuleName('gift_registry')
                ->setControllerName('customer')
                ->setActionName('mygiftregistry');
        } else {
            return null;
        }
        return $this->actionFactory->create('Magento\Framework\App\Action\Forward', ['request' => $request]);
    }
}
