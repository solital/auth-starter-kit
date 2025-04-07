<?php 

namespace Solital\Login;

use Solital\Core\Console\Interface\ExtendCommandsInterface;

/**
 * @generated class generated using Vinci Console
 */
class LoginKit implements ExtendCommandsInterface
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
		return "Solital Login Kit";
	}
}
