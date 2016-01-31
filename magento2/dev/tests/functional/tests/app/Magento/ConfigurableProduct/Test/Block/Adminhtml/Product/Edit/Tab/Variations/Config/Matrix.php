<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Variations\Config;

use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\Client\Locator;
use Magento\Backend\Test\Block\Template;
use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Class Matrix
 * Matrix row form
 */
class Matrix extends Form
{
    /**
     * Mapping for get optional fields
     *
     * @var array
     */
    protected $mappingGetFields = [
        'name' => [
            'selector' => 'td[data-column="name"] > a',
            'strategy' => Locator::SELECTOR_CSS,
        ],
        'sku' => [
            'selector' => 'td[data-column="sku"]',
            'strategy' => Locator::SELECTOR_CSS,
        ],
        'price' => [
            'selector' => 'td[data-column="price"]',
            'strategy' => Locator::SELECTOR_CSS,
        ],
        'quantity_and_stock_status' => [
            'composite' => 1,
            'fields' => [
                'qty' => [
                    'selector' => 'td[data-column="qty"]',
                    'strategy' => Locator::SELECTOR_CSS,
                ],
            ],
        ],
        'weight' => [
            'selector' => 'td[data-column="weight"]',
            'strategy' => Locator::SELECTOR_CSS,
        ],
    ];

    /**
     * Selector for variation row by number
     *
     * @var string
     */
    protected $variationRowByNumber = './/tr[@data-role="row"][%d]';

    /**
     * Selector for variation row
     *
     * @var string
     */
    protected $variationRow = 'tr[data-role="row"]';

    // @codingStandardsIgnoreStart
    /**
     * Selector for row on product grid by product id
     *
     * @var string
     */
    protected $associatedProductGrid = 'div[data-grid-id="associated-products-container"]';
    // @codingStandardsIgnoreEnd

    /**
     * Selector for template block.
     *
     * @var string
     */
    protected $template = './ancestor::body';

    /**
     * Delete variation button selector.
     *
     * @var string
     */
    protected $deleteVariation = '[data-bind*="removeProduct"]';

    /**
     * Choose a different Product button selector.
     *
     * @var string
     */
    protected $chooseProduct = '[data-bind*="showGrid"]';

    /**
     * Action menu
     *
     * @var string
     */
    protected $actionMenu = '.action-select';

    /**
     * Fill variations.
     *
     * @param array $matrix
     * @return void
     */
    public function fillVariations(array $matrix)
    {
        $count = 1;
        foreach ($matrix as $variation) {
            $variationRow = $this->_rootElement->find(
                sprintf($this->variationRowByNumber, $count),
                Locator::SELECTOR_XPATH
            );
            ++$count;

            if (isset($variation['configurable_attribute'])) {
                $this->assignProduct($variationRow, $variation['sku']);
                continue;
            }

            $mapping = $this->dataMapping($variation);
            $this->_fill($mapping, $variationRow);
        }
    }

    /**
     * Assign product to variation matrix
     *
     * @param ElementInterface $variationRow
     * @param string $productSku
     * @return void
     */
    protected function assignProduct(ElementInterface $variationRow, $productSku)
    {
        $variationRow->find($this->actionMenu)->hover();
        $variationRow->find($this->actionMenu)->click();
        $variationRow->find($this->chooseProduct)->click();
        $this->getTemplateBlock()->waitLoader();
        $this->getAssociatedProductGrid()->searchAndSelect(['sku' => $productSku]);
    }

    /**
     * Get variations data
     *
     * @return array
     */
    public function getVariationsData()
    {
        $data = [];
        $variationRows = $this->_rootElement->getElements($this->variationRow);

        foreach ($variationRows as $key => $variationRow) {
            /** @var SimpleElement $variationRow */
            if ($variationRow->isVisible()) {
                $data[$key] = $this->getDataFields($variationRow, $this->mappingGetFields);
            }
        }

        return $data;
    }

    /**
     * Get variation fields.
     *
     * @param SimpleElement $context
     * @param array $fields
     * @return array
     */
    protected function getDataFields(SimpleElement $context, array $fields)
    {
        $data = [];

        foreach ($fields as $name => $params) {
            if (isset($params['composite']) && $params['composite']) {
                $data[$name] = $this->getDataFields($context, $params['fields']);
            } else {
                $data[$name] = $context->find($params['selector'], $params['strategy'])->getText();
            }
        }
        return $data;
    }

    /**
     * Get template block.
     *
     * @return Template
     */
    public function getTemplateBlock()
    {
        return $this->blockFactory->create(
            'Magento\Backend\Test\Block\Template',
            ['element' => $this->_rootElement->find($this->template, Locator::SELECTOR_XPATH)]
        );
    }

    public function deleteVariations()
    {
        $variations = $this->_rootElement->getElements($this->variationRow);
        foreach (array_reverse($variations) as $variation) {
            $variation->find($this->actionMenu)->hover();
            $variation->find($this->actionMenu)->click();
            $variation->find($this->deleteVariation)->click();
        }
    }

    /**
     * @return \Magento\Ui\Test\Block\Adminhtml\DataGrid
     */
    public function getAssociatedProductGrid()
    {
        return $this->blockFactory->create(
            'Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\AssociatedProductGrid',
            ['element' => $this->browser->find($this->associatedProductGrid)]
        );
    }
}
