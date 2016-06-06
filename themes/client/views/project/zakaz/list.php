<?php Yii::app()->getClientScript()->registerCssFile(Yii::app()->theme->baseUrl.'/css/custom.css');?>
<?php Yii::app()->getClientScript()->registerCssFile(Yii::app()->theme->baseUrl . '/css/chat-view.css'); ?>
<?php //Yii::app()->getClientScript()->registerCssFile(Yii::app()->theme->baseUrl.'/js/orders.js');?>

<?php
/* @var $this ZakazController */
/* @var $model Zakaz */

$this->breadcrumbs=array(
	ProjectModule::t('Zakazs')=>array('index'),
	ProjectModule::t('List'),
);
?>
<h1><?=ProjectModule::t('Zakazs')?></h1>
<h1 class='projects-title'><?=ProjectModule::t('SelectProject')?></h1>
<?php
if (Company::getId()){
	$columns = array(
		'id',
		'title',
	);

	if (ProjectField::model()->inTableByVarname('specials')) {
		$columns[] = array(
			'name'=>'specials',
			'filter'=>Catalog::getAll('specials'),
			'value'=>'$data->catalog_specials->cat_name',
		);
	}
	if (ProjectField::model()->inTableByVarname('specials2')) {
		$columns[] = array(
			'name'=>'specials2',
			'filter'=>Catalog::getAll('specials2'),
			'value'=>'$data->catalog_specials2->cat_name',
		);
	}
	$columns[] = 'closestDate';

	if (!isset($only_new))
		$columns[] = [
			'name' => 'executor_event',
            'value' => '$data->getExecutorEvents()',
            'type' => 'raw',
		];
}

if (User::model()->isCorrector() && $tech) {
	$url = Yii::app()->createUrl('/project/chat',array('role'=>'Corrector', 'orderId'=>''));
} elseif (isset($only_new)) {
	$url = Yii::app()->createUrl('/project/chat/view',array('orderId'=>'')).'/';
	if (User::model()->isAuthor()) {
		if(!$profile) echo '<div class="advice">'.ProjectModule::t('It is recommended to fill in the profile...').'</div>';
	} elseif($isGuest) { ?>
	<div class="heading guest-links">
		<a class="right" href="/user/login?role=Author"><?=UserModule::t('Login') ?></a>
		<a class="right" href="/user/registration?role=Author"><?=UserModule::t('Register') ?></a>
	</div>
	<?php }
} else {
	$url = Yii::app()->createUrl('/project/chat',array('orderId'=>''));
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
}
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'zakaz-grid',
	'dataProvider'=>$dataProvider,
	'columns'=>$columns,
	'rowHtmlOptionsExpression'=>'array("style" => "cursor:pointer")',
	'selectionChanged'=>"js:function(sel_id){
		document.location.href='".$url."'+$('#'+sel_id).find('.selected').children().first().text();
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
			document.location.href='".$url."'+$('#'+sel_id).find('.selected').children().first().text();
		}",
	)); 

	echo '</div>';
} //if (!isset($only_new)) 
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
		$('body').on('dblclick', '#zakaz-grid tbody tr', function(event)
		{
			var
				rowNum = $(this).index(),
				keys = $('#zakaz-grid > div.keys > span'),
				rowId = keys.eq(rowNum).text();

			location.href = '<?=$url ?>' + rowId;
		});
	});
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
<?php 	if($isGuest) {
$company = Campaign::getCompany();
if ($company->text4guests) echo '<div class="col-xs-12 text4guests">'.$company->text4guests.'</div>';
?>
<div class="heading guest-links clear">
	<a class="right" href="/user/login?role=Author"><?=UserModule::t('Login') ?></a>
	<a class="right" href="/user/registration?role=Author"><?=UserModule::t('Register') ?></a>
</div>
<?php } ?>
