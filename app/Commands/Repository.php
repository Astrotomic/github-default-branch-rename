<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;
use OutOfBoundsException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class Repository extends RenameCommand
{
    protected $signature = 'repository
                            {name : The name of the repository (required)}
                            {--current= : The current name of the branch to rename (optional)}
                            {--target=main : The new name of the branch to rename (optional)}
                            ';
    protected $description = 'Rename the default branch of a single repository';

    public function handle(): void
    {
        $name = $this->argument('name');

        /** @var array $repo */
        $repo = Http::github()->get("/repos/{$name}")->json();

        $current = $this->option('current') ?? $repo['default_branch'];
        $target = $this->option('target');

        $this->rename($repo, $current, $target);
    }
}
