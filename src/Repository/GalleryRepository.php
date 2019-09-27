<?php

namespace UTubeVideoGallery\Repository;

use UTubeVideoGallery\Entity\Gallery;

class GalleryRepository
{
  public function getItem($galleryID)
  {
    global $wpdb;

    if (!$galleryID)
      return false;

    $galleryQuery = $wpdb->prepare(
      'SELECT *
      FROM ' . $wpdb->prefix . 'utubevideo_dataset
      WHERE DATA_ID = %d',
      $galleryID
    );

    $albumCountQuery = $wpdb->prepare(
      'SELECT COUNT(ALB_ID) AS ALBUM_COUNT
      FROM ' . $wpdb->prefix . 'utubevideo_album
      WHERE DATA_ID = %d',
      $galleryID
    );

    $galleryData = $wpdb->get_row($galleryQuery);
    $albumCount = $wpdb->get_var($albumCountQuery);

    if (!$albumCount)
      $albumCount = 0;

    if ($galleryData)
    {
      $galleryData->ALBUM_COUNT = $albumCount;
      return new Gallery($galleryData);
    }

    return false;
  }

  public function getItems()
  {
    global $wpdb;
    $data = [];

    $galleriesData = $wpdb->get_results(
      'SELECT *
      FROM ' . $wpdb->prefix . 'utubevideo_dataset
      ORDER BY DATA_ID'
    );

    foreach ($galleriesData as $galleryData)
    {
      $albumCount = $wpdb->get_var(
        'SELECT COUNT(ALB_ID) AS ALBUM_COUNT
        FROM ' . $wpdb->prefix . 'utubevideo_album
        WHERE DATA_ID = ' . $galleryData->DATA_ID
      );

      if (!$albumCount)
        $albumCount = 0;

      $galleryData->ALBUM_COUNT = $albumCount;
      $data[] = new Gallery($galleryData);
    }

    return $data;
  }

  public function createItem(
    $title,
    $albumSorting,
    $thumbnailType,
    $displayType
  )
  {
    global $wpdb;

    $currentTime = current_time('timestamp');

    //insert new gallery
    if ($wpdb->insert(
      $wpdb->prefix . 'utubevideo_dataset',
      [
        'DATA_NAME' => $title,
        'DATA_SORT' => $albumSorting,
        'DATA_THUMBTYPE' => $thumbnailType,
        'DATA_DISPLAYTYPE' => $displayType,
        'DATA_UPDATEDATE' => $currentTime
      ]
    ))
      return $wpdb->insert_id;

    return false;
  }

  public function deleteItem($galleryID)
  {
    global $wpdb;

    if ($wpdb->delete(
      $wpdb->prefix . 'utubevideo_dataset',
      ['DATA_ID' => $galleryID]
    ) != false)
      return true;

    return false;
  }

  public function updateItem($galleryID, $updatedFields)
  {
    global $wpdb;

    //update database entry
    if ($wpdb->update(
      $wpdb->prefix . 'utubevideo_dataset',
      $updatedFields,
      ['DATA_ID' => $galleryID]
    ) >= 0)
      return true;

    return false;
  }
}