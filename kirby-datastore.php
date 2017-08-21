<?php

require(__DIR__ . '/KirbyDatastore.class.php');

/**
 * KirbyDatastore init
 */

$database = new JonGacnik\KirbyDatastore\KirbyDatastore(
  c::get('datastore.location', kirby()->roots()->content()),
  c::get('datastore.dbname', 'datastore')
);


/**
 * KirbyDatastore field
 */

$kirby->set('field', 'datastore', __DIR__ . '/fields/datastore');


/**
 * KirbyDatastore site method:
 *
 * @return entire database
 * site()->datastore()
 *
 * @return entries collection
 * site()->datastore('entries')
 */

$kirby->set('site::method', c::get('datastore.method', 'datastore'), function ($site, $collection = false) use ($database) {
  return $collection ? $database->collection($collection) : $database;
});
