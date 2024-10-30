<?php declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\DoctrineEntity;
use Doctrine\Common\Collections\Collection as DoctrineCollectionInterface;
use Doctrine\ORM\Mapping as ORM;
use ReflectionClass;
use ReflectionNamedType;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * This normalizer only work with Doctrine entities, and it improves on the object normalizer:
 * - the *ToMany relations are automatically ignored
 * - the *ToOne relations are automatically normalize only as their primary key instead of being normalized as an object
 *
 * Note that this normalizer is only used when creating or deleting an entity.
 *
 * The $normalizer property will be \Symfony\Component\Serializer\Serializer.
 * See link below why this class actually doesn't implement NormalizerInterface
 * @see https://github.com/symfony/symfony/discussions/58707
 */
final readonly class AuditLogDataNormalizer
{
    /**
     * @var array<string>
     */
    private const array IGNORED_PROPERTIES = [
        // ignoring some properties like that is needed because otherwise, when normalized for the audit logs,
        // the User data would have both the 'created_at' and 'createdAt' keys for instance.
        // It only happens for the User entity thought, not for the Article...

        // on the User entity
        'created_at', 'updated_at', 'userIdentifier', 'verified', 'writer', 'admin',
    ];

    public function __construct(
        private NormalizerInterface $normalizer,
    ) {
    }

    /**
     * @return array<string, scalar|array<mixed>>
     */
    public function normalize(DoctrineEntity $object): array
    {
        $data = $this->normalizer->normalize($object, 'array', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => self::IGNORED_PROPERTIES,
        ]);
        \assert(\is_array($data));
        /** @var array<string, mixed> $data */

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

            if (is_subclass_of($typeName, DoctrineCollectionInterface::class)) {
                // Note Florent: this feature is actually not useful since the entities are first normalized with the default ObjectNormalizer
                // which would throw a Symfony\Component\Serializer\Exception\CircularReferenceException if the entity
                // didn't already make the Collection properties be ignored via the Ignore attribute
                continue;
            }

            if (\is_array($value) && class_exists($typeName) && is_subclass_of($typeName, DoctrineEntity::class)) {
                /** @var class-string<DoctrineEntity> $typeName */
                \assert(\is_array($data[$key]));

                $primaryKeyProperty = $this->getPrimaryKeyPropertyName($typeName);
                if (isset($data[$key][$primaryKeyProperty])) {
                    $data[$key] = $data[$key][$primaryKeyProperty];
                }

                continue;
            }
        }

        return $data;
    }

    /**
     * @param class-string<DoctrineEntity> $entityFqcn
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
}
