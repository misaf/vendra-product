<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

const LANGUAGE_DIRECTORY = __DIR__ . '/../../resources/lang';

it('has at least two available locales', function (): void {
    expect(count(availableLocales()))->toBeGreaterThan(1);
});

it('keeps translation files and keys in sync across locales', function (): void {
    $locales = availableLocales();

    foreach ($locales as $sourceLocale) {
        $sourceFiles = translationFilesForLocale(LANGUAGE_DIRECTORY, $sourceLocale);

        foreach ($locales as $targetLocale) {
            if ($sourceLocale === $targetLocale) {
                continue;
            }

            foreach ($sourceFiles as $relativeFilePath) {
                $sourceFilePath = LANGUAGE_DIRECTORY
                    . DIRECTORY_SEPARATOR
                    . $sourceLocale
                    . DIRECTORY_SEPARATOR
                    . $relativeFilePath;

                $targetFilePath = LANGUAGE_DIRECTORY
                    . DIRECTORY_SEPARATOR
                    . $targetLocale
                    . DIRECTORY_SEPARATOR
                    . $relativeFilePath;

                expect(File::exists($targetFilePath))->toBeTrue(
                    sprintf(
                        'Locale [%s] is missing file [%s] that exists in [%s].',
                        $targetLocale,
                        $relativeFilePath,
                        $sourceLocale,
                    ),
                );

                $sourceKeys = collect(array_keys(Arr::dot(require $sourceFilePath)))
                    ->map(static fn(string $key): string => (string) $key)
                    ->sort()
                    ->values()
                    ->all();

                $targetKeys = collect(array_keys(Arr::dot(require $targetFilePath)))
                    ->map(static fn(string $key): string => (string) $key)
                    ->sort()
                    ->values()
                    ->all();

                $missingKeys = array_values(array_diff($sourceKeys, $targetKeys));

                expect($missingKeys)->toBeEmpty(
                    sprintf(
                        'Locale [%s] is missing keys from [%s] in file [%s]: %s',
                        $targetLocale,
                        $sourceLocale,
                        $relativeFilePath,
                        implode(', ', $missingKeys),
                    ),
                );
            }
        }
    }
});

it('keeps translation file keys sorted', function (): void {
    $locales = availableLocales();

    foreach ($locales as $locale) {
        foreach (translationFilesForLocale(LANGUAGE_DIRECTORY, $locale) as $relativeFilePath) {
            $filePath = LANGUAGE_DIRECTORY
                . DIRECTORY_SEPARATOR
                . $locale
                . DIRECTORY_SEPARATOR
                . $relativeFilePath;

            assertSortedTranslationKeys(
                require $filePath,
                sprintf('Locale [%s] file [%s]', $locale, $relativeFilePath),
            );
        }
    }
});

/**
 * @return list<string>
 */
function availableLocales(): array
{
    return collect(File::directories(LANGUAGE_DIRECTORY))
        ->map(static fn(string $directory): string => basename($directory))
        ->sort()
        ->values()
        ->all();
}

/**
 * @return list<string>
 */
function translationFilesForLocale(string $languageDirectory, string $locale): array
{
    $localeDirectory = $languageDirectory . DIRECTORY_SEPARATOR . $locale;

    return collect(File::allFiles($localeDirectory))
        ->filter(static fn(SplFileInfo $file): bool => 'php' === $file->getExtension())
        ->map(static fn(SplFileInfo $file): string => $file->getRelativePathname())
        ->sort()
        ->values()
        ->all();
}

function assertSortedTranslationKeys(array $translations, string $context): void
{
    if (array_is_list($translations)) {
        return;
    }

    $keys = array_map(static fn(string $key): string => (string) $key, array_keys($translations));
    $sortedKeys = $keys;

    usort($sortedKeys, static fn(string $left, string $right): int => strcmp($left, $right));

    expect($keys)->toBe(
        $sortedKeys,
        sprintf(
            '%s has unsorted keys. Expected order: [%s]. Actual order: [%s].',
            $context,
            implode(', ', $sortedKeys),
            implode(', ', $keys),
        ),
    );

    foreach ($translations as $key => $value) {
        if (is_array($value)) {
            assertSortedTranslationKeys($value, sprintf('%s.%s', $context, (string) $key));
        }
    }
}
