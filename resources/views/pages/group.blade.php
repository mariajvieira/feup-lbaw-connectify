<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Grupo</title>
</head>
<body>
    <h1>{{ $group->group_name }}</h1>
    <p>{{ $group->description }}</p>
    <p><strong>Visibilidade:</strong> {{ $group->visibility ? 'Visível' : 'Não Visível' }}</p>
    <p><strong>Público:</strong> {{ $group->is_public ? 'Sim' : 'Não' }}</p>
</body>
</html>