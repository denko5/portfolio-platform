<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Enums\PublicationStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('category.name')
                    ->badge()
                    ->sortable(),
                TextColumn::make('publication_status')
                    ->label('Status')
                    ->badge(),
                IconColumn::make('on_hold')
                    ->label('On hold')
                    ->boolean()
                    ->state(fn ($record): bool => filled($record->publish_block_reason))
                    ->trueColor('warning')
                    ->falseColor('gray'),
                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),
                TextColumn::make('status_label')
                    ->label('Label')
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name'),
                SelectFilter::make('publication_status')
                    ->label('Status')
                    ->options(PublicationStatus::class),
                TernaryFilter::make('is_featured')
                    ->label('Featured'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
