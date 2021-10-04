<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Storage;


function any(array $array, callable $fn) {
    foreach ($array as $value) {
        if($fn($value)) {
            return true;
        }
    }
    return false;
}

class TableExport implements FromArray, ShouldAutoSize, WithHeadings
{
    public static function formatNumber($number) {
        return floatval(str_replace([".", ","], ["", "."], $number));
    }

    public static function formatDateTime($dateTime) {
        return date('Y-m-d h:i', strtotime(str_replace("/", "-", $dateTime)));
    }

    public function array(): array {
        $logFile = fopen("logs.txt", "w");

        $contents = (string) Storage::get('FBAA_MASK.PRN');
        $lines = explode("\n", $contents);

        $lines = array_values(
            array_filter(
                $lines, 
                fn($line) => !any(
                    [
                        fn($line) => str_contains($line, " ") && !str_contains($line, "-") && !str_contains($line, "/"),
                        fn($line) => str_contains($line, "COOP CRED POUP E INV SICREDI PIONEIRA RS"),
                        fn($line) => str_contains($line, "SISTEMA SICREDI - SISTEMA DE ATENDIMENTO - A.15"),
                        fn($line) => str_contains($line, "MOVIMENTO DIARIO DO CONTA CORRENTE"),
                        fn($line) => str_contains($line, "======"),
                        fn($line) => str_contains($line, "------"),
                        fn($line) => str_contains($line, "Origem  Conta   Nome Correntista"),
                        fn($line) => str_contains($line, "Data/Hora"),
                        fn($line) => str_contains($line, "Total"),
                        fn($line) => str_contains($line, "TOTAIS DO DIA"),
                        fn($line) => strlen($line) < 4
                    ], 
                    fn ($filter) => $filter($line)
                )
            )   
        );

        $finalArr = array();

        for ($k = 0; $k <= count($lines) - 2; $k = $k + 2) {
            array_push($finalArr, 
                [
                    (int) substr($lines[$k], 0, 4), 
                    (int) substr($lines[$k], 5, 2), 
                    (int) substr($lines[$k], 8, 7), 
                    substr($lines[$k], 16, 27), 
                    substr($lines[$k], 48, 8), 
                    substr($lines[$k], 58, 3), 
                    substr($lines[$k], 62, 17),
                    TableExport::formatNumber(substr($lines[$k], 101, 9)),
                    TableExport::formatNumber(substr($lines[$k], 120, 9)),
                    TableExport::formatDateTime($lines[$k + 1], 116, 16)
                ]
            );
        }

        return [...$finalArr];
    }

    public function headings(): array {
        return [
            "Origem Coop",
            "Origem Agência",
            "Conta",
            "Nome Correntista",
            "Docto",
            "Cod",
            "Descrição",
            "Débito",
            "Crédito",
            "Data/Hora"
        ];
    }
}
