<?php
Yii::app()->getClientScript()->registerCssFile(Yii::app()->theme->baseUrl.'/css/plate-form.css');
Yii::app()->getClientScript()->registerCssFile(Yii::app()->theme->baseUrl.'/css/plate-form-media.css');
Yii::app()->clientScript->registerScriptFile('/js/masonry.min.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile('/js/common-masonry.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile('/js/worktypes.js');

/* @var $this ZakazController */
/* @var $model Zakaz */
/* @var $form CActiveForm */
?>
<div class="form-container">

	<?php
	if ($model->isNewRecord) {
		$company = Company::getCompany();
		if ($company->text4customers) echo '<div class="text4customerts">'.$company->text4customers.'</div>';
	}
	echo '<p class="note">'.ProjectModule::t('Fields with <span class="required">*</span> are required.').'</p>';
	
	$form = $this->beginWidget('CActiveForm', array(
		'id'=>'zakaz-form',
		'action'=>isset ($model->id) ? $this->createUrl('zakaz/update', ['id'=>$model->id]) : 'http://'.$_SERVER['SERVER_NAME'].'/project/zakaz/create',
		//'type' => 'horizontal',
		//'htmlOptions' => array('class' => 'well'),
		// Please note: When you enable ajax validation, make sure the corresponding
		// controller action is handling ajax validation correctly.
		// There is a call to performAjaxValidation() commented in generated controller code.
		// See class documentation of CActiveForm for details on this.
		'enableAjaxValidation'=>false,
	)); ?>

	<?php //echo $form->errorSummary($model);
	
	if ($model->unixtime) echo  $form->hiddenField($model,'unixtime');
	echo '<div class="form-item">';
	//echo $form->labelEx($model,'max_exec_date');
	echo '<label for="Zakaz_max_exec_date">'.ProjectModule::t('Deadlines').'</label>';
	$this->widget('ext.juidatetimepicker.EJuiDateTimePicker', array(
		'model' => $model,
		'attribute' => 'dbmax_exec_date',
	));
	echo '<img src="/images/date_1398.png" style="margin-top: -3px;">';
	echo '</div>';
	$projectFields = $model->getFields('Customer');
	if ($projectFields) {
		foreach($projectFields as $field) {
			$work_types = '';
			if ($field->work_types) $work_types = ' data-worktypes="'.$field->work_types.'"';
			echo '<div class="form-item"'.$work_types.'>';
			echo $form->labelEx($model,$field->varname);
			if ($field->field_type=="BOOL"){
				echo $form->checkBox($model,$field->varname, array('style'=>'margin-left: 10px;'));
			} elseif ($field->field_type=="LIST"){
				$list = Catalog::model()->performCatsTree($field->varname);
				echo $form->dropDownList($model, $field->varname, $list, array('empty' => ProjectModule::t('Select a category'),'class'=>'form-control'));
			} elseif ($field->field_type=="TEXT") {
				echo$form->textArea($model,$field->varname,array('rows'=>6, 'cols'=>50, 'class'=>'form-control'));
			} elseif ($field->field_type=="TIMESTAMP" || $field->field_type=="DATE") {
					$varname = $field->varname;
					$model->timestampOutput($field);
					$this->widget('ext.juidatetimepicker.EJuiDateTimePicker', array(
						'model' => $model,
						'attribute' => $varname,
					));
					echo '<img src="/images/date_1398.png" style="margin-top: -3px;">';
			} else {
				echo $form->textField($model,$field->varname,array('size'=>60,'maxlength'=>(($field->field_size)?$field->field_size:255), 'class'=>'form-control'));
			}
			echo '</div>';
		}
	}
	?>
	<div class="form-item" style="min-height: 162px;">
	  <?php
		$this->widget('ext.EAjaxUpload.EAjaxUpload',
			array(
				'id' => 'justFileUpload',
				'config' => array(
					'action' => $this->createUrl('/project/zakaz/upload', $upload_params),
					'template' => '<div class="qq-uploader"><div class="qq-upload-drop-area"><span>'. ProjectModule::t('Drag and drop files here') .'</span><div class="qq-upload-button">'. ProjectModule::t('Attach materials to the order') .'</div><ul class="qq-upload-list">'.$uploaded_files.'</ul></div></div>',
					'disAllowedExtensions' => array('exe','scr'),
					'sizeLimit' => Tools::maxFileSize(), // maximum file size in bytes
					'minSizeLimit' => 1,// minimum file size in bytes
					'onComplete' => "js:function(id, fileName, responseJSON){masonry();}",
				)
			)
		);
		?>
	</div>
	<?php
		if (User::model()->isCorrector()) {
			echo $form->hiddenField($model, 'technicalspec', array('value' => 0));
			echo CHtml::hiddenField('accepted', 1);
		}
	?>
	<?php if ( $isGuest ) { ?>
	<div class="form-item">
	<?php echo $form->labelEx($user,'email'); ?><br/>
	<?php echo $form->textField($user,'email'); ?>
	<?php echo $form->error($user,'email'); ?>
	</div>
	<div class="form-item">
	<?php echo $form->labelEx($user,'phone_number'); ?><br/>
	<?php echo $form->textField($user,'phone_number'); ?>
	<?php echo $form->error($user,'phone_number'); ?>
	</div>
	<?php } ?>
	<div class="form-save">
		<?php echo CHtml::submitButton($model->isNewRecord ? ProjectModule::t('Create') : (User::model()->isCorrector() ? ProjectModule::t('Technical spec accepted') : ProjectModule::t('Save')), array('class' => 'btn btn-primary')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
