<?php

/**
 * This is the model class for table "book".
 *
 * The followings are the available columns in table 'book':
 * @property integer $id
 * @property string $book_name
 * @property string $product_id
 * @property string $book_url
 * @property string $author
 * @property string $publisher
 * @property string $language
 * @property string $mrp
 * @property string $discount_price
 * @property string $isbn
 * @property string $edition
 * @property string $stock_status
 * @property string $pages
 * @property integer $length
 * @property integer $width
 * @property integer $height
 * @property integer $weight
 * @property string $binding
 * @property string $description
 * @property string $image_main_url
 * @property string $image_thumb_url
 * @property integer $category_id
 * @property string $attribute
 * @property string $mc_categories
 * @property integer $publisher_id
 * @property string $isCompleted
 * @property string $sendtoMountcart
 */
class Book extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'book';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('book_name, book_url, category_id, attribute', 'required'),
			array('length, width, height, weight, category_id, publisher_id', 'numerical', 'integerOnly'=>true),
			array('product_id, language, binding, mc_categories', 'length', 'max'=>50),
			array('author', 'length', 'max'=>100),
			array('publisher', 'length', 'max'=>150),
			array('mrp, discount_price, isbn', 'length', 'max'=>15),
			array('edition, pages', 'length', 'max'=>10),
			array('stock_status', 'length', 'max'=>20),
			array('attribute', 'length', 'max'=>60),
			array('isCompleted', 'length', 'max'=>2),
			array('sendtoMountcart', 'length', 'max'=>5),
			array('description, image_main_url, image_thumb_url', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, book_name, product_id, book_url, author, publisher, language, mrp, discount_price, isbn, edition, stock_status, pages, length, width, height, weight, binding, description, image_main_url, image_thumb_url, category_id, attribute, mc_categories, publisher_id, isCompleted, sendtoMountcart', 'safe', 'on'=>'search'),
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
			'book_name' => 'Book Name',
			'product_id' => 'Product',
			'book_url' => 'Book Url',
			'author' => 'Author',
			'publisher' => 'Publisher',
			'language' => 'Language',
			'mrp' => 'Mrp',
			'discount_price' => 'Discount Price',
			'isbn' => 'Isbn',
			'edition' => 'Edition',
			'stock_status' => 'Stock Status',
			'pages' => 'Pages',
			'length' => 'Length',
			'width' => 'Width',
			'height' => 'Height',
			'weight' => 'Weight',
			'binding' => 'Binding',
			'description' => 'Description',
			'image_main_url' => 'Image Main Url',
			'image_thumb_url' => 'Image Thumb Url',
			'category_id' => 'Category',
			'attribute' => 'Attribute',
			'mc_categories' => 'Mc Categories',
			'publisher_id' => 'Publisher',
			'isCompleted' => 'Is Completed',
			'sendtoMountcart' => 'Sendto Mountcart',
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
		$criteria->compare('book_name',$this->book_name,true);
		$criteria->compare('product_id',$this->product_id,true);
		$criteria->compare('book_url',$this->book_url,true);
		$criteria->compare('author',$this->author,true);
		$criteria->compare('publisher',$this->publisher,true);
		$criteria->compare('language',$this->language,true);
		$criteria->compare('mrp',$this->mrp,true);
		$criteria->compare('discount_price',$this->discount_price,true);
		$criteria->compare('isbn',$this->isbn,true);
		$criteria->compare('edition',$this->edition,true);
		$criteria->compare('stock_status',$this->stock_status,true);
		$criteria->compare('pages',$this->pages,true);
		$criteria->compare('length',$this->length);
		$criteria->compare('width',$this->width);
		$criteria->compare('height',$this->height);
		$criteria->compare('weight',$this->weight);
		$criteria->compare('binding',$this->binding,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('image_main_url',$this->image_main_url,true);
		$criteria->compare('image_thumb_url',$this->image_thumb_url,true);
		$criteria->compare('category_id',$this->category_id);
		$criteria->compare('attribute',$this->attribute,true);
		$criteria->compare('mc_categories',$this->mc_categories,true);
		$criteria->compare('publisher_id',$this->publisher_id);
		$criteria->compare('isCompleted',$this->isCompleted,true);
		$criteria->compare('sendtoMountcart',$this->sendtoMountcart,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Book the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
