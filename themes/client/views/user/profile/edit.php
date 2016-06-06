<?php Yii::app()->getClientScript()->registerCssFile(Yii::app()->theme->baseUrl.'/css/custom.css');?>
<?php $this->pageTitle=Yii::app()->name . ' - '.UserModule::t("Profile");

?><!--<div class="row"><div class="col-md-offset-3 col-md-4"><h3><?php //echo UserModule::t('Edit profile'); ?></h3></div></div>-->
<script type="text/javascript">
$(document).ready(function () {
	$("#Profile_notification").on("click", function(){
		if ($(this).is(":checked")) $('#notificationParams').show();
		else $('#notificationParams').hide();
	});
	
	if ($("#Profile_notification").is(":checked")) $('#notificationParams').show();
	else $('#notificationParams').hide();
});
</script>

<div class="row">
    <div class="col-md-7">
<?php
$disAjaxValid = array();
$profileFields=$profile->getFields();
if ($profileFields) {
	foreach($profileFields as $field) {
		if ($field->field_type=="LIST"){
			$disAjaxValid[]=$field->varname;
		}
	}
}
$form=$this->beginWidget('UActiveForm', array(
	'id'=>'profile-form',
	'enableAjaxValidation'=>true,
	'disableAjaxValidationAttributes'=>$disAjaxValid,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
	'htmlOptions' => array('enctype'=>'multipart/form-data','class'=>'form-horizontal'),
)); ?>

  <?php if(Yii::app()->user->hasFlash('profileMessage'))
      echo '<p style="color: #D9534F;">'.Yii::app()->user->getFlash('profileMessage').'</p>';
  ?>
  <p style="font-weight: bold;">
    <?php if(User::model()->isAuthor()) echo ProjectModule::t('Profile message for Authors');?>
  </p>

	<p class="note"><?php echo UserModule::t('Fields with <span class="required">*</span> are required.'); ?></p>

	<?php echo $form->errorSummary(array($model,$profile)); ?>

		<?php if(User::model()->isAuthor()) { ?>
		<div class="form-group">
            <?php echo $form->labelEx($profile,'rating',array('class'=>'col-md-4 control-label')); ?>
            <div class="col-md-8">
                <?php echo $form->textField($profile,'rating',array('size'=>20,'maxlength'=>20,'class'=>'form-control', 'disabled'=>'true')); ?>
            </div>
        </div>
        <div class="form-group">
            <?php echo $form->labelEx($profile,'mailing_for_executors',array('class'=>'col-md-4 control-label')); ?>
            <div class="col-md-8">
                <?php if($profile->mailing_for_executors) $attr = array('checked'=>'checked'); else $attr = array();
				echo $form->checkBox($profile,'mailing_for_executors', $attr ); ?>
            </div>
            <?php echo $form->error($profile,'mailing_for_executors'); ?>
        </div>
		<div class="form-group">
            <?php echo $form->labelEx($profile,'notification',array('class'=>'col-md-4 control-label')); ?>
            <div class="col-md-8">
                <?php echo $form->checkBox($profile,'notification'); ?>
            </div>
            <?php echo $form->error($profile,'notification'); ?>
        </div>
		<div class="form-group" id="notificationParams">
            <?php echo $form->labelEx($profile,'notification_time',array('class'=>'col-md-4 control-label')); ?>
            <div class="col-md-8">
				<?php echo 'Часов '.$form->dropDownList($profile,'hours',$profile->getTime('hours')); ?>
				&nbsp;
				<?php echo 'Минут '.$form->dropDownList($profile,'minutes',$profile->getTime('minutes')); ?>
            </div>
            <?php echo $form->error($profile,'notification_time'); ?>
        </div>
		<?php } ?>
		
        <div class="form-group">
            <?php echo $form->labelEx($model,'full_name',array('class'=>'col-md-4 control-label')); ?>
            <div class="col-md-8">
                <?php echo $form->textField($model,'full_name',array('size'=>20,'maxlength'=>128,'class'=>'form-control')); ?>
            </div>
            <?php echo $form->error($model,'full_name'); ?>
        </div>
	
	    <div class="form-group">
            <?php echo $form->labelEx($model,'phone_number',array('class'=>'col-md-4 control-label')); ?>
            <div class="col-md-8">
                <?php echo $form->textField($model,'phone_number',array('size'=>20,'maxlength'=>20,'class'=>'form-control')); ?>
            </div>
            <?php echo $form->error($model,'phone_number'); ?>
        </div>

		<?php 
		$profileFields=$profile->getFields();
		if ($profileFields) {
			$attributes = $profile->getAttributes();
			foreach($profileFields as $field) {	
		?>
                <div class="form-group">
                    <?php echo $form->labelEx($profile,$field->varname,array('class'=>'col-md-4 control-label'));
					if ($field->field_type=="LIST"){
						$htmlOptions = array('size' => '10', 'multiple' => 'true','style'=>'width:400px;','size'=>'10', 'empty'=>UserModule::t('Use Ctrl for multiply'));
						$data = Catalog::model()->performCatsTree($field->varname);
						$varname = $field->varname;
						$selected = explode(',',$profile->$varname);
						echo '<div class="col-md-8">'.CHtml::listBox('Profile['.$field->varname.']', $selected, $data, $htmlOptions).'</div>';
					/*} elseif ($widgetEdit = $field->widgetEdit($profile,array('htmlOptions'=>array('class'=>'form-control')))) {
                        echo '<div class="col-md-8">'.$widgetEdit.'</div>';
                    } elseif ($field->range) {
                        echo '<div class="col-md-8">'.$form->dropDownList($profile,$field->varname,Profile::range($field->range),array('class'=>'form-control')).'</div>';*/
						} elseif ($field->field_type=="TEXT") {
//                        echo '<div class="col-md-8"><textarea name="Profile['.$field->varname.']" rows="6" cols="50" class="form-control">'.$attributes[$field->varname].'</textarea></div>';
                        echo '<div class="col-md-8">'.$form->textArea($profile,$field->varname,array('rows'=>6, 'cols'=>50,'class'=>'form-control')).'</div>';
                    } else {
//                        echo '<div class="col-md-8"><input type="text" name="Profile['.$field->varname.']" class="form-control" size="60" maxlength="'.(($field->field_size)?$field->field_size:255).'" value="'.$attributes[$field->varname].'"></div>';
                        echo '<div class="col-md-8">'.$form->textField($profile,$field->varname,array('class'=>'form-control','size'=>60,'maxlength'=>(($field->field_size)?$field->field_size:255))).'</div>';
                    }
                    echo $form->error($profile,$field->varname);
                    ?>
                    </div><?php
            }
        }?>
        <!--<div class="form-group">
            <?php echo $form->labelEx($model,'username',array('class'=>'col-md-4 control-label')); ?>
            <div class="col-md-8">
                <?php echo $form->textField($model,'username',array('size'=>20,'maxlength'=>20,'class'=>'form-control')); ?>
            </div>
            <?php echo $form->error($model,'username'); ?>
        </div>-->

        <div class="form-group">
            <?php echo $form->labelEx($model,'email',array('class'=>'col-md-4 control-label')); ?>
            <div class="col-md-8">
                <?php echo $form->textField($model,'email',array('size'=>60,'maxlength'=>128,'class'=>'form-control')); ?>
            </div>
            <?php echo $form->error($model,'email'); ?>
        </div>

        <div class="form-group">
            <div class="col-md-offset-4 col-md-8">
                <?php echo CHtml::submitButton($model->isNewRecord ? UserModule::t('Create') : UserModule::t('Save'),array('class'=>'btn btn-primary btn-save')); ?>
            </div>
		</div>

<?php $this->endWidget(); ?>
    </div>
</div><!-- form -->
