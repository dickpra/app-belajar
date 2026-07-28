<?php

namespace App\Filament\Admin\Resources\EvaluasiResource\Pages;

use App\Filament\Admin\Resources\EvaluasiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEvaluasis extends ListRecords
{
    protected static string $resource = EvaluasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
