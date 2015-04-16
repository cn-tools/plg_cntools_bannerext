<?php
/**
 * plg_cntools_bannerext - Joomla Plugin
 *
 * @package    Joomla
 * @subpackage Plugin
 * @author Clemens Neubauer
 * @link https://github.com/cn-tools/
 * @license		GNU/GPL, see LICENSE.php
 * plg_cntools_bannerext is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('_JEXEC') or die;

require_once JPATH_ROOT . '/components/com_banners/models/banners.php';

class BannersModelExtBanners extends BannersModelBanners
{
	protected function getListQuery()
	{
		$query = parent::getListQuery();
		
		$id = $this->getState('banner.id');
		if ($id)
		{
			$query->where('a.id = ' . (int) $id);
		}

		return $query;
	}
}
