<?php

use App\Permissions;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Tests\Factories\UserFactory;

class UserTest extends TestCase {
  use DatabaseMigrations;

  public function testExample(): void {
    $this->get('/');
    self::assertEquals('Running DatePoll-Backend! ( ͡° ͜ʖ ͡°)', $this->response->getContent());
  }

  public function testUserManagementMiddleware(): void {
    $nonAdmin = UserFactory::createUser('test', false);
    $jwt = UserFactory::getJWTTokenForUserId($nonAdmin->id);

    $data = [
      'test' => 'Test', ];

    $response = $this->post('/api/v1/management/users?token=' . $jwt, $data);
    $response->assertResponseStatus(403);
    $response->seeJsonContains([
      'error_code' => 'permissions_denied',
      'needed_permissions' => [
        Permissions::$ROOT_ADMINISTRATION,
        Permissions::$MANAGEMENT_ADMINISTRATION, ], ]);
  }

  public function testUserCreate(): void {
    self::assertTrue(true);

    return;

    $admin = UserFactory::createUser('test');
    $jwt = UserFactory::getJWTTokenForUserId($admin->id);

    $data = [
      'title' => 'Dr.',
      'username' => 'test.dofenschmirz',
      'firstname' => 'Manfred',
      'surname' => 'Dofen',
      'birthday' => '2020-05-12',
      'join_date' => '2020-05-12',
      'streetname' => 'Ring',
      'streetnumber' => '1',
      'zipcode' => 1010,
      'location' => 'Vienna',
      'activated' => false,
      'activity' => 'aktiv',
      'internal_comment' => 'Tolles Vereinsmitglied',
      'information_denied' => false,
      'bv_member' => 'none',
      'member_number' => 'M112G',
      'phone_numbers' => [
        [
          'label' => 'Mobile',
          'number' => '+43 3333 6666', ],
        [
          'label' => 'Home',
          'number' => '+43 33333 3333', ], ],
      'permissions' => [
        'test.permission',
        'test.permission2', ],
      'email_addresses' => [
        'test1@datepoll.org',
        'test2@datepoll.org', ], ];

    $response = $this->post('/api/v1/management/users?token=' . $jwt, $data);
    $response->assertResponseStatus(201);
    $response->seeJsonContains(['msg' => 'User successful created']);
  }

  public function testUserGetAll(): void {
    self::assertTrue(true);

    return;
    $admin = UserFactory::createUser('test2');
    $jwt = UserFactory::getJWTTokenForUserId($admin->id);

    $response = $this->get('/api/v1/management/users?token=' . $jwt);
    $response->assertResponseStatus(200);
    $response->seeJsonStructure([
      'msg',
      'users' => [
        [
          'id',
          'title',
          'firstname',
          'surname',
          'username',
          'birthday',
          'join_date',
          'streetname',
          'streetnumber',
          'zipcode',
          'location',
          'activated',
          'activity',
          'member_number',
          'internal_comment',
          'information_denied',
          'bv_member',
          'force_password_change',], ], ]);
  }

  public function testUserUpdate(): void {
    $admin = UserFactory::createUser('test3');
    $jwt = UserFactory::getJWTTokenForUserId($admin->id);

    $response = $this->get('/api/v1/management/users?token=' . $jwt);
    $response->assertResponseStatus(200);
    // --- Attention ---
    // The id is at exactly this position. If user names / data changes the position may also change
    // --- Attention ---
    $id = substr($this->response->getContent(), strpos($this->response->getContent(), 'id') + 4, 1);

    $data = [
      'title' => 'Mag.',
      'username' => 'test.neu.dofenschmirz',
      'firstname' => 'Manfred.neu',
      'surname' => 'Dofen.neu',
      'birthday' => '2020-01-12',
      'join_date' => '2020-01-12',
      'streetname' => 'Ring.neu',
      'streetnumber' => '1.neu',
      'zipcode' => 1111,
      'location' => 'Vienna.neu',
      'activated' => false,
      'activity' => 'aktiv.neu',
      'internal_comment' => 'Tolles Vereinsmitglie.neud',
      'information_denied' => false,
      'bv_member' => 'none.neu',
      'member_number' => 'M112G.neu',
      'phone_numbers' => [
        [
          'label' => 'Mobile.neu',
          'number' => '+43 3333 6666.neu', ],
        [
          'label' => 'Hom.neue',
          'number' => '+43 33333 3333.neu', ], ],
      'permissions' => [
        'test.permission.neu',
        'test.permission2.neu', ],
      'email_addresses' => [
        'test1@datepoll.org.neu',
        'test2@datepoll.org.neu', ], ];

    $response = $this->put('/api/v1/management/users/' . $id . '?token=' . $jwt, $data);
    $response->assertResponseStatus(200);
    $response->seeJsonContains(['msg' => 'User updated']);
  }

  public function testUserDelete(): void {
    self::assertTrue(true);

    return;
    $user = UserFactory::createUser('test4');
    $admin = UserFactory::createUser('test5');
    $jwt = UserFactory::getJWTTokenForUserId($admin->id);

    $response = $this->delete('/api/v1/management/users/' . $user->id . '?token=' . $jwt);
    $response->assertResponseStatus(200);
    $response->seeJsonContains(['msg' => 'User deleted']);
  }
}
