<?php

/**
 * This is the model base class for the table "{{yiiscraper_link}}".
 * DO NOT MODIFY THIS FILE! It is automatically generated by giix.
 * If any changes are necessary, you must set or override the required
 * property or method in class "YiiscraperLink".
 *
 * Columns in table "{{yiiscraper_link}}" available as properties of the model,
 * followed by relations of table "{{yiiscraper_link}}" available as properties of the model.
 *
 * @property string $id
 * @property string $url
 * @property string $depth
 * @property integer $active
 * @property string $content
 *
 * @property YiiscraperRelatedData[] $yiiscraperRelatedDatas
 * @property YiiscraperRelatedData[] $yiiscraperRelatedDatas1
 */
abstract class BaseYiiScraperLink extends GxActiveRecord {

	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return '{{yiiscraper_link}}';
	}

	public static function label($n = 1) {
		return Yii::t('app', 'YiiscraperLink|YiiscraperLinks', $n);
	}

	public static function representingColumn() {
		return 'url';
	}

	public function rules() {
		return array(
//			array('parent_id', 'numeric', 'min'=>0),
			array('url', 'required'),
			array('active', 'numerical', 'integerOnly'=>true),
			array('url', 'length', 'max'=>255),
			array('depth', 'length', 'max'=>11),
			array('content', 'safe'),
			array('depth, active, content', 'default', 'setOnEmpty' => true, 'value' => null),
			array('id, url, depth, active, content', 'safe', 'on'=>'search'),
		);
	}

	public function relations() {
		return array(
			'yiiscraperRelatedDatas' => array(self::HAS_MANY, 'YiiscraperRelatedData', 'link_id_from'),
			'yiiscraperRelatedDatas1' => array(self::HAS_MANY, 'YiiscraperRelatedData', 'link_id_to'),
		);
	}

	public function pivotModels() {
		return array(
		);
	}

	public function attributeLabels() {
		return array(
			'id' => Yii::t('app', 'ID'),
			'parent_id' => Yii::t('app', 'Parent ID'),
			'url' => Yii::t('app', 'Url'),
			'depth' => Yii::t('app', 'Depth'),
			'active' => Yii::t('app', 'Active'),
			'content' => Yii::t('app', 'Content'),
			'yiiscraperRelatedDatas' => null,
			'yiiscraperRelatedDatas1' => null,
		);
	}

	public function search() {
		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id, true);
//		$criteria->compare('parent_id', $this->parent_id, true);
		$criteria->compare('url', $this->url, true);
		$criteria->compare('depth', $this->depth, true);
		$criteria->compare('active', $this->active);
		$criteria->compare('content', $this->content, true);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}
}