<?php

class DatastoreField extends BaseField {

  static public $assets = array(
    'js' => array(
      'datatables.min.js',
      'datastore.js'
    ),
    'css' => array(
      'datatables.min.css',
      'datastore.css'
    )
  );

  public $default = array();
  public $fields = array();
  public $columns = array();
  public $collection = null;
  public $rows = 10;
  public $order = 'asc';
  public $modalsize = 'medium';
  public $limit = null;

  public function __construct () {
    // connect to datastore db
    $this->database = new JonGacnik\KirbyDatastore\KirbyDatastore(
      c::get('datastore.location', kirby()->roots()->content()),
      c::get('datastore.dbname', 'datastore')
    );
  }

  public function routes () {

    return array(
      array(
        'pattern' => 'list',
        'method'  => 'get',
        'action'  => 'listentries'
      ),
      array(
        'pattern' => 'add',
        'method'  => 'get|post',
        'action'  => 'add'
      ),
      array(
        'pattern' => '(:any)/update',
        'method'  => 'get|post',
        'action'  => 'update'
      ),
      array(
        'pattern' => '(:any)/delete',
        'method'  => 'get|post',
        'action'  => 'delete',
      )
    );
  }

  public function modalsize () {
    $sizes = array('small', 'medium', 'large');
    return in_array($this->modalsize, $sizes) ? $this->modalsize : 'medium';
  }

  // name of datastore collection specified by collection option, or field name
  public function collection () {
    return (isset($this->collection) && $this->collection) ? str::slug($this->collection) : $this->name;
  }

  public function columns () {
    // if user has not set any columns, extract from fields
    return !empty($this->columns) ? $this->columns : array_map(function ($n) {
      return $n['label'];
    }, $this->fields);
  }

  public function result () {
    return $this->collection();
  }

  public function label () {
    if(!$this->label) return null;

    $addurl = purl($this->model, 'field/' . $this->name . '/datastore/add');

    return <<<HTML
      <label class="label" for="{$this->id()}">
        <h2 class="hgroup hgroup-single-line hgroup-compressed cf">
          <span class="hgroup-title">{$this->i18n($this->label)}</span>
          <span class="hgroup-options shiv shiv-dark shiv-left">
            <span class="hgroup-option-right">
              <a href="{$addurl}" data-modal="true">
                <i class="icon icon-left fa fa-plus-circle"></i><span>Add</span>
              </a>
            </span>
          </span>
        </h2>
      </label>
HTML;
  }

  public function content () {
    return tpl::load(__DIR__ . DS . 'template.php', array('field' => $this));
  }

  public function url ($action) {
    return purl($this->model(), 'field/' . $this->name() . '/datastore/' . $action);
  }  

  public function validate () {
    return true;
  }

}
