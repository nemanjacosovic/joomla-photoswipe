<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/Plugins/JoomPhotoswipe/trunk/joomphotoswipe.php $
// $Id: joomphotoswipe.php 4204 2013-04-16 20:11:54Z erftralle $
/******************************************************************************\
**   JoomGallery Plugin 'Integrate Photoswipe'                                **
**   By: JoomGallery::ProjectTeam                                             **
**   Copyright (C) 2013 JoomGallery::ProjectTeam                              **
**   Released under GNU GPL Public License                                    **
**   License: http://www.gnu.org/copyleft/gpl.html                            **
\******************************************************************************/

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_ADMINISTRATOR.'/components/com_joomgallery/helpers/openimageplugin.php';

/**
 * JoomGallery Plugin 'Integrate Photoswipe'
 *
 * With this plugin JoomGallery is able to use the Photoswipe Javascript
 * (http://http://www.photoswipe.com/) for displaying images.
 *
 * NOTE: Please remember that Photoswipe is licensed under the terms of the
 * MIT License.
 *
 * @package     JoomGallery
 * @since       3.0
 */
class plgJoomGalleryJoomPhotoswipe extends JoomOpenImagePlugin
{
  /**
   * Name of this popup box
   *
   * @var   string
   * @since 3.0
   */
  protected $title = 'Photoswipebox';

  /**
   * Joomgallery configuration
   *
   * @var     object
   */
  private $_jg_config = null;

  /**
   * Joomla document
   *
   * @var     object
   */
  private $_doc = null;

  /**
   * Flag, whether the browser is a mobile one
   *
   * @var     boolean
   */
  private $_isMobile = null;

  /**
   * Initializes the box by adding all necessary image group independent JavaScript and CSS files.
   * This is done only once per page load.
   *
   * @return  void
   * @since   3.0
   */
  protected function init()
  {
    $this->loadLanguage();

    $this->_doc       = JFactory::getDocument();
    $this->_jg_config = JoomConfig::getInstance();

    if(!$this->_isMobile)
    {
      // Load jQuery in 'noConflict' mode
      JHtml::_('jquery.framework');
    }

    // Add Photoswipe assets
    $this->_doc->addScript(JURI::root().'media/plg_joomgallery_joomphotoswipe/klass.min.js');
    if(!$this->_isMobile)
    {
      // Load jQuery version for all browsers
      $this->_doc->addScript(JURI::root().'media/plg_joomgallery_joomphotoswipe/code.photoswipe.jquery.min.js');
    }
    else
    {
      // Load optimized version for mobile devices
      $this->_doc->addScript(JURI::root().'media/plg_joomgallery_joomphotoswipe/code.photoswipe.min.js');
    }
    // Add style sheets
    $this->_doc->addStyleSheet(JURI::root().'media/plg_joomgallery_joomphotoswipe/photoswipe.css');
  }

  /**
   * OnJoomOpenImage method (override)
   *
   * Method is called when an image of JoomGallery shall be opened.
   * It modifies the given link in order to use the respective box for opening the image.
   *
   * @access  public
   * @param   string  $link     The link to modify
   * @param   object  $image    An object holding the image data
   * @param   string  $img_url  The URL to the image which shall be openend
   * @param   string  $group    The name of an image group, most boxes will make an album out of the images of a group
   * @param   string  $type     'orig' for original image, 'img' for detail image or 'thumb' for thumbnail
   * @param   string  $selected If a specific box was selected it will be delivered in this argument
   * @return  void
   * @since   3.0
   */
  public function onJoomOpenImage(&$link, $image = null, $img_url = null, $group = 'joomgallery', $type = 'orig', $selected = null)
  {
    if(!$image)
    {
      // Let JoomGallery know that this plugin is enabled (this is for backwards compatibility only)
      $link = true;

      return;
    }

    if($selected && $selected != $this->title)
    {
      // If an OpenImage plugin was selected but not this one we don't do anything here
      return;
    }

    if(is_null($this->_isMobile))
    {
      // Include class for mobile device detection instead of using JBrowser
      if(!class_exists('Mobile_Detect'))
      {
        require_once dirname(__FILE__).'/Mobile_Detect.php';
      }

      // Check for a mobile or tablet device
      $detect = new Mobile_Detect;
      $this->_isMobile = $detect->isMobile() || $detect->isTablet();
    }

    if(!$this->_isMobile && $this->params->get('cfg_openimage') != $this->title)
    {
      $link = JHtml::_('joomgallery.openimage', $this->params->get('cfg_openimage'), $image, $type, $group);

      return;
    }

    parent::onJoomOpenImage($link, $image, $img_url, $group, $type, $selected);
  }

