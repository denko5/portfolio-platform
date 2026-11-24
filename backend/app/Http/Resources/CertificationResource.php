<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CertificationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'issuer' => $this->issuer,
            'issued_on' => $this->issued_on?->toDateString(),
            'credential_url' => $this->credential_url,
            'note' => $this->note,
        ];
    }
}
