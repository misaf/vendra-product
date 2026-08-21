<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Console\Commands;

use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Misaf\VendraProduct\Models\Product;

#[Signature('vendra-product:resync-descriptions {--dry-run : Report changes without updating products}')]
#[Description('Convert legacy product description HTML into Tiptap JSON documents')]
final class ResyncProductDescriptionsCommand extends Command
{
    public function handle(): int
    {
        $convertedProducts = 0;
        $convertedTranslations = 0;

        Product::query()
            ->withoutGlobalScopes()
            ->select(['id', 'description'])
            ->chunkById(100, function (Collection $products) use (&$convertedProducts, &$convertedTranslations): void {
                foreach ($products as $product) {
                    $originalDescription = $product->getTranslations('description');
                    $description = $this->resyncTranslations($originalDescription);

                    if ($description === $originalDescription) {
                        continue;
                    }

                    $convertedProducts++;
                    $convertedTranslations += count(array_filter(
                        $description,
                        fn(mixed $translation, int|string $locale): bool => $translation !== ($originalDescription[$locale] ?? null),
                        ARRAY_FILTER_USE_BOTH,
                    ));

                    if ($this->option('dry-run')) {
                        continue;
                    }

                    DB::table($product->getTable())
                        ->where($product->getKeyName(), $product->getKey())
                        ->update([
                            'description' => json_encode($description, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                        ]);
                }
            });

        $verb = $this->option('dry-run') ? 'Would convert' : 'Converted';

        $this->components->info("{$verb} {$convertedTranslations} description translations across {$convertedProducts} products.");

        return self::SUCCESS;
    }

    /**
     * @param  array<mixed>  $translations
     * @return array<mixed>
     */
    private function resyncTranslations(array $translations): array
    {
        foreach ($translations as $locale => $translation) {
            if ( ! is_string($translation) || blank($translation)) {
                continue;
            }

            $translations[$locale] = RichContentRenderer::make()
                ->getEditor()
                ->setContent($translation)
                ->getDocument();
        }

        return $translations;
    }
}