  /**
   * This method sets an associative array of attributes for the 'a'-Tag (key/value pairs)
   * which opens the image and adds some image group specific JavaScript code fot the Colorbox.
   *
   * @param   array   $attribs  Associative array of HTML attributes which you have to fill
   * @param   object  $image    An object holding all the relevant data about the image to open
   * @param   string  $img_url  The URL to the image which shall be openend
   * @param   string  $group    The name of an image group, most popup boxes are able to group the images with that
   * @param   string  $type     'orig' for original image, 'img' for detail image or 'thumb' for thumbnail
   * @return  void
   * @since   3.0
   */
  protected function getLinkAttributes(&$attribs, $image, $img_url, $group, $type)
  {
    static $initGroup = array();

    if(!isset($initGroup[$group]))
    {
      // Prepare options
      $preventSlideshow                  = $this->params->get('cfg_operationmode') ? 'false' : 'true';
      $loop                              = $this->params->get('cfg_loop') ? 'true' : 'false';
      $autoStartSlideshow                = $this->params->get('cfg_slideautostart') ? 'true' : 'false';
      $captionAndToolbarHide             = $this->params->get('cfg_captionandtoolbarhide') ? 'true' : 'false';
      $cfg_captionandtoolbarflipposition = $this->params->get('cfg_captionandtoolbarflipposition') ? 'true' : 'false';

      $options  = "                                        {\n";
      $options .= "                                          preventSlideshow: ".$preventSlideshow.",\n";
      $options .= "                                          loop: ".$loop.",\n";
      $options .= "                                          imageScaleMethod: '".$this->params->get('cfg_imagescalemethod')."',\n";
      $options .= "                                          slideSpeed: ".$this->params->get('cfg_slidespeed').",\n";
      $options .= "                                          nextPreviousSlideSpeed: ".$this->params->get('cfg_slidespeed').",\n";
      $options .= "                                          fadeInSpeed: ".$this->params->get('cfg_fadespeed').",\n";
      $options .= "                                          fadeOutSpeed: ".$this->params->get('cfg_fadespeed').",\n";
      $options .= "                                          zIndex: ".$this->params->get('cfg_zindex').",\n";
      $options .= "                                          captionAndToolbarHide: ".$captionAndToolbarHide.",\n";
      $options .= "                                          captionAndToolbarAutoHideDelay: ".$this->params->get('cfg_captionandtoolbarautohidedelay').",\n";
      $options .= "                                          captionAndToolbarFlipPosition: ".$cfg_captionandtoolbarflipposition.",\n";
      $options .= "                                          slideshowDelay: ".$this->params->get('cfg_slideinterval').",\n";
      $options .= "                                          autoStartSlideshow: ".$autoStartSlideshow.",\n";
      $options .= "                                          enableMouseWheel: true,\n";
      $options .= "                                          enableKeyboard: true,\n";
      $options .= "                                          getImageCaption: function(el) {\n";
      $options .= "                                            return el.getAttribute('data-title');\n";
      $options .= "                                          }\n";
      $options .= "                                        });\n";

      // Prepare events
      $events  = "      myPhotoSwipe_".$group.".addEventHandler(PhotoSwipe.EventTypes.onBeforeShow, function(e) {\n";
      $events .= "        joomphotoswipe_onkeydownsave = document.onkeydown;\n";
      $events .= "        window.document.onkeydown           = null;\n";
      $events .= "      });\n";
      $events .= "      myPhotoSwipe_".$group.".addEventHandler(PhotoSwipe.EventTypes.onBeforeHide, function(e) {\n";
      $events .= "        window.document.onkeydown = joomphotoswipe_onkeydownsave;\n";
      $events .= "      });\n";

      if(!$this->_isMobile)
      {
        $script  = "    (function(window, $, PhotoSwipe){\n";
        $script .= "      $(document).ready(function(){\n";
        $script .= "        var myPhotoSwipe_".$group." = $('a[rel^=".$this->title."-".$group."]').photoSwipe(\n";
        $script .= $options;
        $script .= $events;
        $script .= "      });\n";
        $script .= "    }(window, window.jQuery, window.Code.PhotoSwipe));\n";
      }
      else
      {
        $script  = "    (function(window, PhotoSwipe){\n";
        $script .= "      document.addEventListener('DOMContentLoaded', function() {\n";
        $script .= "        var myPhotoSwipe_".$group." = Code.PhotoSwipe.attach(window.document.querySelectorAll('a[rel^=".$this->title."-".$group."]'),\n";
        $script .= $options;
        $script .= $events;
        $script .= "      }, false);\n";
        $script .= "    }(window, window.Code.PhotoSwipe));\n";
      }
      $this->_doc->addScriptDeclaration($script);

      $initGroup[$group] = true;
    }

    // Prepare the caption
    $data_title = '';
    if($this->_jg_config->get('jg_show_title_in_popup'))
    {
      $data_title .= $image->imgtitle;
    }
    if($this->_jg_config->get('jg_show_description_in_popup') && !empty($image->imgtext))
    {
      if(!empty($data_title))
      {
        if(!$this->_isMobile)
        {
          $data_title .= ' : ';
        }
        else
        {
          $data_title .= ' :: ';
        }
      }
      $data_title .= htmlspecialchars(strip_tags($image->imgtext));
    }

    // Prepare the link attributes
    $attribs['rel']        = $this->title.'-'.$group;
    $attribs['data-title'] = $data_title;
  }
}