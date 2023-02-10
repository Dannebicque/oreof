<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\Request;

abstract class JsonRequest
{
    /**
     * @throws \JsonException
     */
    public static function getFromRequest(Request $request): array
    {
        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        }

        return $parametersAsArray;
    }

    /**
     * @throws \JsonException
     */
    public static function getValueFromRequest(Request $request, string $value): mixed
    {
        $parametersAsArray = self::getFromRequest($request);

        return $parametersAsArray[$value] ?? null;
    }
}
