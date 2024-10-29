<?php declare(strict_types=1);

namespace App\Serializer\Normalizer;

use Doctrine\ORM\Mapping as ORM;
use ReflectionClass;
use ReflectionNamedType;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * This normalizer only work with Doctrine entities, and it improves on the object normalizer:
 * - the datetime classes are normalize as a single UTC datetime string
 * - the *ToMany relations are automatically ignored
 * - the *ToOne relations are automatically normalize only as their primary key instead of being normalized as an object
 *
 * Note that this normalizer is only used when creating or deleting an entity.
 */
final readonly class AuditLogDataNormalizer
{
    public function __construct(
        /**
         * see why this is not the NormalizerInterface or the ObjectNormalizer directly
         * @see https://github.com/symfony/symfony/discussions/58707
         */
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @return array<string, scalar|array>
     */
    public function normalize(object $object): array
    {
        $data = $this->serializer->normalize($object, 'array');

        $reflClass = new ReflectionClass($object);
        foreach ($data as $key => $value) { // $value here is already the normalized value of the property/getter
            try {
                $reflProperty = $reflClass->getProperty($key);
            } catch (\ReflectionException) {
                continue;
            }

            $reflType = $reflProperty->getType();
            if (!($reflType instanceof ReflectionNamedType)) {
                continue;
            }

            $typeName = $reflType->getName();
            if (!$this->isDoctrineEntity($typeName)) {
                continue;
            }

            $primaryKeyProperty = $this->getPrimaryKeyPropertyName($typeName);
            if (isset($data[$key][$primaryKeyProperty])) {
                $data[$key] = $data[$key][$primaryKeyProperty];
            }
        }

        return $data;
    }

    /**
     * @param class-string $entityFqcn
     *
     * @return string The property name, or '{ not found}' if no property is found
     */
    private function getPrimaryKeyPropertyName(string $entityFqcn): string
    {
        try {
            $reflProperty = new \ReflectionProperty($entityFqcn, 'id');
            // the property exists, but check that it has the Doctrine Id attribute
            if ($reflProperty->getAttributes(ORM\Id::class) !== []) {
                return 'id';
            }
        } catch (\ReflectionException) {}

        // if we are here, we know that the primary key isn't 'id'
        // so test all the properties of the entity
        foreach ((new ReflectionClass($entityFqcn))->getProperties() as $reflProperty) {
            if ($reflProperty->getAttributes(ORM\Id::class) !== []) {
                return $reflProperty->getName();
            }
        }

        return '{not found}';
    }

    private function isDoctrineEntity(string|object $object): bool
    {
        if (is_string($object) && !class_exists($object)) {
            return false;
        }

        return (new ReflectionClass($object))->getAttributes(ORM\Entity::class) !== [];
    }
}
