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

require_once cot_incfile('mavatars', 'plug', 'object.class');
require_once cot_incfile('mavatars', 'plug', 'class');
cot_extrafields_register_table('mavatars');

require_once cot_langfile('mavatars');

require_once cot_incfile('uploads');
require_once cot_incfile('forms');

function cot_mav_thumb($mavatar, $width, $height, $resize='crop', $filter='')
{
	if(!is_object($mavatar))
	{
		return false;
	}
	return $mavatar->check_thumb($width, $height, $resize, $filter);	
}