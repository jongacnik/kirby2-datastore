<?php

namespace JonGacnik\KirbyDatastore;

require 'vendor/autoload.php';

use Jisly\Jisly;

/**
 * Wrapper around Jisly for auto dir/file creation
 *
 * $database = new KirbyDatastore(__DIR__, 'my-datastore');
 * $items = $database->collection('items');
 * $items->insert(['name' => 'rad']);
 */

class KirbyDatastore {

  public $collections = [];

  /**
   * @param $name database name a.k.a folder name
   * @param $dir  directory where database should be created
   */
  public function __construct ($dir, $name) {
    $this->dir = $dir . '/' . $name;

    if (!file_exists($this->dir)) {
      mkdir($this->dir, 0777, true);
    }

    $this->database = new Jisly($this->dir);
  }

  /**
   * @param $name table name a.k.a file name
   */
  public function collection ($name) {
    if (!in_array($name, $this->collections)) {
      $this->collections[$name] = $this->database->collection($name . '.jsondb');
    }
    return $this->collections[$name];
  }

}