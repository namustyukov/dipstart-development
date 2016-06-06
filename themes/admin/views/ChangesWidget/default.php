<?php
/* @var $this CWidget */
/* @var $user User */
/* @var $changes ProjectChanges */
?>



<span class="block-title"><a class="new-revision-button" data-toggle="collapse" data-parent="#new-changes-block" href="#new-changes-collapse">+</a>
	<?php echo ProjectModule::t('Changes'); ?>:<?=Tools::hint($this->hints['Zakaz_changes'], 'hint-block __changes')?></span>
<div id="new-changes-collapse" class="collapse">
	<?php
	echo CHtml::form('', 'post', array('id' => 'up_file', 'enctype' => 'multipart/form-data'));
	?>
	<div class="col-xs-12">
		<?php echo CHtml::label(ProjectModule::t('Attach file'), 'fileupload'); ?>
		<?php echo CHtml::fileField('ProjectChanges[fileupload]', $fileupload, array('class' => 'col-xs-12 btn btn-user')); ?>
	</div>

	<div class="col-xs-12">
		<?php echo CHtml::label(ProjectModule::t('Comment'), 'comment'); ?>
		<?php echo CHtml::textArea('ProjectChanges[comment]', $comment, array('class' => 'col-xs-12')); ?>
	</div>
	<?php echo CHtml::endForm(); ?>
	<?php
	$url = Yii::app()->createUrl("/project/changes/add",array('project'=>$project->id));
	echo CHtml::htmlButton(ProjectModule::t('Add changes'), array(
		'class' => 'col-xs-12 btn btn-primary addPart',
		'onclick' => "javascript: send('$url')",
	));?>
</div>
<?php
$this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$changes,
	'itemView'=>'_list',
	'summaryCssClass'=>'hidden',
	'emptyText'=>'',
));
?>

