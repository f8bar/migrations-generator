<?php namespace Xethron\MigrationsGenerator\Generators;

use DB;
use Doctrine\DBAL\Types\Type;

class SchemaGenerator {

	/**
	 * @var \Doctrine\DBAL\Schema\AbstractSchemaManager
	 */
	protected $schema;

	/**
	 * @var FieldGenerator
	 */
	protected $fieldGenerator;

	/**
	 * @var ForeignKeyGenerator
	 */
	protected $foreignKeyGenerator;

	/**
	 * @var string
	 */
	protected $database;
	/**
	 * @var bool
	 */
	private $ignoreIndexNames;
	/**
	 * @var bool
	 */
	private $ignoreForeignKeyNames;

	/**
	 * @param string $database
	 * @param bool   $ignoreIndexNames
	 * @param bool   $ignoreForeignKeyNames
	 */
	public function __construct($database, $ignoreIndexNames, $ignoreForeignKeyNames)
	{
		$connection = DB::connection($database)->getDoctrineConnection();
		$connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
		$connection->getDatabasePlatform()->registerDoctrineTypeMapping('bit', 'boolean');
		
		Type::addType('address_type', 'Doctrine\DBAL\Types\EnumAddressType');
		$conn->getDatabasePlatform()->registerDoctrineTypeMapping('EnumAddressType', 'address_type');
		Type::addType('authentication_method', 'Doctrine\DBAL\Types\EnumAuthenticationMethod');
		$conn->getDatabasePlatform()->registerDoctrineTypeMapping('EnumAuthenticationMethod', 'authentication_method');
		Type::addType('data_type', 'Doctrine\DBAL\Types\EnumDataType');
		$conn->getDatabasePlatform()->registerDoctrineTypeMapping('EnumDataType', 'data_type');
		Type::addType('language', 'Doctrine\DBAL\Types\EnumLanguage');
		$conn->getDatabasePlatform()->registerDoctrineTypeMapping('EnumLanguage', 'language');
		
		$this->database = $connection->getDatabase();

		$this->schema = $connection->getSchemaManager();
		$this->fieldGenerator = new FieldGenerator();
		$this->foreignKeyGenerator = new ForeignKeyGenerator();

		$this->ignoreIndexNames = $ignoreIndexNames;
		$this->ignoreForeignKeyNames = $ignoreForeignKeyNames;
	}

	/**
	 * @return mixed
	 */
	public function getTables()
	{
		return $this->schema->listTableNames();
	}

	public function getFields($table)
	{
		return $this->fieldGenerator->generate($table, $this->schema, $this->database, $this->ignoreIndexNames);
	}

	public function getForeignKeyConstraints($table)
	{
		return $this->foreignKeyGenerator->generate($table, $this->schema, $this->ignoreForeignKeyNames);
	}

}
