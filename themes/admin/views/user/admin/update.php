<?php
$this->breadcrumbs=array(
	(UserModule::t('Users'))=>array('admin'),
	$model->username=>array('view','id'=>$model->id),
	(UserModule::t('Update')),
);
$this->menu=array(
    array('label'=>UserModule::t('Create User'), 'url'=>array('create')),
    //array('label'=>UserModule::t('View User'), 'url'=>array('view','id'=>$model->id)),
    array('label'=>UserModule::t('Manage Users'), 'url'=>array('admin')),
    array('label'=>UserModule::t('Manage Profile Field'), 'url'=>array('profileField/admin')),
    //array('label'=>UserModule::t('List User'), 'url'=>array('/user')),
);

$this->widget('zii.widgets.CMenu', array(
	'items'=>$this->menu,
	'htmlOptions'=>array('class'=>'operations'),
));
?>

<h1><?php echo  UserModule::t('Update User')." ".$model->id; ?></h1>

<?php
	echo CHtml::link(UserModule::t('Edit assignments'), $this->createAbsoluteUrl('/rights/assignment/user',array('id'=>$model->id))).'<br /><br />';

	echo $this->renderPartial('_form', array(
			'model'		=> $model,
			'profile'	=> $profile,
			'manager'	=> $manager,
			'admin'		=> $admin,
			'fields'	=> $fields,
			//'specials' => $specials,
		)
		);
?>
