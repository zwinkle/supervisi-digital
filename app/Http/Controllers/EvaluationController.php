<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EvaluationController extends Controller
{
    protected $types = ['rpp','pembelajaran','asesmen'];

    protected $requirementMessages = [
        'rpp' => 'Guru belum mengunggah file RPP.',
        'pembelajaran' => 'Guru belum mengunggah file video pembelajaran.',
        'asesmen' => 'Guru belum mengunggah file asesmen.',
    ];

    public function show(Request $request, Schedule $schedule, $type)
    {
        $user = Auth::user();
        if (!in_array($type, $this->types, true)) abort(404);
        // Supervisor must own the schedule
        if ($schedule->supervisor_id !== $user->id) abort(403);

        $schedule->loadMissing(['submission.documents.file','submission.videoFile']);
        if (!$schedule->hasSubmissionFor($type)) {
            return redirect()->route('supervisor.schedules.assessment', $schedule)
                ->with('error', $this->requirementMessages[$type] ?? 'Berkas pendukung belum tersedia.');
        }

        list($structure, $kind) = self::structureFor($type);
        $existing = Evaluation::where('schedule_id', $schedule->id)
            ->where('teacher_id', $schedule->teacher_id)
            ->where('type', $type)
            ->first();

        return view('supervisor.evaluations.form', [
            'schedule' => $schedule,
            'type' => $type,
            'kind' => $kind,
            'structure' => $structure,
            'existing' => $existing,
        ]);
    }

    public function store(Request $request, Schedule $schedule, $type)
    {
        $user = Auth::user();
        if (!in_array($type, $this->types, true)) abort(404);
        if ($schedule->supervisor_id !== $user->id) abort(403);

        $schedule->loadMissing(['submission.documents.file','submission.videoFile']);
        if (!$schedule->hasSubmissionFor($type)) {
            return redirect()->route('supervisor.schedules.assessment', $schedule)
                ->with('error', $this->requirementMessages[$type] ?? 'Berkas pendukung belum tersedia.');
        }

        list($structure, $kind) = $this->structureFor($type);

        // Build validation rules dynamically
        $rules = [];
        if ($type === 'pembelajaran') {
            foreach ($structure as $section) {
                $sec = $section['key'];
                foreach ($section['items'] as $itemKey => $label) {
                    $rules["{$sec}.{$itemKey}"] = ['nullable','boolean'];
                }
            }
        } else {
            foreach ($structure as $section) {
                $sec = $section['key'];
                foreach ($section['items'] as $itemKey => $label) {
                    $rules["{$sec}.{$itemKey}"] = ['nullable','integer','between:1,4'];
                }
            }
        }
        $validated = $request->validate($rules);

        // Flatten breakdown
        $breakdown = [];
        foreach ($validated as $sectionKey => $items) {
            foreach ($items as $itemKey => $val) {
                $key = $sectionKey.'.'.$itemKey;
                $breakdown[$key] = $type === 'pembelajaran' ? (bool)$val : (int)$val;
            }
        }

        // Compute totals
        list($totalScore, $category) = $this->computeTotals($type, $structure, $breakdown);

        $record = Evaluation::updateOrCreate(
            [
                'schedule_id' => $schedule->id,
                'teacher_id' => $schedule->teacher_id,
                'type' => $type,
            ],
            [
                'breakdown' => $breakdown,
                'total_score' => $totalScore,
                'category' => $category,
            ]
        );

        // After saving, try to mark schedule as completed if requirements satisfied
        try {
            $schedule->checkAndMarkCompleted();
        } catch (\Throwable $e) {
            // ignore marking errors; not critical for saving evaluation
        }

        return redirect()->route('supervisor.schedules')
            ->with('success', 'Penilaian disimpan: '.$type.' (Skor: '.($totalScore ?? '-').($category? ' / '.$category : '').')');
    }

    public static function structureFor($type)
    {
        if ($type === 'rpp') {
            // RPP: Komponen A-E, tiap komponen berisi aspek sesuai rubrik
            $structure = [
                [ 'key' => 'A1', 'title' => 'Identifikasi Peserta Didik', 'items' => [
                    'a1_1' => 'Karakteristik Peserta Didik Jelas',
                    'a1_2' => 'Kebutuhan Belajar Terakomodasi',
                    'a1_3' => 'Gaya Belajar Dipertimbangkan',
                ]],
                [ 'key' => 'A2', 'title' => 'Analisis Materi Pelajaran', 'items' => [
                    'a2_1' => 'Sesuai Kurikulum Berlaku',
                    'a2_2' => 'Relevan Dengan Kehidupan Peserta Didik',
                    'a2_3' => 'Kompleksitas Sesuai Tingkat Kelas',
                ]],
                [ 'key' => 'A3', 'title' => 'Pemilihan Dimensi Profil Lulusan (DPL)', 'items' => [
                    'a3_1' => 'Minimal 2 DPL Dipilih Tepat',
                    'a3_2' => 'DPL Relevan Dengan Materi/Kegiatan',
                    'a3_3' => 'Integrasi Antar DPL Jelas',
                ]],

                [ 'key' => 'B1', 'title' => 'Capaian Pembelajaran', 'items' => [
                    'b1_1' => 'Jelas dan Terukur',
                    'b1_2' => 'Sesuai Standar Kurikulum',
                    'b1_3' => 'Mencerminkan Pembelajaran Mendalam',
                ]],
                [ 'key' => 'B2', 'title' => 'Lintas Disiplin Ilmu', 'items' => [
                    'b2_1' => 'Integrasi Disiplin Ilmu Lain',
                    'b2_2' => 'Koneksi Antar Mapel Jelas',
                    'b2_3' => 'Pendekatan Holistik',
                ]],
                [ 'key' => 'B3', 'title' => 'Tujuan Pembelajaran', 'items' => [
                    'b3_1' => 'Spesifik, Terukur, Dapat Dicapai',
                    'b3_2' => 'Kata Kerja Operasional Tepat',
                    'b3_3' => 'Selaras Dengan Capaian',
                ]],
                [ 'key' => 'B4', 'title' => 'Topik Pembelajaran', 'items' => [
                    'b4_1' => 'Relevan dan Kontekstual',
                    'b4_2' => 'Mendukung Tujuan',
                    'b4_3' => 'Menarik Minat Peserta Didik',
                ]],
                [ 'key' => 'B5', 'title' => 'Praktik Pedagogis', 'items' => [
                    'b5_1' => 'Strategi Inovatif dan Bervariasi',
                    'b5_2' => 'Sesuai Pembelajaran Mendalam',
                    'b5_3' => 'Mendorong Berpikir Tingkat Tinggi',
                ]],
                [ 'key' => 'B6', 'title' => 'Kemitraan Pembelajaran', 'items' => [
                    'b6_1' => 'Melibatkan Berbagai Pihak',
                    'b6_2' => 'Kemitraan Mendukung Tujuan',
                    'b6_3' => 'Peran Masing-Masing Pihak Jelas',
                ]],
                [ 'key' => 'B7', 'title' => 'Lingkungan Pembelajaran', 'items' => [
                    'b7_1' => 'Lingkungan Kondusif',
                    'b7_2' => 'Ruang/Sumber Belajar Optimal',
                    'b7_3' => 'Fleksibilitas Pengaturan Ruang',
                ]],
                [ 'key' => 'B8', 'title' => 'Pemanfaatan Digital', 'items' => [
                    'b8_1' => 'Teknologi Terintegrasi',
                    'b8_2' => 'Platform Digital Mendukung',
                    'b8_3' => 'Literasi Digital Dikembangkan',
                ]],

                [ 'key' => 'C1', 'title' => 'Kegiatan Awal', 'items' => [
                    'c1_1' => 'Berkesan',
                    'c1_2' => 'Berkesadaran',
                    'c1_3' => 'Bermakna',
                ]],
                [ 'key' => 'C2', 'title' => 'Kegiatan Inti - Memahami', 'items' => [
                    'c2_1' => 'Berkesadaran (Reflektif)',
                    'c2_2' => 'Bermakna (Koneksi Pengetahuan)',
                ]],
                [ 'key' => 'C3', 'title' => 'Kegiatan Inti - Mengaplikasi', 'items' => [
                    'c3_1' => 'Berkesadaran (Penerapan Disadari)',
                    'c3_2' => 'Bermakna (Konteks Nyata)',
                    'c3_3' => 'Menggembirakan (Tanpa Tekanan)',
                ]],
                [ 'key' => 'C4', 'title' => 'Kegiatan Inti - Merefleksi', 'items' => [
                    'c4_1' => 'Berkesadaran (Refleksi Mendalam)',
                    'c4_2' => 'Menggembirakan (Suasana Positif)',
                ]],
                [ 'key' => 'C5', 'title' => 'Kegiatan Penutup', 'items' => [
                    'c5_1' => 'Berkesadaran (Kesimpulan/Penguatan)',
                    'c5_2' => 'Bermakna (Koneksi Lanjutan)',
                ]],

                [ 'key' => 'D1', 'title' => 'Asesmen Awal Pembelajaran', 'items' => [
                    'd1_1' => 'Mengukur Pengetahuan Prasyarat',
                    'd1_2' => 'Sesuai Tujuan Pembelajaran',
                    'd1_3' => 'Metode Bervariasi',
                ]],
                [ 'key' => 'D2', 'title' => 'Asesmen Proses Pembelajaran', 'items' => [
                    'd2_1' => 'Assessment For Learning Terintegrasi',
                    'd2_2' => 'Feedback Formatif Berkelanjutan',
                    'd2_3' => 'Monitoring Kemajuan Peserta Didik',
                ]],
                [ 'key' => 'D3', 'title' => 'Asesmen Akhir Pembelajaran', 'items' => [
                    'd3_1' => 'Assessment Of Learning Komprehensif',
                    'd3_2' => 'Mengukur Pencapaian Tujuan',
                    'd3_3' => 'Metode Autentik',
                ]],
                [ 'key' => 'D4', 'title' => 'Kesesuaian Prinsip Asesmen', 'items' => [
                    'd4_1' => 'Assessment As Learning Diterapkan',
                    'd4_2' => 'For/Of Learning Seimbang',
                    'd4_3' => 'Dukung Pembelajaran Mendalam',
                ]],
                [ 'key' => 'D5', 'title' => 'Rubrik Penilaian', 'items' => [
                    'd5_1' => 'Rubrik Jelas dan Terukur',
                    'd5_2' => 'Indikator Sesuai Tujuan',
                    'd5_3' => 'Tingkatan Terdefinisi Jelas',
                ]],

                [ 'key' => 'E1', 'title' => 'Koherensi dan Konsistensi', 'items' => [
                    'e1_1' => 'Keterkaitan Antar Komponen',
                    'e1_2' => 'Alur Logis dan Sistematis',
                    'e1_3' => 'Tanpa Kontradiksi',
                ]],
                [ 'key' => 'E2', 'title' => 'Inovasi dan Kreativitas', 'items' => [
                    'e2_1' => 'Pendekatan Inovatif',
                    'e2_2' => 'Strategi Kreatif dan Menarik',
                    'e2_3' => 'Pembelajaran Abad 21',
                ]],
                [ 'key' => 'E3', 'title' => 'Kelengkapan Dokumen', 'items' => [
                    'e3_1' => 'Komponen Terisi Lengkap',
                    'e3_2' => 'Format Sesuai Template',
                    'e3_3' => 'Dokumen Rapi dan Mudah Dipahami',
                ]],
            ];
            return [$structure, 'skor'];
        }
        if ($type === 'asesmen') {
            // Asesmen: indikator 1-4 per aspek
            $structure = [
                [ 'key' => 'A1', 'title' => 'Perencanaan DL', 'items' => [
                    'a1_1' => 'Instrumen terintegrasi dalam RPP/modul DL',
                    'a1_2' => 'Tujuan selaras HOTS, keterampilan abad 21, transfer',
                ]],
                [ 'key' => 'A2', 'title' => 'Desain Instrumen', 'items' => [
                    'a2_1' => 'Mendorong analisis, evaluasi, sintesis',
                    'a2_2' => 'Konteks nyata/situasi kompleks; ukur pemahaman',
                ]],
                [ 'key' => 'A3', 'title' => 'Variasi Teknik Penilaian', 'items' => [
                    'a3_1' => 'Teknik autentik: proyek, portofolio, studi kasus, dll',
                    'a3_2' => 'Sesuai karakteristik materi & tujuan DL',
                ]],
                [ 'key' => 'A4', 'title' => 'Pelaksanaan Penilaian', 'items' => [
                    'a4_1' => 'Siswa bebas eksplorasi, bertanya, diskusi',
                    'a4_2' => 'Stimulus berpikir kritis/reflektif; objektif & transparan',
                ]],
                [ 'key' => 'A5', 'title' => 'Umpan Balik & Refleksi', 'items' => [
                    'a5_1' => 'Umpan balik konstruktif untuk perbaikan',
                    'a5_2' => 'Siswa refleksi proses & hasil belajar',
                ]],
                [ 'key' => 'A6', 'title' => 'Tindak Lanjut Penilaian', 'items' => [
                    'a6_1' => 'Hasil untuk modifikasi pembelajaran berikutnya',
                    'a6_2' => 'Kaitkan hasil belajar dengan kehidupan nyata/lintas mapel',
                ]],
            ];
            return [$structure, 'skor'];
        }
        // Pembelajaran (Ya/Tidak per deskripsi)
        $structure = [
            [ 'key' => 'A1', 'title' => 'Berkesadaran (mindful)', 'items' => [
                'a11' => 'Asesmen awal untuk mengetahui kondisi awal & kebutuhan',
                'a12' => 'Arahkan & motivasi belajar aktif dan antusias',
                'a13' => 'Variasi strategi/metode agar mencapai tujuan',
            ]],
            [ 'key' => 'A2', 'title' => 'Bermakna (meaningful)', 'items' => [
                'a21' => 'Contoh kontekstual sesuai kehidupan/lingkungan',
                'a22' => 'Pengalaman nyata dalam pembelajaran',
                'a23' => 'Refleksi makna (lisan/tulisan/gambar/simbol)',
            ]],
            [ 'key' => 'A3', 'title' => 'Menggembirakan (joyful)', 'items' => [
                'a31' => 'Komunikasi interaktif guru-siswa',
                'a32' => 'Strategi yang membuat siswa antusias dan gembira',
                'a33' => 'Sesekali ice breaker/game/kuis untuk motivasi',
            ]],

            [ 'key' => 'B1', 'title' => 'Memahami', 'items' => [
                'b11' => 'Tujuan pembelajaran jelas dan runtut',
                'b12' => 'Materi sistematis (mudah→sulit, konkrit→abstrak)',
                'b13' => 'Beragam sumber belajar',
                'b14' => 'Media/alat peraga relevan',
                'b15' => 'Strategi kontekstual/nyata/bermakna (konstruktivisme)',
                'b16' => 'Dorong berpikir kritis (tanya, pendapat, diskusi, kelompok)',
                'b17' => 'Ekspresi pemahaman (lisan, tulisan, gambar, video, dll.)',
                'b18' => 'Asesmen formatif dalam proses',
                'b19' => 'Suasana belajar menyenangkan & bermakna',
                'b1_10' => 'Kesempatan observasi/eksperimen/praktik langsung',
            ]],
            [ 'key' => 'B2', 'title' => 'Mengaplikasikan', 'items' => [
                'b21' => 'Penerapan kontekstual/sesuai kehidupan nyata',
                'b22' => 'Tunjukkan kemampuan (demo/simulasi/proyek/produk)',
            ]],
            [ 'key' => 'B3', 'title' => 'Merefleksikan', 'items' => [
                'b31' => 'Siswa sampaikan kesan & perasaan',
                'b32' => 'Refleksi hal dikuasai/belum/yang ditingkatkan',
            ]],

            [ 'key' => 'C1', 'title' => 'Praktik Pedagogik', 'items' => [
                'c11' => 'Dorong berpikir kritis/tingkat tinggi/praktik nyata',
                'c12' => 'Beragam strategi kreatif (PBL/PjBL/inquiry/STEM/dll)',
            ]],
            [ 'key' => 'C2', 'title' => 'Kemitraan Pembelajaran', 'items' => [
                'c21' => 'Siswa sebagai rekan belajar',
                'c22' => 'Libatkan rekan kerja/ahli/praktisi',
            ]],
            [ 'key' => 'C3', 'title' => 'Lingkungan Belajar', 'items' => [
                'c31' => 'Budaya belajar positif untuk capai tujuan',
                'c32' => 'Ruang belajar aman & nyaman',
                'c33' => 'Manfaatkan lingkungan sekitar sebagai sumber',
            ]],
            [ 'key' => 'C4', 'title' => 'Pemanfaatan Digital', 'items' => [
                'c41' => 'Perangkat digital menunjang efektif/interaktif/kolaboratif',
                'c42' => 'Guru cakap menggunakan perangkat digital',
            ]],
        ];
        return [$structure, 'ya_tidak'];
    }

    private function computeTotals($type, $structure, $breakdown)
    {
        if ($type === 'pembelajaran') {
            $total = 0; $count = 0;
            foreach ($structure as $section) {
                foreach ($section['items'] as $key => $_) {
                    $count++;
                    $val = (bool)($breakdown[$section['key'].'.'.$key] ?? false);
                    if ($val) $total++;
                }
            }
            $percent = $count > 0 ? round(($total / $count) * 100, 2) : null;
            $category = null;
            if ($percent !== null) {
                if ($percent < 60) $category = 'Kurang';
                elseif ($percent < 76) $category = 'Cukup';
                elseif ($percent < 86) $category = 'Baik';
                else $category = 'Sangat Baik';
            }
            return [$percent, $category];
        }
        // rpp/asesmen (1-4); only count provided values
        $sum = 0; $count = 0;
        foreach ($structure as $section) {
            foreach ($section['items'] as $key => $_) {
                $has = array_key_exists($section['key'].'.'.$key, $breakdown) && $breakdown[$section['key'].'.'.$key] !== null && $breakdown[$section['key'].'.'.$key] !== '';
                if (!$has) continue;
                $count++;
                $val = (int)$breakdown[$section['key'].'.'.$key];
                $sum += $val;
            }
        }
        $percent = $count > 0 ? round(($sum / ($count * 4)) * 100, 2) : null;
        return [$percent, null];
    }
}
