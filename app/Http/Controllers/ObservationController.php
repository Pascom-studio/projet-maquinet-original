<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Observation;
use App\Models\Commentaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ObservationController extends Controller
{
    /**
     * Afficher le formulaire de création d'observation
     */
    public function create(User $hotesse)
    {
        // Vérifier que l'utilisateur est bien une hôtesse
        if ($hotesse->fonction !== 'hotesse') {
            abort(404, 'Utilisateur non trouvé ou non hôtesse');
        }

        // Vérifier que l'utilisateur connecté est un Manager
        $user = Auth::user();
        if (!$user->isManager()) {
            abort(403, 'Seuls les Managers peuvent créer des observations');
        }

        // Vérifier que le Manager peut gérer cette hôtesse
        if (!$user->canManageUser($hotesse)) {
            abort(403, 'Vous ne pouvez pas créer d\'observation pour cette hôtesse');
        }

        return view('observations.create', compact('hotesse'));
    }

    /**
     * Enregistrer une nouvelle observation
     */
    public function store(Request $request)
    {
        $request->validate([
            'hotesse_id' => 'required|exists:users,id',
            'titre' => 'required|string|max:255',
            'contenu' => 'required|string',
            'type' => 'required|in:positif,negatif,suggestion',
            'priorite' => 'required|in:faible,moyenne,elevee'
        ]);

        // Vérifier que l'utilisateur cible est bien une hôtesse
        $hotesse = User::findOrFail($request->hotesse_id);
        if ($hotesse->fonction !== 'hotesse') {
            return back()->with('error', 'L\'utilisateur cible n\'est pas une hôtesse');
        }

        // Vérifier que l'utilisateur connecté est un Manager
        $user = Auth::user();
        if (!$user->isManager()) {
            abort(403, 'Seuls les Managers peuvent créer des observations');
        }

        // Vérifier que le Manager peut gérer cette hôtesse
        if (!$user->canManageUser($hotesse)) {
            abort(403, 'Vous ne pouvez pas créer d\'observation pour cette hôtesse');
        }

        // Créer l'observation
        Observation::create([
            'manager_id' => $user->id,
            'hotesse_id' => $request->hotesse_id,
            'titre' => $request->titre,
            'contenu' => $request->contenu,
            'type' => $request->type,
            'priorite' => $request->priorite,
            'date_observation' => now(),
            'est_lu' => false
        ]);

        return redirect()->route('users.show', $hotesse->id)
            ->with('success', 'Observation envoyée avec succès à l\'hôtesse !');
    }

    /**
     * Afficher la liste des observations (pour Manager)
     */
/**
 * Afficher la liste des observations (pour Manager)
 */
public function index()
{
    $user = Auth::user();
    
    if ($user->isManager()) {
        // Manager voit les observations qu'il a créées avec les commentaires
        $observations = Observation::with(['hotesse', 'commentaires.auteur'])
            ->where('manager_id', $user->id);

        // Filtre par type
        if (request('type')) {
            $observations->where('type', request('type'));
        }

        $observations = $observations->latest('date_observation')
            ->paginate(20);
    } else {
        abort(403, 'Accès réservé aux Managers');
    }

    return view('observations.index', compact('observations'));
}
    /**
     * Afficher une observation spécifique
     */
    public function show(Observation $observation)
    {
        $user = Auth::user();
        
        // Seul le Manager auteur ou l'hôtesse concernée peut voir l'observation
        if ($user->id !== $observation->manager_id && $user->id !== $observation->hotesse_id) {
            abort(403, 'Accès non autorisé');
        }

        // Charger les relations avec les commentaires et leurs auteurs
        $observation->load(['manager', 'hotesse', 'commentaires.auteur']);

        // Si c'est l'hôtesse qui consulte, marquer comme lu
        if ($user->id === $observation->hotesse_id && !$observation->est_lu) {
            $observation->update(['est_lu' => true]);
        }

        return view('observations.show', compact('observation'));
    }

    /**
     * Marquer une observation comme lue (pour hôtesse)
     */
    public function marquerCommeLu(Observation $observation)
    {
        $user = Auth::user();
        
        // Seule l'hôtesse concernée peut marquer comme lu
        if ($user->id !== $observation->hotesse_id) {
            abort(403, 'Action non autorisée');
        }

        $observation->update(['est_lu' => true]);

        return back()->with('success', 'Observation marquée comme lue');
    }

    /**
     * Afficher les observations reçues (pour hôtesse) avec filtres
     */
    public function mesObservations()
    {
        $user = Auth::user();
        
        if ($user->fonction !== 'hotesse') {
            abort(403, 'Cette page est réservée aux hôtesses');
        }

        $observations = Observation::with(['manager'])
            ->where('hotesse_id', $user->id);

        // Filtre par type
        if (request('type')) {
            $observations->where('type', request('type'));
        }

        // Filtre par statut
        if (request('statut')) {
            if (request('statut') === 'non_lu') {
                $observations->where('est_lu', false);
            } elseif (request('statut') === 'lu') {
                $observations->where('est_lu', true);
            }
        }

        $observations = $observations->latest('date_observation')
            ->paginate(15);

        return view('observations.mes-observations', compact('observations'));
    }

    /**
     * Ajouter un commentaire à une observation
     */
    public function ajouterCommentaire(Request $request, Observation $observation)
    {
        $user = Auth::user();
        
        // Seule l'hôtesse concernée ou le manager peuvent commenter
        if ($user->id !== $observation->hotesse_id && $user->id !== $observation->manager_id) {
            abort(403, 'Action non autorisée');
        }

        $request->validate([
            'contenu' => 'required|string|max:1000'
        ]);

        DB::beginTransaction();

        try {
            $commentaire = Commentaire::create([
                'observation_id' => $observation->id,
                'user_id' => $user->id,
                'contenu' => $request->contenu
            ]);

            // Marquer comme lu si c'est l'hôtesse qui commente
            if ($user->id === $observation->hotesse_id && !$observation->est_lu) {
                $observation->update(['est_lu' => true]);
            }

            DB::commit();

            return back()->with('success', 'Commentaire ajouté avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'ajout du commentaire: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un commentaire
     */
    public function supprimerCommentaire(Observation $observation, Commentaire $commentaire)
    {
        $user = Auth::user();
        
        // Seul l'auteur du commentaire ou le manager peuvent supprimer
        if ($user->id !== $commentaire->user_id && $user->id !== $observation->manager_id) {
            abort(403, 'Action non autorisée');
        }

        $commentaire->delete();

        return back()->with('success', 'Commentaire supprimé avec succès!');
    }

    /**
     * Supprimer une observation (Manager uniquement)
     */
    public function destroy(Observation $observation)
    {
        $user = Auth::user();
        
        // Seul le Manager auteur peut supprimer
        if ($user->id !== $observation->manager_id) {
            abort(403, 'Action non autorisée');
        }

        $observation->delete();

        return redirect()->route('observations.index')
            ->with('success', 'Observation supprimée avec succès');
    }
}