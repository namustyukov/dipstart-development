<?php

/* @var $this ZakazPartsController */
/* @var $model ZakazParts */

$this->breadcrumbs=array(
	ProjectModule::t('Zakaz Parts')=>array('index'),
	$model->title,
);

$this->menu=array(
	array('label'=>ProjectModule::t('List ZakazParts'), 'url'=>array('index')),
	array('label'=>ProjectModule::t('Create ZakazParts'), 'url'=>array('create')),
	array('label'=>ProjectModule::t('Update ZakazParts'), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>ProjectModule::t('Delete ZakazParts'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>ProjectModule::t('Manage ZakazParts'), 'url'=>array('admin')),
);
?>

<h1><?= ProjectModule::t('View ZakazParts').' '.$model->id ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'proj_id',
		'title',
		'text',
		'file',
		'date',
		'max_exec_date',
		'date_finish',
		'pages',
		'budget',
		'add_demands',
	),
)); ?>
