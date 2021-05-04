<?php
	namespace YetAnother;
	
	use Closure;
	
	/**
	 * Represents a function used by a hook.
	 */
	class HookFunction
	{
		/** @var string|null $name Specifies the name of the function. */
		public ?string $name;
		
		/** @var callable|Closure $function Specifies the function to call. */
		public $function;
		
		/**
		 * HookFunction constructor.
		 * @param string|null $name The name of the function.
		 * @param callable|Closure $callback The function to call.
		 */
		public function __construct(?string $name, $callback)
		{
			$this->name = $name;
			$this->function = $callback;
		}
		
		/**
		 * Calls the hook function.
		 * @param array $parameters
		 * @return false|mixed
		 */
		public function call(array $parameters)
		{
			return call_user_func_array($this->function, $parameters);
		}
	}