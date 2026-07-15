<?php

declare(strict_types=1);

/**
 * Regroupe Style, VariantRenderer et SectionHandler par type sous src/Section/{Type}/.
 *
 * Usage: php scripts/migrate-section-modules.php
 */

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);
$sectionDir = $root . '/src/Section';

/** @var array<string, string> type => PascalCase folder */
const TYPE_MAP = [
    'hero' => 'Hero',
    'features' => 'Feature',
    'integrations' => 'Integration',
    'pricing' => 'Pricing',
    'rate-card' => 'RateCard',
    'contact' => 'Contact',
    'testimonials' => 'Testimonial',
    'gallery' => 'Gallery',
    'blog' => 'Blog',
    'changelog' => 'Changelog',
    'process' => 'Process',
    'list' => 'List',
    'industry' => 'Industry',
    'download' => 'Download',
    'team' => 'Team',
    'projects' => 'Projects',
    'timeline' => 'Timeline',
    'logos' => 'Logos',
    'services' => 'Services',
    'compare' => 'Compare',
    'cta' => 'Cta',
    'awards' => 'Awards',
    'community' => 'Community',
    'stats' => 'Stats',
    'careers' => 'Careers',
    'faq' => 'Faq',
    'code' => 'Code',
    'compliance' => 'Compliance',
    'case-study' => 'CaseStudy',
    'demo' => 'Demo',
    'experience' => 'Experience',
    'waitlist' => 'Waitlist',
];

foreach (TYPE_MAP as $type => $folder) {
    $handlerFile = $sectionDir . '/' . $folder . 'SectionHandler.php';
    if (!is_file($handlerFile)) {
        if ($type === 'features') {
            $handlerFile = $sectionDir . '/FeaturesSectionHandler.php';
        } elseif ($type === 'integrations') {
            $handlerFile = $sectionDir . '/IntegrationsSectionHandler.php';
        } elseif ($type === 'testimonials') {
            $handlerFile = $sectionDir . '/TestimonialsSectionHandler.php';
        }
    }

    $styleClass = match ($type) {
        'features' => 'FeatureStyle',
        'integrations' => 'IntegrationStyle',
        'testimonials' => 'TestimonialStyle',
        'case-study' => 'CaseStudyStyle',
        default => $folder . 'Style',
    };
    $rendererClass = match ($type) {
        'features' => 'FeatureVariantRenderer',
        'integrations' => 'IntegrationVariantRenderer',
        'testimonials' => 'TestimonialVariantRenderer',
        'case-study' => 'CaseStudyVariantRenderer',
        default => $folder . 'VariantRenderer',
    };

    $targetDir = $sectionDir . '/' . $folder;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0775, true);
    }

    migrateFile(
        $root . '/src/' . $styleClass . '.php',
        $targetDir . '/' . $styleClass . '.php',
        'Capsule\\Section\\' . $folder,
    );
    migrateFile(
        $root . '/src/' . $rendererClass . '.php',
        $targetDir . '/' . $rendererClass . '.php',
        'Capsule\\Section\\' . $folder,
    );

    $handlerName = basename($handlerFile);
    if (is_file($handlerFile)) {
        migrateFile(
            $handlerFile,
            $targetDir . '/' . $handlerName,
            'Capsule\\Section\\' . $folder,
            [$styleClass, $rendererClass],
        );
        if ($handlerFile !== $targetDir . '/' . $handlerName) {
            writeStub($handlerFile, 'Capsule\\Section\\' . $folder . '\\' . pathinfo($handlerName, PATHINFO_FILENAME));
        }
    }

    writeStub($root . '/src/' . $styleClass . '.php', 'Capsule\\Section\\' . $folder . '\\' . $styleClass);
    writeStub($root . '/src/' . $rendererClass . '.php', 'Capsule\\Section\\' . $folder . '\\' . $rendererClass);
}

fwrite(STDOUT, "Migration modules section terminée.\n");

function migrateFile(string $source, string $target, string $namespace, array $localUses = []): void
{
    if (!is_file($source)) {
        return;
    }
    if (is_file($target) && realpath($source) === realpath($target)) {
        return;
    }

    $code = file_get_contents($source);
    if ($code === false) {
        return;
    }

    $code = preg_replace('/^namespace Capsule(?:\\\\Section)?;/m', 'namespace ' . $namespace . ';', $code, 1) ?? $code;
    foreach ($localUses as $class) {
        $code = str_replace('use Capsule\\' . $class . ';', 'use ' . $namespace . '\\' . $class . ';', $code);
    }

    file_put_contents($target, $code);
}

function writeStub(string $path, string $targetClass): void
{
    $short = basename($path, '.php');
    $stub = <<<PHP
<?php

declare(strict_types=1);

namespace Capsule;

/** @deprecated Utiliser {$targetClass} */
class {$short} extends \\{$targetClass}
{
}

PHP;
    file_put_contents($path, $stub);
}
