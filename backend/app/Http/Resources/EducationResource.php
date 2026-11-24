<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EducationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'institution' => $this->institution,
            'qualification' => $this->qualification,
            'start_year' => $this->start_year,
            'end_year' => $this->end_year,
            'description' => $this->description,
        ];
    }
}
