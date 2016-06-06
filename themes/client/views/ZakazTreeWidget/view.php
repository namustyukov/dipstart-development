<?php if($model->parent_id) { ?>
<h7 class="center"><?=ProjectModule::t('Parent order')?>:&nbsp;</h7>
<div class="col-xs-12 parentBlock">
	<?php
	//CHtml::dropDownList('Zakaz_parent_id', $model->parent_id, CHtml::listData(Zakaz::model()->findAllByAttributes(['user_id'=>$model->user_id], 'id <> :id', array(':id' => $model->id)), 'id', 'title'),
	//		array('empty' => ' ', 'data-order-number' => $model->id));
	$parent = Zakaz::model()->findByPk($model->parent_id);
	echo '<a href="'.Yii::app()->createUrl('project/chat',array('orderId'=>$model->parent_id)).'">'.$model->parent_id.': '.$parent->title.'</a><br>';
	//echo Tools::hint($hints['Zakaz_status'], 'hint-block __status');?>
	<!--<button class="btn btn-primary btn-spam" onclick="spam(<?php echo $model->id; ?>);" href=""></button>-->
</div>
<?php
}
if(count($childs)) {
	?>
	<h7 class="center"><?=ProjectModule::t('Order childrens')?>:&nbsp;</h7>
	<div class="col-xs-12 childsBlock">
		<?php//Tools::hint($hints['Zakaz_status'], 'hint-block __status')?>
		<?php
		foreach($childs as $child){
			echo '<a href="'.Yii::app()->createUrl('project/chat',array('orderId'=>$child->id)).'">'.$child->id.': '.$child->title.'</a><br>';
		}
		?>
	</div>
	<?php
}
?>
<hr>