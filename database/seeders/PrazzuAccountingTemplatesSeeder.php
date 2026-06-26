<?php

namespace Database\Seeders;

use App\Models\PrazzuTemplate;
use App\Support\PrazzuAccountingTemplateCatalog;
use Illuminate\Database\Seeder;

class PrazzuAccountingTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PrazzuAccountingTemplateCatalog::templates() as $template) {
            PrazzuTemplate::query()->updateOrCreate(
                [
                    'module' => $template['module'],
                    'name' => $template['name'],
                ],
                [
                    'description' => $template['description'],
                    'payload' => $template['payload'],
                    'active' => true,
                ]
            );
        }
    }
}
