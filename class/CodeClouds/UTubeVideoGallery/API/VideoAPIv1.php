<?php

namespace CodeClouds\UTubeVideoGallery\API;

use CodeClouds\UTubeVideoGallery\API\APIv1;
use WP_REST_Request;
use WP_REST_Server;
use stdClass;
use utvAdminGen;





//thumbnail
////load
////save


class VideoAPIv1 extends APIv1
{
  public function __construct()
  {
    add_action('rest_api_init', [$this, 'registerRoutes']);
  }

  public function registerRoutes()
  {
    //get all videos in album endpoint
    register_rest_route(
      $this->_namespace . '/' . $this->_version,
      'albums/(?P<albumID>\d+)/videos',
      [
        'methods' => WP_REST_Server::READABLE,
        'callback' => [$this, 'getAllItems'],
        'args' => [
          'galleryID',
          'albumID'
        ]
      ]
    );

    //create new video endpoint
    register_rest_route(
      $this->_namespace . '/' . $this->_version,
      'videos',
      [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => [$this, 'createItem'],
        'permission_callback' => function() {
          return current_user_can('edit_others_posts');
        }
      ]
    );

    //get, update, delete video endpoints
    register_rest_route(
      $this->_namespace . '/' . $this->_version,
      'videos/(?P<videoID>\d+)',
      [
        [
          'methods' => WP_REST_Server::READABLE,
          'callback' => [$this, 'getItem'],
          'args' => [
            'videoID'
          ],
          'permission_callback' => function() {
            return true;
            //return current_user_can('edit_others_posts');
          }
        ],
        [
          'methods' => WP_REST_Server::DELETABLE,
          'callback' => [$this, 'deleteItem'],
          'args' => [
            'videoID'
          ],
          'permission_callback' => function() {
            return true;
            //return current_user_can('edit_others_posts');
          }
        ],
        [
          'methods' => 'PATCH',
          'callback' => [$this, 'updateItem'],
          'args' => [
            'videoID'
          ],
          'permission_callback' => function() {
            return true;
            //return current_user_can('edit_others_posts');
          }
        ]
      ]
    );
  }

  public function getItem(WP_REST_Request $req)
  {
    $videoData = new stdClass();
    $thumbnailDirectory = wp_upload_dir()['baseurl'];

    global $wpdb;
    $video = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'utubevideo_video WHERE VID_ID = ' . $req['videoID']);

    if (!$video)
      return $this->errorResponse('The specified video resource was not found');

    $video = $video[0];
    $videoData->id = $video->VID_ID;
    $videoData->title = $video->VID_NAME;
    $videoData->source = $video->VID_SOURCE;
    $videoData->thumbnail = $thumbnailDirectory . '/utubevideo-cache/' . $video->VID_URL . $video->VID_ID . '.jpg';
    $videoData->url = $video->VID_URL;
    $videoData->quality = $video->VID_QUALITY;
    $videoData->showChrome = $video->VID_CHROME;
    $videoData->startTime = $video->VID_STARTTIME;
    $videoData->endTime = $video->VID_ENDTIME;
    $videoData->position = $video->VID_POS;
    $videoData->published = $video->VID_PUBLISH;
    $videoData->updateDate = $video->VID_UPDATEDATE;
    $videoData->albumID = $video->ALB_ID;

