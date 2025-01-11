<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(name: 'app:note')]
class NoteCommand extends Command
{
    protected function configure()
    {
        $this->addArgument('data', InputArgument::REQUIRED);
        $this->addArgument('semitones', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filesystem = new Filesystem();
        $file = file_get_contents(getcwd() . '/' . $input->getArgument('data'));

        if (false === $file) {
            $output->writeln('Invalid file');
        }

        $unsortedData = json_decode((string) $file, true);

        if (json_last_error()) {
            $output->writeln('Failed to parse json file ' . json_last_error_msg());
        }

        $semitones = (float) $input->getArgument('semitones');

        $data = $unsortedData;

        usort($data, fn($a, $b) =>
            [$a[0], $a[1]]
            <=>
            [$b[0], $b[1]]
        );

        //check data for keyboard range
        $checkMin = false;
        if ($semitones < 0) {
            $elem = $data[0];
            $checkMin = true;
        } else {
            $elem = $data[count($data) - 1];
        }
        $this->transposeElem($elem, $semitones);

        if ($checkMin) {
            if ($elem[0] < -3 || ($elem[0] == -3 && $elem[1] < 10)) {
                $output->writeln('Out of range. Min value');

                return Command::FAILURE;
            }
        } else {
            if ($elem[0] > 5 || ($elem[0] == 5 && $elem[1] > 1)) {
                $output->writeln('Out of range. Max value');

                return Command::FAILURE;
            }
        }

        foreach ($unsortedData as $k => $v) {
            $this->transposeElem($unsortedData[$k], $semitones);
        }

        $dir = getcwd() . '/public/tmp';

        if (!$filesystem->exists($dir)) {
            $filesystem->mkdir($dir);
        }

        $filesystem->dumpFile($dir . '/out.json', (string) json_encode($unsortedData));
        $output->writeln("Success! Your file is here: http://localhost:9081/tmp/out.json");

        return Command::SUCCESS;
    }

    private function transposeElem(array &$item, float $transpose): void
    {
        $item[1] = $item[1] + $transpose;

        if ($item[1] <= 0) {
            $item[0]--;

            if ($item[1] == 0) {
                $item[1] = 12;
            } else {
                $item[1] = 12 + $item[1];
            }
        } else {
            if ($item[1] > 12) {
                $item[0]++;
                $item[1] = $item[1] - 12;
            }
        }
    }
}
