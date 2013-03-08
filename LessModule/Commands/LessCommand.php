<?php

/**
 * Description of LessCommand
 *
 * @author pave
 */

namespace LessModule\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class LessCommand extends Command {

	protected function configure() {
		$this->setName('less:compile')
				->setDescription('compiles less into css file')
				->setDefinition(array(
					new InputArgument('fileIn', InputArgument::REQUIRED),
					new InputArgument('fileOut', InputArgument::REQUIRED)
				));
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$in = $this->normalizePath($input->getArgument('fileIn'));
		$out =$this->normalizePath($input->getArgument('fileOut'));

		$less = new \lessc();
		try {
			$less->compileFile($in, $out);
		} catch (\Exception $e) {
			$output->writeln("<error>fatal error: {$e->getMessage()}</error>");
		}
	}
	
	protected function normalizePath($path)
	{
		if(substr($path, 0, 1) === '/'){
			return $path;
		}
		
		return getcwd() . '/' . $path;
	}

}

