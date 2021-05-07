<?php

namespace App\Tests\Command;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use App\Command\PostsCreateCommand;

/**
 * Class PostsCreateCommandTest
 * @package App\Tests\Command
 *
 * Theee's not much to do unit tests on here, since most methods touch the DB.
 * Still, I can at least test the function that grabs an object from the API.
 * That could be useful if we have a blocked port, for example.
 * EDIT: I guess relying on an external service makes this an integration test.
 * So be it.
 */
class PostsCreateCommandTest extends TestCase
{
    private MockObject $inputMock;

    private MockObject $entityManagerMock;

    private PostsCreateCommand $command;

    public function setUp(): void
    {
        $this->inputMock = $this->createMock(
            InputInterface::class
        );
        /** @var MockObject $input */
        $this->inputMock->method('getOption')->willReturn(false);

        $this->entityManagerMock = $this->createMock(
            EntityManagerInterface::class
        );

        $this->command = new PostsCreateCommand($this->entityManagerMock);

        parent::setUp();
    }

    public function testGetNextEndpointAsObjReturnsObject()
    {
        self::assertEquals(
            gettype(
                $this->command->getNextEndpointAsObj($this->inputMock)
            ), 'object'
        );
    }

    public function testGetNextEndpointAsObjReturnsCorrectClass()
    {
        self::assertEquals(
            get_class(
                $this->command->getNextEndpointAsObj($this->inputMock)
            ), 'stdClass'
        );
    }
}
