<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Storage;

class TableExport implements FromArray, ShouldAutoSize, WithHeadings
{
    public function array(): array {
        $logFile = fopen("logs.txt", "w");
        $contents = (string) Storage::get('FBAA_MASK.PRN');
        fwrite($logFile, "Extração dos dados do arquivo PRN e conversão para string");

        $lines = array_filter(explode("\n", $contents), function($line) {
            return $line !== "\r" && !str_contains($line, "Total");
        });
        fwrite($logFile, "\nTransformação do conteúdo do arquivo em um arranjo de linhas");
        fwrite($logFile, "\nFiltração das linhas que não incluiam os dados a serem colocados na tabela");
        
        $res = array();

        fwrite($logFile, "\nProcessamento das páginas:");
        for ($i = 0; $i < 300; $i++) {
            fwrite($logFile, "\nEliminação do cabeçalho da página {$i}");

            $lines = array_splice($lines, 7);
            $found = false;
            fwrite($logFile, "\nEncontrar o index da linha de término da página");
            for ($j = 0; $j < sizeof($lines) && !$found; $j++) {
                if (str_contains($lines[$j], "---")) {
                    fwrite($logFile, "\nRemoção dos dados da página de acordo com o index encontrado anteriormente");
                    array_push($res, ...array_splice($lines, 0, $j));
                    $found = true;
                    fwrite($logFile, "\nAnálise da próxima página\n");
                }
            }
        }


        $finalArr = array();

        fwrite($logFile, "\nAnálise dos dados de cada página, tipagem e inserção no arranjo para geração da tabela");
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
