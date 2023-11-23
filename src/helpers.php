<?php
	use YetAnother\Hook;
	use YetAnother\HookFunction;
	
	if (!function_exists('hook_add'))
	{
		/**
		 * Adds a function to a hook.
		 * @param string $hook The name of the hook.
		 * @param callable|Closure $callable The function to call.
		 * @param int $priority Optional. The priority to execute the function at.
		 * @param string|null $name Optional. The name for the registered function in order to remove it later.
		 */
		function hook_add(string $hook, callable|Closure $callable, int $priority = 10, ?string $name = null): HookFunction
		{
			return Hook::get($hook)->add($callable, $priority, $name);
		}
	}
	
	if (!function_exists('hook_remove'))
	{
		/**
		 * Removes a function from a hook.
		 * @param string $hook The name of the hook.
		 * @param string $name The name of the registered function.
		 * @param int|null $priority Optional. The priority the function was added to.
		 */
		function hook_remove(string $hook, string $name, ?int $priority = null): Hook
		{
			return Hook::get($hook)->remove($name, $priority);
		}
	}

	if (!function_exists('hook_run'))
	{
		/**
		 * Executes a hook with parameters.
		 * @param string $name The name of the hook.
		 * @param array $parameters The parameters, if any, to pass to each function.
		 */
		function hook_run(string $name, ...$parameters): void
		{
			Hook::get($name)->execute($parameters);
		}
	}

	if (!function_exists('hook_filter'))
	{
		/**
		 * Executes a hook as a filter, passing an initial value and optional parameters and returning the result.
		 * @param string $name The name of the hook.
		 * @param mixed $initial The initial value to filter.
		 * @param array $parameters The parameters, if any, to pass to each function.
		 * @return mixed
		 */
		function hook_filter(string $name, mixed $initial, ...$parameters): mixed
		{
			return Hook::get($name)->executeFilter($initial, $parameters);
		}
	}

	if (!function_exists('hook_filter_until'))
	{
		/**
		 * Executes a hook as a filter, passing an initial value and optional parameters and returning the result.
		 * If the specified value is returned by a function, the value is returned immediately and no further filter
		 * callbacks are executed.
		 * @param string $name The name of the hook.
		 * @param mixed $initial The initial value to filter.
		 * @param mixed $value The value to wait for and return immediately. This is compared strictly.
		 * @param array $parameters The parameters, if any, to pass to each function.
		 * @return mixed
		 */
		function hook_filter_until(string $name, mixed $initial, mixed $value, ...$parameters): mixed
		{
			return Hook::get($name)->executeFilterUntil($initial, $value, $parameters);
		}
	}
