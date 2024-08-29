<?php

use Tatter\Schemas\Drafter\BaseDrafter;
use Tatter\Schemas\Structures\Field;
use Tatter\Schemas\Structures\Mergeable;
use Tatter\Schemas\Structures\Table;
use Tests\Support\SchemasTestCase;

/**
 * @internal
 */
final class BaseDrafterTest extends SchemasTestCase
{
    protected ?BaseDrafter $handler = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new class ($this->config) extends BaseDrafter {};
    }

    public function testFindKeyToForeignTable()
    {
        $table  = new Table('machines');
        $method = $this->getPrivateMethodInvoker($this->handler, 'findKeyToForeignTable');

        $fields          = new Mergeable();
        $fields->factory = new Field();
        $fields->serial  = new Field();
        $table->fields   = $fields;

        $this->assertSame('factory', $method($table, 'factories'));

        $fields             = new Mergeable();
        $fields->factory_id = new Field();
        $fields->serial     = new Field();
        $table->fields      = $fields;

        $this->assertSame('factory_id', $method($table, 'factories'));

        $fields            = new Mergeable();
        $fields->factories = new Field();
        $fields->serial    = new Field();
        $table->fields     = $fields;

        $this->assertSame('factories', $method($table, 'factories'));

        $fields               = new Mergeable();
        $fields->factories_id = new Field();
        $fields->serial       = new Field();
        $table->fields        = $fields;

        $this->assertSame('factories_id', $method($table, 'factories'));
    }

    public function testNotFindKeyToForeignTable()
    {
        $table  = new Table('machines');
        $method = $this->getPrivateMethodInvoker($this->handler, 'findKeyToForeignTable');

        $fields            = new Mergeable();
        $fields->factories = new Field();
        $fields->serial    = new Field();
        $table->fields     = $fields;

        $this->assertNull($method($table, 'lawyers'));
    }

    public function testFindPrimaryKeyActual()
    {
        $table  = new Table('machines');
        $method = $this->getPrivateMethodInvoker($this->handler, 'findPrimaryKey');

        $field              = new Field('machine_id');
        $field->primary_key = true;

        $fields             = new Mergeable();
        $fields->machine_id = $field;
        $fields->serial     = new Field();
        $table->fields      = $fields;

        $this->assertSame('machine_id', $method($table));
    }

    public function testFindPrimaryKeyImplied()
    {
        $table  = new Table('machines');
        $method = $this->getPrivateMethodInvoker($this->handler, 'findPrimaryKey');

        $fields         = new Mergeable();
        $fields->id     = new Field('id');
        $fields->serial = new Field();
        $table->fields  = $fields;

        $this->assertSame('id', $method($table));
    }

    public function testNotFindPrimaryKey()
    {
        $table  = new Table('machines');
        $method = $this->getPrivateMethodInvoker($this->handler, 'findPrimaryKey');

        $fields          = new Mergeable();
        $fields->primary = new Field('primary');
        $fields->serial  = new Field();
        $table->fields   = $fields;

        $this->assertNull($method($table));
    }
}
