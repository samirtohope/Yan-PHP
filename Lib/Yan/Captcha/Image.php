<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Image.php 19 2012-04-28 02:42:04Z kakalong $
 */

require_once 'Yan/Captcha/Abstract.php';

/**
 * Yan_Captcha_Image
 *
 * @category   Yan
 * @package    Yan_Captcha
 */
class Yan_Captcha_Image extends Yan_Captcha_Abstract
{
	protected static $VN = array("a", "e", "i", "o", "u", "y","2","3","4","5","6","7","8","9");
	protected static $CN =  array("b","c","d","f","g","h","j","k","m","n","p","q","r",
		"s","t","u","v","w","x","z","2","3","4","5","6","7","8","9");

	/**
	 * Session
	 *
	 * @var & $_SESSION
	 */
	protected $_session = array();

	/**
	 * Element name
	 *
	 * Useful to generate/check form fields
	 *
	 * @var string
	 */
	protected $_name = 'captcha';

	/**
	 * Generated word
	 *
	 * @var string
	 */
	protected $_word;

	/**
	 * Length of the word to generate
	 *
	 * @var integer
	 */
	protected $_wordlen = 5;

	/** Width of the image */
	protected $_width  = 200;

	/** Height of the image */
	protected $_height = 70;

	protected $_format = 'png';

	/** Background color in RGB-array */
	protected $_bgColor = array(255, 255, 255);

	/** Foreground colors in RGB-array */
	protected $_colors = array(
		array(27,78,181), // blue
		array(22,163,35), // green
		array(214,36,7),  // red
	);

	/** Shadow color in RGB-array or null */
	protected $_shadowColor = null; //array(0, 0, 0);

	/**
	 * Font configuration
	 *
	 * - font: TTF file
	 * - spacing: relative pixel space between character
	 * - minSize: min font size
	 * - maxSize: max font size
	 */
	protected $_fonts = array();

	/** Wave configuracion in X and Y axes */
	protected $_waveX = array('period'=>11,'amplitude'=>5);
	protected $_waveY = array('period'=>12,'amplitude'=>14);

	/** letter rotation clockwise */
	protected $_maxRotation = 8;

	/**
	 * Blur effect for better image quality (but slower image processing).
	 * Better image results with scale=3
	 */
	protected $_blur = false;

	protected $_contentType = 'image/png';

	public function setSession(array & $session) {
		$this->_session = & $session;
		return $this;
	}

	public function setName($name) {
		$this->_name = $name;
		return $this;
	}

	public function getName() {
		return $this->_name;
	}

	public function getContentType(){
		return $this->_contentType;
	}

	/**
	 * Retrieve word length to use when genrating captcha
	 *
	 * @return integer
	 */
	public function getWordlen() {
		return $this->_wordlen;
	}

	/**
	 * Set word length of captcha
	 *
	 * @param integer $wordlen
	 * @return this
	 */
	public function setWordlen($wordlen)
	{
		$this->_wordlen = $wordlen;
		return $this;
	}

	/**
	 * add font config to random font
	 *
	 * $parram $font array('spacing' => -3, 'minSize' => 27, 'maxSize' => 30, 'file' => 'AntykwaBold.ttf')
	 * @return Yan_Captcha_Image
	 */
	public function addFont(array $font) {
		$this->_fonts[] = $font;
		return $this;
	}

	public function setFonts(array $fonts) {
		$this->_fonts = $fonts;
		return $this;
	}

	/**
	 * add a color to random Foreground colors
	 *
	 * @param $color array RGB (red,green,blue)
	 * @return Yan_Captcha_Image
	 */
	public function addFgColor($color) {
		$this->_colors[] = $this->_getColor($color);
		return $this;
	}

	/**
	 * set background color
	 *
	 * @param $color array RGB (red,green,blue)
	 * @return Yan_Captcha_Image
	 */
	public function setBgColor($color) {
		$this->_bgColor = $this->_getColor($color);
		return $this;
	}

	/**
	 * set shadow color
	 *
	 * @param $color array RGB (red,green,blue) or #ffffff
	 * @return Yan_Captcha_Image
	 */
	public function setShadowColor($color) {
		$this->_shadowColor = $this->_getColor($color);
		return $this;
	}

	protected function _getColor($color) {
		if (is_array($color)) {
			return $color;
		}

		if (preg_match('/^#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i',(string)$color,$m)) {
			return array($m[1],$m[2],$m[3]);
		}
		return null;
	}

	/**
	 * set wave effect
	 *
	 * $param $wave array('x'=>array('period'=>int,'amplitude'=>int),y=>array('period'=>int,'amplitude'=>int))
	 * @return Yan_Captcha_Image
	 */
	public function setWave(array $wave) {
		if (isset($wave['x'])) {
			$this->_waveX = $wave['x'];
		}
		if (isset($wave['y'])) {
			$this->_waveY = $wave['y'];
		}
		return $this;
	}

	/**
	 * enable or disable blur effect
	 *
	 * @param $flag boolean
	 * @return Yan_Captcha_Image
	 */
	public function setBlur($flag=true) {
		$this->_blur = (bool) $flag;
		return $this;
	}

