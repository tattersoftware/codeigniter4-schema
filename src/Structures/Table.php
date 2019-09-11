<?php namespace Tatter\Schemas\Structures;

class Table
{
	/**
	 * The table name.
	 *
	 * @var ?string
	 */
	public $name;
	
	/**
	 * Whether the table is a pivot.
	 *
	 * @var bool
	 */
	public $pivot = false;
	
	/**
	 * The table's fields.
	 *
	 * @var array of Field objects
	 */
	public $fields = [];
	
	/**
	 * The table's indices.
	 *
	 * @var array of Index objects
	 */
	public $indexes = [];
	
	/**
	 * The table's foreign keys.
	 *
	 * @var array of ForeignKey objects
	 */
	public $foreignKeys = [];
	
	/**
	 * Relationships this table has with others
	 *
	 * @var array of Relations
	 */
	public $relations = [];
	
	public function __construct($name = null)
	{
		$this->name = $name;
	}
	
	/**
	 * Merges data from one table into the other; latter overwrites.
	 *
	 * @return $this
	 */
	public function merge(Table $table): Table
	{
		$this->name  = $table->name;
		$this->pivot = $this->pivot || $table->pivot;
		
		foreach ($schema->tables as $tableName => $table)
		{
			if (isset($this->tables[$tableName]))
			{
				$this->tables[$tableName] = $this->tables[$tableName]->merge($table);
			}
			else
			{
				$this->tables[$tableName] = $table;
			}
		}
		
		return $this;
	}
}
