<?php

namespace App\Filament\Resources\Education\Tables;

use App\Enums\PublicationStatus;
use App\Filament\Support\PublishingActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EducationTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('qualification')->searchable()->sortable()->wrap(),
                TextColumn::make('institution')->searchable()->wrap(),
                TextColumn::make('start_year'),
                TextColumn::make('end_year')->placeholder('Ongoing'),
                TextColumn::make('publication_status')
                    ->label('Status')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('publication_status')->label('Status')->options(PublicationStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    PublishingActions::publish(),
                    PublishingActions::unpublish(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