	public function setFormat($format){
		$format = strtolower($format);
		$auto = $format == 'auto';
		$supported = imagetypes();
		if (($auto || $format=='png') && ($supported & IMG_PNG)){
			$this->_format = 'png';
			$this->_contentType = 'image/png';
		}
		elseif(($auto||$format=='jpeg'||$format=='jpg')&&($supported & IMG_JPG))
		{
			$this->_format = 'jpeg';
			$this->_contentType = 'image/jpeg';
		}
		elseif(($auto||$format=='gif') && ($supported & IMG_GIF))
		{
			$this->_format = 'gif';
			$this->_contentType = 'image/gif';
		}
		return $this;
	}

	/**
	 * Set captcha image height
	 *
	 * @param  int $height
	 * @return Yan_Captcha_Image
	 */
	public function setHeight($height)
	{
		$this->_height = $height;
		return $this;
	}

	/**
	 * Set captcha image width
	 *
	 * @param  int $width
	 * @return Yan_Captcha_Image
	 */
	public function setWidth($width)
	{
		$this->_width = $width;
		return $this;
	}

	/**
	 * output captcha
	 * 
	 * @param boolean $return
	 * @return stream output if set $return true
	 */
	public function render($return = false)
	{
		$this->_generate();
		$img = $this->_generateImage();
		$func = 'image'.$this->_format;
		if ($return) {
			ob_start();
		}
		$func($img);
		imagedestroy($img);
		if ($return) {
			return ob_get_clean();
		}
	}

	/**
	 * Generate a new captcha word
	 *
	 * @return string new captcha word
	 */
	protected function _generate()
	{
		$word       = '';
		$wordLen    = $this->getWordlen();
		$vowels     = self::$VN;
		$consonants = self::$CN;

		for ($i=0; $i < $wordLen; $i = $i + 2) {
			// generate word with mix of vowels and consonants
			$consonant = $consonants[array_rand($consonants)];
			$vowel     = $vowels[array_rand($vowels)];
			$word     .= $consonant . $vowel;
		}

		if (strlen($word) > $wordLen) {
			$word = substr($word, 0, $wordLen);
		}
		$this->_session[$this->getName()] = $word;
		$this->_word = $word;
	}

	protected function _generateImage()
	{
		$scale = 2;

		/** Initialization */

		// allocate
		$img = imagecreatetruecolor($this->_width * $scale, $this->_height * $scale);

		// Background color
		$bgColor = $this->_allocateColor($img, $this->_bgColor);
		imagefilledrectangle($img, 0, 0, $this->_width * $scale, $this->_height * $scale, $bgColor);

		// Foreground color
		$fgColor = $this->_allocateColor($img, $this->_colors[mt_rand(0, count($this->_colors)-1)]);

		// Shadow color
		$shadowColor = null;
		if (! empty($this->_shadowColor)) {
			$shadowColor = $this->_allocateColor($img, $this->_shadowColor);
		}




		/** Text insertion */

		$text = $this->_word;
		if (empty($this->_fonts)) {
			require_once 'Yan/Captcha/Exception.php';
			throw new Yan_Captcha_Exception('No font file to use!');
		}
		$fontcfg  = $this->_fonts[array_rand($this->_fonts)];

		// Full path of font file
		$fontfile = $fontcfg['file'];

		// Text generation (char by char)
		$x      = 20 * $scale;
		$y      = round(($this->_height*27/40)*$scale);
		$length = strlen($text);
		for ($i=0; $i<$length; $i++) {
			$degree   = rand($this->_maxRotation*-1, $this->_maxRotation);
			$fontsize = rand($fontcfg['minSize'], $fontcfg['maxSize']) * $scale;
			$letter   = substr($text, $i, 1);

			if ($shadowColor) {
				$coords = imagettftext($img, $fontsize, $degree,
					$x+$scale, $y+$scale,
					$shadowColor, $fontfile, $letter);
			}
			$coords = imagettftext($img, $fontsize, $degree,
				$x, $y,
				$fgColor, $fontfile, $letter);
			$x += ($coords[2]-$x) + ($fontcfg['spacing']*$scale);
		}





		/** Transformations */

		// X-axis wave generation
		$xp = $scale * $this->_waveX['period'] * rand(1,3);
		$k = rand(0, 100);
		for ($i = 0; $i < ($this->_width * $scale); $i++) {
			imagecopy($img, $img,
				$i-1, sin($k+$i/$xp) * ($scale * $this->_waveX['amplitude']),
				$i, 0, 1, $this->_height * $scale);
		}

		// Y-axis wave generation
		$k = rand(0, 100);
		$yp = $scale * $this->_waveY['period'] * rand(1,2);
		for ($i = 0; $i < ($this->_height * $scale); $i++) {
			imagecopy($img, $img,
				sin($k+$i/$yp) * ($scale * $this->_waveY['amplitude']), $i-1,
				0, $i, $this->_width * $scale, 1);
		}
		if ($this->_blur && function_exists('imagefilter')) {
			imagefilter($img, IMG_FILTER_GAUSSIAN_BLUR);
		}



		/* Reduce the image to the final size */
		$imResampled = imagecreatetruecolor($this->_width, $this->_height);
		imagecopyresampled($imResampled, $img,
			0, 0, 0, 0,
			$this->_width, $this->_height,
			$this->_width * $scale, $this->_height * $scale
		);
		imagedestroy($img);
		return $imResampled;
	}

	protected function _allocateColor($img, array $color) {
		return imagecolorallocate($img, $color[0], $color[1], $color[2]);
	}
}