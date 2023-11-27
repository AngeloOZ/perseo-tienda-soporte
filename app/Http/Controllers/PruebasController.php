<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PruebasController extends Controller
{
    public function word_to_pdf_python()
    {
        // Comando para ejecutar el script de Python
        $python = 'python';
        $BasePath = base_path();

        // Path files
        $pathInput = "public/word/pcControl.docx";
        $pathOutput = "public/word";

        // Comando para ejecutar el script de Python
        $command = escapeshellcmd("$python $BasePath/scripts/convert_to_pdf.py $pathInput $pathOutput");

        // Ejecuta el comando y captura la salida
        $output = shell_exec($command . ' 2>&1');

        if (str_contains($output, 'Error')) {
            echo $output . "<br><br/>";
            echo "Error al convertir el archivo";
        } else {
            echo "Archivo convertido correctamente";
        }
    }
}
