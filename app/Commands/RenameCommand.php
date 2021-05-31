<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;
use OutOfBoundsException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

abstract class RenameCommand extends Command
{
    protected function rename(array $repo, string $current, string $target): void
    {
        if($repo['archived'] || $repo['disabled']) {
            $this->warn("Skip [{$repo['full_name']}] repository as it is archived or disabled.");
            return;
        }

        if($repo['fork']) {
            $this->warn("Skip [{$repo['full_name']}] repository as it is forked.");
            return;
        }

        /** @var \Illuminate\Support\Collection $branches */
        $branches = Http::github()->get("/repos/{$repo['full_name']}/branches")->collect()->pluck('name');

        if (!$branches->contains($current)) {
            throw new OutOfBoundsException("The [{$current}] of [{$repo['full_name']}] repository branch does not exist.");
        }

        if ($branches->contains($target)) {
            throw new OutOfBoundsException("The [{$target}] of [{$repo['full_name']}] repository branch does already exist.");
        }

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::github()->post("/repos/{$repo['full_name']}/branches/{$current}/rename", [
            'new_name' => $target,
        ]);

        if ($response->status() !== Response::HTTP_CREATED) {
            throw new RuntimeException('Renaming failed.');
        }

        $this->info("Renamed [{$current}] branch of [{$repo['full_name']}] repository to [{$target}].");
    }
}
