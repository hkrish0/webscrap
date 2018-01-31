<?php
/* @var $this ScrapformController */
/* @var $model Scrapform */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'scrapform-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'publisher_id'); ?>
		<?php echo $form->dropDownList($model, 'publisher_id', CHtml::listData(Publisher::model()->findAll(), 'id', 'publisher_name'),array('ajax' => array('type'=>'POST','url'=>CController::createUrl('publisher/getpublishercategories'),'update'=>'#Scrapform_publisher_cat',
		),'prompt'=>'Select Publisher'));?>
		<?php echo $form->error($model,'publisher_id'); ?>
	</div>


	<div class="row">
		<?php echo CHtml::activeLabel($model,'publisher_cat'); ?>
		<?php echo $form->dropDownList($model, 'publisher_cat', CHtml::listData(Category::model()->findAll(), 'id', 'category_name'),array('ajax' => array('type'=>'POST','url'=>CController::createUrl('category/getcategoryurl'),'update'=>'#Scrapform_url',
		),'prompt'=>'Select Publisher Category'));?>
		<?php //echo $form->error($model,'publisher_id'); ?>
	</div>


	<div class="row">
		<?php echo $form->labelEx($model,'discount_x'); ?>
		<?php echo $form->textField($model,'discount_x',array('size'=>30,'maxlength'=>50)); ?>
		<?php echo $form->error($model,'discount_x'); ?>
	</div>


	<div class="row">
		<?php echo $form->labelEx($model,'discount_y'); ?>
		<?php echo $form->textField($model,'discount_y',array('size'=>30,'maxlength'=>50)); ?>
		<?php echo $form->error($model,'discount_y'); ?>
	</div>





	<div class="row">
		<?php echo CHtml::activeLabel($model,'mount_categories'); ?>
		<?php echo $form->dropDownList($model, 'mount_categories', CHtml::listData(OcCategoryDescription::model()->findAll(), 'category_id', 'name'),array('empty'=>'','multiple'=>true ,'style'=>'width:400px;','size'=>'10'));?>
		<?php //echo $form->error($model,'publisher_id'); ?>
	</div>


	<?php 
	$this->widget('ext.chosen.EChosenWidget',array(
	    'selector'=>'#Scrapform_mount_categories',
	    'options'=>array(),
	)); ?>

	

	<div class="row">
		<?php echo $form->labelEx($model,'url'); ?>
		<?php echo $form->textArea($model,'url',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'url'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'uri'); ?>
		<?php echo $form->textField($model,'uri',array('size'=>60,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'uri'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'attribute'); ?>
		<?php echo $form->textField($model,'attribute',array('size'=>60,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'attribute'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->