<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1   { font-size: 18px; border-bottom: 1px solid #ccc; padding-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ccc; padding: 6px 10px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h1>Relatório de Análise</h1>

    @if (!empty($data))
        <table>
            <thead>
                <tr>
                    <th>Campo</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $key => $value)
                    <tr>
                        <td>{{ $key }}</td>
                        <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Nenhum dado disponível.</p>
    @endif
</body>
</html>
