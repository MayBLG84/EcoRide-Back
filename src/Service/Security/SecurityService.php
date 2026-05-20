<?php

namespace App\Service\Security;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class SecurityService
{
    private array $allowedMimeTypes = [
        'application/pdf',
        'image/jpeg',
        'image/png'
    ];

    private int $maxFileSize = 2097152; // 2MB

    /**
     * Sanitize a string input by trimming, limiting length,
     * and removing unwanted characters.
     *
     * @param string $input The input string to sanitize.
     * @param int $maxLength Maximum allowed length (default 255).
     * @return string Sanitized string.
     */
    public function sanitizeString(mixed $input, int $maxLength = 255): string
    {
        // Force string
        $clean = (string) $input;

        // Remove HTML tags
        $clean = strip_tags($clean);

        // Trim spaces
        $clean = trim($clean);

        // Normalize internal spaces
        $clean = preg_replace('/\s+/u', ' ', $clean) ?? '';

        // Limit size
        $clean = mb_substr($clean, 0, $maxLength);

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

    public function isValidBirthday(\DateTimeInterface $birthday, int $minimumAge = 18): bool
    {
        $today = new \DateTimeImmutable();

        if ($birthday > $today) {
            return false;
        }

        $age = $today->diff($birthday)->y;

        return $age >= $minimumAge;
    }

    public function isAdult(\DateTimeInterface $birthday, int $minimumAge = 18): bool
    {
        $today = new \DateTimeImmutable();
        $age = $today->diff($birthday)->y;
        return $age >= $minimumAge;
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

    /**
     * Clean nickname before setting it to DB
     */
    public function cleanNickname(string $nickname): string
    {
        $clean = preg_replace('/[\x00-\x1F\x7F]/u', '', $nickname);
        $clean = preg_replace('/[\p{C}\p{Zl}\p{Zp}]/u', '', $clean);

        return $clean;
    }

    public function isValidNickname(string $nickname): bool
    {
        return (bool) preg_match(
            '/^[A-Za-z0-9][A-Za-z0-9\-.\*@]{2,19}$/',
            $nickname
        );
    }

    public function isValidName(string $name): bool
    {
        return (bool) preg_match("/^[\p{L} '-]+$/u", $name);
    }

    public function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function isValidTelephone(string $telephone): bool
    {
        return (bool) preg_match('/^\+?[0-9]{8,15}$/', $telephone);
    }

    public function validateAttachments(array $files): bool
    {
        foreach ($files as $file) {

            if (!$file instanceof UploadedFile) {
                return false;
            }

            if ($file->getSize() > $this->maxFileSize) {
                return false;
            }

            if (!in_array($file->getMimeType(), $this->allowedMimeTypes, true)) {
                return false;
            }
        }

        return true;
    }

    public function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }
}
