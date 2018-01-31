<?php
/* @var $this Category2Controller */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Category Details',
);

$this->menu=array(
	array('label'=>'Create CategoryDetails', 'url'=>array('create')),
	array('label'=>'Manage CategoryDetails', 'url'=>array('admin')),
);
?>

<h1>Category Details</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
