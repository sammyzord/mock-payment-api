<?php

namespace App\Http\Controllers\Api;

use Exception;

use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\MakeTransactionRequest;
use App\Services\TransactionService;
use App\Exceptions\Services\TransactionService\TransactionException;
use App\Exceptions\Services\TransactionService\NotificationException;
use App\Exceptions\Services\TransactionService\GetAuthorizationException;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function makeTransaction(MakeTransactionRequest $request)
    {
        try {
            $validated = $request->validated();

            $value = (int) $validated['value'];

            $payer = Auth::user();
            $payee = User::find($validated['payee']);

            if ($payer->id == $payee->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User cannot make a transaction to itself',
                    'data' => null
                ], 403);
            }

            $this->transactionService::getAuthorization();
            $this->transactionService::makeTransaction($payer, $payee, $value);
            $this->transactionService::sendNotification();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaction successful',
                'data' => null
            ]);
        } catch (NotificationException) {
            return response()->json([
                'status' => 'success',
                'message' => 'Transaction successful',
                'data' => [
                    'notification' => 'failed'
                ]
            ]);
        } catch (GetAuthorizationException | TransactionException $exc) {
            return response()->json([
                'status' => 'error',
                'message' => $exc->getMessage(),
                'data' => null
            ], 403);
        } catch (Exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
                'data' => null
            ], 500);
        }
    }
}
