<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\Block\Adminhtml;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\ElementInterface;

/**
 * Is used to represent a new unified form with collapsible sections on the page.
 */
class FormSections extends AbstractFormContainers
{
    /**
     * CSS locator of the section collapsible title
     *
     * @var string
     */
    protected $sectionTitle = '.fieldset-wrapper-title';

    /**
     * CSS locator of the section content
     *
     * @var string
     */
    protected $content = '.admin__fieldset-wrapper-content';

    /**
     * XPath locator of the collapsible fieldset
     *
     * @var string
     */
    protected $collapsible =
        'div[contains(@class,"fieldset-wrapper")][contains(@class,"admin__collapsible-block-wrapper")]';

    /**
     * Get Section class.
     *
     * @param string $sectionName
     * @return Section
     * @throws \Exception
     */
    public function getSection($sectionName)
    {
        return $this->getContainer($sectionName);
    }

    /**
     * {@inheritdoc}
     */
    protected function openContainer($sectionName)
    {
        return $this->openSection($sectionName);
    }

    /**
     * Get the section title element
     *
     * @param string $sectionName
     * @return ElementInterface
     */
    protected function getSectionTitleElement($sectionName)
    {
        $container = $this->getContainerElement($sectionName);
        return $container->find($this->sectionTitle);
    }

    /**
     * Opens the section.
     *
     * @param string $sectionName
     * @return $this
     */
    public function openSection($sectionName)
    {
        if ($this->isCollapsible($sectionName)) {
            $this->getSectionTitleElement($sectionName)->click();
        } else {
            //Scroll to the top of the page so that the page actions header does not overlap any controls
            $this->browser->find($this->header)->hover();
        }
        return $this;
    }

    /**
     * Checks if the section is collapsible on the form.
     *
     * @param string $sectionName
     * @return bool
     */
    public function isCollapsible($sectionName)
    {
        $title = $this->getSectionTitleElement($sectionName);
        if (!$title->isVisible()) {
            return false;
        };
        return $title->find('parent::' . $this->collapsible, Locator::SELECTOR_XPATH)->isVisible();
    }
}
