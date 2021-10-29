<?php

namespace App;

/**
 * Class Route
 * @package DevCoder
 */
final class Route
{
    /**
     * @var string
     */
    private string $path;

    /**
     * @var array<string>
     */
    private array $parameters = [];

    /**
     * @var array<string>
     */
    private array $vars = [];

    /**
     * Route constructor.
     * @param string $path
     * @param array $parameters
     *    $parameters = [
     *      0 => (string) Controller name : HomeController::class.
     *      1 => (string|null) Method name or null if invoke method
     *    ]
     */
    public function __construct(string $path, array $parameters)
    {
        $this->path = $path;
        $this->parameters = $parameters;
    }

    public function match(string $path): bool
    {
        $regex = $this->getPath();
        foreach ($this->getVarsNames() as $variable) {
            $varName = trim($variable, '{\}');
            $regex = str_replace($variable, '(?P<' . $varName . '>[^/]++)', $regex);
        }

        if (preg_match('#^' . $regex . '$#sD', self::trimPath($path), $matches)) {
            $values = array_filter($matches, static function ($key) {
                return is_string($key);
            }, ARRAY_FILTER_USE_KEY);
            foreach ($values as $key => $value) {
                $this->vars[$key] = $value;
            }
            return true;
        }
        return false;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getVarsNames(): array
    {
        preg_match_all('/{[^}]*}/', $this->path, $matches);
        return reset($matches) ?? [];
    }

    public function hasVars(): bool
    {
        return $this->getVarsNames() !== [];
    }

    public function getVars(): array
    {
        return $this->vars;
    }

    public static function trimPath(string $path): string
    {
        return rtrim(ltrim(trim($path), '/'), '/');
    }
}
