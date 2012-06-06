<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Head.php';

require_once 'Yan/Uri.php';

class Yan_UriTest extends PHPUnit_Framework_TestCase
{
	public function testConstruct()
	{
		try {
			new Yan_Uri('http://yanbingbing.com');
		} catch (Yan_Exception $e) {
			$this->fail($e->getMessage());
		}
	}

	public function testGetPart()
	{
		$uri = new Yan_Uri('http://root:123123@yanbingbing.com:8000/index.php?a=1&b=2#frag');
		try {
			$this->assertEquals('http', $uri->getScheme());
			$this->assertEquals('root', $uri->getUsername());
			$this->assertEquals('123123', $uri->getPassword());
			$this->assertEquals('yanbingbing.com', $uri->getHost());
			$this->assertEquals('8000', $uri->getPort());
			$this->assertEquals('/index.php', $uri->getPath());
			$this->assertEquals('a=1&b=2', $uri->getQuery());
			$this->assertEquals('frag', $uri->getFragment());
		} catch (Yan_Exception $e) {
			$this->fail($e->getMessage());
		}
	}

	public function testGetPartComplex()
	{
		$uri = new Yan_Uri('协议://用户::&&&4554YY@@yanbingbing.中国::端口/a/b//???##frag');
		try {
			$this->assertEquals('协议', $uri->getScheme());
			$this->assertEquals('用户', $uri->getUsername());
			$this->assertEquals(':&&&4554YY', $uri->getPassword());
			$this->assertEquals('@yanbingbing.中国', $uri->getHost());
			$this->assertEquals(':端口', $uri->getPort());
			$this->assertEquals('/a/b//', $uri->getPath());
			$this->assertEquals('??', $uri->getQuery());
			$this->assertEquals('#frag', $uri->getFragment());
		} catch (Yan_Exception $e) {
			$this->fail($e->getMessage());
		}
	}

	public function testGetUri()
	{
		$uri = new Yan_Uri('协议://用户::&&&4554YY@@yanbingbing.中国::端口/a/b//???##frag');
		$this->assertEquals('协议://用户::&&&4554YY@@yanbingbing.中国::端口/a/b//???##frag', $uri->getUri());
		$this->assertEquals('协议://用户::&&&4554YY@@yanbingbing.中国::端口/a/b//???##frag', $uri);
	}
}
