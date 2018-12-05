<?php

namespace CodeClouds\UTubeVideoGallery\UI;

class GalleryView
{
  private $_atts;

  public function __construct($atts)
  {
    $this->mapAttributes($atts);
  }

  private function mapAttributes($atts)
  {
    //map default attributes
    $this->_atts = shortcode_atts([
      'id' => null,
      'panelvideocount' => 14, //video count per panel view / page
      'theme' => 'light', //[light, dark, transparent]
      'icon' => 'red', //[default, red, blue]
      'controls' => 'false', //[true, false]
      'videocount' => null, //[any integer]
      'albumcount' => null //[any integer]
    ], $atts, 'utubevideo');
  }

  public function render()
  {
    return '<div
      class="utv-gallery-root"
      data-id="' . $this->_atts['id'] . '"
    ></div>';
  }
}
