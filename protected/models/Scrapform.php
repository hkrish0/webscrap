<?php

/**
 * This is the model class for table "scrapform".
 *
 * The followings are the available columns in table 'scrapform':
 * @property integer $id
 * @property integer $publisher_id
 * @property string $url
 * @property string $uri
 * @property string $attribute
 */
class Scrapform extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */


	const STATUS_SUCCESS = 'Success';
	const STATUS_ERROR = 'Failed';
	const STOCK_STATUS_IN='1';
	const STOCK_STATUS_OUT='0';
	const SUCCESS_MESSAGE_TEXT='No More books to fetch';

	public $publisher_cat;
	public $mount_categories;
	public $discount_x;
	public $discount_y;
	public function tableName()
	{
		return 'scrapform';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('publisher_id', 'required'),
			array('publisher_id', 'numerical', 'integerOnly'=>true),
			array('uri, attribute', 'length', 'max'=>100),
			array('url', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, publisher_id, url, uri, attribute', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'publisher_id' => 'Publisher',
			'url' => 'Url',
			'uri' => 'Uri',
			'attribute' => 'Attribute',
			'publisher_cat' =>'Publisher Categories',
			'mount_categories' => 'Mount Categories'

		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('publisher_id',$this->publisher_id);
		$criteria->compare('url',$this->url,true);
		$criteria->compare('uri',$this->uri,true);
		$criteria->compare('attribute',$this->attribute,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Scrapform the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
