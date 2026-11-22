<?php

namespace App\Filament\Resources\SkillCategories\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SkillCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(80)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state, string $operation): void {
                        if ($operation === 'create') {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(80)
                    ->alphaDash()
                    ->unique(ignoreRecord: true),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Repeater::make('skills')
                    ->relationship('skills')
                    ->orderColumn('sort_order')
                    ->reorderable()
                    ->defaultItems(0)
                    ->addActionLabel('Add skill')
                    ->helperText('Plain skills only. No percentage bars or proficiency ratings.')
                    ->schema([
                        TextInput::make('name')->required()->maxLength(80),
                        TextInput::make('description')->maxLength(200),
                        Toggle::make('is_visible')->label('Visible')->default(true),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
