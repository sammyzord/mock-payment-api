<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;

use App\Models\User;
use App\Services\TransactionService;
use App\Exceptions\Services\TransactionService\TransactionException;
use App\Exceptions\Services\TransactionService\NotificationException;
use App\Exceptions\Services\TransactionService\GetAuthorizationException;

class TransactionServiceTest extends TestCase
{
  use RefreshDatabase;

  public function setUp(): void
  {
    parent::setUp();

    $this->service = TransactionService::class;

    $this->user = User::factory()->create();
    $this->user->balance = 100000;
    $this->user->save();

    $this->shopkeeper = User::factory()->shopkeeper()->create();
  }


  public function testTransactionSuccess()
  {
    $this->service::makeTransaction($this->user, $this->shopkeeper, 1000);

    $test_user = User::find($this->user->id);
    $test_shop = User::find($this->shopkeeper->id);

    $this->assertEquals($test_user->balance, 100000 - 1000);
    $this->assertEquals($test_shop->balance, 1000);
  }

  public function testTransactionFail()
  {
    $this->expectException(TransactionException::class);
    $this->expectExceptionMessage('User balance insufficient');

    $this->service::makeTransaction($this->user, $this->shopkeeper, 100001);


    $test_user = User::find($this->user->id);
    $test_shop = User::find($this->shopkeeper->id);

    $this->assertEquals($test_user->balance, 100000);
    $this->assertEquals($test_shop->balance, 0);
  }

  public function testGetAuthorizationSuccess()
  {
    Http::fake([
      '*' => Http::response(['message' => 'Autorizado'], 200),
    ]);

    $this->expectNotToPerformAssertions();

    $this->service::getAuthorization();
  }

  public function testGetAuthorizationFail()
  {
    Http::fake([
      '*' => Http::response(['message' => 'Não Autorizado'], 403),
    ]);

    $this->expectException(GetAuthorizationException::class);
    $this->expectExceptionMessage('Transaction not authorized');

    $this->service::getAuthorization();
  }

  public function testNotificationSuccess()
  {
    Http::fake([
      '*' => Http::response(['message' => 'Success'], 200),
    ]);

    $this->expectNotToPerformAssertions();

    $this->service::sendNotification();
  }

  public function testNotificationFail()
  {
    Http::fake([
      '*' => Http::response(['message' => 'Error'], 500),
    ]);

    $this->expectException(NotificationException::class);
    $this->expectExceptionMessage('Notification Error');

    $this->service::sendNotification();
  }
}
