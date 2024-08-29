<?php

use CodeIgniter\Test\DatabaseTestTrait;
use Tatter\Schemas\Config\Schemas;
use Tatter\Schemas\Drafter\Handlers\DatabaseHandler;
use Tests\Support\Database\Seeds\TestSeeder;
use Tests\Support\SchemasTestCase;

/**
 * @internal
 */
final class DatabaseDrafterTest extends SchemasTestCase
{
    use DatabaseTestTrait;

    // Configure the database to be migrated and seeded once
    protected $migrateOnce = true;
    protected $seedOnce    = true;
    protected $seed        = TestSeeder::class;
    protected $basePath    = SUPPORTPATH . 'Database/';
    private DatabaseHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new DatabaseHandler($this->config, 'tests');
        $this->schema  = $this->handler->draft();
    }

    public function testHasAllTables()
    {
        $this->assertCount(8, $this->schema->tables);
    }

    public function testHasSpecificTable()
    {
        $this->assertTrue(property_exists($this->schema->tables, 'factories'));
    }

    public function testDetectsPivotTablesWithFK()
    {
        $this->assertTrue($this->schema->tables->factories_workers->pivot);
    }

    public function testDetectsPivotTablesWithoutFK()
    {
        $this->assertTrue($this->schema->tables->machines_servicers->pivot);
    }

    public function testIgnoredTables()
    {
        $this->assertFalse(property_exists($this->schema->tables, 'migrations'));

        $config                = new Schemas();
        $config->ignoredTables = [];

        $handler = new DatabaseHandler($config, 'tests');
        $schema  = $handler->draft();

        $this->assertTrue(property_exists($schema->tables, 'migrations'));
    }

    // -------------------- RELATIONSHIPS --------------------

    public function testDetectsAllRelationships()
    {
        if ($this->db->DBDriver === 'SQLite3') {
            $this->markTestSkipped('SQLite3 does not always support foreign key reads.');
        }

        $relationsCount = 0;

        foreach ($this->schema->tables as $table) {
            $relationsCount += is_countable($table->relations) ? count($table->relations) : 0;
        }

        $this->assertSame(14, $relationsCount);
    }

    public function testBelongsTo()
    {
        $table1 = $this->schema->tables->lawyers;
        $table2 = $this->schema->tables->servicers;

        $this->assertSame('belongsTo', $table1->relations->{$table2->name}->type);

        $pivot = [
            'lawyers',
            'servicer_id',
            'servicers',
            'id',
        ];
        $this->assertSame([$pivot], $table1->relations->{$table2->name}->pivots);
    }

    public function testHasMany()
    {
        $table1 = $this->schema->tables->servicers;
        $table2 = $this->schema->tables->lawyers;

        $this->assertSame('hasMany', $table1->relations->{$table2->name}->type);

        $pivot = [
            'servicers',
            'id',
            'lawyers',
            'servicer_id',
        ];
        $this->assertSame([$pivot], $table1->relations->{$table2->name}->pivots);
    }

    public function testHasManyFromForeignKey()
    {
        if ($this->db->DBDriver === 'SQLite3') {
            $this->markTestSkipped('SQLite3 does not always support foreign key reads.');
        }

        $table1 = $this->schema->tables->workers;
        $table2 = $this->schema->tables->lawsuits;

        $this->assertSame('hasMany', $table1->relations->{$table2->name}->type);

        $pivot = [
            'workers',
            'id',
            'lawsuits',
            'client',
        ];
        $this->assertSame([$pivot], $table1->relations->{$table2->name}->pivots);
    }

    public function testManyToMany()
    {
        $table1 = $this->schema->tables->servicers;
        $table2 = $this->schema->tables->machines;

        $this->assertSame('manyToMany', $table1->relations->{$table2->name}->type);
        $this->assertSame('manyToMany', $table2->relations->{$table1->name}->type);

        $pivot1 = [
            'servicers',
            'id',
            'machines_servicers',
            'servicer_id',
        ];
        $pivot2 = [
            'machines_servicers',
            'machine_id',
            'machines',
            'id',
        ];
        $this->assertSame([$pivot1, $pivot2], $table1->relations->{$table2->name}->pivots);

        $pivot1 = [
            'machines',
            'id',
            'machines_servicers',
            'machine_id',
        ];
        $pivot2 = [
            'machines_servicers',
            'servicer_id',
            'servicers',
            'id',
        ];
        $this->assertSame([$pivot1, $pivot2], $table2->relations->{$table1->name}->pivots);
    }
}
