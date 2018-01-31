<?php
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
class ScrapformController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';
	 public $defaultAction = 'create';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','view','test'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('create','update','details','addmount','getgk','getgkcat',
				'getgkdetails','deleteall','getstate','getdistrict','gettaluk','getvillage'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','delete'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Scrapform;
		$model->discount_x=50;
		$model->discount_y=110;
		if(isset($_POST['Scrapform']))
		{
			$model->attributes=$_POST['Scrapform'];
			$publisher_id=$model->publisher_id;
			$publisher=Publisher::model()->findByPk($publisher_id);
			$attr=$model->attribute;
			$url = (empty($model->url) ? $publisher->url : $model->url);
			$uri = (empty($publisher->uri) ? $model->uri : $publisher->uri);
			$discount_x=$model->discount_x;
			$discount_y=$model->discount_y;
			$mountcart_categories=json_encode($_POST['Scrapform']['mount_categories']);
			$publisher_function=$publisher->function_name;
			$status=$this->$publisher_function($publisher_id,$url,$uri,$attr,$mountcart_categories,$discount_x,$discount_y);
			
			$result=json_decode($status);
			if($result->status==Scrapform::STATUS_SUCCESS){
				echo "Status : ".$result->status."<br/> Inserted : ".$result->inserted." </br>Out of Stock : ".$result->OutStock;
			}
			else
			{
				echo "Status : ".$result->status."<br/> Message : ".$result->message." <br/>Error : ".$result->error;
			}
				
		}
		$this->render('create',array(
			'model'=>$model,
		));
			
	}

	public function actionDetails()
	{
		if(isset($_POST['publisher_id']))
		{
			$publisher_id=$_POST['publisher_id'];
			$publisher=Publisher::model()->findByPk($publisher_id);
			$publisher_function=$publisher->function_name_other;
			$status=$this->$publisher_function($publisher_id);
			$result=json_decode($status);
			if($result->status=='error')			
				echo $result->message;
			else
				echo "Status : ".$result->status."<br/> Message : ".$result->message." </br> Error : ".$result->error;	

		}
		
		$this->render('details');
	}

	public function actionAddmount()
	{
		if(isset($_POST['publisher_id']))
		{
			$publisher_id=$_POST['publisher_id'];
			$book=Book::model()->findAllByAttributes(array('publisher_id'=>$publisher_id,'isCompleted'=>'1','sendtoMountcart'=>'0','stock_status'=>'1'));
			if(!empty($book))
			{	
		        $results = array();
		        $stock_status=5; 
		        $quantity=100;
		        $status = 1;
		        try {
		            foreach ($book as $book_data) {
		                $this->addToMountCart($book_data->attributes, $stock_status, $quantity, $status); 
		            }
		           
		        } catch (\Exception $e) {
		            echo $e->getMessage();
		        }
		        echo "succes";
		    }
		    else{
		    	echo "No more books to push";
		    }    

		}
		
		$this->render('addmount');
	}

	/*Get Arihant Books */

	public function getArihant($publisher_id,$url,$uri,$attr,$mc_cat)
	{
		$count=0;
		$stock_count=0;
		$url = $url.$uri;
		$params=array('sort' => 'p.sort_order-ASC','page' => '1','limit' =>'500');
		$client = new Client();
		$res = $client->request('GET', $url,['query'=>$params]);
		$contents=$res->getBody()->getContents();
		$crawler = new Crawler($contents);
        $filter = $crawler->filter('div.prodexp')->filter('div.col-md-4');

        try
        {
	        foreach ($filter as $content) {
	        	$crawler = new Crawler($content);
	        	$nostock=$crawler->filter('span.nostock')->count();
	        	if($nostock==Scrapform::STOCK_STATUS_OUT){
		        	$product_id_count=Book::model()->countByAttributes(array("publisher_id"=>$publisher_id));
		        	$product_id_count++;
		        	$book=new Book;
		    		$book->book_name = $crawler->filter('a.prdocutname')->html();
		    		$book->mrp = $crawler->filter('span.priceold')->html();
		    		$book->discount_price = $this->calculateDiscount($book->mrp);
		    		$book->book_url = $crawler->filter('a.prdocutname')->attr('href');
		    		$book->image_thumb_url = $crawler->filter('div.thumbnail > a > img')->attr('src');
		    		$book->publisher_id=$publisher_id;
		    		$book->category_id='1';
		    		$book->attribute=$attr;
		    		$book->date_added=date('Y-m-d H:i:s');
		    		$book->mc_categories=$mc_cat;
		    		$book->product_id='ARIH'.($product_id_count);
	    			$valid=$book->save(false);	
	    			if($valid){
		    			$count++;
		            	echo 'Completed '.$count.' record'."</br>";	
	       			}
	    		}
	    		else
	    		{
	    			$stock_count++;
	    		}
	       		$result['status'] =Scrapform::STATUS_SUCCESS;
        		$result['inserted']=$count.' Books';
        		$result['OutStock']=$stock_count.' Books';
	            $result['error'] ='no'; 
	        }
	        return json_encode($result);
	        
    	}	
        catch(\Exception $e) {
            $result['status'] = Scrapform::STATUS_ERROR;
            $result['message'] =$e->getMessage();
            $result['error']='Failed at '.++$count.' record';
            return json_encode($result);
        }
       
	}

	/* Get Arihant Book Details */

	public function getArihantdetails($publisher_id)
	{
		$result=array();
		$count=0;
		//$mc_categories=array('main'=>105,'sub'=>207);
		$book=Book::model()->findAllByAttributes(array('publisher_id'=>$publisher_id,'isCompleted'=>'0'));
		try
		{	
			if(!empty($book))
			{
				foreach($book as $book_data)
				{
					$url = $book_data->book_url;
					$client = new Client();
					$res = $client->request('GET', $url);
					$contents=$res->getBody()->getContents();
					$crawler = new Crawler($contents);
			        $filter = $crawler->filter('div.speci-box')->filter('div.row')->filter('div.col-xs-8')->eq(1)->html();
			        $book_data->author=$crawler->filter('div.speci-box')->filter('div.row')->filter('div.col-xs-8')->eq(0)->html();
			        $book_data->edition=$crawler->filter('div.speci-box')->filter('div.row')->filter('div.col-xs-8')->eq(1)->html();
			        $book_data->isbn=$crawler->filter('div.speci-box')->filter('div.row')->filter('div.col-xs-8')->eq(2)->html();
			        $book_data->language=$crawler->filter('div.speci-box')->filter('div.row')->filter('div.col-xs-8')->eq(5)->html();
			        $book_data->binding=$crawler->filter('div.speci-box')->filter('div.row')->filter('div.col-xs-8')->eq(6)->html();
			        $book_data->pages=$crawler->filter('div.speci-box')->filter('div.row')->filter('div.col-xs-8')->last()->html();
			        $book_data->image_main_url=$crawler->filter('div.mainimage > img')->attr('src');
			        $book_data->description=$crawler->filter('div.descrition')->filter('div.row')->filter('div.productinforight')->last()->html();
			        $book_data->isCompleted=1;
			  		$valid=$book_data->save(false);
			  		if($valid){
		    			$count++;
		            	echo 'Completed '.$count.' record'."</br>";	
	       			}
		       		$result['status'] =  Scrapform::STATUS_SUCCESS;
		            $result['message'] = 'Completed '.$count.' records';
		            $result['error'] ='no';  
				}
					return json_encode($result);
			}
			else
			{
				$result['status'] = Scrapform::STATUS_ERROR;
			 	$result['message'] = Scrapform::SUCCESS_MESSAGE_TEXT;
		        $result['error'] ='no';
			 	return json_encode($result);
			}


		}
		catch(\Exception $e) {
            $result['status'] = Scrapform::STATUS_ERROR;
            $result['message'] =$e->getMessage();
            $result['error']='Failed at '.++$count.' record';
            return json_encode($result);
        }
        
	}

 /* Get all Gk Categories */

	public function actionGetgkcat()
	{
		$count=0;
		$url="https://www.gkpublications.com/index.php?route=product/category&path=150";
		$params=array('sort' => 'p.sort_order-ASC','page' => '1','limit' =>'500');
		$client = new Client();
		$res = $client->request('GET', $url);
		$contents=$res->getBody()->getContents();
		$crawler = new Crawler($contents);
        $filter = $crawler->filter('div.tree-menu')->filter('ul.box-category')->filter('ul.accordion-body')->filter('li');
        //echo "<pre>",print_r($filter),"</pre>";exit;
        
        try
        {
	        foreach ($filter as $content) {
	        	$crawler = new Crawler($content);
	        	foreach($crawler as $sample){
	             $cat_name= $crawler->filter('a')->html();
	             $cat_url= $crawler->filter('a')->attr('href');
	             $category=new Category;
	             $category->publisher_id=12;
	             $category->category_name=$cat_name;
	             $category->url=$cat_url;
	             $category->save(false);
	             $count++;
	             echo $count;
	             
	        	}
	        	if($count=="32")break;
	        	
	        }
	        exit;
	        if($valid)
         		return 'success'.$count; 
    	}	
        catch(\Exception $e) {
            echo $e->getMessage();
            $result['status'] = 'failed';
            return $result;
        }
	}

	/* Get Gk publication Books */

	public function getGk($publisher_id,$url,$uri,$attr,$mc_cat,$discount_x,$discount_y)
	{
		$count=0;
		$stock_count=0;
		$url = $url."&".$uri;
		$image_thumb_url=null;
		//$params=array('sort' => 'p.sort_order-ASC','page' => '1','limit' =>'500');
		
        try
        {
        	$client = new Client();
			$res = $client->request('GET', $url);
			$contents=$res->getBody()->getContents();
			$crawler = new Crawler($contents);
	        $filter = $crawler->filter('div.products-row')->filter('div.item-full');

	        foreach ($filter as $content) {
	        	$crawler = new Crawler($content);
	        	$product_id_count=Book::model()->countByAttributes(array("publisher_id"=>12));
	    		$product_id_count++;
	        	$book=new Book;
	    		$book_url = $crawler->filter('div.product-meta')->filter('h3')->filter('a')->attr('href');
	    		$result_data=$this->getGkDetails($book_url);
	    		$details=json_decode($result_data);
    			if($details->stock_status){
		    		$book_name = $crawler->filter('div.product-meta')->filter('h3')->filter('a')->html();
		    		$mrp =$crawler->filter('div.product-meta')->filter('span.price-new')->html();
		    		if($crawler->filter('div.image > a > img')->count()){
		    			$image_thumb_url=$crawler->filter('div.image > a > img')->attr('src');
		    		}

		    		$book->book_name = $book_name;
	    			$book->mrp = $mrp;
	    			$book->discount_price = $this->calculateDiscount($book->mrp,$discount_x,$discount_y);
	    			$book->book_url = $book_url;
	    			$book->image_thumb_url = $image_thumb_url;
	    			$book->image_main_url= $image_thumb_url;
		    		$book->publisher_id=$publisher_id;
		    		$book->attribute=$attr;
		    		$book->product_id='GKPU'.($product_id_count);
		    		$book->mc_categories=$mc_cat;
		    		$book->author='GKP';
		    		$book->edition='2017';
		    		$book->isbn=$details->isbn;
		    		$book->description=$details->description;
		    		$book->isCompleted=1;
		    		$valid=$book->save(false);
		    		if($valid){
		    			$count++;
		            	echo 'Completed '.$count.' record'."</br>";	
		       		}	  
	        	}
	        	else{
	        		$stock_count++;
	        	}

        		
	        }
	        	$result['status'] =Scrapform::STATUS_SUCCESS;
        		$result['inserted']=$count.' Books';
        		$result['OutStock']=$stock_count.' Books';
	            $result['error'] ='no';
	         	return json_encode($result); 
    	}	
        catch(\Exception $e) {
            $result['status'] = Scrapform::STATUS_ERROR;
            $result['message'] =$e->getMessage();
            $result['error']='Failed at '.++$count.' record';
            return json_encode($result);
        }
       
	}

	/* Get GK Details */

	public function getGkDetails($url)
	{
		$result=array();
		$count=0;

			$url = $url;
			$client = new Client();
			$res = $client->request('GET', $url);
			$contents=$res->getBody()->getContents();
			$crawler = new Crawler($contents);
	        $stock=$crawler->filter('div.product-info')->filter('div.col-xs-12')->filter('ul.list-unstyled')->eq(2)->html();
	        if(preg_match('~[0-9]~', $stock) > Scrapform::STOCK_STATUS_OUT){
	        	$isbn=$crawler->filter('div.product-info')->filter('div.col-xs-12')->filter('ul.list-unstyled > li')->last()->html();
	        	$description=$crawler->filter('div.product-info')->filter('div#tab-description')->html();
	        	$result['stock_status']=Scrapform::STOCK_STATUS_IN;
	        	$result['isbn'] =$this->filter_number($isbn);
            	$result['description'] = $description;
			}
			else{
				$result['stock_status']=Scrapform::STOCK_STATUS_OUT;	
			}
			return json_encode($result);

	}

	/* Pushing the data to mountcart database */


	public function addToMountCart($data, $stock_status=0, $quantity=0, $status = 0) {
        $result = array(); 
        $related_products_all=array();
        $product_name_count=strlen($data['book_name']);
        $categories=json_decode($data['mc_categories'],true); 

        
            
        if($data['pages']==null){
        	$data['pages']="";
        }
        try {
            $imageFile = $this->pushImage($data);

            $result['imageStatus'] = 'Success';

            Yii::app()->db2->createCommand()->insert('oc_product', array(
                'model' => $data['product_id'],
                'author' => $data['author'],
                'isbn' => $data['isbn'],
                'quantity' => $quantity,
                'stock_status_id' => $stock_status,
                'image' => $imageFile,
                'manufacturer_id' => $data['publisher_id'],
                'price' =>$this->filter_number_from_string($data['mrp']),
                'cost' => 0,
                'edit' => $data['edition'],
                'sku' => '',
                'upc' => '',
                'ean' => isset($data['language']) ? $data['language'] : "English",
                'jan' => $data['pages'],
                'mpn' => '',
                'location' => '',
                'tax_class_id' => 0,
                'length' => '',
                'width' => '',
                'height' => '',
                'date_available' => new CDbExpression('NOW()'),
                'status' => $status,
                'date_added' => new CDbExpression('NOW()'),
                'date_modified' => new CDbExpression('NOW()'),

            ));
            //var_dump(debug_backtrace());exit;
            $result['oc_product_status'] = 'Success';

            $product_id = Yii::app()->db2->getLastInsertID();

            Yii::app()->db2->createCommand()->insert('oc_product_description', array(
                'product_id' => $product_id,
                'language_id' => 1,
                'name' => $data['book_name'],
                'description' => $data['description'],
                'meta_description' => '',
                'meta_keyword' =>$data['book_name'],
                'tag' => ''
            ));

            $result['oc_product_description_status'] = 'Success';

            Yii::app()->db2->createCommand()->insert('oc_product_attribute', array(
                'product_id' => $product_id,
                'attribute_id' => 17,
                'language_id' => 1,
                'text'        =>$data['attribute']
            ));

            
            foreach($categories as $sub)
            {
            	Yii::app()->db2->createCommand()->insert('oc_product_to_category', array(
	                'product_id' => $product_id,
	                'category_id' => $sub,
            	));
            }
            


            Yii::app()->db2->createCommand()->insert('oc_product_reward', array(
                'product_id' => $product_id,
                'customer_group_id' => 1,
                'points' => 0
            ));

            $result['oc_product_reward_status'] = 'Success';

            Yii::app()->db2->createCommand()->insert('oc_product_to_store', array(
                'product_id' => $product_id,
                'store_id' => 0
            ));

            $result['oc_product_to_store_status'] = 'Success';
            $result['status'] = 'Success';
            $result['product_id'] = $product_id;


            /***** Related Products *****/

	    	// while($product_name_count > 0 ){
	     //    	$product_keyword=substr($data['book_name'],0,$product_name_count);
	     //    	$related_products = Yii::app()->db2->createCommand('SELECT product_id FROM `oc_product` WHERE product_id IN (SELECT product_id FROM oc_product_to_category WHERE category_id="'.$categories[1].'") AND product_id IN (SELECT product_id FROM `oc_product_description` WHERE name LIKE "'.$product_keyword.'%") AND product_id IN (SELECT product_id FROM `oc_product_attribute` where text="'.$data['attribute'].'")  AND product_id <> '.$product_id.' LIMIT 10')->queryAll();
	     //    	if(!empty($related_products)){
	     //        	foreach($related_products as $related_id){
	     //        		array_push($related_products_all,$related_id['product_id']);	
	     //        	}
	     //        }	
	  
	     //        $related_products_all_unique=$this->remove_duplicate_from_array($related_products_all);
	     //    	$product_name_count--;
	     //    }
	     //    echo "count=".count($related_products_all_unique)."<br/>";
	    	// if(!empty($related_products_all_unique)){
	     //       	foreach($related_products_all_unique as $related_product_id){
	     //       		echo $related_product_id."<br/>";
	     //    		Yii::app()->db2->createCommand()->insert('oc_product_related', array(
		    //             'product_id' =>$product_id,
		    //             'related_id' =>$related_product_id,
	     //        	));
	     //        	Yii::app()->db2->createCommand()->insert('oc_product_related', array(
		    //             'product_id' =>$related_product_id,
		    //             'related_id' =>$product_id,
	     //        	));
	     //    	}
	        	
	    	// }
            
             /**** Related Products *****/

              //Add Special Discount
            Yii::app()->db2->createCommand()->insert('oc_product_special', array(
                'product_id' => $product_id,
                'customer_group_id' => 1,
                'priority' => 0,
                'price' => $data['discount_price'],
                // 'date_start'=>new CDbExpression('NOW()'),
                // 'date_end'=>new CDbExpression('NOW()')
                'date_start'=>'0000-00-00',
                'date_end'=>'0000-00-00'
            ));

            $this->setPushSuccess($data['id']);

           
        } catch(\Exception $e) {
            echo $e->getMessage();
            $result['status'] = 'failed';
            return $result;
        }
        return 'success';
    } 

    public function pushImage($book) 
	{

        //$link = 'http://123.236.65.191/images/'.$book['url'];
        //$book=Book::model()->findAllByAttributes(array('publisher_id'=>'1'));
        //foreach($book as $book_data)
        //{
        //$destdir = '/var/www/mountcart/image/data/';
        //$destdir = "/home/mountcart12/www/image/data/";

        //$link='https:'.$book['image_main_url'];
        	$link=$book['image_main_url'];
        	if (!file_exists('/home/mynewmountcart/www/image/tmp/')) {
    			mkdir('/home/mynewmountcart/www/image/tmp/', 0777, true);
			}
        	$destdir = "/home/mynewmountcart/www/image/tmp/";
	        //$destdir = "/home/mynewmountcart/www/image/data/";
	        

	        $extension = pathinfo($link, PATHINFO_EXTENSION);
	        $img=file_get_contents($this->file_url($link));
	        $saveFileName = strtotime("now").rand(0,100).'.'.$extension;
	        file_put_contents($destdir.$saveFileName, $img);
	        $destdir1="/home/mynewmountcart/www/image/data/".$saveFileName;


	        /* Image Compression using Imagick */

	        $im = new Imagick($destdir.$saveFileName);
	        $im->optimizeImageLayers();
			$im->setImageCompression(Imagick::COMPRESSION_JPEG);
			$im->setImageCompressionQuality(25);
			$im->writeImages($destdir1, true);

			 /* Image Compression using Imagick */


	         //$source_photo="/home/mynewmountcart/www/image/tmp/".$saveFileName;
	         //$d = $this->compress_image($source_photo, $destdir1, 01);
	         $this->deleteDirectory("/home/mynewmountcart/www/image/tmp/");

	         return 'data/'.$saveFileName;
    	//}
    }

   	private function compress_image($source_url, $destination_url, $quality) {
		$info = getimagesize($source_url);
	 
		if ($info['mime'] == 'image/jpeg') $image = imagecreatefromjpeg($source_url);
		elseif ($info['mime'] == 'image/gif') $image = imagecreatefromgif($source_url);
		elseif ($info['mime'] == 'image/png') $image = imagecreatefrompng($source_url);
	 
		//save it
		imagejpeg($image, $destination_url, $quality);
	 
		//return destination file url
		return $destination_url;
	}


	private function processDiscount($mrp) 
     {
        
        if($mrp < 300) {
            return 20;
        }

        if($mrp > 300 && $mrp < 500) {
            return 40;
        }

        if($mrp > 500 && $mrp < 700) {
            return 60;
        }

        if($mrp > 700 && $mrp < 900) {
            return 80;
        }

        if($mrp > 900 && $mrp < 1000) {
            return 100;
        }

        if($mrp > 1000) {
            return 130;
        }
    }

     private function calculateDiscount($mrp,$discount_x,$discount_y)
     {
     	$mrp = $this->filter_number_from_string($mrp);
     	$processValue=$this->processDiscount($mrp);
     	$discPrice=(($discount_y-$discount_x)/100 * $mrp) + $processValue;
     	return $this->roundUpToAny($discPrice);
     	
     } 
	
	private function filter_number_from_string($string) {
		
       //preg_match_all('!\d+!', $string, $matches);
       //return implode("",$matches[0]);
		$matches=explode(".",$string);
        return $matches[1];
    }

    private function roundUpToAny($n,$x=5,$y=10) {
        return (round($n)%$y <= 2) ? round($n / $y) * $y : round($n / $x ) * $x;
    }

    private function filter_number($string){
    	return filter_var($string, FILTER_SANITIZE_NUMBER_INT);
    }

    public function setPushSuccess($id) {
       $book=Book::model()->findByPk($id);
       $book->sendtoMountcart=1;
       $book->save(false);
    }

    private function file_url($url){
	  $parts = parse_url($url);
	  $path_parts = array_map('rawurldecode', explode('/', $parts['path']));

	  return
	    $parts['scheme'] . '://' .
	    $parts['host'] .
	    implode('/', array_map('rawurlencode', $path_parts));
	}

	private function deleteDirectory($dir) {
    	system('rm -rf ' . escapeshellarg($dir), $retval);
    	return $retval == 0; // UNIX commands return zero on success
	}

	private function remove_duplicate_from_array($array)
	{
		return array_unique($array);
	}

    public function actionTest()
    {
    	//echo mb_detect_encoding("\xEF\x83\x98",'UTF-8',true);
    	$dummy=array('0'=>'A','1'=>'B','2'=>'C','3'=>'D','4'=>'A','5'=>'E','6'=>'B');
    	print_r(array_unique($dummy));

    }

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Scrapform']))
		{
			$model->attributes=$_POST['Scrapform'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDeleteAll()
	{
		//$this->loadModel($id)->delete();
		Book::model()->deleteAll();
		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		//if(!isset($_GET['ajax']))
			//$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('Scrapform');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new Scrapform('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Scrapform']))
			$model->attributes=$_GET['Scrapform'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Scrapform the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Scrapform::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Scrapform $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='scrapform-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	public function actionGetstate(){
		$url = "https://api.data.gov.in/resource/20138aa5-e53f-4d83-a7e2-f280688b11cc";		
		$params=array('format' => 'json','api-key' => '579b464db66ec23bdd0000018c4c71b247294c5648d4738527ad248b','limit' =>'500');
		// $client = new Client();
		// $res = $client->request('GET', $url,['query'=>$params]);
		// $contents=$res->getBody()->getContents();
		//echo "<pre>",print_r($contents),"</pre>";

		$dis=$this->getdistrict();print_r($dis);exit;


$state_list='<li data-val="35" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Andaman &amp; Nicobar Islands</div>
</li>
									<li data-val="28" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Andhra Pradesh</div>
</li>
									<li data-val="12" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Arunachal Pradesh</div>
</li>
									<li data-val="18" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Assam</div>
</li>
									<li data-val="10" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Bihar</div>
</li>
									<li data-val="4" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Chandigarh</div>
</li>
									<li data-val="22" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Chhattisgarh</div>
</li>
									<li data-val="26" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Dadra &amp; Nagar Haveli</div>
</li>
									<li data-val="25" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Daman &amp; Diu</div>
</li>
									<li data-val="30" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Goa</div>
</li>
									<li data-val="24" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Gujarat</div>
</li>
									<li data-val="6" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Haryana</div>
</li>
									<li data-val="2" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Himachal Pradesh</div>
</li>
									<li data-val="1" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Jammu &amp; Kashmir</div>
</li>
									<li data-val="20" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Jharkhand</div>
</li>
									<li data-val="29" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Karnataka</div>
</li>
									<li data-val="32" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Kerala</div>
</li>
									<li data-val="31" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Lakshadweep</div>
</li>
									<li data-val="23" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Madhya Pradesh</div>
</li>
									<li data-val="27" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Maharashtra</div>
</li>
									<li data-val="14" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Manipur</div>
</li>
									<li data-val="17" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Meghalaya</div>
</li>
									<li data-val="15" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Mizoram</div>
</li>
									<li data-val="13" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Nagaland</div>
</li>
									<li data-val="7" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Nct Of Delhi</div>
</li>
									<li data-val="21" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Odisha</div>
</li>
									<li data-val="34" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Puducherry</div>
</li>
									<li data-val="3" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Punjab</div>
</li>
									<li data-val="8" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Rajasthan</div>
</li>
									<li data-val="11" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Sikkim</div>
</li>
									<li data-val="33" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Tamil Nadu</div>
</li>
									<li data-val="40" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Telangana</div>
</li>
									<li data-val="16" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Tripura</div>
</li>
									<li data-val="9" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Uttar Pradesh</div>
</li>
									<li data-val="5" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">Uttarakhand</div>
</li>
									<li data-val="19" class="past" data-type="state" data-next-level="district">
<div class="custom-select-item-content">West Bengal</div>
</li>';
								
$crawler=new crawler($state_list);
$i=0;
while ($i<36){
	//print_r($contents);exit;
	$state_id=$crawler->filter('li')->eq($i)->attr('data-val');
	$state_name=$crawler->filter('li')->filter('div')->eq($i)->html();

	$states=new States;
	$states->id=$state_id;
	$states->state_name=$state_name;

	$states->save();
	echo $state_id." ".$state_name."<br/>";
	$i++;
}
	}
//$attr=$crawler->filter('li')->eq(1)->attr('data-val');

//print_r($attr);
//print_r($content);

public function actionGetdistrict(){

	$url="https://www.wikivillage.in/villages/findvalues";
	$client = new Client();
	$state_id=1;
	while($state_id < 36)
	{
		$i=1;
		$res = $client->request('POST', $url,[
			'form_params' => [
				'type' => 'state',
				'id' => $state_id
			]
		]);
		$contents=$res->getBody()->getContents();
		$crawler=new Crawler($contents);
		
		foreach ($crawler->filter('li') as $li) {
			$district_id=$crawler->filter('li')->eq($i-1)->attr('data-val');
			$district_name=$crawler->filter('li')->filter('div')->eq($i-1)->html();
			echo "district_id->".$district_id;
			echo "district_name->".$district_name;
			$district=new Districts;
			$district->district_id=$district_id;
			$district->state_id=$state_id;
			$district->district_name=$district_name;
			$district->save(false);
			$i++;
		}
	$state_id++;
	}
}	

public function actionGettaluk(){

	$url="https://www.wikivillage.in/villages/findvalues";
	$client = new Client();
	//$district_id=40535;
	$districts=Districts::model()->findAll();

	foreach($districts as $district_value)
	{
		$district_id=$district_value->district_id;
		$i=1;
		$res = $client->request('POST', $url,[
			'form_params' => [
				'type' => 'district',
				'id' => $district_id
			]
		]);
		$contents=$res->getBody()->getContents();
		$crawler=new Crawler($contents);
		
		foreach ($crawler->filter('li') as $li) {
			$taluk_id=$crawler->filter('li')->eq($i-1)->attr('data-val');
			$taluk_name=$crawler->filter('li')->filter('div')->eq($i-1)->html();
			echo "taluk_id->".$taluk_id;
			echo "taluk_name->".$taluk_name;
			$taluk=new Taluks;
			$taluk->taluk_id=$taluk_id;
			$taluk->district_id=$district_id;
			$taluk->taluk_name=$taluk_name;
			$taluk->save(false);
			$i++;
		}
	//exit;
	}
}

public function actionGetvillage(){

	$url="https://www.wikivillage.in/villages/findvalues";
	$client = new Client();
	//$district_id=40535;
	$taluks=Taluks::model()->findAllBySql("Select * from taluks where vi_status=0 LIMIT 1000");

	if(empty($taluks)){
		echo "All Taluks Finished";
	}

	$curl_arr = array();
	$master = curl_multi_init();
	$i=0;
	foreach($taluks as $taluk_value)
	{
		$taluk_id=$taluk_value->taluk_id;
		//$taluk_id="2343186";
		//$i=1;

		//curl requests

		$curl_arr[$i] = curl_init($url);
		curl_setopt($curl_arr[$i], CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_arr[$i], CURLOPT_POSTFIELDS,
            "type=block&id=".$taluk_id);
		//sleep(2);
		curl_multi_add_handle($master, $curl_arr[$i]);
		
		
		// $res = $client->request('POST', $url,[
		// 	'form_params' => [
		// 		'type' => 'block',
		// 		'id' => $taluk_id
		// 	]
		// ]);
		// $contents=$res->getBody()->getContents();

		// $crawler=new Crawler($contents);
		
		// foreach ($crawler->filter('li') as $li) {
		// 	$village_id=$crawler->filter('li')->eq($i-1)->attr('data-val');
		// 	$village_name=$crawler->filter('li')->filter('div')->filter('a')->eq($i-1)->html();
		// 	//echo "village_id->".$village_id;
		// 	//echo "village_name->".$village_name;
		// 	$village=new Villages;
		// 	$village->village_id=$village_id;
		// 	$village->taluk_id=$taluk_id;
		// 	$village->village_name=$village_name;
		// 	$valid=$village->save(false);
			
		// 	$i++;
		// }
		// if($valid)
		// 	{
		// 		$taluk_model=Taluks::model()->findByPk($taluk_value->id);
		// 		$taluk_model->vi_status=1;
		// 		$taluk_model->save(false);
		// 	}
		//exit;
		$i++;
	}
	$i=0;
	do {
		$status=curl_multi_exec($master,$running);
	} while($running > 0);
	
	if($status > 0) {
		 echo "ERROR!\n " . curl_multi_strerror($status);
	}

	echo "results: ";
	foreach($taluks as $taluk_value)
	{
		$taluk_id=$taluk_value->taluk_id;
		$j=1;
		$results = curl_multi_getcontent  ( $curl_arr[$i]  );
		if (!curl_errno($curl_arr[$i])) {
			$info = curl_getinfo($curl_arr[$i]);
			echo 'Took ', $info['total_time'], ' seconds to send a request to ', $info['url'], "\n";
		}
		$crawler=new Crawler($results);
		foreach ($crawler->filter('li') as $li) {

			$village_id=$crawler->filter('li')->eq($j-1)->attr('data-val');
			$village_name=$crawler->filter('li')->filter('div')->filter('a')->eq($j-1)->html();
			//echo "village_id->".$village_id;
			//echo "village_name->".$village_name;
			$village=new Villages;
			$village->village_id=$village_id;
			$village->taluk_id=$taluk_id;
			$village->village_name=$village_name;
			$valid=$village->save(false);
			$j++;
		}

		if($valid)
		{
			$taluk_model=Taluks::model()->findByPk($taluk_value->id);
			//print_r($taluk_model);exit;
			$taluk_model->vi_status=1;
			$taluk_model->save(false);
		}

		//echo( $i . "\n" . $results . "\n");
		$i++;
	}
	echo 'done';
}
}
