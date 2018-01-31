<?php
/* @var $this ScrapformController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Scrapforms',
);

$this->menu=array(
	array('label'=>'Create Scrapform', 'url'=>array('create')),
	array('label'=>'Manage Scrapform', 'url'=>array('admin')),
);
?>

<h1>Scrapforms</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
