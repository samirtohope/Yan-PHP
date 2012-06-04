<?php
class CaptchaController extends Yan_Controller
{
	protected function captcha()
	{
		Yan_Session::start();
		$captcha = new Yan_Captcha_Image(array(
			'name' => 'valicode',
			'format' => $this->_request['format'],
			'fonts' => array(
				array('spacing' => -2, 'minSize' => 26, 'maxSize' => 30,
					'file' => APP_PATH . '/Assets/Fonts/Ding-DongDaddyO.ttf'),
				/*array('spacing' =>-1.5,'minSize' => 28, 'maxSize' => 30,
								'file' => APP_PATH.'/Assets/Fonts/StayPuft.ttf')*/
			),
			'height' => 50
		));
		$captcha->setSession($_SESSION);
		return new Yan_Output_Captcha($this->_response, array(
			'captcha' => $captcha
		));
	}
}