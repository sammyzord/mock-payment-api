<?php

namespace App\Services;

use Exception;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

use App\Models\User;
use App\Exceptions\Services\TransactionService\TransactionException;
use App\Exceptions\Services\TransactionService\NotificationException;
use App\Exceptions\Services\TransactionService\GetAuthorizationException;

class TransactionService
{

  public static function getAuthorization(): void
  {
    $message = Http::get('https://run.mocky.io/v3/8fafdd68-a090-496f-8c9a-3442cf30dae6')['message'];
    if ($message != 'Autorizado') {
      throw new GetAuthorizationException('Transaction not authorized');
    }
  }

  public static function sendNotification(): void
  {
    $message = Http::get('http://o4d9z.mocklab.io/notify')['message'];
    if ($message != 'Success') {
      throw new NotificationException('Notification Error');
    }
  }

  public static function makeTransaction(User $payer, User $payee, int $value): void
  {
    try {
      if ($payer->balance < $value) {
        throw new TransactionException('User balance insufficient');
      }

      DB::beginTransaction();

      $payer->balance = $payer->balance - $value;
      $payer->save();

      $payee->balance = $payee->balance + $value;
      $payee->save();

      DB::commit();
    } catch (Exception $err) {
      DB::rollBack();
      throw $err;
    }
  }
}
