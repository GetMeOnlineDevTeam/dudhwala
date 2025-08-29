<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class UsersExport implements FromArray, WithHeadings, WithStyles, WithEvents
{
    protected array $filters = [];
    /** @var array<int, string|null> */
    protected array $docLinks = []; // row-aligned list of URLs for AfterSheet

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function array(): array
    {
        $q = User::query()
            ->where('role', 'user')
            ->with(['documents' => function ($q) {
                $q->latest(); // so first() is the latest
            }]);

        if (!empty($this->filters['search'])) {
            $s = $this->filters['search'];
            $q->where(function ($qq) use ($s) {
                $qq->where('first_name', 'like', "%{$s}%")
                   ->orWhere('last_name',  'like', "%{$s}%");
            });
        }

        if (isset($this->filters['verification']) && $this->filters['verification'] !== '') {
            $q->where('is_verified', (int) $this->filters['verification']);
        }

        $rows = $q->orderBy('id', 'desc')
            ->get(['id','first_name','last_name','contact_number','is_verified'])
            ->map(function ($u) {
                $doc = $u->documents->first();
                $url = $doc && !empty($doc->document)
                    ? Storage::disk('public')->url($doc->document)
                    : null;

                // keep URL for hyperlinking after-sheet
                $this->docLinks[] = $url;

                // ID | Full Name | Contact | Document Verification | Document
                return [
                    $u->id,
                    trim(($u->first_name ?? '').' '.($u->last_name ?? '')),
                    $u->contact_number ?? '',
                    $u->is_verified ? 'Verified' : 'Not Verified',
                    $url ? 'Download' : '',
                ];
            })
            ->toArray();

        return $rows;
    }

    public function headings(): array
    {
        // Correct order
        return ['ID', 'Full Name', 'Contact', 'Verification', 'Document'];
    }

    public function styles(Worksheet $sheet)
    {
        $cols = count($this->headings());
        for ($i = 1; $i <= $cols; $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }
        $sheet->getStyle('A1:'.Coordinate::stringFromColumnIndex($cols).'1')->getFont()->setBold(true);
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $startRow = 2;
                // "Document" is the 5th column (E)
                $docColIndex = 5;
                $sheet = $event->sheet->getDelegate();

                foreach ($this->docLinks as $i => $url) {
                    if (!$url) continue;

                    $cell = Coordinate::stringFromColumnIndex($docColIndex) . ($startRow + $i);

                    // ensure cell has text
                    if ($sheet->getCell($cell)->getValue() === '') {
                        $sheet->setCellValue($cell, 'Download');
                    }

                    // set hyperlink + basic link styling
                    $sheet->getCell($cell)->getHyperlink()->setUrl($url);
                    $sheet->getStyle($cell)->getFont()->setUnderline(true);
                    $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                }
            },
        ];
    }
}
