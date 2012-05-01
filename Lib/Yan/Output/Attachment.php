<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Attachment.php 20 2012-04-28 05:55:14Z kakalong $
 */

require_once 'Yan/Output/Abstract.php';

/**
 * Yan_Output_Attachment
 *
 * @category   Yan
 * @package    Yan_Output
 */
class Yan_Output_Attachment extends Yan_Output_Abstract
{
	/**
	 * file name of attachment output
	 *
	 * @var string
	 */
	protected $_fileName = null;

	/**
	 * charset of file name
	 *
	 * @var string
	 */
	protected $_fileNameCharset = 'utf-8';

	/**
	 * file stream mimetype, try detect if null given
	 *
	 * @var string
	 */
	protected $_mimeType = null;

	/**
	 * wheather use client cache
	 *
	 * @var bool
	 */
	protected $_clientCache = false;

	/**
	 * file of to output
	 *
	 * @var string
	 */
	protected $_file = null;

	/**
	 * set the file to output
	 *
	 * @param string $file
	 * @return Yan_Output_Attachment
	 * @throws Yan_Output_Exception
	 */
	public function setFile($file)
	{
		if (!is_file($file) || !is_readable($file)) {
			require_once 'Yan/Output/Exception.php';
			throw new Yan_Output_Exception("Cannot access file '$file'.");
		}
		$this->_file = $file;
		return $this;
	}

	/**
	 * set the mimetype of filestream
	 *
	 * @param string $mimeType
	 * @return Yan_Output_Attachment
	 */
	public function setMimeType($mimeType)
	{
		$this->_mimeType = $mimeType;
		return $this;
	}

	/**
	 * set charset of file name
	 *
	 * @param string $charset
	 * @return Yan_Output_Attachment
	 */
	public function setFileNameCharset($charset)
	{
		$this->_fileNameCharset = $charset;
		return $this;
	}

	/**
	 * set the filename to output
	 *
	 * @param string $fileName
	 * @return Yan_Output_Attachment
	 */
	public function setFileName($fileName)
	{
		$this->_fileName = $fileName;
		return $this;
	}

	/**
	 * turn on client cache
	 *
	 * @param bool $flag
	 * @return Yan_Output_Attachment
	 */
	public function setClientCache($flag)
	{
		$this->_clientCache = (bool) $flag;
		return $this;
	}

	/**
	 * return body of output
	 *
	 * @return string
	 */
	public function getBody()
	{
		if (null == $this->_file) {
			return '';
		}

		$fileSize = filesize($this->_file);

		if (null === $this->_fileName) {
			$this->_fileName = basename($this->_file);
		}
		$fileName = htmlspecialchars($this->_fileName);

		if (null === $this->_mimeType) {
			$ext = pathinfo($fileName, PATHINFO_EXTENSION);
			$this->_mimeType = $this->_getMimeType($ext);
		}


		$this->_response->setHeader('Content-Type', $this->_mimeType)
			 ->setHeader('Content-Disposition',
				"attachment; filename={$fileName}; charset={$this->_fileNameCharset}")
			 ->setHeader('Content-Length',$fileSize)
			 ->setHeader('Cache-Control','public, must-revalidate, max-age=0')
			 ->setHeader('Content-Transfer-Encoding','binary');
		if ($this->_clientCache) {
			$this->_response->setHeader('Pragma','cache');
		}

		return file_get_contents($this->_file);
	}

