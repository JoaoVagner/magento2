<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\ResourceModel;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Model\Entity\MetadataPool;

/**
 * Class AttributePersistor
 */
class AttributePersistor
{
    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var FormatInterface
     */
    protected $localeFormat;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var array
     */
    protected $insert = [];

    /**
     * @var array
     */
    protected $update = [];

    /**
     * @var array
     */
    protected $delete = [];

    /**
     * @param FormatInterface $localeFormat
     * @param AttributeRepositoryInterface $attributeRepository
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        FormatInterface $localeFormat,
        AttributeRepositoryInterface $attributeRepository,
        MetadataPool $metadataPool
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->metadataPool = $metadataPool;
        $this->localeFormat = $localeFormat;
    }

    /**
     * @param string $entityType
     * @param int $link
     * @param string $attributeCode
     * @return void
     */
    public function registerDelete($entityType, $link, $attributeCode)
    {
        $this->delete[$entityType][$link][$attributeCode] = null;
    }

    /**
     * @param string $entityType
     * @param int $link
     * @param string $attributeCode
     * @param mixed $value
     * @return void
     */
    public function registerUpdate($entityType, $link, $attributeCode, $value)
    {
        $this->update[$entityType][$link][$attributeCode] = $value;
    }

    /**
     * @param string $entityType
     * @param int $link
     * @param string $attributeCode
     * @param mixed $value
     * @return void
     */
    public function registerInsert($entityType, $link, $attributeCode, $value)
    {
        $this->insert[$entityType][$link][$attributeCode] = $value;
    }

    /**
     * @param string $entityType
     * @param array $context
     * @return void
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processDeletes($entityType, $context)
    {
        if (!isset($this->delete[$entityType]) || !is_array($this->delete[$entityType])) {
            return;
        }
        $metadata = $this->metadataPool->getMetadata($entityType);
        foreach ($this->delete[$entityType] as $link => $data) {
            $attributeCodes = array_keys($data);
            foreach ($attributeCodes as $attributeCode) {
                /** @var AbstractAttribute $attribute */
                $attribute = $this->attributeRepository->get($metadata->getEavEntityType(), $attributeCode);
                $conditions = [
                    $metadata->getLinkField() . ' = ?' => $link,
                    'attribute_id = ?' => $attribute->getAttributeId()
                ];
                foreach ($context as $field => $value) {
                    $conditions[$metadata->getEntityConnection()->quoteIdentifier($field) . ' = ?'] = $value;
                }
                $metadata->getEntityConnection()->delete(
                    $attribute->getBackend()->getTable(),
                    $conditions
                );
            }
        }
    }

    /**
     * @param string $entityType
     * @param array $context
     * @return void
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processInserts($entityType, $context)
    {
        if (!isset($this->insert[$entityType]) || !is_array($this->insert[$entityType])) {
            return;
        }
        $metadata = $this->metadataPool->getMetadata($entityType);
        foreach ($this->insert[$entityType] as $link => $data) {
            foreach ($data as $attributeCode => $attributeValue) {
                /** @var AbstractAttribute $attribute */
                $attribute = $this->attributeRepository->get(
                    $metadata->getEavEntityType(),
                    $attributeCode
                );
                $data = [
                    $metadata->getLinkField() => $link,
                    'attribute_id' => $attribute->getAttributeId(),
                    'value' => $this->prepareValue($entityType, $attributeValue, $attribute)
                ];
                foreach ($context as $field => $value) {
                    $data[$field] = $value;
                }
                $metadata->getEntityConnection()->insert($attribute->getBackend()->getTable(), $data);
            }
        }
    }

    /**
     * @param string $entityType
     * @param array $context
     * @return void
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processUpdates($entityType, $context)
    {
        if (!isset($this->update[$entityType]) || !is_array($this->update[$entityType])) {
            return;
        }
        $metadata = $this->metadataPool->getMetadata($entityType);
        foreach ($this->update[$entityType] as $link => $data) {
            foreach ($data as $attributeCode => $attributeValue) {
                /** @var AbstractAttribute $attribute */
                $attribute = $this->attributeRepository->get(
                    $metadata->getEavEntityType(),
                    $attributeCode
                );
                $conditions = [
                    $metadata->getLinkField() . ' = ?' => $link,
                    'attribute_id = ?' => $attribute->getAttributeId(),
                ];
                foreach ($context as $field => $value) {
                    $conditions[$metadata->getEntityConnection()->quoteIdentifier($field) . ' = ?'] = $value;
                }
                $metadata->getEntityConnection()->update(
                    $attribute->getBackend()->getTable(),
                    [
                        'value' => $this->prepareValue($entityType, $attributeValue, $attribute)
                    ],
                    $conditions
                );
            }
        }
    }

    /**
     * Flush attributes to storage
     *
     * @param string $entityType
     * @param array $context
     * @return void
     */
    public function flush($entityType, $context)
    {
        $this->processDeletes($entityType, $context);
        $this->processInserts($entityType, $context);
        $this->processUpdates($entityType, $context);

        unset($this->delete, $this->insert, $this->update);
    }

    /**
     * @param string $entityType
     * @param string $value
     * @param AbstractAttribute $attribute
     * @return string
     * @throws \Exception
     */
    protected function prepareValue($entityType, $value, AbstractAttribute $attribute)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $type = $attribute->getBackendType();
        if (($type == 'int' || $type == 'decimal' || $type == 'datetime') && $value === '') {
            $value = null;
        } elseif ($type == 'decimal') {
            $value = $this->localeFormat->getNumber($value);
        }
        $describe = $metadata->getEntityConnection()->describeTable($attribute->getBackendTable());
        return $metadata->getEntityConnection()->prepareColumnValue($describe['value'], $value);
    }
}
