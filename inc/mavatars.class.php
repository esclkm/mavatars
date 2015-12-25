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

class mavatar
{

	/**
	 * @var array Total mavatar config
	 */
	static $config = array();

	/**
	 * @var string Extension
	 */
	public $extension = '__default';

	/**
	 * @var string category
	 */
	public $category = '__default';

	/**
	 * @var string code
	 */
	public $code;

	/**
	 * @var array mavatar files (mavatars) array
	 */
	public $mavatars = array();

	/**
	 * @var array code
	 */
	private $suppressed_ext = array('php', 'php3', 'php4', 'php5', '', 'js', 'exe', 'swf');
	private $filepath = '';
	private $thumbpath = '';
	private $required = '';
	private $allowed_ext = '';
	private $maxsize = '';

	public function __construct($extension, $category, $code, $inputdata = null)
	{
		$this->get_config($extension, $category);

		$this->extension = $extension;
		$this->category = $category;
		$this->code = $code;

		$this->get_mavatars($inputdata);
	}

	/**
	 * Загружает таблицу конфигов
	 */
	protected function load_config_table()
	{
		global $cfg;
		if (!empty(self::$config))
		{
			return true;
		}
		$tpaset = str_replace("\r", "", $cfg['plugin']['mavatars']['set']);
		$tpaset = explode("\n", $tpaset);
		foreach ($tpaset as $val)
		{
			$val = explode('|', $val);
			$val = array_map('trim', $val);
			if (count($val) > 1)
			{
				$val_ext = empty($val[0]) ? '__default' : $val[0];
				$val_cat = (empty($val[1]) || empty($val[0])) ? '__default' : $val[1];

				$val_path = $this->fix_path($val[2], $cfg['photos_dir']);
				$val_thumbspath = $this->fix_path($val[3], $val_path);

				$val[5] = str_replace(array(' ', '.', '*'), array('', '', ''), $val[5]);
				$extensions = explode(',', mb_strtolower($val[5]));

				$mav_cfg[$val_ext][$val_cat] = array(
					'filepath' => $val_path,
					'thumbspath' => $val_thumbspath,
					'required' => (int)$val[4] ? 1 : 0,
					'allowed_ext' => (!empty($val[5])) ? $extensions : array(),
					'maxsize' => ((int)$val[6] > 0) ? (int)$val[6] : 0
				);
			}
		}
		if (!$mav_cfg['__default']['__default'])
		{
			$def_photodir = $this->fix_path($cfg['photos_dir']);
			$mav_cfg['__default']['__default'] = array(
				'filepath' => $def_photodir,
				'thumbspath' => $def_photodir,
				'required' => 0,
				'allowed_ext' => array(),
				'maxsize' => 0
			);
		}
		self::$config = $mav_cfg;
		return true;
	}

	/**
	 * Функция загружает текущую конфигурацию
	 *
	 * @param string $extension Расширение
	 * @param string $category $categoryКатегория
	 */
	protected function get_config($extension = '__default', $category = '__default')
	{
		$this->load_config_table();

		if (!isset(self::$config[$extension]))
		{
			$extension = '__default';
		}
		if ($extension == '__default')
		{
			$category = '__default';
		}
		else
		{
			if ($category != '__default')
			{

				$cat_parents = cot_structure_parents($extension, $category);
				$cat_parents = array_reverse($cat_parents);

				$category = '__default';
				foreach ($cat_parents as $cat)
				{
					if (isset(self::$config[$extension][$cat]))
					{
						$category = $cat;
						break;
					}
				}
			}
			if (!isset(self::$config[$extension][$category]))
			{
				$extension = '__default';
				$category = '__default';
			}
		}
		$this->filepath = self::$config[$extension][$category]['filepath'];
		$this->thumbpath = self::$config[$extension][$category]['thumbspath'];
		$this->required = self::$config[$extension][$category]['required'];
		$this->allowed_ext = self::$config[$extension][$category]['allowed_ext'];
		$this->maxsize = self::$config[$extension][$category]['maxsize'];
	}

