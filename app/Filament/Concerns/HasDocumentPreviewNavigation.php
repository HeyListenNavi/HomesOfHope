<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;

trait HasDocumentPreviewNavigation
{
    /**
     * Compute previous/next record IDs and position from the current filtered and sorted table query.
     *
     * @return array{previousRecordId: ?string, nextRecordId: ?string, currentPosition: ?int, totalCount: int}
     */
    protected function getPreviewNavigationData(Model $record): array
    {
        $recordIds = $this->getFilteredSortedTableQuery()
            ->pluck($record->getQualifiedKeyName())
            ->map(fn (mixed $id): string => (string) $id)
            ->values()
            ->all();

        $currentIndex = array_search((string) $record->getKey(), $recordIds, true);
        $totalCount = count($recordIds);

        if ($currentIndex === false) {
            return [
                'previousRecordId' => null,
                'nextRecordId' => null,
                'currentPosition' => null,
                'totalCount' => $totalCount,
            ];
        }

        return [
            'previousRecordId' => $currentIndex > 0 ? $recordIds[$currentIndex - 1] : null,
            'nextRecordId' => $currentIndex < $totalCount - 1 ? $recordIds[$currentIndex + 1] : null,
            'currentPosition' => $currentIndex + 1,
            'totalCount' => $totalCount,
        ];
    }
}
