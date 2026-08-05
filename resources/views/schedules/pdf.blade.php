<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <style>
        @page {
            size: A4 landscape;
            margin: 14mm 0 14mm 0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body { width: 100%; height: 100%; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #000000;
            background: #ffffff;
        }

        /* Wrapper enforces left margin and limits width so right margin is respected */
        .wrapper {
            margin-left: 20mm;
            margin-top: 8mm;
            width: 257mm; /* 297mm (A4 landscape) - 20mm left - 20mm right */
        }

        .page-title {
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            color: #000000;
            margin-bottom: 20pt;
            letter-spacing: 1px;
        }

        .week-pair {
            width: 100%;
            margin-bottom: 12pt;
        }

        .week-pair table.outer {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        /* Two content columns with an explicit gap column in the middle */
        .week-pair table.outer > tbody > tr > td.col-left {
            width: 46%;
            vertical-align: top;
        }

        .week-pair table.outer > tbody > tr > td.col-gap {
            width: 8%;
        }

        .week-pair table.outer > tbody > tr > td.col-right {
            width: 46%;
            vertical-align: top;
        }

        /* Inner meeting block */
        table.block {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #b0b0b0;
        }

        table.block .block-header {
            background-color: #0D4C92;
            color: #ffffff;
            text-align: center;
            font-size: 8pt;
            font-weight: bold;
            padding: 3pt 4pt;
            border: 1px solid #0D4C92;
            letter-spacing: 0.5px;
        }

        table.block .role-label {
            width: 42%;
            padding: 2.5pt 5pt;
            border: 1px solid #d0d0d0;
            font-size: 8pt;
            font-weight: bold;
            color: #1a1a1a;
            vertical-align: middle;
            background: #ffffff;
        }

        table.block .role-name {
            width: 58%;
            padding: 2.5pt 5pt;
            border: 1px solid #d0d0d0;
            font-size: 8pt;
            color: #000000;
            text-align: center;
            vertical-align: middle;
            background: #ffffff;
        }

        table.block .role-name.empty {
            color: #cccccc;
            font-style: italic;
        }

        .footer-note {
            margin-top: 6pt;
            text-align: center;
            font-size: 7.5pt;
            color: #888888;
        }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="page-title">PROGRAMA DE ACOMODADORES</div>

    @foreach ($weeks as $week)
    <div class="week-pair">
        <table class="outer">
            <tbody>
                <tr>
                    <td class="col-left">
                        @include('schedules._meeting_block', [
                            'dateLabel' => $week['friday_label'],
                            'meeting'   => $week['meetings']['friday'],
                            'roles'     => $roles,
                        ])
                    </td>
                    <td class="col-gap"></td>
                    <td class="col-right">
                        @include('schedules._meeting_block', [
                            'dateLabel' => $week['saturday_label'],
                            'meeting'   => $week['meetings']['saturday'],
                            'roles'     => $roles,
                        ])
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    @endforeach

    <div class="footer-note">{{ $spanishMonthLabel }} &mdash; Programa de Acomodadores</div>

</div>
</body>
</html>
