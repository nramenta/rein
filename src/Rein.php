<?php
/**
 * Rein - A simple PHP test runner and reporter.
 *
 * @author  Nofriandi Ramenta <nramenta@gmail.com>
 * @license http://en.wikipedia.org/wiki/MIT_License MIT
 */

class Rein
{
    /**
     * Runs tests and outputs report. This function will run all functions
     * beginning with test_ found in all files provided as arguments. Invalid
     * files are skipped.
     *
     * @param string|array $files Either a string or an array of test files
     *
     * @return bool True if all tests passes, false otherwise
     */
    public static function run($files)
    {
        static $tokens = array();

        $failures = array();

        assert_options(ASSERT_ACTIVE,     true);
        assert_options(ASSERT_BAIL,       false);
        assert_options(ASSERT_WARNING,    false);
        assert_options(ASSERT_QUIET_EVAL, false);
        assert_options(ASSERT_CALLBACK,   function($file, $line, $assert, $description = null) use (&$failures, &$tokens) {
            $failures[] = array('file' => $file, 'line' => $line, 'assert' => $assert, 'description' => $description);
        });

        if (is_string($files)) $files = (array) $files;

        $asserts = 0;
        $tests   = 0;

        $t0 = microtime(true);
        foreach ($files as $file) {
            if (!(is_string($file) && is_readable($file))) continue;
            $source  = file_get_contents($file);
            $asserts = preg_match_all('/assert\(/im', $source, $_);
            if (preg_match_all('/function (test_(?:[a-z_][a-z0-9_]*))\(/im', $source, $matches)) {
                $functions = $matches[1];
                require_once $file;
                foreach ($functions as $f) {
                    if (is_callable($f)) {
                        $tests += 1;
                        call_user_func($f);
                    }
                }
            }
        }
        $t  = round(microtime(true) - $t0, 3);

        ob_start();

        if ($failures) {
            print 'Failed ' . count($failures) . " of $asserts assert(s) in $tests test(s) - completed in $t seconds." . PHP_EOL;
            print str_repeat('-', 79) . PHP_EOL;
            foreach ($failures as $i => $failure) {
                print ($i+1) . ". $failure[file]:$failure[line]" . PHP_EOL;
                if (strlen($failure['assert'])) print "\t$failure[assert]" . PHP_EOL;
                print str_repeat('-', 79) . PHP_EOL;
            }
        } else {
            print "Passed $asserts assert(s) in $tests test(s) - completed in $t seconds." . PHP_EOL;
        }

        $report = ob_get_clean();

        if (php_sapi_name() == 'cli') {
            echo $report;
        } else {
            echo '<pre>' . PHP_EOL;
            echo $report;
            echo '</pre>' . PHP_EOL;
        }

        return empty($failures);
    }
}

