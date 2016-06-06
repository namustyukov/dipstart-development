<?php
/* @var $this CategoriesController */
/* @var $model Categories */

$this->menu=array(
	//array('label'=>Yii::t('site','List Categories'), 'url'=>array('index')),
	array('label'=>Yii::t('site','Manage Categories'), 'url'=>array('admin')),
	array('label'=>Yii::t('site','Create Categories'), 'url'=>array('create')),
	array('label'=>Yii::t('site','View Categories'), 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>Yii::t('site','Delete Categories'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('site','Are you sure you want to delete this item?'))),
);
?>

<h1><?php echo Yii::t('site','Update Categories').' '.$model->id; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>