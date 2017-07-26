<?php
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
class SiteController extends Controller
{

	CONST ATTR_PHYSICS='Physics';
	CONST ATTR_CHEMISTRY='Chemistry';
	CONST ATTR_MATHS='Mathematics';

	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex($attr)
	{
		$count=1;
		$attr=$_GET['attr'];
		$result = array();
		if($attr==self::ATTR_PHYSICS)
			$uri='physics-textbooks';
		else if($attr==self::ATTR_CHEMISTRY)
			$uri='chemistry-textbooks';
		else
			$uri='maths-textbooks';
		$url = 'https://www.arihantbooks.com/'.$uri;
		
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
	        	$count=Book::model()->countByAttributes(array("publisher_id"=>1));
	        	$book=new Book;
	    		$book->book_name = $crawler->filter('a.prdocutname')->html();
	    		$book->mrp = $crawler->filter('span.priceold')->html();
	    		$book->discount_price = $this->calculateDiscount($book->mrp);
	    		$book->book_url = $crawler->filter('a.prdocutname')->attr('href');
	    		$book->image_thumb_url = $crawler->filter('div.thumbnail > a > img')->attr('src');
	    		$book->publisher_id='1';
	    		$book->category_id='1';
	    		$book->attribute=$attr;
	    		$book->product_id='ARIH'.($count+1);
	    		$nostock=$crawler->filter('span.nostock')->count();
	    		if($nostock==0)
	    		$valid=$book->save(false);	
	        }
    	}	
        catch(\Exception $e) {
            echo $e->getMessage();
            $result['status'] = 'failed';
            return $result;
        }
        echo 'success';   
	}

	public function actionBookdetails()
	{
		$result=array();
		$mc_categories=array('main'=>105,'sub'=>207);
		$book=Book::model()->findAllByAttributes(array('publisher_id'=>'1','isCompleted'=>'0'));
		try
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
		        $book_data->mc_categories=json_encode($mc_categories);
		        $book_data->description=$crawler->filter('div.descrition')->filter('div.row')->filter('div.productinforight')->last()->html();
		        $book_data->isCompleted=1;
		  		$book_data->save(false);
			}
		}
		catch(\Exception $e) {
            echo $e->getMessage();
            $result['status'] = 'failed';
            echo $result;
        }

		echo 'successs';
	}

	public function actionCategories()
	{
		$result = array();
		$url = 'https://www.arihantbooks.com/jee-main--advanced';

		$client = new Client();
		$res = $client->request('GET', $url);
		$contents=$res->getBody()->getContents();
		$crawler = new Crawler($contents);

		$crawler->filter('ul.second_cat')->each(function (Crawler $node) {
            return $node->nodeName();
        });
	}

	public function addToMountCart($data, $stock_status=0, $quantity=0, $status = 0) {
        $result = array();  
        $categories=json_decode($data['mc_categories'],true); 
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
                'manufacturer_id' => 17,
                'price' =>preg_replace('/[a-zA-Z.]/', '', $data['mrp']),
                'cost' => 0,
                'edit' => '2016',
                'sku' => '',
                'upc' => '',
                'ean' => isset($data['language']) ? $data['language'] : "English",
                'jan' => '',
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

            //Add Special Discount
            Yii::app()->db2->createCommand()->insert('oc_product_special', array(
                'product_id' => $product_id,
                'customer_group_id' => 1,
                'priority' => 0,
                'price' => $data['discount_price'],
                'date_start'=>'0000-00-00',
                'date_end'=>'0000-00-00'
            ));

            $this->setPushSuccess($data['id']);

           
        } catch(\Exception $e) {
            echo $e->getMessage();
            $result['status'] = 'failed';
            return $result;
        }
        echo 'success';
    }

	public function pushImage($book) 
	{

        //$link = 'http://123.236.65.191/images/'.$book['url'];
        //$book=Book::model()->findAllByAttributes(array('publisher_id'=>'1'));
        //foreach($book as $book_data)
        //{
        //$destdir = '/var/www/mountcart/image/data/';
        //$destdir = "/home/mountcart12/www/image/data/";
        	$link='https:'.$book['image_main_url'];
	        //$destdir = "/var/www/html/mcscrap/image/data/";
	        $destdir = "/home/mynewmountcart/www/image/data/";
	        $extension = pathinfo($link, PATHINFO_EXTENSION);
	        $img=file_get_contents($link);
	        $saveFileName = strtotime("now").rand(0,100).'.'.$extension;
	        file_put_contents($destdir.$saveFileName, $img);
	        return 'data/'.$saveFileName;
    	//}
    }

    public function actionDiscount()
    {
    	$dis=$this->calculateDiscount(250);
    	echo "discount=".$dis;
    }

     private function processDiscount($mrp) 
     {
        
        if($mrp < 300) {
            return 30;
        }

        if($mrp > 300 && $mrp < 500) {
            return 50;
        }

        if($mrp > 500 && $mrp < 700) {
            return 70;
        }

        if($mrp > 700 && $mrp < 900) {
            return 90;
        }

        if($mrp > 900 && $mrp < 1000) {
            return 110;
        }

        if($mrp > 1000) {
            return 140;
        }
    }

     private function calculateDiscount($mrp)
     {
     	$x=50;
     	$mrp = $this->filter_number_from_string($mrp);
     	$processValue=$this->processDiscount($mrp);
     	$discPrice=(110-$x)/100 * $mrp + $processValue;
     	return $this->roundUpToAny($discPrice);
     } 
	
	private function filter_number_from_string($string) {
        $string = strtolower($string);
        $string = preg_replace('/[a-z.]/', '', $string);
        return $string;
        //if(is_string($string) && strchr($string, 'rs', true))
            //return $this->roundUpToAny(floatval(preg_replace('/[^0-9\.,]+/', '', $string)) * 1000);
        // else {
        //     return $string;
        // }
    }

    private function roundUpToAny($n,$x=5,$y=10) {
        return (round($n)%$y <= 2) ? round($n / $y) * $y : round($n / $x ) * $x;
    }

    public function setPushSuccess($id) {
       $book=Book::model()->findByPk($id);
       $book->sendtoMountcart=1;
       $book->save(false);
    }

    public function actionprocessImage()
    {
    	$data=Book::model()->findByPk(97);
    	$imageFile = $this->pushImage($data);
    	echo $imageFile;
    }


    public function actionmassPush()
    {
       	$book=Book::model()->findAllByAttributes(array('publisher_id'=>'1','isCompleted'=>'1','sendtoMountcart'=>'0'));
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
    }


    public function actionOnePush()
    {
        $stock_status=0;
        $quantity=0;
        $status = 0;
    	$book=Book::model()->findByPk(142);
    	$this->addToMountCart($book->attributes, $stock_status, $quantity, $status); 
    }

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		$model=new LoginForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())
				$this->redirect(Yii::app()->user->returnUrl);
		}
		// display the login form
		$this->render('login',array('model'=>$model));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
}