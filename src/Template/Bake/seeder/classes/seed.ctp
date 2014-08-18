<?php
echo "<?php\n";
echo 'namespace ' . $namespace . "\\Seed;\n";
echo "\n";
echo "use DataSeeder\\Seed\\BaseSeed\n\n";
?>
/**
 * Seed class <?php echo $name; ?>Seed
 */
class <?php echo $name; ?>Seed extends BaseSeed {

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
	public $uses = [];

/**
 * Data to create
 *
 * @var array
 */
	public $data = [];

/**
 * Method that is called when creating data
 */
	protected function _up() {
	}

/**
 * Method that is called when deleting data
 */
	protected function _down() {
	}
}