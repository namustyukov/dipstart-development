<?php Yii::app()->getClientScript()->registerCssFile(Yii::app()->theme->baseUrl.'/css/custom.css');?>
<?php //Yii::app()->getClientScript()->registerCssFile(Yii::app()->theme->baseUrl.'/js/orders.js');?>
	
    <div class="col-md-12">
        <h3><?=ProjectModule::t('Zakazs')?></h3>
    </div>
<?php 
if (Campaign::getId()){
	$columns = array(
		'id',
		'title',
		[
			'name' => 'customer_event',
            'value' => '$data->getCustomerEvents()',
            'type' => 'raw',
		],
	);
}/* else {
	$columns = array(
        'id',
        [
            'name' => 'category_id',
            'value' => 'isset($data->category) ? $data->category->cat_name : ""'
        ],
        [
            'name' => 'job_id',
            'value' => 'isset($data->job) ? $data->job->job_name : ""'
        ],
        'title',
        [
            'name' => 'date',
            'value' => 'Yii::app()->dateFormatter->formatDateTime($data->date)'
        ],
        [
            'name' => 'max_exec_date',
            'value' => 'Yii::app()->dateFormatter->formatDateTime($data->max_exec_date)'
        ],
        [
            'header' => '',
            'type' => 'raw',
            'value' => 'CHtml::link("чат", ["/project/chat", "orderId"=>$data->id])'
        ],
	);
}*/
if (!isset($only_new)) {
?>
<section>
	<div id="control-menu">
		<ul class="userMenu nav nav-tabs" id="yw0">
			<li class="active" id="first-tab-li">
				<a href="#" onclick="clickOnTab(0); return false;"><?= UserModule::t('CurrentProjects') ?></a>
			</li>
			<li id="second-tab-li">
				<a href="#" onclick="clickOnTab(1); return false;"><?= UserModule::t('DoneProjects') ?></a>
			</li>
		</ul>
	</div>
</section>

<div class="twin-tab">
<div class="first-tab" id="first-tab">
<?php
	}; //if (!isset($only_new)) {
	$this->widget('zii.widgets.grid.CGridView', array(
		'id'=>'zakaz-grid-current',
		'dataProvider'=>$dataProvider,
		'columns'=>$columns,
		//'enablePagination' => false,
		'htmlOptions'=>array('class'=>'col-md-12 table table-striped'),
		'rowHtmlOptionsExpression'=>'array("style" => "cursor:pointer")',
		'selectionChanged'=>"js:function(sel_id){
			var orderId = $('#'+sel_id).find('.selected').children().first().text();
			var url = '".Yii::app()->createUrl('/project/chat',array('orderId'=>''))."'+orderId;
			if (orderId*1 > 0) document.location.href=url;
		}",
	)); 
if (!isset($only_new)) {
?>
</div>
<div class="second-tab" id="second-tab">
<?php
	$this->widget('zii.widgets.grid.CGridView', array(
		'id'=>'zakaz-grid-done',
		'dataProvider'=>$dataProvider_done,
		'columns'=>$columns,
		'rowHtmlOptionsExpression'=>'array("style" => "cursor:pointer")',
		'selectionChanged'=>"js:function(sel_id){
			document.location.href='".Yii::app()->createUrl('/project/chat',array('orderId'=>''))."'+$('#'+sel_id).find('.selected').children().first().text();
		}",
	)); 

	echo '</div>';
	}; //if (!isset($only_new)) 
?>
<script>
	function clickOnTab(num){
		if (num == 0){
			document.getElementById('first-tab').style.display = 'block';
			document.getElementById('second-tab').style.display = 'none';

			//document.getElementById('first-tab').style.backgroundColor = '#FFFFFF';
			//document.getElementById('second-tab').style.backgroundColor = '#EBF4FF';
			$('#first-tab-li').addClass('active');
			$('#second-tab-li').removeClass('active');
		} else {
			document.getElementById('first-tab').style.display = 'none';
			document.getElementById('second-tab').style.display = 'block';

			//document.getElementById('first-tab').style.backgroundColor = '#FFFFFF';
			//document.getElementById('second-tab').style.backgroundColor = '#EBF4FF';
			$('#first-tab-li').removeClass('active');
			$('#second-tab-li').addClass('active');
		};	
	};	
	$(document).ready(function()
	{
		$('body').on('dblclick', '#zakaz-grid-done tbody tr', function(event)
		{
			var
				rowNum = $(this).index(),
				keys = $('#zakaz-grid-done > div.keys > span'),
				rowId = keys.eq(rowNum).text();

			location.href = '/project/chat?orderId=' + rowId;
		});
	});
</script>
