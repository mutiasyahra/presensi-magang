<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithDrawings, WithEvents
{
    private $collection;

    public function __construct()
    {
        $this->collection = Attendance::with(['user', 'user.intern'])->orderBy('attendance_date', 'desc')->get();
    }

    public function collection()
    {
        return $this->collection;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Peserta',
            'Intern ID',
            'Proyek',
            'Tanggal',
            'Clock In',
            'Status In',
            'Rencana Kegiatan',
            'Clock Out',
            'Status Out',
            'Progress Kegiatan',
            'Bukti (Evidence)',
            'Lokasi',
            'Durasi Kerja'
        ];
    }

    public function map($attendance): array
    {
        static $no = 1;

        $duration = '-';
        if ($attendance->clock_in && $attendance->clock_out) {
            $start = \Carbon\Carbon::parse($attendance->clock_in);
            $end = \Carbon\Carbon::parse($attendance->clock_out);
            $diff = $start->diff($end);
            $duration = $diff->format('%hh %im');
        }

        return [
            $no++,
            $attendance->user->name ?? '-',
            $attendance->user->intern->intern_id ?? '-',
            $attendance->user->intern->project ?? '-',
            $attendance->attendance_date,
            $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-',
            $attendance->clock_in_status ?? '-',
            $attendance->rencana_kegiatan ?? '-',
            $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-',
            $attendance->clock_out_status ?? '-',
            $attendance->progress_kegiatan ?? '-',
            $this->getEvidenceCellContent($attendance->evidence),
            $attendance->clock_in_location ?? '-',
            $duration
        ];
    }

    public function drawings()
    {
        $drawings = [];
        $row = 2; // Data starts at row 2

        foreach ($this->collection as $attendance) {
            if ($attendance->evidence) {
                $path = storage_path('app/public/' . $attendance->evidence);
                
                // Only embed if file exists and is an image (not PDF)
                if (file_exists($path) && in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'])) {
                    $drawing = new Drawing();
                    $drawing->setName('Evidence');
                    $drawing->setDescription('Evidence Photo');
                    $drawing->setPath($path);
                    $drawing->setHeight(50);
                    $drawing->setCoordinates('L' . $row); // Column L is the 12th column (Bukti)
                    $drawing->setOffsetX(10);
                    $drawing->setOffsetY(10);
                    $drawings[] = $drawing;
                }
            }
            $row++;
        }

        return $drawings;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $totalRows = $this->collection->count() + 1;
                
                // Set row height for data rows to accommodate images
                for ($i = 2; $i <= $totalRows; $i++) {
                    $event->sheet->getDelegate()->getRowDimension($i)->setRowHeight(60);
                }

                // Set column width for Evidence column
                $event->sheet->getDelegate()->getColumnDimension('L')->setWidth(25);
                
                // Horizontal and Vertical Alignment
                $event->sheet->getDelegate()->getStyle('A1:N' . $totalRows)->getAlignment()->setVertical('center');
                $event->sheet->getDelegate()->getStyle('A1:N' . $totalRows)->getAlignment()->setWrapText(true);
            },
        ];
    }

    private function getEvidenceCellContent($evidence)
    {
        if (!$evidence) return '-';

        $path = storage_path('app/public/' . $evidence);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        // If it's an image that will be embedded as a drawing, return empty string so text doesn't overlap
        if (file_exists($path) && in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            return '';
        }

        // If it's a PDF or something else, return the URL
        return url('storage/' . $evidence);
    }
}