<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Unit\Model;

class RuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $coupon;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\SalesRule\Model\Rule\Condition\CombineFactory
     */
    protected $conditionCombineFactoryMock;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $condProdCombineFactoryMock;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->coupon = $this->getMockBuilder('Magento\SalesRule\Model\Coupon')
            ->disableOriginalConstructor()
            ->setMethods(['loadPrimaryByRule', 'setRule', 'setIsPrimary', 'getCode', 'getUsageLimit'])
            ->getMock();

        $couponFactory = $this->getMockBuilder('Magento\SalesRule\Model\CouponFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $couponFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->coupon);

        $this->conditionCombineFactoryMock = $this->getMockBuilder(
            '\Magento\SalesRule\Model\Rule\Condition\CombineFactory'
        )->disableOriginalConstructor()
            ->getMock();

        $this->condProdCombineFactoryMock = $this->getMockBuilder(
            '\Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory'
        )->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            'Magento\SalesRule\Model\Rule',
            [
                'couponFactory' => $couponFactory,
                'condCombineFactory' => $this->conditionCombineFactoryMock,
                'condProdCombineF' => $this->condProdCombineFactoryMock,
            ]
        );
    }

    public function testLoadCouponCode()
    {
        $this->coupon->expects($this->once())
            ->method('loadPrimaryByRule')
            ->with(1);
        $this->coupon->expects($this->once())
            ->method('setRule')
            ->with($this->model)
            ->willReturnSelf();
        $this->coupon->expects($this->once())
            ->method('setIsPrimary')
            ->with(true)
            ->willReturnSelf();
        $this->coupon->expects($this->once())
            ->method('getCode')
            ->willReturn('test_code');
        $this->coupon->expects($this->once())
            ->method('getUsageLimit')
            ->willReturn(1);

        $this->model->setId(1);
        $this->model->setUsesPerCoupon(null);
        $this->model->setUseAutoGeneration(false);

        $this->model->loadCouponCode();
        $this->assertEquals(1, $this->model->getUsesPerCoupon());
    }

    public function testBeforeSaveResetConditionToNull()
    {
        $conditionMock = $this->setupConditionMock();

        //Make sure that we reset _condition in beforeSave method
        $this->conditionCombineFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($conditionMock);

        $prodConditionMock = $this->setupProdConditionMock();
        $this->condProdCombineFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($prodConditionMock);

        $this->model->beforeSave();
        $this->model->getConditions();
        $this->model->getActions();
    }

    protected function setupProdConditionMock()
    {
        $prodConditionMock = $this->getMockBuilder('\Magento\SalesRule\Model\Rule\Condition\Product\Combine')
            ->disableOriginalConstructor()
            ->setMethods(['setRule', 'setId', 'loadArray', 'getConditions'])
            ->getMock();

        $prodConditionMock->expects($this->any())
            ->method('setRule')
            ->willReturnSelf();
        $prodConditionMock->expects($this->any())
            ->method('setId')
            ->willReturnSelf();
        $prodConditionMock->expects($this->any())
            ->method('getConditions')
            ->willReturn([]);

        return $prodConditionMock;
    }

    protected function setupConditionMock()
    {
        $conditionMock = $this->getMockBuilder('\Magento\SalesRule\Model\Rule\Condition\Combine')
            ->disableOriginalConstructor()
            ->setMethods(['setRule', 'setId', 'loadArray', 'getConditions'])
            ->getMock();
        $conditionMock->expects($this->any())
            ->method('setRule')
            ->willReturnSelf();
        $conditionMock->expects($this->any())
            ->method('setId')
            ->willReturnSelf();
        $conditionMock->expects($this->any())
            ->method('getConditions')
            ->willReturn([]);

        return $conditionMock;
    }
}
