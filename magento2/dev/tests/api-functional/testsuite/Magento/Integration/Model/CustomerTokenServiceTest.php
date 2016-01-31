<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\InputException;
use Magento\Integration\Model\Oauth\Token as TokenModel;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\User\Model\User as UserModel;
use Magento\Framework\Webapi\Exception as HTTPExceptionCodes;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory;

/**
 * api-functional test for \Magento\Integration\Model\CustomerTokenService.
 */
class CustomerTokenServiceTest extends WebapiAbstract
{
    const SERVICE_NAME = "integrationCustomerTokenServiceV1";
    const SERVICE_VERSION = "V1";
    const RESOURCE_PATH_CUSTOMER_TOKEN = "/V1/integration/customer/token";
    const RESOURCE_PATH_ADMIN_TOKEN = "/V1/integration/admin/token";

    /**
     * @var CustomerTokenServiceInterface
     */
    private $tokenService;

    /**
     * @var AccountManagementInterface
     */
    private $customerAccountManagement;

    /**
     * @var CollectionFactory
     */
    private $tokenCollection;

    /**
     * @var UserModel
     */
    private $userModel;

    /**
     * Setup CustomerTokenService
     */
    public function setUp()
    {
        $this->_markTestAsRestOnly();
        $this->tokenService = Bootstrap::getObjectManager()->get('Magento\Integration\Model\CustomerTokenService');
        $this->customerAccountManagement = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Api\AccountManagementInterface'
        );
        $tokenCollectionFactory = Bootstrap::getObjectManager()->get(
            'Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory'
        );
        $this->tokenCollection = $tokenCollectionFactory->create();
        $this->userModel = Bootstrap::getObjectManager()->get('Magento\User\Model\User');
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCreateCustomerAccessToken()
    {
        $customerUserName = 'customer@example.com';
        $password = 'password';
        $isTokenCorrect = false;

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_CUSTOMER_TOKEN,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
        ];
        $requestData = ['username' => $customerUserName, 'password' => $password];
        $accessToken = $this->_webApiCall($serviceInfo, $requestData);

        $customerData = $this->customerAccountManagement->authenticate($customerUserName, $password);

        /** @var $this->tokenCollection \Magento\Integration\Model\ResourceModel\Oauth\Token\Collection */
        $this->tokenCollection->addFilterByCustomerId($customerData->getId());

        foreach ($this->tokenCollection->getItems() as $item) {
            /** @var $item TokenModel */
            if ($item->getToken() == $accessToken) {
                $isTokenCorrect = true;
            }
        }
        $this->assertTrue($isTokenCorrect);
    }

    /**
     * @dataProvider validationDataProvider
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testCreateCustomerAccessTokenEmptyOrNullCredentials($username, $password)
    {
        try {
            $serviceInfo = [
                'rest' => [
                    'resourcePath' => self::RESOURCE_PATH_CUSTOMER_TOKEN,
                    'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
                ],
            ];
            $requestData = ['username' => '', 'password' => ''];
            $this->_webApiCall($serviceInfo, $requestData);
        } catch (\Exception $e) {
            $this->assertInputExceptionMessages($e);
        }
    }

    public function testCreateCustomerAccessTokenInvalidCustomer()
    {
        $customerUserName = 'invalid';
        $password = 'invalid';
        try {
            $serviceInfo = [
                'rest' => [
                    'resourcePath' => self::RESOURCE_PATH_CUSTOMER_TOKEN,
                    'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
                ],
            ];
            $requestData = ['username' => $customerUserName, 'password' => $password];
            $this->_webApiCall($serviceInfo, $requestData);
        } catch (\Exception $e) {
            $this->assertEquals(HTTPExceptionCodes::HTTP_UNAUTHORIZED, $e->getCode());
            $exceptionData = $this->processRestExceptionResult($e);
            $expectedExceptionData = ['message' => 'Invalid login or password.'];
        }
        $this->assertEquals($expectedExceptionData, $exceptionData);
    }

    /**
     * Provider to test input validation
     *
     * @return array
     */
    public function validationDataProvider()
    {
        return [
            'Check for empty credentials' => ['', ''],
            'Check for null credentials' => [null, null]
        ];
    }

    /**
     * Assert for presence of Input exception messages
     *
     * @param \Exception $e
     */
    private function assertInputExceptionMessages($e)
    {
        $this->assertEquals(HTTPExceptionCodes::HTTP_BAD_REQUEST, $e->getCode());
        $exceptionData = $this->processRestExceptionResult($e);
        $expectedExceptionData = [
            'message' => InputException::DEFAULT_MESSAGE,
            'errors' => [
                [
                    'message' => InputException::REQUIRED_FIELD,
                    'parameters' => [
                        'fieldName' => 'username',
                    ],
                ],
                [
                    'message' => InputException::REQUIRED_FIELD,
                    'parameters' => [
                        'fieldName' => 'password',
                    ]
                ],
            ],
        ];
        $this->assertEquals($expectedExceptionData, $exceptionData);
    }
}
