<?php

/**
 * This is the model base class for the table "{{yiiscraper_related_data}}".
 *
 * Columns in table "{{yiiscraper_related_data}}" available as properties of the model,
 * followed by relations of table "{{yiiscraper_related_data}}" available as properties of the model.
 *
 * @property string $link_id_from
 * @property string $link_id_to
 *
 * @property YiiscraperLink $linkIdFrom
 * @property YiiscraperLink $linkIdTo
 */
class YiiScraperRelatedData extends CActiveRecord {

	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return '{{yiiscraper_related_data}}';
	}

	public function rules() {
		return array(
			array('link_id_from, link_id_to', 'required'),
			array('link_id_from, link_id_to', 'length', 'max'=>11),
			array('link_id_from, link_id_to', 'safe', 'on'=>'search'),
		);
	}

	public function relations() {
		return array(
			'linkIdFrom' => array(self::BELONGS_TO, 'YiiscraperLink', 'link_id_from'),
			'linkIdTo' => array(self::BELONGS_TO, 'YiiscraperLink', 'link_id_to'),
		);
	}

	public function attributeLabels() {
		return array(
			'link_id_from' => null,
			'link_id_to' => null,
			'linkIdFrom' => null,
			'linkIdTo' => null,
		);
	}

	public function search() {
		$criteria = new CDbCriteria;

		$criteria->compare('link_id_from', $this->link_id_from);
		$criteria->compare('link_id_to', $this->link_id_to);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}
}