<?php
use Cake\Core\Configure;

echo "<?php\n";
echo 'namespace ' . Configure::read('App.namespace') . "\\Config\\Seed;\n";
echo "\n";
echo "use DataSeeder\\Lib\\CakeSeed";
?>
/**
* Seed class <?php echo $class; ?>Seed
*/
class <?php echo $class; ?>Seed extends CakeSeed {

/**
* Seed description
*
* @var string
*/
	public $description = '';

/**
* Models to include in this seed
*
* @var array
*/
	public $uses = array();

/**
* Data to create
*
* @var array
*/
	public $data = array();

/**
* Method that is called when creating data
*/
	public function up() {
	}

/**
* Method that is called when deleting data
*/
	public function down() {
	}
}