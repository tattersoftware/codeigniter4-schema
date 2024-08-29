<?php

use Tatter\Schemas\Drafter\Handlers\ModelHandler;
use Tests\Support\SchemasTestCase;

/**
 * @internal
 */
final class ModelDrafterTest extends SchemasTestCase
{
    private ?ModelHandler $handler = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new ModelHandler($this->config);
    }

    public function testSetGroup()
    {
        $this->assertSame('tests', $this->handler->getGroup());
        $this->handler->setGroup('foobar');
        $this->assertSame('foobar', $this->handler->getGroup());
    }

    public function testGetModels()
    {
        $method = $this->getPrivateMethodInvoker($this->handler, 'getModels');
        $models = $method($this->handler);
        $this->assertCount(4, $models, implode(PHP_EOL, $models));
        $this->assertContains('Tests\Support\Models\FactoryModel', $models);
    }

    public function testGetModelsRespectsGroup()
    {
        $this->handler->setGroup('default');

        $method = $this->getPrivateMethodInvoker($this->handler, 'getModels');
        $models = $method($this->handler);
        $this->assertCount(0, $models);
    }

    public function testDraftsSchemaFromModels()
    {
        $schema = $this->handler->draft();

        $this->assertSame('servicers', $schema->tables->servicers->name);
        $this->assertSame('Tests\Support\Models\WorkerModel', $schema->tables->workers->model);
        $this->assertCount(6, $schema->tables->machines->fields);
        $this->assertTrue($schema->tables->factories->fields->id->primary_key);
    }
}
