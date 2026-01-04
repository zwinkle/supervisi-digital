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
            // RPP: Komponen A-E
            $structure = [
                [ 'key' => 'A1', 'title' => 'Identifikasi Peserta Didik', 'items' => [
                    'a1_1' => 'Karakteristik Peserta Didik Teridentifikasi Dengan Jelas',
                    'a1_2' => 'Kebutuhan Belajar Peserta Didik Terakomodasi',
                    'a1_3' => 'Gaya Belajar Peserta Didik Dipertimbangkan',
                ]],
                [ 'key' => 'A2', 'title' => 'Analisis Materi Pelajaran', 'items' => [
                    'a2_1' => 'Materi Sesuai Dengan Kurikulum Yang Berlaku',
                    'a2_2' => 'Materi Relevan Dengan Kehidupan Peserta Didik',
                    'a2_3' => 'Kompleksitas Materi Sesuai Dengan Tingkat Kelas',
                ]],
                [ 'key' => 'A3', 'title' => 'Pemilihan Dimensi Profil Lulusan (DPL)', 'items' => [
                    'a3_1' => 'Minimal 2 DPL Dipilih Dengan Tepat',
                    'a3_2' => 'DPL Relevan Dengan Materi Dan Kegiatan Pembelajaran',
                    'a3_3' => 'Integrasi Antar DPL Terlihat Jelas',
                ]],

                [ 'key' => 'B1', 'title' => 'Capaian Pembelajaran', 'items' => [
                    'b1_1' => 'Capaian Pembelajaran Ditulis Dengan Jelas Dan Terukur',
                    'b1_2' => 'Sesuai Dengan Standar Kurikulum',
                    'b1_3' => 'Mencerminkan Pembelajaran Mendalam',
                ]],
                [ 'key' => 'B2', 'title' => 'Lintas Disiplin Ilmu', 'items' => [
                    'b2_1' => 'Integrasi Dengan Disiplin Ilmu Lain Teridentifikasi',
                    'b2_2' => 'Koneksi Antar Mata Pelajaran Jelas',
                    'b2_3' => 'Pendekatan Holistik Dalam Pembelajaran',
                ]],
                [ 'key' => 'B3', 'title' => 'Tujuan Pembelajaran', 'items' => [
                    'b3_1' => 'Tujuan Spesifik, Terukur, Dan Dapat Dicapai',
                    'b3_2' => 'Menggunakan Kata Kerja Operasional Yang Tepat',
                    'b3_3' => 'Selaras Dengan Capaian Pembelajaran',
                ]],
                [ 'key' => 'B4', 'title' => 'Topik Pembelajaran', 'items' => [
                    'b4_1' => 'Topik Relevan Dan Kontekstual',
                    'b4_2' => 'Mendukung Pencapaian Tujuan Pembelajaran',
                    'b4_3' => 'Menarik Minat Peserta Didik',
                ]],
                [ 'key' => 'B5', 'title' => 'Praktik Pedagogis', 'items' => [
                    'b5_1' => 'Strategi Pembelajaran Inovatif Dan Bervariasi',
                    'b5_2' => 'Sesuai Dengan Karakteristik Pembelajaran Mendalam',
                    'b5_3' => 'Mendorong Berpikir Tingkat Tinggi',
                ]],
                [ 'key' => 'B6', 'title' => 'Kemitraan Pembelajaran', 'items' => [
                    'b6_1' => 'Melibatkan Berbagai Pihak (Orang Tua, Masyarakat, dll)',
                    'b6_2' => 'Kemitraan Mendukung Tujuan Pembelajaran',
                    'b6_3' => 'Peran Masing-Masing Pihak Jelas',
                ]],
                [ 'key' => 'B7', 'title' => 'Lingkungan Pembelajaran', 'items' => [
                    'b7_1' => 'Lingkungan Kondusif Untuk Pembelajaran Mendalam',
                    'b7_2' => 'Pemanfaatan Ruang Dan Sumber Belajar Optimal',
                    'b7_3' => 'Fleksibilitas Dalam Pengaturan Ruang',
                ]],
                [ 'key' => 'B8', 'title' => 'Pemanfaatan Digital', 'items' => [
                    'b8_1' => 'Teknologi Terintegrasi Dengan Baik',
                    'b8_2' => 'Platform Digital Mendukung Tujuan Pembelajaran',
                    'b8_3' => 'Literasi Digital Peserta Didik Dikembangkan',
                ]],

                [ 'key' => 'C1', 'title' => 'Kegiatan Awal', 'items' => [
                    'c1_1' => 'Berkesan (Menarik Perhatian Dan Motivasi)',
                    'c1_2' => 'Berkesadaran (Membangun Awareness)',
                    'c1_3' => 'Bermakna (Relevan Dengan Pengalaman Peserta Didik)',
                ]],
                [ 'key' => 'C2', 'title' => 'Kegiatan Inti - Memahami', 'items' => [
                    'c2_1' => 'Berkesadaran (Proses Berpikir Reflektif)',
                    'c2_2' => 'Bermakna (Koneksi Dengan Pengetahuan Sebelumnya)',
                ]],
                [ 'key' => 'C3', 'title' => 'Kegiatan Inti - Mengaplikasi', 'items' => [
                    'c3_1' => 'Berkesadaran (Penerapan Yang Disadari)',
                    'c3_2' => 'Bermakna (Aplikasi Dalam Konteks Nyata)',
                    'c3_3' => 'Menggembirakan (Belajar Tanpa Tekanan)',
                ]],
                [ 'key' => 'C4', 'title' => 'Kegiatan Inti - Merefleksi', 'items' => [
                    'c4_1' => 'Berkesadaran (Refleksi Mendalam Tentang Pembelajaran)',
                    'c4_2' => 'Menggembirakan (Suasana Positif Dan Menyenangkan)',
                ]],
                [ 'key' => 'C5', 'title' => 'Kegiatan Penutup', 'items' => [
                    'c5_1' => 'Berkesadaran (Kesimpulan Dan Penguatan Pemahaman)',
                    'c5_2' => 'Bermakna (Koneksi Dengan Pembelajaran Selanjutnya)',
                ]],

                [ 'key' => 'D1', 'title' => 'Asesmen Awal Pembelajaran', 'items' => [
                    'd1_1' => 'Mengukur Pengetahuan Prasyarat',
                    'd1_2' => 'Sesuai Dengan Tujuan Pembelajaran',
                    'd1_3' => 'Metode Asesmen Bervariasi',
                ]],
                [ 'key' => 'D2', 'title' => 'Asesmen Proses Pembelajaran', 'items' => [
                    'd2_1' => 'Assessment For Learning Terintegrasi',
                    'd2_2' => 'Feedback Formatif Berkelanjutan',
                    'd2_3' => 'Monitoring Kemajuan Peserta Didik',
                ]],
                [ 'key' => 'D3', 'title' => 'Asesmen Akhir Pembelajaran', 'items' => [
                    'd3_1' => 'Assessment Of Learning Komprehensif',
                    'd3_2' => 'Mengukur Pencapaian Tujuan Pembelajaran',
                    'd3_3' => 'Metode Asesmen Autentik',
                ]],
                [ 'key' => 'D4', 'title' => 'Kesesuaian Dengan Prinsip Asesmen', 'items' => [
                    'd4_1' => 'Assessment As Learning Diterapkan',
                    'd4_2' => 'Assessment For Learning Dan Of Learning Seimbang',
                    'd4_3' => 'Asesmen Mendukung Pembelajaran Mendalam',
                ]],
                [ 'key' => 'D5', 'title' => 'Rubrik Penilaian', 'items' => [
                    'd5_1' => 'Rubrik Jelas Dan Terukur',
                    'd5_2' => 'Indikator Sesuai Dengan Tujuan Pembelajaran',
                    'd5_3' => 'Tingkatan (Baru Memulai-Mahir) Terdefinisi Jelas',
                ]],

                [ 'key' => 'E1', 'title' => 'Koherensi Dan Konsistensi', 'items' => [
                    'e1_1' => 'Keterkaitan Antar Komponen Jelas',
                    'e1_2' => 'Alur Pembelajaran Logis Dan Sistematis',
                    'e1_3' => 'Tidak Ada Kontradiksi Antar Bagian',
                ]],
                [ 'key' => 'E2', 'title' => 'Inovasi Dan Kreativitas', 'items' => [
                    'e2_1' => 'Pendekatan Pembelajaran Inovatif',
                    'e2_2' => 'Strategi Kreatif Dan Menarik',
                    'e2_3' => 'Mencerminkan Pembelajaran Abad 21',
                ]],
                [ 'key' => 'E3', 'title' => 'Kelengkapan Dokumen', 'items' => [
                    'e3_1' => 'Semua Komponen Terisi Lengkap',
                    'e3_2' => 'Format Sesuai Dengan Template',
                    'e3_3' => 'Dokumen Rapi Dan Mudah Dipahami',
                ]],
            ];
            return [$structure, 'skor'];
        }
        if ($type === 'asesmen') {
            // Asesmen: indikator 1-4 per aspek
            $structure = [
                [ 'key' => 'A1', 'title' => 'Perencanaan Penilaian Deep Learning', 'items' => [
                    'a1_1' => 'Instrumen penilaian terintegrasi dalam RPP atau modul pembelajaran mendalam',
                    'a1_2' => 'Tujuan penilaian selaras dengan kompetensi berpikir tingkat tinggi (HOTS), keterampilan abad 21, dan transfer pengetahuan',
                ]],
                [ 'key' => 'A2', 'title' => 'Desain Instrumen Penilaian', 'items' => [
                    'a2_1' => 'Instrumen mendorong analisis, evaluasi, sintesis ide',
                    'a2_2' => 'Mencakup konteks nyata dan situasi kompleks; mengukur pemahaman konseptual, bukan sekadar hafalan',
                ]],
                [ 'key' => 'A3', 'title' => 'Variasi Teknik Penilaian', 'items' => [
                    'a3_1' => 'Guru menggunakan teknik autentik: proyek, portofolio, studi kasus, performa, refleksi diri, dsb',
                    'a3_2' => 'Teknik disesuaikan dengan karakteristik materi dan tujuan pembelajaran mendalam',
                ]],
                [ 'key' => 'A4', 'title' => 'Pelaksanaan Penilaian', 'items' => [
                    'a4_1' => 'Siswa diberi kebebasan eksplorasi, bertanya, dan berdiskusi',
                    'a4_2' => 'Guru memberikan stimulus pemikiran kritis dan reflektif; penilaian dilakukan secara objektif dan transparan',
                ]],
                [ 'key' => 'A5', 'title' => 'Umpan Balik dan Refleksi', 'items' => [
                    'a5_1' => 'Guru memberikan umpan balik konstruktif yang mendorong perbaikan',
                    'a5_2' => 'Siswa terlibat dalam refleksi terhadap proses dan hasil belajar mereka',
                ]],
                [ 'key' => 'A6', 'title' => 'Tindak Lanjut Penilaian', 'items' => [
                    'a6_1' => 'Hasil penilaian digunakan untuk memodifikasi pembelajaran berikutnya',
                    'a6_2' => 'Guru mendorong siswa untuk mengaitkan hasil belajar dengan kehidupan nyata atau lintas mata pelajaran',
                ]],
            ];
            return [$structure, 'skor'];
        }
        // Pembelajaran (Ya/Tidak per deskripsi)
        $structure = [
            [ 'key' => 'A1', 'title' => 'Berkesadaran (mindful)', 'items' => [
                'a11' => 'Guru melakukan asesmen awal untuk mengetahui kondisi awal dan kebutuhan belajar peserta didik.',
                'a12' => 'Guru mengarahkan dan memotivasi peserta didik untuk belajar secara antusias dan aktif.',
                'a13' => 'Guru menggunakan variasi strategi dan metode mengajar agar perserta didik bisa memahami materi dan mencapai tujuan pembelajaran.',
            ]],
            [ 'key' => 'A2', 'title' => 'Bermakna (meaningful)', 'items' => [
                'a21' => 'Guru menyampaikan materi disertai dengan contoh kontekstual yang sesuai dengan kehidupan dan lingkungan peserta didik.',
                'a22' => 'Guru mengarahkan perserta didik untuk belajar melalui pengalaman nyata.',
                'a23' => 'Perserta didik merefleksikan makna dari materi yang dipelajari dalam bentuk lisan, tulisan, gambar, atau simbol.',
            ]],
            [ 'key' => 'A3', 'title' => 'Menggembirakan (joyful)', 'items' => [
                'a31' => 'Guru membangun komunikasi pembelajaran yang interaktif dengan peserta didik',
                'a32' => 'Guru menggunakan strategi pembelajaran yang membuat peserta didik antusias dan gembira.',
                'a33' => 'Sekali waktu guru menggunakan ice breaker, game, atau kuis untuk meningkatkan motivasi, konsentrasi, atau membangun suasana pembelajaran yang menyenangkan.',
            ]],

            [ 'key' => 'B1', 'title' => 'Memahami', 'items' => [
                'b11' => 'Guru menyampaikan tujuan pembelajaran dengan jelas dan runtut.',
                'b12' => 'Guru menjelaskan materi pelajaran secara sistematis dari mudah ke sulit, sederhana ke kompleks, konkrit ke abstrak.',
                'b13' => 'Guru menggunakan beragam sumber belajar dan mendorong perserta didik untuk belajar dari beragam sumber.',
                'b14' => 'Guru menggunakan alat peraga/ media pembelajaran yang relevan dengan materi yang diajarkan.',
                'b15' => 'Guru menerapkan pendekatan konstruktivisme, menyajikan materi melalui strategi/pendekatan kontekstual/contoh atau pengalaman belajar yang nyata dan bermakna bagi peserta didik.',
                'b16' => 'Guru mendorong perserta didik untuk untuk berpikir kritis melalui aktif bertanya, menyampaikan pendapat, diskusi, dan bekerja dalam kelompok dalam menyelesai kan masalah.',
                'b17' => 'Guru memberikan kesempatan kepada murid untuk mengungkapkan atau mengekspresikan pemahamannya terkait materi yang dipelajarinya melalui beragam cara (lisan, tulisan, gambar, video, dll.).',
                'b18' => 'Guru melakukan asesmen formatif dalam proses pembelajaran.',
                'b19' => 'Guru membangun suasana belajar yang menyenangkan dan bermakna bagi murid.',
                'b1_10' => 'Guru memberikan kesempatan kepada perserta didik untuk belajar melalui observasi, eksperimen, atau praktik langsung dalam menyelesaikan masalah.',
            ]],
            [ 'key' => 'B2', 'title' => 'Mengaplikasikan', 'items' => [
                'b21' => 'Guru memberikan kesempatan kepada Peserta Didik untuk menerapkan materi secara kontekstual/sesuai dengan kehidupan nyata.',
                'b22' => 'Guru memberikan kesempatan kepada Peserta Didik untuk menunjukkan kemampuannya melalui demonstrasi, simulasi, proyek, atau produk.',
            ]],
            [ 'key' => 'B3', 'title' => 'Merefleksikan', 'items' => [
                'b31' => 'Guru memberikan kesempatan kepada perserta didik untuk menyampaikan kesan dan perasaan selama mengikuti pembelajaran.',
                'b32' => 'Guru memberikan kesempatan kepada perserta didik untuk merefleksikan materi yang telah dipelajarinya, seperti hal yang telah dikuasai, hal yang belum dikuasai, dan hal yang ingin lebih dalam ditingkatkan penguasaannya.',
            ]],

            [ 'key' => 'C1', 'title' => 'Praktik Pedagogik', 'items' => [
                'c11' => 'Guru menerapkan kegiatan Pembelajaran yang mendorong berpikir kritis, berpikir tingkat tinggi, dan praktik nyata.',
                'c12' => 'Guru menerapkan beragam strategi pembelajaran yang mendorong perserta didik menyelesaikan masalah secara kreatif (PBL, PjBL, inquiry, discovery, STEM, dll).',
            ]],
            [ 'key' => 'C2', 'title' => 'Kemitraan Pembelajaran', 'items' => [
                'c21' => 'Guru melibatkan perserta didik selain sebagai subjek belajar juga sebagai rekan belajar.',
                'c22' => 'Guru melibatkan rekan kerja (team teaching), ahli, atau praktisi dalam menunjang penyampaian materi pelajaran.',
            ]],
            [ 'key' => 'C3', 'title' => 'Lingkungan Belajar', 'items' => [
                'c31' => 'Guru membangun budaya belajar yang positif dalam rangka mencapai tujuan pembelajaran dan profil lulusan.',
                'c32' => 'Guru mendesain ruang belajar yang aman dan nyaman untuk belajar peserta didik.',
                'c33' => 'Guru memanfaatkan lingkungan sekitar sebagai sumber belajar peserta didik.',
                'c34' => 'Guru memanfaatkan ruang kelas fisik (luring), ruang kelas digital (LMS, daring), dan memadukan ruang belajar luring dan daring.',
            ]],
            [ 'key' => 'C4', 'title' => 'Pemanfaatan Digital', 'items' => [
                'c41' => 'Guru memanfaatkan perangkat digital untuk menunjang pembelajaran agar lebih efektif, interaktif, kolaboratif, dan menarik bagi murid.',
                'c42' => 'Guru terampil/cakap dalam menggunakan perangkat digital dalam menunjang pembelajaran.',
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
