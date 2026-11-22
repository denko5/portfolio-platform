<?php

namespace App\Filament\Resources\Technologies\Schemas;

use App\Enums\TechnologyType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TechnologyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(60)
                    ->unique(ignoreRecord: true)
                    ->dehydrateStateUsing(fn (?string $state): string => trim((string) $state))
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state, string $operation): void {
                        if ($operation === 'create') {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(60)
                    ->alphaDash()
                    ->unique(ignoreRecord: true),
                Select::make('type')
                    ->options(TechnologyType::class)
                    ->required()
                    ->helperText('Language, tool, platform or concept (for example EDA).'),
                Toggle::make('is_featured')->label('Featured in the scrolling banner'),
                TextInput::make('sort_order')->numeric()->default(0)->required(),
            ]);
    }
}
