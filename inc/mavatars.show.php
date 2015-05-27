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

$id = cot_import('id', 'G', 'INT');
$h = cot_import('h', 'G', 'INT');
$w = cot_import('w', 'G', 'INT');
$method = cot_import('method', 'G', 'TXT');

cot_block((int)$id > 0);

$h = empty($h) ? (int)$cfg['plugin']['mavatars']['height'] : (int)$h;
$w = empty($w) ? (int)$cfg['plugin']['mavatars']['width'] : (int)$w;
$method = empty($method) ? $cfg['plugin']['mavatars']['method'] : $method;

$h = empty($h) ? 640 : (int)$h;
$w = empty($w) ? 640 : (int)$w;
$method = empty($method) ? 'width' : $method;

$mavatar = new mavatar_object((int)$id);
$t = new XTemplate(cot_tplfile(array('mavatars', 'show'), 'plug'));
if ($mavatar->id)
{
	$out['subtitle'] = $mavatar->desc;
	$t->assign('MAVATAR',  $mavatar);
	$t->assign('IMG', $mavatar->thumb($w, $h, $method));
	
	/* === Hook === */
	foreach (cot_getextplugins('mavatars.show.tags') as $pl)
	{
		include $pl;
	}
	/* ===== */
	
}
else
{
	cot_die();
}

