<?php
$this->menu=array(
    array('label'=>ProjectModule::t('Create Project Field'), 'url'=>array('create')),
    array('label'=>ProjectModule::t('View Project Field'), 'url'=>array('view','id'=>$model->id)),
    array('label'=>ProjectModule::t('Manage Project Fields'), 'url'=>array('admin')),
);
$this->widget('zii.widgets.CMenu', array(
	'items'=>$this->menu,
	'htmlOptions'=>array('class'=>'operations'),
));
?>

<h1><?php echo ProjectModule::t('Update Project Field').' '.$model->id; ?></h1>
<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>