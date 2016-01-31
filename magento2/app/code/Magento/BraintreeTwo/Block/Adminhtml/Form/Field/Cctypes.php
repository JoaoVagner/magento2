<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Magento\BraintreeTwo\Helper\CcType;

/**
 * Class Cctypes
 */
class Cctypes extends Select
{
    /**
     * @var \
     */
    private $ccTypeHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\BraintreeTwo\Helper\CcType $ccTypeHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CcType $ccTypeHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->ccTypeHelper = $ccTypeHelper;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->ccTypeHelper->getCcTypes());
        }
        $this->setClass('cc-type-select');
        $this->setExtraParams('multiple="multiple"');
        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value . '[]');
    }
}
