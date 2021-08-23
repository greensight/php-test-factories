<?php

namespace Greensight\TestFactories;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;

abstract class Factory
{
    protected array $only = [];
    protected array $except = [];

    abstract protected function definition(): array;

    abstract public function make(array $extra = []);

    public function __construct(protected Generator $faker)
    {
    }

    public static function new(): static
    {
        $faker = class_exists(Container::class)
            ? Container::getInstance()->make(Generator::class)
            : FakerFactory::create();

        return new static($faker);
    }

    public function makeSeveral(int $count, array $extra = []): Collection
    {
        return collect()
            ->times($count)
            ->map(fn () => $this->make($extra));
    }

    public function only(array $fields): static
    {
        return $this->immutableSet('only', $fields);
    }

    public function except(array $fields): static
    {
        return $this->immutableSet('except', $fields);
    }

    protected function prepareDefinition(): array
    {
        return $this->removeMissingValues(
            $this->removeNotSpecifiedInOnly(
                $this->removeSpecifiedInExcept(
                    $this->definition()
                )
            )
        );
    }

    protected function makeArray(array $extra = []): array
    {
        return $this->mergeDefinitionWithExtra($extra);
    }

    protected function mergeDefinitionWithExtra(array $extra): array
    {
        return array_merge($this->prepareDefinition(), $extra);
    }

    protected function immutableSet(string $field, $value): static
    {
        $clone = clone $this;
        $clone->$field = $value;

        return $clone;
    }

    protected function when(bool $condition, $value, $default = null): mixed
    {
        if ($condition) {
            return value($value);
        }

        return func_num_args() === 3 ? value($default) : new FactoryMissingValue;
    }

    protected function whenNotNull($condition, $value, $default = null): mixed
    {
        return $this->when($condition !== null, $value, $default);
    }

    protected function executed($condition, $value, $default = null): mixed
    {
        return $this->when($condition !== null, $value, $default);
    }

    protected function removeMissingValues(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value instanceof PotentiallyMissing && $value->isMissing()) {
                unset($data[$key]);
            }

            if (is_array($value)) {
                $data[$key] = $this->removeMissingValues($value);
            }
        }

        return $data;
    }

    protected function removeNotSpecifiedInOnly(array $data): array
    {
        return $this->only ? Arr::only($data, $this->only) : $data;
    }

    protected function removeSpecifiedInExcept(array $data): array
    {
        return $this->except ? Arr::except($data, $this->except) : $data;
    }

    protected function executeNested(Factory|array|Collection|null $nested, $default = null): mixed
    {
        if ($nested === null) {
            return value($default);
        }

        if ($nested instanceof Factory) {
            return $nested->make();
        }

        if (is_array($nested)) {
            return array_map(fn ($factory) => $factory->make(), $nested);
        }

        if ($nested instanceof Collection) {
            return $nested->map(fn ($factory) => $factory->make())->all();
        }

        throw new InvalidArgumentException("Argument must be a Factory|array<Factory>|Collection<Factory>|null");
    }
}
