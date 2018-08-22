<?php

require_once(plugin_dir_path(__FILE__) . 'utvWPListTableBase.class.php');

class utvVideoListTable extends utvWPListTableBase
{
  private $_id, $_baseURL;

  function __construct($id)
  {
    global $status, $page;

    parent::__construct([
      'singular' => '',
      'plural' => 'utv-sortable-table',
      'ajax' => false
    ]);

    $this->_id = $id;
    $this->_baseURL = wp_upload_dir();
    $this->_baseURL = $this->_baseURL['baseurl'] . '/utubevideo-cache/';
  }

  function get_columns()
  {
    $columns = [
      'cb' => '<input type="checkbox">',
      'utv-vidthumbnail' => __('Thumbnail', 'utvg'),
      'name' => __('Name', 'utvg'),
      'published' => __('Published', 'utvg'),
      'dateadd' => __('Date Added', 'utvg')
    ];

    return $columns;
  }

  function prepare_items()
  {
    $this->process_bulk_action();

    $columns = $this->get_columns();
    $hidden = [];
    $sortable = $this->get_sortable_columns();
    $this->_column_headers = [$columns, $hidden, $sortable];

    $this->items = $this->setup_items();

    if (!empty($_GET['orderby']) && !empty($_GET['order']))
      usort($this->items, [$this, 'usort_reorder']);
  }

  function column_default($item, $column_name)
  {
    switch ($column_name)
    {
      case 'utv-vidthumbnail':
      case 'name':
      case 'published':
      case 'dateadd':
        return $item[$column_name];
      default:
        return 'An unknown error has occured';
    }
  }

  function get_sortable_columns()
  {
    $sortable_columns = [
      'name'  => ['name', false],
      'published' => ['published', false],
      'dateadd' => ['dateadd', false]
    ];

    return $sortable_columns;
  }

  function usort_reorder($a, $b)
  {
    // If no sort, default to title
    $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'name';
    // If no order, default to asc
    $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';
    // Determine sort order
    $result = strcmp( $a[$orderby], $b[$orderby] );
    // Send final sort direction to usort
    return ($order === 'asc') ? $result : -$result;
  }

  //add id to table rows
  function single_row($item)
  {
    static $row_class = '';
    $row_class = ( $row_class == '' ? ' class="alternate"' : '' );

    echo '<tr id="' . $item['ID'] . '" ' . $row_class . '>';
    $this->single_row_columns( $item );
    echo '</tr>';
  }

  function get_bulk_actions()
  {
    $actions = [
      'delete' => __('Delete', 'utvg'),
      'publish' => __('Publish', 'utvg'),
      'unpublish' => __('Unpublish', 'utvg'),
      'refresh' => __('Refresh Thumbnail', 'utvg')
    ];

    return $actions;
  }

  function process_bulk_action()
  {
    $action = $this->current_action();

    if ($action != -1)
    {
      global $wpdb;
      require_once 'utvAdminGen.class.php';

      $options = get_option('utubevideo_main_opts');

      utvAdminGen::initialize($options);

      if ($action == 'delete')
        utvAdminGen::deleteVideos($_POST['video'], $wpdb);
      elseif ($action == 'publish')
        utvAdminGen::toggleVideosPublish($_POST['video'], '1', $wpdb);
      elseif ($action == 'unpublish')
        utvAdminGen::toggleVideosPublish($_POST['video'], '0', $wpdb);
      elseif ($action == 'refresh')
        utvAdminGen::refreshThumbnails($_POST['video'], $wpdb);
    }
  }

  function column_cb($item)
  {
    return sprintf('<input type="checkbox" name="video[]" value="%s">', $item['ID']);
  }

  function no_items()
  {
    _e('No videos found', 'utvg');
  }

  function setup_items()
  {
    global $wpdb;
    $cells = [];

    $data = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'utubevideo_video WHERE ALB_ID = ' . $this->_id . ' ORDER BY VID_POS', ARRAY_A);

    foreach ($data as $val)
    {
      array_push($cells, [
        'ID' => $val['VID_ID'],
        'utv-vidthumbnail' => ($val['VID_SOURCE'] == 'youtube' ? '<a href="https://www.youtube.com/watch?v=' . $val['VID_URL'] . '" target="_blank" title="' . __('Watch On YouTube', 'utvg') . '">' : '<a href="https://www.vimeo.com/' . $val['VID_URL'] . '" target="_blank" title="' . __('Watch On Vimeo', 'utvg') . '">') .
        '<img src="' . $this->_baseURL . $val['VID_URL'] . $val['VID_ID'] . '.jpg" class="utv-preview-thumb" data-rjs="2">
        </a>
        <span class="utv-sortable-handle" title="' . __('Click and drag to reorder') . '">::</span>',
        'name' => '<span class="utv-row-title">' . stripslashes($val['VID_NAME']) . '</span>
        <div class="utv-row-actions">
        <a href="?page=utubevideo&view=videoedit&id=' . $val['VID_ID'] . '&pid=' . $this->_id . '-' . $_GET['pid'] . '" title="' . __('Edit this item', 'utvg') . '">' . __('Edit', 'utvg') . '</a>
        <span class="utv-row-divider">|</span>
        <a href="" class="ut-delete-video" title="' . __('Delete this item', 'utvg') . '">' . __('Delete', 'utvg') . '</a>
        <span class="utv-row-divider">|</span>' . ($val['VID_SOURCE'] == 'youtube' ? '<a href="https://www.youtube.com/watch?v=' . $val['VID_URL'] . '" target="_blank" title="' . __('Watch On YouTube', 'utvg') . '">' : '<a href="https://www.vimeo.com/' . $val['VID_URL'] . '" target="_blank" title="' . __('Watch On Vimeo', 'utvg') . '">') . __('Watch', 'utvg') . '</a>
        </div>',
        'published' => $val['VID_PUBLISH'] == '1' ? '<a href="" class="utv-publish" title="' . __('Click to toggle', 'utvg') . '"/>' : '<a href="" class="utv-unpublish" title="' . __('Click to toggle', 'utvg') . '"/>',
        'dateadd' => date('Y/m/d', $val['VID_UPDATEDATE'])
      ]);
    }

    return $cells;
  }
}

?>
