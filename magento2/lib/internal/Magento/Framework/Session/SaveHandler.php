<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session;

use Magento\Framework\Exception\SessionException;
use Magento\Framework\Session\Config\ConfigInterface;

/**
 * Magento session save handler
 */
class SaveHandler implements SaveHandlerInterface
{
    /**
     * Session handler
     *
     * @var \SessionHandler
     */
    protected $saveHandlerAdapter;

    /**
     * Constructor
     *
     * @param SaveHandlerFactory $saveHandlerFactory
     * @param ConfigInterface $config
     * @param string $default
     */
    public function __construct(
        SaveHandlerFactory $saveHandlerFactory,
        ConfigInterface $config,
        $default = self::DEFAULT_HANDLER
    ) {
        $saveMethod = $config->getSaveHandlerName();
        try {
            $connection = $saveHandlerFactory->create($saveMethod);
        } catch (SessionException $e) {
            $connection = $saveHandlerFactory->create($default);
            $config->setSaveHandler($default);
        }
        $this->saveHandlerAdapter = $connection;
    }

    /**
     * Open Session - retrieve resources
     *
     * @param string $savePath
     * @param string $name
     * @return bool
     */
    public function open($savePath, $name)
    {
        return $this->saveHandlerAdapter->open($savePath, $name);
    }

    /**
     * Close Session - free resources
     *
     * @return bool
     */
    public function close()
    {
        return $this->saveHandlerAdapter->close();
    }

    /**
     * Read session data
     *
     * @param string $sessionId
     * @return string
     */
    public function read($sessionId)
    {
        return $this->saveHandlerAdapter->read($sessionId);
    }

    /**
     * Write Session - commit data to resource
     *
     * @param string $sessionId
     * @param string $data
     * @return bool
     */
    public function write($sessionId, $data)
    {
        return $this->saveHandlerAdapter->write($sessionId, $data);
    }

    /**
     * Destroy Session - remove data from resource for given session id
     *
     * @param string $sessionId
     * @return bool
     */
    public function destroy($sessionId)
    {
        return $this->saveHandlerAdapter->destroy($sessionId);
    }

    /**
     * Garbage Collection - remove old session data older
     * than $maxLifetime (in seconds)
     *
     * @param int $maxLifetime
     * @return bool
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function gc($maxLifetime)
    {
        return $this->saveHandlerAdapter->gc($maxLifetime);
    }
}
