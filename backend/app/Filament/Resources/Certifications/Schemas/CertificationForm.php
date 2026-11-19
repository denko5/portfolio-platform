<?php

namespace App\Filament\Resources\Certifications\Schemas;

use App\Enums\PublicationStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CertificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('issuer')
                    ->required(),
                DatePicker::make('issued_on')
                    ->required(),
                TextInput::make('credential_url')
                    ->url(),
                TextInput::make('note'),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('publication_status')
                    ->options(PublicationStatus::class)
                    ->default('draft')
                    ->required(),
            ]);
    }
}
