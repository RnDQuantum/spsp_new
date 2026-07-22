<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

class InspectLspDb extends Command
{
    protected $signature = 'db:inspect-lsp {--table= : Specific table to inspect} {--action=list : Action: list, describe, sample, counts, query}  {--query= : SQL query for action=query}';

    protected $description = 'Inspect LSP database tables (local or production based on DB_LSP_LOCAL)';

    private function conn(): Connection
    {
        return DB::connection('lsp');
    }

    public function handle(): int
    {
        $action = $this->option('action');
        $table = $this->option('table');

        match ($action) {
            'list' => $this->listTables(),
            'describe' => $this->describeTable($table),
            'sample' => $this->sampleTable($table),
            'counts' => $this->tableCounts(),
            'query' => $this->runQuery($this->option('query')),
            default => $this->error("Unknown action: {$action}"),
        };

        return 0;
    }

    private function listTables(): void
    {
        $tables = $this->conn()->select('SHOW TABLES');
        $this->info('=== ALL TABLES IN LSP DB ('.config('database.connections.lsp.database').') ===');
        foreach ($tables as $t) {
            $vals = get_object_vars($t);
            $this->line(array_values($vals)[0]);
        }
    }

    private function describeTable(?string $table): void
    {
        if (! $table) {
            $this->error('Please specify --table=name');

            return;
        }

        $columns = $this->conn()->select("DESCRIBE `{$table}`");
        $this->info("=== STRUCTURE: {$table} ===");
        $headers = ['Field', 'Type', 'Null', 'Key', 'Default', 'Extra'];
        $rows = array_map(fn ($c) => [
            $c->Field, $c->Type, $c->Null, $c->Key, $c->Default ?? 'NULL', $c->Extra,
        ], $columns);
        $this->table($headers, $rows);
    }

    private function sampleTable(?string $table): void
    {
        if (! $table) {
            $this->error('Please specify --table=name');

            return;
        }

        $rows = $this->conn()->select("SELECT * FROM `{$table}` LIMIT 3");
        $this->info("=== SAMPLE DATA: {$table} (max 3 rows) ===");
        if (empty($rows)) {
            $this->warn('No data found.');

            return;
        }
        $headers = array_keys(get_object_vars($rows[0]));
        $data = array_map(fn ($r) => array_map(fn ($v) => is_null($v) ? 'NULL' : (strlen((string) $v) > 50 ? substr((string) $v, 0, 47).'...' : (string) $v), get_object_vars($r)), $rows);
        $this->table($headers, $data);
    }

    private function tableCounts(): void
    {
        $tables = $this->conn()->select('SHOW TABLES');
        $this->info('=== ROW COUNTS ===');
        $rows = [];
        foreach ($tables as $t) {
            $vals = get_object_vars($t);
            $name = array_values($vals)[0];
            $count = $this->conn()->select("SELECT COUNT(*) as cnt FROM `{$name}`");
            $rows[] = [$name, $count[0]->cnt];
        }
        $this->table(['Table', 'Rows'], $rows);
    }

    private function runQuery(?string $query): void
    {
        if (! $query) {
            $this->error('Please specify --query="SELECT ..."');

            return;
        }

        $rows = $this->conn()->select($query);
        $this->info('=== QUERY RESULT ===');
        if (empty($rows)) {
            $this->warn('No results returned.');

            return;
        }
        $headers = array_keys(get_object_vars($rows[0]));
        $data = array_map(fn ($r) => array_map(fn ($v) => is_null($v) ? 'NULL' : (strlen((string) $v) > 50 ? substr((string) $v, 0, 47).'...' : (string) $v), get_object_vars($r)), $rows);
        $this->table($headers, $data);
    }
}
