<?php

namespace Workbench\App\Filament\Pages;

use BackedEnum;
use Camya\Filament\Forms\Components\TitleWithSlugInput;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Workbench\App\Models\DemoPost;

/**
 * @property-read Schema $form
 */
class TitleWithSlugPlayground extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $title = 'Title With Slug Playground';

    protected static ?string $slug = 'title-with-slug-playground';

    protected string $view = 'workbench::filament.pages.title-with-slug-playground';

    public array $data = [];

    public ?array $savedData = null;

    public ?DemoPost $record = null;

    public string $mode = 'edit';

    public function mount(): void
    {
        $this->resetDemo();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TitleWithSlugInput::make(
                    urlPath: '/blog/',
                    titleLabel: 'Title',
                    titlePlaceholder: 'Write a title...',
                    slugLabel: 'Permalink:',
                    urlVisitLinkVisible: $this->isEditMode(),
                )->columnSpanFull(),
            ])
            ->statePath('data')
            ->model($this->record)
            ->operation($this->mode);
    }

    public function save(): void
    {
        $this->savedData = $this->form->getState();

        if ($this->record) {
            $this->record->fill($this->savedData);
        }
    }

    public function setMode(string $mode): void
    {
        if (! in_array($mode, ['create', 'edit'], true)) {
            return;
        }

        $this->mode = $mode;
        $this->resetDemo();
    }

    public function resetDemo(): void
    {
        $this->savedData = null;

        if (! $this->isEditMode()) {
            $this->record = null;

            $this->form->fill([
                'title' => '',
                'slug' => '',
            ]);

            return;
        }

        $this->record = new DemoPost([
            'title' => 'Workbench Demo Post',
            'slug' => 'workbench-demo-post',
        ]);

        $this->form->fill($this->record->attributesToArray());
    }

    protected function isEditMode(): bool
    {
        return $this->mode === 'edit';
    }
}
