<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penilaian</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1, h2, h3 { margin: 0 0 6px 0; }
        .meta { margin-bottom: 12px; }
        .meta table { width: 100%; border-collapse: collapse; }
        .meta td { padding: 6px 8px; border: 1px solid #ccc; }

        .section-title { margin: 16px 0 6px; font-weight: bold; font-size: 14px; }
        table.table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.table th, table.table td { border: 1px solid #444; padding: 6px 8px; vertical-align: top; }
        table.table th { background: #f0f0f0; font-weight: bold; }
        .group-row td { background: #fafafa; font-weight: bold; }
        .subtotal-row td { background: #f9f9ff; font-weight: bold; }
        .total-row td { background: #eef7ff; font-weight: bold; }
        .small { font-size: 11px; }
        .bullet { padding-left: 14px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
@php($evalByType = ($schedule->evaluations ?? collect())->keyBy('type'))
@php($getVal = function($type,$sectionKey,$itemKey) use ($evalByType){ $e=$evalByType->get($type); if(!$e) return null; $bd=$e->breakdown ?? []; $k=$sectionKey.'.'.$itemKey; return array_key_exists($k,$bd)?$bd[$k]:null; })

<h1 style="margin-bottom:4px;">Laporan Penilaian Supervisi</h1>
<div class="meta">
    <table>
        <tr><td>Nama Guru</td><td>{{ $schedule->teacher->name ?? '' }}</td></tr>
        <tr><td>NIP</td><td>{{ $schedule->teacher->nip ?? '' }}</td></tr>
        <tr><td>Nama Sekolah</td><td>{{ $schedule->school->name ?? '' }}</td></tr>
        <tr><td>Nama Supervisor</td><td>{{ $schedule->supervisor->name ?? '' }}</td></tr>
        <tr><td>Tanggal Supervisi</td><td>{{ optional($schedule->date)->format('d-m-Y') }}</td></tr>
        <tr><td>Jenis Guru</td><td>{{ $schedule->teacher->teacher_type_label ?? '' }}</td></tr>
        <tr><td>Detail Penugasan</td><td>{{ $schedule->teacher->teacher_detail_label ?? '' }}</td></tr>
        <tr><td>Kelas Supervisi</td><td>{{ $schedule->class_name ?? '' }}</td></tr>
    </table>

<div class="page-break"></div>
</div>

@php([$rppStructure,] = \App\Http\Controllers\EvaluationController::structureFor('rpp'))
<div class="section-title">RPP</div>
<table class="table small">
    <thead>
    <tr>
        <th style="width:60px;">No.</th>
        <th>Aspek yang Dinilai</th>
        <th style="width:70px;">Skor</th>
        <th style="width:160px;">Keterangan</th>
    </tr>
    </thead>
    <tbody>
    @php($no=1)
    @php($current=null)
    @php($groupSum=0)
    @php($groupCount=0)
    @php($grandSum=0)
    @php($grandCount=0)
    @foreach($rppStructure as $section)
        @php($group = substr($section['key'],0,1))
        @if($current && $group!==$current)
            <tr class="subtotal-row"><td colspan="2">Subtotal {{ $current }}</td><td>{{ $groupCount ? $groupSum : '' }}</td><td></td></tr>
            @php($groupSum=0)
            @php($groupCount=0)
        @endif
        @if($group!==$current)
            <tr class="group-row"><td colspan="4">{{ $group }}. KOMPONEN</td></tr>
            @php($current=$group)
        @endif
        <tr><td>{{ $no++ }}</td><td>{{ $section['title'] }}</td><td></td><td></td></tr>
        @foreach($section['items'] as $itemKey=>$label)
            @php($val = $getVal('rpp', $section['key'], $itemKey))
            @if($val!==null && $val!=='')
                @php($groupSum += (int)$val)
                @php($groupCount++)
                @php($grandSum += (int)$val)
                @php($grandCount++)
            @endif
            <tr>
                <td></td>
                <td class="bullet">&#8226; {{ $label }}</td>
                <td>{{ $val }}</td>
                <td></td>
            </tr>
        @endforeach
    @endforeach
    @if($current)
        <tr class="subtotal-row"><td colspan="2">Subtotal {{ $current }}</td><td>{{ $groupCount ? $groupSum : '' }}</td><td></td></tr>
    @endif
    <tr class="total-row"><td colspan="2">TOTAL SKOR RPP</td><td>{{ $grandCount ? $grandSum : '' }}</td><td></td></tr>
    @php($rppPercent = $grandCount ? round(($grandSum/($grandCount*4))*100,2) : null)
    <tr class="total-row"><td colspan="2">PERSENTASE RPP</td><td>{{ $rppPercent !== null ? $rppPercent.'%' : '' }}</td><td></td></tr>
    </tbody>
</table>

<div class="page-break"></div>

@php([$pembStructure,] = \App\Http\Controllers\EvaluationController::structureFor('pembelajaran'))
<div class="section-title">PEMBELAJARAN (DEEP LEARNING)</div>
<table class="table small">
    <thead>
    <tr>
        <th style="width:200px;">Aspek</th>
        <th>Deskripsi</th>
        <th style="width:40px;">Ya</th>
        <th style="width:50px;">Tidak</th>
        <th style="width:160px;">Keterangan</th>
    </tr>
    </thead>
    <tbody>
    @php($yes=0)
    @php($total=0)
    @foreach($pembStructure as $section)
        <tr class="group-row"><td colspan="5">{{ $section['title'] }}</td></tr>
        @foreach($section['items'] as $itemKey=>$label)
            @php($val = $getVal('pembelajaran',$section['key'],$itemKey))
            @if($val===true || $val===false)
                @php($total++)
                @if($val===true) @php($yes++) @endif
            @endif
            <tr>
                <td></td>
                <td class="bullet">{{ $label }}</td>
                <td style="text-align:center;">{!! $val===true ? '✓' : '' !!}</td>
                <td style="text-align:center;">{!! $val===false ? '✓' : '' !!}</td>
                <td></td>
            </tr>
        @endforeach
    @endforeach
    <tr class="total-row"><td colspan="2">TOTAL YA</td><td>{{ $yes }}</td><td></td><td></td></tr>
    @php($pembPercent = $total ? round(($yes/$total)*100,2) : null)
    <tr class="total-row"><td colspan="2">PERSENTASE PEMBELAJARAN</td><td>{{ $pembPercent !== null ? $pembPercent.'%' : '' }}</td><td></td><td></td></tr>
    </tbody>
</table>

<div class="page-break"></div>

@php([$asesStructure,] = \App\Http\Controllers\EvaluationController::structureFor('asesmen'))
<div class="section-title">ASESMEN</div>
<table class="table small">
    <thead>
    <tr>
        <th style="width:60px;">No.</th>
        <th>Aspek yang Dinilai</th>
        <th style="width:70px;">Skor</th>
        <th style="width:160px;">Keterangan</th>
    </tr>
    </thead>
    <tbody>
    @php($no=1)
    @php($current=null)
    @php($groupSum=0)
    @php($groupCount=0)
    @php($grandSum=0)
    @php($grandCount=0)
    @foreach($asesStructure as $section)
        @php($group = substr($section['key'],0,1))
        @if($current && $group!==$current)
            <tr class="subtotal-row"><td colspan="2">Subtotal {{ $current }}</td><td>{{ $groupCount ? $groupSum : '' }}</td><td></td></tr>
            @php($groupSum=0)
            @php($groupCount=0)
        @endif
        @if($group!==$current)
            <tr class="group-row"><td colspan="4">{{ $group }}. KOMPONEN</td></tr>
            @php($current=$group)
        @endif
        <tr><td>{{ $no++ }}</td><td>{{ $section['title'] }}</td><td></td><td></td></tr>
        @foreach($section['items'] as $itemKey=>$label)
            @php($val = $getVal('asesmen', $section['key'], $itemKey))
            @if($val!==null && $val!=='')
                @php($groupSum += (int)$val)
                @php($groupCount++)
                @php($grandSum += (int)$val)
                @php($grandCount++)
            @endif
            <tr>
                <td></td>
                <td class="bullet">&#8226; {{ $label }}</td>
                <td>{{ $val }}</td>
                <td></td>
            </tr>
        @endforeach
    @endforeach
    @if($current)
        <tr class="subtotal-row"><td colspan="2">Subtotal {{ $current }}</td><td>{{ $groupCount ? $groupSum : '' }}</td><td></td></tr>
    @endif
    <tr class="total-row"><td colspan="2">TOTAL SKOR ASESMEN</td><td>{{ $grandCount ? $grandSum : '' }}</td><td></td></tr>
    @php($asesPercent = $grandCount ? round(($grandSum/($grandCount*4))*100,2) : null)
    <tr class="total-row"><td colspan="2">PERSENTASE ASESMEN</td><td>{{ $asesPercent !== null ? $asesPercent.'%' : '' }}</td><td></td></tr>
    </tbody>
</table>

</body>
</html>
