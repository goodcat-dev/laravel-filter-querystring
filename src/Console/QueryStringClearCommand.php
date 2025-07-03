<?php

namespace Goodcat\QueryString\Console;

use Goodcat\QueryString\QueryString;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'querystring:clear')]
class QueryStringClearCommand extends Command
{
    protected $name = 'querystring:clear';

    protected $description = 'Clear all querystring cache';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->files->delete(QueryString::getCachePath());

        $this->components->info('Cached query strings cleared successfully.');
    }
}