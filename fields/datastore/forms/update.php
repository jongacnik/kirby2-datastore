<?php 

return function($model, $structure, $entry) {
  
  $form = new Kirby\Panel\Form($structure->fields(), $entry, $structure->field());

  $form->cancel($model);
  $form->buttons->submit->value = l('ok');

  return $form;

};