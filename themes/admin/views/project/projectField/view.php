<?php
$this->menu=array(
    array('label'=>ProjectModule::t('Create Project Field'), 'url'=>array('create')),
    array('label'=>ProjectModule::t('Update Project Field'), 'url'=>array('update','id'=>$model->id)),
    array('label'=>ProjectModule::t('Delete Project Field'), 'url'=>'#','linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>UserModule::t('Are you sure to delete this item?'))),
    array('label'=>ProjectModule::t('Manage Project Fields'), 'url'=>array('admin')),
);
$this->widget('zii.widgets.CMenu', array(
	'items'=>$this->menu,
	'htmlOptions'=>array('class'=>'operations'),
));
?>
<h1><?php
//echo UserModule::t('View Project Field #').$model->varname;
echo UserModule::t('FieldOfOrders').' "'.$model->varname.'"';
?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'varname',
		'title',
		'field_type',
		'field_size',
		'required',
		'error_message',
		'default',
		'position',
		'visible',
		array(
			'label'=>ProjectModule::t('Work types'),
			'type'=>'raw',
			'value'=>$model->work_types==null?'('.UserModule::t("All").')':Catalog::getNamesByIds($model->work_types,', '),
		),
	),
)); ?>
