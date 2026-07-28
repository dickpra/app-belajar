<?php

namespace App\Filament\Admin\Resources\EvaluasiResource\Pages;

use App\Filament\Admin\Resources\EvaluasiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEvaluasi extends EditRecord
{
    protected static string $resource = EvaluasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
