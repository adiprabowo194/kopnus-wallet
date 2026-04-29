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

use Illuminate\Support\Str;

class WalletController extends Controller
{
    // GET /api/wallet/{memberCode}/balance
    public function balance(string $memberCode)
    {
        $member = Member::where('member_code', $memberCode)->first();

        if (!$member) {
            throw new MemberNotFoundException();
        }
        if (!$member->isActive()) {
            throw new MemberInactiveException();
        }

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

    // POST /api/wallet/{memberCode}/history
    public function history(string $memberCode)
    {
        $member = Member::where('member_code', $memberCode)->first();

        if (!$member) {
            throw new MemberNotFoundException();
        }

        if (!$member->isActive()) {
            throw new MemberInactiveException();
        }

        $transactions = Transaction::where('member_id', $member->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Histori transaksi ditemukan.',
            'data'    => TransactionResource::collection($transactions),
            'meta'    => [
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'per_page'     => $transactions->perPage(),
                'total'        => $transactions->total(),
            ]
        ]);
    }

    // POST /api/wallet/{memberCode}/deposit
    public function deposit(DepositRequest $request, string $memberCode)
    {
        $member = Member::where('member_code', $memberCode)->firstOrFail();

        if (!$member->isActive()) {
            throw new MemberInactiveException();
        }

        $transaction = DB::transaction(function () use ($member, $request) {
            $locked = Member::where('member_code', $member->member_code)->lockForUpdate()->firstOrFail();

            $balanceBefore = (float) $locked->balance;
            $balanceAfter  = round($balanceBefore + (float) $request->amount, 2);

            $locked->balance = $balanceAfter;
            $locked->save();

            return Transaction::create([
                'reference_number' => 'TXN-' . now()->format('Ymd-His') . '-' . Str::random(5),
                'member_id'        => $locked->id,
                'type'             => 'deposit',
                'amount'           => $request->amount,
                'balance_before'   => $balanceBefore,
                'balance_after'    => $balanceAfter,
                'description'      => $request->description,
            ]);
        });

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Deposit berhasil.',
            'data'    => new TransactionResource($transaction->load('member')),
        ]);
    }

    // POST /api/wallet/{memberCode}/withdraw
    public function withdraw(WithdrawRequest $request, string $memberCode)
    {
        $member = Member::where('member_code', $memberCode)->firstOrFail();
        if (!$member) {
            throw new MemberNotFoundException();
        }
        if (!$member->isActive()) {
            throw new MemberInactiveException();
        }

        try {
            $transaction = DB::transaction(function () use ($member, $request) {
                $locked = Member::where('member_code', $member->member_code)
                    ->lockForUpdate()
                    ->first();
                $balanceBefore = (float) $locked->balance;

                if ($balanceBefore <= 0) {
                    throw new InsufficientBalanceException();
                }

                if ($balanceBefore < (float) $request->amount) {

                    throw new InsufficientBalanceException();
                }

                $balanceAfter = round($balanceBefore - (float) $request->amount, 2);

                $locked->balance = $balanceAfter;
                $locked->save();

                return Transaction::create([
                    'reference_number' => 'TXN-' . now()->format('Ymd-His') . '-' . Str::random(5),
                    'member_id'        => $locked->id,
                    'type'             => 'withdraw',
                    'amount'           => $request->amount,
                    'balance_before'   => $balanceBefore,
                    'balance_after'    => $balanceAfter,
                    'description'      => $request->description,
                ]);
            });

            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Withdraw berhasil.',
                'data'    => new TransactionResource($transaction->load('member')),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'code'    => 422,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
