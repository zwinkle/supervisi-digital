# Rubrik Supervisi Digital

Dokumen ini menjadi referensi resmi untuk implementasi penilaian dan pembuatan template Excel. Terdiri dari tiga bagian: Penilaian RPP, Penilaian Pembelajaran, dan Penilaian Asesmen.

## Metadata Wajib (per jadwal/pengisian)
- **Nama Guru**
- **NIP**
- **Nama Sekolah**
- **Nama Supervisor**
- **Tanggal Supervisi** (DD-MM-YYYY)
- **Mata Pelajaran**
- **Kelas**

Atribut di atas harus tersedia pada sistem (form kelengkapan profil untuk guru pada login pertama) dan disertakan pada setiap lembar penilaian.

---

## 1) Penilaian RPP (Skor 1–4 per Aspek)
- **Skala skor aspek**: 1 (Kurang), 2 (Cukup), 3 (Baik), 4 (Sangat Baik)
- **Perhitungan per komponen**: jumlah skor aspek dalam komponen.
- **Perhitungan total**:
  - `TotalSkorDiperoleh = SUM(semua skor aspek)`
  - `TotalSkorMaks = (jumlah aspek keseluruhan) * 4`
  - `Persentase = ROUND((TotalSkorDiperoleh / TotalSkorMaks) * 100, 2)`

### Komponen & Aspek

#### A. Komponen Identifikasi Pembelajaran
- A.1 Identifikasi Peserta Didik
  - Karakteristik Peserta Didik Teridentifikasi Dengan Jelas
  - Kebutuhan Belajar Peserta Didik Terakomodasi
  - Gaya Belajar Peserta Didik Dipertimbangkan
- A.2 Analisis Materi Pelajaran
  - Materi Sesuai Dengan Kurikulum Yang Berlaku
  - Materi Relevan Dengan Kehidupan Peserta Didik
  - Kompleksitas Materi Sesuai Dengan Tingkat Kelas
- A.3 Pemilihan Dimensi Profil Lulusan (DPL)
  - Minimal 2 DPL Dipilih Dengan Tepat
  - DPL Relevan Dengan Materi Dan Kegiatan Pembelajaran
  - Integrasi Antar DPL Terlihat Jelas

#### B. Komponen Desain Pembelajaran
- B.1 Capaian Pembelajaran
  - Capaian Pembelajaran Ditulis Dengan Jelas Dan Terukur
  - Sesuai Dengan Standar Kurikulum
  - Mencerminkan Pembelajaran Mendalam
- B.2 Lintas Disiplin Ilmu
  - Integrasi Dengan Disiplin Ilmu Lain Teridentifikasi
  - Koneksi Antar Mata Pelajaran Jelas
  - Pendekatan Holistik Dalam Pembelajaran
- B.3 Tujuan Pembelajaran
  - Tujuan Spesifik, Terukur, Dan Dapat Dicapai
  - Menggunakan Kata Kerja Operasional Yang Tepat
  - Selaras Dengan Capaian Pembelajaran
- B.4 Topik Pembelajaran
  - Topik Relevan Dan Kontekstual
  - Mendukung Pencapaian Tujuan Pembelajaran
  - Menarik Minat Peserta Didik
- B.5 Praktik Pedagogis
  - Strategi Pembelajaran Inovatif Dan Bervariasi
  - Sesuai Dengan Karakteristik Pembelajaran Mendalam
  - Mendorong Berpikir Tingkat Tinggi
- B.6 Kemitraan Pembelajaran
  - Melibatkan Berbagai Pihak (Orang Tua, Masyarakat, dll)
  - Kemitraan Mendukung Tujuan Pembelajaran
  - Peran Masing-Masing Pihak Jelas
- B.7 Lingkungan Pembelajaran
  - Lingkungan Kondusif Untuk Pembelajaran Mendalam
  - Pemanfaatan Ruang Dan Sumber Belajar Optimal
  - Fleksibilitas Dalam Pengaturan Ruang
- B.8 Pemanfaatan Digital
  - Teknologi Terintegrasi Dengan Baik
  - Platform Digital Mendukung Tujuan Pembelajaran
  - Literasi Digital Peserta Didik Dikembangkan