	/**
	 * detect file mimetype
	 *
	 * @param string $ext
	 * @return string
	 */
	protected function _getMimeType($ext)
	{
		static $mimetypes = array(
			"ez" => "application/andrew-inset",
			"hqx" => "application/mac-binhex40",
			"cpt" => "application/mac-compactpro",
			"doc" => "application/msword",
			"bin" => "application/octet-stream",
			"dms" => "application/octet-stream",
			"lha" => "application/octet-stream",
			"lzh" => "application/octet-stream",
			"exe" => "application/octet-stream",
			"class" => "application/octet-stream",
			"so" => "application/octet-stream",
			"dll" => "application/octet-stream",
			"oda" => "application/oda",
			"pdf" => "application/pdf",
			"ai" => "application/postscript",
			"eps" => "application/postscript",
			"ps" => "application/postscript",
			"smi" => "application/smil",
			"smil" => "application/smil",
			"wbxml" => "application/vnd.wap.wbxml",
			"wmlc" => "application/vnd.wap.wmlc",
			"wmlsc" => "application/vnd.wap.wmlscriptc",
			"bcpio" => "application/x-bcpio",
			"vcd" => "application/x-cdlink",
			"pgn" => "application/x-chess-pgn",
			"cpio" => "application/x-cpio",
			"csh" => "application/x-csh",
			"dcr" => "application/x-director",
			"dir" => "application/x-director",
			"dxr" => "application/x-director",
			"dvi" => "application/x-dvi",
			"spl" => "application/x-futuresplash",
			"gtar" => "application/x-gtar",
			"hdf" => "application/x-hdf",
			"js" => "application/x-javascript",
			"skp" => "application/x-koan",
			"skd" => "application/x-koan",
			"skt" => "application/x-koan",
			"skm" => "application/x-koan",
			"latex" => "application/x-latex",
			"nc" => "application/x-netcdf",
			"cdf" => "application/x-netcdf",
			"sh" => "application/x-sh",
			"shar" => "application/x-shar",
			"swf" => "application/x-shockwave-flash",
			"sit" => "application/x-stuffit",
			"sv4cpio" => "application/x-sv4cpio",
			"sv4crc" => "application/x-sv4crc",
			"tar" => "application/x-tar",
			"tcl" => "application/x-tcl",
			"tex" => "application/x-tex",
			"texinfo" => "application/x-texinfo",
			"texi" => "application/x-texinfo",
			"t" => "application/x-troff",
			"tr" => "application/x-troff",
			"roff" => "application/x-troff",
			"man" => "application/x-troff-man",
			"me" => "application/x-troff-me",
			"ms" => "application/x-troff-ms",
			"ustar" => "application/x-ustar",
			"src" => "application/x-wais-source",
			"xhtml" => "application/xhtml+xml",
			"xht" => "application/xhtml+xml",
			"zip" => "application/zip",
			"au" => "audio/basic",
			"snd" => "audio/basic",
			"mid" => "audio/midi",
			"midi" => "audio/midi",
			"kar" => "audio/midi",
			"mpga" => "audio/mpeg",
			"mp2" => "audio/mpeg",
			"mp3" => "audio/mpeg",
			"aif" => "audio/x-aiff",
			"aiff" => "audio/x-aiff",
			"aifc" => "audio/x-aiff",
			"m3u" => "audio/x-mpegurl",
			"ram" => "audio/x-pn-realaudio",
			"rm" => "audio/x-pn-realaudio",
			"rpm" => "audio/x-pn-realaudio-plugin",
			"ra" => "audio/x-realaudio",
			"wav" => "audio/x-wav",
			"pdb" => "chemical/x-pdb",
			"xyz" => "chemical/x-xyz",
			"bmp" => "image/bmp",
			"gif" => "image/gif",
			"ief" => "image/ief",
			"jpeg" => "image/jpeg",
			"jpg" => "image/jpeg",
			"jpe" => "image/jpeg",
			"png" => "image/png",
			"tiff" => "image/tiff",
			"tif" => "image/tif",
			"djvu" => "image/vnd.djvu",
			"djv" => "image/vnd.djvu",
			"wbmp" => "image/vnd.wap.wbmp",
			"ras" => "image/x-cmu-raster",
			"pnm" => "image/x-portable-anymap",
			"pbm" => "image/x-portable-bitmap",
			"pgm" => "image/x-portable-graymap",
			"ppm" => "image/x-portable-pixmap",
			"rgb" => "image/x-rgb",
			"xbm" => "image/x-xbitmap",
			"xpm" => "image/x-xpixmap",
			"xwd" => "image/x-windowdump",
			"igs" => "model/iges",
			"iges" => "model/iges",
			"msh" => "model/mesh",
			"mesh" => "model/mesh",
			"silo" => "model/mesh",
			"wrl" => "model/vrml",
			"vrml" => "model/vrml",
			"css" => "text/css",
			"html" => "text/html",
			"htm" => "text/html",
			"asc" => "text/plain",
			"txt" => "text/plain",
			"rtx" => "text/richtext",
			"rtf" => "text/rtf",
			"sgml" => "text/sgml",
			"sgm" => "text/sgml",
			"tsv" => "text/tab-seperated-values",
			"wml" => "text/vnd.wap.wml",
			"wmls" => "text/vnd.wap.wmlscript",
			"etx" => "text/x-setext",
			"xml" => "text/xml",
			"xsl" => "text/xml",
			"mpeg" => "video/mpeg",
			"mpg" => "video/mpeg",
			"mpe" => "video/mpeg",
			"qt" => "video/quicktime",
			"mov" => "video/quicktime",
			"mxu" => "video/vnd.mpegurl",
			"avi" => "video/x-msvideo",
			"movie" => "video/x-sgi-movie",
			"ice" => "x-conference-xcooltalk"
		);
		return array_key_exists($ext,$mimetypes) ? $mimetypes[$ext] : "application/octet-stream";
	}
}