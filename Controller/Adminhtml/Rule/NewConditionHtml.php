<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Controller\Adminhtml\Rule;

use Magento\Framework\App\ResponseInterface;
use Magento\Rule\Model\Condition\AbstractCondition;
use Faonni\SmartCategory\Controller\Adminhtml\Rule as Action;
use Faonni\SmartCategory\Model\Rule;

/**
 * NewConditionHtml controller
 */
class NewConditionHtml extends Action
{
    /**
     * New condition html
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $request = $this->getRequest();
        $id = $request->getParam('id');
        $formName = $request->getParam('form_namespace');
        $typeArr = explode('|', str_replace('-', '/', $request->getParam('type')));
        $type = $typeArr[0];

        $model = $this->_objectManager->create($type)
            ->setId($id)
            ->setType($type)
            ->setRule($this->_objectManager->create(Rule::class))
            ->setPrefix('conditions');

        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof AbstractCondition) {
            $model->setJsFormObject($request->getParam('form'));
            $model->setFormName($formName);
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
}
