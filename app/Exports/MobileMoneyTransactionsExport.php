<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MobileMoneyTransactionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
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
            'Nom',
            'Prénom',
            'Téléphone',
            'Type Opération',
            'Opérateur',
            'Montant (FCFA)',
            'Commission (FCFA)',
            'CNIB',
            'Statut',
            'Caissier'
        ];
    }

    public function map($transaction): array
    {
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
            $transaction->cnib ?? 'N/A',
            $transaction->statut,
            $transaction->user->name ?? 'N/A'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style pour l'en-tête
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => '0b5f37']
            ]
        ]);

        // Auto-adjust column widths
        foreach(range('A','M') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [
            // Style pour les totaux si vous en ajoutez
        ];
    }

    public function title(): string
    {
        return 'Transactions Mobile Money';
    }
}