<?php

namespace App\Http\Controllers;

use App\Exports\TableExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class FileSystemController extends Controller
{
    public function index() {
        return view("landing");
    }

    public function export() {
        return Excel::download(new TableExport, "sicredi.xlsx");
    }
}
