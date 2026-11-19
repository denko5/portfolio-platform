<?php

namespace App\Filament\Resources\Education\Schemas;

use App\Enums\PublicationStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class EducationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('institution')
                    ->required(),
                TextInput::make('qualification')
                    ->required(),
                TextInput::make('start_year')
                    ->required()
                    ->numeric(),
                TextInput::make('end_year')
                    ->numeric(),
                Textarea::make('description')
                    ->columnSpanFull(),
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
