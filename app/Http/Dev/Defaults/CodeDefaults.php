<?php

declare(strict_types=1);

namespace App\Http\Dev\Defaults;

trait CodeDefaults
{
    private static function codeContent(string $variant): array
    {
        return match ($variant) {
            default => [
                'tagline' => './install.sh',
                'title' => 'ÉCRIVEZ DU CODE.',
                'subtitle' => 'LIVREZ PLUS VITE.',
                'text' => 'Construisez des applications modernes avec du code propre et réutilisable. Notre SDK fournit des utilitaires puissants pour plusieurs langages.',
                'buttons' => [
                    ['label' => 'Commencer', 'href' => '#', 'style' => 'primary'],
                ],
                'items' => self::codeexample1Snippets(),
            ],
        };
    }

    /**
     * @return list<array<string, string>>
     */
    private static function codeexample1Snippets(): array
    {
        return [
            [
                'label' => 'Javascript',
                'icon' => 'javascript',
                'title' => 'utils.js',
                'text' => <<<'JS'
function fibonacci(n) {
  if (n <= 1) return n;
  return fibonacci(n - 1) + fibonacci(n - 2);
}

function debounce(func, delay) {
  let timeoutId;
  return (...args) => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => func(...args), delay);
  };
}
JS,
            ],
            [
                'label' => 'Python',
                'icon' => 'python',
                'title' => 'utils.py',
                'text' => <<<'PY'
def fibonacci(n):
    if n <= 1:
        return n
    return fibonacci(n - 1) + fibonacci(n - 2)

def debounce(func, delay):
    import threading
    timer = None
    def wrapper(*args, **kwargs):
        nonlocal timer
        if timer:
            timer.cancel()
        timer = threading.Timer(delay, func, args, kwargs)
        timer.start()
    return wrapper
PY,
            ],
            [
                'label' => 'Go',
                'icon' => 'go',
                'title' => 'utils.go',
                'text' => <<<'GO'
package utils

func Fibonacci(n int) int {
    if n <= 1 {
        return n
    }
    return Fibonacci(n-1) + Fibonacci(n-2)
}

func Filter[T any](slice []T, predicate func(T) bool) []T {
    result := make([]T, 0)
    for _, item := range slice {
        if predicate(item) {
            result = append(result, item)
        }
    }
    return result
}
GO,
            ],
            [
                'label' => 'Ruby',
                'icon' => 'ruby',
                'title' => 'utils.rb',
                'text' => <<<'RB'
def fibonacci(n)
  return n if n <= 1
  fibonacci(n - 1) + fibonacci(n - 2)
end

def debounce(delay, &block)
  @timer&.cancel
  @timer = Thread.new do
    sleep(delay)
    block.call
  end
end
RB,
            ],
        ];
    }
}
