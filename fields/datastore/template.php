<div 
  class="structure<?php e($field->readonly(), ' structure-readonly') ?>" 
  data-field="datastore" 
>
  <table
    class="display"
    width="100%"
    data-datastore-columns="<?php __(json_encode($field->columns())) ?>"
    data-datastore-entries="<?php __($field->url('list')) ?>"
    data-datastore-add="<?php __($field->url('add')) ?>"
    data-datastore-update="<?php __($field->url('update')) ?>"
    data-datastore-delete="<?php __($field->url('delete')) ?>"
    data-datastore-rows="<?php __($field->rows) ?>"
    data-datastore-order="<?php __($field->order) ?>"
  ></table>
</div>