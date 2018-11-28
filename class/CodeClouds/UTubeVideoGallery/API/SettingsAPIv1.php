<?php

namespace CodeClouds\UTubeVideoGallery\API;

use CodeClouds\UTubeVideoGallery\API\APIv1;
use WP_REST_Request;
use WP_REST_Server;
use stdClass;

class SettingsAPIv1 extends APIv1
{
  public function __construct()
  {
    add_action('rest_api_init', [$this, 'registerRoutes']);
  }

  public function registerRoutes()
  {
    //get, update settings endpoints
    register_rest_route(
      $this->_namespace . '/' . $this->_version,
      'settings',
      [
        [
          'methods' => WP_REST_Server::READABLE,
          'callback' => [$this, 'getItem'],
          'permission_callback' => function()
          {
            return current_user_can('edit_others_posts');
          }
        ],
        [
          'methods' => 'PATCH',
          'callback' => [$this, 'updateItem'],
          'permission_callback' => function()
          {
            return current_user_can('edit_others_posts');
          }
        ]
      ]
    );
  }

  public function getItem(WP_REST_Request $req)
  {
    $pluginSettings = get_option('utubevideo_main_opts');

    $settingData = new stdClass();
    $settingData->version = $pluginSettings['version'];
    $settingData->popupPlayerWidth = $pluginSettings['playerWidth'];
    $settingData->playerControlsTheme = $pluginSettings['playerControlTheme'];
    $settingData->playerControlsColor = $pluginSettings['playerProgressColor'];
    $settingData->popupPlayerOverlayOpacity = $pluginSettings['fancyboxOverlayOpacity'];
    $settingData->popupPlayerOverlayColor = $pluginSettings['fancyboxOverlayColor'];
    $settingData->thumbnailWidth = $pluginSettings['thumbnailWidth'];
    $settingData->thumbnailVerticalPadding = $pluginSettings['thumbnailVerticalPadding'];
    $settingData->thumbnailHorizontalPadding = $pluginSettings['thumbnailPadding'];
    $settingData->thumbnailBorderRadius = $pluginSettings['thumbnailBorderRadius'];
    $settingData->youtubeAPIKey = $pluginSettings['youtubeApiKey'];
    $settingData->youtubeAutoplay = $pluginSettings['youtubeAutoplay'] ? true : false;
    $settingData->youtubeHideDetails = $pluginSettings['youtubeDetailsHide'] ? true : false;
    $settingData->vimeoAutoplay = $pluginSettings['vimeoAutoplay'] ? true : false;
    $settingData->vimeoHideDetails = $pluginSettings['vimeoDetailsHide'] ? true : false;
    $settingData->removeVideoPopupScript = $pluginSettings['skipMagnificPopup'] == 'yes' ? true : false;

    //get php version
    preg_match('/^(.*?)-(.*)/', PHP_VERSION, $matches);
    $settingData->phpVersion = isset($matches[1]) ? $matches[1] : '';

    //get gd status
    $settingData->gdEnabled = extension_loaded('gd');

    //get imagemagick status
    $settingData->imageMagickEnabled = extension_loaded('imagick');

    return $this->response($settingData);
  }

  public function updateItem(WP_REST_Request $req)
  {
    $pluginSettings = get_option('utubevideo_main_opts');

    //gather data fields
    $playerControlsColor = sanitize_text_field($req['playerControlsColor']);
    $playerControlsTheme = sanitize_text_field($req['playerControlsTheme']);
    $popupPlayerWidth = sanitize_text_field($req['popupPlayerWidth']);
    $popupPlayerOverlayColor = sanitize_text_field($req['popupPlayerOverlayColor']);
    $popupPlayerOverlayOpacity = sanitize_text_field($req['popupPlayerOverlayOpacity']);
    $thumbnailBorderRadius = sanitize_text_field($req['thumbnailBorderRadius']);
    $thumbnailWidth = sanitize_text_field($req['thumbnailWidth']);
    $thumbnailHorizontalPadding = sanitize_text_field($req['thumbnailHorizontalPadding']);
    $thumbnailVerticalPadding = sanitize_text_field($req['thumbnailVerticalPadding']);
    $youtubeAPIKey = sanitize_text_field($req['youtubeAPIKey']);

    if (isset($req['removeVideoPopupScript']))
      $removeVideoPopupScript = $req['removeVideoPopupScript'] ? 'yes' : 'no';
    else
      $removeVideoPopupScript = null;

    if (isset($req['vimeoAutoplay']))
      $vimeoAutoplay = $req['vimeoAutoplay'] ? 1 : 0;
    else
      $vimeoAutoplay = null;

    if (isset($req['vimeoHideDetails']))
      $vimeoHideDetails = $req['vimeoHideDetails'] ? 1 : 0;
    else
      $vimeoHideDetails = null;

    if (isset($req['youtubeAutoplay']))
      $youtubeAutoplay = $req['youtubeAutoplay'] ? 1 : 0;
    else
      $youtubeAutoplay = null;

    if (isset($req['youtubeHideDetails']))
      $youtubeHideDetails = $req['youtubeHideDetails'] ? 1 : 0;
    else
      $youtubeHideDetails = null;

    //set optional update fields
    if ($playerControlsColor)
      $pluginSettings['playerProgressColor'] = $playerControlsColor;

    if ($playerControlsTheme)
      $pluginSettings['playerControlTheme'] = $playerControlsTheme;

    if ($popupPlayerWidth)
      $pluginSettings['playerWidth'] = $popupPlayerWidth;

    if ($popupPlayerOverlayColor)
      $pluginSettings['fancyboxOverlayColor'] = $popupPlayerOverlayColor;

    if ($popupPlayerOverlayOpacity)
      $pluginSettings['fancyboxOverlayOpacity'] = $popupPlayerOverlayOpacity;

    if ($thumbnailBorderRadius)
      $pluginSettings['thumbnailBorderRadius'] = $thumbnailBorderRadius;

    if ($thumbnailWidth)
      $pluginSettings['thumbnailWidth'] = $thumbnailWidth;

    if ($thumbnailHorizontalPadding)
      $pluginSettings['thumbnailPadding'] = $thumbnailHorizontalPadding;

    if ($thumbnailVerticalPadding)
      $pluginSettings['thumbnailVerticalPadding'] = $thumbnailVerticalPadding;

    if ($youtubeAPIKey != null)
      $pluginSettings['youtubeApiKey'] = $youtubeAPIKey;

    if ($removeVideoPopupScript)
      $pluginSettings['skipMagnificPopup'] = $removeVideoPopupScript;

    if ($vimeoAutoplay)
      $pluginSettings['vimeoAutoplay'] = $vimeoAutoplay;

    if ($vimeoHideDetails)
      $pluginSettings['vimeoDetailsHide'] = $vimeoHideDetails;

    if ($youtubeAutoplay)
      $pluginSettings['youtubeAutoplay'] = $youtubeAutoplay;

    if ($youtubeHideDetails)
      $pluginSettings['youtubeDetailsHide'] = $youtubeHideDetails;

    update_option('utubevideo_main_opts', $pluginSettings);

    return $this->response(null);
  }
}