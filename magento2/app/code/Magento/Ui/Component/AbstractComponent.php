<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\DataSourceInterface;
use Magento\Framework\View\Element\UiComponent\ObserverInterface;

/**
 * Abstract class AbstractComponent
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractComponent extends DataObject implements UiComponentInterface
{
    /**
     * Render context
     *
     * @var ContextInterface
     */
    protected $context;

    /**
     * @var UiComponentInterface[]
     */
    protected $components;

    /**
     * @var array
     */
    protected $componentData = [];

    /**
     * @var DataSourceInterface[]
     */
    protected $dataSources = [];

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        $this->context = $context;
        $this->components = $components;
        $this->initObservers($data);
        $this->context->getProcessor()->register($this);
        $this->_data = array_replace_recursive($this->_data, $data);
    }

    /**
     * Get component context
     *
     * @return ContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData('name');
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        if ($this->getData(UiComponentFactory::IMPORT_CHILDREN_FROM_META)) {
            $children = (array)$this->getContext()->getDataProvider()->getMeta();
            foreach ($children as $name => $childData) {
                $this->createChildComponent($name, $childData);
            }
        }

        $jsConfig = $this->getJsConfig($this);
        if (isset($jsConfig['provider'])) {
            unset($jsConfig['extends']);
            $this->getContext()->addComponentDefinition($this->getName(), $jsConfig);
        } else {
            $this->getContext()->addComponentDefinition($this->getComponentName(), $jsConfig);
        }

        if ($this->hasData('actions')) {
            $this->getContext()->addActions($this->getData('actions'), $this);
        }

        if ($this->hasData('buttons')) {
            $this->getContext()->addButtons($this->getData('buttons'), $this);
        }
        $this->getContext()->getProcessor()->notify($this->getComponentName());
    }

    /**
     * Create child Ui Component
     *
     * @param string $name
     * @param array $childData
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function createChildComponent($name, array $childData)
    {
        if (empty($childData)) {
            return $this;
        }

        $childComponent = $this->getComponent($name);
        if ($childComponent === null) {
            $argument = [
                'context' => $this->getContext(),
                'data' => [
                    'name' => $name,
                    'config' => $childData
                ]
            ];

            if (!isset($childData['componentType'])) {
                throw new LocalizedException(
                    __('The configuration parameter "componentType" is a required for "%1" component.', $name)
                );
            }

            $childComponent = $this->getContext()
                ->getUiComponentFactory()
                ->create($name, $childData['componentType'], $argument);
            $this->prepareChildComponent($childComponent);
            $this->addComponent($name, $childComponent);
        } else {
            $this->updateComponent($childData, $childComponent);
        }

        return $this;
    }

    /**
     * Call prepare method in the component UI
     *
     * @param UiComponentInterface $component
     * @return $this
     */
    protected function prepareChildComponent(UiComponentInterface $component)
    {
        $childComponents = $component->getChildComponents();
        if (!empty($childComponents)) {
            foreach ($childComponents as $child) {
                $this->prepareChildComponent($child);
            }
        }
        $component->prepare();

        return $this;
    }

    /**
     * Produce and return block's html output
     *
     * @return string
     */
    public function toHtml()
    {
        $this->render();
    }

    /**
     * Render component
     *
     * @return string
     */
    public function render()
    {
        $result = $this->getContext()->getRenderEngine()->render($this, $this->getTemplate());
        return $result;
    }

    /**
     * Add component
     *
     * @param string $name
     * @param UiComponentInterface $component
     * @return void
     */
    public function addComponent($name, UiComponentInterface $component)
    {
        $this->components[$name] = $component;
    }

    /**
     * @param string $name
     * @return UiComponentInterface
     */
    public function getComponent($name)
    {
        return isset($this->components[$name]) ? $this->components[$name] : null;
    }

    /**
     * Get components
     *
     * @return UiComponentInterface[]
     */
    public function getChildComponents()
    {
        return $this->components;
    }

    /**
     * Render child component
     *
     * @param string $name
     * @return string
     */
    public function renderChildComponent($name)
    {
        $result = null;
        if (isset($this->components[$name])) {
            $result = $this->components[$name]->render();
        }
        return $result;
    }

    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->getData('template') . '.xhtml';
    }

    /**
     * Get component configuration
     *
     * @return array
     */
    public function getConfiguration()
    {
        return (array)$this->getData('config');
    }

    /**
     * Get configuration of related JavaScript Component
     * (force extending the root component if component does not extend other component)
     *
     * @param UiComponentInterface $component
     * @return array
     */
    public function getJsConfig(UiComponentInterface $component)
    {
        $jsConfig = (array)$component->getData('js_config');
        if (!isset($jsConfig['extends'])) {
            $jsConfig['extends'] = $component->getContext()->getNamespace();
        }
        return $jsConfig;
    }

    /**
     * Component data setter
     *
     * @param string|array $key
     * @param mixed $value
     * @return void
     */
    public function setData($key, $value = null)
    {
        parent::setData($key, $value);
    }

    /**
     * Component data getter
     *
     * @param string $key
     * @param string|int $index
     * @return mixed
     */
    public function getData($key = '', $index = null)
    {
        return parent::getData($key, $index);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        return $dataSource;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataSourceData()
    {
        return [];
    }

    /**
     * Initiate observers
     *
     * @param array $data
     * @return void
     */
    protected function initObservers(array & $data = [])
    {
        if (isset($data['observers']) && is_array($data['observers'])) {
            foreach ($data['observers'] as $observerType => $observer) {
                if (!is_object($observer)) {
                    $observer = $this;
                }
                if ($observer instanceof ObserverInterface) {
                    $this->getContext()->getProcessor()->attach($observerType, $observer);
                }
                unset($data['observers']);
            }
        }
    }

    /**
     * Update component data
     *
     * @param array $componentData
     * @param UiComponentInterface $component
     * @return $this
     */
    protected function updateComponent(array $componentData, UiComponentInterface $component)
    {
        $config = $component->getData('config');
        // XML data configuration override configuration coming from the DB
        $config = array_replace_recursive($componentData, $config);
        $component->setData('config', $config);

        return $this;
    }

    /**
     * Update DataScope
     *
     * @param array $data
     * @param string $name
     * @return array
     */
    protected function updateDataScope(array $data, $name)
    {
        if (!isset($data['dataScope'])) {
            $data['dataScope'] = $name;
        }
        return $data;
    }
}
