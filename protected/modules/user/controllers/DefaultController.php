<?php

class DefaultController extends Controller {

	private $_model;
	
    public function filters() {
        return CMap::mergeArray(parent::filters(),array(
            'accessControl', // perform access control for CRUD operations
			'ajaxOnly +rating',
			'postOnly +rating'
        ));
    }
    public function accessRules()
    {
        return array(
            array('allow',
                'actions'=>array('index','rating'),
                'users'=>array('@'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }
    /**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		
        switch ($_GET['s']) {
            case 'Author':
                $prof=Profile::model()->with('user','AuthAssignment')->findAll();
//                $prof=Profile::model()->findAll();
//echo '$prof(0)=';
//var_dump($prof);			
//die('$prof(0)='.var_dump($prof));
//echo '$count(prof)='.count($prof);
                //$cat=Categories::model()->findAll();
                //foreach($cat as $key=>$val) $rescat[$val->getAttributes()['id']]=$val->getAttributes()['cat_name'];
				$itog	= array();
                foreach ($prof as $key=>$val) {
					//echo $val['user_id'].' ';
                    $res=$val->getAttributes();
                    if($res1=$val->AuthAssignment) $res1=$val->AuthAssignment->getAttributes();   // Непонятное место, без условия бывает глючит ---<<
                    if ($res['discipline']!='') {
                        $res['cat_name']=implode(',',array_intersect_key(array()/*$rescat*/,array_flip(explode(',',$res['discipline']))));
                    }
                    if ($res1['itemname']=='Author') {
						$resuser=array();
						$user   = User::model()->findByPk($val->user_id);
				
						$resuser= $user->getAttributes();
						$resuser=$val->user->getAttributes(); 
                        $itog[$user->id]=array_merge($res,$resuser);
                    }
                }
                $dataProvider=new CArrayDataProvider($itog, array(
                    'pagination'=>array(
                        'pageSize'=>Yii::app()->controller->module->user_page_size,
                    ),
                ));
                break;
            default:
                $dataProvider=new CActiveDataProvider('User', array(
                    'criteria'=>array(
                        'condition'=>'status>'.User::STATUS_BANNED,
                        'with' => array(
                            'AuthAssignment' => array(
                                'condition' => 'itemname="Customer"',
                                'together' => true,
                            ),
                        ),
                    ),
                    'pagination'=>array(
                        'pageSize'=>Yii::app()->controller->module->user_page_size,
                    ),
                ));
                break;
        }
		
		$this->render('/user/index',array(
			'dataProvider'=>$dataProvider
		));
	}
	
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	 */
	public function loadUser($id=null) {
		if($this->_model===null) {
			if($id!==null || isset($_GET['id']))
				$this->_model=User::model()->findbyPk($id!==null ? $id : $_GET['id']);
			if($this->_model===null)
				throw new CHttpException(404,'The requested page does not exist.');
		}
		return $this->_model;
	}
        
	public function actionRating() {
		if (!User::model()->isManager()) {
			throw new CHttpException(403);
		}
		
		$user_id = Yii::app()->request->getPost('user_id');
		$action = Yii::app()->request->getPost('action');
		
		$user = $this->loadUser($user_id);
		
		if(!isset($user->profile)) {
			$profile = new Profile();
			$profile->user_id = $user_id;
			$profile->save();
			$user = $this->loadUser($user_id);
		}
		$rating = (int)$user->profile->rating;

		if ($action == 'up') {
			$rating++;
		} elseif ($action == 'down') {
			$rating--;
		}
		$user->profile->rating = $rating;
		$user->profile->save(false);
		
		echo $user->profile->rating;
	}
}
