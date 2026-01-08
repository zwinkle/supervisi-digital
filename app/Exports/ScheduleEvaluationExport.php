<?php

namespace App\Exports;

use App\Models\Schedule;
use App\Http\Controllers\EvaluationController;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ScheduleEvaluationExport implements FromArray, WithStyles, WithColumnWidths
{
    protected Schedule $schedule;

    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule->load(['school','supervisor','teacher','evaluations']);
    }

    /**
     * Membangun array data utama untuk sheet Excel.
     * Baris dibangun secara manual row-by-row.
     */
    public function array(): array
    {
        $s = $this->schedule;
        // Group evaluations berdasarkan tipe (rpp, pembelajaran, asesmen) untuk akses cepat
        $evalByType = $s->evaluations->keyBy('type');

        $rows = [];
        // --- Bagian Header Metadata ---
        $rows[] = ['Nama Guru', $s->teacher->name ?? ''];
        $rows[] = ['NIP', $s->teacher->nip ?? ''];
        $rows[] = ['Nama Sekolah', $s->school->name ?? ''];
        $rows[] = ['Nama Supervisor', $s->supervisor->name ?? ''];
        $rows[] = ['Tanggal Supervisi', optional($s->date)->format('d-m-Y')];
        $rows[] = ['Jenis Guru', $s->teacher->teacher_type_label ?? ''];
        $rows[] = ['Detail Penugasan', $s->teacher->teacher_detail_label ?? ''];
        $rows[] = ['Kelas Supervisi', $s->class_name ?? ''];

        // Helper closure untuk mengambil nilai evaluasi spesifik dari JSON breakdown
        $getVal = function(string $type, string $sectionKey, string $itemKey) use ($evalByType) {
            $e = $evalByType->get($type);
            if (!$e) return null;
            $bd = $e->breakdown ?? [];
            $key = $sectionKey.'.'.$itemKey;
            return array_key_exists($key, $bd) ? $bd[$key] : null;
        };

        // --- Bagian 1: RPP ---
        // Mengambil struktur instrumen RPP dari EvaluationController
        [$rppStructure, ] = EvaluationController::structureFor('rpp');
        $rows[] = []; // Spacer row
        $rows[] = ['RPP'];
        $rows[] = ['No.','Aspek yang Dinilai','Skor','Keterangan'];
        
        $no = 1; 
        $currentGroup = null; 
        $groupSum = 0; $groupCount = 0; 
        $grandSum = 0; $grandCount = 0;
        
        foreach ($rppStructure as $section) {
            // Logika grouping: Deteksi perubahan huruf awal key (misal A.1 -> A)
            $group = substr($section['key'],0,1);
            
            // Jika pindah group (misal A ke B), cetak subtotal group sebelumnya
            if ($currentGroup !== null && $group !== $currentGroup) {
                $rows[] = ['Subtotal '.$currentGroup, '', $groupCount ? $groupSum : '', ''];
                $groupSum = 0; $groupCount = 0;
            }
            
            // Cetak Header Group baru
            if ($group !== $currentGroup) {
                $rows[] = [$group.'. KOMPONEN', '', '', ''];
                $currentGroup = $group;
            }
            
            $rows[] = [$no, $section['title'], '', ''];
            $no++;
            
            foreach ($section['items'] as $itemKey => $label) {
                $val = $getVal('rpp', $section['key'], $itemKey);
                // Akumulasi skor jika ada nilai
                if ($val !== null && $val !== '') { 
                    $groupSum += (int)$val; 
                    $groupCount++; 
                    $grandSum += (int)$val; 
                    $grandCount++; 
                }
                $rows[] = ['', '• '.$label, is_null($val)?'':$val, ''];
            }
        }
        // Cetak subtotal group terakhir
        if ($currentGroup !== null) {
            $rows[] = ['Subtotal '.$currentGroup, '', $groupCount ? $groupSum : '', ''];
        }
        
        $rows[] = ['TOTAL SKOR RPP', '', $grandCount ? $grandSum : '', ''];
        $rppPercent = $this->computePercent('rpp', $rppStructure, $getVal);
        $rows[] = ['PERSENTASE RPP', '', $rppPercent !== null ? $rppPercent.'%' : '', ''];

        // --- Bagian 2: Pembelajaran (Deep Learning) ---
        [$pembStructure, ] = EvaluationController::structureFor('pembelajaran');
        $rows[] = [];
        $rows[] = ['PEMBELAJARAN (DEEP LEARNING)'];
        $rows[] = ['Aspek','Deskripsi','Ya','Tidak','Keterangan (Catatan)'];
        
        $yes = 0; $total = 0;
        foreach ($pembStructure as $section) {
            $rows[] = [$section['title'], '', '', '', ''];
            foreach ($section['items'] as $itemKey => $label) {
                $val = $getVal('pembelajaran', $section['key'], $itemKey);
                // Hitung total item yang dinilai (Ya/Tidak)
                if ($val === true || $val === false) { 
                    $total++; 
                    if ($val === true) $yes++; 
                }
                // 'Ya' check di kolom C, 'Tidak' check di kolom D
                $rows[] = ['', $label, $val === true ? '✓' : '', $val === false ? '✓' : '', ''];
            }
        }
        $rows[] = ['TOTAL YA', '', $yes, '', ''];
        $pembPercent = $total ? round(($yes/$total)*100, 2) : null;
        $rows[] = ['PERSENTASE PEMBELAJARAN', '', $pembPercent !== null ? $pembPercent.'%' : '', '', ''];

        // --- Bagian 3: Asesmen ---
        [$asesStructure, ] = EvaluationController::structureFor('asesmen');
        $rows[] = [];
        $rows[] = ['ASESMEN'];
        $rows[] = ['No.','Aspek yang Dinilai','Skor','Keterangan'];
        
        $no = 1; $currentGroup = null; $groupSum = 0; $groupCount = 0; $grandSum = 0; $grandCount = 0;
        foreach ($asesStructure as $section) {
            $group = substr($section['key'],0,1);
            if ($currentGroup !== null && $group !== $currentGroup) {
                $rows[] = ['Subtotal '.$currentGroup, '', $groupCount ? $groupSum : '', ''];
                $groupSum = 0; $groupCount = 0;
            }
            if ($group !== $currentGroup) {
                $rows[] = [$group.'. KOMPONEN', '', '', ''];
                $currentGroup = $group;
            }
            $rows[] = [$no, $section['title'], '', ''];
            $no++;
            foreach ($section['items'] as $itemKey => $label) {
                $val = $getVal('asesmen', $section['key'], $itemKey);
                if ($val !== null && $val !== '') { $groupSum += (int)$val; $groupCount++; $grandSum += (int)$val; $grandCount++; }
                $rows[] = ['', '• '.$label, is_null($val)?'':$val, ''];
            }
        }
        if ($currentGroup !== null) {
            $rows[] = ['Subtotal '.$currentGroup, '', $groupCount ? $groupSum : '', ''];
        }
        $rows[] = ['TOTAL SKOR ASESMEN', '', $grandCount ? $grandSum : '', ''];
        $asesPercent = $this->computePercent('asesmen', $asesStructure, $getVal);
        $rows[] = ['PERSENTASE ASESMEN', '', $asesPercent !== null ? $asesPercent.'%' : '', ''];

        return $rows;
    }

    /**
     * Menghitung persentase skor berdasarkan tipe evaluasi.
     * 
     * @param string $type Tipe evaluasi ('rpp', 'pembelajaran', 'asesmen')
     * @param array $structure Struktur instrumen
     * @param \Closure $getVal Helper untuk ambil nilai
     * @return float|null Nilai persentase atau null jika tidak ada data
     */
    protected function computePercent(string $type, array $structure, \Closure $getVal): ?float
    {
        if ($type === 'pembelajaran') {
            // Untuk Pembelajaran: (Jumlah Ya / Total Item) * 100
            $cnt = 0; $yes = 0;
            foreach ($structure as $sec) {
                foreach ($sec['items'] as $ik => $_) {
                    $v = $getVal('pembelajaran', $sec['key'], $ik);
                    if ($v === true || $v === false) { $cnt++; if ($v === true) $yes++; }
                }
            }
            return $cnt ? round(($yes/$cnt)*100, 2) : null;
        } else {
            // Untuk Skor Angka 1-4: (Total Skor Diperoleh / (Total Item * 4)) * 100
            $cnt = 0; $sum = 0;
            foreach ($structure as $sec) {
                foreach ($sec['items'] as $ik => $_) {
                    $v = $getVal($type, $sec['key'], $ik);
                    if ($v !== null && $v !== '') { $cnt++; $sum += (int)$v; }
                }
            }
            return $cnt ? round(($sum/($cnt*4))*100, 2) : null;
        }
    }

    public function styles(Worksheet $sheet)
    {
        // Styling dasar: Wrap text dan align top
        $highestRow = $sheet->getHighestRow();
        for ($row = 1; $row <= $highestRow; $row++) {
            $sheet->getStyle('A'.$row.':E'.$row)->getAlignment()->setWrapText(true)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        }
        
        // Bold untuk baris-baris header atau judul seksi tertentu
        foreach (['RPP','PEMBELAJARAN (DEEP LEARNING)','ASESMEN','No.','Aspek','Aspek yang Dinilai'] as $marker) {
            $cellIterator = $sheet->getRowIterator(1, $highestRow);
            foreach ($cellIterator as $row) {
                $rowIndex = $row->getRowIndex();
                $value = (string)($sheet->getCell('A'.$rowIndex)->getValue());
                if ($value === $marker) {
                    $sheet->getStyle('A'.$rowIndex.':E'.$rowIndex)->getFont()->setBold(true);
                }
            }
        }
        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 60,
            'C' => 12,
            'D' => 30, // Kolom D untuk "Tidak" (Pembelajaran) atau "Keterangan"
            'E' => 30, // Kolom E untuk Keterangan (Pembelajaran)
        ];
    }
}
