<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommercialController extends Controller
{
    public function dashboard()
    {
        $commercial = Auth::user();
        
        if (!$commercial->isCommercial()) {
            abort(403, 'Accès non autorisé.');
        }

        // Récupérer les mobile caissiers assignés à ce commercial
        $mobileCaissiers = User::where('commercial_id', $commercial->id)
                              ->where('fonction', 'mobile_caissier')
                              ->with(['admin'])
                              ->orderBy('prenom')
                              ->get();

        return view('commercial.dashboard', compact('mobileCaissiers'));
    }

    public function searchMobileCaissier(Request $request)
    {
        $commercial = Auth::user();
        
        if (!$commercial->isCommercial()) {
            abort(403, 'Accès non autorisé.');
        }

        $search = $request->get('search');

        $mobileCaissiers = User::where('commercial_id', $commercial->id)
                              ->where('fonction', 'mobile_caissier')
                              ->where(function($query) use ($search) {
                                  $query->where('email', 'like', "%{$search}%")
                                        ->orWhere('prenom', 'like', "%{$search}%")
                                        ->orWhere('name', 'like', "%{$search}%");
                              })
                              ->with(['admin'])
                              ->orderBy('prenom')
                              ->get();

        return view('commercial.dashboard', compact('mobileCaissiers', 'search'));
    }
}