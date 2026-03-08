<?php

namespace App\Tests\Application\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginFailureTest extends WebTestCase
{
    public function testLoginWithInvalidCredentials(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'mauvais@mail.com',
            '_password' => 'mauvaismdp',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/login');

        $client->followRedirect();

        $this->assertSelectorExists('.alert-danger');
    }
}
