<?php

namespace CodeClouds\UTubeVideoGallery\Repository;

use CodeClouds\UTubeVideoGallery\Entity\Video;

class VideoRepository
{
  static function getItem($videoID)
  {
    global $wpdb;

    if (!$videoID)
      return false;

    $query = $wpdb->prepare(
      'SELECT v.*, d.DATA_THUMBTYPE AS THUMBNAIL_TYPE FROM '
      . $wpdb->prefix . 'utubevideo_video v INNER JOIN '
      . $wpdb->prefix . 'utubevideo_album a ON v.ALB_ID = a.ALB_ID INNER JOIN '
      . $wpdb->prefix . 'utubevideo_dataset d ON a.DATA_ID = d.DATA_ID '
      . 'WHERE VID_ID = %d',
      $videoID
    );

    $videoData = $wpdb->get_row($query);

    if ($videoData)
      return new Video($videoData);

    return false;
  }

  static function getItems()
  {
    global $wpdb;
    $data = [];

    $videosData = $wpdb->get_results(
      'SELECT *
      FROM ' . $wpdb->prefix . 'utubevideo_video
      ORDER BY VID_ID'
    );

    foreach ($videosData as $videoData)
      $data[] = new Video($videoData);

    return $data;
  }

  static function createItem($form, $nextSortPosition, $thumbnailType)
  {
    global $wpdb;

    //insert new video
    if ($wpdb->insert(
      $wpdb->prefix . 'utubevideo_video',
      [
        'VID_SOURCE' => $form->getSource(),
        'VID_NAME' => $form->getTitle(),
        'VID_DESCRIPTION' => $form->getDescription(),
        'VID_URL' => $form->getSourceID(),
        'VID_THUMBTYPE' => $thumbnailType,
        'VID_QUALITY' => $form->getQuality(),
        'VID_CHROME' => $form->getShowControls(),
        'VID_STARTTIME' => $form->getStartTime(),
        'VID_ENDTIME' => $form->getEndTime(),
        'VID_POS' => $nextSortPosition,
        'VID_UPDATEDATE' => current_time('timestamp'),
        'ALB_ID' => $form->getAlbumID(),
        'PLAY_ID' => $form->getPlaylistID()
      ]
    ))
      return $wpdb->insert_id;

    return false;
  }

  static function deleteItem($videoID)
  {
    global $wpdb;

    //delete video from database
    if ($wpdb->delete(
      $wpdb->prefix . 'utubevideo_video',
      ['VID_ID' => $videoID]
    ) !== false)
      return true;

    return false;
  }

  static function updateItem($form)
  {
    global $wpdb;

    //list of fields to update [patch]
    $updatedFields = [];

    //set optional update fields
    if ($form->getTitle() != null)
      $updatedFields['VID_NAME'] = $form->getTitle();

    if ($form->getDescription() != null)
      $updatedFields['VID_DESCRIPTION'] = $form->getDescription();

    if ($form->getQuality() != null)
      $updatedFields['VID_QUALITY'] = $form->getQuality();

    if ($form->getShowControls() !== null)
      $updatedFields['VID_CHROME'] = $form->getShowControls();

    if ($form->getPublished() !== null)
      $updatedFields['VID_PUBLISH'] = $form->getPublished();

    if ($form->getAlbumID() != null)
      $updatedFields['ALB_ID'] = $form->getAlbumID();

    //set required update fields
    $updatedFields['VID_UPDATEDATE'] = current_time('timestamp');
    $updatedFields['VID_THUMBTYPE'] = 'default';

    //set semi required update fields
    $updatedFields['VID_STARTTIME'] = $form->getStartTime();
    $updatedFields['VID_ENDTIME'] = $form->getEndTime();

    //update query
    if ($wpdb->update(
      $wpdb->prefix . 'utubevideo_video',
      $updatedFields,
      ['VID_ID' => $form->getVideoID()]
    ) >= 0)
      return true;

    return false;
  }

  static function updateItemPosition($videoID, $position)
  {
    global $wpdb;

    $wpdb->update(
      $wpdb->prefix . 'utubevideo_video',
      ['VID_POS' => $position],
      ['VID_ID' => $videoID]
    );
  }

  static function getItemsByAlbum($albumID)
  {
    global $wpdb;
    $data = [];

    //check for valid albumID
    if (!$albumID)
      return false;

    $query = $wpdb->prepare(
      'SELECT *
      FROM ' . $wpdb->prefix . 'utubevideo_video
      WHERE ALB_ID = %d
      ORDER BY VID_POS',
      $albumID
    );

    $videosData = $wpdb->get_results($query);

    foreach ($videosData as $videoData)
      $data[] = new Video($videoData);

    return $data;
  }

