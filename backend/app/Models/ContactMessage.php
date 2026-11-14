<?php

namespace App\Models;

use App\Enums\EnquiryStatus;
use App\Enums\EnquiryType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ContactMessage extends Model
{
    protected $fillable = ['name', 'email', 'organisation', 'enquiry_type', 'subject', 'message', 'budget_range', 'privacy_accepted_at', 'ip_hash'];

    protected function casts(): array
    {
        return [
            'enquiry_type' => EnquiryType::class,
            'status' => EnquiryStatus::class,
            'privacy_accepted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $message): void {
            $message->reference ??= (string) Str::ulid();
            $message->status ??= EnquiryStatus::New;
        });
    }
}
