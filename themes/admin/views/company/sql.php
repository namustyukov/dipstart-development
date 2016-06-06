<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'create-company-form',
	'enableAjaxValidation'=>true,
	'htmlOptions' => array('enctype'=>'multipart/form-data'),
)); ?>
	<?php if(Yii::app()->user->hasFlash('sqlSuccessMessage'))
		echo '<p class="success">'.Yii::app()->user->getFlash('sqlSuccessMessage').'</p>';
	if(Yii::app()->user->hasFlash('companyErrorMessage'))
		echo '<p class="error">'.Yii::app()->user->getFlash('sqlErrorMessage').'</p>';
	?>
	<div class="row">
		<?php echo CHtml::textArea('code'); ?>
	</div>
	<div class="row buttons">
		<?php echo CHtml::submitButton(ProjectModule::t('Go')); ?>
	</div>
	<?=$echo?> 
<?php $this->endWidget(); ?>
</div><!-- form -->