<?php

namespace App\Filament\Resources\Experiences\Schemas;

use App\Enums\PublicationStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ExperienceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('organisation')
                    ->required(),
                TextInput::make('role_title')
                    ->required(),
                TextInput::make('location'),
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date'),
                Toggle::make('is_current')
                    ->required(),
                Textarea::make('summary')
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
