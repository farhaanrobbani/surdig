<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat {{ $letter->nomor }}</title>
    <style>
        @page { margin: 0.8cm 3cm 3cm 3cm; }
        * { font-family: 'Arial', 'DejaVu Sans', sans-serif; }
        body { font-size: 12px; line-height: 1.5; color: #111; }

        /* Kop surat */
        .kop { text-align: center; margin-bottom: 4px; }
        .kop .instansi, .kop-dengan-logo .instansi, .kop .judul, .kop-dengan-logo .judul { font-size: {{ $kopSizes['judul'] }}px; font-weight: bold; letter-spacing: 0.5px; }
        .kop .sub, .kop-dengan-logo .sub { font-size: {{ $kopSizes['sub'] }}px; font-weight: bold; }
        .kop .sub2, .kop-dengan-logo .sub2 { font-size: {{ $kopSizes['sub2'] }}px; font-weight: bold; }
        .kop .alamat, .kop-dengan-logo .alamat, .kop .baris, .kop-dengan-logo .baris { font-size: {{ $kopSizes['baris'] }}px; }
        .kop-dengan-logo { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .kop-dengan-logo td { vertical-align: middle; }
        .kop-dengan-logo .logo { width: 100px; text-align: center; }
        .kop-dengan-logo .logo img { width: 90px; height: auto; }
        .kop-dengan-logo .teks { text-align: center; padding-left: 6px; }
        .garis-tebal { border: none; border-top: 3px solid #111; margin: 4px 0 0 0; }
        .garis-tipis { border: none; border-top: 1.5px solid #111; margin: 1px 0 0 0; }

        .header { margin: 18px 0; line-height: 1; }
        .header p { margin: 0 0 12px 0; }
        .header p:last-child { margin: 0; }
        .isi { text-align: justify; line-height: 1.5; }
        .isi p { margin: 0 0 12px 0; line-height: 1.5; }
        .isi table { line-height: 1.5; }
        .ttd { margin-top: 40px; text-align: right; padding-right: 8px; }
        .ttd .blok { display: inline-block; text-align: left; }
        .ttd .kota { margin-bottom: 0; }
        .ttd .anchor { font-size: 16px; font-weight: bold; margin: 6px 0; }
        .ttd .nama { font-weight: bold; text-decoration: underline; }
        .ttd .nip { font-size: 11px; line-height: 1; margin-top: 14px; }
        .footer-surat { position: fixed; bottom: 1cm; left: 0; right: 0; padding-left: 0.8cm; padding-right: 0.8cm; text-align: left; font-size: 11px; line-height: 1.4; color: #111; }
    </style>
</head>
<body>
    @php($selectedLogo = ($settings['kop_logo'] ?? 'logo1') === 'logo2' && ! empty($settings['logo2_path']) ? $settings['logo2_path'] : $settings['logo_path'])
    @php($hasLogo = ! empty($selectedLogo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($selectedLogo))
    @php($hasKopTeks = ! empty($kopLines))

    @if ($hasLogo || $hasKopTeks)
        <table class="kop-dengan-logo">
            <tr>
                @if ($hasLogo)
                    <td class="logo">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->path($selectedLogo) }}" alt="Logo {{ $settings['instansi'] ?? 'KUA' }}">
                    </td>
                @endif
                <td class="teks">
                    @if ($hasKopTeks)
                        @foreach ($kopLines as $kopLine)
                            <div class="{{ $kopLine['class'] }}">{{ $kopLine['text'] }}</div>
                        @endforeach
                    @else
                        <div class="instansi">{{ $settings['instansi'] }}</div>
                        <div class="sub">KECAMATAN {{ $settings['kecamatan'] }} KABUPATEN {{ $settings['kabupaten'] }}</div>
                        <div class="alamat">
                            {{ $settings['alamat'] }}
                            @if ($settings['telepon']) &bull; Telp. {{ $settings['telepon'] }} @endif
                            @if ($settings['email']) &bull; Email: {{ $settings['email'] }} @endif
                            @if ($settings['kode_pos']) &bull; Kode Pos {{ $settings['kode_pos'] }} @endif
                        </div>
                    @endif
                </td>
            </tr>
        </table>
        <hr class="garis-tebal">
        <hr class="garis-tipis">
    @else
    <div class="kop">
        @if ($hasKopTeks)
            @foreach ($kopLines as $kopLine)
                <div class="{{ $kopLine['class'] }}">{{ $kopLine['text'] }}</div>
            @endforeach
        @else
            <div class="instansi">{{ $settings['instansi'] }}</div>
            <div class="sub">KECAMATAN {{ $settings['kecamatan'] }} KABUPATEN {{ $settings['kabupaten'] }}</div>
            <div class="alamat">
                {{ $settings['alamat'] }}
                @if ($settings['telepon']) &bull; Telp. {{ $settings['telepon'] }} @endif
                @if ($settings['email']) &bull; Email: {{ $settings['email'] }} @endif
                @if ($settings['kode_pos']) &bull; Kode Pos {{ $settings['kode_pos'] }} @endif
            </div>
        @endif
    </div>
    <hr class="garis-tebal">
    <hr class="garis-tipis">
    @endif

    <div class="header">
        {!! $letter->renderHeader() !!}
    </div>

    @if ($letter->tampilkan_tanggal)
        <div style="text-align: right;">{{ $letter->tanggal_surat ? tanggal_indonesia($letter->tanggal_surat, 'd F Y') : '' }}</div>
    @endif

    <div class="isi">
        {!! $body !!}
    </div>

    <div class="ttd">
        <div class="blok">
            <div class="kota">{{ Str::title($settings['kecamatan']) }}, {{ $letter->tanggal_surat ? tanggal_indonesia($letter->tanggal_surat, 'd F Y') : '' }}</div>
            <div>Kepala,</div>
            @if (($settings['kop_anchor'] ?? '1') !== '0')
                <div class="anchor">^</div>
            @else
                <div style="height: 40px;"></div>
            @endif
            <div class="nama">{{ $settings['kepala_nama'] }}</div>
            @if ($settings['kepala_nip'])
                <div class="nip">NIP. {{ $settings['kepala_nip'] }}</div>
            @endif
        </div>
    </div>

    @if ($kopFooterEnabled && ! empty($kopFooter))
        <div class="footer-surat">{{ $kopFooter }}</div>
    @endif
</body>
</html>
