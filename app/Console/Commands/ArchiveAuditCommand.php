<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Audit;

class ArchiveAuditCommand extends Command
{
    protected $signature = 'audit:archive';
    protected $description = 'Archive et supprime les anciens audits (+365 jours)';

    public function handle()
    {
        $count = Audit::archiveOldRecords(365);

        $this->info("{$count} audits archivés et supprimés.");
        return 0;
    }
}
