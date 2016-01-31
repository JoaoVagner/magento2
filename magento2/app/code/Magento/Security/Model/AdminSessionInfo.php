<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model;

/**
 * Admin Session Info Model
 *
 * @method string getSessionId()
 * @method int getUserId() getUserId()
 * @method int getStatus()
 * @method string getUpdatedAt()
 * @method string getCreatedAt()
 */
class AdminSessionInfo extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Admin session status definition
     */

    /**
     * Admin logged in
     */
    const LOGGED_IN = 1;

    /**
     * Admin logged out
     */
    const LOGGED_OUT = 0;

    /**
     * User has been logged out by another login with the same credentials
     */
    const LOGGED_OUT_BY_LOGIN = 2;

    /**
     * User has been logged out manually from another session
     */
    const LOGGED_OUT_MANUALLY = 3;

    /**
     * All other open sessions were terminated
     */
    protected $isOtherSessionsTerminated = false;

    /**
     * @var \Magento\Security\Helper\SecurityConfig
     */
    protected $securityConfig;

    /**
     * AdminSessionInfo constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Security\Helper\SecurityConfig $securityConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Security\Helper\SecurityConfig $securityConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->securityConfig = $securityConfig;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Security\Model\ResourceModel\AdminSessionInfo');
    }

    /**
     * Check if a status is logged in
     *
     * @return bool
     */
    public function isLoggedInStatus()
    {
        return $this->getData('status') == self::LOGGED_IN;
    }

    /**
     * Check if a user is active
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->isLoggedInStatus() && !$this->isSessionExpired();
    }

    /**
     * Check whether the session is expired
     *
     * @return bool
     */
    public function isSessionExpired()
    {
        $lifetime = $this->securityConfig->getAdminSessionLifetime();
        $currentTime = $this->securityConfig->getCurrentTimestamp();
        $lastUpdatedTime = $this->getUpdatedAt();
        if (!is_numeric($lastUpdatedTime)) {
            $lastUpdatedTime = strtotime($lastUpdatedTime);
        }

        return $lastUpdatedTime <= ($currentTime - $lifetime) ? true : false;
    }

    /**
     * Get formatted IP
     *
     * @return string
     */
    public function getFormattedIp()
    {
        return long2ip($this->getIp());
    }

    /**
     * Check if other sessions terminated
     *
     * @return bool
     */
    public function isOtherSessionsTerminated()
    {
        return $this->isOtherSessionsTerminated;
    }

    /**
     * Setter for isOtherSessionsTerminated
     *
     * @param bool $isOtherSessionsTerminated
     * @return this
     */
    public function setIsOtherSessionsTerminated($isOtherSessionsTerminated)
    {
        $this->isOtherSessionsTerminated = (bool) $isOtherSessionsTerminated;
        return $this;
    }
}
