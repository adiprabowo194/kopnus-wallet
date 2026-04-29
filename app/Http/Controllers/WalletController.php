<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientBalanceException;
use Illuminate\Support\Facades\DB;

use App\Models\Transaction;
use App\Http\Requests\WithdrawRequest;
use App\Http\Resources\TransactionResource;
use App\Http\Requests\DepositRequest;
use App\Exceptions\MemberNotFoundException;
use App\Exceptions\MemberInactiveException;

use App\Models\Member;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WalletController extends Controller
{

    public function checkMember(string $memberCode)
    {
        $member = Member::where('member_code', $memberCode)->first();
        if (!$member) {
            throw new MemberNotFoundException();
        }
        if (!$member->isActive()) {
            throw new MemberInactiveException();
        }
        return $member;
    }
    // GET /api/wallet/{memberCode}/balance
    public function balance(string $memberCode)
    {
        $member = $this->checkMember($memberCode);
        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Saldo ditampilkan.',
            'data'    => [
                'member_code' => $member->member_code,
                'name'        => $member->name,
                'balance'     => (float) $member->balance,
            ],
        ]);
    }

    // POST /api/wallet/{memberCode}/deposit
    public function deposit(DepositRequest $request, string $memberCode)
    {
        $member = $this->checkMember($memberCode);
        try {
            $transaction = DB::transaction(function () use ($member, $request) {
                $locked = Member::where('member_code', $member->member_code)->lockForUpdate()->firstOrFail();

                $balanceBefore = (float) $locked->balance;
                $balanceAfter  = round($balanceBefore + (float) $request->amount, 2);

                $locked->balance = $balanceAfter;
                $locked->save();

                $transaction = Transaction::create([
                    'reference_number' => 'TXN-' . now()->format('Ymd-His') . '-' . Str::random(5),
                    'member_code'        => $locked->member_code,
                    'type'             => 'deposit',
                    'amount'           => $request->amount,
                    'balance_before'   => $balanceBefore,
                    'balance_after'    => $balanceAfter,
                    'description'      => $request->description,
                ]);

                //  LOG SUCCESS
                Log::channel('wallet')->info('DEPOSIT_SUCCESS', [
                    'member_code' => $locked->member_code,
                    'amount'      => $request->amount,
                    'before'      => $balanceBefore,
                    'after'       => $balanceAfter,
                    'reference'   => $transaction->reference_number,
                    'ip'          => request()->ip(),
                ]);
                return $transaction;
            });

            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Deposit berhasil.',
                'data'    => new TransactionResource($transaction->load('member')),
            ]);
        } catch (\Throwable $e) {
            //  LOG ERROR
            Log::channel('wallet')->error('DEPOSIT_FAILED', [
                'member_code' => $memberCode,
                'amount'      => $request->amount,
                'error'       => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => 'error',
                'code'    => 500,
                'message' => 'Server error. Contact admin.'
            ]);
        }
    }

    // POST /api/wallet/{memberCode}/withdraw
    public function withdraw(WithdrawRequest $request, string $memberCode)
    {
        $member = $this->checkMember($memberCode);

        try {
            $transaction = DB::transaction(function () use ($member, $request) {

                $balanceBefore = (float) $member->balance;

                if ($balanceBefore <= 0) {
                    throw new InsufficientBalanceException();
                }

                if ($balanceBefore < (float) $request->amount) {

                    throw new InsufficientBalanceException();
                }

                $balanceAfter = round($balanceBefore - (float) $request->amount, 2);

                $member->balance = $balanceAfter;
                $member->save();

                $transaction = Transaction::create([
                    'reference_number' => 'TXN-' . now()->format('Ymd-His') . '-' . Str::random(5),
                    'member_code'        => $member->member_code,
                    'type'             => 'withdraw',
                    'amount'           => $request->amount,
                    'balance_before'   => $balanceBefore,
                    'balance_after'    => $balanceAfter,
                    'description'      => $request->description,
                ]);

                // LOG SUCCESS
                Log::channel('wallet')->info('WITHDRAW_SUCCESS', [
                    'member_code' => $member->member_code,
                    'amount'      => $request->amount,
                    'before'      => $balanceBefore,
                    'after'       => $balanceAfter,
                    'reference'   => $transaction->reference_number,
                    'ip'          => request()->ip(),
                ]);
                return $transaction;
            });

            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Withdraw berhasil.',
                'data'    => new TransactionResource($transaction->load('member')),
            ]);
        } catch (InsufficientBalanceException $e) {

            return response()->json([
                'status'  => 'error',
                'code'    => 400,
                'message' => 'Saldo tidak mencukupi.'
            ], 400);
        } catch (\Exception $e) {

            //  LOG ERROR
            Log::channel('wallet')->error('WITHDRAW_FAILED', [
                'member_code' => $memberCode,
                'amount'      => $request->amount,
                'error'       => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => 'error',
                'code'    => 500,
                'message' => 'Server error. Contact admin.'
            ], 500);
        }
    }
}