#### C. Komponen Pengalaman Belajar
- C.1 Kegiatan Awal
  - Berkesan (Menarik Perhatian Dan Motivasi)
  - Berkesadaran (Membangun Awareness)
  - Bermakna (Relevan Dengan Pengalaman Peserta Didik)
- C.2 Kegiatan Inti - Memahami
  - Berkesadaran (Proses Berpikir Reflektif)
  - Bermakna (Koneksi Dengan Pengetahuan Sebelumnya)
- C.3 Kegiatan Inti - Mengaplikasi
  - Berkesadaran (Penerapan Yang Disadari)
  - Bermakna (Aplikasi Dalam Konteks Nyata)
  - Menggembirakan (Belajar Tanpa Tekanan)
- C.4 Kegiatan Inti - Merefleksi
  - Berkesadaran (Refleksi Mendalam Tentang Pembelajaran)
  - Menggembirakan (Suasana Positif Dan Menyenangkan)
- C.5 Kegiatan Penutup
  - Berkesadaran (Kesimpulan Dan Penguatan Pemahaman)
  - Bermakna (Koneksi Dengan Pembelajaran Selanjutnya)

#### D. Komponen Asesmen Pembelajaran
- D.1 Asesmen Awal Pembelajaran
  - Mengukur Pengetahuan Prasyarat
  - Sesuai Dengan Tujuan Pembelajaran
  - Metode Asesmen Bervariasi
- D.2 Asesmen Proses Pembelajaran
  - Assessment For Learning Terintegrasi
  - Feedback Formatif Berkelanjutan
  - Monitoring Kemajuan Peserta Didik
- D.3 Asesmen Akhir Pembelajaran
  - Assessment Of Learning Komprehensif
  - Mengukur Pencapaian Tujuan Pembelajaran
  - Metode Asesmen Autentik
- D.4 Kesesuaian Dengan Prinsip Asesmen
  - Assessment As Learning Diterapkan
  - Assessment For Learning Dan Of Learning Seimbang
  - Asesmen Mendukung Pembelajaran Mendalam
- D.5 Rubrik Penilaian
  - Rubrik Jelas Dan Terukur
  - Indikator Sesuai Dengan Tujuan Pembelajaran
  - Tingkatan (Baru Memulai-Mahir) Terdefinisi Jelas

#### E. Kualitas Keseluruhan Perencanaan
- E.1 Koherensi Dan Konsistensi
  - Keterkaitan Antar Komponen Jelas
  - Alur Pembelajaran Logis Dan Sistematis
  - Tidak Ada Kontradiksi Antar Bagian
- E.2 Inovasi Dan Kreativitas
  - Pendekatan Pembelajaran Inovatif
  - Strategi Kreatif Dan Menarik
  - Mencerminkan Pembelajaran Abad 21
- E.3 Kelengkapan Dokumen
  - Semua Komponen Terisi Lengkap
  - Format Sesuai Dengan Template
  - Dokumen Rapi Dan Mudah Dipahami

---

## 2) Penilaian Pembelajaran (Ya/Tidak per Deskripsi)
- **Skala**: Ya/Tidak untuk tiap deskripsi aspek.
- **Perhitungan per komponen**:
  - `SkorKomponen = (Jumlah YA / Jumlah Deskripsi) * 100`
- **Kategori**:
  - < 60: Kurang
  - 60–75: Cukup
  - 76–85: Baik
  - 86–100: Sangat Baik

### Komponen, Aspek, dan Deskripsi

#### A. Penerapan Prinsip Pembelajaran
- A.1 Berkesadaran (mindful)
  - A.1.1 Guru melakukan asesmen awal untuk mengetahui kondisi awal dan kebutuhan belajar peserta didik.
  - A.1.2 Guru mengarahkan dan memotivasi peserta didik untuk belajar secara antusias dan aktif.
  - A.1.3 Guru menggunakan variasi strategi dan metode mengajar agar perserta didik bisa memahami materi dan mencapai tujuan pembelajaran.
- A.2 Bermakna (meaningful)
  - A.2.1 Guru menyampaikan materi disertai dengan contoh kontekstual yang sesuai dengan kehidupan dan lingkungan peserta didik.
  - A.2.2 Guru mengarahkan perserta didik untuk belajar melalui pengalaman nyata.
  - A.2.3 Perserta didik merefleksikan makna dari materi yang dipelajari dalam bentuk lisan, tulisan, gambar, atau simbol.
