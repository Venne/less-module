<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace LessModule\Macros;

use Venne;

/**
 * @author Pavlína Ostrá <pave.pr@gmail.com>
 */
class LessMacro extends \Nette\Latte\Macros\MacroSet
{

	/** @var Venne\Module\Helpers */
	protected $moduleHelpers;

	/** @var string */
	protected $wwwCacheDir;

	/** @var string */
	protected $wwwDir;

	/** @var string */
	protected $debugMode = FALSE;


	public function setModuleHelpers($moduleHelpers)
	{
		$this->moduleHelpers = $moduleHelpers;
	}


	public function setWwwCacheDir($wwwCacheDir)
	{
		$this->wwwCacheDir = $wwwCacheDir;
	}


	public function setWwwDir($wwwDir)
	{
		$this->wwwDir = $wwwDir;
	}


	public function setDebugMode($debugMode)
	{
		$this->debugMode = $debugMode;
	}


	public function filter(\Nette\Latte\MacroNode $node, $writer)
	{
		$path = $node->tokenizer->fetchWord();
		$params = $writer->formatArray();
		$path = $this->moduleHelpers->expandPath($path, 'Resources/public');
		$path = \Nette\Utils\Strings::replace($path, '~\\\~', '/');

		if (!$this->debugMode) {
			$less = new \lessc();

			$file = new \SplFileInfo($path);
			$targetFile = $file->getBasename() . '-' . md5($path . filemtime($path)) . '.css';
			$targetDir = $this->wwwCacheDir . '/less';
			$target = $targetDir . '/' . $targetFile;
			$targetUrl = substr($target, strlen($this->wwwDir));

			if (!file_exists($targetDir)) {
				umask(0000);
				mkdir($targetDir, 0777, true);
			}

			$less->compileFile($path, $target);

			return ('$control->getPresenter()->getContext()->getService("assets.assetManager")->addStylesheet("' . $targetUrl . '", ' . $params . '); ');
		} else {
			return ('
				$_less_file = new \SplFileInfo("' . $path . '");
				$_less_targetFile = $_less_file->getBasename() .  \'-\' . md5(\'' . $path . '\') . \'-\' . md5(\'' . $path . '\' . filemtime("' . $path . '")) . \'.css\';
				$_less_targetDir = \'' . $this->wwwCacheDir . '/less\';
				$_less_target = $_less_targetDir  . \'/\' . $_less_targetFile;
				$_less_targetUrl = substr($_less_target, strlen(\'' . $this->wwwDir . '\'));

				if (!file_exists($_less_target)) {
					$_less = new \lessc();
					if (!file_exists($_less_targetDir)) {
						umask(0000);
						mkdir($_less_targetDir, 0777, true);
					}

					// Remove old files
					foreach (\Nette\Utils\Finder::findFiles($_less_file->getBasename() . \'-\' . md5(\'' . $path . '\') . \'-*\')->from($_less_targetDir) as $_less_old) {
						unlink($_less_old->getPathname());
					}

					$_less->compileFile(\'' . $path . '\', $_less_target);
				}

				$control->getPresenter()->getContext()->getService("assets.assetManager")->addStylesheet($_less_targetUrl, ' . $params . ');
			');
		}
	}


	public static function install(\Nette\Latte\Compiler $compiler, Venne\Module\Helpers $moduleHelpers = NULL, $wwwCacheDir = NULL, $wwwDir = NULL, $debugMode = NULL)
	{
		$me = new LessMacro($compiler);

		$me->moduleHelpers = $moduleHelpers;
		$me->wwwCacheDir = $wwwCacheDir;
		$me->wwwDir = $wwwDir;
		$me->debugMode = (bool) $debugMode;

		$me->addMacro('less', array($me, "filter"));
	}

}

