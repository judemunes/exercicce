<?php
require_once 'fonction.php';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'ajouter':
            $taches = chargerTaches();
            $taches[] = [
                'id' => uniqid(),
                'titre' => $_POST['titre'],
                'description' => $_POST['description'],
                'priorite' => $_POST['priorite'],
                'statut' => 'à faire',
                'date_creation' => date('Y-m-d H:i:s'),
                'date_limite' => $_POST['date_limite']
            ];
            sauvegarderTaches($taches);
            break;

        case 'supprimer':
            $taches = chargerTaches();
            $taches = array_filter($taches, function ($t) {
                return $t['id'] !== $_POST['id'];
            });
            sauvegarderTaches(array_values($taches));
            break;

        case 'changer_statut':
            $taches = chargerTaches();
            foreach ($taches as &$tache) {
                if ($tache['id'] === $_POST['id']) {
                    $tache['statut'] = getStatutSuivant($tache['statut']);
                    break;
                }
            }
            sauvegarderTaches($taches);
            break;
    }

    // Redirection pour éviter le rechargement POST
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Charger les tâches
$taches = chargerTaches();

// Paramètres de filtrage
$recherche = $_GET['recherche'] ?? '';
$statut = $_GET['statut'] ?? '';
$priorite = $_GET['priorite'] ?? '';

// Filtrer les tâches
$tachesFiltrees = filtrerTaches($taches, $recherche, $statut, $priorite);

