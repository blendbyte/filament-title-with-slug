<?php

use Blendbyte\FilamentTitleWithSlug\SlugInput;
use Blendbyte\FilamentTitleWithSlug\Tests\Support\Record;
use Blendbyte\FilamentTitleWithSlug\Tests\Support\TestableForm;
use Blendbyte\FilamentTitleWithSlug\TitleWithSlugInput;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Livewire\Livewire;

// ---------------------------------------------------------------------------
// Rendering & display
// ---------------------------------------------------------------------------

describe('rendering', function () {
    it('renders without errors', function () {
        TestableForm::$formSchema = [TitleWithSlugInput::make()];

        Livewire::test(TestableForm::class)->assertOk();
    });

    it('renders the permalink row instead of the embedded TextInput fallback', function () {
        TestableForm::$formSchema = [TitleWithSlugInput::make()];

        // Regression: SlugInput must declare the $view PROPERTY. TextInput implements
        // HasEmbeddedView, so ViewComponent::toHtml() only reaches the Blade view when
        // hasView() (i.e. isset($this->view)) is true. A getView() override alone makes
        // Filament fall back to TextInput::toEmbeddedHtml() — silently, with no error —
        // and the whole permalink row disappears behind a plain text input.
        expect(SlugInput::make('slug')->hasView())->toBeTrue();

        Livewire::test(TestableForm::class)
            ->assertSeeHtml('fts-slug-row')
            ->assertSeeHtml('fts-slug-edit-link');
    });

    it('binds to default field names (title / slug)', function () {
        TestableForm::$formSchema = [TitleWithSlugInput::make()];

        Livewire::test(TestableForm::class)
            ->set(['data.title' => 'Persisted Title', 'data.slug' => 'persisted-slug'])
            ->assertSet('data.title', 'Persisted Title')
            ->assertSet('data.slug', 'persisted-slug')
            ->assertSeeHtml('wire:model.live.blur="data.title"')
            ->assertSeeHtml('id="form.slug"')
            ->assertSee('persisted-slug');
    });

    it('binds to custom field names and renders custom labels', function () {
        TestableForm::$formSchema = [
            TitleWithSlugInput::make(
                fieldTitle: 'TitleFieldName',
                fieldSlug: 'SlugFieldName',
                urlVisitLinkLabel: '*Visit Link Label*',
                titleLabel: '*Title Label*',
                titlePlaceholder: '*Title Placeholder*',
                slugLabel: '*Slug Label*',
            ),
        ];

        Livewire::test(TestableForm::class, [
            'record' => new Record(['SlugFieldName' => 'persisted-slug']),
        ])
            ->assertSeeHtml('wire:model.live.blur="data.TitleFieldName"')
            ->assertSeeHtml('id="form.SlugFieldName"')
            ->assertSee('*Title Label*')
            ->assertSee('*Slug Label*')
            ->assertSee('*Visit Link Label*')
            ->assertSeeHtml('placeholder="*Title Placeholder*"');
    });

    it('reads default field names from config', function () {
        config()->set('filament-title-with-slug.field_title', 'name');
        config()->set('filament-title-with-slug.field_slug', 'handle');

        TestableForm::$formSchema = [TitleWithSlugInput::make()];

        Livewire::test(TestableForm::class)
            ->set(['data.name' => 'Test', 'data.handle' => 'test'])
            ->assertSeeHtml('wire:model.live.blur="data.name"')
            ->assertSeeHtml('id="form.handle"');
    });

    it('shows the default permalink label prefix', function () {
        TestableForm::$formSchema = [TitleWithSlugInput::make()];

        Livewire::test(TestableForm::class)
            ->set(['data.title' => 'Test', 'data.slug' => 'test'])
            ->assertSeeHtml(trans('filament-title-with-slug::package.permalink_label'));
    });

    it('shows slug label postfix', function () {
        TestableForm::$formSchema = [
            TitleWithSlugInput::make(slugLabelPostfix: '.mysite.com'),
        ];

        Livewire::test(TestableForm::class)
            ->set(['data.title' => 'Test', 'data.slug' => 'test'])
            ->assertSeeHtml('.mysite.com');
    });

    it('renders title as readonly when titleIsReadonly is true', function () {
        TestableForm::$formSchema = [
            TitleWithSlugInput::make(titleIsReadonly: true),
        ];

        Livewire::test(TestableForm::class)
            ->set(['data.title' => 'Readonly Title', 'data.slug' => 'readonly-title'])
            ->assertSeeHtml('readonly');
    });

    it('renders slug in static display mode when slugIsReadonly is true', function () {
        TestableForm::$formSchema = [
            TitleWithSlugInput::make(slugIsReadonly: true),
        ];

        $component = Livewire::test(TestableForm::class)
            ->set(['data.title' => 'Readonly Slug', 'data.slug' => 'readonly-slug']);

        // Readonly mode renders as a static span, not an interactive edit link
        $component->assertSeeHtml('readonly-slug');
        // The pencil edit icon / initModification button should not be present
        $component->assertDontSeeHtml('initModification');
    });

    it('hides the URL host when urlHostVisible is false', function () {
        config()->set('filament-title-with-slug.url_host', 'https://www.example.com');

        TestableForm::$formSchema = [
            TitleWithSlugInput::make(urlHostVisible: false, urlPath: '/articles/'),
        ];

        Livewire::test(TestableForm::class, [
            'record' => new Record(['title' => 'Test', 'slug' => 'test']),
        ])
            ->assertDontSeeHtml('https://www.example.com')
            ->assertSeeHtml('/articles/');
    });
});

