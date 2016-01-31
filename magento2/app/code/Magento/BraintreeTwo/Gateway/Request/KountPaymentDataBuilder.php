<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Request;

use Magento\BraintreeTwo\Gateway\Config\Config;
use Magento\BraintreeTwo\Gateway\Helper\SubjectReader;
use Magento\BraintreeTwo\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class KountPaymentDataBuilder
 */
class KountPaymentDataBuilder implements BuilderInterface
{
    /**
     * Additional data for Advanced Fraud Tools
     */
    const DEVICE_DATA = 'deviceData';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param Config $config
     * @param SubjectReader $subjectReader
     */
    public function __construct(Config $config, SubjectReader $subjectReader)
    {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $result = [];
        if (!$this->config->hasFraudProtection()) {
            return $result;
        }
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $payment = $paymentDO->getPayment();
        $data = $payment->getAdditionalInformation();

        if (isset($data[DataAssignObserver::DEVICE_DATA])) {
            $result[self::DEVICE_DATA] = $data[DataAssignObserver::DEVICE_DATA];
        }

        return $result;
    }
}