	/**
	 * Функция получает маватары для текущего элемента
	 * @param $mavatars_ids array массив маватаров
	 * @return array
	 */
	public function get_mavatars($mavatars_ids = null)
	{
		global $db, $db_mavatars;
		$this->mavatars = array();
		if ($this->code != 'new')
		{
			if(is_array($mavatars_ids))
			{
				$mavs = array();
				foreach ($mavatars_ids as $data)
				{
					if ($data['mav_extension'] == $this->extension && $data['mav_code'] == $this->code)
					{
						$mavs[] = $data;
					}
				}
			}			
			elseif ((int)$mavatars_ids > 0)
			{
				$this->mavatars[1] = new mavatar_object($mavatars_ids);
				return true;
			}
			else
			{
				$mavs = $db->query("SELECT * FROM $db_mavatars WHERE mav_extension ='".$db->prep($this->extension)."' AND
				 mav_code = '".$db->prep($this->code)."' ORDER BY mav_order ASC, mav_item ASC")->fetchAll();
			}

			$i = 0;
			foreach ($mavs as $mav_row)
			{
				$i++;
				$this->mavatars[$i] = new mavatar_object($mav_row);
			}
			return true;
		}
		return false;
	}

	public function select_mavatar($id)
	{
		global $db, $db_mavatars;
		foreach ($this->mavatars as $mavatar)
		{
			if ($mavatar->id == $id)
			{
				return $mavatar;
			}
		}
		return new mavatar_object($id);
	}

	public function delete_mavatar($mavatar)
	{
		global $db, $db_mavatars;
		if (!is_object($mavatar))
		{
			$mavatar = new mavatar_object($mavatar);
		}
		$db->delete($db_mavatars, "mav_id=".$mavatar->id);
		$this->delete_files($mavatar);

		$key = array_search($mavatar, $this->mavatars);
		if ($key !== FALSE)
		{
			unset($this->mavatars[$key]);
		}
	}

	public function delete_all_mavatars()
	{
		foreach ($this->mavatars as $mavatar)
		{
			$this->delete_mavatar($mavatar);
		}
	}

	public function delete_files($mavatar, $onlythumbs = false)
	{
		foreach ($mavatar->files(!$onlythumbs, true) as $key => $file)
		{
			if (file_exists($file) && is_writable($file))
			{
				@unlink($file);
			}
		}
	}

	public function tags()
	{
		return $this->mavatars;
	}

	public function upload_form()
	{
		global $cfg, $L;
		$mskin = cot_tplfile(array('mavatars', 'form', $this->extension, $this->category, $this->code), 'plug');
		$t = new XTemplate($mskin);
		
		$mavatars_count = 0;
		foreach ($this->mavatars as $key => $mavatar)
		{
			if(file_exists($mavatar->file_path()))
			{
				$t->assign('MAVATAR', $mavatar);
				$t->assign('MAVATAR_NUM', $key);
				$t->assign($mavatar->edittags());
				$t->parse("MAIN.FILES.ROW");
				$mavatars_count++;
			}
			else
			{
				$this->delete_mavatar($mavatar);
			}
		}
		if ($mavatars_count > 0)
		{
			$t->parse("MAIN.FILES");
		}

		if ($cfg['jquery'])
		{
			$t->assign("FILEUPLOAD_URL", cot_url('plug', 'r=mavatars&m=upload&ext='.$this->extension.'&cat='.$this->category.'&code='.$this->code.'&'.cot_xg(), '', true));
			$t->assign("FILEUPLOAD_URL_NOX", cot_url('plug', 'r=mavatars&m=upload&ext='.$this->extension.'&cat='.$this->category.'&code='.$this->code, '', true));
			$t->parse("MAIN.AJAXUPLOAD");
		}
		else
		{
			$t->parse("MAIN.UPLOAD");
		}
		$t->parse("MAIN");
		return $t->text("MAIN");
	}

	function file_upload($file_object)
	{
		global $cfg, $L;

		list($file_name, $file_extension) = $this->file_info($file_object['name']);

		if (!empty($file_name) && !in_array($file_extension, $this->suppressed_ext) && (in_array($file_extension, $this->allowed_ext) || empty($this->allowed_ext)))
		{
			$safe_name = $this->safename($this->filepath, $file_name, $file_extension);
			$file_fullname = $this->file_path($this->filepath, $safe_name, $file_extension);
			if ($this->file_check($file_object['tmp_name'], $file_extension) || !$cfg['plugin']['mavatars']['filecheck'])
			{
				if (move_uploaded_file($file_object['tmp_name'], $file_fullname))
				{
					return array(
						'fullname' => $file_fullname,
						'extension' => $file_extension,
						'size' => $file_object['size'],
						'path' => $this->filepath,
						'name' => $safe_name,
						'origname' => $file_name
					);
				}
			}		
			return false;
		}
		return false;
	}

	/// стоп
	function mavatar_add($file, $desc = '', $order = 0, $type = '')
	{
		global $db, $db_mavatars, $sys, $cot_extrafields, $usr;
		$mavarray = array(
			'mav_userid' => $usr['id'],
			'mav_extension' => $this->extension,
			'mav_category' => $this->category,
			'mav_code' => $this->code,
			'mav_item' => $this->item,
			'mav_filepath' => $file['path'],
			'mav_filename' => $file['name'],
			'mav_fileext' => $file['extension'],
			'mav_fileorigname' => $file['origname'],
			'mav_thumbpath' => $this->thumbpath,
			'mav_filesize' => $file['size'],
			'mav_desc' => empty($desc) ? $file['origname'] : $desc,
			'mav_order' => $order,
			'mav_date' => $sys['now'],
			'mav_type' => $type,
		);

		$db->insert($db_mavatars, $mavarray);
		$mavarray['mav_id'] = $db->lastInsertId();
		return new mavatar_object($mavarray);
	}

	function ajax_upload($input_name = 'mavatar_file')
	{

		global $db, $db_mavatars, $L;
		$order = count($this->mavatars);
		$uploadErrors = array();
		$files_array = $this->filedata_to_array($input_name);
		if(empty($files_array[0]))
		{
			$uploadErrors[] = $L['mavatar_error_upload'];
		}
		$file = $this->file_upload($files_array[0]);
		if(!$file)
		{
			$uploadErrors[] = $L['mavatar_error_mimetype'];
		}
		if ($file && empty($uploadErrors))
		{
			$order++;
			$mavatar = $this->mavatar_add($file, '', $order);

		
			$mskin = cot_tplfile(array('mavatars', 'form', $this->extension, $this->category, $this->code), 'plug');
			$t = new XTemplate($mskin);

			$t->assign('MAVATAR', $mavatar);
			$t->assign('MAVATAR_NUM', $key);
			$t->assign($mavatar->edittags());
			$t->parse("MAIN.FILES.ROW");
			// код выполняется для посторения формы если нет маватаров

			$data['mavatar'] = $mavatar;
			if (count($this->mavatars))
			{
				$data['form'] = htmlspecialchars($t->text("MAIN.FILES.ROW"));
			}
			else
			{
				$t->parse("MAIN.FILES");
				$data['form'] = htmlspecialchars($t->text("MAIN.FILES"));
			}
			$data['success'] = 1;
		}
		else
		{
			$data['error'] = implode(',' , $uploadErrors);
		}
		return $data;
	}

// тттттт
	function upload($input_name = 'mavatar_file')
	{

		global $db, $db_mavatars, $cfg;

		if ($cfg['plugin']['mavatars']['turnajax'])
		{
			return false;
		}

		$order = count($this->mavatars);
		$files_array = $this->filedata_to_array($input_name);

		foreach ($files_array as $key => $file_object)
		{
			$file = $this->file_upload($file_object);
			if ($file)
			{
				$order++;
				$this->mavatar_add($file, '', $order);
			}
		}
		//
	}

	function update()
	{
		global $db, $db_mavatars, $sys, $cot_extrafields;

		if ($this->code != 'new')
		{

			$mavatars['mav_enabled'] = cot_import('mavatar_enabled', 'P', 'ARR');
			$mavatars['mav_order'] = cot_import('mavatar_order', 'P', 'ARR');
			$mavatars['mav_desc'] = cot_import('mavatar_desc', 'P', 'ARR');
			$mavatars['mav_text'] = cot_import('mavatar_text', 'P', 'ARR');

			$mavatars['mav_enabled'] = (count($mavatars['mav_enabled']) > 0) ? $mavatars['mav_enabled'] : array();

			foreach ($cot_extrafields[$db_mavatars] as $exfld)
			{
				if ($exfld['field_type'] != 'file' || $exfld['field_type'] != 'filesize')
				{
					$mavatars['mav_'.$exfld['field_name']] = cot_import('mavatar_'.$exfld['field_name'], 'P', 'ARR');
				}
			}

			foreach ($mavatars['mav_enabled'] as $id => $enabled)
			{
				$mavatar_info = $this->select_mavatar($id);
				$mavatar = array();
				$enabled = cot_import($enabled, 'D', 'BOL') ? true : false;
				$mavatar['mav_order'] = cot_import($mavatars['mav_order'][$id], 'D', 'INT');
				$mavatar['mav_desc'] = cot_import($mavatars['mav_desc'][$id], 'D', 'TXT');
				$mavatar['mav_text'] = cot_import($mavatars['mav_text'][$id], 'D', 'TXT');

				foreach ($cot_extrafields[$db_mavatars] as $exfld)
				{
					$mavatar['mav_'.$exfld['field_name']] = cot_import_extrafields($mavatars['mav_'.$exfld['field_name']][$id], $exfld, 'D', $mavatar_info->$exfld['field_name']);
				}

				if ($enabled)
				{
					if($mavatar_info->code == 'new')
					{
						$mavatar['mav_extension'] = $this->extension;
						$mavatar['mav_category'] = $this->category;
						$mavatar['mav_code'] = $this->code;
					}
					$mavatar['mav_filename'] = $this->rename_file($mavatar_info, $mavatar['mav_desc']);
					//cot_watch($mavatar, $mavatar_info);
					$mavatar['mav_date'] = $sys['now'];
					$db->update($db_mavatars, $mavatar, 'mav_id='.(int)$id);
				}
				else
				{
					
					$this->delete_mavatar($mavatar_info);
				}
			}
			$this->get_mavatars();
		}
	}

	private function filedata_to_array($input_name = 'mavatar_file')
	{
		$files_array = array();
		if (is_array($_FILES[$input_name]['name']))
		{
			foreach ($_FILES[$input_name]['name'] as $key => $val)
			{
				$files_array[$key]['name'] = $_FILES[$input_name]['name'][$key];
				$files_array[$key]['tmp_name'] = $_FILES[$input_name]['tmp_name'][$key];
				$files_array[$key]['size'] = $_FILES[$input_name]['size'][$key];
				$files_array[$key]['error'] = $_FILES[$input_name]['error'][$key];
			}
		}
		else
		{
			$files_array[0] = $_FILES[$input_name];
		}

		return $files_array;
	}

	/**
	 * Strips all unsafe characters from file base name and converts it to latin
	 *
	 * @param string $directory File path
	 * @param string $name File base name
	 * @param string $ext File extension
	 * @return string
	 */
	function safename($directory, $name, $ext)
	{
		global $lang, $cot_translit, $sys;
		if (!$cot_translit && $lang != 'en' && file_exists(cot_langfile('translit', 'core')))
		{
			require_once cot_langfile('translit', 'core');
		}

		if ($lang != 'en' && is_array($cot_translit))
		{
			$name = strtr($name, $cot_translit);
		}

		$name = str_replace(' ', '_', $name);
		$name = preg_replace('#[^a-zA-Z0-9\-_\.\ \+]#', '', $name);
		$name = str_replace('..', '.', $name);
		$name = mb_substr($name, 0, 200);
		$name = mb_strtolower($name);
		
		if (empty($name))
		{
			$name = cot_unique();
		}
		if (file_exists($this->file_path($directory, $name, $ext)))
		{
			$name .= "_".cot_date('ymd_His', $sys['now']);
		}
		if (file_exists($this->file_path($directory, $name, $ext)))
		{
			$name .= "_".rand(1, 999);
		}
		return $name;
	}

	/**
	 * Checks a file to be sure it is valid
	 *
	 * @param string $file File path
	 * @param string $ext File extension
	 * @return bool
	 */
	function file_check($file, $ext)
	{
		global $L, $cfg, $mime_type;
		require './datas/mimetype.php';
		$fcheck = FALSE;
		$images_types = array(
			'jpg' => IMAGETYPE_JPEG,
			'jpeg' =>IMAGETYPE_JPEG,
			'png' => IMAGETYPE_PNG,
			'gif' => IMAGETYPE_GIF);
		
		if (isset($images_types[$ext]))
		{
			$fcheck = (exif_imagetype($file) == $images_types[$ext]);
		}
		else
		{
			if (!empty($mime_type[$ext]))
			{
				foreach ($mime_type[$ext] as $mime)
				{
					$content = file_get_contents($file, 0, NULL, $mime[3], $mime[4]);
					$content = ($mime[2]) ? bin2hex($content) : $content;
					$mime[1] = ($mime[2]) ? strtolower($mime[1]) : $mime[1];
					$i++;
					if ($content == $mime[1])
					{
						$fcheck = TRUE;
						break;
					}
				}
			}
		}
		return($fcheck);
	}

	private function rename_file($object, $newname)
	{

		if ($newname != $object->desc && !empty($newname))
		{
			$newfilename = $this->safename($object->filepath, $newname, $object->fileext);
			
			$newpath = $this->file_path($object->filepath, $newfilename, $object->fileext);
			$oldpath = $this->file_path($object->filepath, $object->filename, $object->fileext);
			$this->delete_files($object, true);
			if (rename($oldpath, $newpath))
			{
				return $newfilename;
			}	
		}
		return $object->filename;
	}

	private function file_info($file)
	{
		$path_parts = pathinfo($file);
		$name = $path_parts['filename'];
		$extension = mb_strtolower($path_parts['extension']);
		return array($name, $extension);
	}

	private function file_path($dir, $file, $ext)
	{
		$dir = $this->fix_path($dir);
		return $dir.$file.'.'.$ext;
	}

	private function fix_path($path, $default = '')
	{
		$path = (!empty($path)) ? $path : $default;
		$path .= (substr($path, -1) == '/') ? '' : '/';
		return $path;
	}

	public function filter($param, $value)
	{
		$array = array();
		$param = mb_strtolower($param);
		$key = 0;
		foreach ($this->mavatars as $mavatar)
		{
			if ($mavatar->$param == $value && $mavatar->id)
			{
				$key++;
				$array[$key] = $mavatar;
			}
		}
		return $array;
	}

	public function extfilter($extensions, $inarray = true)
	{
		$array = array();
		$extensions = mb_strtolower($extensions);
		$extensions = array_map('trim', explode(',', $extensions));
		$key = 0;
		foreach ($this->mavatars as $mavatar)
		{
			if (((in_array($mavatar->fileext, $extensions) && $inarray) ||
				(!in_array($mavatar->fileext, $extensions) && !$inarray)) && $mavatar->id)
			{
				$key++;
				$array[$key] = $mavatar;
			}
		}
		return $array;
	}

	public function extfilter_images()
	{
		$extensions = "jpg,png,gif,jpeg,bmp";
		return $this->extfilter($extensions, true);
	}

	public function extfilter_notimages()
	{
		$extensions = "jpg,png,gif,jpeg,bmp";
		return $this->extfilter($extensions, false);
	}

}
