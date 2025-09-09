<?php

namespace App\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class NestedIdEntityDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface{

    use DenormalizerAwareTrait;

    // Entités (ORM) prises en charge - Boolean : si le résultat peut être mis en cache
    private array $supportedNestedEntities = [
        'App\Entity\FicheMatiere' => false,
    ];

    // Permet de mettre à jour le contexte du serializer
    private const NESTED_SERIALIZER_CALLED = 'NESTED_SERIALIZER_CALLED';

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []) : mixed
    {
        // Si l'on dénormalise l'objet, on le marque dans le contexte, pour éviter un deuxième passage
        $context[self::NESTED_SERIALIZER_CALLED] = true;
        $object = $this->denormalizer->denormalize($data, $type, $format, $context);
        $object->setDeserializedId($data['id']);

        return $object;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return array_key_exists($type, $this->supportedNestedEntities)
            && isset($context[self::NESTED_SERIALIZER_CALLED]) === false
            && $format === 'json';
    }

    public function getSupportedTypes(?string $format): array {
        return $this->supportedNestedEntities;
    }
}