// ---------------------------------------------------------------------------
// URL generation & visit link
// ---------------------------------------------------------------------------

describe('URL generation', function () {
    it('builds the visit link from host, path, and slug', function () {
        config()->set('filament-title-with-slug.url_host', 'https://www.camya.com');

        TestableForm::$formSchema = [
            TitleWithSlugInput::make(urlPath: '/blog/'),
        ];

        Livewire::test(TestableForm::class, [
            'record' => new Record(['title' => 'Persisted Title', 'slug' => 'persisted-slug']),
        ])->assertSeeHtml('https://www.camya.com/blog/persisted-slug');
    });

    it('hides the visit link when urlVisitLinkVisible is false', function () {
        TestableForm::$formSchema = [
            TitleWithSlugInput::make(
                urlVisitLinkVisible: false,
                urlVisitLinkLabel: '*Visit Link Label*',
            ),
        ];

        Livewire::test(TestableForm::class)
            ->assertDontSee('*Visit Link Label*');
    });

    it('accepts a custom visit link route from a closure', function () {
        TestableForm::$formSchema = [
            TitleWithSlugInput::make(
                urlPath: '',
                urlHostVisible: false,
                urlVisitLinkRoute: fn (?Model $record) => $record?->slug
                    ? 'https://'.$record->slug.'.camya.com'
                    : null,
                slugLabelPostfix: '.camya.com',
            ),
        ];

        Livewire::test(TestableForm::class, [
            'record' => new Record(['title' => 'My Subdomain', 'slug' => 'my-subdomain']),
        ])
            ->assertSeeHtml('https://my-subdomain.camya.com')
            ->assertSeeHtml('>.camya.com<');
    });

    it('accepts a dynamic visit link label from a closure', function () {
        TestableForm::$formSchema = [
            TitleWithSlugInput::make(
                urlVisitLinkLabel: fn () => '*Dynamic Label*',
            ),
        ];

        Livewire::test(TestableForm::class, [
            'record' => new Record(['title' => 'Test', 'slug' => 'test']),
        ])->assertSee('*Dynamic Label*');
    });

    it('renders a URL when slug has no required rule and value is a path separator', function () {
        config()->set('filament-title-with-slug.url_host', 'https://www.camya.com');

        TestableForm::$formSchema = [
            TitleWithSlugInput::make(slugRules: []),
        ];

        Livewire::test(TestableForm::class, [
            'record' => new Record(['title' => 'My Homepage', 'slug' => '/']),
        ])->assertSeeHtml("!editing ? 'https://www.camya.com/' : '/'");
    });

    it('does not render a visit link when no record exists', function () {
        config()->set('filament-title-with-slug.url_host', 'https://www.example.com');

        TestableForm::$formSchema = [
            TitleWithSlugInput::make(urlVisitLinkLabel: '*Visit Link*'),
        ];

        // No record passed — create context, no persisted URL to link to
        Livewire::test(TestableForm::class)
            ->assertDontSee('*Visit Link*');
    });

    it('shows the default visit label with the model name', function () {
        config()->set('filament-title-with-slug.url_host', 'https://www.example.com');

        TestableForm::$formSchema = [
            TitleWithSlugInput::make(), // no urlVisitLinkLabel
        ];

        // Default label = "Visit" translation + class basename headline = "Visit Record"
        Livewire::test(TestableForm::class, [
            'record' => new Record(['title' => 'Test', 'slug' => 'test']),
        ])->assertSee('Visit Record');
    });

    it('shows the visit URL in readonly slug mode', function () {
        config()->set('filament-title-with-slug.url_host', 'https://www.example.com');

        TestableForm::$formSchema = [
            TitleWithSlugInput::make(slugIsReadonly: true),
        ];

        Livewire::test(TestableForm::class, [
            'record' => new Record(['title' => 'Test', 'slug' => 'my-slug']),
        ])
            ->assertSeeHtml('https://www.example.com')
            ->assertSeeHtml('my-slug')
            ->assertDontSeeHtml('initModification');
    });
});

