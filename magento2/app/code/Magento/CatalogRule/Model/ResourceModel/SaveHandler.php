<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\ResourceModel;

use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\Framework\Model\Entity\MetadataPool;

class SaveHandler
{
    /**
     * @var Rule
     */
    protected $ruleResource;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @param Rule $ruleResource
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        Rule $ruleResource,
        MetadataPool $metadataPool
    ) {
        $this->ruleResource = $ruleResource;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @param string $entityType
     * @param array $entityData
     * @return array
     * @throws \Exception
     */
    public function execute($entityType, $entityData)
    {
        $linkField = $this->metadataPool->getMetadata($entityType)->getLinkField();
        if (isset($entityData['website_ids'])) {
            $websiteIds = $entityData['website_ids'];
            if (!is_array($websiteIds)) {
                $websiteIds = explode(',', (string)$websiteIds);
            }
            $this->ruleResource->bindRuleToEntity($entityData[$linkField], $websiteIds, 'website');
        }

        if (isset($entityData['customer_group_ids'])) {
            $customerGroupIds = $entityData['customer_group_ids'];
            if (!is_array($customerGroupIds)) {
                $customerGroupIds = explode(',', (string)$customerGroupIds);
            }
            $this->ruleResource->bindRuleToEntity($entityData[$linkField], $customerGroupIds, 'customer_group');
        }
        return $entityData;
    }
}
