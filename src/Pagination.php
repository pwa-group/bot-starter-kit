<?php

namespace App;

class Pagination
{
    const LIMIT = 5;

    public function __construct(
        private int   $currentPage = 1,
        private array $models = []
    )
    {
    }

    public function getModels(): array
    {
        return array_slice(
            $this->models,
            $this->getOffset(),
            self::LIMIT);
    }

    public function getButtons($url = ''): array|false
    {
        if ($this->getCountModels() === $this->getTotalModels()) {
            return false;
        } else {
            $buttons = [];
            if ($this->currentPage > 1) {
                $prev = $this->currentPage - 1;
                $buttons[] = ['text' => '🔙Назад', 'callback_data' => "{$url}/{$prev}"];
            }
            if ($this->currentPage < ($this->getTotalModels() / self::LIMIT)) {
                $next = $this->currentPage + 1;
                $buttons[] = ['text' => 'Вперед🔜', 'callback_data' => "{$url}/{$next}"];
            }
            return $buttons;
        }
    }

    public function getCaption(): string
    {
        if ($this->getCountModels() === $this->getTotalModels()) {
            return '';
        } else {
            $countModels = StringHelpers::plural($this->getCountModels() + $this->getOffset());
            return "\n\nПоказано {$countModels} из {$this->getTotalModels()}";
        }
    }

    private function getCountModels(): int
    {
        return count($this->getModels());
    }

    private function getTotalModels(): int
    {
        return count($this->models);
    }

    private function getOffset(): int
    {
        return ceil(($this->currentPage * self::LIMIT) - self::LIMIT);
    }
}
