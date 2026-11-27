<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use App\Enums\EnquiryStatus;
use App\Enums\EnquiryType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ContactMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // The visitor's details are read-only: only the status and your notes can change.
                TextInput::make('name')->disabled()->dehydrated(false),
                TextInput::make('email')->disabled()->dehydrated(false),
                TextInput::make('organisation')->disabled()->dehydrated(false),
                Select::make('enquiry_type')->label('Type')->options(EnquiryType::class)->disabled()->dehydrated(false),
                TextInput::make('subject')->disabled()->dehydrated(false)->columnSpanFull(),
                Textarea::make('message')->rows(8)->disabled()->dehydrated(false)->columnSpanFull(),
                TextInput::make('budget_range')->disabled()->dehydrated(false),
                TextInput::make('reference')->disabled()->dehydrated(false),
                DateTimePicker::make('created_at')->label('Received')->disabled()->dehydrated(false),
                DateTimePicker::make('privacy_accepted_at')->label('Privacy notice accepted')->disabled()->dehydrated(false),
                Select::make('status')
                    ->options(EnquiryStatus::class)
                    ->required(),
                Textarea::make('admin_notes')
                    ->label('Your notes (private)')
                    ->rows(5)
                    ->maxLength(5000)
                    ->columnSpanFull(),
            ]);
    }
}
