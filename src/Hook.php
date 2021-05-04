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
		
		private array $functions = [];
		private string $name;
		
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
		 * Gets the name of the hook.
		 * @return string
		 */
		public function name(): string
		{
			return $this->name;
		}
		
		/**
		 * Gets the functions registered in the hook.
		 * @return array
		 */
		public function getFunctions(): array
		{
			return $this->functions;
		}
		
		/**
		 * Registers a function in the hook.
		 * @param callable|Closure $callable The function to call.
		 * @param int $priority Optional. The priority to execute the function at.
		 * @param string|null $name Optional. The name for the registered function in order to remove it later.
		 * @return HookFunction
		 */
		public function add($callable, int $priority = 10, ?string $name = null): HookFunction
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
					/** @var HookFunction $item */
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
					/** @var HookFunction $item */
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
		public function run(...$parameters)
		{
			$this->execute($parameters);
		}

		/**
		 * Executes every function in the hook.
		 * @param array $parameters The parameters, if any, to pass to each function.
		 */
		public function execute(array $parameters)
		{
			foreach($this->functions as $items)
			{
				foreach($items as $item)
				{
					/** @var HookFunction $item */
					$item->call($parameters);
				}
			}
		}

		/**
		 * Executes every function in the hook.
	     * @param mixed $initial The initial value to filter.
		 * @param array $parameters The parameters, if any, to pass to each function.
		 * @return mixed
		 */
		public function filter($initial, ...$parameters)
		{
			return $this->executeFilter($initial, $parameters);
		}

		/**
		 * Executes every function in the hook.
	     * @param mixed $initial The initial value to filter.
		 * @param array $parameters The parameters, if any, to pass to each function.
		 * @return mixed
		 */
		public function executeFilter($initial, array $parameters)
		{
			foreach($this->functions as $items)
			{
				foreach($items as $item)
				{
					/** @var HookFunction $item */
					$initial = $item->call(array_merge([ $initial ], $parameters));
				}
			}
			return $initial;
		}
	}
