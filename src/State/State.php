<?php declare(strict_types=1);

namespace Acme\State;


class State
{
    protected $state = [
        'xdebug' => [
            'language' => '',
            'idekey' => '',
            'appid' => '',
            'engine_version' => '',
            'fileuri' => '',
            'protocol_version' => '',
        ],
        'files' => [],
        'errors' => ["demo error1", "demo error2"],
        'features' => [
            'asdsd'
        ],
    ];

    public function getFullState(): array
    {
        return $this->state;
    }

    public function generateJsState(string $path)
    {
        file_put_contents($path, implode(PHP_EOL, [
            '/*',
            'This is generated file, do not edit!',
            'JS object notation parser is faster than parsing actual JS object.',
            'Pretty view:',
            json_encode($this->state, JSON_PRETTY_PRINT),
            '*/',
            'export default JSON.parse(\'' . json_encode($this->state) . '\');',
        ]));
    }

    public function getState(string $path): array
    {
        return $this->state;
    }

    public function setState(string $path, $value)
    {
        $keys = explode('.', $path);

        $ref = &$this->state;
        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($ref[$key])) {
                $ref[$key] = [];
            }
            $ref = &$ref[$key];

        }

        $ref = $value;
    }
}
