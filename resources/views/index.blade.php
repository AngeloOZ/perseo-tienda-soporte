<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    <h1>Subir archivos CSV</h1>
    @if (!isset($pagos))
        <form action="{{ route('csv_post') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="csv" id="" accept=".csv">
            <br><br>
            <input type="submit" value="submit">
        </form>
    @endif

    @if (isset($pagos))
        <table border="1">
            <thead>
                <tr>
                    @foreach ($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($pagos as $pago)
                    <tr>
                        @foreach ($pago as $item)
                            <td>{{ $item }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>

</html>
