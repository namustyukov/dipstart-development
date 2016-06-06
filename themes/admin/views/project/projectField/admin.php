<?php Yii::app()->getClientScript()->registerCssFile(Yii::app()->theme->baseUrl.'/css/manager.css');?>
<?php
$this->menu=array(
    array('label'=>ProjectModule::t('Create Project Field'), 'url'=>array('create')),
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
    $.fn.yiiGridView.update('Project-field-grid', {
        data: $(this).serialize()
    });
    return false;
});
");


?>
<h1><?php echo ProjectModule::t('Manage Project Fields'); ?></h1>
<div class="row white-bg inside-block">
<div class="col-md-12">
<p><?php echo UserModule::t("You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b> or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done."); ?></p>
</div>
<div class="col-md-12">
<?php echo CHtml::link(UserModule::t('Advanced Search'),'#',array('class'=>'search-button')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array('model'=>$model)); ?>
</div><!-- search-form -->
</div>
<div class="col-md-12">
<?php $this->widget('zii.widgets.grid.CGridView', array(
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		'id',
		array(
			'name'=>'varname',
			'type'=>'raw',
			'value'=>'UHtml::markSearch($data,"varname")',
		),
		array(
			'name'=>'title',
			'value'=>'UserModule::t($data->title)',
		),
		array(
			'name'=>'field_type',
			'value'=>'$data->field_type',
			'filter'=>ProjectField::itemAlias("field_type"),
		),
		'field_size',
		//'field_size_min',
		array(
			'name'=>'required',
			'value'=>'ProjectField::itemAlias("required",$data->required)',
			'filter'=>ProjectField::itemAlias("required"),
		),
		//'match',
		//'range',
		//'error_message',
		//'other_validator',
		//'default',
		'position',
		array(
			'name'=>'visible',
			'value'=>'ProjectField::itemAlias("visible",$data->visible)',
			'filter'=>ProjectField::itemAlias("visible"),
		),
		array(
			'name'=>'work_types',
			'type'=>'raw',
			'value'=>'"<a href=\"/project/projectField/workTypes?id=".$data->id."\">".
						(($data->work_types==null)?"(".UserModule::t("All").")":$data->work_types) ."</a>"',
		),
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
</div>
</div>