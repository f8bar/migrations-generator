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
		$connection->getDatabasePlatform()->registerDoctrineTypeMapping('json', 'string');
		
		Type::addType('address_type', 'Doctrine\DBAL\Types\EnumAddressType');
		$connection->getDatabasePlatform()->registerDoctrineTypeMapping('address_type', 'address_type');
		Type::addType('authentication_method', 'Doctrine\DBAL\Types\EnumAuthenticationMethod');
		$connection->getDatabasePlatform()->registerDoctrineTypeMapping('authentication_method', 'authentication_method');
		Type::addType('data_type', 'Doctrine\DBAL\Types\EnumDataType');
		$connection->getDatabasePlatform()->registerDoctrineTypeMapping('data_type', 'data_type');
		Type::addType('language', 'Doctrine\DBAL\Types\EnumLanguage');
		$connection->getDatabasePlatform()->registerDoctrineTypeMapping('language', 'language');
		
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
		$tables = $this->schema->listTableNames();
		if (is_array($tables))
		{
			for ($i = 0; $i < count($tables); $i++)
			{
				if (preg_match('/"/', $tables[$i]))
				{
					$tables[$i] = str_replace('"', '', $tables[$i]);
				}
			}
		}
		return $tables;
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
