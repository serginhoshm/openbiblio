<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Catalog;

final class SearchTerms
{
    /**
     * @return list<string>
     */
    public static function fromInput(string $input): array
    {
        $normalized = preg_replace('/\s+/', ' ', trim($input));
        if (!is_string($normalized) || $normalized === '') {
            return [];
        }

        $terms = [];
        $term = '';
        $quoted = false;
        $escaped = false;

        for ($index = 0, $length = strlen($normalized); $index < $length; $index++) {
            $character = $normalized[$index];
            if ($quoted) {
                if ($escaped) {
                    $term .= $character;
                    $escaped = false;
                } elseif ($character === '\\') {
                    $escaped = true;
                } elseif ($character === '"') {
                    $quoted = false;
                } else {
                    $term .= $character;
                }
            } elseif ($character === '"') {
                $quoted = true;
            } elseif ($character === ' ') {
                if ($term !== '') {
                    $terms[] = strtolower($term);
                    $term = '';
                }
            } else {
                $term .= $character;
            }
        }

        if ($term !== '') {
            $terms[] = strtolower($term);
        }

        $terms = array_values(array_unique($terms));
        usort($terms, static fn (string $left, string $right): int => strlen($right) <=> strlen($left));

        return $terms;
    }
}
