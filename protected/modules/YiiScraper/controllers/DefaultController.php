<?php

class DefaultController extends Controller
{
	public function actionIndex()
	{
		$this->render('index');
	}
	
	public function actionInstall()
	{
		$this->module->install();
		echo 'OK: installed';
	}

	public function actionUninstall()
	{
		$this->module->uninstall();
		echo 'OK: uninstalled';
	}
}