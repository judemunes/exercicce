<?php
// Fonctions utilitaires

function chargerTaches() {
    if (file_exists('taches.json')) {
        $data = file_get_contents('taches.json');
        return json_decode($data, true) ?: [];
    }
    return [];
}

function sauvegarderTaches($taches) {
    file_put_contents('taches.json', json_encode($taches, JSON_PRETTY_PRINT));
}

function estEnRetard($tache) {
    if ($tache['statut'] === 'terminée') return false;
    return strtotime($tache['date_limite']) < time();
}

function getStatutSuivant($statutActuel) {
    switch ($statutActuel) {
        case 'à faire': return 'en cours';
        case 'en cours': return 'terminée';
        default: return $statutActuel;
    }
}

function filtrerTaches($taches, $recherche = '', $statut = '', $priorite = '') {
    $resultats = $taches;
    
    if (!empty($recherche)) {
        $resultats = array_filter($resultats, function($t) use ($recherche) {
            return stripos($t['titre'], $recherche) !== false || 
                   stripos($t['description'], $recherche) !== false;
        });
    }
    
    if (!empty($statut) && $statut !== 'all') {
        $resultats = array_filter($resultats, function($t) use ($statut) {
            return $t['statut'] === $statut;
        });
    }
    
    if (!empty($priorite) && $priorite !== 'all') {
        $resultats = array_filter($resultats, function($t) use ($priorite) {
            return $t['priorite'] === $priorite;
        });
    }
    
    return $resultats;
}
?>