
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'zakaz-parts-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note"><?=ProjectModule::t('Fields with <span class="required">*</span> are required.')?></p>

	<?php echo $form->errorSummary($model); ?>
        
	<div class="row">
		<?php echo $form->labelEx($model,'proj_id'); ?>
		<?php echo $model->proj_id; ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'title'); ?>
		<?php echo $form->textField($model,'title',array('size'=>60,'maxlength'=>255)); ?>
		<?php echo $form->error($model,'title'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'comment'); ?>
		<?php echo $form->textArea($model,'comment',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'comment'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'file'); ?>
		<?php $this->widget('ext.EAjaxUpload.EAjaxUpload',
                    array(
                       'id'=>'EAjaxUpload',
                       'config'=>array(
                                       'action'=>$this->createUrl('zakazParts/upload/'.$model->id),
                                       'template'=>'<div class="qq-uploader"><div class="qq-upload-drop-area"><span>'.ProjectModule::t('Drop files here to upload').'</span></div><div class="qq-upload-button">'. ProjectModule::t('Upload a file') .'</div><ul class="qq-upload-list"></ul></div>',
                                       'debug'=>false,
                                       'allowedExtensions'=>array('jpg, '),
                                       'sizeLimit'=>10*1024*1024,// maximum file size in bytes
                                       'minSizeLimit'=>10*1024,// minimum file size in bytes
                                       'onComplete'=>"js:function(id, fileName, responseJSON){ alert(fileName); }"
                                      )
                    )); ?>
                <?php echo $form->hiddenField($model, 'file')?>
		<?php echo $form->error($model,'file'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'date'); ?>
                <?php
                    $this->widget('zii.widgets.jui.CJuiDatePicker',array(
                        'name'=>'Zakaz[date]',
                        // additional javascript options for the date picker plugin
                        'language' => 'ru',
                        'value' => $model->date,
                        'options'=>array(
                            'dateFormat'=>'yy-mm-dd',
                            'showAnim'=>'drop',//'slide','fold','slideDown','fadeIn','blind','bounce','clip','drop'
                        ),
                        'htmlOptions'=>array(
                            'style'=>'height:20px;background-white:blue;color:black;',
                        ),
                    ));
                ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'author_id'); ?>
		<?php echo $form->textField($model,'author_id'); ?>
		<?php echo $form->error($model,'author_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'show'); ?>
		<?php echo $form->textField($model,'show'); ?>
		<?php echo $form->error($model,'show'); ?>
	</div>

	<div class="row buttons">
		<?php $attr = array(); ?>
		<?php if(Yii::app()->user->isGuest) $attr['disabled'] = 'disabled'; ?>
		<?php echo CHtml::submitButton('Save', $attr); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->