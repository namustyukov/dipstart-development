<?php

class PaymentWidget extends CWidget {
    
    public $projectId;
    public $hints;
    
    public function init() {
        
        Yii::app()->clientScript->registerScriptFile('/js/project_payment.js');
        $payments = ProjectPayments::model()->find('order_id = :ORDER_ID', array(
            ':ORDER_ID' => $this->projectId
        ));
        /*
         * убрал генерацию мусорных записей в таблицу
        if (!$payment) {
            $payment = new ProjectPayments;
            $payment->order_id = $this->projectId;
            $payment->received = 0;
            $payment->to_receive = 0;
            $payment->to_pay = 0;
            $payment->save();
        }
         * 
         */
        $this->renderForm($payments);
        
    }
    
    public function renderForm($payments) {
        $userRole = User::model()->getUserRole();
        if ($userRole == 'Admin') {
            $userRole = 'Manager';
        }
        $this->render('view'.$userRole, array(
            'model'     => $payments,
            'projectId' => $this->projectId,
            'zakaz'     => Zakaz::model()->findByPk($this->projectId)
        ));
    }

}