// Calculer les statistiques
$total = count($taches);
$terminees = count(array_filter($taches, fn($t) => $t['statut'] === 'terminée'));
$enRetard = count(array_filter($taches, 'estEnRetard'));
$pourcentageTerminees = $total > 0 ? round(($terminees / $total) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Tâches</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial;
            margin: 20px;
            background: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .card {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        .btn-danger {
            background: #dc3545;
        }

        .btn-success {
            background: #28a745;
        }

        .taches-grid {
            display: grid;
            gap: 15px;
        }

        .tache {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            background: #fff;
        }

        .tache.en-retard {
            border-left: 5px solid #dc3545;
            background: #fff5f5;
        }

        .tache-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .tache-titre {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
        }

        .priorite-haute {
            background: #f8d7da;
            color: #721c24;
        }

        .priorite-moyenne {
            background: #fff3cd;
            color: #856404;
        }

        .priorite-basse {
            background: #d4edda;
            color: #155724;
        }

        .statut-a-faire {
            background: #e2e3e5;
            color: #383d41;
        }

        .statut-en-cours {
            background: #cce5ff;
            color: #004085;
        }

        .statut-terminee {
            background: #d4edda;
            color: #155724;
        }

        .tache-actions {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .stat-card {
            text-align: center;
            padding: 15px;
            border-radius: 5px;
            color: white;
        }

        .stat-card.total {
            background: #6c757d;
        }

        .stat-card.done {
            background: #28a745;
        }

        .stat-card.late {
            background: #dc3545;
        }

        .filtres {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
            align-items: end;
        }

        .filtres button,
        .filtres .btn {
            padding: 20px 25px;
            font-size: 15px;
            height: fit-content;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: fit-content;
        }

        .btn {
            background: #6c757d;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn:hover {
            background: #5a6268;
            color: white;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>📋 Gestionnaire de Tâches</h1>

        <!-- Formulaire d'ajout -->
        <div class="card">
            <h2>Ajouter une tâche</h2>
            <form method="POST">
                <input type="hidden" name="action" value="ajouter">

                <div class="form-group">
                    <label>Titre :</label>
                    <input type="text" name="titre" required>
                </div>

                <div class="form-group">
                    <label>Description :</label>
                    <textarea name="description" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Priorité :</label>
                    <select name="priorite" required>
                        <option value="basse">Basse</option>
                        <option value="moyenne" selected>Moyenne</option>
                        <option value="haute">Haute</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Date limite :</label>
                    <input type="date" name="date_limite" required min="<?= date('Y-m-d') ?>">
                </div>

                <button type="submit">➕ Ajouter la tâche</button>
            </form>
        </div>

        <!-- Filtres -->
        <div class="card">
            <h2>Filtres</h2>
            <form method="GET" class="filtres">
                <div class="form-group">
                    <input type="text" name="search" placeholder="Rechercher..." value="<?= htmlspecialchars($recherche) ?>">
                </div>

                <div class="form-group">
                    <select name="status">
                        <option value="">Tous les statuts</option>
                        <option value="à faire" <?= $statut === 'à faire' ? 'selected' : '' ?>>À faire</option>
                        <option value="en cours" <?= $statut === 'en cours' ? 'selected' : '' ?>>En cours</option>
                        <option value="terminée" <?= $statut === 'terminée' ? 'selected' : '' ?>>Terminée</option>
                    </select>
                </div>

                <div class="form-group">
                    <select name="priority">
                        <option value="">Toutes les priorités</option>
                        <option value="basse" <?= $priorite === 'basse' ? 'selected' : '' ?>>Basse</option>
                        <option value="moyenne" <?= $priorite === 'moyenne' ? 'selected' : '' ?>>Moyenne</option>
                        <option value="haute" <?= $priorite === 'haute' ? 'selected' : '' ?>>Haute</option>
                    </select>
                </div>

                <button type="submit">🔍 Filtrer</button>
                <a href="?" class="btn">🔄 Réinitialiser</a>
            </form>
        </div>

        <!-- Statistiques -->
        <div class="stats">
            <div class="stat-card total">
                <h3>Total</h3>
                <p><?= $total ?> tâches</p>
            </div>

            <div class="stat-card done">
                <h3>Terminées</h3>
                <p><?= $terminees ?> (<?= $pourcentageTerminees ?>%)</p>
            </div>

            <div class="stat-card late">
                <h3>En retard</h3>
                <p><?= $enRetard ?></p>
            </div>
        </div>

        <!-- Liste des tâches -->
        <div class="card">
            <h2>Liste des tâches (<?= count($tachesFiltrees) ?>)</h2>

            <?php if (empty($tachesFiltrees)): ?>
                <p>Aucune tâche trouvée.</p>
            <?php else: ?>
                <div class="taches-grid">
                    <?php foreach ($tachesFiltrees as $tache): ?>
                        <div class="tache <?= estEnRetard($tache) ? 'en-retard' : '' ?>">
                            <div class="tache-header">
                                <h3 class="tache-titre"><?= htmlspecialchars($tache['titre']) ?></h3>
                                <div>
                                    <span class="badge priorite-<?= $tache['priorite'] ?>">
                                        <?= ucfirst($tache['priorite']) ?>
                                    </span>
                                    <span class="badge statut-<?= str_replace(' ', '-', $tache['statut']) ?>">
                                        <?= ucfirst($tache['statut']) ?>
                                    </span>
                                    <?php if (estEnRetard($tache)): ?>
                                        <span class="badge" style="background:#dc3545;color:white;">⚠️ Retard</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <p><?= nl2br(htmlspecialchars($tache['description'])) ?></p>

                            <div style="color:#666; font-size:14px; margin-top:10px;">
                                <div>Créée : <?= date('d/m/Y', strtotime($tache['date_creation'])) ?></div>
                                <div>Limite : <?= date('d/m/Y', strtotime($tache['date_limite'])) ?></div>
                            </div>

                            <div class="tache-actions">
                                <?php if ($tache['statut'] !== 'terminée'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="changer_statut">
                                        <input type="hidden" name="id" value="<?= $tache['id'] ?>">
                                        <button type="submit" class="btn-success">
                                            Marquer <?= $tache['statut'] === 'à faire' ? 'En cours' : 'Terminée' ?>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="supprimer">
                                    <input type="hidden" name="id" value="<?= $tache['id'] ?>">
                                    <button type="submit" class="btn-danger"
                                        onclick="return confirm('Supprimer cette tâche ?')">
                                        🗑️ Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>