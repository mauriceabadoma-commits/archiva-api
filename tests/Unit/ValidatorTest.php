<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Support\Validator;

/**
 * Tests « unitaires » au sens strict : aucune base de données, aucun
 * fichier, aucun réseau. On vérifie uniquement le calcul d'une fonction
 * à partir de ses arguments.
 */
class ValidatorTest extends TestCase
{
    public function test_valid_email_is_accepted(): void
    {
        $this->assertTrue(Validator::isValidEmail('aurelle@archiva.com'));
    }

    public function test_email_without_at_sign_is_rejected(): void
    {
        $this->assertFalse(Validator::isValidEmail('pas-un-email'));
    }

    public function test_empty_email_is_rejected(): void
    {
        $this->assertFalse(Validator::isValidEmail(''));
    }

    public function test_password_with_enough_characters_is_valid(): void
    {
        $this->assertTrue(Validator::isValidPassword('secret123'));
    }

    public function test_password_too_short_is_invalid(): void
    {
        $this->assertFalse(Validator::isValidPassword('abc'));
    }

    public function test_password_minimum_length_is_configurable(): void
    {
        $this->assertTrue(Validator::isValidPassword('abcd', minLength: 4));
        $this->assertFalse(Validator::isValidPassword('abc', minLength: 4));
    }
}
