<?php

namespace Laurent\AnnaFileParser;

use League\Csv\Reader;
use League\Csv\Writer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Symfony\Component\String\u;

#[AsCommand(name: 'app:parse')]
class ParseCommand extends Command
{
    private const INPUT_FILE_PATH = __DIR__ . '/../data/sample1.csv';
    private const OUTPUT_FILE_PATH = __DIR__ . '/../data/result.csv';
    private const VALUE_ON = "On";

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $csvInput = Reader::createFromPath(self::INPUT_FILE_PATH)->setHeaderOffset(0);
        $csvOutput = Writer::createFromPath(self::OUTPUT_FILE_PATH, 'w+');

        $csvOutput->insertOne(self::getNewHeader($csvInput->getHeader()));
        foreach ($csvInput->getRecords() as $row) {
            $csvOutput->insertOne(self::getNewRow($row));
        }

        return Command::SUCCESS;
    }

    /**
     * @param string[] $header
     * @return string[]
     */
    private static function getNewHeader(array $header): array
    {
        return array_unique(array_map(fn($word) => self::getNewKey($word), $header));
    }

    private static function getNewKey(string $key): string
    {
        return preg_replace("/_\d$/", "", $key);
    }

    /**
     * @param string[] $row
     * @return string[]
     */
    private static function getNewRow(array $row): array
    {
        $result = [];
        foreach ($row as $k => $v) {
            $newValue = self::getNewValue($k, $v);
            if ($newValue !== "") {
                $result[self::getNewKey($k)] = $newValue;
            }
        }

        return $result;
    }

    private static function getNewValue(string $key, string $value): string
    {
        if (!preg_match("/_\d$/", $key)) {
            return $value;
        }
        if ($value == self::VALUE_ON) {
            return u($key)->slice(-1);
        }

        return "";
    }
}