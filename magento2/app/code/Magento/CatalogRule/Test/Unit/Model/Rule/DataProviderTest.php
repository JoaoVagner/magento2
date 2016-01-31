<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Test\Unit\Model\Rule;

class DataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Rule\DataProvider
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionMock;

    protected function setUp()
    {
        $this->collectionFactoryMock = $this->getMock(
            'Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->searchCriteriaBuilderMock = $this->getMock(
            'Magento\Framework\Api\SearchCriteriaBuilder',
            [],
            [],
            '',
            false
        );
        $this->storeMock = $this->getMock('Magento\Store\Model\System\Store', [], [], '', false);
        $this->groupRepositoryMock = $this->getMock('Magento\Customer\Api\GroupRepositoryInterface', [], [], '', false);
        $this->dataObjectMock = $this->getMock('Magento\Framework\Convert\DataObject', [], [], '', false);

        $this->collectionMock = $this->getMock(
            'Magento\CatalogRule\Model\ResourceModel\Rule\Collection',
            [],
            [],
            '',
            false
        );
        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->collectionMock);
        $searchCriteriaMock = $this->getMock('Magento\Framework\Api\SearchCriteriaInterface', [], [], '', false);
        $groupSearchResultsMock = $this->getMock(
            'Magento\Customer\Api\Data\GroupSearchResultsInterface',
            [],
            [],
            '',
            false
        );
        $groupsMock = $this->getMock('Magento\Customer\Api\Data\GroupInterface', [], [], '', false);

        $this->searchCriteriaBuilderMock->expects($this->once())->method('create')->willReturn($searchCriteriaMock);
        $this->groupRepositoryMock->expects($this->once())->method('getList')->with($searchCriteriaMock)
            ->willReturn($groupSearchResultsMock);
        $groupSearchResultsMock->expects($this->once())->method('getItems')->willReturn([$groupsMock]);
        $this->storeMock->expects($this->once())->method('getWebsiteValuesForForm')->willReturn([]);
        $this->dataObjectMock->expects($this->once())->method('toOptionArray')->with([$groupsMock], 'id', 'code')
            ->willReturn([]);

        $this->model = new \Magento\CatalogRule\Model\Rule\DataProvider(
            'Name',
            'Primary',
            'Request',
            $this->collectionFactoryMock,
            $this->storeMock,
            $this->groupRepositoryMock,
            $this->searchCriteriaBuilderMock,
            $this->dataObjectMock
        );
    }

    public function testGetData()
    {
        $ruleId = 42;
        $ruleData = ['name' => 'Catalog Price Rule'];

        $ruleMock = $this->getMock('Magento\CatalogRule\Model\Rule', [], [], '', false);
        $this->collectionMock->expects($this->once())->method('getItems')->willReturn([$ruleMock]);

        $ruleMock->expects($this->atLeastOnce())->method('getId')->willReturn($ruleId);
        $ruleMock->expects($this->once())->method('load')->willReturnSelf();
        $ruleMock->expects($this->once())->method('getData')->willReturn($ruleData);

        $this->assertEquals([$ruleId => $ruleData], $this->model->getData());
        // Load from object-cache the second time
        $this->assertEquals([$ruleId => $ruleData], $this->model->getData());
    }
}
