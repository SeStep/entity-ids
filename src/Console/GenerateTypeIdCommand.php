<?php declare(strict_types=1);

namespace SeStep\EntityIds\Console;

use SeStep\EntityIds\IdGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateTypeIdCommand extends Command
{
    /** @var IdGenerator */
    private $generator;
    /** @var string[]  */
    private $types;

    /**
     * GenerateTypeIdCommand constructor.
     *
     * @param string $name
     * @param IdGenerator $generator
     */
    public function __construct(string $name, IdGenerator $generator)
    {
        parent::__construct($name);

        $this->generator = $generator;
        $this->types = $generator->getTypes();
    }

    protected function configure(): void
    {
        $this->addArgument('type', InputArgument::REQUIRED, 'Class name or checkSump of type to generate id for');
        $this->addArgument('count', InputArgument::OPTIONAL, 'Count of IDs to generate', '1');
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $typeArg = $input->getArgument('type');
        if (is_numeric($typeArg)) {
            if (!isset($this->types[$typeArg])) {
                $output->writeln("#$typeArg is not recognized to belong to an registered type");
                return 1;
            }

            $type = $this->types[$typeArg];
        } elseif(is_string($typeArg)) {
            $type = $typeArg;
        } else {
            throw new \TypeError("Type argument should be string or int, got" . gettype($typeArg));
        }

        if (!in_array($type, $this->types)) {
            $output->writeln("Type '$type' is not recognized");
            return 2;
        }

        $count = $input->getArgument('count');
        if (!is_numeric($count) || $count <= 0) {
            $count = 1;
        }

        $output->writeln("Generating IDs for type '$type'", $output::VERBOSITY_VERBOSE);
        for ($i = 0; $i < $count; $i++) {
            $output->writeln($this->generator->generateId($type));
        }

        return 0;
    }
}
