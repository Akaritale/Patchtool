<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class Generate extends Command
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    protected static $defaultName = 'downloads:generate';
    /**
     * Base Patch URL
     */
    private string $url = 'https://patch.akaritale.com/';

    private string $project = 'Launcher';
    private string $directory;

    /**
     * @return SymfonyStyle
     */
    public function io(): SymfonyStyle
    {
        return $this->io;
    }

    protected function configure(): void
    {
        $this->setDescription('Generate download links list.');
        $this->addArgument('project', InputArgument::OPTIONAL, 'Select for which project we generate the urls.', $this->project);
        $this->addOption('project-dir', null, InputArgument::OPTIONAL, 'Directory where the patcher projects are located.');
        $this->addOption('url', null, InputArgument::OPTIONAL, 'Base Download URL', $this->url);
        //$this->addOption('output', null, InputArgument::OPTIONAL, 'Output file where links will be written to.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        //$file = $input->getOption('output');
        $this->url = (string)$input->getOption('url');
        $this->project = (string)$input->getArgument('project');

        $this->directory = $input->getOption('project-dir');
        $directory = $this->directory;

        $projects = $this->fetchProjects($directory);
        if (empty($projects)) {
            $this->io()->error("No Projects found in directory: {$directory}");
            return Command::FAILURE;
        }

        if (in_array($this->project, $projects, false) === false) {
            $this->io()->error("Selected project ({$this->project}) not found.");
            return Command::FAILURE;
        }

        $iterator = new \RecursiveDirectoryIterator($directory . DIRECTORY_SEPARATOR . $this->project);
        foreach (new \RecursiveIteratorIterator($iterator) as $item) {
            $parts = explode('/', $item);
            if(!is_array($parts)) {
                continue;
            }

            $file = array_pop($parts);
            if ($file === '.' || $file === '..') {
                continue;
            }

            $this->writeLine($item);
        }

        return Command::SUCCESS;
    }

    protected function fetchProjects(string $directory): array
    {
        $projects = [];
        $scan = scandir(realpath($directory));

        foreach ($scan as $item) {
            $path = $directory . DIRECTORY_SEPARATOR . $item;
            if (!is_dir($path)) {
                continue;
            }

            if ($item === '.' || $item === '..') {
                continue;
            }

            $projects[] = $item;
        }

        return $projects;
    }

    private function writeLine(string $item)
    {
        $file = str_replace("{$this->directory}/{$this->project}/", '', $item);
        $url = $this->url . $this->project . '/' . $file;

        $this->io()->writeln("{$file}\t{$url}");
    }
}