# PHP Test factories

Define factories to generate any kind of object or even arrays for unit tests.

## Installation

You can install the package via composer:

`composer require greensight/test-factories`

## Basic usage

Let's create a factory and extend abstract Factory.
All you need is to define `definition` and `make` methods.

```php
use Greensight\LaravelTestFactories\Factory;

class CustomerFactory extends Factory
{
    public ?int $id = null;
    public ?FileFactory $avatarFactory = null;
    public ?array $addressFactories = null;

    protected function definition(): array
    {
        return [
            'id' => $this->whenNotNull($this->id, $this->id),
            'user_id' => $this->faker->unique()->randomNumber(),
            'avatar' => $this->avatarFactory?->make(),
            'addresses' => $this->executeNested($this->addressFactories, new FactoryMissingValue()),
        ];
    }

    public function make(array $extra = []): array
    {
        static::$index += 1;

        return new CustomerDTO($this->mergeDefinitionWithExtra($extra));
    }

    public function withId(?int $id = null): self
    {
        return $this->immutableSet('id', $id ?? $this->faker->randomNumber());
    }

    public function withAvatar(FileFactory $factory = null): self
    {
        return $this->immutableSet('avatarFactory', $factory ?? FileFactory::new());
    }

    public function includesAddresses(?array $factories = null): self
    {
        return $this->immutableSet('addressFactories', $factories ?? [CustomerAddressFactory::new()]);
    }
}

// Now we can use Factory like that
$customerData1 = CustomerFactory::new()->make();
$customerData2 = CustomerFactory::new()->withId()->withAvatar(FileFactory::new()->someCustomMethod())->make();
```

As you can see the package uses `fakerphp/faker` to generate test data.

You can override any fields in `make` method:

```php
$customerData1 = CustomerFactory::new()->make(['user_id' => 2]);
```

If you target is an array, then you can use a helper method `makeArray`:

```php
    public function make(array $extra = []): array
    {
        return $this->makeArray($extra);
    }
```

It's recommended to use `$this->immutableSet` in state change methods to make sure previously created factories are not affected.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

### Testing

1. composer install
2. npm i
3. Start Elasticsearch in your preferred way.
4. Copy `phpunit.xml.dist` to `phpunit.xml` and set correct env variables there
6. composer test

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
