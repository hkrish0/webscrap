<?php
/* @var $this Category2Controller */
/* @var $model CategoryDetails */

$this->breadcrumbs=array(
	'Category Details'=>array('index'),
	$model->id,
);

$this->menu=array(
	array('label'=>'List CategoryDetails', 'url'=>array('index')),
	array('label'=>'Create CategoryDetails', 'url'=>array('create')),
	array('label'=>'Update CategoryDetails', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete CategoryDetails', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage CategoryDetails', 'url'=>array('admin')),
);
?>

<h1>View CategoryDetails #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'category_id',
		'attribute',
		'mount_cat_id',
	),
)); ?>
