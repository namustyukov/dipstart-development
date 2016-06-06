<?php

class PaymentController extends Controller {

    protected $_request;
    protected $_response;

    /*public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
            'postOnly + delete, affiliatePayment', // we only allow deletion via POST request
        );
    }*/
    /*public function accessRules()
    {
        return array(
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions'=>array('view','apiview','approvetransaction','managersapprove','managerscancel','savepayments'),
                'users'=>array('admin','manager'),
            ),
			//array('allow',
			//	'actions'=>array('affiliatePayment'),
			//	'roles'=>array('customer'),
			//),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }*/
	
    protected function _prepairJson() {
        $this->_request = Yii::app()->jsonRequest;
        $this->_response = new JsonHttpResponse();
    }

    public function actionView() {
		$model = new Payment('search');
		$model->unsetAttributes();
		if(Yii::app()->request->isAjaxRequest) {
			$params = Yii::app()->request->getParam('Payment');
			$model->setAttributes($params);
			Yii::app()->user->setState('PaymentFilterState', $params);
			$test = '=);';
		}

		$data = $model->getTotalData();
			
		$data = array(
			'in' => array(
				'sum' => empty($data) ? 0 : $data[0]['s'],
				'count' => empty($data) ? 0 : $data[0]['ctn'],
			),
			'out' => array(
				'sum' => empty($data) ? 0 : $data[1]['s'],
				'count' => empty($data) ? 0 : $data[1]['ctn'],
			)
		);
		
		$this->render('admin',array(
			'model'=>$model,
			'data'=>$data,
			'test'=>$test,
		));
    }
	
	public function actionGetPayNumber($payType, $user) {
		echo Profile::model()->getPayNumber($payType, $user);
    }
	

    /*public function actionApiView() {
		$c_id = Company::getId();
		$table_prefix = '';
		if ($c_id) {
			$table_prefix = $c_id.'_';
		}
        $user = User::model()->findByPk(Yii::app()->user->id);
        if (!$user->superuser) {
            $this->redirect('/');
        } else {
            $this->_prepairJson();
            $sort = $this->_request->getParam('sort');
            $type = $this->_request->getParam('type');
            $searchField = $this->_request->getParam('search_field');
            $searchType = $this->_request->getParam('search_type');
            $searchString = $this->_request->getParam('search_string');
            if (!$sort) {
                $sort = 'receive_date';
            }
            if (!$type) {
                $type = 'DESC';
            }
            if ($searchField == '' || $searchString == '' || $searchType == '') {
                $sql = 'SELECT * FROM `'.$table_prefix.'Payment` ORDER BY `'.$sort.'` '.$type.' ; ';
            } else {
                switch ($searchType) {
                    case 'bigger':
                        $searchType = '<';
                        break;
                    case 'smaller':
                        $searchType = '>';
                        break;
                    case 'equal':
                        $searchType = '=';
                        break;
                }
                $sql = 'SELECT * FROM `'.$table_prefix.'Payment` WHERE `'.$searchField.'` '.$searchType.' '.$searchString.' ORDER BY `'.$sort.'` '.$type.' ; ';
            }
            $data = Payment::model()->findAllBySql($sql);
            $report = array();
            $report['summary'] = 0;
            $report['ids_count'] = 0;
            foreach ($data as $row) {
                $report['ids_count']++;
                if ($row->payment_type == Payment::INCOMING_CUSTOMER) {
                    $report['summary'] = $report['summary'] + $row->summ;
                } else {
                    $report['summary'] = $report['summary'] - $row->summ;
                }

            }
            $this->_response->setData( array(
                'data' => $data,
                'report' => $report
            ));
            $this->_response->send();
        }
    }*/

    /*public function actionAdmin() {
        $this->render('admin');
    }*/

     public function actionApproveTransaction(){  // Ajax approve in actionView
        $this->_prepairJson();
        $id = $this->_request->getParam('id');
        $method = $this->_request->getParam('method');
        $type = $this->_request->getParam('type');
        $number = $this->_request->getParam('number');
        if (!$method) {
            $method = 'Cash';
        }
		
        $payment = Payment::model()->findByPk($id);
        if (!$payment) {
            $this->_response->setData(array(
                'result'  => false,
                'message' => 'Not found'
            ));
            Yii::app()->end();
        }

		$this->_response->setData(array(
            'result' => $payment->approveFromBookkeeper($method, $type, $number)
        ));
        $this->_response->send();
    }
	
	public function actionRejectTransaction(){  // Ajax approve in actionView
        $this->_prepairJson();
        $id = $this->_request->getParam('id');
        $method = $this->_request->getParam('method');
        if (!$method) {
            $method = 'Cash';
        }

        $payment = Payment::model()->findByPk($id);
        if (!$payment) {
            $this->_response->setData(array(
                'result'  => false,
                'message' => 'Not found'
            ));
            Yii::app()->end();
        }

        $this->_response->setData(array(
            'result' => $payment->rejectFromBookkeeper($method)
        ));
        $this->_response->send();
    }
	
