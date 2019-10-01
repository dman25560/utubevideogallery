<?php

namespace UTubeVideoGallery\Service\Manager;

use UTubeVideoGallery\Repository\VideoRepository;
use UTubeVideoGallery\Service\Thumbnail;
use UTubeVideoGallery\Exception\UserMessageException;

class VideoManager
{
  //get video
  public static function getVideo($videoID)
  {
    //get video
    $video = VideoRepository::getItem($videoID);

    //check if video exists
    if (!$video)
      throw new UserMessageException(__('Database Error: The video does not exist', 'utvg'));

    return $video;
  }

  //get all videos
  public static function getVideos()
  {
    return VideoRepository::getItems();
  }

  //get videos in album
  public static function getAlbumVideos($albumID)
  {
    return VideoRepository::getItemsByAlbum($albumID);
  }

  //create video
  public static function createVideo($form)
  {
    //get next video sort position
    $nextSortPosition = VideoRepository::getNextSortPositionByAlbum($form->getAlbumID());

    //get video thumbnail type
    $thumbnailType = VideoRepository::getThumbnailTypeByAlbum($form->getAlbumID());

    try
    {
      //insert new video
      $videoID = VideoRepository::createItem($form, $nextSortPosition, $thumbnailType);

      //if successfull video creation..
      if ($videoID)
      {
        $thumbnail = new Thumbnail($videoID);
        $thumbnail->save();

        return true;
      }
      else
        throw new UserMessageException(__('Database Error: Video creation failed', 'utvg'));
    }
    catch (\Exception $e)
    {
      //delete video from db due to error if needed
      if (isset($videoID) && $videoID !== false)
        VideoRepository::deleteItem($videoID);

      //rethrow if UserMessageException
      if (get_class($e) == UserMessageException::class)
        throw $e;

      return false;
    }
  }

  //update video
  public static function updateVideo($form)
  {
    //update video
    if (VideoRepository::updateItem($form))
    {
      if (!$form->getSkipThumbnailRender())
      {
        //refresh video thumbnail
        $thumbnail = new Thumbnail($form->getVideoID());
        $thumbnail->save();
      }
    }
    else
      throw new UserMessageException(__('Database Error: Failed to update video', 'utvg'));
  }

  //delete video
  public static function deleteVideo($videoID)
  {
    //get video
    $video = VideoRepository::getItem($videoID);

    //check if video exists
    if (!$video)
      throw new UserMessageException(__('Database Error: Video does not exist', 'utvg'));

    //delete video
    if (!VideoRepository::deleteItem($videoID))
      throw new UserMessageException(__('Database Error: Failed to delete video', 'utvg'));

    //delete video thumbnail
    $thumbnailPath = wp_upload_dir();
    $thumbnailPath = $thumbnailPath['basedir'] . '/utubevideo-cache/';
    unlink($thumbnailPath . $video->getThumbnail() . '.jpg');
    unlink($thumbnailPath . $video->getThumbnail() . '@2x.jpg');
  }
}
