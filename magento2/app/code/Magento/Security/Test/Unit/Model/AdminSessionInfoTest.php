<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test class for \Magento\Security\Model\AdminSessionInfo testing
 */
class AdminSessionInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  \Magento\Security\Model\AdminSessionInfo
     */
    protected $model;

    /**
     * @var \Magento\Security\Helper\SecurityConfig
     */
    protected $securityConfigMock;

    /**
     * @var  \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Init mocks for tests
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->securityConfigMock =  $this->getMock(
            'Magento\Security\Helper\SecurityConfig',
            ['getAdminSessionLifetime', 'getCurrentTimestamp'],
            [],
            '',
            false
        );

        $this->model = $this->objectManager->getObject(
            '\Magento\Security\Model\AdminSessionInfo',
            [
                'securityConfig' => $this->securityConfigMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testIsLoggedInStatus()
    {
        $this->model->setData('status', \Magento\Security\Model\AdminSessionInfo::LOGGED_IN);
        $this->assertEquals(true, $this->model->isLoggedInStatus());
    }

    /**
     * @param bool $expectedResult
     * @param string $sessionLifetime
     * @dataProvider dataProviderSessionLifetime
     */
    public function testSessionExpired($expectedResult, $sessionLifetime)
    {
        $timestamp = time();

        $this->securityConfigMock->expects($this->once())
            ->method('getAdminSessionLifetime')
            ->will($this->returnValue($sessionLifetime));

        $this->securityConfigMock->expects($this->once())
            ->method('getCurrentTimestamp')
            ->willReturn($timestamp);

        $this->model->setUpdatedAt(
            date("Y-m-d H:i:s", $timestamp - 1)
        );

        $this->assertEquals($expectedResult, $this->model->isSessionExpired());
    }

    /**
     * @return array
     */
    public function dataProviderSessionLifetime()
    {
        return [
            ['expectedResult' => true, 'sessionLifetime' => '0'],
            ['expectedResult' => true, 'sessionLifetime' => '1'],
            ['expectedResult' => false, 'sessionLifetime' => '2']
        ];
    }

    /**
     * @param bool $expectedResult
     * @param bool $sessionLifetime
     * @dataProvider dataProviderIsActive
     */
    public function testIsActive($expectedResult, $sessionLifetime)
    {
        $this->model->setData('status', \Magento\Security\Model\AdminSessionInfo::LOGGED_IN);
        $this->securityConfigMock->expects($this->any())
            ->method('getAdminSessionLifetime')
            ->will($this->returnValue($sessionLifetime));
        $this->securityConfigMock->expects($this->any())
            ->method('getCurrentTimestamp')
            ->will($this->returnValue(10));
        $this->model->setUpdatedAt(9);

        $this->assertEquals($expectedResult, $this->model->isActive());
    }

    /**
     * @return array
     */
    public function dataProviderIsActive()
    {
        return [
            ['expectedResult' => false, 'sessionLifetime' => '0'],
            ['expectedResult' => false, 'sessionLifetime' => '1'],
            ['expectedResult' => true, 'sessionLifetime' => '2']
        ];
    }

    /**
     * @return void
     */
    public function testGetFormattedIp()
    {
        $formattedIp = '127.0.0.1';
        $longIp = 2130706433;
        $this->model->setIp($longIp);
        $this->assertEquals($formattedIp, $this->model->getFormattedIp());
    }

    /**
     * @return void
     */
    public function testIsOtherSessionsTerminated()
    {
        $this->assertEquals(false, $this->model->isOtherSessionsTerminated());
    }

    /**
     * @param bool $isOtherSessionsTerminated
     * @dataProvider dataProviderIsOtherSessionsTerminated
     */
    public function testSetIsOtherSessionsTerminated($isOtherSessionsTerminated)
    {
        $this->assertInstanceOf(
            '\Magento\Security\Model\AdminSessionInfo',
            $this->model->setIsOtherSessionsTerminated($isOtherSessionsTerminated)
        );
    }

    /**
     * @return array
     */
    public function dataProviderIsOtherSessionsTerminated()
    {
        return [[true], [false]];
    }
}
