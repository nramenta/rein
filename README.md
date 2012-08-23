# `rein` - A stupidly-simple PHP test runner and reporter

## Why use `rein`

You're a busy developer and you like the idea of automating tests but find most
existing solutions much too bloated and cumbersome to deal with.

`rein` consists of a single function within a single PHP file. The whole thing
can be read and understood in less than 5 minutes. To run tests, simply provide
a file or an array of files as an argument to the `rein_run()` function:

```php
<?php
include '/path/to/rein.php';
rein_run('my_tests.php');
```

`rein` will then include and scan the `my_tests.php` file for functions
beginning with `test_`, executes them as tests and outputs a report. You can
run this from the CLI or from the browser. `rein` will not dictate the way you
organize your test files, how you bootstrap your tests, how you load your
fixtures or what goes where and why; it's automated testing for grown-ups. The
only thing it assumes is that you'll be using `assert()` in your tests.

## Requirements
`rein` requires PHP 5.3 or greater.

## License
`rein` is released under the [MIT license](http://opensource.org/licenses/MIT).

## Testing in 5 minutes or less

This section is intended for those who are absolute beginners when it comes to
automating tests.

Suppose you're writing a function to convert hexadecimal string representation
of a color into an RGB array with `'r'`, `'g'` and `'b'` elements. Here's one
way you can write it:

```php
<?php
// hex2rgb.php
function hex2rgb($color)
{
    $color = ltrim($color, '#');
    $int = hexdec($color);
    return array(
        'r' => 0xFF & ($int >> 0x10),
        'g' => 0xFF & ($int >> 0x8),
        'b' => 0xFF & $int,
    );
}
```

And here's the example test file:

```php
<?php
// test_hex2rgb.php
include '/path/to/hex2rgb.php';
include '/path/to/rein.php';

rein_run(__FILE__); // run the current file

function test_hex2rgb()
{
    assert("hex2rgb('#FFFFFF') == array('r' => 255, 'g' => 255, 'b' => 255);");
    assert("hex2rgb('#FF0000') == array('r' => 255, 'g' => 0, 'b' => 0);");
    assert("hex2rgb('#000000') == array('r' => 0, 'g' => 0, 'b' => 0);");
    assert("hex2rgb('#FFF') == array('r' => 255, 'g' => 255, 'b' => 255);");
    assert("hex2rgb('#FF') == false;");
}
```

When writing tests, you must take into account as many inputs, outputs, and
processing errors you can think of. You must also think of the kinds of edge
cases your code will have to endure.

Note that you can put as many `test_` functions as you like in one file or
divide them up according to their functionality -- whichever makes the most
sense to you.

You can then run the test file from the command line or browser. The result is
as follows:

```
Failed 2 of 5 assert(s) in 1 test(s) - completed in 0 seconds.
-------------------------------------------------------------------------------
1. /path/to/your/tests/test_hex2rgb.php:13
	hex2rgb('#FFF') == array('r' => 255, 'g' => 255, 'b' => 255);
-------------------------------------------------------------------------------
2. /path/to/your/tests/test_hex2rgb.php:14
	hex2rgb('#FF') == false;
-------------------------------------------------------------------------------
```

By using the native `assert()` function, `rein` is able to report every failed
assertions in your test files. From the test result above, we know that our
`hex2rgb()` function fails for at least one type of valid input: three-letter
abbreviated hexadecimal colors. We can now try to fix our function as follows:

```php
<?php
// hex2rgb.php
// returns an RGB array or false on error
function hex2rgb($color)
{
    $color = ltrim($color, '#');
    if (strlen($color) == 3) {
        $tmp = '';
        for ($i = 0; $i < 3; $i++) $tmp .= str_repeat($color[$i], 2);
        $color = $tmp;
    }
    if (!preg_match('/^([a-f]|[0-9]){3}(([a-f]|[0-9]){3})?$/i', $color)) {
        return false;
    }
    $int = hexdec($color);
    return array(
        'r' => 0xFF & ($int >> 0x10),
        'g' => 0xFF & ($int >> 0x8),
        'b' => 0xFF & $int,
    );
}
```

And get the following result:

```
Passed 5 assert(s) in 1 test(s) - completed in 0.001 seconds.
```

You can expand the example above so that `hex2rgb()` can gracefully handle other
types of errors as well. The idea is by using tests you can prevent certain bugs
from occuring provided you stay disciplined and meticulous in adding, updating
and running your tests against your code. As you add features, refactor old code
and fix existing bugs, your tests will make sure you don't accidentally create
new bugs.

The next step would be to automate your tests entirely. Once you've decided the
best setup for your tests, you should create a pre-commit hook for your version
control system so it'll run your tests just before each commit, and will refuse
to do so if any of your tests fails. Another thing you can do if you use `make`
is to automatically run your tests just before packaging and shipping your code.

I hope this section clears up any confusion you may have on automating tests.