  static function getPublishedItemsByAlbum($albumID, $sortDirection = 'desc')
  {
    global $wpdb;
    $data = [];

    //check for valid albumID
    if (!$albumID)
      return false;

    $query = $wpdb->prepare(
      'SELECT *
      FROM ' . $wpdb->prefix . 'utubevideo_video
      WHERE ALB_ID = %d
      && VID_PUBLISH = 1
      ORDER BY VID_POS ' . $sortDirection,
      $albumID
    );

    $videosData = $wpdb->get_results($query);

    foreach ($videosData as $videoData)
      $data[] = new Video($videoData);

    return $data;
  }

  static function getItemsByPlaylist($playlistID)
  {
    global $wpdb;
    $data = [];

    if (!$playlistID)
      return false;

    $query = $wpdb->prepare(
      'SELECT *
      FROM ' . $wpdb->prefix . 'utubevideo_video
      WHERE PLAY_ID = %d
      ORDER BY VID_POS',
      $playlistID
    );

    $videosData = $wpdb->get_results($query);

    foreach ($videosData as $videoData)
      $data[] = new Video($videoData);

    return $data;
  }

  static function getItemsByGallery($galleryID)
  {
    global $wpdb;
    $data = [];

    $albumIDsQuery = $wpdb->prepare(
      'SELECT ALB_ID
      FROM ' . $wpdb->prefix . 'utubevideo_album
      WHERE DATA_ID = %d',
      $galleryID
    );

    //get albums in gallery
    $albumIDsData = $wpdb->get_results($albumIDsQuery);
    $albumIDs = [-1];

    foreach ($albumIDsData as $albumIDData)
      $albumIDs[] = $albumIDData->ALB_ID;

    $albumsQueryString = implode(', ', $albumIDs);

    $videosData = $wpdb->get_results(
      'SELECT *
      FROM ' . $wpdb->prefix . 'utubevideo_video
      WHERE ALB_ID IN (' . $albumsQueryString . ')'
    );

    foreach ($videosData as $videoData)
      $data[] = new Video($videoData);

    return $data;
  }

  static function getNextSortPositionByAlbum($albumID)
  {
    global $wpdb;

    //check for valid albumID
    if ($albumID === false)
      return false;

    $query = $wpdb->prepare(
      'SELECT COUNT(VID_ID) AS VID_COUNT
      FROM ' . $wpdb->prefix . 'utubevideo_video
      WHERE ALB_ID = %d',
      $albumID
    );

    //get next sort position for album
    $nextSortPosition = $wpdb->get_var($query);

    if ($nextSortPosition === false)
      $nextSortPosition = 0;

    return $nextSortPosition;
  }

  static function getThumbnailTypeByAlbum($albumID = false)
  {
    global $wpdb;

    //check for valid albumID
    if ($albumID === false)
      return false;

    $query = $wpdb->prepare(
      'SELECT DATA_THUMBTYPE
      FROM ' . $wpdb->prefix . 'utubevideo_album a
      INNER JOIN ' . $wpdb->prefix . 'utubevideo_dataset d ON a.DATA_ID = d.DATA_ID
      WHERE a.ALB_ID = %d',
      $albumID
    );

    $thumbnailType = $wpdb->get_var($query);

    if (!$thumbnailType)
      $thumbnailType = 'rectangle';

    return $thumbnailType;
  }

  static function deleteItemsByAlbum($albumID = false)
  {
    global $wpdb;

    //delete videos from database
    if ($wpdb->delete(
      $wpdb->prefix . 'utubevideo_video',
      ['ALB_ID' => $albumID]
    ) !== false)
      return true;

    return false;
  }

  static function deleteItemsByGallery($galleryID)
  {
    global $wpdb;

    $albumIDsQuery = $wpdb->prepare(
      'SELECT ALB_ID
      FROM ' . $wpdb->prefix . 'utubevideo_album
      WHERE DATA_ID = %d',
      $galleryID
    );

    //get albums in gallery
    $albumIDsData = $wpdb->get_results($albumIDsQuery);
    $albumIDs = [-1];

    foreach ($albumIDsData as $albumIDData)
      $albumIDs[] = $albumIDData->ALB_ID;

    $albumsQueryString = implode(', ', $albumIDs);

    if ($wpdb->query(
      'DELETE
      FROM ' . $wpdb->prefix . 'utubevideo_video
      WHERE ALB_ID IN (' . $albumsQueryString . ')'
    ) !== false)
      return true;

    return false;
  }
}
