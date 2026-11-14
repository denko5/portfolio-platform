<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Enums\PublicationStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slug')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                TextInput::make('summary')
                    ->required(),
                Textarea::make('problem')
                    ->columnSpanFull(),
                Textarea::make('objectives')
                    ->columnSpanFull(),
                Textarea::make('role')
                    ->columnSpanFull(),
                Textarea::make('approach')
                    ->columnSpanFull(),
                Textarea::make('challenges')
                    ->columnSpanFull(),
                Textarea::make('outcomes')
                    ->columnSpanFull(),
                Textarea::make('lessons')
                    ->columnSpanFull(),
                Textarea::make('description_markdown')
                    ->columnSpanFull(),
                TextInput::make('status_label'),
                Textarea::make('public_note')
                    ->columnSpanFull(),
                TextInput::make('link_note'),
                Select::make('publication_status')
                    ->options(PublicationStatus::class)
                    ->default('draft')
                    ->required(),
                DateTimePicker::make('published_at'),
                Toggle::make('is_featured')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('seo_title'),
                TextInput::make('seo_description'),
            ]);
    }
}
