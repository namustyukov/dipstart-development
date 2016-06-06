<?php

Yii::import('application.extensions.booster.widgets.TbModal');

class TemplateModal extends TbModal
{
    public $types;
	public $orderId;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $this->render('template_modal', ['types' => $this->types, 'orderId' => $this->orderId]);
        parent::run();
    }
}