<?php
/* @var $this ZakazPartsController */
/* @var $model ZakazParts */

$this->breadcrumbs=array(
	ProjectModule::t('Zakaz Parts')=>array('index'),
	$model->title=>array('view','id'=>$model->id),
	ProjectModule::t('Update'),
);

$this->menu=array(
	array('label'=>ProjectModule::t('List ZakazParts'), 'url'=>array('index')),
	array('label'=>ProjectModule::t('Create ZakazParts'), 'url'=>array('create')),
	array('label'=>ProjectModule::t('View ZakazParts'), 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>ProjectModule::t('Manage ZakazParts'), 'url'=>array('admin')),
);
?>

<h1><?= ProjectModule::t('Update ZakazParts').' '.$model->id ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>