<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use DateTime;
use Exception;
use Mockery as m;
use Doctrine\Common\Collections\ArrayCollection as Collection;
use Symfony\Component\Validator\Validation;
use App\Tests\TestCase;

/**
 * Class EntityBase
 * @group model
 */
class EntityBase extends TestCase
{
    protected object $object;

    /**
     * Remove all mock objects
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->object);
    }

    /**
     * Engage the symfony validator and test the object.
     * @param int $expectedCount how many errors are you expecting
     */
    protected function validate(int $expectedCount): array
    {
        $validator = Validation::createValidatorBuilder()
                ->enableAttributeMapping()
                ->getValidator();
        $errors = $validator->validate($this->object);
        $errorCount = count($errors);
        $parsedErrors = [];
        foreach ($errors as $error) {
            $parsedErrors[$error->getPropertyPath()] = $error->getMessage();
        }
        $this->assertEquals(
            $errorCount,
            $expectedCount,
            "Expected {$expectedCount} errors, found {$errorCount}: " .
            var_export($parsedErrors, true)
        );

        return $parsedErrors;
    }

    protected function validateNotBlanks(array $fields): void
    {

        $errors = $this->validate(count($fields));

        foreach ($fields as $key) {
            $this->assertTrue(
                array_key_exists($key, $errors),
                "{$key} key not found in errors: " . var_export(array_keys($errors), true)
            );
            $this->assertSame('This value should not be blank.', $errors[$key]);
        }
    }

    protected function validateNotNulls(array $fields): void
    {

        $errors = $this->validate(count($fields));

        foreach ($fields as $key) {
            $this->assertTrue(
                array_key_exists($key, $errors),
                "{$key} key not found in errors: " . var_export(array_keys($errors), true)
            );
            $this->assertSame('This value should not be null.', $errors[$key]);
        }
    }

    /**
     * A generic test for entity setters.
     *
     * @param string $property
     * @param string $type
     */
    protected function basicSetTest(string $property, string $type): void
    {
        $setMethod = $this->getSetMethodForProperty($property);
        $getMethod = $this->getGetMethodForProperty($property);
        $this->assertTrue(method_exists($this->object, $setMethod), "Method {$setMethod} missing");
        $this->assertTrue(method_exists($this->object, $getMethod), "Method {$getMethod} missing");
        $expected = $this->getValueForType($type);
        $this->object->$setMethod($expected);
        $this->assertSame($expected, $this->object->$getMethod());
    }

    /**
     * A generic test for boolean entity setters.
     *
     * @param string $property
     * @param bool $is should we use is vs has when generating the method.
     */
    protected function booleanSetTest(string $property, bool $is = true): void
    {
        $setMethod = $this->getSetMethodForProperty($property);
        $isMethod = $is ? $this->getIsMethodForProperty($property) : $this->getHasMethodForProperty($property);
        $this->assertTrue(method_exists($this->object, $setMethod), "Method {$setMethod} missing");
        $this->assertTrue(method_exists($this->object, $isMethod), "Method {$isMethod} missing");
        $expected = $this->getValueForType('boolean');
        $this->object->$setMethod($expected);
        $this->assertSame($expected, $this->object->$isMethod());
    }

    /**
     * A generic test for entity setters which use other entites
     *
     * @param string $property
     * @param string $entityName
     */
    protected function entitySetTest(string $property, string $entityName): void
    {
        $setMethod = $this->getSetMethodForProperty($property);
        $getMethod = $this->getGetMethodForProperty($property);
        $this->assertTrue(method_exists($this->object, $getMethod), "Method {$getMethod} missing");
        $this->assertTrue(method_exists($this->object, $setMethod), "Method {$setMethod} missing");
        $obj = m::mock('App\Entity\\' . $entityName);
        $this->object->$setMethod($obj);
        $this->assertSame($obj, $this->object->$getMethod());
    }

    /**
     * A generic test for setters for collections.
     * @todo should we mock Collection when passing it to the setMethod?
     *
     * @param string $property
     * @param string $entityName
     * @param string|bool $getter name of the method to use instead of a generated method, or FALSE if n/a.
     * @param string|bool $setter name of the method to use instead of a generated method, or FALSE if n/a.
     * @param string|bool $crossSaveMethod name of the method to call on the inverse side of the relationship.
     */
    protected function entityCollectionSetTest(
        string $property,
        string $entityName,
        string|bool $getter = false,
        string|bool $setter = false,
        string|bool $crossSaveMethod = false
    ): void {
        $getMethod = $getter ? $getter : $this->getGetMethodForCollectionProperty($property);
        $setMethod = $setter ? $setter : $this->getSetMethodForCollectionProperty($property);
        $this->assertTrue(method_exists($this->object, $setMethod), "Method {$setMethod} missing");
        $this->assertTrue(method_exists($this->object, $getMethod), "Method {$getMethod} missing");
        $arr = $this->getArrayOfMockObjects('App\Entity\\' . $entityName, 10);
        if ($crossSaveMethod) {
            foreach ($arr as $obj) {
                $obj->shouldReceive($crossSaveMethod)->with($this->object)->once();
            }
        }
        $collection = new Collection($arr);
        $this->object->$setMethod($collection);
        $results = $this->object->$getMethod();
        $this->assertTrue($results instanceof Collection, 'Collection not returned.');

        foreach ($arr as $obj) {
            $this->assertTrue($results->contains($obj));
        }
    }


