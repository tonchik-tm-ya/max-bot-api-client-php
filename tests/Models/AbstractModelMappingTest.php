<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models;

use BushlanovDev\MaxMessengerBot\Attributes\ArrayOf;
use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Models\AbstractModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractModel::class)]
#[CoversClass(ArrayOf::class)]
final class AbstractModelMappingTest extends TestCase
{
    #[Test]
    public function itCorrectlyCastsArrayOfEnums(): void
    {
        $result = DummyModelForMapping::fromArray([
            'name' => 'Test With Enums',
            'update_types' => ['message_created', 'bot_started'],
        ]);

        $this->assertInstanceOf(DummyModelForMapping::class, $result);
        $this->assertIsArray($result->updateTypes);
        $this->assertCount(2, $result->updateTypes);
        $this->assertInstanceOf(UpdateType::class, $result->updateTypes[0]);
        $this->assertSame(UpdateType::MessageCreated, $result->updateTypes[0]);
        $this->assertSame(UpdateType::BotStarted, $result->updateTypes[1]);
    }

    #[Test]
    public function itCorrectlyCastsArrayOfModels(): void
    {
        $result = DummyModelForMapping::fromArray([
            'name' => 'Test With Models',
            'child_models' => [
                ['value' => 'child 1'],
                ['value' => 'child 2'],
            ],
        ]);

        $this->assertIsArray($result->childModels);
        $this->assertCount(2, $result->childModels);
        $this->assertInstanceOf(DummyChildModel::class, $result->childModels[0]);
        $this->assertSame('child 1', $result->childModels[0]->value);
    }

    #[Test]
    public function itReturnsRawArrayWhenAttributeIsMissing(): void
    {
        $result = DummyModelForMapping::fromArray(['untyped_array' => ['a', 'b', 'c']]);

        $this->assertIsArray($result->untypedArray);
        $this->assertSame(['a', 'b', 'c'], $result->untypedArray);
    }

    #[Test]
    public function itHandlesEmptyArraysCorrectly(): void
    {
        $result = DummyModelForMapping::fromArray([
            'name' => 'Test with Empty',
            'update_types' => [],
        ]);

        $this->assertIsArray($result->updateTypes);
        $this->assertEmpty($result->updateTypes);
    }

    #[Test]
    public function itHandlesNullForNullableArray(): void
    {
        $result = DummyModelForMapping::fromArray(['child_models' => null]);

        $this->assertNull($result->childModels);
    }
}

final readonly class DummyModelForMapping extends AbstractModel
{
    public function __construct(
        public ?string $name,
        #[ArrayOf(UpdateType::class)]
        public ?array $updateTypes,
        #[ArrayOf(DummyChildModel::class)]
        public ?array $childModels,
        public ?array $untypedArray,
    )
    {
    }
}

final readonly class DummyChildModel extends AbstractModel
{
    public function __construct(public string $value)
    {
    }
}
