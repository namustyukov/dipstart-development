<?php /* @var $this Controller */ ?>
<?php $this->beginContent('//layouts/main'); ?>
<div class=" last">
	<div id="sidebar">
	<?php
		$this->beginWidget('zii.widgets.CPortlet', array(
			'title'=>Yii::t('site','Operations'),
		));
		$this->widget('zii.widgets.CMenu', array(
			'items'=>$this->menu,
			'htmlOptions'=>array('class'=>'operations'),
		));
		$this->endWidget();
	?>
	</div><!-- sidebar -->
</div>
<div class="">
	<div id="content">
		<?php echo $content; ?>
	</div><!-- content -->
</div>
<?php $this->endContent(); ?>