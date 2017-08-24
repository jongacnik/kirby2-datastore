<?php

class DatastoreFieldController extends Kirby\Panel\Controllers\Field {

  public function add() {

    $self      = $this;
    $field     = $this->field();
    $model     = $this->model();
    $structure = $this->structure($model);
    
    // abort if the field already has too many items or is readonly
    if($field->readonly || (!is_null($field->limit) && $field->entries()->count() >= $field->limit)) {
      return $this->modal('error', array(
        'text' => l('fields.structure.max.error')
      ));
    }
    
    $modalsize = $this->field()->modalsize();
    $form      = $this->form('add', array($model, $structure), function($form) use($model, $structure, $self, $field) {

      $form->validate();

      if(!$form->isValid()) {
        return false;
      }

      // add to datastore
      $data = $form->serialize();
      $field->database->collection($field->collection())->insert($data);

      $self->redirect($model);

    });

    return $this->modal('add', compact('form', 'modalsize'));

  }

  public function update($entryId) {

    $self      = $this;
    $field     = $this->field();
    $model     = $this->model();
    $structure = $this->structure($model);
    
    $entry = (array)$field->database->collection($field->collection())->findOne(['_rid' => $entryId]);

    // abort if the field is readonly
    if($field->readonly) {
      return $this->modal('error', array(
        'text' => l('fields.structure.max.error')
      ));
    }

    if(!$entry) {
      return $this->modal('error', array(
        'text' => l('fields.structure.entry.error')
      ));
    }

    $modalsize = $this->field()->modalsize();
    $form      = $this->form('update', array($model, $structure, $entry), function($form) use($model, $structure, $self, $field, $entryId) {

      // run the form validator
      $form->validate();

      if(!$form->isValid()) {
        return false;
      }

      // update in datastore
      $data = $form->serialize();
      $data['_rid'] = $entryId;
      $field->database->collection($field->collection())->update($entryId, $data);

      $self->redirect($model);

    });

    return $this->modal('update', compact('form', 'modalsize'));
        
  }

  public function delete($entryId) {
    
    $self      = $this;
    $field     = $this->field();
    $model     = $this->model();
    $structure = $this->structure($model);
    
    $entry = (array)$field->database->collection($field->collection())->findOne(['_rid' => $entryId]);

    // abort if the field is readonly
    if($field->readonly) {
      return $this->modal('error', array(
        'text' => l('fields.structure.max.error')
      ));
    }

    if(!$entry) {
      return $this->modal('error', array(
        'text' => l('fields.structure.entry.error')
      ));
    }

    $form = $this->form('delete', $model, function() use($self, $model, $structure, $field, $entryId) {
      
      // remove from datastore
      $entry = $field->database->collection($field->collection())->delete($entryId);

      $self->redirect($model);
    });
    
    return $this->modal('delete', compact('form'));

  }

  // get entries for the current table
  public function list () {
    $field = $this->field();
    $data = $field->database->collection($field->collection())->find();
    if($field->filter()) {
      $data = call_user_func($field->filter(), $data);
    } 
    return response::json($data);
  }

  protected function structure($model) {
    return $model->structure()->forField($this->fieldname());
  }

}
