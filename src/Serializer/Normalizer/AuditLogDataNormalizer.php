<?php declare(strict_types=1);

namespace App\Serializer\Normalizer;

use Doctrine\ORM\Mapping as ORM;
use ReflectionClass;
use ReflectionNamedType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * This normalizer only work with Doctrine entities, and it improves on the object normalizer:
 * - the datetime classes are normalize as a single UTC datetime string
 * - the *ToMany relations are automatically ignored
 * - the *ToOne relations are automatically normalize only as their primary key instead of being normalized as an object
 *
 * Note that this normalizer is only used when creating or deleting an entity.
 */
final readonly class AuditLogDataNormalizer implements NormalizerInterface
{
    public function __construct(
        // this property isn't used, but for some reason I have to make sure that the Serializer is loaded in the DI container
        // otherwise, there is an error in "vendor/symfony/serializer/Normalizer/AbstractObjectNormalizer.php:218"
        // Symfony\Component\Serializer\Exception\LogicException: Cannot normalize attribute "createdAt" because the injected serializer is not a normalizer.
        #[Autowire(service: 'serializer.normalizer.object')]
        private NormalizerInterface $normalizer,
    ) {
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        \assert(\is_object($object));
        $data = $this->normalizer->normalize($object, 'array', $context);

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

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        if (!\is_object($data)) {
            return false;
        }

        return $this->isDoctrineEntity($data);
    }

    private function isDoctrineEntity(string|object $object): bool
    {
        if (is_string($object) && !class_exists($object)) {
            return false;
        }

        return (new ReflectionClass($object))->getAttributes(ORM\Entity::class) !== [];
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['object' => true];
    }
}