    return $this->response($videoData);
  }

  public function createItem(WP_REST_Request $req)
  {
    //require helper classes
    require_once(dirname(__FILE__) . '/../../../utvAdminGen.php');
    utvAdminGen::initialize([]);

    global $wpdb;

    //gather data fields
    $urlKey = sanitize_text_field($req['urlKey']);
    $title = sanitize_text_field($req['title']);
    $quality = sanitize_text_field($req['quality']);
    $controls = ($req['controls'] ? 0 : 1);
    $startTime = sanitize_text_field($req['startTime']);
    $endTime = sanitize_text_field($req['endTime']);
    $source = sanitize_text_field($req['source']);
    $albumID = sanitize_key($req['albumID']);
    $time = current_time('timestamp');

    //check for required fields
    if (empty($urlKey) || empty($quality) || !isset($controls) || empty($source) || !isset($albumID))
      return $this->errorResponse('Invalid parameters');

    //get current video count for album
    $videoCount = $wpdb->get_results('SELECT COUNT(VID_ID) AS VID_COUNT FROM ' . $wpdb->prefix . 'utubevideo_video WHERE ALB_ID = ' . $albumID)[0];
    $gallery = $wpdb->get_results('SELECT DATA_THUMBTYPE FROM ' . $wpdb->prefix . 'utubevideo_album a INNER JOIN ' . $wpdb->prefix . 'utubevideo_dataset d ON a.DATA_ID = d.DATA_ID WHERE a.ALB_ID = ' . $albumID)[0];
    $nextSortPos = $videoCount->VID_COUNT;

    //get source thumbnail
    if ($source == 'youtube')
      $sourceThumbnail = 'http://img.youtube.com/vi/' . $urlKey . '/0.jpg';
    elseif ($source == 'vimeo')
    {
      $data = utvAdminGen::queryAPI('https://vimeo.com/api/v2/video/' . $urlKey . '.json');
      $sourceThumbnail = $data[0]['thumbnail_large'];
    }

    //insert new video
    if ($wpdb->insert(
      $wpdb->prefix . 'utubevideo_video',
      [
        'VID_SOURCE' => $source,
        'VID_NAME' => $title,
        'VID_URL' => $urlKey,
        'VID_THUMBTYPE' => $gallery->DATA_THUMBTYPE,
        'VID_QUALITY' => $quality,
        'VID_CHROME' => $controls,
        'VID_STARTTIME' => $startTime,
        'VID_ENDTIME' => $endTime,
        'VID_POS' => $nextSortPos,
        'VID_UPDATEDATE' => $time,
        'ALB_ID' => $albumID
      ]
    ))
    {
      //get last insert id and save thumbnail
      $videoID = $wpdb->insert_id;

      if (!utvAdminGen::saveThumbnail($sourceThumbnail, $urlKey . $videoID, $gallery->DATA_THUMBTYPE))
      {
        $wpdb->query('DELETE FROM ' . $wpdb->prefix . 'utubevideo_video WHERE VID_ID ="' . $videoID . '"');
        return $this->errorResponse('Video thumbnail save error');
      }

      return $this->response(null, 201);
    }
    else
      return $this->errorResponse('A database error has occurred');
  }

  public function deleteItem(WP_REST_Request $req)
  {










  }

  public function updateItem(WP_REST_Request $req)
  {
    //require helper classes
    require_once(dirname(__FILE__) . '/../../../utvAdminGen.php');
    utvAdminGen::initialize([]);

    global $wpdb;

    //check for valid videoID
    if (!$req['videoID'])
      return $this->errorResponse('Invalid Video ID');

    //gather data fields
    $title = sanitize_text_field($req['title']);
    $quality = sanitize_text_field($req['quality']);
    $controls = ($req['controls'] ? 0 : 1);
    $startTime = sanitize_text_field($req['startTime']);
    $endTime = sanitize_text_field($req['endTime']);
    $albumID = sanitize_key($req['albumID']);
    $updateDate = current_time('timestamp');

    //get thumbnail type
    if ($albumID != null)
    {
      //get by new album id
      $thumbnailType = $wpdb->get_results(
        'SELECT d.DATA_THUMBTYPE AS THUMBNAIL_TYPE FROM '
        . $wpdb->prefix . 'utubevideo_album a INNER JOIN '
        . $wpdb->prefix . 'utubevideo_dataset d ON a.DATA_ID = d.DATA_ID '
        . 'WHERE a.ALB_ID = ' . $albumID
      );
    }
    else
    {
      //get by current album id
      $thumbnailType = $wpdb->get_results(
        'SELECT d.DATA_THUMBTYPE AS THUMBNAIL_TYPE FROM '
        . $wpdb->prefix . 'utubevideo_video v INNER JOIN '
        . $wpdb->prefix . 'utubevideo_album a ON v.ALB_ID = a.ALB_ID INNER JOIN '
        . $wpdb->prefix . 'utubevideo_dataset d ON a.DATA_ID = d.DATA_ID '
        . 'WHERE v.VID_ID = ' . $req['videoID']
      );
    }

    if (!isset($thumbnailType[0]->THUMBNAIL_TYPE))
      return $this->errorResponse('Cannot determine thumbnail type');

    $thumbnailType = $thumbnailType[0]->THUMBNAIL_TYPE;

    //get video source
    $thumbnailData = $wpdb->get_results(
      'SELECT VID_ID, VID_SOURCE, VID_URL FROM '
      . $wpdb->prefix . 'utubevideo_video '
      . 'WHERE VID_ID = ' . $req['videoID']
    );

    if (!$thumbnailData)
      return $this->errorResponse('Cannot determine video source data');

    $thumbnailData = $thumbnailData[0];

    //regenerate video thumbnail
    if ($thumbnailData->VID_SOURCE == 'youtube')
      $sourceURL = 'http://img.youtube.com/vi/' . $thumbnailData->VID_URL . '/0.jpg';
    elseif ($thumbnailData->VID_SOURCE == 'vimeo')
    {
      $data = utvAdminGen::queryAPI('https://vimeo.com/api/v2/video/' . $thumbnailData->VID_URL . '.json')[0];
      $sourceURL = $data['thumbnail_large'];
    }

    if (!utvAdminGen::saveThumbnail($sourceURL, $thumbnailData->VID_URL . $thumbnailData->VID_ID, $thumbnailType))
      return $this->errorResponse('Video thumbnail refresh failed');

    //create updatedFields array
    $updatedFields = [];

    //set optional update fields
    if ($title != null)
      $updatedFields['VID_NAME'] = $title;

    if ($quality != null)
      $updatedFields['VID_QUALITY'] = $quality;

    if ($controls != null)
      $updatedFields['VID_CHROME'] = $controls;

    if ($startTime != null)
      $updatedFields['VID_STARTTIME'] = $startTime;

    if ($endTime != null)
      $updatedFields['VID_ENDTIME'] = $endTime;

    if ($albumID != null)
      $updatedFields['ALB_ID'] = $albumID;

    //set required update fields
    $updatedFields['VID_UPDATEDATE'] = $updateDate;
    $updatedFields['VID_THUMBTYPE'] = $thumbnailType;

    //update database entry
    if ($wpdb->update(
      $wpdb->prefix . 'utubevideo_video',
      $updatedFields,
      ['VID_ID' => $req['videoID']]
    ) >= 0)
      return $this->response(null);
    else
      return $this->errorResponse('A database error has occurred');
  }

  public function getAllItems(WP_REST_Request $req)
  {
    $data = [];
    $thumbnailDirectory = wp_upload_dir()['baseurl'];
    global $wpdb;

    $videos = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'utubevideo_video WHERE ALB_ID = ' . $req['albumID'] . ' ORDER BY VID_POS');

    foreach ($videos as $video)
    {
      $videoData = new stdClass();
      $videoData->id = $video->VID_ID;
      $videoData->title = $video->VID_NAME;
      $videoData->source = $video->VID_SOURCE;
      $videoData->thumbnail = $thumbnailDirectory . '/utubevideo-cache/' . $video->VID_URL . $video->VID_ID . '.jpg';
      $videoData->url = $video->VID_URL;
      $videoData->quality = $video->VID_QUALITY;
      $videoData->showChrome = $video->VID_CHROME;
      $videoData->startTime = $video->VID_STARTTIME;
      $videoData->endTime = $video->VID_ENDTIME;
      $videoData->position = $video->VID_POS;
      $videoData->published = $video->VID_PUBLISH;
      $videoData->updateDate = $video->VID_UPDATEDATE;
      $videoData->albumID = $video->ALB_ID;

      $data[] = $videoData;
    }

    return $this->response($data);
  }
}
