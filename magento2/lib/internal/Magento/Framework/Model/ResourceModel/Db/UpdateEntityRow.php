<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\ResourceModel\Db;

use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Model\Entity\EntityMetadata;

/**
 * Class ReadEntityRow
 */
class UpdateEntityRow
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        MetadataPool $metadataPool
    ) {
        $this->metadataPool = $metadataPool;
    }

    /**
     * @param EntityMetadata $metadata
     * @param array $data
     * @return array
     */
    protected function prepareData(EntityMetadata $metadata, $data)
    {
        $output = [];
        foreach ($metadata->getEntityConnection()->describeTable($metadata->getEntityTable()) as $column) {

            if ($column['DEFAULT'] == 'CURRENT_TIMESTAMP' || $column['IDENTITY']) {
                continue;
            }
            if (isset($data[strtolower($column['COLUMN_NAME'])])) {
                $output[strtolower($column['COLUMN_NAME'])] = $data[strtolower($column['COLUMN_NAME'])];
            }
        }
        return $output;
    }

    /**
     * @param string $entityType
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    public function execute($entityType, $data)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $connection = $metadata->getEntityConnection();
        return $connection->update(
            $metadata->getEntityTable(),
            $this->prepareData($metadata, $data),
            [$metadata->getLinkField() . ' = ?' => $data[$metadata->getLinkField()]]
        );
    }
}
