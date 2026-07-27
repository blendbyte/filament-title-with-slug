<?php

namespace Blendbyte\FilamentTitleWithSlug;

use Closure;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;

class SlugInput extends TextInput
{
    /**
     * Must be the $view PROPERTY, not a getView() override.
     *
     * TextInput implements HasEmbeddedView, and ViewComponent::toHtml() only
     * renders the Blade view when hasView() is true — which checks
     * isset($this->view). With just a getView() override, hasView() stays false
     * and Filament silently falls back to TextInput::toEmbeddedHtml(), dropping
     * the whole permalink row in favour of a plain text input.
     */
    protected string $view = 'filament-title-with-slug::forms.fields.slug-input';

    protected string|Closure|null $context = null;

    protected string|Closure $basePath = '/';

    protected string|Closure|null $baseUrl = null;

    protected bool $showUrl = true;

    protected bool $cancelled = false;

    protected Closure $recordSlug;

    protected bool|Closure $slugReadOnly = false;

    protected string $labelPrefix;

    protected ?Closure $visitLinkRoute = null;

    protected string|Closure|null $visitLinkLabel = null;

    protected bool|Closure $slugInputUrlVisitLinkVisible = true;

    protected ?Closure $slugInputModelName = null;

    protected string|Closure|null $slugLabelPostfix = null;

    public function slugInputUrlVisitLinkVisible(bool|Closure $slugInputUrlVisitLinkVisible): static
    {
        $this->slugInputUrlVisitLinkVisible = $slugInputUrlVisitLinkVisible;

        return $this;
    }

    public function getSlugInputUrlVisitLinkVisible(): ?bool
    {
        return $this->evaluate($this->slugInputUrlVisitLinkVisible);
    }

    public function slugInputModelName(?Closure $slugInputModelName): static
    {
        $this->slugInputModelName = $slugInputModelName;

        return $this;
    }

    public function getSlugInputModelName(): ?string
    {
        return $this->evaluate($this->slugInputModelName);
    }

    public function slugInputVisitLinkRoute(?Closure $visitLinkRoute): static
    {
        $this->visitLinkRoute = $visitLinkRoute;

        return $this;
    }

    public function getVisitLinkRoute(): ?string
    {
        return $this->evaluate($this->visitLinkRoute);
    }

    public function slugInputVisitLinkLabel(string|Closure|null $visitLinkLabel): static
    {
        $this->visitLinkLabel = $visitLinkLabel;

        return $this;
    }

    public function getVisitLinkLabel(): string
    {
        $label = $this->evaluate($this->visitLinkLabel);

        if ($label === '') {
            return '';
        }

        return $label ?: trans('filament-title-with-slug::package.permalink_label_link_visit').' '.$this->getSlugInputModelName();
    }

    public function slugInputLabelPrefix(?string $labelPrefix): static
    {
        $this->labelPrefix = $labelPrefix ?? trans('filament-title-with-slug::package.permalink_label');

        return $this;
    }

    public function getLabelPrefix(): string
    {
        return $this->evaluate($this->labelPrefix);
    }

    public function slugReadOnly(Closure|bool $condition = true): static
    {
        $this->slugReadOnly = $condition;

        return $this;
    }

    public function getSlugReadOnly(): bool
    {
        return $this->evaluate($this->slugReadOnly);
    }

    public function slugInputContext(string|Closure|null $context): static
    {
        $this->context = $context;

        return $this;
    }

    public function getContext(): ?string
    {
        return $this->evaluate($this->context);
    }

    public function slugInputSlugLabelPostfix(string|Closure|null $slugLabelPostfix): static
    {
        $this->slugLabelPostfix = $slugLabelPostfix;

        return $this;
    }

    public function getSlugLabelPostfix(): ?string
    {
        return $this->evaluate($this->slugLabelPostfix);
    }

    public function slugInputRecordSlug(Closure $recordSlug): static
    {
        $this->recordSlug = $recordSlug;

        return $this;
    }

    public function getRecordSlug(): ?string
    {
        return $this->evaluate($this->recordSlug);
    }

    public function getRecordUrl(): ?string
    {
        if (! $this->getRecordSlug()) {
            return null;
        }

        $visitLinkRoute = $this->getVisitLinkRoute();

        return $visitLinkRoute
            ? $visitLinkRoute
            : $this->getFullBaseUrl().$this->getRecordSlug();
    }

    public function slugInputBasePath(string|Closure|null $path): static
    {
        $this->basePath = ! is_null($path) ? $path : $this->basePath;

        return $this;
    }

    public function slugInputBaseUrl(string|Closure|null $url): static
    {
        $this->baseUrl = $url ?: config('app.url');

        return $this;
    }

    public function getBaseUrl(): string
    {
        return Str::rtrim($this->evaluate($this->baseUrl), '/');
    }

    public function slugInputShowUrl(bool $showUrl): static
    {
        $this->showUrl = $showUrl;

        return $this;
    }

    public function getShowUrl(): ?bool
    {
        return $this->showUrl;
    }

    public function getFullBaseUrl(): ?string
    {
        return $this->showUrl
            ? $this->getBaseUrl().$this->getBasePath()
            : $this->getBasePath();
    }

    public function getBasePath(): string
    {
        return $this->evaluate($this->basePath);
    }
}
