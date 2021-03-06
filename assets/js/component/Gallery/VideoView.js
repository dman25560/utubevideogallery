import React from 'react';
import VideoThumbnails from './VideoThumbnails';
import Paging from './Paging';
import galleryService from '../../service/GalleryService';

class VideoView extends React.Component
{
  constructor(props)
  {
    super(props);

    this.openVideo = this.openVideo.bind(this);
  }

  openVideo(value)
  {
    const selectedVideo = this.props.videos[value];

    if (selectedVideo)
      this.props.onOpenVideoPopup(selectedVideo);
  }

  getPagingNode()
  {
    //return if pagination not enabled
    if (!this.props.thumbnailsPerPage)
      return null;

    const {
      videos,
      currentPage,
      onChangePage
    } = this.props;

    //get total pages
    let totalPages = Math.ceil(videos.length / parseInt(this.props.thumbnailsPerPage));

    //return paging component
    return <Paging
      currentPage={currentPage}
      totalPages={totalPages}
      onChangePage={onChangePage}
    />;
  }

  render()
  {
    const {
      iconType,
      thumbnailType,
      videos,
      currentPage,
      thumbnailsPerPage,
      onChangePage
    } = this.props;

    const galleryClasses = galleryService.getGalleryClasses(iconType);

    return (
      <div className={galleryClasses.join(' ')}>
        <VideoThumbnails
          videos={videos}
          onOpenVideo={this.openVideo}
          thumbnailType={thumbnailType}
          currentPage={currentPage}
          thumbnailsPerPage={thumbnailsPerPage}
        />
        {this.getPagingNode()}
      </div>
    );
  }
}

export default VideoView;
