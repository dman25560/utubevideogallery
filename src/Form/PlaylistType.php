<?php

namespace UTubeVideoGallery\Form;

use WP_REST_Request;
use UTubeVideoGallery\Exception\UserMessageException;

class PlaylistType
{
  private $playlistID;
  private $title;
  private $source;
  private $sourceID;
  private $videoQuality;
  private $showControls;
  private $albumID;

  public function __construct(WP_REST_Request $req)
  {
    if (isset($req['playlistID']))
      $this->playlistID = sanitize_key($req['playlistID']);

    if (isset($req['title']))
      $this->title = sanitize_text_field($req['title']);

    if (isset($req['source']))
      $this->source = sanitize_text_field($req['source']);

    if (isset($req['sourceID']))
      $this->sourceID = sanitize_text_field($req['sourceID']);

    if (isset($req['videoQuality']))
      $this->videoQuality = sanitize_text_field($req['videoQuality']);

    if (isset($req['showControls']))
      $this->showControls = ($req['showControls'] ? 0 : 1);

    if (isset($req['albumID']))
      $this->albumID = sanitize_key($req['albumID']);
  }

  public function getPlaylistID()
  {
    return $this->playlistID;
  }

  public function getTitle()
  {
    return $this->title;
  }

  public function getSource()
  {
    return $this->source;
  }

  public function getSourceID()
  {
    return $this->sourceID;
  }

  public function getVideoQuality()
  {
    return $this->videoQuality;
  }

  public function getShowControls()
  {
    return $this->showControls;
  }

  public function getAlbumID()
  {
    return $this->albumID;
  }

  //validate form specific cases
  public function validate(string $action)
  {
    if ($action == 'create')
      $this->validateCreate();
    else if ($action == 'update')
      $this->validateUpdate();
    else if ($action == 'delete')
      $this->validateDelete();
    else if ($action == 'get')
      $this->validateGet();
  }

  private function validateCreate()
  {
    //check for required fields
    if (empty($this->title)
      || empty($this->source)
      || empty($this->sourceID)
      || empty($this->videoQuality)
      || empty($this->albumID)
    )
      throw new UserMessageException(__('Invalid parameters', 'utvg'));
  }

  private function validateUpdate()
  {
    //check for valid playlistID
    if (!$this->playlistID)
      throw new UserMessageException(__('Invalid playlist ID', 'utvg'));
  }

  private function validateDelete()
  {
    //check for valid playlistID
    if (!$this->playlistID)
      throw new UserMessageException(__('Invalid playlist ID', 'utvg'));
  }

  private function validateGet()
  {
    //check for valid playlistID
    if (!$this->playlistID)
      throw new UserMessageException(__('Invalid playlist ID', 'utvg'));
  }
}
