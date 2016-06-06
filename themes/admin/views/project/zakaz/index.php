<?php Yii::app()->getClientScript()->registerCssFile(Yii::app()->theme->baseUrl.'/css/manager.css');?>

<div class="row white-bg inside-block">
<div class="col-md-12">
<div class="link-for-executors">
	<?=ProjectModule::t('Link for freelancers') ?>: <a href="/project/zakaz/list"><?='http://'.$_SERVER['SERVER_NAME'].'/project/zakaz/list'?></a>
</div>
<?php
$this->breadcrumbs=array(
	ProjectModule::t('Zakazs'),
);
$columns = array('id');
$columns[] = array(
	'name'=>'title',
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
$columns[] = array(
	'name'=>'max_exec_date',
	'filter' => $this->widget('zii.widgets.jui.CJuiDatePicker', array(
		'model'=>$model,
		'attribute'=>'dbmax_exec_date',
		'language'=>Yii::app()->language,
	), true),
	'value'=>'$data->dbmax_exec_date',
);
$columns[] = array(
	'name'=>'author_informed',
	'filter' => $this->widget('zii.widgets.jui.CJuiDatePicker', array(
		'model'=>$model,
		'attribute'=>'dbauthor_informed',
		'language'=>Yii::app()->language,
	), true),
	'value'=>'$data->dbauthor_informed',
);
$columns[] = array(
	'name'=>'manager_informed',
	'filter' => $this->widget('zii.widgets.jui.CJuiDatePicker', array(
		'model'=>$model,
		'attribute'=>'dbmanager_informed',
		'language'=>Yii::app()->language,
	), true),
	'value'=>'$data->dbmanager_informed',
);
$columns[] = array(
	'name'=>'status',
	'filter'=>ProjectStatus::getAll(),
	'value'=>'$data->statusName',
);
$columns[] = array(
	'name'=>'lastPartStatus',
	'filter'=>ZakazParts::model()->getForFilter(),
	'value'=>'$data->lastPartStatus',
);
$columns[] = array(
	'name'=>'lastPartDate',
	'filter' => $this->widget('zii.widgets.jui.CJuiDatePicker', array(
		'model'=>$model,
		'attribute'=>'lastPartDate',
		'language'=>Yii::app()->language,
	), true),
	'value'=>'$data->lastPartDate',
);
if (ProjectField::model()->inTableByVarname('technicalspec')) {
	$columns[] = array(
		'name'=>'technicalspec',
		'value'=>'$data->technicalspec == 1 ? ProjectModule::t(\'Yes\') : ProjectModule::t(\'No\')',
		//'filter'=>CHtml::listData(array('Yes','No'), array(1,0), )
		'filter'=>array("0" => ProjectModule::t('No'), "1" => ProjectModule::t('Yes')),
	);
}
$columns[] = array(
	'class'=>'CButtonColumn',
	'template'=>'{delete}{update}',
);
/*
$projectFields = $model->getFields();
if ($projectFields) {
	foreach($projectFields as $field) {
		if ($field->field_type=="LIST"){
			$varname = $field->varname;
			$arr = Catalog::getAll($varname);
			if (!$arr) $arr = Catalog::getAll($varname, 0); // Если список одноуровненвый
			$columns[] = array(
					'name'=>$varname,
					'filter'=>$arr,
					'value'=>'$data->catalog_'.$varname.'->cat_name',
				);
		} elseif ($field->varname != 'soderjanie' && $field->varname != 'description'  && $field->varname !='opisanie') { // !!! Сделать настраиваемым
			$columns[] = $field->varname;
		}
	}
}*/
/*$columns[] = array(
		'name'=>'date',
		'filter' => $this->widget('zii.widgets.jui.CJuiDatePicker', array(
			'model'=>$model,
			'attribute'=>'dbdate',
			'language'=>Yii::app()->language,
			),true),
		'value'=>'$data->dbdate'
	);
*/
?>
<div id="grid">
<?php $this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'order_list',
	'dataProvider'=>$model->search(),
    'filter'=>$model,
	'ajaxUpdate' => true,
    'afterAjaxUpdate' => "function(id, data) {
		jQuery('#Zakaz_dbmax_exec_date').datepicker(jQuery.extend({showMonthAfterYear:false},jQuery.datepicker.regional['".Yii::app()->language."']));
		jQuery('#Zakaz_dbauthor_informed').datepicker(jQuery.extend({showMonthAfterYear:false},jQuery.datepicker.regional['".Yii::app()->language."']));
		jQuery('#Zakaz_dbmanager_informed').datepicker(jQuery.extend({showMonthAfterYear:false},jQuery.datepicker.regional['".Yii::app()->language."']));
		jQuery('#Zakaz_lastPartDate').datepicker(jQuery.extend({showMonthAfterYear:false},jQuery.datepicker.regional['".Yii::app()->language."']));
		}",
    'columns'=>$columns,
    'ajaxType'=>'POST',
    'rowHtmlOptionsExpression'=>'array("style" => "cursor:pointer")',
    'selectionChanged'=>"js:function(id){
        document.location.href=$('.selected').find('td').find('a.update').attr('href');
    }",
));
?>
<script>
    $(document).ready(function()
    {
        $('body').on('dblclick', '#order_list tbody tr', function(event)
        {
            var
                rowNum = $(this).index(),
                keys = $('#order_list > div.keys > span'),
                rowId = keys.eq(rowNum).text();
   if (rowId.length>0)
            location.href = '/project/zakaz/update/id/' + rowId;
        });
    });
</script>
</div>
    </div>
</div>
<?php
/*
Yii::app()->clientScript->registerScript('re-install-date-picker', "
function reinstallDatePicker(id, data) {
    $('[id ^= Zakaz_db]').datepicker(jQuery.extend(jQuery.datepicker.regional['ru']));
}
");
*/