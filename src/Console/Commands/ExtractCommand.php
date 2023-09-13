<?php

declare(strict_types=1);

namespace AMgrade\LaravelJsTranslations\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

use function array_flip;
use function array_shift;
use function explode;
use function file_put_contents;
use function implode;
use function is_dir;
use function json_decode;
use function json_encode;
use function mb_strlen;
use function mb_substr;
use function pathinfo;
use function str_replace;

use const DIRECTORY_SEPARATOR;
use const JSON_THROW_ON_ERROR;
use const null;
use const PATHINFO_EXTENSION;
use const true;

class ExtractCommand extends Command
{
    protected $signature = 'js-translations:extract {--B|bundle=default} {--D|destination=} {--N|namespace=}';

    protected $description = 'Extract translations into JS';

    protected array $config = [];

    protected array $extensions = ['php', 'json'];

    protected ?string $namespace;

    protected array $exclude = [];

    protected string $destination;

    protected array $translations = [];

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \InvalidArgumentException
     * @throws \JsonException
     */
    public function handle(): int
    {
        $this->setConfig()
            ->setNamespace()
            ->setExclude()
            ->setDestination();

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($this->getTranslationFiles() as $file) {
            $relativePathname = $file->getRelativePathname();
            $pathnamePieces = explode(DIRECTORY_SEPARATOR, $relativePathname);
            $locale = array_shift($pathnamePieces);
            $pathname = implode(DIRECTORY_SEPARATOR, $pathnamePieces);

            if (
                $this->shouldSkipLocale($locale) ||
                $this->shouldSkipFile($relativePathname, $pathname)
            ) {
                continue;
            }

            $extension = $file->getExtension();

            $pathname = mb_substr(
                $pathname,
                0,
                mb_strlen($pathname) - mb_strlen($extension) - 1,
            );

            $pathname = str_replace(DIRECTORY_SEPARATOR, '.', $pathname);

            $key = null !== $this->namespace
                ? "{$locale}.{$this->namespace}.{$pathname}"
                : "{$locale}.{$pathname}";

            Arr::set($this->translations, $key, $this->getFileContent($file, $extension));
        }

        $result = file_put_contents(
            $this->destination,
            $this->getContent($this->destination),
        );

        if (!$result) {
            $this->warn('Something went wrong, maybe you don\'t have permissions to write into destination');

            return self::FAILURE;
        }

        $this->info("Translations have been successfully extracted into '{$this->destination}'");

        return self::SUCCESS;
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function setConfig(): ExtractCommand
    {
        /** @var \Illuminate\Contracts\Config\Repository $config */
        $config = $this->laravel->make('config');

        $bundle = $this->option('bundle');

        $this->config = $config->get("js-translations.bundles.{$bundle}", []);

        return $this;
    }

    protected function setNamespace(): ExtractCommand
    {
        $this->namespace = $this->option('namespace') ?? $config['namespace'] ?? null;

        return $this;
    }

    protected function setExclude(): ExtractCommand
    {
        $this->exclude = [
            'locales' => array_flip($config['exclude']['locales'] ?? []),
            'files' => array_flip($config['exclude']['files'] ?? []),
            'extensions' => array_flip($config['exclude']['extensions'] ?? []),
        ];

        return $this;
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function setDestination(): ExtractCommand
    {
        $destination = $this->option('destination') ?? $this->config['destination'] ?? null;

        if (!$destination) {
            throw new InvalidArgumentException('Please, provide a destination');
        }

        $this->destination = $destination;

        return $this;
    }

    protected function getTranslationFiles(): Finder
    {
        $extensions = $this->extensions;

        foreach ($extensions as $key => $extension) {
            if (isset($this->exclude['extensions'][$extension])) {
                unset($extensions[$key]);
            }
        }

        $extensions = implode('|', $extensions);

        return Finder::create()
            ->files()
            ->name("/\.({$extensions})$/")
            ->ignoreDotFiles(true)
            ->in($this->getPath());
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function getPath(): string
    {
        $path = $this->config['path'] ?? null;

        if (null === $path || !is_dir($path)) {
            throw new InvalidArgumentException("'{$path}' is not correct path");
        }

        return $path;
    }

    protected function shouldSkipLocale(string $locale): bool
    {
        return isset($this->exclude['locales'][$locale]);
    }

    protected function shouldSkipFile(
        string $relativePathname,
        string $pathname
    ): bool {
        return isset($this->exclude['files'][$relativePathname]) ||
            isset($this->exclude['files'][$pathname]);
    }

    /**
     * @throws \JsonException
     */
    protected function getFileContent(SplFileInfo $file, string $extension): array
    {
        $content = [];

        if ($extension === 'php') {
            $content = require $file->getRealPath();
        } elseif ($extension === 'json') {
            $content = json_decode(
                $file->getContents(),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        }

        return $content;
    }

    /**
     * @throws \JsonException
     */
    protected function getContent(string $destination): string
    {
        $content = json_encode($this->translations, JSON_THROW_ON_ERROR);

        if (pathinfo($destination, PATHINFO_EXTENSION) === 'json') {
            return $content;
        }

        return "export default {$content};";
    }
}
