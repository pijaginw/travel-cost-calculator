<?php

namespace App\Tests\Entity;

use App\Entity\Trip;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TripTest extends KernelTestCase
{
    public function testTripNameCannotBeTooLong(): void
    {
        self::bootKernel(['environment' => 'test']);
        $validator = static::getContainer()->get('validator');

        $trip = new Trip();
        $trip->setTripName(str_repeat('a', 71));
        $trip->setTripCurrency('USD');

        $errors = $validator->validate($trip);

        // Expecting 1 error because max length is 70 (FR-006)
        $this->assertCount(1, $errors);
    }
}
