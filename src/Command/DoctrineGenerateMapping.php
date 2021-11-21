<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Command;

use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class DoctrineGenerateMapping extends Command
{
    protected static $defaultName = 'app:doctrine-generate-mapping';

    private string $pathToModel;
    private string $mappingFile;

    public function __construct(ParameterServiceInterface $parameterService)
    {
        parent::__construct();

        $this->pathToModel = $parameterService->get('model.path');
        $this->mappingFile = __DIR__ . '/mapping.yaml';;
    }

    protected function configure(): void
    {
        $this->setDescription('Generate doctrine entity mapping for doctrine config.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mapping = [];

        $finder = new Finder;
        $finder->directories()->in($this->pathToModel);

        foreach ($finder as $folder) {
            $finder = new Finder;
            $finder->directories()
                ->name('Entity')
                ->in($folder->getLinkTarget());

            if (count($finder) === 0) {
                continue;
            }

            $fileName = $folder->getFilename();

            $key = sprintf('App\Model\%s', $fileName);
            $mapping[$key] = [
                'is_bundle' => false,
                'type' => 'attribute',
                'dir' => sprintf('%%kernel.project_dir%%/src/Model/%s/Entity', $fileName),
                'prefix' => sprintf('App\Model\%s\Entity', $fileName)
            ];
        }

        file_put_contents($this->mappingFile, Yaml::dump($mapping));

        return Command::SUCCESS;
    }
}
