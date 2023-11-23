<?php
	namespace Hook;
	
	use PHPUnit\Framework\TestCase;
	use YetAnother\Hook;
	
	class HookTests extends TestCase
	{
		function setUp(): void
		{
			parent::setUp();
			Hook::resetAll();
		}
		
		function testHookIsRegisteredThenRemoved()
		{
			$hook = Hook::get('hook');
			
			hook_add('hook', function() { }, 10, 'key');
			self::assertCount(1, $hook->getFunctions()[10]);
			
			hook_remove('hook', 'key');
			self::assertCount(0, $hook->getFunctions()[10]);
		}
		
		function testFunctionIsCalledInHookUntilRemoved()
		{
			$value = null;
			
			hook_add('hook', function() use(&$value)
			{
				$value = 'Hello';
			}, 10, 'key');
			
			hook_run('hook');
			self::assertEquals('Hello', $value);
			$value = null;
			
			hook_remove('hook', 'key');
			hook_run('hook');
			
			self::assertNull($value);
		}
		
		function testFilterAccumulatesValues()
		{
			$sum = fn(int $i, int $x) => $i + $x;
			$mul = fn(int $i, int $x) => $i * $x;
			$str = fn(int $i, int $x) => $i . $x;
			
			hook_add('filter', $str, 2);
			hook_add('filter', $mul, 1);
			hook_add('filter', $sum, 0);
			
			$result = hook_filter('filter', 2, 5);
			
			self::assertEquals('355', $result);
		}
		
		function testFiltersRunUntilFalseIsReceived()
		{
			$added = [];
			$a = function () use (&$added)
			{
				return $added[] = 'Hello';
			};
			$b = function () use (&$added)
			{
				return $added[] = 'World';
			};
			$c = fn() => '123';
			$d = function () use (&$added)
			{
				return $added[] = '!';
			};
			
			hook_add('filter', $a, 0);
			hook_add('filter', $b, 1);
			hook_add('filter', $c, 2);
			hook_add('filter', $d, 3);
			
			$result = hook_filter_until('filter', 0, '123');
			
			self::assertEquals('123', $result);
			self::assertEquals(join(',', ['Hello', 'World']), join(',', $added));
		}
	}
