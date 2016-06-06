<?php
Yii::app()->getClientScript()->registerCssFile(Yii::app()->theme->baseUrl.'/css/reset.css');
Yii::app()->getClientScript()->registerCssFile(Yii::app()->theme->baseUrl.'/css/custom.css');

/* @var $this ZakazController */
/* @var $model Zakaz */
/* @var $form CActiveForm */

$c_id = Company::getId();
$url = '/uploads/c'.$c_id.'/temp/'.$model->unixtime.'/';
$uploaded_files = $model->generateMaterialsList($url, true);
$upload_params = array('unixtime' => $model->unixtime);

$this->renderPartial('/zakaz/_form', array(	'model' => $model,
											'upload_params' => $upload_params,
											'uploaded_files' => $uploaded_files,
											'isGuest' => $isGuest,
											'user' => $user));
?>

<!--<script>
    /*Remove attachment file*/
    function removeFile(obj){
        if (confirm("<?php echo Yii::t('site', 'Are you sure you want to delete this item?');?>")) {
            var data=$(obj).data();
            $.post('/project/zakaz/apiRenameFile?unixtime=<?php echo $model->unixtime; ?>', JSON.stringify({
                'data': data
            }), function (response) {
                if (response.data){
                    obj.remove();
                    $('#'+data.link).remove();
                }
            }, 'json');
    }}/*END Remove attachment file*/
</script>-->
<?php if (!$new && User::model()->isCustomer()) : ?>



<?php $this->beginWidget(
	'booster.widgets.TbModal',
	array('id' => 'agreement','htmlOptions' => array('data-backdrop' => 'static'))
); ?>

	<div class="modal-header">
		<!--<a class="close" data-dismiss="modal">&times;</a>-->
		<h4><?= Yii::t('site','User Agreement') ?></h4>
	</div>

	<div class="modal-body">
		<?php echo Company::getAgreement(); ?>
	</div>

	<div class="modal-footer">
		<?php $this->widget(
			'booster.widgets.TbButton',
			array(
				'label' => ProjectModule::t('Yes, I accept'),
				'context' => 'success',
				'buttonType' => 'link',
				'url' => Yii::app()->createUrl('/project/chat', array('orderId'=>$model->id)),
				//'htmlOptions' => array('class' => 'pull-left'),
			)
		); ?>
		<?php $this->widget(
			'booster.widgets.TbButton',
			array(
				'label' => ProjectModule::t('No, I want the other conditions'),
				'context' => 'warning',
				'url' => Yii::app()->createUrl('/project/chat', array('orderId'=>$model->id)),
				'buttonType' => 'ajaxButton',
				'htmlOptions' => array(
					'id' => 'not-accept-btn',
					'data-message' => $agreementNotAccepted,
					'data-template' => $messageForCustomer,
					'data-href' => Yii::app()->createUrl('/project/chat', array('orderId'=>$model->id)),
					//'onclick' => '',
				),
			)
		); ?>
	</div>

<?php $this->endWidget(); ?>
	<script>
		$(document).ready(function(){
			$("#agreement").modal("show");
		});
		$("#not-accept-btn").click(function () { //можно вынести в отдельный файл
			$.post(
				$(this).attr("data-href"),
				{
					ProjectMessages : {recipient: "manager", message : $(this).attr("data-message")}
				}).done(function(){
					bootbox.alert($("#not-accept-btn").attr("data-template"), function(){
						$(location).attr("href",$("#not-accept-btn").attr("data-href"));
					});
				});
		});

	</script>
<?php endif; ?>
