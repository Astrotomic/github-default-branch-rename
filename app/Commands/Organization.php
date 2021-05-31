<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;
use OutOfBoundsException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class Organization extends RenameCommand
{
    protected $signature = 'organization
                            {name : The name of the organization (required)}
                            {--type=all : The type of repositories to handle (optional)}
                            {--current= : The current name of the branch to rename (optional)}
                            {--target=main : The new name of the branch to rename (optional)}
                            ';
    protected $description = 'Rename the default branch of all organization repositories';

    public function handle(): void
    {
        $name = $this->argument('name');

        $page = 1;
        do {
            $response = Http::github()->get("/orgs/{$name}/repos", [
                'type' => $this->option('type'),
                'per_page' => 100,
                'page' => $page,
            ])->collect();

            $response->each(function (array $repo) {
                $current = $this->option('current') ?? $repo['default_branch'];
                $target = $this->option('target');

                if($current === $target) {
                    $this->warn("Skip [{$repo['full_name']}] repository as current and target branch names equal [{$current}].");
                    return;
                }

                try {
                    $this->rename($repo, $current, $target);
                } catch(OutOfBoundsException $ex) {
                    $this->error($ex->getMessage());
                }
            });

            $page++;
        } while($response->count() >= 100);

    }
}
