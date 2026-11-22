<?php

namespace App\Filament\Resources\Experiences\Schemas;

use App\Enums\PublicationStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
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
                TextInput::make('role_title')->label('Role')->required()->maxLength(160),
                TextInput::make('organisation')->required()->maxLength(160),
                TextInput::make('location')->maxLength(120)->helperText('For example the department or site.'),
                Toggle::make('is_current')->label('Current role'),
                DatePicker::make('start_date')->required()->helperText('Shown as month and year.'),
                DatePicker::make('end_date')->afterOrEqual('start_date')->helperText('Leave empty for a current role.'),
                Textarea::make('summary')->rows(3)->maxLength(2000)->columnSpanFull(),
                Repeater::make('highlights')
                    ->relationship('highlights')
                    ->orderColumn('sort_order')
                    ->reorderable()
                    ->defaultItems(0)
                    ->addActionLabel('Add highlight')
                    ->helperText('Only verified responsibilities and achievements.')
                    ->schema([
                        TextInput::make('body')->label('Highlight')->required()->maxLength(400),
                    ])
                    ->columnSpanFull(),
                Select::make('technologies')
                    ->relationship('technologies', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),
                Select::make('publication_status')
                    ->label('Status')
                    ->options(PublicationStatus::class)
                    ->default(PublicationStatus::Draft->value)
                    ->required(),
                TextInput::make('sort_order')->numeric()->default(0)->required(),
            ]);
    }
}
