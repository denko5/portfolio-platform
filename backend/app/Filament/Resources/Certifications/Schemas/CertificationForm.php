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
                TextInput::make('title')->required()->maxLength(180),
                TextInput::make('issuer')->required()->maxLength(160),
                DatePicker::make('issued_on')->required()->helperText('Shown as month and year.'),
                TextInput::make('credential_url')->url()->maxLength(500)->helperText('Optional link to verify the certificate.'),
                TextInput::make('note')->maxLength(160)->helperText('For example "Graduated with a Merit".')->columnSpanFull(),
                Select::make('publication_status')
                    ->label('Status')
                    ->options(PublicationStatus::class)
                    ->default(PublicationStatus::Draft->value)
                    ->required(),
                TextInput::make('sort_order')->numeric()->default(0)->required(),
            ]);
    }
}
