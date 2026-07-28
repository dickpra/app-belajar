<?php

namespace App\Filament\Admin\Resources\EvaluasiResource\Pages;

use App\Filament\Admin\Resources\EvaluasiResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEvaluasi extends ViewRecord
{
    protected static string $resource = EvaluasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EvaluasiResource\Widgets\EvaluasiStats::class,
        ];
    }
}
