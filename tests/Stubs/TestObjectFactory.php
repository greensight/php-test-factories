<?php

namespace Greensight\TestFactories\Tests\Stubs;

use Greensight\TestFactories\Factory;

class TestObjectFactory extends Factory
{
    public ?int $id = null;

    protected function definition(): array
    {
        return [
            'id' => $this->whenNotNull($this->id, $this->id),
            'user_id' => $this->faker->randomNumber(),
        ];
    }

    public function make(array $extra = [])
    {
        return new TestObjectDTO($this->mergeDefinitionWithExtra($extra));
    }

    public function withId(?int $id = null): self
    {
        return $this->immutableSet('id', $id ?? $this->faker->randomNumber());
    }
}
