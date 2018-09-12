import React from 'react';
import Griddle from './Griddle/Griddle';

class AlbumTable extends React.Component
{
  constructor(props)
  {
    super(props);
  }

  getHeaders()
  {
    return [
      {
        key: 'id',
        title: 'ID',
        sortable: true,
        sortDirection: 'desc'
      },
      {
        key: 'thumbnail',
        title: 'Thumbnail',
        sortable: false,
        sortDirection: '',
        formatter: (row, cellData) =>
        {
          return <img
            src={cellData}
            className="utv-preview-thumb"
            data-rjs="2"
          />
        }
      },
      {
        key: 'title',
        title: 'Title',
        sortable: true,
        sortDirection: '',
        formatter: (row, cellData) =>
        {
          return <a
            onClick={() => this.props.changeAlbum(row.id)}
            href="javascript:void(0)"
            className="utv-row-title">
              {cellData}
          </a>
        }
      },
      {
        key: 'published',
        title: 'Published',
        sortable: true,
        sortDirection: ''
      },
      {
        key: 'dateAdded',
        title: 'Date Added',
        sortable: true,
        sortDirection: ''
      },
      {
        key: 'videoCount',
        title: '# Videos',
        sortable: true,
        sortDirection: ''
      }
    ];
  }

  getDataMapping(data)
  {
    let newData = [];

    for (let item of data)
    {
      let record = {};
      let dateAdded = new Date(item.updateDate * 1000);
      record.id =  item.id;
      record.thumbnail = item.thumbnail;
      record.title = item.title;
      record.published = item.published;
      record.dateAdded = dateAdded.getFullYear() + '/' + (dateAdded.getMonth() + 1) + '/' + dateAdded.getDate();
      record.videoCount = item.videoCount;
      newData.push(record);
    }

    return newData;
  }

  render()
  {
    return <Griddle
      headers={this.getHeaders()}
      recordLabel="albums"
      apiLoadPath={'/wp-json/utubevideogallery/v1/galleries/' + this.props.selectedGallery + '/albums'}
      dataMapper={this.getDataMapping}
    />
  }
}

export default AlbumTable;