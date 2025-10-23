<!DOCTYPE html>
<html lang="id">
  <body style="font-family:Arial,Helvetica,sans-serif;line-height:1.5;color:#111">
    <h2>Undangan Bergabung ke Supervisi Digital</h2>
    <p>Halo,</p>
    <p>Anda diundang untuk bergabung sebagai <strong>{{ $role === 'teacher' ? 'Guru' : ucfirst($role) }}</strong> pada aplikasi Supervisi Digital.</p>
    @if (!empty($schoolIds))
      <p>Sekolah terkait:</p>
      <ul>
        @php($schools = \App\Models\School::whereIn('id', $schoolIds)->pluck('name'))
        @foreach($schools as $name)
          <li>{{ $name }}</li>
        @endforeach
      </ul>
    @endif
    <p>Silakan klik tautan berikut untuk menerima undangan dan membuat kata sandi Anda:</p>
    <p><a href="{{ $signedUrl }}">Terima Undangan</a></p>
    <p>Tautan ini berlaku sampai: <strong>{{ $expiresAt->timezone(config('app.timezone'))->format('d M Y H:i') }}</strong></p>
    <p>Jika Anda tidak merasa menerima undangan ini, abaikan email ini.</p>
    <hr>
    <p>Terima kasih,<br>Admin Supervisi Digital</p>
  </body>
</html>
