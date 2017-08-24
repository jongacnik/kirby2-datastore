# Kirby Datastore

Provides Kirby with a flat-file json datastore for housing large-ish quantities of data, and a panel field for managing this data.

## Instance

```php
// Get datastore instance
$database = site()->datastore();

// Get/Create a collection 
$people = $database->collection('people');

// Get entries as array
$people->find();
```

The datastore is implemented with [Jisly](https://github.com/r0mdau/jisly), so refer to those docs for all methods. Sorry for the French but the code examples are good!

## Field

The `datastore` field is an extension of the structure field. Instead of the entries being saved into a page text file as yaml, the entries are saved to the datastore.

```yaml
people:
  label: People
  type: datastore
  fields:
    name:
      label: Name
      type: text
    age:
      label: Age
      type: number
```

To access entries from the template:

```php
site()->datastore('people')->find();
```

### Field Options

```yaml
people:
  label: People
  type: datastore
  collection: persons
  fields:
    name:
      label: Name
      type: text
    email:
      label: Email
      type: email
    age:
      label: Age
      type: number
    glasses:
      label: Glasses
      type: toggle
  columns:
    name: Name
    email: Email
    details:
      label: Details
      value: >
        {{ age }}, {{ glasses }}
  order: desc

```

## Options

Defaults shown

```php
c::set('datastore.location', kirby()->roots()->content());
c::set('datastore.dbname', 'datastore');
c::set('datastore.method', 'datastore');
```

## Usage

```yaml
entries:
  label: Entries
  type: datastore
```

## Filter Data
A custom filter can be applied to the data before it is put out as a json response. This is perfect if you need to modify some of the data for presentation, change columns, etc.

### Example
Create a simple plugin `site/plugins/mydatafilters/mydatafilters.php`:
```php
<?php

class MyDataFilters {
  static function myfilterfunc($data) {
    // filter data here
    return $data;
  }
}
```
Update field definition:
```yaml
people:
  label: People
  type: datastore
  collection: persons
  filter: MyDataFilters::myfilterfunc
  fields:
    name:
      label: Name
      type: text
    email:
      label: Email
      type: email
    age:
      label: Age
      type: number
    glasses:
      label: Glasses
      type: toggle
  columns:
    name: Name
    email: Email
    details:
      label: Details
      value: >
        {{ age }}, {{ glasses }}
  order: desc

```


## Why?

Sometimes I need to store large-ish quantities of data, think along the lines of 10,000+ entries with 10+ fields. With Kirby we have 2 built-in options:

- 10,000 pages
- 10,000 structure (yaml) entries

10,000 pages is a mess and quite slow, especially if you need to iterate over and filter that data. The advantage to the Kirby flat-file system is in a tree-based structure; 10,000 subfolders isn’t making the best use of that.

10,000 structure entries initially feels more sane, but will be a nightmare to manage in the panel, and Kirby’s `yaml` or `toStructure` methods become quite slow (understandably) on this many entries, so iterating and filtering is also an issue here.

I needed a different way to store data, but wanted the solution to remain flat-file to not break with the Kirby ethos, and wanted the data modelable and editable via the panel.