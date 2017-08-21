<?php

class DatastoreField extends BaseField {

  static public $assets = array(
    'js' => array(
      'datatables.min.js',
      'date.format.min.js',
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
  public $entry = null;
  public $structure = null;
  public $modalsize = 'medium';
  public $limit = null;
  public $sort = null;
  public $flip = false;

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
        'action'  => 'list'
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

  public function sort () {
    return $this->sort ? str::split($this->sort) : false;
  }

  public function flip () {
    return $this->flip === true ? true : false;
  }

  public function structure () {
    if(!is_null($this->structure)) {
      return $this->structure;
    } else {
      $structure = $this->model->structure()->forField($this->name);

      // add default items if the default value is being used
      if(is_array($this->default()) && $this->value() === $this->default()) {
        foreach($this->default() as $defaultItem) {
          $structure->store()->add($defaultItem);
        }
      }

      return $this->structure = $structure;
    }
  }

  public function fields () {

    $output = array();

    // use the configured fields if available
    $fieldData = $this->structure->fields();
    $fields = $this->entry;
    if(!is_array($fields)) {
      // fall back to all existing fields
      $fields = array_keys($fieldData);
    }

    foreach($fields as $f) {
      if(!isset($fieldData[$f])) continue;
      $v = $fieldData[$f];

      $v['name']  = $f;
      $v['value'] = '{{' . $f . '}}';

      $output[] = $v;
    }

    return $output;

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

  public function entries () {
    // get all entries from mongo-lite and convert to kirby structure
    $entries = structure($this->database->collection($this->collection())->find());

    if ($sort = $this->sort()) {
      $entries = call([$entries, 'sortBy'], $sort);
    }

    if ($this->flip()) {
      $entries = $entries->flip();
    }

    return $entries;
  }

  public function result () {
    return $this->collection();
  }

  public function entry ($data) {

    if(is_null($this->entry) or !is_string($this->entry)) {
      $html = array();
      foreach($this->fields as $name => $field) {
        if(isset($data->$name)) {
          $html[] = $data->$name;          
        }
      }
      return implode('<br>', $html);
    } else {
    
      $text = $this->entry;

      foreach((array)$data as $key => $value) {
        if(is_array($value)) {
          $value = implode(', ', array_values($value));
        }
        $text = str_replace('{{' . $key . '}}', $value, $text);
      }

      return $text;
    
    }

  }

  public function label () {
    if(!$this->label) return null;

    $label = new Brick('label');
    $label->addClass('label');
    $label->attr('for', $this->id());

    $h2 = new Brick('h2');
    $h2->addClass('hgroup hgroup-single-line hgroup-compressed cf');
    $span = new Brick('span', $this->i18n($this->label));
    $span->addClass('hgroup-title');

    $h2->append($span);

    // Edit/Add links if index of subpages
    $wrap = new Brick('span');
    $wrap->addClass('hgroup-options shiv shiv-dark shiv-left');

    $add = new Brick('a');
    $add->html('<i class="icon icon-left fa fa-plus-circle"></i>' . l('fields.structure.add'));
    $add->addClass('structure-add-button label-option');
    $add->data('modal', true);
    $add->attr('href', purl($this->model, 'field/' . $this->name . '/datastore/add'));

    $wrap->append($add);

    $h2->append($wrap);
    $label->append($h2);

    return $label;
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