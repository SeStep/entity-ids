<?php declare(strict_types=1);

namespace SeStep\EntityIds\Console;

use SeStep\EntityIds\IdGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListIdTypesCommand extends Command
{
    /** @var string[] */
    private $types;

    /**
     * ListIdTypesCommand constructor.
     * @param string $name
     * @param IdGenerator $generator
     */
    public function __construct(string $name, IdGenerator $generator)
    {
        parent::__construct($name);

        $this->types = $generator->getTypes();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = new Table($output);
        $table->setHeaders(['Num', 'Type']);

        foreach ($this->types as $checkSum => $type) {
            $table->addRow([$checkSum, $type]);
        }

        $table->render();

        return 0;
    }
}
