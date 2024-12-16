<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Grupo</title>
</head>
<body>
    <h1>Criar um novo grupo</h1>

    <form action="{{ route('group.store') }}" method="POST">
        @csrf

        <label for="group_name">Nome do Grupo:</label>
        <input type="text" id="group_name" name="group_name" required>

        <label for="description">Descrição:</label>
        <textarea id="description" name="description"></textarea>

        <label for="visibility">Visibilidade:</label>
        <select id="visibility" name="visibility" required>
            <option value="1">Visível</option>
            <option value="0">Não Visível</option>
        </select>

        <label for="is_public">Público:</label>
        <select id="is_public" name="is_public" required>
            <option value="1">Sim</option>
            <option value="0">Não</option>
        </select>

        <button type="submit">Criar Grupo</button>
    </form>
</body>
</html>
