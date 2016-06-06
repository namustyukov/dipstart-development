<br><br><ul>
<?php
function echoTree($trees, $childs, $node, $n = 0){
	echo '<li><a href="'.Yii::app()->createUrl('/project/chat',array('orderId'=>$node)).'">'.$node.': '.$trees[$node].'</a>';
	if(count($childs[$node])) foreach($childs[$node] as $child) {
		echo '<ul>';
		echoTree($trees, $childs, $child, $n + 1);
		echo '</ul>';
	}
	echo '</li>';
}

foreach($forest as $tree){
	echoTree($trees, $childs, $tree);
	//print_r($tree);
	//echo '<li>'.$trees[$tree].'</li>';
}
?>
</ul>