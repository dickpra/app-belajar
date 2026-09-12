<?php

namespace App\Filament\Teacher\Resources\EvaluationResource\Pages;

use App\Filament\Teacher\Resources\EvaluationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEvaluations extends ListRecords
{
    protected static string $resource = EvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
