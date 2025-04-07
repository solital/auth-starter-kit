<?php 

namespace Solital\AuthKit;

use Solital\Core\Console\Interface\ExtendCommandsInterface;

class AuthKit implements ExtendCommandsInterface
{
	/**
	 * @return array
	 */
	public function getCommandClass(): array
	{
		return [
            MakeAuth::class
        ];
	}

	/**
	 * @return string
	 */
	public function getTypeCommands(): string
	{
		return "Solital Auth Kit";
	}
}
