<?php

namespace Gedmo\Timestampable\Mapping\Driver;

use Gedmo\Mapping\Driver,
    Doctrine\Common\Annotations\AnnotationReader,
    Gedmo\Exception\InvalidMappingException;

/**
 * This is an annotation mapping driver for Timestampable
 * behavioral extension. Used for extraction of extended
 * metadata from Annotations specificaly for Timestampable
 * extension.
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Gedmo.Translatable.Mapping.Driver
 * @subpackage Annotation
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Annotation implements Driver
{
    /**
     * Annotation field is timestampable
     */
    const ANNOTATION_TIMESTAMPABLE = 'Gedmo\Timestampable\Mapping\Timestampable';

    /**
     * List of types which are valid for timestamp
     *
     * @var array
     */
    private $validTypes = array(
        'date',
        'time',
        'datetime',
        'timestamp'
    );

    /**
     * {@inheritDoc}
     */
    public function validateFullMetadata($meta, array $config) {}

    /**
     * {@inheritDoc}
     */
    public function readExtendedMetadata($meta, array &$config) {
        require_once __DIR__ . '/../Annotations.php';
        $reader = new AnnotationReader();
        $reader->setAnnotationNamespaceAlias('Gedmo\Timestampable\Mapping\\', 'gedmo');

        $class = $meta->getReflectionClass();
        // property annotations
        foreach ($class->getProperties() as $property) {
            if ($meta->isMappedSuperclass && !$property->isPrivate() ||
                $meta->isInheritedField($property->name) ||
                isset($meta->associationMappings[$property->name]['inherited'])
            ) {
                continue;
            }
            if ($timestampable = $reader->getPropertyAnnotation($property, self::ANNOTATION_TIMESTAMPABLE)) {
                $field = $property->getName();
                if (!$meta->hasField($field)) {
                    throw new InvalidMappingException("Unable to find timestampable [{$field}] as mapped property in entity - {$meta->name}");
                }
                if (!$this->isValidField($meta, $field)) {
                    throw new InvalidMappingException("Field - [{$field}] type is not valid and must be 'date', 'datetime' or 'time' in class - {$meta->name}");
                }
                if (!in_array($timestampable->on, array('update', 'create', 'change'))) {
                    throw new InvalidMappingException("Field - [{$field}] trigger 'on' is not one of [update, create, change] in class - {$meta->name}");
                }
                if ($timestampable->on == 'change') {
                    if (!isset($timestampable->field) || !isset($timestampable->value)) {
                        throw new InvalidMappingException("Missing parameters on property - {$field}, field and value must be set on [change] trigger in class - {$meta->name}");
                    }
                    $field = array(
                        'field' => $field,
                        'trackedField' => $timestampable->field,
                        'value' => $timestampable->value
                    );
                }
                // properties are unique and mapper checks that, no risk here
                $config[$timestampable->on][] = $field;
            }
        }
    }

    /**
     * Checks if $field type is valid
     *
     * @param ClassMetadata $meta
     * @param string $field
     * @return boolean
     */
    protected function isValidField($meta, $field)
    {
        $mapping = $meta->getFieldMapping($field);
        return $mapping && in_array($mapping['type'], $this->validTypes);
    }
}