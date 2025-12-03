<?php

namespace App\Service;

class SecurityService
{
    /**
     * Sanitize a string input by trimming, limiting length,
     * and removing unwanted characters.
     *
     * @param string $input The input string to sanitize.
     * @param int $maxLength Maximum allowed length (default 255).
     * @return string Sanitized string.
     */
    public function sanitizeString(string $input, int $maxLength = 255): string
    {
        $clean = trim($input);
        $clean = mb_substr($clean, 0, $maxLength);
        // Remove characters that are not letters, numbers, spaces, or hyphens
        $clean = preg_replace('/[^\p{L}\p{N}\s\-]/u', '', $clean);
        return $clean;
    }

    /**
     * Normalize a string for consistent comparisons and searches.
     * Removes accents and converts to lowercase.
     *
     * @param string $input The input string to normalize.
     * @return string Normalized string.
     */
    public function normalizeString(string $input): string
    {
        $input = $this->sanitizeString($input);
        $normalized = \Normalizer::normalize($input, \Normalizer::FORM_KD);
        // Remove diacritics (accents)
        $normalized = preg_replace('/\p{Mn}/u', '', $normalized);
        return mb_strtolower($normalized);
    }

    /**
     * Validate that a given value is a DateTimeInterface object.
     *
     * @param \DateTimeInterface $date The date to validate.
     * @return bool True if valid, false otherwise.
     */
    public function validateDate(\DateTimeInterface $date): bool
    {
        return $date instanceof \DateTimeInterface;
    }

    /**
     * Validate that a city name is valid according to European/French rules.
     * Only letters (including accents), spaces, and hyphens are allowed.
     *
     * @param string $city The city name to validate.
     * @return bool True if valid, false otherwise.
     */
    public function isValidCity(string $city): bool
    {
        $city = trim($city);
        return (bool) preg_match('/^[\p{L}\s\-]+$/u', $city);
    }

    /**
     * Validate that a date represented as an array (NgbDateStruct) is valid.
     * Checks:
     * - Year >= current year
     * - Month between 1 and 12
     * - Day valid for given month and year (accounts for leap years)
     *
     * @param array{year:int,month:int,day:int} $dateStruct The date structure to validate.
     * @return bool True if valid, false otherwise.
     */
    public function isValidDate(array $dateStruct): bool
    {
        if (!isset($dateStruct['year'], $dateStruct['month'], $dateStruct['day'])) {
            return false;
        }

        $year = (int)$dateStruct['year'];
        $month = (int)$dateStruct['month'];
        $day = (int)$dateStruct['day'];

        $currentYear = (int)(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')))->format('Y');
        if ($year < $currentYear) {
            return false;
        }

        if ($month < 1 || $month > 12) {
            return false;
        }

        return checkdate($month, $day, $year);
    }

    /**
     * Convert a valid NgbDateStruct array to a \DateTimeImmutable object.
     * Returns null if the structure is invalid.
     *
     * @param array{year:int,month:int,day:int} $dateStruct The date structure to convert.
     * @return \DateTimeImmutable|null Converted date object or null if invalid.
     */
    public function dateStructToDateTimeImmutable(array $dateStruct): ?\DateTimeImmutable
    {
        if (!$this->isValidDate($dateStruct)) {
            return null;
        }

        return \DateTimeImmutable::createFromFormat(
            'Y-m-d',
            sprintf('%04d-%02d-%02d', $dateStruct['year'], $dateStruct['month'], $dateStruct['day']),
            new \DateTimeZone('Europe/Paris')
        );
    }
}
