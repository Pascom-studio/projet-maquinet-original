<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MobileMoneyCommissionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        // CORRECTION : Gestion des données manquantes
        return $this->data['commissions'] ?? collect();
    }

    public function headings(): array
    {
        return [
            'Période',
            'Opérateur', 
            'Type Opération',
            'Nombre Transactions',
            'Montant Total (FCFA)',
            'Commission Totale (FCFA)',
            'Commission Moyenne (FCFA)'
        ];
    }

    public function map($commission): array
    {
        $commissionMoyenne = $commission->nombre_transactions > 0 
            ? $commission->commission_totale / $commission->nombre_transactions 
            : 0;

        return [
            \Carbon\Carbon::createFromFormat('Y-m', $commission->periode)->format('F Y'),
            $this->data['getOperateurNom']($commission->operateur),
            $this->data['getTypeOperationLabel']($commission->type_operation),
            $commission->nombre_transactions,
            number_format($commission->montant_total, 2, ',', ' '),
            number_format($commission->commission_totale, 2, ',', ' '),
            number_format($commissionMoyenne, 2, ',', ' ')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // CORRECTION : Gestion du cas où il n'y a pas de données
        $commissionsCount = count($this->data['commissions'] ?? []);
        $lastRow = $commissionsCount > 0 ? $commissionsCount + 1 : 1;

        // Styles pour l'en-tête
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '8c52ff'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Styles pour les données seulement s'il y en a
        if ($commissionsCount > 0) {
            $sheet->getStyle('A2:G' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'DDDDDD'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        // Ajuster la largeur des colonnes
        $sheet->getColumnDimension('A')->setWidth(15); // Période
        $sheet->getColumnDimension('B')->setWidth(15); // Opérateur
        $sheet->getColumnDimension('C')->setWidth(15); // Type Opération
        $sheet->getColumnDimension('D')->setWidth(18); // Nombre Transactions
        $sheet->getColumnDimension('E')->setWidth(20); // Montant Total
        $sheet->getColumnDimension('F')->setWidth(20); // Commission Totale
        $sheet->getColumnDimension('G')->setWidth(20); // Commission Moyenne

        // Ajouter les statistiques seulement s'il y a des données
        if ($commissionsCount > 0) {
            $this->addStatistics($sheet, $lastRow);
        } else {
            // Message si pas de données
            $sheet->setCellValue('A3', 'AUCUNE DONNÉE DISPONIBLE');
            $sheet->mergeCells('A3:G3');
            $sheet->getStyle('A3')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FF0000']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        return $sheet;
    }

    private function addStatistics(Worksheet $sheet, $lastRow)
    {
        $startStatsRow = $lastRow + 2;
        
        // Titre du rapport
        $sheet->setCellValue('A' . $startStatsRow, 'RAPPORT DES COMMISSIONS - SYNTHÈSE GROUPÉE');
        $sheet->mergeCells('A' . $startStatsRow . ':G' . $startStatsRow);
        $sheet->getStyle('A' . $startStatsRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E8F4FD'],
            ],
        ]);

        // Période
        $sheet->setCellValue('A' . ($startStatsRow + 1), 'Période:');
        $sheet->setCellValue('B' . ($startStatsRow + 1), $this->data['statistiques']['periode'] ?? 'Non spécifiée');
        $sheet->mergeCells('B' . ($startStatsRow + 1) . ':G' . ($startStatsRow + 1));

        // Filtres
        $sheet->setCellValue('A' . ($startStatsRow + 2), 'Filtre Opérateur:');
        $sheet->setCellValue('B' . ($startStatsRow + 2), $this->data['filtres']['operateur'] ?? 'Tous');
        $sheet->mergeCells('B' . ($startStatsRow + 2) . ':G' . ($startStatsRow + 2));

        $sheet->setCellValue('A' . ($startStatsRow + 3), 'Filtre Type Opération:');
        $sheet->setCellValue('B' . ($startStatsRow + 3), $this->data['filtres']['type_operation'] ?? 'Tous');
        $sheet->mergeCells('B' . ($startStatsRow + 3) . ':G' . ($startStatsRow + 3));

        // Statistiques générales
        $statsTitleRow = $startStatsRow + 5;
        $sheet->setCellValue('A' . $statsTitleRow, 'STATISTIQUES GÉNÉRALES');
        $sheet->mergeCells('A' . $statsTitleRow . ':G' . $statsTitleRow);
        $sheet->getStyle('A' . $statsTitleRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F0F8FF'],
            ],
        ]);

        $sheet->setCellValue('A' . ($statsTitleRow + 1), 'Total Transactions:');
        $sheet->setCellValue('B' . ($statsTitleRow + 1), $this->data['statistiques']['total_transactions'] ?? 0);

        $sheet->setCellValue('A' . ($statsTitleRow + 2), 'Total Commissions:');
        $sheet->setCellValue('B' . ($statsTitleRow + 2), number_format($this->data['statistiques']['total_commissions'] ?? 0, 2, ',', ' ') . ' FCFA');

        $sheet->setCellValue('A' . ($statsTitleRow + 3), 'Commission Moyenne:');
        $sheet->setCellValue('B' . ($statsTitleRow + 3), number_format($this->data['statistiques']['commission_moyenne'] ?? 0, 2, ',', ' ') . ' FCFA');

        // Répartition par opérateur
        $operateurTitleRow = $statsTitleRow + 5;
        $sheet->setCellValue('A' . $operateurTitleRow, 'RÉPARTITION PAR OPÉRATEUR');
        $sheet->mergeCells('A' . $operateurTitleRow . ':G' . $operateurTitleRow);
        $sheet->getStyle('A' . $operateurTitleRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F0F8FF'],
            ],
        ]);

        $currentRow = $operateurTitleRow + 1;
        $commissionsParOperateur = $this->data['statistiques']['commissions_par_operateur'] ?? [];
        
        foreach ($commissionsParOperateur as $operateur => $data) {
            if ($data['transactions'] > 0) {
                $operateurNom = $this->data['getOperateurNom']($operateur);
                $sheet->setCellValue('A' . $currentRow, $operateurNom . ':');
                $sheet->setCellValue('B' . $currentRow, number_format($data['commission'], 2, ',', ' ') . ' FCFA');
                $sheet->setCellValue('C' . $currentRow, '(' . $data['transactions'] . ' transactions - ' . number_format($data['pourcentage'], 1, ',', ' ') . '%)');
                $currentRow++;
            }
        }

        // Appliquer les bordures aux statistiques
        $statsEndRow = $currentRow - 1;
        if ($statsEndRow >= $startStatsRow) {
            $sheet->getStyle('A' . $startStatsRow . ':G' . $statsEndRow)
                  ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }
    }

    public function title(): string
    {
        return 'Commissions Groupées';
    }
}