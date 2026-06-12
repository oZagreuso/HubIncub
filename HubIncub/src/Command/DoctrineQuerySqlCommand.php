<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'doctrine:query:sql',
    description: 'Exécute une requête SQL avec la connexion Doctrine DBAL.',
)]
final class DoctrineQuerySqlCommand extends Command
{
    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('sql', InputArgument::REQUIRED, 'La requête SQL à exécuter.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $sql = trim((string) $input->getArgument('sql'));

        if ('' === $sql) {
            $io->error('La requête SQL ne peut pas être vide.');

            return Command::INVALID;
        }

        try {
            $result = $this->connection->executeQuery($sql);
            $rows = $result->fetchAllAssociative();
        } catch (Exception $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        if ([] === $rows) {
            $io->success('Requête exécutée. Aucun résultat à afficher.');

            return Command::SUCCESS;
        }

        $headers = array_keys($rows[0]);
        $io->table($headers, array_map(static fn (array $row): array => array_values($row), $rows));

        return Command::SUCCESS;
    }
}
