<?php

/**
 * mavatars for Cotonti CMF
 *
 * @version 1.00
 * @author	esclkm
 * @copyright (c) 2013 esclkm
 */
defined('COT_CODE') or die('Wrong URL');

/* @var $db CotDB */
/* @var $cache Cache */
/* @var $t Xtemplate */

global $db_mavatars, $db_x, $cfg, $R;

cot::$db->registerTable('mavatars');

cot_extrafields_register_table('mavatars');

require_once cot_langfile('mavatars');

require_once cot_incfile('uploads');
require_once cot_incfile('forms');

class mavatar_object
{

	public $dbdata = array();
	public $prop = array();
	public $id = 0;
	private $is_image = false;
	private $images_ext = array('jpg', 'jpeg', 'png', 'gif');

	public function __construct($dbdata)
	{
		if(!is_array($dbdata) && (int)$dbdata > 0)
		{
			global $db, $db_mavatars;
			$id = $dbdata;
			$dbdata = $db->query("SELECT * FROM $db_mavatars WHERE mav_id ='".(int)$id."' LIMIT 1")->fetch();
		}
		if((int)$dbdata['mav_id'] > 0)
		{
			$dbdata['mav_filepath'] = $this->fix_path($dbdata['mav_filepath']);
			if(!empty($dbdata['mav_thumbpath']))
			{
				$dbdata['mav_thumbpath'] = $this->fix_path($dbdata['mav_thumbpath']);
			}
			else
			{
				$dbdata['mav_thumbpath'] = $dbdata['mav_filepath'];
			}
			$this->dbdata = $dbdata;
			$this->id = $dbdata['mav_id'];
			$this->is_image = in_array($this->dbdata['mav_fileext'], $this->images_ext);
		}
		if(!file_exists($this->file_path()))
		{
			$this->id = 0;
		}
	}
	// В этом методе супер соль! это и теги и миниатюры
	public function __get($name)
	{
		if($this->id == 0)
		{
			return false;
		}
		$name = strtolower($name);
		if (preg_match("/thumb_(\d+)_(\d+)_(crop|width|height|auto)_?(.+)?/i", $name, $mt))	
		{
			return $this->thumb($mt[1], $mt[2], $mt[3], $mt[4]);
		}
		if (preg_match("/check_thumb_(\d+)_(\d+)_(crop|width|height|auto)_?(.+)?/i", $name, $mt))	
		{
			return $this->check_thumb($mt[1], $mt[2], $mt[3], $mt[4]);
		}
		if(isset($this->dbdata['mav_'.$name]))
		{
			return $this->dbdata['mav_'.$name];
		}
		if($name == 'file')
		{
			return $this->file_path();
		}		
		if($name == 'show')
		{
			return cot_url('plug', 'e=mavatars&m=show&id='.$this->id);
		}
		if($name == 'download')
		{
			return cot_url('plug', 'r=mavatars&m=download&id='.$this->id.'&'.cot_xg());
		}		
	}
	public function edittags($prefix = "mavatar_")
	{
		if(!$this->id)
		{
			return false;
		}
		
		global $db_mavatars, $cot_extrafields, $L;
		$tags = array(
			'ENABLED' => cot_checkbox(true, $prefix.'enabled['.$this->id.']', '', 'title="'.$L['Enabled'].'"'),
			'FILEORDER' => cot_inputbox('text', $prefix.'order['.$this->id.']', $this->dbdata['mav_order'], 'maxlength="4" size="4"'),
			'FILEDESC' => cot_inputbox('text', $prefix.'desc['.$this->id.']', $this->dbdata['mav_desc']),
			'FILEDESCTEXT' => cot_textarea($prefix.'desc['.$this->id.']', $this->dbdata['mav_desc'], 2, 30),
			'FILETEXT' => cot_inputbox('text', $prefix.'text['.$this->id.']', $this->dbdata['mav_text']),
			'FILETEXTTEXT' => cot_textarea($prefix.'text['.$this->id.']', $this->dbdata['mav_text'], 2, 30),
		);
		foreach ($cot_extrafields[$db_mavatars] as $exfld)
		{
			$uname = mb_strtoupper($exfld['field_name']);
			$tags[$uname] = cot_build_extrafields($prefix.$exfld['field_name'].'['.$this->id.']', $exfld, $this->dbdata['mav_'.$exfld['field_name']]);
			$tags[$uname.'_TITLE'] = isset($L['mavatar_'.$exfld['field_name'].'_title']) ? $L['mavatar_'.$exfld['field_name'].'_title'] : $exfld['field_description'];
		}
		return $tags;
	}

