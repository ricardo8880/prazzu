<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Limite atingido</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=20260702-css-migration-lote10">
</head>
<body class="limit-page">

<div class="box">
    <h1>Limite do plano atingido</h1>

    <p>
        Você atingiu o limite do seu plano.
    </p>

    <p>
        Faça upgrade para continuar utilizando o sistema sem restrições.
    </p>

    <a href="{{ route('planos') }}">
        Fazer upgrade
    </a>
</div>

</body>
</html>
