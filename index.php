<?php
$file = 'taches.json';

// Initialiser le fichier s'il n'existe pas
if (!file_exists($file)) {
    file_put_contents($file, json_encode([]));
}

// Lire les tâches
$tasks = json_decode(file_get_contents($file), true);

// Ajouter une tâche
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_tache'])) {
    $new_tache = [
        'id' => uniqid(),
        'titre' => $_POST['titre'],
        'description' => $_POST['description'],
        'statut' => $_POST['statut']
    ];
    $tasks[] = $new_tache;
    file_put_contents($file, json_encode($tasks));
    header('Location: index.php');
    exit;
}

// Supprimer une tâche
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $tasks = array_filter($tasks, function ($tache) use ($id) {
        return $tache['id'] !== $id;
    });
    file_put_contents($file, json_encode(array_values($tasks)));
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.css">
    <title>Gestion des Tâches</title>
    <style>
        body {
            background-color: #f0f2f5;
            padding-top: 30px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            max-width: 1100px;
        }

        .card-form {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 40px;
        }

        .card-form .card-header {
            background-color: #0047cc;
            /* Bleu plus proche de l'image */
            color: white;
            font-size: 1.1rem;
            padding: 12px 20px;
            border: none;
        }

        .card-form .card-body {
            background-color: #fdfdfd;
        }

        .task-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            background: #f8f9fa;
            padding: 20px;
            height: 100%;
            margin-bottom: 20px;
        }

        .task-card .card-title {
            font-size: 1.2rem;
            font-weight: 500;
        }

        .badge-en-cours {
            background-color: #f1b400;
            /* Jaune/Orange de l'image */
            color: #fff;
            font-weight: normal;
            border-radius: 4px;
        }

        .badge-terminee {
            background-color: #0c5a24;
            /* Vert foncé de l'image */
            color: #fff;
            font-weight: normal;
            border-radius: 4px;
        }

        .badge-non-commencee {
            background-color: #6c757d;
            color: #fff;
            font-weight: normal;
            border-radius: 4px;
        }

        .header-title {
            text-align: center;
            margin-bottom: 40px;
            font-size: 2.5rem;
            font-weight: 500;
        }

        .section-title {
            margin-bottom: 25px;
            font-size: 2rem;
            font-weight: 400;
        }

        .btn-add {
            background-color: #0c5a24;
            color: white;
            padding: 8px 25px;
            border: none;
            border-radius: 5px;
        }

        .btn-add:hover {
            background-color: #094a1d;
            color: white;
        }

        .btn-modifier {
            background-color: #0026e6;
            color: white;
            border-radius: 4px;
        }

        .btn-supprimer {
            background-color: #cc0000;
            color: white;
            border-radius: 4px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1 class="header-title">Gestion des Tâches</h1>

        <!-- Formulaire d'ajout -->
        <div class="card card-form">
            <div class="card-header">
                Ajouter une tâche
            </div>
            <div class="card-body">
                <form action="index.php" method="POST">
                    <div class="mb-3">
                        <label for="titre" class="form-label">Titre</label>
                        <input type="text" class="form-control" id="titre" name="titre" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="statut" class="form-label">Statut</label>
                        <select class="form-select" id="statut" name="statut">
                            <option value="En cours">En cours</option>
                            <option value="Terminée">Terminée</option>
                            <option value="En attente">En attente</option>
                        </select>
                    </div>
                    <button type="submit" name="add_task" class="btn btn-add">Ajouter la tâche</button>
                </form>
            </div>
        </div>

        <h2 class="section-title">Liste des tâches</h2>
        <div class="row">
            <?php if (empty($tasks)): ?>
                <div class="col-12 text-center text-muted">
                    Aucune tâche pour le moment.
                </div>
            <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                    <div class="col-md-4">
                        <div class="card task-card p-3">
                            <h5 class="card-title"><?php echo htmlspecialchars($task['titre']); ?></h5>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($task['description']); ?></p>
                            <div class="mb-3">
                                <?php
                                $badgeClass = 'badge-en-cours';
                                if ($task['statut'] === 'Terminée') $badgeClass = 'badge-terminee';
                                if ($task['statut'] === 'En attente') $badgeClass = 'badge-non-commencee';
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo htmlspecialchars($task['statut']); ?>
                                </span>
                            </div>
                            <hr>
                            <div class="d-flex gap-2">
                                <a href="#" class="btn btn-modifier btn-sm px-3">Modifier</a>
                                <a href="index.php?delete=<?php echo $task['id']; ?>" class="btn btn-supprimer btn-sm px-3" onclick="return confirm('Supprimer cette tâche ?')">Supprimer</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>