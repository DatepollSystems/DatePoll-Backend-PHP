<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class BaseTest extends TestCase {
  use DatabaseMigrations;

  public function testExample(): void {
    $this->get('/');

    self::assertEquals('Running DatePoll-Backend! ( ͡° ͜ʖ ͡°)', $this->response->getContent());
  }

  public function testJWTMiddlewareWithoutJWT(): void {
    $data = [
      'test' => 'Test', ];
    $response = $this->post('/api/v1/management/users', $data);
    $response->assertResponseStatus(401);
    $response->seeJsonContains(['error_code' => 'token_not_provided']);
  }

  public function testJWTMiddlewareWithExpiredJWT(): void {
    $data = [
      'test' => 'Test', ];
    $response = $this->post('/api/v1/management/users?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJsdW1lbi1qd3QiLCJzdWIiOjEsImlhdCI6MTU1MjkxMzg5NiwiZXhwIjoxNTUyOTM1NDk2fQ.9bUazZR5sRf5Pyvpsxzd06r_TeUc68RSlnSC85aOibU', $data);
    $response->assertResponseStatus(401);
    $response->seeJsonStructure(['error_code']);
  }

  public function testJWTMiddlewareWithIncorrectJWT(): void {
    $data = [
      'test' => 'Test', ];
    $response = $this->post('/api/v1/management/users?token=eyJ0eXAiOiJKV1QiLCJhbGcasdffffffffffffff', $data);
    $response->assertResponseStatus(401);
    $response->seeJsonContains(['error_code' => 'token_incorrect']);
  }
}
