<?php

namespace App\Filament\Resources\Technologies\Tables;

use App\Enums\TechnologyType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TechnologiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('type')->badge(),
                IconColumn::make('is_featured')->label('Featured')->boolean(),
                TextColumn::make('projects_count')->counts('projects')->label('Projects')->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')->options(TechnologyType::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
