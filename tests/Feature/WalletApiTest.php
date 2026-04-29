<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\MemberSeeder;
use Tests\TestCase;

class WalletApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up aplikasi sebelum setiap test dijalankan.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        $this->seed(MemberSeeder::class);
    }

    /** @test */
    public function test_balance_tampil()
    {
        $response = $this->getJson("/api/wallet/MBR-0001/balance");

        $response->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Saldo ditampilkan.',
                'data'    => [
                    'member_code' => 'MBR-0001',
                    'name'        => 'Budi Santoso',
                    'balance'     => 500000.00,
                ],
            ]);
    }

    /** @test */
    public function test_deposit_berhasil()
    {
        $member = Member::where('member_code', 'MBR-0002')->first();
        $member->status = 'active';
        $member->save();


        $response = $this->postJson("/api/wallet/MBR-0002/deposit", [
            'amount'      => 50000,
            'description' => 'Setoran Tunai'
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1300000, $member->fresh()->balance);
    }

    /** @test */
    public function test_withdraw_berhasil()
    {

        $response = $this->postJson("/api/wallet/MBR-0001/withdraw", [
            'amount'      => 100000,
            'description' => 'Tarik Tunai ATM'
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Withdraw berhasil.']);
        $member = Member::where('member_code', 'MBR-0001')->first();
        $this->assertEquals(400000, $member->balance);
    }

    /** @test */
    public function test_withdraw_gagal_saldo_tidak_cukup()
    {

        $response = $this->postJson("/api/wallet/MBR-0003/withdraw", [
            'amount'      => 10000,
            'description' => 'Tarik Tunai'
        ]);

        $response->assertStatus(422)
            ->assertJson(['status' => 'error']);
    }

    /** @test */
    public function test_member_tidak_aktif()
    {
        $response = $this->getJson("/api/wallet/MBR-0004/balance");
        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Member tidak aktif.'
            ]);
    }
}