// ---------------------------------------------------------------------------
// Auto-slug generation
// ---------------------------------------------------------------------------

describe('auto-slug generation', function () {
    it('auto-generates slug from title in create context', function () {
        TestableForm::$formSchema = [TitleWithSlugInput::make()];

        Livewire::test(TestableForm::class)
            ->set('data.title', 'Hello World')
            ->assertSet('data.slug', 'hello-world');
    });

    it('handles multi-word and special character titles', function () {
        TestableForm::$formSchema = [TitleWithSlugInput::make()];

        Livewire::test(TestableForm::class)
            ->set('data.title', 'The Quick Brown Fox & Friends!')
            ->assertSet('data.slug', 'the-quick-brown-fox-friends');
    });

    it('does not auto-update slug when editing an existing record', function () {
        TestableForm::$formSchema = [TitleWithSlugInput::make()];

        // Existing record means edit context — slug must stay unchanged
        Livewire::test(TestableForm::class, [
            'record' => new Record(['title' => 'Original Title', 'slug' => 'original-slug']),
        ])
            ->set('data.title', 'Updated Title')
            ->assertSet('data.slug', 'original-slug');
    });

    it('does not overwrite slug when it was manually edited (slug_auto_update_disabled)', function () {
        TestableForm::$formSchema = [TitleWithSlugInput::make()];

        // Simulates the user having manually edited the slug field:
        // the slug's afterStateUpdated sets slug_auto_update_disabled = true
        Livewire::test(TestableForm::class)
            ->set('data.slug', 'my-custom-slug')  // manual slug edit
            ->set('data.title', 'Some New Title')  // title change should not overwrite
            ->assertSet('data.slug', 'my-custom-slug');
    });

    it('applies a custom slugifier closure', function () {
        TestableForm::$formSchema = [
            TitleWithSlugInput::make(
                slugSlugifier: fn (string $value) => strtoupper(Str::slug($value)),
            ),
        ];

        Livewire::test(TestableForm::class)
            ->set('data.title', 'hello world')
            ->assertSet('data.slug', 'HELLO-WORLD');
    });

    it('fires the titleAfterStateUpdated callback', function () {
        TestableForm::$formSchema = [
            TitleWithSlugInput::make(
                // Use the callback to overwrite the slug with a sentinel value
                // so we can confirm the callback executed
                titleAfterStateUpdated: function (Set $set) {
                    $set('slug', 'callback-ran');
                },
            ),
        ];

        Livewire::test(TestableForm::class)
            ->set('data.title', 'Trigger')
            ->assertSet('data.slug', 'callback-ran');
    });

    it('re-slugifies from title when slug is cleared', function () {
        TestableForm::$formSchema = [TitleWithSlugInput::make()];

        // User types a title, then manually clears the slug — should regenerate from title
        Livewire::test(TestableForm::class)
            ->set('data.title', 'Hello World')
            ->set('data.slug', '')         // clear the slug
            ->assertSet('data.slug', 'hello-world');
    });

    it('fires the slugAfterStateUpdated callback', function () {
        $sentinel = 'sentinel-'.uniqid();

        TestableForm::$formSchema = [
            TitleWithSlugInput::make(
                slugAfterStateUpdated: function (Set $set) use ($sentinel) {
                    $set('slug', $sentinel);
                },
            ),
        ];

        Livewire::test(TestableForm::class)
            ->set('data.slug', 'any-value')
            ->assertSet('data.slug', $sentinel);
    });

    it('auto-generates slug when a custom titleField is used', function () {
        TestableForm::$formSchema = [
            TitleWithSlugInput::make(
                titleField: \Filament\Forms\Components\TextInput::make('title'),
            ),
        ];

        Livewire::test(TestableForm::class)
            ->set('data.title', 'Hello World')
            ->assertSet('data.slug', 'hello-world');
    });

    it('derives fieldTitle from custom titleField name', function () {
        TestableForm::$formSchema = [
            TitleWithSlugInput::make(
                titleField: \Filament\Forms\Components\TextInput::make('name'),
                fieldSlug: 'handle',
            ),
        ];

        Livewire::test(TestableForm::class)
            ->set('data.name', 'My Page')
            ->assertSet('data.handle', 'my-page');
    });

    it('does not auto-update slug in edit context when using a custom titleField', function () {
        TestableForm::$formSchema = [
            TitleWithSlugInput::make(
                titleField: \Filament\Forms\Components\TextInput::make('title'),
            ),
        ];

        Livewire::test(TestableForm::class, [
            'record' => new Record(['title' => 'Original Title', 'slug' => 'original-slug']),
        ])
            ->set('data.title', 'Updated Title')
            ->assertSet('data.slug', 'original-slug');
    });

    it('fires titleAfterStateUpdated callback when using a custom titleField', function () {
        TestableForm::$formSchema = [
            TitleWithSlugInput::make(
                titleField: \Filament\Forms\Components\TextInput::make('title'),
                titleAfterStateUpdated: function (Set $set) {
                    $set('slug', 'custom-field-callback-ran');
                },
            ),
        ];

        Livewire::test(TestableForm::class)
            ->set('data.title', 'Trigger')
            ->assertSet('data.slug', 'custom-field-callback-ran');
    });

    it('applies a custom slugifier when using a custom titleField', function () {
        TestableForm::$formSchema = [
            TitleWithSlugInput::make(
                titleField: \Filament\Forms\Components\TextInput::make('title'),
                slugSlugifier: fn (string $value) => strtoupper(Str::slug($value)),
            ),
        ];

        Livewire::test(TestableForm::class)
            ->set('data.title', 'hello world')
            ->assertSet('data.slug', 'HELLO-WORLD');
    });

    it('respects explicit fieldTitle when used alongside a custom titleField', function () {
        // fieldTitle should override the getName() derivation, and the slug
        // re-gen ($get($fieldTitle)) must use the explicit name too.
        TestableForm::$formSchema = [
            TitleWithSlugInput::make(
                fieldTitle: 'name',
                fieldSlug: 'handle',
                titleField: \Filament\Forms\Components\TextInput::make('name'),
            ),
        ];

        Livewire::test(TestableForm::class)
            ->set('data.name', 'My Page')
            ->assertSet('data.handle', 'my-page');
    });
});

