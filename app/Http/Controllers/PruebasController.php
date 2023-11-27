<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PruebasController extends Controller
{
    public function word_to_pdf_python()
    {
        // Comando para ejecutar el script de Python
        $python = 'python3';
        $BasePath = base_path();

        // Path files
        $pathInput = "$BasePath/public/word/pcContable.docx";
        $pathOutput = "$BasePath/public/word";

        // Comando para ejecutar el script de Python
        $command = escapeshellcmd("$python $BasePath/scripts/convert_to_pdf.py $pathInput $pathOutput");

        // dd($command);

        // Ejecuta el comando y captura la salida
        $output = shell_exec($command);

        echo $output . "<br><br/>";

        if (str_contains($output, 'Error')) {
            echo "Error al convertir el archivo";
        } else {
            echo "Archivo convertido correctamente";
        }
    }
}
