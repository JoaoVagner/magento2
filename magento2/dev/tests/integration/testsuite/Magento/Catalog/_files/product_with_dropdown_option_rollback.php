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

$productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Catalog\Api\ProductRepositoryInterface');
try {
    $product = $productRepository->get('simple_dropdown_option');
    $product->delete();
} catch (NoSuchEntityException $e) {

}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
