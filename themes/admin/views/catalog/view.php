<?php
/* @var $this CategoriesController */
/* @var $model Categories */

/*$this->breadcrumbs=array(
	Yii::t('site','Categories')=>array('index'),
	$model->id,
);*/

$this->menu=array(
	//array('label'=>Yii::t('site','List Categories'), 'url'=>array('index')),
	array('label'=>Yii::t('site','Manage Categories'), 'url'=>array('admin')),
	array('label'=>Yii::t('site','Create Categories'), 'url'=>array('create')),
	array('label'=>Yii::t('site','Update Categories'), 'url'=>array('update','id'=>$model->id)),
	array('label'=>Yii::t('site','Delete Categories'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('site','Are you sure you want to delete this item?'))),
);
?>

<h1><?=Yii::t('site','View Categories')?> #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'field_varname',
		'cat_name',
	     array(
            'name' => 'parent_id',
            'type' => 'raw',
            'value' => Catalog::model()->performParent($model->parent_id),
        ),
	),
)); ?>