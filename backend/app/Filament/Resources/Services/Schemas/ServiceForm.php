<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Enums\PublicationStatus;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(120)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state, string $operation): void {
                        if ($operation === 'create') {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(120)
                    ->alphaDash()
                    ->unique(ignoreRecord: true),
                Textarea::make('summary')
                    ->required()
                    ->maxLength(300)
                    ->rows(3)
                    ->columnSpanFull(),
                Select::make('icon_key')
                    ->label('Icon')
                    ->options([
                        'chart' => 'Chart',
                        'pie' => 'Pie chart',
                        'globe' => 'Globe',
                        'code' => 'Code',
                        'database' => 'Database',
                        'server' => 'Server',
                        'video' => 'Video',
                    ])
                    ->required(),
                Select::make('publication_status')
                    ->label('Status')
                    ->options(PublicationStatus::class)
                    ->default(PublicationStatus::Draft->value)
                    ->required(),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Repeater::make('items')
                    ->relationship('items')
                    ->orderColumn('sort_order')
                    ->reorderable()
                    ->defaultItems(0)
                    ->addActionLabel('Add item')
                    ->schema([
                        TextInput::make('body')->label('Item')->required()->maxLength(200),
                    ])
                    ->columnSpanFull(),
                Select::make('technologies')
                    ->relationship('technologies', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),
            ]);
    }
}