- A.3 Menggembirakan (joyful)
  - A.3.1 Guru membangun komunikasi pembelajaran yang interaktif dengan peserta didik
  - A.3.2 Guru menggunakan strategi pembelajaran yang membuat peserta didik antusias dan gembira.
  - A.3.3 Sekali waktu guru menggunakan ice breaker, game, atau kuis untuk meningkatkan motivasi, konsentrasi, atau membangun suasana pembelajaran yang menyenangkan.

#### B. Pengalaman Belajar
- B.1 Memahami
  - B.1.1 Guru menyampaikan tujuan pembelajaran dengan jelas dan runtut.
  - B.1.2 Guru menjelaskan materi pelajaran secara sistematis dari mudah ke sulit, sederhana ke kompleks, konkrit ke abstrak.
  - B.1.3 Guru menggunakan beragam sumber belajar dan mendorong perserta didik untuk belajar dari beragam sumber.
  - B.1.4 Guru menggunakan alat peraga/ media pembelajaran yang relevan dengan materi yang diajarkan.
  - B.1.5 Guru menerapkan pendekatan konstruktivisme, menyajikan materi melalui strategi/pendekatan kontekstual/contoh atau pengalaman belajar yang nyata dan bermakna bagi peserta didik.
  - B.1.6 Guru mendorong perserta didik untuk untuk berpikir kritis melalui aktif bertanya, menyampaikan pendapat, diskusi, dan bekerja dalam kelompok dalam menyelesai kan masalah.
  - B.1.7 Guru memberikan kesempatan kepada murid untuk mengungkapkan atau mengekspresikan pemahamannya terkait materi yang dipelajarinya melalui beragam cara (lisan, tulisan, gambar, video, dll.).
  - B.1.8 Guru melakukan asesmen formatif dalam proses pembelajaran.
  - B.1.9 Guru membangun suasana belajar yang menyenangkan dan bermakna bagi murid.
  - B.1.10 Guru memberikan kesempatan kepada perserta didik untuk belajar melalui observasi, eksperimen, atau praktik langsung dalam menyelesaikan masalah.
- B.2 Mengaplikasikan
  - B.2.1 Guru memberikan kesempatan kepada Peserta Didik untuk menerapkan materi secara kontekstual/sesuai dengan kehidupan nyata.
  - B.2.2 Guru memberikan kesempatan kepada Peserta Didik untuk menunjukkan kemampuannya melalui demonstrasi, simulasi, proyek, atau produk.
- B.3 Merefleksikan
  - B.3.1 Guru memberikan kesempatan kepada perserta didik untuk menyampaikan kesan dan perasaan selama mengikuti pembelajaran.
  - B.3.2 Guru memberikan kesempatan kepada perserta didik untuk merefleksikan materi yang telah dipelajarinya, seperti hal yang telah dikuasai, hal yang belum dikuasai, dan hal yang ingin lebih dalam ditingkatkan penguasaannya.

#### C. Kerangka Pembelajaran
- C.1 Praktik Pedagogik
  - C.1.1 Guru menerapkan kegiatan Pembelajaran yang mendorong berpikir kritis, berpikir tingkat tinggi, dan praktik nyata.
  - C.1.2 Guru menerapkan beragam strategi pembelajaran yang mendorong perserta didik menyelesaikan masalah secara kreatif (PBL, PjBL, inquiry, discovery, STEM, dll).
- C.2 Kemitraan Pembelajaran
  - C.2.1 Guru melibatkan perserta didik selain sebagai subjek belajar juga sebagai rekan belajar.
  - C.2.2 Guru melibatkan rekan kerja (team teaching), ahli, atau praktisi dalam menunjang penyampaian materi pelajaran.
- C.3 Lingkungan Belajar
  - C.3.1 Guru membangun budaya belajar yang positif dalam rangka mencapai tujuan pembelajaran dan profil lulusan.
  - C.3.2 Guru mendesain ruang belajar yang aman dan nyaman untuk belajar peserta didik.
  - C.3.3 Guru memanfaatkan lingkungan sekitar sebagai sumber belajar peserta didik.
  - C.3.4 Guru memanfaatkan ruang kelas fisik (luring), ruang kelas digital (LMS, daring), dan memadukan ruang belajar luring dan daring.
