
<div id="project_payments" class="col-xs-12">

    <!--<span class="block-title"><?=ProjectModule::t('Payments')?></span>-->
	<div style="padding-top: 5px;">
		<table class="table paytable">
		   <tr>
				<td>
					<?=ProjectModule::t('Project cost:')?>
					<?=Tools::hint($this->hints['Zakaz_project_cost'], 'hint-block __project_cost')?>
				</td>
				<td>
					<input type="text" class="project_price_input send_costs" size="10" value="<?php echo $model->project_price; ?>"/>
				</td>
				
			</tr>
			<tr>
				<td>
					<?=ProjectModule::t('Obtained from the client:')?>
					<?=Tools::hint($this->hints['Zakaz_payment_received'], 'hint-block __payment_received')?>
				</td>
				<td>
					<b><span class="payment_received"><?php echo $model->received; ?></span></b>
				</td>
			</tr>
			<tr>
				<td>
					<?=ProjectModule::t('To draw up the account:')?>
					<?=Tools::hint($this->hints['Zakaz_to_receive'], 'hint-block __to_receive')?>
				</td>
				<td>
					<input type="text" size="10" class="to_receive_input" value=""/>
					<button class="btn instant-send-buttons bg-green sendToRecive"><img src="<?=Yii::app()->theme->baseUrl?>\images\ok.png" title="<?=ProjectModule::t('Send')?>"></button>
				</td>
			</tr>
			<tr>
				<td>
					<?=ProjectModule::t('It is worth to pay:')?>
					<?=Tools::hint($this->hints['Zakaz_payment_to_receive'], 'hint-block __payment_to_receive')?>
				</td>
				<td>
					<b><span class="payment_to_receive"><?php echo $model->to_receive; ?></span></b>
				</td>
			</tr>
		</table>
		<table class="table paytable">
		   <tr>
				<td>
					<?=ProjectModule::t('The cost for the author:')?>
					<?=Tools::hint($this->hints['Zakaz_work_price'], 'hint-block __work_price')?>
				</td>
				<td>
					<input type="text" class="work_price_input send_costs" size="10" value="<?php echo $model->work_price; ?>"/>
				</td>
			</tr>
			<tr>
				<td>
					<?=ProjectModule::t('Paid work:')?>
					<?=Tools::hint($this->hints['Zakaz_payment_payed'], 'hint-block __payment_payed')?>
				</td>
				<td>
					<b><span class="payment_payed"><?php echo $model->payed; ?></span></b>
				</td>
				</td>
			</tr>
			<tr>
				<td>
					<?=ProjectModule::t('Send payment to:')?>
					<?=Tools::hint($this->hints['Zakaz_to_pay'], 'hint-block __to_pay')?>
				</td>
				<td>
					<input type="text" size="10" class="to_pay_input" value="" <?php if($model->received=='') echo 'disabled';?>/>
					<button class="btn instant-send-buttons bg-green sendToPay"><img src="<?=Yii::app()->theme->baseUrl?>\images\ok.png" title="<?=ProjectModule::t('Send')?>"></button>
				</td>
			</tr>
			<tr>
				<td>
					<?=ProjectModule::t('Total to pay:')?>
					<?=Tools::hint($this->hints['Zakaz_payment_to_pay'], 'hint-block __payment_to_pay')?>
				</td>
				<td>
					<b><span class="payment_to_pay"><?php echo $model->to_pay; ?></span></b>
				</td>
			</tr>
		</td>
		</table>
	</div>
	<? //if($model->to_receive>0){ ?>
	<div class="confirm-the-payment">
		<span><?=ProjectModule::t('Confirm payment from client:')?></span>
		<button class="btn instant-send-buttons send_managers_approve bg-green"><img src="<?=Yii::app()->theme->baseUrl?>\images\ok.png" title="<?=ProjectModule::t('Confirm')?>"></button>
        <?php if ($this->hints['Zakaz_confirm']) { ?>
        <div class="hint-block __confirm">
            ?
            <div class="hint-block_content">
                <?=$this->hints['Zakaz_confirm']?>
            </div>
        </div>
        <?php } ?>
		<button class="btn instant-send-buttons send_managers_cancel bg-red"><img src="<?=Yii::app()->theme->baseUrl?>\images\del.png" title="<?=ProjectModule::t('Cancel')?>"></button>
        <?php if ($this->hints['Zakaz_cancel']) { ?>
        <div class="hint-block __cancel">
            ?
            <div class="hint-block_content">
                <?=$this->hints['Zakaz_cancel']?>
            </div>
        </div>
        <?php } ?>
		<div class="btn-group-xs inline-block" role="group">
		<?php 
        
            if ($zakaz) {
                $i = 1;
                foreach ($zakaz->images as $item) {
                    echo CHtml::link(ProjectModule::t('Cheque') . $i++, PaymentImage::getFolder() . $item->image, array ('target' => '_blank' )) . ' ';
                }
            }
		?>
		</div>
	</div>
	<? //} ?>
	<?php $disable = ''; ?>
	<?php if(Yii::app()->user->isGuest) $disabled = ' disabled;' ?>
	
    <!--<button class="btn btn-primary pay-save-btn send_user_payments"<?= $diabled ?>><?=ProjectModule::t('Save')?></button>-->
    </div>

<script type="text/javascript">
    $(document).ready(function () {
        var projectPayments = new ProjectPayments(<?php echo $projectId; ?>);
    });
</script>