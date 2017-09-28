<?php
/**
 * Copyright © 2011-2017 Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * 
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Controller\Adminhtml\Rule;

use Faonni\SmartCategory\Controller\Adminhtml\Rule;

/**
 * SmartCategory chooser controller
 */
class Chooser extends Rule
{
    /**
     * Prepare block for chooser
     *
     * @return void
     */
    public function execute()
    {
        $request = $this->getRequest();

        switch ($request->getParam('attribute')) {
            case 'sku':
                $block = $this->_view->getLayout()->createBlock(
                    'Faonni\SmartCategory\Block\Adminhtml\Rule\Chooser\Sku',
                    'smartcategory_chooser_sku',
                    ['data' => ['js_form_object' => $request->getParam('form')]]
                );
                break;            
            case 'category_ids':
                $ids = $request->getParam('selected', []);
                if (is_array($ids)) {
                    foreach ($ids as $key => &$id) {
                        $id = (int)$id;
                        if ($id <= 0) {
                            unset($ids[$key]);
                        }
                    }
                    $ids = array_unique($ids);
                } else {
                    $ids = [];
                }
                $block = $this->_view->getLayout()->createBlock(
                    'Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree',
                    'smartcategory_chooser_category_ids',
                    ['data' => ['js_form_object' => $request->getParam('form')]]
                )->setCategoryIds(
                    $ids
                );
                break;

            default:
                $block = false;
                break;
        }

        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }
}