	public function actionCancelTransaction() {  // Ajax approve in actionView
        $this->_prepairJson();
        $id = $this->_request->getParam('id');
        $method = $this->_request->getParam('method');
        if (!$method) {
            $method = 'Cash';
        }

        $payment = Payment::model()->findByPk($id);
        if (!$payment) {
            $this->_response->setData(array(
                'result'  => false,
                'message' => 'Not found'
            ));
            Yii::app()->end();
        }

        $this->_response->setData(array(
            'result' => $payment->cancelPayment($method)
        ));
        $this->_response->send();
    }

    /*public function actionApproveFromBookkeeper() { // dubl ^

        $this->_prepairJson();
        $id = $this->_request->getParam('id');
        $method = $this->_request->getParam('method');
        if (!$method) {
            $method = 'Cash';
        }

        $payment = Payment::model()->findByPk($id);
        if (!$payment) {
            $this->_response->setData(array(
                'result'  => false,
                'message' => 'Not found'
            ));
            Yii::app()->end();
        }

        $this->_response->setData(array(
            'result' => $payment->approveFromBookkeeper($method)
        ));
        $this->_response->send();

    }*/

    public function actionSavePayments() { // Changes in payment block in order managment      // Не лишняя ли это функция?
        $this->_prepairJson();
        $orderId = $this->_request->getParam('order_id');
        $payment = ProjectPayments::model()->find('order_id = :ORDER_ID', array(
            ':ORDER_ID'=>$orderId
        ));
        if (!$payment) {
            $payment = new ProjectPayments;
            $payment->order_id = $orderId;
            $payment->received = 0;
            $payment->to_receive = 0;
            $payment->to_pay = 0;
        }
        
        $to_receive = $this->_request->getParam('to_receive', 0);
        
        if($this->_request->getParam('project_price')) $payment->project_price = $this->_request->getParam('project_price');
        if($this->_request->getParam('to_receive')) $payment->to_receive   += (int) $this->_request->getParam('to_receive');
        if(!($this->_request->getParam('work_price') === null)) $payment->work_price = $this->_request->getParam('work_price');
        if($this->_request->getParam('to_pay')) $paying              = (int) $this->_request->getParam('to_pay');
        
        if ( ($paying > 0) && ($to_receive == 0) && ($payment->work_price > 0) && ($paying + $payment->to_pay > $payment->work_price + $payment->payed) && ((int) $payment->to_pay > 0) ) {
            echo CJson::encode(['Оплата превышает лимит']);
            Yii::app()->end();
        }
        
        $payment->to_pay    += $paying;
        if ($payment->save()) {
			//(To User)
            $order = Zakaz::model()->findByPk($orderId);
            if ($payment->project_price > 0 && $order && $order->status == 1) {
                $order->status = 2;
                $order->save(false);
            }
			//(To Author)
            //$order = Zakaz::model()->findByPk($orderId);
            
            if ($paying>0) {
                
                $user = User::model()->with('profile')->findByPk($order->executor);
                $manag = User::model()->findByPk(Yii::app()->user->id);
                
                $buh = new Payment;
                $buh->approve = 0;
                $buh->order_id = $orderId;
                $buh->receive_date = date('Y-m-d H:i:s');
                $buh->theme = $order->title;
                $buh->user = $user->email;
                $buh->summ = $paying;
                $buh->payment_type = Payment::OUTCOMING_EXECUTOR;
                $buh->manager = $manag->email;
                /*$buh->details_ya = $user->profile->yandex;
                $buh->details_wm = $user->profile->wmr;
                $buh->details_bank = $user->profile->bank_account;*/
                $buh->save();
            }
            
            $this->_response->setData(
                array (
                    'project_price' => $payment->project_price,
                    'to_receive'    => $payment->to_receive,
                    'work_price'    => $payment->work_price,
                    'to_pay'        => $payment->to_pay
                )
            );
        } else {
            $this->_response->setData(
                array (
                    'result' => 'false'
                )
            );
        }
        $this->_response->send();
	}

