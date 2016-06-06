<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends RController
{
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='//layouts/main';
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();
	public $authMenu = array();
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */

	public $breadcrumbs=array();

    public function init(){
		// --- Организации
		$c_id = Company::getId();
		if ($c_id) {
			if(Company::getCompany()->frozen) {
				echo 'Where is my money, dude ?!?!?!';
				die;
			}
			ProjectChanges::$file_path = 'uploads/c'.$c_id.'/changes_documents';
			Zakaz::$files_folder = '/uploads/c'.$c_id.'/';
			
			Yii::app()->language = Company::getLanguage();
		} else {
			$tmp = explode('.',$_SERVER['SERVER_NAME']);
			if (array_shift($tmp)=='www') {
				$this->redirect('http://'.implode('.',$tmp));
			} else {
				echo 'Requested company not found.';
			}
			Yii::app()->end();
		}
		// ---
        if (!Yii::app()->user->isGuest)
            switch (User::model()->getUserRole()) {
                case ('Manager'):
                case ('Admin'):
				case ('root'):
                    Yii::app()->theme='admin';
                    break;
                case ('Author'):
					$menu[] = array('label'=>Yii::t('site','My orders'), 'url'=>array('/project/zakaz/ownList'));
                	$menu[] = array('label'=>Yii::t('site','New projects'), 'url'=>array('/project/zakaz/list'));
					if (User::model()->isCorrector()) {
	                    $menu[] = array('label'=>Yii::t('site','New projects for technical'), 'url'=>array('/project/zakaz/listtech'));
						//if (Company::getCompany()->module_tree) $menu[] = array('label'=>Yii::t('site','Tree structure'), 'url'=>array('/project/zakaz/tree'));
					}
					if (Company::getCompany()->agreement4executors && Company::getCompany()->agreement4executors != '') $menu[] = array('label'=>Yii::t('site','User Agreement'), 'url'=>array('/site/agreement'));
					//$menu[] = array('label'=>Yii::t('site','Personal account'), 'url'=>array('/user/profile/account'));
					$menu[] = array('label'=>Yii::t('site','Logout'), 'url'=>array('/user/logout'));// Далее выводится в обратном порядке
					$menu[] = array('label'=>Yii::t('site','Profile'), 'url'=>array('/user/profile/edit'));
					$this->menu = $menu;

                    Yii::app()->theme='client';
                    break;
                case ('Customer'):
						$menu[] = array('label'=>Yii::t('site','My orders'), 'url'=>array('/project/zakaz/customerOrderList'));
						if (Company::getCompany()->module_tree) $menu[] = array('label'=>Yii::t('site','Tree structure'), 'url'=>array('/project/zakaz/tree'));
						$menu[] = array('label'=>Yii::t('site','Create order'), 'url'=>array('/project/zakaz/create'));
                        //$menu[] = array('label'=>Yii::t('site','Personal account'), 'url'=>array('/user/profile/account'));
						if (Company::getCompany()->agreement4customers && Company::getCompany()->agreement4customers != '') $menu[] = array('label'=>Yii::t('site','User Agreement'), 'url'=>array('/site/agreement'));
						$menu[] = array('label'=>Yii::t('site','Logout'), 'url'=>array('/user/logout'));// Даллее выводится в обратном порядке
						$menu[] = array('label'=>Yii::t('site','Profile'), 'url'=>array('/user/profile/edit'));
                    $this->menu = $menu;
					/*$this->authMenu = array(
					    array('label'=>Yii::t('site','Logout'), 'url'=>array('/user/logout')),
					);*/
                    Yii::app()->theme='client';
                    break;
                case ('Webmaster'):
                    $this->menu = array(
						array('label'=>Yii::t('site','Stats'), 'url'=>array('/partner/stats')),
						array('label'=>Yii::t('site','Promo materials'), 'url'=>array('/partner/materials')),
						array('label'=>Yii::t('site','Logout'), 'url'=>array('/user/logout')),// Даллее выводится в обратном порядке
						array('label'=>Yii::t('site','Profile'), 'url'=>array('/user/profile/edit')),
                    );
					$this->authMenu = array(
					    array('label'=>Yii::t('site','Logout'), 'url'=>array('/user/logout')),
					);
                    Yii::app()->theme='client';
					if (Yii::app()->getRequest()->getRequestUri()=='/project/zakaz/list') $this->redirect('/');
                    break;
            }

//		var_dump(Yii::app()->controller->module->id ,Yii::app()->controller->id, Yii::app()->controller->action->id);
//		die();
    }

    public function filters(){
        return array(
            'rights',
			//'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
        );
    }
}
