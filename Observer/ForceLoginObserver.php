<?php

/**
 * Mazeapi Software.
 *
 * @package   Mazeapi_ForceLogin
 * @author    Mazeapi
 * @license   https://mazeapi.com/license.html
 */

namespace Mazeapi\ForceLogin\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;


class ForceLoginObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var \Strativ\CustomView\Helper\Data
     */
    protected $_strativHelper;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_adminSession;

    /**
     * CustomerRegisterObserver constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param Strativ\CustomView\Helper\Data $strativHelper
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Strativ\CustomView\Helper\Data $strativHelper
    )
    {
        $this->_customerSession = $customerSession;
        $this->redirect = $redirect;
        $this->_adminSession = $authSession;
        $this->_strativHelper = $strativHelper;
    }

    public function execute(Observer $observer)
    {
        if ($this->_strativHelper->isForceLoginEnabled()) {

            $actionName = $observer->getEvent()->getRequest()->getFullActionName();


            $controller = $observer->getControllerAction();


            $openActions = array(
                'customer_account_create',
                'customer_account_createpost',
                'customer_account_login',
                'customer_account_loginpost',
                'customer_account_logoutsuccess',
                'customer_account_forgotpassword',
                'customer_account_forgotpasswordpost',
                'customer_account_resetpassword',
                'customer_account_resetpasswordpost',
                'customer_account_confirm',
                'customer_account_confirmation',
            );
            
            if ($this->_adminSession->isLoggedIn()) {
                return $this;
            }
            if (preg_match('/^(adminhtml_\w+)/', $actionName)) {
                return $this;
            }
            if (in_array($actionName, $openActions) && !$this->_customerSession->isLoggedIn()) {
                return $this; //if in allowed actions do nothing.
            } elseif (!$this->_customerSession->isLoggedIn()) {
                $this->redirect->redirect($controller->getResponse(), 'customer/account/login');
            } else {
                return $this;
            }
        }

        return $this;

    }

}