	public function actionAffiliatePayment(){
		//print_r($_REQUEST);
		//Yii::app()->end();
		$hashSecretWord = Campaign::getPayment2ChekoutHash(); //2Checkout Secret Word
		$hashSid = Campaign::getPayment2Chekout(); //2Checkout account number
		$hashTotal = $_REQUEST['total']; //Sale total to validate against
		$hashOrder = $_REQUEST['order_number']; //2Checkout Order Number   ---- =1 for test!!
		$StringToHash = strtoupper(md5($hashSecretWord . $hashSid . $hashOrder . $hashTotal));
		
		if ($StringToHash != $_REQUEST['key']) {
			$result = 'Fail - Hash Mismatch'; 
		} else { 
			$result = 'Success - Hash Matched';
			$orderId = $_REQUEST['li_0_product_id'];
			$payment = ProjectPayments::model()->find('order_id = :ORDER_ID', array(
				'ORDER_ID'=>$orderId
			));
			$payment->received = $payment->received + $hashTotal;
			$payment->to_receive -= $hashTotal;
			if ($payment->save() && $hashTotal != 0) {
				$order = Zakaz::model()->resetScope()->findByPk($orderId);
				if ($order->status < 3) $order->status = 3;
				$order->save();
				if($payment->received == $payment->project_price) $this->saveFullPaymentWebmasterLog($order);
				
				$buh = new Payment;
				$buh->order_id = $orderId;
				$buh->receive_date = date('Y-m-d H:i:s');
				$buh->theme = $order->title;
				$user = User::model()->findByPk($order->user_id);
				$buh->user = $user->email;
				$buh->summ = (float) $hashTotal;
				$buh->payment_type = Payment::INCOMING_CUSTOMER;
				$buh->manager = 'robot@2checkout.com';
				$buh->approve = 1;
				$buh->method = 'Bank';
				if($buh->save()){
					echo 'ok';
					//Yii::app()->user->setFlash('tipDay','Данные сохранены');
					EventHelper::payForOrder($orderId);
					$this->redirect(array('/project/chat', 'orderId'=>$orderId));
				} else {
					echo 'Error! Can\'t save buh-payment';
				}
			} else {
				echo 'Error! Can\'t save order-payment';
			}
		}
		//echo $result;
	}
    public function actionManagersApprove() {  // Approve payment image
        $this->_prepairJson();
        $orderId = $this->_request->getParam('order_id');
        $payment = ProjectPayments::model()->find('order_id = :ORDER_ID', array(
            'ORDER_ID'=>$orderId
        ));
		if($payment->to_receive != 0){
			$summ = $payment->to_receive;
			$payment->received += $summ;
			$payment->to_receive = 0;
			if ($payment->save()) {
				$order = Zakaz::model()->findByPk($orderId);
				PaymentImage::model()->approve($order->id);
				if ($order->status < 3) $order->status = 3;
				$order->save();
				if($payment->received == $payment->project_price) $this->saveFullPaymentWebmasterLog($order);
				//
				$buh = new Payment;
				$buh->approve = 0;
				$buh->order_id = $orderId;
				$buh->receive_date = date('Y-m-d H:i:s');
				$buh->theme = $order->title;
				$user = User::model()->findByPk($order->user_id);
				$buh->user = $user->email;
				$buh->summ = $summ;
				$buh->payment_type = Payment::INCOMING_CUSTOMER;
				$manag = User::model()->findByPk(Yii::app()->user->id);
				$buh->manager = $manag->email;
				$buh->save();
				//EventHelper::payForOrder($orderId);
				$this->_response->setData(
					array (
						'received' => $payment->received,
					)
				);
			} else {
				$this->_response->setData(
					array (
						'received' => $payment->received,
						'error' => 'Can`t save...'
					)
				);
			}
		}
        $this->_response->send();

    }

    public function actionManagersCancel() {

        $this->_prepairJson();
        $orderId = $this->_request->getParam('order_id');
        $payment = ProjectPayments::model()->find('order_id = :ORDER_ID', array(
            'ORDER_ID'=>$orderId
        ));
        $payment->to_receive = 0;
        if ($payment->save()) {
            PaymentImage::model()->remove($orderId);
            $this->_response->setData(
                array (
                    'to_receive' => $payment->to_receive
                )
            );
        } else {
            $this->_response->setData(
                array (
                    'to_receive' => $payment->to_receive
                )
            );
        }
        $this->_response->send();
    }
	
	public function saveFullPaymentWebmasterLog($order) {
		$user = User::model()->findByPk($order->user_id);
		if ( $user->pid){
			$webmasterlog = new WebmasterLog();
			$webmasterlog->pid = $user->pid;
			$webmasterlog->uid = $user->id;
			$webmasterlog->date = date("Y-m-d"); 
			$webmasterlog->order_id = $order->id;
			$openlog = WebmasterLog::model()->findByAttributes(
				array('order_id'=>$order->id),'action = :p1 OR action = :p2', array(':p1'=>WebmasterLog::FIRST_ORDER, ':p2'=>WebmasterLog::NON_FIRST_ORDER)
			);
			if($openlog->action == WebmasterLog::FIRST_ORDER){
				$webmasterlog->action = WebmasterLog::FULL_PAYMENT_4_FIRST_ORDER;
			}elseif($openlog->action == WebmasterLog::NON_FIRST_ORDER){
				$webmasterlog->action = WebmasterLog::FULL_PAYMENT_4_NON_FIRST_ORDER;
			}
			$webmasterlog->save();
		}
	}
}
