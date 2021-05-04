# YetAnother\Hook

Hooks is a small library that provided hookable actions and filters,
similar to WordPress. This can be used in any PHP 7.4+ project and is
a simple way of implementing plugin capabilities in your applications
by exposing hookable actions and filters to plugin-code.

The `Hook` class provides a simple interface to registering functions
to call under specific hook names, sorted by priority. A hook's list
of functions can then be executed with optional parameters, or used
as an accumulator for an initial value.

While the `Hook` class itself can be used to create, register and
execute hooks, you can also use the global `hook_*` functions to
shortcut these operations.

---

### Menu Example

A menu might include a default list of items, then this
list may be passed to a filter hook to allow plugins to modify or append
to the list.

#### Application Usage

```php
$menu = [
    'Plugins' => [
        new MenuItem('Plugin Manager', '/plugins/index')
    ]
];
$menuItems = hook_filter('main_menu', $menu);

// Add menu items to UI...
```

#### Plugin Usage

A plugin can then modify the list by receiving it and adding its own items
through a callback added to the same hook.

```php
hook_add('main_menu', function(array $menu)
{
    $menu['Plugins'][] = new MenuItem('My Plugin', '/my-plugin/');
    return $menu;
});
```

----

## Executing Actions with Hooks

To create, add a function to a hook then execute it, use the following
alternatives:

```php
$hook = \YetAnother\Hook::get('do_stuff');
$hook->add(function($param)
{
    print($param);
});
$hook->run('Hello, world!');

// "Hello, world!" is printed
```

```php
hook_add('do_stuff', fn($param) => print($param));
hook_run('do_stuff', 'Hello, world!');
```

## Filtering with Hooks

By registering callbacks on a hook used as a filter, you can
receive input and pass results to the next function in the filter.

```php
hook_add('accumulate', fn($initial, $parameter) => $initial + $parameter);
hook_add('accumulate', fn($initial, $parameter) => $initial * $parameter);

$result = hook_filter('accumulate', 5, 7);

// $result = 54 ((5 + 7) * 7)
```

```php
hook_add('menu', function(array $menu)
{
    $menu[] = 'World';
    return $menu;
});

$menu = hook_filter('menu', [ 'Hello' ]);

// $menu = [ 'Hello', 'World' ]
```