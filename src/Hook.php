<?php
	/** @noinspection PhpUnused */
	
	namespace YetAnother;

	use Closure;
	
	/**
	 * Represents a hook that can be used to implement plugin architectures.
	 * Hooks can define slots where actions are executed or filters that
	 * can modify data used by a system.
	 * @package App\Library
	 */
	class Hook
	{
		/** @var self[] $hooks */
		private static array $hooks = [];
		
		/** @var array<int, list<HookFunction>> $functions */
		private array $functions = [];
		
		private string $name;
		
		/**
		 * Gets all of the hooks whether or not they contain any callbacks.
		 * @return Hook[]
		 */
		public static function getAll(): array
		{
			return self::$hooks;
		}
		
		/**
		 * Resets
		 * @return void
		 */
		public static function resetAll(): void
		{
			self::$hooks = [];
		}
		
		/**
		 * Initialises a new hook.
		 * @param string $name The name of the hook.
		 */
		protected function __construct(string $name)
		{
			$this->name = $name;
		}

		/**
		 * Finds or creates a new global hook using the specified name.
		 * @param string $name
		 * @return self
		 */
		public static function get(string $name): self
		{
			return self::$hooks[$name] ??= new self($name);
		}
		
		/**
		 * Returns the name of the hook.
		 * @return string
		 */
		public function name(): string
		{
			return $this->name;
		}
		
		/**
		 * Returns the functions registered in the hook.
		 * @return array
		 */
		public function getFunctions(): array
		{
			return $this->functions;
		}
		
		/**
		 * Resets the hook, removing all functions registered in it.
		 * @return void
		 */
		public function reset(): void
		{
			$this->functions = [];
		}
		
		/**
		 * Registers a function in the hook.
		 * @param callable|Closure $callable The function to call.
		 * @param int $priority Optional. The priority to execute the function at.
		 * @param string|null $name Optional. The name for the registered function in order to remove it later.
		 * @return HookFunction
		 */
		public function add(callable|Closure $callable, int $priority = 10, ?string $name = null): HookFunction
		{
			$this->functions[$priority] ??= [];
			$this->functions[$priority][] = $function = new HookFunction($name, $callable);
			ksort($this->functions);
			return $function;
		}

		/**
		 * Unregisters a function in the hook.
		 * @param string $name The name of the registered function.
		 * @param int|null $priority Optional. The priority the function was added to.
		 * @return self
		 */
		public function remove(string $name, ?int $priority = null): self
		{
			foreach($this->functions as $p=>&$list)
			{
				if ($priority !== null && $p != $priority)
					continue;
				
				foreach($list as $index=>&$item)
				{
					if ($item->name === $name)
						unset($list[$index]);
				}
			}
			return $this;
		}
		
		/**
		 * Unregisters a function in the hook.
		 * @param HookFunction $function The registered function.
		 * @return self
		 */
		public function removeFunction(HookFunction $function): self
		{
			foreach($this->functions as &$list)
			{
				foreach($list as $index=>&$item)
				{
					if ($item === $function)
						unset($list[$index]);
				}
			}
			return $this;
		}

		/**
		 * Executes every function in the hook.
		 * @param array $parameters The parameters, if any, to pass to each function.
		 */
		public function run(...$parameters): void
		{
			$this->execute($parameters);
		}

		/**
		 * Executes every function in the hook.
		 * @param array $parameters The parameters, if any, to pass to each function.
		 */
		public function execute(array $parameters): void
		{
			foreach($this->functions as $items)
			{
				foreach($items as $item)
				{
					$item->call($parameters);
				}
			}
		}

		/**
		 * Executes every function in the hook until a specific value is returned.
		 * @param mixed $returnValue The value to wait for and return immediately. This is compared strictly.
		 * @param array $parameters The parameters, if any, to pass to each function.
		 * @return bool True if the specified value was returned by a function, false otherwise.
		 */
		public function executeUntil(mixed $returnValue, array $parameters): bool
		{
			foreach($this->functions as $items)
			{
				foreach($items as $item)
				{
					if ($item->call($parameters) === $returnValue)
						return true;
				}
			}
			return false;
		}

		/**
		 * Executes every function in the hook.
	     * @param mixed $initial The initial value to filter.
		 * @param array $parameters The parameters, if any, to pass to each function.
		 * @return mixed
		 */
		public function filter(mixed $initial, ...$parameters): mixed
		{
			return $this->executeFilter($initial, $parameters);
		}
		
		/**
		 * Executes every function in the hook until the first non-null result is returned.
		 * @param mixed ...$parameters
		 * @return mixed
		 */
		public function firstResult(...$parameters): mixed
		{
			foreach($this->functions as $items)
			{
				foreach($items as $item)
				{
					if (($value = $item->call($parameters)) !== null)
						return $value;
				}
			}
			return null;
		}

		/**
		 * Executes every function in the hook.
	     * @param mixed $initial The initial value to filter.
		 * @param array $parameters The parameters, if any, to pass to each function.
		 * @return mixed
		 */
		public function executeFilter(mixed $initial, array $parameters): mixed
		{
			foreach($this->functions as $items)
			{
				foreach($items as $item)
				{
					$initial = $item->call(array_merge([ $initial ], $parameters));
				}
			}
			return $initial;
		}
		
		/**
		 * Executes every function in the hook.
		 * @param mixed $initial The initial value to filter.
		 * @param mixed $value The value to wait for and return immediately. This is compared strictly.
		 * @param array $parameters The parameters, if any, to pass to each function.
		 * @return mixed
		 */
		public function executeFilterUntil(mixed $initial, mixed $value, array $parameters): mixed
		{
			foreach($this->functions as $items)
			{
				foreach($items as $item)
				{
					$initial = $item->call(array_merge([ $initial ], $parameters));
					if ($initial === $value)
						return $value;
				}
			}
			return $initial;
		}
	}
