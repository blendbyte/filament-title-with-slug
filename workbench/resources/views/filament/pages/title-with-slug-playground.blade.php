<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex flex-wrap gap-3">
            <x-filament::button
                type="button"
                :color="$mode === 'create' ? 'primary' : 'gray'"
                wire:click="setMode('create')"
            >
                Create
            </x-filament::button>

            <x-filament::button
                type="button"
                :color="$mode === 'edit' ? 'primary' : 'gray'"
                wire:click="setMode('edit')"
            >
                Edit
            </x-filament::button>

            <x-filament::button
                type="button"
                color="gray"
                wire:click="resetDemo"
            >
                Reset Demo
            </x-filament::button>
        </div>

        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <x-filament::button type="submit">
                Dump State
            </x-filament::button>
        </form>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
                <h2 class="text-lg font-semibold text-stone-950">Current Form State</h2>
                <pre class="mt-4 overflow-x-auto rounded-xl bg-stone-950 p-4 text-xs text-stone-100">{{ json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </section>

            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
                <h2 class="text-lg font-semibold text-stone-950">Last Submitted State</h2>
                <pre class="mt-4 overflow-x-auto rounded-xl bg-stone-950 p-4 text-xs text-stone-100">{{ json_encode($savedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </section>
        </div>
    </div>
</x-filament-panels::page>
