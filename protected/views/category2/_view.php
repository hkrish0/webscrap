<?php
/* @var $this Category2Controller */
/* @var $data CategoryDetails */
?>

<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->id), array('view', 'id'=>$data->id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('category_id')); ?>:</b>
	<?php echo CHtml::encode($data->category_id); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('attribute')); ?>:</b>
	<?php echo CHtml::encode($data->attribute); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('mount_cat_id')); ?>:</b>
	<?php echo CHtml::encode($data->mount_cat_id); ?>
	<br />


</div>