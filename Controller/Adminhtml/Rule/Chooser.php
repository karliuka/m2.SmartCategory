<?php
/**
 * Faonni
 *  
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade module to newer
 * versions in the future.
 * 
 * @package     Faonni_SmartCategory
 * @copyright   Copyright (c) 2016 Karliuka Vitalii(karliuka.vitalii@gmail.com) 
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Faonni\SmartCategory\Controller\Adminhtml\Rule;

class Chooser extends \Faonni\SmartCategory\Controller\Adminhtml\Rule
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
                    'Faonni\SmartCategory\Block\Adminhtml\Chooser\Sku',
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
