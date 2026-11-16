<?php

namespace App\Console\Commands;

use App\Enums\LinkType;
use App\Enums\PublicationStatus;
use App\Enums\TechnologyType;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Technology;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportLegacyProjects extends Command
{
    protected $signature = 'portfolio:import-legacy-projects {--dry-run : Validate the data and files without writing anything}';

    protected $description = 'Import the legacy portfolio projects as drafts. Projects whose slug already exists are skipped.';

    public function handle(): int
    {
        $base = database_path('data/legacy');
        $data = json_decode(file_get_contents($base.'/projects.json'), true, 512, JSON_THROW_ON_ERROR);

        $problems = $this->validateData($data, $base);
        if ($problems !== []) {
            foreach ($problems as $problem) {
                $this->error($problem);
            }

            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->info(count($data['projects']).' projects validated. Nothing was written.');

            return self::SUCCESS;
        }

        $categories = [];
        foreach ($data['categories'] as $index => $category) {
            // Reuse a category that already exists with the same slug or the same name (any letter case).
            $categories[$category['slug']] = ProjectCategory::query()
                ->where('slug', $category['slug'])
                ->orWhereRaw('lower(name) = ?', [Str::lower($category['name'])])
                ->first()
                ?? ProjectCategory::create([
                    'slug' => $category['slug'],
                    'name' => $category['name'],
                    'sort_order' => ($index + 1) * 10,
                ]);
        }

        $types = collect($data['technologies'])->mapWithKeys(fn (array $t): array => [$t['name'] => $t['type']]);

        $created = 0;
        $skipped = 0;

        foreach ($data['projects'] as $row) {
            if (Project::withTrashed()->where('slug', $row['slug'])->exists()) {
                $skipped++;
                $this->line('Skipped (already exists): '.$row['slug']);

                continue;
            }

            DB::transaction(fn () => $this->importProject($row, $categories[$row['category']], $types, $base));
            $created++;
            $this->info('Imported draft: '.$row['slug']);
        }

        $this->newLine();
        $this->info("Done. Created {$created}, skipped {$skipped}. Every imported project is a draft.");

        return self::SUCCESS;
    }

    private function importProject(array $row, ProjectCategory $category, $types, string $base): void
    {
        $project = Project::create([
            'slug' => $row['slug'],
            'title' => $row['title'],
            'category_id' => $category->id,
            'summary' => $row['summary'],
            'problem' => $row['problem'],
            'status_label' => $row['status_label'],
            'public_note' => $row['public_note'],
            'link_note' => $row['link_note'],
            'publication_status' => PublicationStatus::Draft,
            'is_featured' => $row['is_featured'],
            'sort_order' => $row['sort_order'],
            'publish_block_reason' => $row['publish_block_reason'],
        ]);

        foreach ($row['features'] as $index => $body) {
            $project->features()->create(['body' => $body, 'sort_order' => $index + 1]);
        }

        foreach ($row['links'] as $index => $link) {
            $project->links()->create([
                'label' => $link['label'],
                'url' => $link['url'],
                'type' => LinkType::from($link['type'])->value,
                'sort_order' => $index + 1,
            ]);
        }

        foreach ($row['technologies'] as $index => $name) {
            $technology = Technology::resolve($name, TechnologyType::from($types[$name] ?? 'tool')->value);
            $project->technologies()->attach($technology->id, ['sort_order' => $index + 1]);
        }

        foreach ($row['images'] as $index => $image) {
            $record = $project->images()->create([
                'alt' => $image['alt'],
                'is_placeholder' => $image['is_placeholder'],
                'sort_order' => $index + 1,
            ]);
            $record->addMedia($base.'/images/'.$image['file'])->preservingOriginal()->toMediaCollection('image');
        }

        foreach ($row['downloads'] as $download) {
            $project->addMedia($base.'/downloads/'.$download['file'])->preservingOriginal()->toMediaCollection('downloads');
        }
    }

    /** @return list<string> */
    private function validateData(array $data, string $base): array
    {
        $problems = [];
        $categorySlugs = array_column($data['categories'], 'slug');
        $techTypes = collect($data['technologies'])->pluck('type', 'name');
        $slugs = [];

        foreach ($data['projects'] as $row) {
            $slug = $row['slug'];
            $slugs[$slug] = ($slugs[$slug] ?? 0) + 1;

            if (! in_array($row['category'], $categorySlugs, true)) {
                $problems[] = "{$slug}: unknown category {$row['category']}";
            }
            foreach ($row['technologies'] as $name) {
                if (! $techTypes->has($name)) {
                    $problems[] = "{$slug}: technology not classified: {$name}";
                }
            }
            foreach ($row['links'] as $link) {
                if (LinkType::tryFrom($link['type']) === null) {
                    $problems[] = "{$slug}: unknown link type {$link['type']}";
                }
            }
            foreach ($row['images'] as $image) {
                if (! is_file($base.'/images/'.$image['file'])) {
                    $problems[] = "{$slug}: missing image file {$image['file']}";
                }
                if (trim($image['alt']) === '') {
                    $problems[] = "{$slug}: image {$image['file']} has no alt text";
                }
            }
            foreach ($row['downloads'] as $download) {
                if (! is_file($base.'/downloads/'.$download['file'])) {
                    $problems[] = "{$slug}: missing download file {$download['file']}";
                }
            }
        }

        foreach ($slugs as $slug => $count) {
            if ($count > 1) {
                $problems[] = "Duplicate slug: {$slug}";
            }
        }

        return $problems;
    }
}
