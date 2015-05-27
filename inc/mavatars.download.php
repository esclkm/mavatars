<?php

/**
 * mavatars for Cotonti CMF
 *
 * @version 1.00
 * @author	esclkm
 * @copyright (c) 2013 esclkm
 */
defined('COT_CODE') or die('Wrong URL');

$id = cot_import('id', 'G', 'INT');

$mavatar = new mavatar_object($id);

if(!$mavatar->id)
{
	cot_die_message('404', true);
	exit;
}
cot_check_xg();

$image = $mavatar->thumb($mavatars_tags[1], $width, $height, $resize, $filter, $quality);
if (ob_get_level()) {
   ob_end_clean();
 }
$file = $mavatar->file_path();
$db->query("UPDATE $db_mavatars SET mav_downloads=mav_downloads+1 WHERE mav_id=".$mavatar->id);
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . $mavatar->filename.'.'.$mavatar->fileext);
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file));
// читаем файл и отправляем его пользователю
if ($fd = fopen($file, 'rb')) {
  while (!feof($fd)) {
	print fread($fd, 1024);
  }
  fclose($fd);
}
exit;