<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Model\Adminhtml\Source\VaultProvidersMap;

/**
 * Class PaymentTokenRepositoryProxy
 * @api
 */
class PaymentTokenRepositoryProxy implements PaymentTokenRepositoryInterface
{
    /**
     * @var PaymentTokenRepositoryInterface[]
     */
    private $repositories;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $repository;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $nullRepository;

    /**
     * @var VaultPaymentInterface
     */
    private $vaultPayment;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Constructor
     *
     * @param PaymentTokenRepositoryInterface $nullRepository
     * @param VaultPaymentInterface $vaultPayment
     * @param ConfigInterface $config
     * @param ObjectManagerInterface $objectManager
     * @param PaymentTokenRepositoryInterface[] $repositories
     */
    public function __construct(
        PaymentTokenRepositoryInterface $nullRepository,
        VaultPaymentInterface $vaultPayment,
        ConfigInterface $config,
        ObjectManagerInterface $objectManager,
        array $repositories
    ) {
        $this->repositories = $repositories;
        $this->nullRepository = $nullRepository;
        $this->vaultPayment = $vaultPayment;
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    /**
     * @return PaymentTokenRepositoryInterface
     */
    private function getRepository()
    {
        if ($this->repository === null) {
            $this->repository = $this->getInstance();
        }

        return $this->repository;
    }

    /**
     * @return PaymentTokenRepositoryInterface
     */
    private function getInstance()
    {
        if (!$this->vaultPayment->isActive()) {
            return $this->nullRepository;
        }

        $methodCode = $this->config->getValue(VaultProvidersMap::VALUE_CODE);

        return isset($this->repositories[$methodCode])
            ? $this->objectManager->get($this->repositories[$methodCode])
            : $this->nullRepository;
    }

    /**
     * @inheritdoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria)
    {
        return $this->getRepository()->getList($searchCriteria);
    }

    /**
     * @inheritdoc
     */
    public function delete(PaymentTokenInterface $paymentToken)
    {
        return $this->getRepository()->delete($paymentToken);
    }

    /**
     * @inheritdoc
     */
    public function save(PaymentTokenInterface $paymentToken)
    {
        return $this->getRepository()->save($paymentToken);
    }

    /**
     * @inheritdoc
     */
    public function getById($entityId)
    {
        return $this->getRepository()->getById($entityId);
    }
}
