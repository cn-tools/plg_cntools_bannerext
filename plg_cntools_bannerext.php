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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class plgContentPlg_CNTools_BannerExt extends JPlugin
{
	var $_Count;
	var $_articleID;

	public function plgContentPlg_CNTools_BannerExt( &$subject, $config )
	{
		parent::__construct( $subject, $config );
		
		$this->_count = 0;
		$this->_articleID = '';
	}

	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		if (property_exists($article, 'text'))
		{
			$regex = '#{BannerExt}(\d+){/BannerExt}#is';
	
			if (property_exists($article, 'id'))
			{
				$this->_articleID = $article->id;
			} else {
				$this->_articleID = '_noArticleIdSet';
			}
			$article->text = preg_replace_callback($regex, array('plgContentPlg_CNTools_BannerExt', 'renderBannerExt'), $article->text, -1, $count );
		}
	}

	public function renderBannerExt(&$matches)
	{
		$lValue = htmlspecialchars_decode($matches[1]);
		$lResult = $this->doTransferCode($lValue);
		return $lResult;
	}
	
	protected function doTransferCode($bannerID) {
		/* in $bannerID comes the id of the used banner */
		$lResult = JText::_('PLG_CNTOOLS_BANNEREXT_NOBANNER_MSG');
		if ($lResult == 'PLG_CNTOOLS_BANNEREXT_NOBANNER_MSG') { $lResult =''; }
		
		if ($lResult!='')
		{
			$lResult = '<span class="bannerext'. $bannerID . ' bannerext' . $this->params->get('css') . '">' . $lResult . '</span>';
		}
		
		if ($bannerID!='')
		{
			include_once JPATH_ROOT . '/plugins/content/plg_cntools_bannerext/helper.php';

			JModelLegacy::addIncludePath(JPATH_ROOT . '/plugins/content/plg_cntools_bannerext', 'BannersModelExt');
			$model = JModelLegacy::getInstance('Banners', 'BannersModelExt', array('ignore_request' => true));
			$model->setState('banner.id', (int) $bannerID);

			$banners = $model->getItems();
			$model->impress();

			if ($banners)
			{
				$item = $banners[0];
				if ($item)
				{
					$lResult = $this->doDesignItem($item);
				}
			}
		}
		
		return $lResult;
	}
	
	protected function doDesignItem(&$item)
	{
		$lResult = '<' . $this->params->get('tag', 'span') . ' id="bannerext' . $this->_articleID . '_' . $this->_count . '" class="bannerext' . $item->id . ' bannerext' . $this->params->get('css') . '">';
		$baseurl = JUri::base();
		$link = JRoute::_('index.php?option=com_banners&task=click&id=' . $item->id);
		if ($item->type == 1)
		{
			// Text based banners 
			$lResult .= str_replace(array('{CLICKURL}', '{NAME}'), array($link, $item->name), $item->custombannercode);
		}
		else
		{
			$imageurl = $item->params->get('imageurl');
			$width = $item->params->get('width');
			$height = $item->params->get('height');
			require_once JPATH_ROOT . '/components/com_banners/helpers/banner.php';

			if (BannerHelper::isImage($imageurl))
			{
				// Image based banner 
				$alt = $item->params->get('alt');
				$alt = $alt ? $alt : $item->name; 
				$alt = $alt ? $alt : JText::_('PLG_CNTOOLS_BANNEREXT_BANNER');
				if ($item->clickurl)
				{
					// Wrap the banner in a link
					$target = $this->params->get('target', 1);
					if ($target == 1)
					{
						// Open in a new window
						$lResult .= '<a  href="' . $link . '" target="_blank" title="' . htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8') . '">';
							$lResult .= '<img src="' . $baseurl . $imageurl . '" alt="' . $alt . '"';
							if (!empty($width)) $lResult .= ' width ="' . $width . '"';
							if (!empty($height)) $lResult .= ' height ="' . $height . '"';
							$lResult .= '/>';
						$lResult .= '</a>';
					}
					elseif ($target == 2)
					{
						// Open in a popup window
						$lResult .= '<a href="' . $link . '" onclick="window.open(this.href, "", "toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550"); return false" title="' . htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8') . '">';
							$lResult .= '<img src="' . $baseurl . $imageurl . '" alt="' . $alt . '"';
							if (!empty($width)) $lResult .= ' width ="' . $width . '"';
							if (!empty($height)) $lResult .= ' height ="' . $height . '"';
							$lResult .= '/>';
						$lResult .= '</a>';
					}
					else
					{
						// Open in parent window
						$lResult .= '<a href="' . $link . '" title="' . htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8') . '">';
							$lResult .= '<img src="' . $baseurl . $imageurl . '" alt="' . $alt . '"';
							if (!empty($width)) $lResult .= ' width ="' . $width . '"';
							if (!empty($height)) $lResult .= ' height ="' . $height . '"';
							$lResult .= '/>';
						$lResult .= '</a>';
					}
				}
				else
				{
					// Just display the image if no link specified
					$lResult .= '<img src="' . $baseurl . $imageurl . '" alt="' . $alt . '"';
						if (!empty($width)) $lResult .= ' width ="' . $width . '"';
						if (!empty($height)) $lResult .= ' height ="' . $height . '"';
					$lResult .= '/>';
				}
			}
			elseif (BannerHelper::isFlash($imageurl))
			{
				$lResult .= '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0"';
				if (!empty($width)) $lResult .= ' width ="' . $width . '"';
				if (!empty($height)) $lResult .= ' height ="' . $height . '"';
				$lResult .= '>';
					$lResult .= '<param name="movie" value="echo $imageurl;" />';
					$lResult .= '<embed src="echo $imageurl;" loop="false" pluginspage="http://www.macromedia.com/go/get/flashplayer" type="application/x-shockwave-flash"';
					if (!empty($width)) $lResult .= ' width ="' . $width . '"';
					if (!empty($height)) $lResult .= ' height ="' . $height . '"';
					$lResult .= '/>';
				$lResult .= '</object>';
			}
		}

		$lResult .= '</' . $this->params->get('tag', 'span') . '>';

		return $lResult;
	}
}

?>

