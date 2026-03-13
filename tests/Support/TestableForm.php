<?php

namespace Camya\Filament\Tests\Support;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;
use Livewire\Component;

class TestableForm extends Component implements HasForms
{
    use InteractsWithForms;

    public static array $formSchema = [];

    public array $data = [];

    public $record;

    protected $listeners = ['$refresh'];

    public function mount(): void
    {
        $this->resetErrorBag();
        $this->form->fill($this->record?->attributesToArray() ?? []);
    }

    public function getErrorBag(): MessageBag
    {
        $bag = parent::getErrorBag();

        if ($bag instanceof MessageBag) {
            return $bag;
        }

        $bag = new MessageBag;
        $this->setErrorBag($bag);

        return $bag;
    }

    public function render()
    {
        return view('filament-title-with-slug::tests.support.testable-form');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(static::$formSchema)
            ->model($this->record instanceof Model ? $this->record : null)
            ->statePath('data');
    }
}
