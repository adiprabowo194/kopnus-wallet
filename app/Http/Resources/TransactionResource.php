<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'reference_no'    => $this->reference_no,
            'type'            => $this->type,
            'amount'          => (float) $this->amount, // aman untuk decimal
            'balance_before'  => (float) $this->balance_before,
            'balance_after'   => (float) $this->balance_after,
            'status'          => $this->status,
            'description'     => $this->description,
            'member' => [
                'member_code' => $this->member->member_code,
                'name'        => $this->member->name,
            ],

            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
