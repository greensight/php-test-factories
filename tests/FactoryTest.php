<?php

use Greensight\TestFactories\Tests\Stubs\TestArrayFactory;
use Greensight\TestFactories\Tests\Stubs\TestObjectDTO;
use Greensight\TestFactories\Tests\Stubs\TestObjectFactory;
use Illuminate\Support\Collection;

test('testObjectFactory can create object', function () {
    $id = 5;
    $result = TestObjectFactory::new()->withId($id)->make();

    expect($result)->toBeInstanceOf(TestObjectDTO::class);
    expect($result->fields['id'])->toEqual($id);
});

test('testObjectFactory can create object and overrid fields in make', function () {
    $id = 5;
    $id2 = 2;
    $result = TestObjectFactory::new()->withId($id)->make(['id' => $id2]);

    expect($result)->toBeInstanceOf(TestObjectDTO::class);
    expect($result->fields['id'])->toEqual($id2);
});

test('testArrayFactory can create arrays', function () {
    $id = 5;
    $result = TestArrayFactory::new()->withId($id)->make();

    expect($result)->toBeArray();
    expect($result['id'])->toEqual($id);
});

test('makeSeveral works', function () {
    $id = 5;
    $count = 3;
    $result = TestObjectFactory::new()->makeSeveral($count, ['id' => $id]);

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result)->toHaveCount($count);
    $result->each(fn ($x) => expect($x->fields['id'])->toEqual($id));
});
