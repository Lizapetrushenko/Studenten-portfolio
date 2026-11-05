<?php

namespace Tests\Feature\Views;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BladeTemplateCompilationTest extends TestCase
{
    public function test_every_blade_template_compiles(): void
    {
        $viewRoot = resource_path('views');
        $templates = File::allFiles($viewRoot);
        $bladeTemplates = array_values(array_filter(
            $templates,
            fn (\SplFileInfo $file): bool => str_ends_with($file->getFilename(), '.blade.php'),
        ));

        $this->assertNotEmpty($bladeTemplates, 'The project should contain Blade templates.');

        foreach ($bladeTemplates as $template) {
            $compiled = Blade::compileString(File::get($template->getPathname()));
            $this->assertNotEmpty($compiled, 'Blade template did not compile: '.$template->getPathname());
        }
    }
}
