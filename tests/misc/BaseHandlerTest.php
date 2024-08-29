<?php

use Tatter\Schemas\BaseHandler;
use Tests\Support\SchemasTestCase;

/**
 * @internal
 */
final class BaseHandlerTest extends SchemasTestCase
{
    protected ?BaseHandler $handler = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new class ($this->config) extends BaseHandler {};
    }

    public function testGetErrors()
    {
        $this->assertSame([], $this->handler->getErrors());
    }
}
