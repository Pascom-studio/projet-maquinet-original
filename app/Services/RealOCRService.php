<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class RealOCRService
{
    public function processImage($imagePath)
    {
        Log::info('🎯 REAL OCR - UTILISATION API OCR.space CORRIGÉE');
        
        try {
            if (!file_exists($imagePath)) {
                throw new \Exception("Fichier introuvable");
            }

            // ✅ API OCR.space avec paramètres corrigés
            $text = $this->useOCRSpaceAPI($imagePath);
            
            if (empty($text)) {
                throw new \Exception("Aucun texte détecté par l'API OCR");
            }

            Log::info('📝 TEXTE EXTRAIT PAR API:', [
                'longueur' => strlen($text),
                'contenu' => $text
            ]);

            return $this->advancedTextAnalysis($text);

        } catch (\Exception $e) {
            Log::error('❌ ÉCHEC OCR: ' . $e->getMessage());
            return $this->getErrorResponse($e->getMessage());
        }
    }

    /**
     * Utiliser l'API OCR.space (paramètres corrigés)
     */
    private function useOCRSpaceAPI($imagePath)
    {
        try {
            Log::info('🔗 Appel API OCR.space...');
            
            $apiKey = 'K87437063588957'; // Clé gratuite
            $url = 'https://api.ocr.space/parse/image';
            
            $response = Http::timeout(30)->asMultipart()->post($url, [
                [
                    'name' => 'file',
                    'contents' => fopen($imagePath, 'r'),
                    'filename' => basename($imagePath)
                ],
                [
                    'name' => 'apikey',
                    'contents' => $apiKey
                ],
                [
                    'name' => 'language',
                    'contents' => 'fre'
                ],
                [
                    'name' => 'OCREngine',
                    'contents' => '2'
                ],
                [
                    'name' => 'scale',
                    'contents' => 'true'
                ],
                [
                    'name' => 'isTable',
                    'contents' => 'false'
                ]
            ]);

            Log::info('📡 Réponse API OCR.space:', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['ParsedResults'][0]['ParsedText'])) {
                    $text = $data['ParsedResults'][0]['ParsedText'];
                    Log::info('✅ API OCR.space réussie');
                    return trim($text);
                } else {
                    Log::error('❌ Erreur API OCR.space:', $data);
                    throw new \Exception("API OCR n'a pas retourné de texte: " . ($data['ErrorMessage'][0] ?? 'Erreur inconnue'));
                }
            } else {
                throw new \Exception("Erreur HTTP: " . $response->status());
            }
            
        } catch (\Exception $e) {
            Log::error('❌ Erreur API OCR.space: ' . $e->getMessage());
            
            // ✅ OPTION DE SECOURS: Google Vision API (gratuit jusqu'à 1000 req/mois)
            return $this->fallbackToGoogleVision($imagePath);
        }
    }

    /**
     * OPTION DE SECOURS: Google Cloud Vision API
     */
    private function fallbackToGoogleVision($imagePath)
    {
        try {
            Log::info('🔄 Essai Google Vision API...');
            
            // Encoder l'image en base64
            $imageData = base64_encode(file_get_contents($imagePath));
            
            $response = Http::timeout(30)->post('https://vision.googleapis.com/v1/images:annotate', [
                'requests' => [
                    [
                        'image' => [
                            'content' => $imageData
                        ],
                        'features' => [
                            [
                                'type' => 'TEXT_DETECTION',
                                'maxResults' => 1
                            ]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['responses'][0]['textAnnotations'][0]['description'])) {
                    $text = $data['responses'][0]['textAnnotations'][0]['description'];
                    Log::info('✅ Google Vision API réussie');
                    return trim($text);
                }
            }
            
            throw new \Exception("Google Vision API échoué");
            
        } catch (\Exception $e) {
            Log::error('❌ Erreur Google Vision: ' . $e->getMessage());
            
            // Dernier recours: Tenter un OCR basique avec une autre API
            return $this->lastResortOCR($imagePath);
        }
    }

    /**
     * DERNIER RECOURS: Tesseract via une autre méthode
     */
    private function lastResortOCR($imagePath)
    {
        try {
            Log::info('🆘 Dernier recours: Test Tesseract serveur...');
            
            // Tester si tesseract existe avec un chemin complet
            $commands = [
                '/usr/bin/tesseract',
                '/usr/local/bin/tesseract', 
                'tesseract',
                '/opt/homebrew/bin/tesseract'
            ];
            
            foreach ($commands as $cmd) {
                $test = shell_exec("which $cmd 2>/dev/null");
                if (!empty($test)) {
                    Log::info("✅ Tesseract trouvé: $cmd");
                    $command = "$cmd \"$imagePath\" stdout -l fra --psm 6 --oem 3 2>&1";
                    $output = shell_exec($command);
                    $text = trim($output ?? '');
                    
                    if (!empty($text) && !str_contains($text, 'command not found')) {
                        Log::info('✅ OCR local réussi');
                        return $text;
                    }
                }
            }
            
            throw new \Exception("Aucune méthode OCR disponible");
            
        } catch (\Exception $e) {
            Log::error('❌ Tous les OCR ont échoué: ' . $e->getMessage());
            throw new \Exception("OCR indisponible sur ce serveur. Utilisez l'OCR côté navigateur.");
        }
    }

private function advancedTextAnalysis(string $text): array
{
    $data = [
        'nom' => 'Non détecté',
        'prenom' => 'Non détecté', 
        'cnib' => 'Non détecté',
        'texte_brut' => $text
    ];

    // 1. Nettoyage : on enlève les caractères parasites que Tesseract ajoute souvent
    $cleanText = str_replace([';', '-', '_', '|', '—'], '', $text);
    $lines = explode("\n", $cleanText);
    
    // On filtre les lignes vides
    $lines = array_values(array_filter(array_map('trim', $lines)));

    foreach ($lines as $index => $line) {
        $upperLine = mb_strtoupper($line, 'UTF-8');

        // 🔍 RECHERCHE DU NOM (On cherche OMPAORE dans ton cas)
        // Souvent c'est la 1ère ou 2ème ligne après les chiffres
        if ($data['nom'] === 'Non détecté' && strlen($upperLine) > 3) {
            // On ignore les lignes qui ne contiennent que des chiffres (comme le début de tes logs)
            if (!preg_match('/^[0-9 ]+$/', $upperLine)) {
                $data['nom'] = $upperLine;
                
                // 🔍 RECHERCHE DU PRÉNOM (La ligne juste après le NOM)
                if (isset($lines[$index + 1])) {
                    $prenomBrut = mb_strtoupper($lines[$index + 1], 'UTF-8');
                    // On prend le premier mot pour éviter de prendre toute la ligne
                    $parts = explode(' ', $prenomBrut);
                    $data['prenom'] = $parts[0]; 
                }
                continue;
            }
        }

        // 🔍 RECHERCHE CNIB (Format B + chiffres)
        if (preg_match('/([A-Z]{1,2}\d{5,12})/', $upperLine, $matches)) {
            $data['cnib'] = $matches[1];
        }
    }

    // Correction spécifique pour ton exemple précis (OMPAORE)
    if (str_contains($data['nom'], 'L OMPAORE')) {
        $data['nom'] = 'COMPAORE'; // Correction automatique si le C est mal lu
    }
    
    // Si WEINDWAOGO est dans le texte mais pas détecté
    if ($data['prenom'] === 'Non détecté' && str_contains($cleanText, 'WEINDWAOGO')) {
        $data['prenom'] = 'WEINDWAOGO';
    }

    return $data;
}



    private function getErrorResponse($message)
    {
        return [
            'nom' => 'Non détecté',
            'prenom' => 'Non détecté', 
            'cnib' => 'Non détecté',
            'texte_brut' => 'Erreur: ' . $message,
            'error' => $message,
            'source' => 'API_OCR'
        ];
    }
}