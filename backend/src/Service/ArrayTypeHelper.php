<?php

namespace App\Service;

/**
 * Helper service to safely extract typed values from mixed arrays (typically from json_decode).
 * This eliminates PHPStan errors for offsetAccess.nonOffsetAccessible.
 */
class ArrayTypeHelper
{
    /**
     * Get an integer value from an array.
     *
     * @param array<string, mixed> $data
     *
     * @throws \InvalidArgumentException if key doesn't exist or value is not an integer
     */
    public static function getInt(array $data, string $key): int
    {
        if (!isset($data[$key])) {
            throw new \InvalidArgumentException("Key '{$key}' is missing from array");
        }

        $value = $data[$key];
        if (!is_int($value)) {
            throw new \InvalidArgumentException("Key '{$key}' must be an integer, got " . gettype($value));
        }

        return $value;
    }

    /**
     * Try to get an integer value from an array (nullable).
     *
     * @param array<string, mixed> $data
     *
     * @throws \InvalidArgumentException if value exists but is not an integer
     */
    public static function tryGetInt(array $data, string $key): ?int
    {
        if (!array_key_exists($key, $data)) {
            return null;
        }

        $value = $data[$key];
        if ($value === null) {
            return null;
        }

        if (!is_int($value)) {
            throw new \InvalidArgumentException("Key '{$key}' must be an integer or null, got " . gettype($value));
        }

        return $value;
    }

    /**
     * Get a string value from an array.
     *
     * @param array<string, mixed> $data
     *
     * @throws \InvalidArgumentException if key doesn't exist or value is not a string
     */
    public static function getString(array $data, string $key): string
    {
        if (!isset($data[$key])) {
            throw new \InvalidArgumentException("Key '{$key}' is missing from array");
        }

        $value = $data[$key];
        if (!is_string($value)) {
            throw new \InvalidArgumentException("Key '{$key}' must be a string, got " . gettype($value));
        }

        return $value;
    }

    /**
     * Try to get a string value from an array (nullable).
     *
     * @param array<string, mixed> $data
     *
     * @throws \InvalidArgumentException if value exists but is not a string
     */
    public static function tryGetString(array $data, string $key): ?string
    {
        if (!array_key_exists($key, $data)) {
            return null;
        }

        $value = $data[$key];
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException("Key '{$key}' must be a string or null, got " . gettype($value));
        }

