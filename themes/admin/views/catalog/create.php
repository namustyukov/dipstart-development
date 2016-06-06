<?php
/* @var $this CategoriesController */
/* @var $model Categories */

$this->breadcrumbs=array(
	Yii::t('site','Categories')=>array('index'),
	Yii::t('site','Create'),
);

$this->menu=array(
	//array('label'=>Yii::t('site','List Categories'), 'url'=>array('index')),
	array('label'=>Yii::t('site','Manage Categories'), 'url'=>array('admin')),
);
?>

<h1><?=Yii::t('site','Create Categories')?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>