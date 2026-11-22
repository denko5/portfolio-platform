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
                TextInput::make('qualification')->required()->maxLength(180),
                TextInput::make('institution')->required()->maxLength(180),
                TextInput::make('start_year')->numeric()->required()->minValue(1950)->maxValue(2100),
                TextInput::make('end_year')->numeric()->minValue(1950)->maxValue(2100)->gte('start_year')->helperText('Leave empty if ongoing.'),
                Textarea::make('description')->rows(3)->columnSpanFull(),
                Select::make('publication_status')
                    ->label('Status')
                    ->options(PublicationStatus::class)
                    ->default(PublicationStatus::Draft->value)
                    ->required(),
                TextInput::make('sort_order')->numeric()->default(0)->required(),
            ]);
    }
}