	public function files($mainfile = true, $thumbs = true)
	{
		$file_list = array();
		if(!$this->id)
		{
			return $file_list;
		}
		
		if (file_exists($this->file_path()))
		{
			if ($this->is_image && $thumbs)
			{
				// большое изменение - теперь должны миниатюры хранится в папках !
				foreach (glob($this->dbdata['mav_thumbpath'].'*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir)
				{
					$file = $this->thumb_path($dir);
					if (file_exists($file))
					{
						$file_list[basename($dir)] = $file;
					}
				}
			}
			if ($mainfile)
			{
				$file_list['main'] = $this->file_path();
			}
		}
		return $file_list;
	}

	public function file_path($path = '')
	{
		$path = empty($path) ? $this->dbdata['mav_filepath'] : $path;
		return $path.$this->dbdata['mav_filename'].'.'.$this->dbdata['mav_fileext'];
	}

	public function thumb_path($size_dir)
	{
		$size_dir = $this->fix_path($size_dir);
		return $size_dir.$this->dbdata['mav_filename'].'.'.$this->dbdata['mav_fileext'];
	}

	private function fix_path($path)
	{
		if (!empty($path) && (mb_substr($path, -1) != '/'))
		{
			$path .= '/';
		}
		return $path;
	}

	public function thumb( $width, $height, $resize = 'crop', $filter = '', $quality = 85)
	{
		if (!$this->is_image || !$this->id)
		{
			return false;
		}

		$source_file = $this->file_path();
		if (!file_exists($source_file))
		{
			return false;
		}

		$thumb_dir = $this->dbdata['mav_thumbpath'].$width.'_'.$height.'_'.$resize;
		$thumb_dir .= (!empty($filter)) ? '_'.$filter : '';
		$thumb_dir = $this->fix_path($thumb_dir);
		if (!file_exists($thumb_dir))
		{
			mkdir($thumb_dir, 0777);
			chmod($thumb_dir, 0777);
		}
		$thumb_file = $this->thumb_path($thumb_dir);

		if (file_exists($thumb_file))
		{
			return $thumb_file;
		}

		list($width_orig, $height_orig) = getimagesize($source_file);
		$x_pos = 0;
		$y_pos = 0;

		$width = (mb_substr($width, -1, 1) == '%') ? (int)($width_orig * (int)mb_substr($width, 0, -1) / 100) : (int)$width;
		$height = (mb_substr($height, -1, 1) == '%') ? (int)($height_orig * (int)mb_substr($height, 0, -1) / 100) : (int)$height;

		if ($resize == 'crop')
		{
			$newimage = imagecreatetruecolor($width, $height);
			$width_temp = $width;
			$height_temp = $height;

			if ($width_orig / $height_orig > $width / $height)
			{
				$width = $width_orig * $height / $height_orig;
				$x_pos = -($width - $width_temp) / 2;
				$y_pos = 0;
			}
			else
			{
				$height = $height_orig * $width / $width_orig;
				$y_pos = -($height - $height_temp) / 2;
				$x_pos = 0;
			}
		}
		else
		{
			if ($resize == 'width' || $height == 0)
			{
				if ($width_orig > $width)
				{
					$height = $height_orig * $width / $width_orig;
				}
				else
				{
					$width = $width_orig;
					$height = $height_orig;
				}
			}
			elseif ($resize == 'height' || $width == 0)
			{
				if ($height_orig > $height)
				{
					$width = $width_orig * $height / $height_orig;
				}
				else
				{
					$width = $width_orig;
					$height = $height_orig;
				}
			}
			elseif ($resize == 'auto')
			{
				if ($width_orig < $width && $height_orig < $height)
				{
					$width = $width_orig;
					$height = $height_orig;
				}
				else
				{
					if ($width_orig / $height_orig > $width / $height)
					{
						$height = $width * $height_orig / $width_orig;
					}
					else
					{
						$width = $height * $width_orig / $height_orig;
					}
				}
			}


			$newimage = imagecreatetruecolor($width, $height); //
		}

		switch ($this->dbdata['mav_fileext'])
		{
			case 'gif':
				$oldimage = imagecreatefromgif($source_file);
				break;
			case 'png':
				imagealphablending($newimage, false);
				imagesavealpha($newimage, true);
				$oldimage = imagecreatefrompng($source_file);
				break;
			default:
				$oldimage = imagecreatefromjpeg($source_file);
				break;
		}

		imagecopyresampled($newimage, $oldimage, $x_pos, $y_pos, 0, 0, $width, $height, $width_orig, $height_orig);

		if (function_exists($filter))
		{
		//	$r = new ReflectionFunction($filter);
		//	cot_watch($r->getFileName() ,	$r->getStartLine());
			$filter($newimage);
		}

		switch ($this->dbdata['mav_fileext'])
		{
			case 'gif':
				imagegif($newimage, $thumb_file);
				break;
			case 'png':
				imagepng($newimage, $thumb_file);
				break;
			default:
				imagejpeg($newimage, $thumb_file, $quality);
				break;
		}

		imagedestroy($newimage);
		imagedestroy($oldimage);

		return $thumb_file;
	}

	/**
	 * Creates image thumbnail
	 *
	 * @param array $object Mavatar object or string with img path
	 * @param string $target Thumbnail path
	 * @param int $width Thumbnail width
	 * @param int $height Thumbnail height
	 * @param string $resize resize options: crop auto width height
	 * @param string $filter filter options: need exists function with this name
	 * @param int $quality JPEG quality in %
	 */
	public function check_thumb($width, $height, $resize = 'crop', $filter = '', $quality = 85)
	{


		if (!$this->is_image || $this->id == 0)
		{
			return false;
		}

		$source_file = $this->file_path();


		$thumb_dir = $this->dbdata['mav_thumbpath'].$width.'_'.$height.'_'.$resize;
		$thumb_dir .= (!empty($filter)) ? '_'.$filter : '';
		$thumb_dir = $this->fix_path($thumb_dir);

		$thumb_file = $this->file_path($thumb_dir);

		if (file_exists($thumb_file))
		{
			//empty($filter) || cot_print($thumb_file);
			return $thumb_file;
		}
		else
		{
			return cot_url('plug', 'r=mavatars&m=thumb&ext='.$this->extension.'&cat='.$this->category.'&code='.$this->code.'&id='.$this->id.'&width='.$width
				.'&height='.$height.'&resize='.$resize.'&filter='.$filter.'&quality='.$quality);
		}
	}

}