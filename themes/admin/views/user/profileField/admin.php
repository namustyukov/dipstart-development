<?php Yii::app()->getClientScript()->registerCssFile(Yii::app()->theme->baseUrl.'/css/manager.css');?>
<?php
$this->breadcrumbs=array(
	UserModule::t('Profile Fields')=>array('admin'),
	UserModule::t('Manage'),
);
$this->menu=array(
    array('label'=>UserModule::t('Create Profile Field'), 'url'=>array('create')),
    //array('label'=>UserModule::t('Manage Profile Field'), 'url'=>array('admin')),
    array('label'=>UserModule::t('Manage Users'), 'url'=>array('/user/admin')),
);

$this->widget('zii.widgets.CMenu', array(
	'items'=>$this->menu,
	'htmlOptions'=>array('class'=>'operations'),
));
	
Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
    $('.search-form').toggle();
    return false;
});
$('.search-form form').submit(function(){
    $.fn.yiiGridView.update('profile-field-grid', {
        data: $(this).serialize()
    });
    return false;
});
");


?>
<h1><?php echo UserModule::t('Manage Profile Fields'); ?></h1>
<div class="row white-bg inside-block">
<div class="col-md-12">
<p><?php echo UserModule::t("You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b> or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done."); ?></p>
</div>
<div class="col-md-12">
<?php echo CHtml::link(UserModule::t('Advanced Search'),'#',array('class'=>'search-button')); ?>
<div class="search-form" style="display:none">
<?php 
//	$this->renderPartial('_search',array(
//		'model'=>$model,
//	)); 
?>
</div><!-- search-form -->
</div>
<div class="col-md-12">
<?php $this->widget('zii.widgets.grid.CGridView', array(
//	'dataProvider'=>$model->search(),
	'dataProvider'=>$dataProvider,
//	'filter'=>$model,
	'columns'=>array(
		'id',
		array(
			'name'=> UserModule::t('varname'),
			'type'=>'raw',
			'value'=>'UHtml::markSearch($data,"varname")',
		),
		array(
			'name'=> UserModule::t('title'),
			'value'=>'UserModule::t($data->title)',
		),
		array(
			'name'=> UserModule::t('field_type'),
			'value'=>'$data->field_type',
			'filter'=>ProfileField::itemAlias("field_type"),
		),
		'field_size',
		//'field_size_min',
		array(
			'name'=>UserModule::t('required'),
			'value'=>'ProfileField::itemAlias("required",$data->required)',
			'filter'=>ProfileField::itemAlias("required"),
		),
		//'match',
		//'range',
		//'error_message',
		//'other_validator',
		//'default',
		'position',

		array(
			'name'=>UserModule::t('visible'),
			'value'=>'ProfileField::itemAlias("visible",$data->visible)',
			'filter'=>ProfileField::itemAlias("visible"),
		),
        array(
			'name'=>'paymentProps',
			'value'=> '$data->paymentProps',
		),            
		//*/
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
</div>
</div>
