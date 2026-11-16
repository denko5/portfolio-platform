<?php

namespace App\Console\Commands;

use App\Enums\PublicationStatus;
use App\Enums\TechnologyType;
use App\Models\Certification;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Skill;
use App\Models\SkillCategory;
use App\Models\SocialLink;
use App\Models\Technology;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportLegacyContent extends Command
{
    protected $signature = 'portfolio:import-legacy-content {--dry-run : Validate the data without writing anything}';

    protected $description = 'Import the legacy services, skills, experience, education, certifications, social links and site settings. Existing rows are left untouched.';

    public function handle(): int
    {
        $data = json_decode(file_get_contents(database_path('data/legacy/content.json')), true, 512, JSON_THROW_ON_ERROR);

        $problems = $this->validateData($data);
        if ($problems !== []) {
            foreach ($problems as $problem) {
                $this->error($problem);
            }

            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->info('Content data validated. Nothing was written.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($data): void {
            $this->importSettings($data['settings']);
            $this->importSocialLinks($data['social_links']);
            $this->importServices($data['services'], $data['technology_types']);
            $this->importSkills($data['skill_categories']);
            $this->importExperiences($data['experiences']);
            $this->importEducation($data['education']);
            $this->importCertifications($data['certifications']);
            $this->markFeaturedTechnologies($data['featured_technologies'], $data['technology_types']);
        });

        $this->newLine();
        $this->info('Done. Imported content is saved as drafts. Existing rows were left untouched.');
        $this->warn('Contact email, phone and WhatsApp are NOT imported. Enter them in the admin settings.');

        return self::SUCCESS;
    }

    /** Fill only the settings that are still empty, so admin edits are never overwritten. */
    private function importSettings(array $settings): void
    {
        $record = SiteSetting::current();

        foreach ($settings as $field => $value) {
            $current = $record->{$field};
            if ($current === null || $current === '') {
                $record->{$field} = $value;
            }
        }

        $record->save();
        $this->line('Settings: filled empty fields only.');
    }

    private function importSocialLinks(array $links): void
    {
        foreach ($links as $link) {
            SocialLink::firstOrCreate(
                ['platform' => $link['platform']],
                ['label' => $link['label'], 'url' => $link['url'], 'is_visible' => true, 'sort_order' => $link['sort_order']],
            );
        }
        $this->line('Social links: '.count($links).' checked.');
    }

    private function importServices(array $services, array $types): void
    {
        foreach ($services as $row) {
            if (Service::where('slug', $row['slug'])->exists()) {
                continue;
            }

            $service = Service::create([
                'slug' => $row['slug'],
                'title' => $row['title'],
                'summary' => $row['summary'],
                'icon_key' => $row['icon_key'],
                'sort_order' => $row['sort_order'],
                'publication_status' => PublicationStatus::Draft,
            ]);

            foreach ($row['items'] as $index => $body) {
                $service->items()->create(['body' => $body, 'sort_order' => $index + 1]);
            }

            foreach ($row['technologies'] as $name) {
                $service->technologies()->syncWithoutDetaching([
                    Technology::resolve($name, TechnologyType::from($types[$name] ?? 'tool')->value)->id,
                ]);
            }
        }
        $this->line('Services: '.count($services).' checked.');
    }

    private function importSkills(array $categories): void
    {
        foreach ($categories as $row) {
            $category = SkillCategory::firstOrCreate(
                ['slug' => $row['slug']],
                ['name' => $row['name'], 'sort_order' => $row['sort_order']],
            );

            foreach ($row['skills'] as $index => $name) {
                Skill::firstOrCreate(
                    ['category_id' => $category->id, 'name' => $name],
                    ['is_visible' => true, 'sort_order' => ($index + 1) * 10],
                );
            }
        }
        $this->line('Skills: '.count($categories).' categories checked.');
    }

    private function importExperiences(array $experiences): void
    {
        foreach ($experiences as $row) {
            $exists = Experience::where('organisation', $row['organisation'])
                ->where('role_title', $row['role_title'])
                ->whereDate('start_date', $row['start_date'])
                ->exists();
            if ($exists) {
                continue;
            }

            $experience = Experience::create([
                'organisation' => $row['organisation'],
                'role_title' => $row['role_title'],
                'location' => $row['location'],
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date'],
                'is_current' => $row['is_current'],
                'sort_order' => $row['sort_order'],
                'publication_status' => PublicationStatus::Draft,
            ]);

            foreach ($row['highlights'] as $index => $body) {
                $experience->highlights()->create(['body' => $body, 'sort_order' => $index + 1]);
            }
        }
        $this->line('Experience: '.count($experiences).' checked.');
    }

    private function importEducation(array $entries): void
    {
        foreach ($entries as $row) {
            Education::firstOrCreate(
                ['institution' => $row['institution'], 'qualification' => $row['qualification']],
                ['start_year' => $row['start_year'], 'end_year' => $row['end_year'], 'sort_order' => $row['sort_order'], 'publication_status' => PublicationStatus::Draft],
            );
        }
        $this->line('Education: '.count($entries).' checked.');
    }

    private function importCertifications(array $entries): void
    {
        foreach ($entries as $row) {
            if (Certification::where('title', $row['title'])->whereDate('issued_on', $row['issued_on'])->exists()) {
                continue;
            }

            Certification::create([
                'title' => $row['title'],
                'issuer' => $row['issuer'],
                'issued_on' => $row['issued_on'],
                'note' => $row['note'],
                'sort_order' => $row['sort_order'],
                'publication_status' => PublicationStatus::Draft,
            ]);
        }
        $this->line('Certifications: '.count($entries).' checked.');
    }

    private function markFeaturedTechnologies(array $names, array $types): void
    {
        foreach ($names as $name) {
            Technology::resolve($name, TechnologyType::from($types[$name] ?? 'tool')->value)->update(['is_featured' => true]);
        }
        $this->line('Featured technologies (marquee): '.count($names).' marked.');
    }

    /** @return list<string> */
    private function validateData(array $data): array
    {
        $problems = [];

        foreach ($data['services'] as $service) {
            foreach ($service['technologies'] as $name) {
                if (! isset($data['technology_types'][$name])) {
                    $problems[] = "{$service['slug']}: technology not classified: {$name}";
                }
            }
        }
        foreach ($data['featured_technologies'] as $name) {
            if (! isset($data['technology_types'][$name])) {
                $problems[] = "Featured technology not classified: {$name}";
            }
        }
        foreach ($data['technology_types'] as $name => $type) {
            if (TechnologyType::tryFrom($type) === null) {
                $problems[] = "Unknown technology type for {$name}: {$type}";
            }
        }
        if (str_contains(strtolower((string) ($data['settings']['bio_markdown'] ?? '')), 'lexops')) {
            $problems[] = 'Bio mentions the product name; use the neutral project name.';
        }

        return $problems;
    }
}
