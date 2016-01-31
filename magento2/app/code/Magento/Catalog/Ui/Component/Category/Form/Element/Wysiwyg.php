<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component\Category\Form\Element;

use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Helper\Data as DataHelper;
use Magento\Catalog\Api\CategoryAttributeRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Editor as EditorElement;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Wysiwyg\ConfigInterface;
use Magento\Framework\View\LayoutInterface;

/**
 * Catalog Wysiwyg
 */
class Wysiwyg extends \Magento\Ui\Component\Form\Element\Wysiwyg
{
    /**
     * @var DataHelper
     */
    protected $backendHelper;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * Wysiwyg constructor.
     * @param ContextInterface $context
     * @param Form $form
     * @param EditorElement $editorElement
     * @param ConfigInterface $wysiwygConfig
     * @param LayoutInterface $layout
     * @param DataHelper $backendHelper
     * @param CategoryAttributeRepositoryInterface $attrRepository
     * @param array $components
     * @param array $data
     * @param array $config
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ContextInterface $context,
        Form $form,
        EditorElement $editorElement,
        ConfigInterface $wysiwygConfig,
        LayoutInterface $layout,
        DataHelper $backendHelper,
        CategoryAttributeRepositoryInterface $attrRepository,
        array $components = [],
        array $data = [],
        array $config = []
    ) {
        $this->layout = $layout;
        $this->backendHelper = $backendHelper;

        $editorElement->setData('wysiwyg', (bool)$attrRepository->get($data['name'])->getIsWysiwygEnabled());
        parent::__construct($context, $form, $editorElement, $wysiwygConfig, $components, $data, $config);
        $this->setData($this->prepareData($this->getData()));
    }

    /**
     * Prepare wysiwyg content
     *
     * @param array $data
     * @return array
     */
    private function prepareData($data)
    {
        if ($this->editorElement->isEnabled()) {
            $data['config']['content'] .= $this->getWysiwygButtonHtml();
        }
        return $data;
    }

    /**
     * Return wysiwyg button html
     *
     * @return string
     */
    private function getWysiwygButtonHtml()
    {
        return $this->layout->createBlock(
            Button::class,
            '',
            [
                'data' => [
                    'label' => __('WYSIWYG Editor'),
                    'type' => 'button',
                    'class' => 'action-wysiwyg',
                    'onclick' => 'catalogWysiwygEditor.open(\'' . $this->backendHelper->getUrl(
                        'catalog/product/wysiwyg'
                    ) . '\', \'' . $this->editorElement->getHtmlId() . '\')',
                ]
            ]
        )->toHtml();
    }
}
