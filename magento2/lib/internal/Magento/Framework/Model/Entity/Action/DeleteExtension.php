<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity\Action;

use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\ExtensionPool;

/**
 * Class DeleteExtension
 */
class DeleteExtension
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var ExtensionPool
     */
    protected $extensionPool;

    /**
     * @param MetadataPool $metadataPool
     * @param ExtensionPool $extensionPool
     */
    public function __construct(
        MetadataPool $metadataPool,
        ExtensionPool $extensionPool
    ) {
        $this->metadataPool = $metadataPool;
        $this->extensionPool = $extensionPool;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @param array $data
     * @return object
     * @throws \Exception
     */
    public function execute($entityType, $entity, $data = [])
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        if ($metadata->getEavEntityType()) {
            $hydrator = $this->metadataPool->getHydrator($entityType);
            $entityData = array_merge($hydrator->extract($entity), $data);
            $actions = $this->extensionPool->getActions($entityType, 'delete');
            foreach ($actions as $action) {
                $action->execute($entityType, $entityData);
            }
            $entity = $hydrator->hydrate($entity, $entityData);
        }
        return $entity;
    }
}
