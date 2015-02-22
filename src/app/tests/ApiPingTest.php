<?php

/**
 * Class ApiPingTest
 */
class ApiPingTest extends TestCase
{
    /**
     * @test
     */
    public function ping()
    {
        $this->client->request('GET', '/api/ping');

        $this->assertTrue($this->client->getResponse()->isOk());

        $decoded = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('pong', $decoded);
    }
}
