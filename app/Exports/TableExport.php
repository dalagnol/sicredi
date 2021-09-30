<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Storage;

class TableExport implements FromArray, ShouldAutoSize, WithHeadings
{
    public function array(): array {
        $contents = (string) Storage::get('FBAA_MASK.PRN');
        $lines = array_filter(explode("\n", $contents), function($line) {
            return $line !== "\r" && !str_contains($line, "Total");
        });
        
        $res = array();

        for ($i = 0; $i < 4; $i++) {
            $lines = array_splice($lines, 7);
            $found = false;
            for ($j = 0; $j < sizeof($lines) && !$found; $j++) {
                if (str_contains($lines[$j], "---")) {
                    array_push($res, ...array_splice($lines, 0, $j));
                    $found = true;
                }
            }
        }

        $finalArr = array();

        for ($k = 0; $k <= count($res) - 1; $k = $k + 2) {
            array_push($finalArr, 
            [
                substr($res[$k], 0, 4), 
                (int) substr($res[$k], 5, 2), 
                (int) substr($res[$k], 8, 7), 
                substr($res[$k], 16, 27), 
                substr($res[$k], 48, 8), 
                substr($res[$k], 58, 3), 
                substr($res[$k], 62, 17),
                floatval(str_replace([".", ","], ["", "."], substr($res[$k], 101, 9))),
                floatval(str_replace([".", ","], ["", "."], substr($res[$k], 120, 9))),
                date('Y-m-d', strtotime(str_replace("/", "-", substr($res[$k + 1], 116, 10))))
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
