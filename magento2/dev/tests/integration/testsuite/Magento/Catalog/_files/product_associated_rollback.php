<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\Exception\NoSuchEntityException;

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$repository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Catalog\Model\ProductRepository'
);
try {
    $product = $repository->get('simple');
    $product->delete();
} catch (NoSuchEntityException $e) {
    //Entity already deleted
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
