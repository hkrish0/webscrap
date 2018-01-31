<?php
/* @var $this DistrictsController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Districts',
);

$this->menu=array(
	array('label'=>'Create Districts', 'url'=>array('create')),
	array('label'=>'Manage Districts', 'url'=>array('admin')),
);
?>

<h1>Districts</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
