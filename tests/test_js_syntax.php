<?php
$content = file_get_contents(__DIR__ . '/../public/assets/js/ajax.js');

$stack = [];
$len = strlen($content);
$inString = false;
$stringChar = '';
$inComment = false;
$inRegex = false;
$line = 1;

for ($i = 0; $i < $len; $i++) {
    $c = $content[$i];
    $next = ($i + 1 < $len) ? $content[$i + 1] : '';
    $prev = ($i > 0) ? $content[$i - 1] : '';

    if ($c === "\n") {
        $line++;
        if ($inComment === 'line') {
            $inComment = false;
        }
    }

    if ($inComment === 'line') continue;
    if ($inComment === 'block') {
        if ($c === '*' && $next === '/') {
            $inComment = false;
            $i++;
        }
        continue;
    }

    if ($inString) {
        if ($c === '\\') {
            $i++; // skip escaped
            continue;
        }
        if ($c === $stringChar) {
            $inString = false;
        }
        continue;
    }

    if ($inRegex) {
        if ($c === '\\') {
            $i++;
            continue;
        }
        if ($c === '/') {
            $inRegex = false;
        }
        continue;
    }

    if ($c === '/' && $next === '/') {
        $inComment = 'line';
        $i++;
        continue;
    }
    if ($c === '/' && $next === '*') {
        $inComment = 'block';
        $i++;
        continue;
    }

    // Check if / is start of regex
    if ($c === '/') {
        // Look back non-whitespace
        $j = $i - 1;
        while ($j >= 0 && ctype_space($content[$j])) $j--;
        $prevNonSpace = ($j >= 0) ? $content[$j] : '';
        if (in_array($prevNonSpace, ['(', '=', ':', ',', '[', '!', '&', '|', '?', '{', ';', ''], true)) {
            $inRegex = true;
            continue;
        }
    }

    if ($c === '"' || $c === "'" || $c === '`') {
        $inString = true;
        $stringChar = $c;
        continue;
    }

    if ($c === '{' || $c === '(' || $c === '[') {
        $stack[] = ['char' => $c, 'line' => $line];
    } elseif ($c === '}' || $c === ')' || $c === ']') {
        if (empty($stack)) {
            echo "SYNTAX ERROR: Unexpected $c at line $line\n";
            exit(1);
        }
        $last = array_pop($stack);
        $expected = match ($last['char']) {
            '{' => '}',
            '(' => ')',
            '[' => ']',
        };
        if ($c !== $expected) {
            echo "SYNTAX ERROR: Expected {$expected} (opened at line {$last['line']}), got $c at line $line\n";
            exit(1);
        }
    }
}

if (!empty($stack)) {
    $last = end($stack);
    echo "SYNTAX ERROR: Unclosed {$last['char']} opened at line {$last['line']}\n";
    exit(1);
}

echo "SUCCESS: public/assets/js/ajax.js has PERFECTLY BALANCED SYNTAX!\n";
