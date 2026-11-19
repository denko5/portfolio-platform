<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Enums\PublicationStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slug')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('summary')
                    ->required(),
                TextInput::make('icon_key')
                    ->required(),
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
