<?php
/* @var $this ZakazPartsController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	ProjectModule::t('Zakaz Parts'),
);

$this->menu=array(
	array('label'=>ProjectModule::t('Create ZakazParts'), 'url'=>array('create')),
	array('label'=>ProjectModule::t('Manage ZakazParts'), 'url'=>array('admin')),
);
?>

<h1><?= ProjectModule::t('Zakaz Parts') ?></h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
