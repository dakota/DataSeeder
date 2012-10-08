<?php
class ExampleSeed extends SeedShell {
  public $uses = array('Category');

  var $data = array(
    'name' => 'Test Category',
    'user_id' => 2
  );

  function up() {
    $this->Category->create();
    $this->Category->save($this->data);
  }

  function down() {
    $this->Category->deleteAll(array(
      'Category.name' => $this->data['name'],
      'Category.user_id' => $this->data['user_id']
    ), false);
  }
}