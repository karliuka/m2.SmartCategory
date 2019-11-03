<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Config\Model\Config\Source\Yesno;

/**
 * Attribute prepare observer
 */
class AttributePrepareObserver implements ObserverInterface
{
    /**
     * Source Yesno
     *
     * @var Yesno
     */
    protected $yesNo;

    /**
     * Initialize Observer
     *
     * @param Yesno $yesNo
     */
    public function __construct(
        Yesno $yesNo
    ) {
        $this->yesNo = $yesNo;
    }

    /**
     * Handler for attribute prepare event
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $form = $observer->getEvent()->getForm();
        $yesnoSource = $this->yesNo->toOptionArray();
        $fieldset = $form->getElement('front_fieldset');

        if ($fieldset) {
            $fieldset->addField(
                'is_used_for_smart_rules',
                'select',
                [
                    'name' => 'is_used_for_smart_rules',
                    'label' => __('Use for Smart Category Rule'),
                    'title' => __('Use for Smart Category Rule'),
                    'values' => $yesnoSource,
                ],
                'is_used_for_promo_rules'
            );
        }
    }
}