        return $value;
    }

    /**
     * Get a boolean value from an array.
     *
     * @param array<string, mixed> $data
     *
     * @throws \InvalidArgumentException if key doesn't exist or value is not a boolean
     */
    public static function getBool(array $data, string $key): bool
    {
        if (!isset($data[$key])) {
            throw new \InvalidArgumentException("Key '{$key}' is missing from array");
        }

        $value = $data[$key];
        if (!is_bool($value)) {
            throw new \InvalidArgumentException("Key '{$key}' must be a boolean, got " . gettype($value));
        }

        return $value;
    }

    /**
     * Try to get a boolean value from an array (nullable).
     *
     * @param array<string, mixed> $data
     *
     * @throws \InvalidArgumentException if value exists but is not a boolean
     */
    public static function tryGetBool(array $data, string $key): ?bool
    {
        if (!isset($data[$key])) {
            return null;
        }

        $value = $data[$key];
        if ($value === null) {
            return null;
        }

        if (!is_bool($value)) {
            throw new \InvalidArgumentException("Key '{$key}' must be a boolean or null, got " . gettype($value));
        }

        return $value;
    }

    /**
     * Get a float value from an array.
     *
     * @param array<string, mixed> $data
     *
     * @throws \InvalidArgumentException if key doesn't exist or value is not a float
     */
    public static function getFloat(array $data, string $key): float
    {
        if (!isset($data[$key])) {
            throw new \InvalidArgumentException("Key '{$key}' is missing from array");
        }

        $value = $data[$key];
        if (!is_float($value) && !is_int($value)) {
            throw new \InvalidArgumentException("Key '{$key}' must be a float or integer, got " . gettype($value));
        }

        return (float) $value;
    }

    /**
     * Try to get a float value from an array (nullable).
     *
     * @param array<string, mixed> $data
     *
     * @throws \InvalidArgumentException if value exists but is not a float
     */
    public static function tryGetFloat(array $data, string $key): ?float
    {
        if (!array_key_exists($key, $data)) {
            return null;
        }

        $value = $data[$key];
        if ($value === null) {
            return null;
        }

        if (!is_float($value) && !is_int($value)) {
            throw new \InvalidArgumentException("Key '{$key}' must be a float, integer, or null, got " . gettype($value));
        }

        return (float) $value;
    }

    /**
     * Get an array value from an array.
     *
     * @param array<string, mixed> $data
     *
     * @throws \InvalidArgumentException if key doesn't exist or value is not an array
     *
     * @return array<string, mixed>
     */
    public static function getArray(array $data, string $key): array
    {
        if (!isset($data[$key])) {
            throw new \InvalidArgumentException("Key '{$key}' is missing from array");
        }

        $value = $data[$key];
        if (!is_array($value)) {
            throw new \InvalidArgumentException("Key '{$key}' must be an array, got " . gettype($value));
        }

        return $value;
    }

    /**
     * Try to get an array value from an array (nullable).
     *
     * @param array<string, mixed> $data
     *
     * @throws \InvalidArgumentException if value exists but is not an array
     *
     * @return array<string, mixed>|null
     */
    public static function tryGetArray(array $data, string $key): ?array
    {
        if (!array_key_exists($key, $data)) {
            return null;
        }

        $value = $data[$key];
        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            throw new \InvalidArgumentException("Key '{$key}' must be an array or null, got " . gettype($value));
        }

        return $value;
    }

    /**
     * Get a numeric value (int or float) from a string or numeric type.
     * Handles numeric strings like "123" or "45.67" and converts them to int/float.
     *
     * @param array<string, mixed> $data
     *
     * @throws \InvalidArgumentException if value exists but is not numeric
     *
     * @return int|float|null Returns int if value is whole number, float if decimal, null if key doesn't exist
     */
    public static function tryGetNumeric(array $data, string $key): int|float|null
    {
        if (!array_key_exists($key, $data)) {
            return null;
        }
        $value = $data[$key];
        if ($value === null) {
            return null;
        }

        // Handle numeric types directly
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        // Handle numeric strings
        if (is_string($value)) {
            // Check if it's a numeric string
            if (is_numeric($value)) {
                // Check if it's a whole number (int) or has decimal (float)
                if (strpos($value, '.') !== false || strpos($value, 'e') !== false || strpos($value, 'E') !== false) {
                    return (float) $value;
                }

                return (int) $value;
            }
        }

        throw new \InvalidArgumentException("Key '{$key}' must be numeric (int, float, or numeric string), got " . gettype($value));
    }

    /**
     * Get a numeric value as float from a string or numeric type.
     * Handles numeric strings like "123" or "45.67" and converts them to float.
     * Returns null if the value is not numeric (instead of throwing an exception).
     *
     * @param array<string, mixed> $data
     *
     * @return float|null Returns float if numeric, null if key doesn't exist or value is not numeric
     */
    public static function tryGetNumericAsFloat(array $data, string $key): ?float
    {
        if (!array_key_exists($key, $data)) {
            return null;
        }
        $value = $data[$key];
        if ($value === null) {
            return null;
        }

        // Handle numeric types directly
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        // Handle numeric strings
        if (is_string($value)) {
            // Check if it's a numeric string
            if (is_numeric($value)) {
                return (float) $value;
            }
        }

        // Not numeric, return null
        return null;
    }

    /**
     * Safely convert a mixed value to an array.
     * Returns the array if the value is already an array, otherwise returns an empty array.
     *
     * @return array<int|string, mixed>
     */
    public static function getArrayFromMixed(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        return [];
    }
}
