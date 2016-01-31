<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\BraintreeTwo\Observer\DataAssignObserver;

/**
 * Class DataAssignObserverTest
 */
class DataAssignObserverTest extends \PHPUnit_Framework_TestCase
{
    const PAYMENT_METHOD_NONCE = 'nonce';
    const DEVICE_DATA = '{"test": "test"}';

    public function testExecute()
    {
        $observerContainer = $this->getMockBuilder(Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentInfoModel = $this->getMock(InfoInterface::class);
        $dataObject = new DataObject(
            [
                'payment_method_nonce' => self::PAYMENT_METHOD_NONCE,
                'device_data' => self::DEVICE_DATA,
            ]
        );
        $observerContainer->expects(static::atLeastOnce())
            ->method('getEvent')
            ->willReturn($event);
        $event->expects(static::exactly(2))
            ->method('getDataByKey')
            ->willReturnMap(
                [
                    [AbstractDataAssignObserver::MODEL_CODE, $paymentInfoModel],
                    [AbstractDataAssignObserver::DATA_CODE, $dataObject]
                ]
            );
        $paymentInfoModel->expects(static::at(0))
            ->method('setAdditionalInformation')
            ->with('payment_method_nonce', self::PAYMENT_METHOD_NONCE);
        $paymentInfoModel->expects(static::at(1))
            ->method('setAdditionalInformation')
            ->with('device_data', self::DEVICE_DATA);

        $observer = new DataAssignObserver();
        $observer->execute($observerContainer);
    }
}
