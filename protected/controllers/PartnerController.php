<?php

class PartnerController extends Controller {

	public function actionRedirect($pid, $url = null) {
		setcookie('partner', $pid, time()+60*60*24*30*3, '/');
		$model = new WebmasterLog();
		$model->pid = $pid;
		$model->date = date("Y-m-d"); 
		if ( isset($_COOKIE['partner']))
			$model->action =  WebmasterLog::PARTNER_REPEAT;
		else
			$model->action =  WebmasterLog::PARTNER_UNIQUE;
		$model->save();
		if($url) {
			$this->redirect($url);
		} else {
			$fp = Company::getFrontPage();
			if ($fp) $this->redirect($fp);
			else $this->redirect($this->createUrl('project/zakaz/create'));
		}
	}
	
	public function actionMaterials() {
		$this->render('materials', array('pid'=>Yii::app()->user->id));
	}
	
	public function actionStats($chosen_month = false) {
		$user = User::model()->findByPk(Yii::app()->user->id);
		//$registration_month = date('n',$user->create_at);
		//$registration_year = date('Y',$user->create_at);
		$tmp = explode('-', $user->create_at);
		$registration_year = $tmp[0];
		$registration_month = $tmp[1];
		$current_month = date ('m');
		$current_year = date ('Y');
		$months = array();
		for ($year = $registration_year ; $year <= $current_year; $year++) {
			$first_month = 1;
			$last_month = 12;
			if($year == $registration_year) $first_month = $registration_month;
			if($year == $current_year) $last_month = $current_month;
			for ($month = $first_month; $month <= $last_month; $month++) {
				if($month < 10) $month = '0'.$month;
				$months[$year.'-'.$month] = $year.'-'.$month;
			}
		}
		if(!$chosen_month) $chosen_month = $current_year.'-'.$current_month;
		$tmp = explode('-', $chosen_month);
		$days = cal_days_in_month(CAL_GREGORIAN, $tmp[1], $tmp[0]);
		$rawData = WebmasterLog::getLogsSumm($user->id, $chosen_month.'-01', $chosen_month.'-'.$days);
		$arrayDataProvider=new CArrayDataProvider($rawData, array(
		   'id'=>'id',
			'pagination'=>array(
				'pageSize'=>31,
			),
		));

		$this->render('stats', array(
			'dataProvider'=>$arrayDataProvider,
			'months' => $months,
			'chosen_month' => $chosen_month,
		));
		
	}
	
}