- C.4 Pemanfaatan Digital
  - C.4.1 Guru memanfaatkan perangkat digital untuk menunjang pembelajaran agar lebih efektif, interaktif, kolaboratif, dan menarik bagi murid.
  - C.4.2 Guru terampil/cakap dalam menggunakan perangkat digital dalam menunjang pembelajaran.

---

## 3) Penilaian Asesmen (Skor 1–4 per Indikator)
- **Skala skor indikator**: 1 (Kurang), 2 (Cukup), 3 (Baik), 4 (Sangat Baik)
- **Perhitungan**:
  - `TotalSkorDiperoleh = SUM(semua skor indikator)`
  - `TotalSkorMaks = (jumlah indikator) * 4`
  - `Persentase = ROUND((TotalSkorDiperoleh / TotalSkorMaks) * 100, 2)`

### Komponen, Aspek, dan Indikator

#### A. Aspek Penilaian dan Indikator
- A.1 Perencanaan Penilaian Deep Learning
  - A.1.1 Instrumen penilaian terintegrasi dalam RPP atau modul pembelajaran mendalam
  - A.1.2 Tujuan penilaian selaras dengan kompetensi berpikir tingkat tinggi (HOTS), keterampilan abad 21, dan transfer pengetahuan
- A.2 Desain Instrumen Penilaian
  - A.2.1 Instrumen mendorong analisis, evaluasi, sintesis ide
  - A.2.2 Mencakup konteks nyata dan situasi kompleks; mengukur pemahaman konseptual, bukan sekadar hafalan
- A.3 Variasi Teknik Penilaian
  - A.3.1 Guru menggunakan teknik autentik: proyek, portofolio, studi kasus, performa, refleksi diri, dsb
  - A.3.2 Teknik disesuaikan dengan karakteristik materi dan tujuan pembelajaran mendalam
- A.4 Pelaksanaan Penilaian
  - A.4.1 Siswa diberi kebebasan eksplorasi, bertanya, dan berdiskusi
  - A.4.2 Guru memberikan stimulus pemikiran kritis dan reflektif; penilaian dilakukan secara objektif dan transparan
- A.5 Umpan Balik dan Refleksi
  - A.5.1 Guru memberikan umpan balik konstruktif yang mendorong perbaikan
  - A.5.2 Siswa terlibat dalam refleksi terhadap proses dan hasil belajar mereka
- A.6 Tindak Lanjut Penilaian
  - A.6.1 Hasil penilaian digunakan untuk memodifikasi pembelajaran berikutnya
  - A.6.2 Guru mendorong siswa untuk mengaitkan hasil belajar dengan kehidupan nyata atau lintas mata pelajaran

---

## Konvensi Implementasi (DB & Excel)
- **DB Schema (konsep)**:
  - `rubrics` (opsional, definisi indikator/bobot jika perlu disesuaikan per sekolah)
  - `evaluations` menyimpan metadata wajib + `type` in {rpp, pembelajaran, asesmen} + `breakdown` JSON
  - `breakdown` untuk RPP/Asesmen: {"Komponen.Aspek[.Indikator?]": skor}
  - `breakdown` untuk Pembelajaran: {"Komponen.Aspek.DeskripsiID": true/false}
- **Excel**:
  - Sheet Metadata: isi otomatis dari sistem (guru, NIP, sekolah, supervisor, tanggal, mapel, kelas)
  - Sheet Rubrik: daftar komponen/aspek/indikator/deskripsi (read-only)
  - Sheet Penilaian: kolom input (skor 1–4 atau YA/TIDAK), kolom total dan persentase dengan formula
- **Kategori (Pembelajaran)**:
  - < 60 Kurang; 60–75 Cukup; 76–85 Baik; 86–100 Sangat Baik

## Catatan
- Semua perhitungan final dilakukan kembali di server untuk menghindari manipulasi Excel.
- Validasi tipe/ukuran/durasi file dilakukan saat unggah dan/atau setelah upload ke Google Drive via metadata.
