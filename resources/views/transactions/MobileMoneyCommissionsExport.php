<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MobileMoneyCommissionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithEvents
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data['transactions'];
    }

    public function headings(): array
    {
        return [
            'ID Transaction',
            'Date',
            'Heure', 
            'Nom Client',
            'Prénom Client',
            'Téléphone',
            'Type Opération',
            'Opérateur',
            'Montant Transaction (FCFA)',
            'Commission (FCFA)',
            'Taux Commission',
            'CNIB',
            'Caissier'
        ];
    }

    public function map($transaction): array
    {
        $tauxCommission = $transaction->montant > 0 ? ($transaction->commission / $transaction->montant) * 100 : 0;

        return [
            $transaction->id_transaction,
            $transaction->created_at->format('d/m/Y'),
            $transaction->created_at->format('H:i'),
            $transaction->nom,
            $transaction->prenom,
            $transaction->telephone,
            $this->data['getTypeOperationLabel']($transaction->type_operation),
            $this->data['getOperateurNom']($transaction->nature),
            number_format($transaction->montant, 2, ',', ' '),
            number_format($transaction->commission, 2, ',', ' '),
            number_format($tauxCommission, 2, ',', ' ') . '%',
            $transaction->cnib ?? 'N/A',
            $transaction->user->name ?? 'N/A'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Styles pour l'en-tête
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2E86AB']]
        ]);

        // Bordures pour toutes les cellules
        $sheet->getStyle('A1:M' . ($this->data['transactions']->count() + 1))
              ->getBorders()->getAllBorders()->setBorderStyle('thin');

        // Alignement
        $sheet->getStyle('A1:M' . ($this->data['transactions']->count() + 1))
              ->getAlignment()->setVertical('center');

        // Largeur des colonnes
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(20);
        $sheet->getColumnDimension('J')->setWidth(20);
        $sheet->getColumnDimension('K')->setWidth(15);
        $sheet->getColumnDimension('L')->setWidth(15);
        $sheet->getColumnDimension('M')->setWidth(15);
    }

    public function title(): string
    {
        return 'Commissions Mobile Money';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $data = $this->data;
                $rowStart = $this->data['transactions']->count() + 3;
                
                // Ajouter les statistiques
                $event->sheet->setCellValue('A' . $rowStart, 'RAPPORT DES COMMISSIONS - SYNTHÈSE');
                $event->sheet->mergeCells('A' . $rowStart . ':M' . $rowStart);
                $event->sheet->getStyle('A' . $rowStart)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F8F9FA']]
                ]);

                $rowStart++;
                $event->sheet->setCellValue('A' . $rowStart, 'Période:');
                $event->sheet->setCellValue('B' . $rowStart, $data['statistiques']['periode']);
                
                $rowStart++;
                $event->sheet->setCellValue('A' . $rowStart, 'Filtre Opérateur:');
                $event->sheet->setCellValue('B' . $rowStart, $data['filtres']['operateur']);
                
                $rowStart++;
                $event->sheet->setCellValue('A' . $rowStart, 'Filtre Type Opération:');
                $event->sheet->setCellValue('B' . $rowStart, $data['filtres']['type_operation']);
                
                $rowStart += 2;
                $event->sheet->setCellValue('A' . $rowStart, 'STATISTIQUES GÉNÉRALES');
                $event->sheet->mergeCells('A' . $rowStart . ':M' . $rowStart);
                $event->sheet->getStyle('A' . $rowStart)->getFont()->setBold(true);
                
                $rowStart++;
                $event->sheet->setCellValue('A' . $rowStart, 'Total Transactions:');
                $event->sheet->setCellValue('B' . $rowStart, $data['statistiques']['total_transactions']);
                
                $rowStart++;
                $event->sheet->setCellValue('A' . $rowStart, 'Total Commissions:');
                $event->sheet->setCellValue('B' . $rowStart, number_format($data['statistiques']['total_commissions'], 2, ',', ' ') . ' FCFA');
                
                $rowStart++;
                $event->sheet->setCellValue('A' . $rowStart, 'Commission Moyenne:');
                $event->sheet->setCellValue('B' . $rowStart, number_format($data['statistiques']['commission_moyenne'], 2, ',', ' ') . ' FCFA');

                // Appliquer les styles aux statistiques
                $event->sheet->getStyle('A' . ($rowStart - 6) . ':M' . $rowStart)
                      ->getBorders()->getAllBorders()->setBorderStyle('thin');
            },
        ];
    }
}