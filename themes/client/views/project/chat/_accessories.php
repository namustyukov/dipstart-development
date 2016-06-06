<div class="row" data-role="<?=User::model()->isCustomer()?>" id="message_send" data-message-send="<?=Templates::model()->getTemplate(31)?>">
<?php
//if (!Yii::app()->request->isAjaxRequest){
	echo CHtml::form(); ?>
	
	<div class="chat-text-area">
		<?php echo CHtml::label(ProjectModule::t('Message'),'message', array('id' => 'msgLabel')); ?>
		<?php echo CHtml::textArea('message','', array('rows' => 6, 'class' => 'col-xs-12', 'placeholder' => ProjectModule::t('Enter your message...'))); ?>
	</div>
	<div class="chat-edit-text-area" id="div-edit-message" style="display: none;">
		<?php echo CHtml::label(ProjectModule::t('Message'),'edit-message', array('id' => 'msgLabel1')); ?>
		<?php echo CHtml::textArea('edit-message','', array('rows' => 6, 'class' => 'col-xs-12', 'placeholder' => ProjectModule::t('Enter your message...'), 'id' => 'edit-message')); ?>
	</div>

	<?php
		if (count($buttonTemplates))
		{
	?>
			<div class="message-buttons">
			<?php
				foreach ($buttonTemplates as $item) {
			?>
					<div class="message-buttons-items">
						<button type="button" class="btn btn-primary btn-message" id="<?=$item->name?>"><?=$item->title?></button>
						<div class="message-buttons-text-hidden"><?=$item->text?></div>
					</div>
			<?php
				}
			?>
			</div>
	<?php
		}
	?>

	<div class="chat-buttons">
	<?php
		if($role == 'Executor' || $role == 'Author') {
			$attr = array('name' => 'author_to_customer', 'class' => 'btn btn-primary btn-chat chtpl0-submit1','id'=>'chat-author-to-customer');
			echo  CHtml::submitButton(ProjectModule::t('Send to customer'), $attr) ;
			$attr = array('name' => 'author_to_manager', 'class' => 'btn btn-primary btn-chat chtpl0-submit2','id'=>'chat-author-to-manager');
			echo  CHtml::submitButton(ProjectModule::t('Send to administrator'), $attr) ;
		}
		if($role == 'Executor') {
			$attr = array('name' => 'author_to_corrector', 'class' => 'btn btn-primary btn-chat chtpl0-submit1','id'=>'chat-author-to-corrector');
			echo  CHtml::submitButton(ProjectModule::t('Send to corrector'), $attr) ;
		}
		if($role == 'Customer') {
			$attr = array('name' => 'customer_to_author', 'class' => 'btn btn-primary btn-chat chtpl0-submit1','id'=>'chat-customer-to-author');
			echo  CHtml::submitButton(ProjectModule::t('Send to executor'), $attr) ;
			$attr = array('name' => 'customer_to_manager', 'class' => 'btn btn-primary btn-chat chtpl0-submit2','id'=>'chat-customer-to-manager');
			echo  CHtml::submitButton(ProjectModule::t('Send to administrator'), $attr) ;
			$attr = array('name' => 'customer_to_corrector', 'class' => 'btn btn-primary btn-chat chtpl0-submit1','id'=>'chat-customer-to-corrector');
			echo  CHtml::submitButton(ProjectModule::t('Send to corrector'), $attr) ;
		}
		if($role == 'Corrector') {
			$attr = array('name' => 'corrector_to_customer', 'class' => 'btn btn-primary btn-chat chtpl0-submit1','id'=>'chat-corrector-to-customer');
			echo  CHtml::submitButton(ProjectModule::t('Send to customer'), $attr) ;
			$attr = array('name' => 'corrector_to_manager', 'class' => 'btn btn-primary btn-chat chtpl0-submit2','id'=>'chat-corrector-to-manager');
			echo  CHtml::submitButton(ProjectModule::t('Send to administrator'), $attr) ;
			$attr = array('name' => 'corrector_to_author', 'class' => 'btn btn-primary btn-chat chtpl0-submit2','id'=>'chat-corrector-to-executor');
			echo  CHtml::submitButton(ProjectModule::t('Send to executor'), $attr) ;
		}
		//$edit_button = ProjectModule::t('Edit Last Message');
		//echo CHtml::submitButton($edit_button, array('name' => 'edit-message', 'class' => 'btn btn-primary chat-edit','step' => '0','id' => 'chat-edit'));
	?>
	</div>

	<?php echo CHtml::hiddenField('order',$order->id);
	CHtml::endForm();
//}
?>
<!-- form -->
</div>