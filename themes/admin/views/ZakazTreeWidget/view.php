<div class="row">
	<div class="col-xs-12 parentBlock">
	   <span class="block-title"><?=ProjectModule::t('Parent order')?><?php //echo $form->labelEx($model, 'parent_id'); ?>:&nbsp;</span>
	   <?=CHtml::dropDownList('Zakaz_parent_id', $model->parent_id, CHtml::listData(Zakaz::model()->findAllByAttributes(['user_id'=>$model->user_id], 'id <> :id', array(':id' => $model->id)), 'id', 'title'),
				array('empty' => ' '	)); ?>

		<?=Tools::hint($hints['Zakaz_status'], 'hint-block __status')?>
		<!--<button class="btn btn-primary btn-spam" onclick="spam(<?php echo $model->id; ?>);" href=""></button>-->
	</div>
<?php
if(count($childs)) {
	?>
	<div class="col-xs-12 childsBlock">
	   <span class="block-title"><?=ProjectModule::t('Order childrens')?>:&nbsp;</span>
		<?=Tools::hint($hints['Zakaz_status'], 'hint-block __status')?>
		<br>
		<?php
		foreach($childs as $child){
			echo '<a href="'.Yii::app()->createUrl('project/zakaz/update/',array('id'=>$child->id)).'">'.$child->id.': '.$child->title.'</a><br>';
		}
		?>
	</div>
	<?php
}
?>
</div><hr>