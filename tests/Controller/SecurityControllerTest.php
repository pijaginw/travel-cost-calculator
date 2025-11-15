<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    // Test that anonymous users are redirected to log in
    public function testAnonymousUsersCannotAccessDashboard(): void
    {
        $client = static::createClient([ 'environment' => 'test']);
        $client->request('GET', '/trip/list');

        $this->assertResponseRedirects('/login');
    }

    // Test that logged-in users can access the dashboard
    public function testLoggedInUserCanAccessDashboard(): void
    {
        $client = static::createClient([ 'environment' => 'test']);

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = new User();
        $testUser->setEmail('test@example.com');
        $testUser->setPassword('password');

        $userRepository->save($testUser, true);

        $client->loginUser($testUser);

        $client->request('GET', '/trip/list');
        $this->assertResponseIsSuccessful();
    }
}
