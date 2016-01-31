<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\OrderInvoiceNew;
use Magento\Sales\Test\Page\Adminhtml\OrderInvoiceView;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Shipping\Test\Page\Adminhtml\OrderShipmentView;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Create invoice from order on backend.
 */
class CreateInvoiceStep implements TestStepInterface
{
    /**
     * Orders Page.
     *
     * @var OrderIndex
     */
    protected $orderIndex;

    /**
     * Order View Page.
     *
     * @var SalesOrderView
     */
    protected $salesOrderView;

    /**
     * Order New Invoice Page.
     *
     * @var OrderInvoiceNew
     */
    protected $orderInvoiceNew;

    /**
     * Order invoice view page.
     *
     * @var OrderInvoiceView
     */
    protected $orderInvoiceView;

    /**
     * Order shipment view page.
     *
     * @var OrderShipmentView
     */
    protected $orderShipmentView;

    /**
     * OrderInjectable fixture.
     *
     * @var OrderInjectable
     */
    protected $order;

    /**
     * Invoice data.
     *
     * @var array|null
     */
    protected $data;

    /**
     * Whether Invoice is partial.
     *
     * @var string
     */
    protected $isInvoicePartial;

    /**
     * @construct
     * @param OrderIndex $orderIndex
     * @param SalesOrderView $salesOrderView
     * @param OrderInvoiceNew $orderInvoiceNew
     * @param OrderInvoiceView $orderInvoiceView
     * @param OrderInjectable $order
     * @param OrderShipmentView $orderShipmentView
     * @param array|null $data [optional]
     * @param string $isInvoicePartial [optional]
     */
    public function __construct(
        OrderIndex $orderIndex,
        SalesOrderView $salesOrderView,
        OrderInvoiceNew $orderInvoiceNew,
        OrderInvoiceView $orderInvoiceView,
        OrderInjectable $order,
        OrderShipmentView $orderShipmentView,
        $data = null,
        $isInvoicePartial = null
    ) {
        $this->orderIndex = $orderIndex;
        $this->salesOrderView = $salesOrderView;
        $this->orderInvoiceNew = $orderInvoiceNew;
        $this->orderInvoiceView = $orderInvoiceView;
        $this->order = $order;
        $this->orderShipmentView = $orderShipmentView;
        $this->data = $data;
        $this->isInvoicePartial = $isInvoicePartial;
    }

    /**
     * Create invoice (with shipment optionally) for order on backend.
     *
     * @return array
     */
    public function run()
    {
        $this->orderIndex->open();
        $this->orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $this->order->getId()]);
        $this->salesOrderView->getPageActions()->invoice();
        if (!empty($this->data)) {
            $this->orderInvoiceNew->getFormBlock()->fillProductData(
                $this->data,
                $this->order->getEntityId()['products']
            );
            $this->orderInvoiceNew->getFormBlock()->updateQty();
            $this->orderInvoiceNew->getFormBlock()->fillFormData($this->data);
            if (isset($this->isInvoicePartial)) {
                $this->orderInvoiceNew->getFormBlock()->submit();
                $this->salesOrderView->getPageActions()->invoice();
            }
        }
        $this->orderInvoiceNew->getFormBlock()->submit();
        $invoiceIds = $this->getInvoiceIds();
        if (!empty($this->data)) {
            $shipmentIds = $this->getShipmentIds();
        }

        return [
            'ids' => [
                'invoiceIds' => $invoiceIds,
                'shipmentIds' => isset($shipmentIds) ? $shipmentIds : null,
            ]
        ];
    }

    /**
     * Get invoice ids.
     *
     * @return array
     */
    protected function getInvoiceIds()
    {
        $this->salesOrderView->getOrderForm()->openTab('invoices');
        return $this->salesOrderView->getOrderForm()->getTab('invoices')->getGridBlock()->getIds();
    }

    /**
     * Get shipment ids.
     *
     * @return array
     */
    protected function getShipmentIds()
    {
        $this->salesOrderView->getOrderForm()->openTab('shipments');
        return $this->salesOrderView->getOrderForm()->getTab('shipments')->getGridBlock()->getIds();
    }
}
