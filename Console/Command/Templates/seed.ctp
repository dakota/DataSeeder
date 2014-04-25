<?php
echo "<?php\n";
?>
/**
* Seed class <?php echo $class; ?>Seed
*/
class <?php echo $class; ?>Seed {

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
* Method that is called to generate data
*/
	public function generate() {
		$data = array();
		return $data;
	}

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