// ---------------------------------------------------------------------------
// titleFieldWrapper
// ---------------------------------------------------------------------------

describe('titleFieldWrapper', function () {
    it('passes the title field to the wrapper and uses the returned component', function () {
        TestableForm::$formSchema = [
            TitleWithSlugInput::make(
                titleFieldWrapper: fn ($field) => $field->label('*Wrapper Applied*'),
            ),
        ];

        Livewire::test(TestableForm::class)
            ->assertSee('*Wrapper Applied*');
    });

    it('slug still auto-generates after the title field is wrapped', function () {
        TestableForm::$formSchema = [
            TitleWithSlugInput::make(
                titleFieldWrapper: fn ($field) => $field->label('Wrapped'),
            ),
        ];

        Livewire::test(TestableForm::class)
            ->set('data.title', 'Hello World')
            ->assertSet('data.slug', 'hello-world');
    });

    it('applies the wrapper to a custom titleField', function () {
        TestableForm::$formSchema = [
            TitleWithSlugInput::make(
                titleField: \Filament\Forms\Components\TextInput::make('title'),
                titleFieldWrapper: fn ($field) => $field->label('*Custom Wrapped*'),
            ),
        ];

        Livewire::test(TestableForm::class)
            ->assertSee('*Custom Wrapped*');
    });
});

// ---------------------------------------------------------------------------
// titleExtraInputAttributes
// ---------------------------------------------------------------------------

describe('titleExtraInputAttributes', function () {
    it('applies custom extra input attributes to the title input', function () {
        TestableForm::$formSchema = [
            TitleWithSlugInput::make(
                titleExtraInputAttributes: ['style' => 'color: red;', 'data-testid' => 'custom-title'],
            ),
        ];

        Livewire::test(TestableForm::class)
            ->assertSeeHtml('data-testid="custom-title"');
    });
});
