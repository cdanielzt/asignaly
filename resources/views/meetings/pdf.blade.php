<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #1a1a1a;
            padding: 14mm 16mm;
        }

        table.masthead {
            width: 100%;
            margin-bottom: 14pt;
        }

        table.masthead td {
            vertical-align: bottom;
        }

        .masthead-name {
            font-size: 9pt;
            color: #8c8c8c;
        }

        .masthead-title {
            text-align: right;
            font-size: 12pt;
            font-weight: bold;
            border-bottom: 1pt solid #999999;
            padding-bottom: 4pt;
        }

        .week {
            margin-bottom: 4pt;
        }

        .week + .week {
            margin-top: 14pt;
            padding-top: 14pt;
            border-top: 0.75pt solid #cccccc;
        }

        table.week-meta {
            width: 100%;
            margin-bottom: 8pt;
        }

        table.week-meta td {
            vertical-align: top;
        }

        td.week-date-cell {
            width: 56%;
        }

        .week-date {
            font-size: 10.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        td.info-cell {
            width: 44%;
        }

        table.info-box {
            margin-left: auto;
        }

        table.info-box td {
            font-size: 8pt;
            padding: 0 0 1pt 0;
        }

        table.info-box td.info-label {
            text-align: right;
            color: #8c8c8c;
            padding-right: 5pt;
            white-space: nowrap;
        }

        table.info-box td.info-name {
            font-weight: bold;
            white-space: nowrap;
        }

        table.standalone-row {
            width: 100%;
            border-collapse: collapse;
            margin: 3pt 0;
        }

        table.standalone-row td.accent-cell {
            width: 3pt;
            background-color: #3c7f8b;
        }

        table.standalone-row td.standalone-text {
            padding-left: 7pt;
            font-size: 8pt;
            color: #8c8c8c;
        }

        .section-band {
            display: block;
            width: 46%;
            color: #ffffff;
            font-weight: bold;
            font-size: 8.5pt;
            text-transform: uppercase;
            letter-spacing: 0.3pt;
            padding: 3pt 6pt;
            margin: 3pt 0 4pt 0;
        }

        .section-band.tesoros { background-color: #3c7f8b; }
        .section-band.maestros { background-color: #d68f00; }
        .section-band.vida-cristiana { background-color: #bf2f13; }

        table.items {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 2pt;
        }

        table.items col.col-num   { width: 3%; }
        table.items col.col-title { width: 43%; }
        table.items col.col-label { width: 20%; }
        table.items col.col-name1 { width: 17%; }
        table.items col.col-name2 { width: 17%; }

        table.items td {
            padding: 2.5pt 3pt 2.5pt 0;
            font-size: 8pt;
            vertical-align: top;
            line-height: 1.25;
        }

        td.item-num {
            width: 3%;
            padding-right: 0;
            color: #8c8c8c;
            white-space: nowrap;
        }

        td.item-title {
            color: #1a1a1a;
        }

        td.item-label {
            text-align: right;
            color: #8c8c8c;
            padding-right: 5pt;
            white-space: nowrap;
        }

        td.item-name {
            font-weight: bold;
            color: #1a1a1a;
        }

        td.item-name.empty {
            font-weight: normal;
            font-style: italic;
            color: #bbbbbb;
        }

        table.closing-prayer {
            width: 100%;
            margin-top: 6pt;
        }

        table.closing-prayer td.label {
            width: 56%;
        }

        table.closing-prayer td.label-cell {
            width: 27%;
            text-align: right;
            color: #8c8c8c;
            padding-right: 5pt;
        }

        table.closing-prayer td.name-cell {
            width: 17%;
            font-weight: bold;
        }

        table.closing-prayer td.name-cell.empty {
            font-weight: normal;
            font-style: italic;
            color: #bbbbbb;
        }
    </style>
</head>
<body>

    <table class="masthead">
        <tr>
            <td class="masthead-name">CONGREGACIÓN ORIENTE</td>
            <td class="masthead-title">Programa para la reunión de entre semana</td>
        </tr>
    </table>

    @foreach ($weeks as $week)
    <div class="week">
        <table class="week-meta">
            <tr>
                <td class="week-date-cell">
                    <div class="week-date">
                        {{ $week['date_label'] }}
                        @if ($week['bible_reading'])
                            | {{ $week['bible_reading'] }}
                        @endif
                    </div>
                </td>
                <td class="info-cell">
                    @if (!empty($week['info']))
                        <table class="info-box">
                            @foreach ($week['info'] as $row)
                            <tr>
                                <td class="info-label">{{ $row['label'] }}</td>
                                <td class="info-name">{{ $row['name'] }}</td>
                            </tr>
                            @endforeach
                        </table>
                    @endif
                </td>
            </tr>
        </table>

        @if ($week['intro'])
            <table class="standalone-row">
                <tr>
                    <td class="accent-cell">&nbsp;</td>
                    <td class="standalone-text">
                        {{ $week['intro']['title'] }}{{ $week['intro']['duration'] ? ' (' . $week['intro']['duration'] . ')' : '' }}
                    </td>
                </tr>
            </table>
        @endif

        @foreach ($week['body_sections'] as $section)
            @continue(empty($section['items']))
            <span class="section-band {{ $section['band_class'] }}">{{ $section['title'] }}</span>
            <table class="items">
                <colgroup>
                    <col class="col-num"><col class="col-title"><col class="col-label"><col class="col-name1"><col class="col-name2">
                </colgroup>
                @foreach ($section['items'] as $item)
                <tr>
                    <td class="item-num">{{ $item['number'] }}.</td>
                    <td class="item-title">
                        {{ $item['title'] ?: '—' }}{{ $item['duration'] ? ' (' . $item['duration'] . ')' : '' }}
                    </td>
                    <td class="item-label">{{ $item['label'] }}</td>
                    <td class="item-name {{ empty($item['names'][0]) ? 'empty' : '' }}">
                        {{ $item['names'][0] ?? '— sin asignar —' }}
                    </td>
                    <td class="item-name {{ empty($item['names'][1]) ? 'empty' : '' }}">
                        {{ $item['names'][1] ?? '' }}
                    </td>
                </tr>
                @endforeach
            </table>
        @endforeach

        @if ($week['closing_intro'])
            <table class="standalone-row">
                <tr>
                    <td class="accent-cell">&nbsp;</td>
                    <td class="standalone-text">
                        {{ $week['closing_intro']['title'] }}{{ $week['closing_intro']['duration'] ? ' (' . $week['closing_intro']['duration'] . ')' : '' }}
                    </td>
                </tr>
            </table>
        @endif

        <table class="closing-prayer">
            <tr>
                <td class="label"></td>
                <td class="label-cell">Oración:</td>
                <td class="name-cell {{ empty($week['closing_prayer']) ? 'empty' : '' }}">
                    {{ $week['closing_prayer'] ?? '— sin asignar —' }}
                </td>
            </tr>
        </table>
    </div>
    @endforeach

</body>
</html>
