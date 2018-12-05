import React from 'react';
import VideoThumbnails from './VideoThumbnails';

class VideoView extends React.Component
{
  constructor(props)
  {
    super(props);

    this.openVideo = this.openVideo.bind(this);
  }

  openVideo(value)
  {
    let selectedVideo = this.props.videos[value];

    if (selectedVideo)
      this.props.onOpenVideoPopup(selectedVideo);
  }

  render()
  {
    return (
      <div className="utv-gallery utv-icon-red">
        <VideoThumbnails
          videos={this.props.videos}
          onOpenVideo={this.openVideo}
          thumbnailType={this.props.thumbnailType}
        />
      </div>
    );
  }
}

export default VideoView;