    /**
     * A generic test for entity setters which hold collections of other entities.
     *
     * @param string $property
     * @param string $entityName
     * @param string|bool $getter name of the method to use instead of a generated method, or FALSE if n/a.
     * @param string|bool $setter name of the method to use instead of a generated method, or FALSE if n/a.
     * @param string|bool $crossSaveMethod name of the method to call on the inverse side of the relationship.
     */
    protected function entityCollectionAddTest(
        string $property,
        string $entityName,
        string|bool $getter = false,
        string|bool $setter = false,
        string|bool $crossSaveMethod = false
    ): void {
        $arr = $this->getArrayOfMockObjects('App\Entity\\' . $entityName, 10);
        $addMethod = $setter ? $setter : $this->getAddMethodForProperty($property);
        $getMethod = $getter ? $getter : $this->getGetMethodForCollectionProperty($property);
        $this->assertTrue(method_exists($this->object, $addMethod), "Method {$addMethod} missing");
        $this->assertTrue(method_exists($this->object, $getMethod), "Method {$getMethod} missing");
        foreach ($arr as $obj) {
            if ($crossSaveMethod) {
                $obj->shouldReceive($crossSaveMethod)->with($this->object)->once();
            }
            $this->object->$addMethod($obj);
        }
        $results = $this->object->$getMethod();
        $this->assertTrue($results instanceof Collection, 'Collection not returned.');

        foreach ($arr as $obj) {
            $this->assertTrue($results->contains($obj));
        }
    }

    /**
     * A generic test for entity setters which hold collections of other entities.
     *
     * @param string $property
     * @param string $entityName
     * @param string|bool $getter name of the method to use instead of a generated method, or FALSE if n/a.
     * @param string|bool $adder name of the method used to add instead of a generated method, or FALSE if n/a.
     * @param string|bool $remover name of the method to use instead of a generated method, or FALSE if n/a.
     * @param string|bool $crossSaveMethod name of the method to call on the inverse side of the relationship.
     */
    protected function entityCollectionRemoveTest(
        string $property,
        string $entityName,
        string|bool $getter = false,
        string|bool $adder = false,
        string|bool $remover = false,
        string|bool $crossSaveMethod = false
    ): void {
        $arr = $this->getArrayOfMockObjects('App\Entity\\' . $entityName, 10);
        $addMethod = $adder ? $adder : $this->getAddMethodForProperty($property);
        $removeMethod = $remover ? $remover : $this->getRemoveMethodForProperty($property);
        $getMethod = $getter ? $getter : $this->getGetMethodForCollectionProperty($property);
        $this->assertTrue(method_exists($this->object, $addMethod), "Method {$addMethod} missing from {$entityName}");
        $this->assertTrue(
            method_exists($this->object, $removeMethod),
            "Method {$removeMethod} missing from {$entityName}"
        );
        $this->assertTrue(method_exists($this->object, $getMethod), "Method {$getMethod} missing from {$entityName}");

        foreach ($arr as $obj) {
            $obj->shouldIgnoreMissing();
            $this->object->$addMethod($obj);
        }
        $results = $this->object->$getMethod();
        $this->assertTrue($results instanceof Collection, 'Collection not returned.');
        foreach ($arr as $obj) {
            if ($crossSaveMethod) {
                $obj->shouldReceive($crossSaveMethod)->with($this->object)->once();
            }
            $this->assertTrue($results->contains($obj));
        }

        foreach ($arr as $obj) {
            $this->object->$removeMethod($obj);
        }
        $results = $this->object->$getMethod();
        $this->assertTrue($results instanceof Collection, 'Collection not returned.');
        foreach ($arr as $obj) {
            $this->assertTrue(!$results->contains($obj), 'Entity was not removed correctly');
        }
    }

    protected function getArrayOfMockObjects(string $className, int $count): array
    {
        $arr = [];
        for ($i = 0; $i < $count; $i++) {
            $arr[] = m::mock($className);
        }

        return $arr;
    }

    protected function getSetMethodForProperty(string $property): string
    {
        return 'set' . ucfirst($property);
    }

    protected function getGetMethodForProperty(string $property): string
    {
        return 'get' . ucfirst($property);
    }

    protected function getIsMethodForProperty(string $property): string
    {
        return 'is' . ucfirst($property);
    }

    protected function getHasMethodForProperty(string $property): string
    {
        return 'has' . ucfirst($property);
    }

    protected function getGetMethodForCollectionProperty(string $property): string
    {
        return 'get' . ucfirst($property) . 's';
    }

    protected function getSetMethodForCollectionProperty(string $property): string
    {
        return 'set' . ucfirst($property) . 's';
    }

    protected function getAddMethodForProperty(string $property): string
    {
        return 'add' . ucfirst($property);
    }

    protected function getRemoveMethodForProperty(string $property): string
    {
        return 'remove' . ucfirst($property);
    }

    protected function getValueForType(string $type): DateTime|float|int|bool|string
    {
        return match ($type) {
            'integer' => 10,
            'float' => 10.5,
            'string' => 'lorem ipsum',
            'hexcolor' => '#fa3cc2',
            'email' => 'dev.null@example.com',
            'phone' => '000-000-0000',
            'datetime' => new DateTime(),
            'bool', 'boolean' => true,
            default => throw new Exception("No values for type {$type}"),
        };
    }
}
