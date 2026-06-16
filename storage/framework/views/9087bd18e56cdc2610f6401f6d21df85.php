<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Limite atingido</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9fafb;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        h1 {
            color: #dc2626;
        }

        p {
            margin-top: 10px;
            color: #374151;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 20px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
    </style>
</head>
<body>

<div class="box">
    <h1>Limite do plano atingido</h1>

    <p>
        Você atingiu o limite do seu plano.
    </p>

    <p>
        Faça upgrade para continuar utilizando o sistema sem restrições.
    </p>

    <a href="<?php echo e(route('planos')); ?>">
        Fazer upgrade
    </a>
</div>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\errors\limite.blade.php ENDPATH**/ ?>