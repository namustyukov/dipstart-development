<?php
class CallController extends Controller {
    
//	/**
//	 * @return array action filters
//	 */
	public function filters()
	{
		return array(
//			'accessControl', // perform access control for CRUD operations
			'postOnly + refresh', // we only allow refresh via POST request
		);
	}
//    
//	/**
//	 * Specifies the access control rules.
//	 * This method is used by the 'accessControl' filter.
//	 * @return array access control rules
//	 */
//	public function accessRules()
//	{
//		return array(
//			array('allow', // allow authenticated user to perform 'call' and 'refreah'
//				'actions'=>array('call', 'refresh'),
//				'users'=>array('@'),
//			),
//			array('allow', // allow admin user to perform 'index'
//				'actions'=>array('index'),
//				'users'=>array('admin'),
//			),
//			array('deny',  // deny all users
//				'users'=>array('*'),
//			),
//		);
//	}
    
	/**
	 * Выводим список всех звонков.
	 */
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('CrmCdr');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}
    
    /**
     * Инициируем звонок
     */
    public function actionCall()
    {
        // Проверим нет ли сохраненного токена в сессии
        // Работу с сессией вынес в прикладной код, потому что врапер может вызываться из командной строки
        Yii::app()->cdr->cookie2token();
        $to = (int) $_POST['to'];
        $result = Yii::app()->cdr->call($to);
        // Сохраним токен в сессии есть есть что сохранять
        Yii::app()->cdr->token2cookie();
        //
        if(false != $result) echo 'Calling '.$to;
        else echo 'FAIL';
    }    
    /**
     * Запросим (обновим) данные о звонках у Телфина
     */
    public function actionRefresh()
    {
        // Проверим нет ли сохраненного токена в сессии
        // Работу с сессией вынес в прикладной код, потому что врапер может вызываться из командной строки
        Yii::app()->cdr->cookie2token();
        // Обновим данные из Телфина
        Yii::app()->cdr->refresh();
        // Сохраним токен в сессии есть есть что сохранять
        Yii::app()->cdr->token2cookie();
        //
        $this->redirect(['/call/index']);
    }
}