<?php

use CodeIgniter\Test\DatabaseTestTrait;
use Config\Services;
use Tatter\Schemas\Archiver\Handlers\CacheHandler as CacheArchiver;
use Tatter\Schemas\Drafter\Handlers\DatabaseHandler;
use Tatter\Schemas\Drafter\Handlers\DirectoryHandler;
use Tatter\Schemas\Drafter\Handlers\ModelHandler;
use Tests\Support\Database\Seeds\TestSeeder;
use Tests\Support\SchemasTestCase;

/**
 * @internal
 */
final class LiveTest extends SchemasTestCase
{
    use DatabaseTestTrait;

    // Configure the database to be migrated and seeded once
    protected $migrateOnce = true;
    protected $seedOnce    = true;
    protected $seed        = TestSeeder::class;
    protected $basePath    = SUPPORTPATH . 'Database/';

    // Probably a quite common scenario
    public function testDatabaseToCache()
    {
        $cache           = Services::cache();
        $databaseHandler = new DatabaseHandler($this->config, 'tests');
        $cacheHandler    = new CacheArchiver($this->config, $cache);

        $this->schemas->draft([$databaseHandler])->archive([$cacheHandler]);
        $this->assertEmpty($this->schemas->getErrors());

        $schemaFromService = $this->schemas->get();
        $schemaFromCache   = $cache->get('schema-testing');
        $this->assertCount(is_countable($schemaFromCache->tables) ? count($schemaFromCache->tables) : 0, $schemaFromService->tables);

        $this->assertTrue(property_exists($schemaFromService->tables, 'factories'));
    }

    public function testDatabaseMergeFile()
    {
        if ($this->db->DBDriver === 'SQLite3') {
            $this->markTestSkipped('SQLite3 does not always support foreign key reads.');
        }

        $databaseHandler = new DatabaseHandler($this->config, 'tests');
        $fileHandler     = new DirectoryHandler($this->config);

        $schema = $this->schemas->draft([$databaseHandler, $fileHandler])->get();

        $this->assertTrue(property_exists($schema->tables, 'products'));
        $this->assertCount(3, $schema->tables->workers->relations);
    }

    public function testMergeAllDrafters()
    {
        if ($this->db->DBDriver === 'SQLite3') {
            $this->markTestSkipped('SQLite3 does not always support foreign key reads.');
        }

        $databaseHandler = new DatabaseHandler($this->config, 'tests');
        $modelHandler    = new ModelHandler($this->config);
        $fileHandler     = new DirectoryHandler($this->config);

        $schema = $this->schemas->draft([$databaseHandler, $modelHandler, $fileHandler])->get();

        $this->assertTrue(property_exists($schema->tables, 'products'));
        $this->assertSame('Tests\Support\Models\FactoryModel', $schema->tables->factories->model);
        $this->assertCount(3, $schema->tables->workers->relations);
    }

    public function testGetReturnsSchemaWithReader()
    {
        // Draft & archive a copy of the schema so we can test reading it
        $result = $this->schemas->draft()->archive();
        $this->assertTrue($result);

        $this->schemas->reset();

        $schema = $this->schemas->read()->get();

        $this->assertInstanceOf('\Tatter\Schemas\Reader\BaseReader', $schema->tables); // @phpstan-ignore-line
    }

    public function testAutoRead()
    {
        if ($this->db->DBDriver === 'SQLite3') {
            $this->markTestSkipped('SQLite3 does not always support foreign key reads.');
        }

        $this->config->automate['read'] = true;

        // Draft & archive a copy of the schema so we can test reading it
        $result = $this->schemas->draft()->archive();
        $this->assertTrue($result);

        $this->schemas->reset();

        $schema = $this->schemas->get();

        $this->assertSame('Tests\Support\Models\FactoryModel', $schema->tables->factories->model);
        $this->assertCount(3, $schema->tables->workers->relations);
    }
}
