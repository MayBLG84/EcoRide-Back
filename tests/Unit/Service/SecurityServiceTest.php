<?php

namespace App\Tests\Service;

use App\Service\SecurityService;
use PHPUnit\Framework\TestCase;

class SecurityServiceTest extends TestCase
{
    private SecurityService $service;

    protected function setUp(): void
    {
        $this->service = new SecurityService();
    }

    // -------------------- sanitizeString --------------------

    public function testSanitizeStringRemovesInvalidChars(): void
    {
        $input = "  Hello!@# World\t\n ";
        $expected = "Hello World";
        $this->assertSame($expected, $this->service->sanitizeString($input));
    }

    public function testSanitizeStringTrimsAndLimitsLength(): void
    {
        $input = str_repeat("a", 300);
        $result = $this->service->sanitizeString($input, 100);
        $this->assertSame(100, mb_strlen($result));
    }

    // -------------------- normalizeString --------------------

    public function testNormalizeStringRemovesAccentsAndLowercases(): void
    {
        $input = "Éléphant ÀÇ";
        $expected = "elephant ac";
        $this->assertSame($expected, $this->service->normalizeString($input));
    }

    // -------------------- validateDate --------------------


    public function testValidateDateReturnsTrueForDateTimeInterface(): void
    {
        $immutable = new \DateTimeImmutable();
        $this->assertTrue($this->service->validateDate($immutable));

        $mutable = new \DateTime();
        $this->assertTrue($this->service->validateDate($mutable));
    }

    // -------------------- isValidCity --------------------

    public function testIsValidCityAcceptsValidCity(): void
    {
        $this->assertTrue($this->service->isValidCity("Paris"));
        $this->assertTrue($this->service->isValidCity("Saint-Étienne"));
        $this->assertTrue($this->service->isValidCity("La Rochelle"));
    }

    public function testIsValidCityRejectsInvalidCity(): void
    {
        $this->assertFalse($this->service->isValidCity("Paris123"));
        $this->assertFalse($this->service->isValidCity("Berlin!"));
        $this->assertFalse($this->service->isValidCity("  "));
    }

    // -------------------- isValidDate --------------------

    public function testIsValidDateAcceptsValidDate(): void
    {
        $futureDate = ['year' => 2030, 'month' => 2, 'day' => 28];
        $this->assertTrue($this->service->isValidDate($futureDate));
    }

    public function testIsValidDateRejectsPastDate(): void
    {
        $pastDate = ['year' => 2000, 'month' => 1, 'day' => 1];
        $this->assertFalse($this->service->isValidDate($pastDate));
    }

    public function testIsValidDateRejectsInvalidDayOrMonth(): void
    {
        $this->assertFalse($this->service->isValidDate(['year' => 2030, 'month' => 13, 'day' => 1]));
        $this->assertFalse($this->service->isValidDate(['year' => 2030, 'month' => 2, 'day' => 30]));
    }

    // -------------------- dateStructToDateTimeImmutable --------------------

    public function testDateStructToDateTimeImmutableReturnsDateTime(): void
    {
        $struct = ['year' => 2030, 'month' => 1, 'day' => 15];
        $result = $this->service->dateStructToDateTimeImmutable($struct);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result);
        $this->assertSame('2030-01-15', $result->format('Y-m-d'));
    }

    public function testDateStructToDateTimeImmutableReturnsNullForInvalid(): void
    {
        $struct = ['year' => 2030, 'month' => 2, 'day' => 30]; // invalid
        $this->assertNull($this->service->dateStructToDateTimeImmutable($struct));
    }

    // -------------------- cleanNickname --------------------

    public function testCleanNicknameRemovesControlChars(): void
    {
        $input = "Nick\x00name\x1F\u{2029}";
        $expected = "Nickname";
        $this->assertSame($expected, $this->service->cleanNickname($input));
    }

    // -------------------- testSanitizeAndNormalizeUnicode --------------------

    public function testSanitizeAndNormalizeUnicodeStrings(): void
    {
        $input = " Héllo 🌍! Ça va? 💡🚀 123 — test — ";

        $sanitized = $this->service->sanitizeString($input);
        $this->assertEquals("Héllo  Ça va  123  test ", $sanitized);

        $normalized = $this->service->normalizeString($input);

        $this->assertEquals("hello  ca va  123  test ", $normalized);
    }
}
