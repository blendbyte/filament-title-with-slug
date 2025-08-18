<x-filament-forms::field-wrapper
    :id="$getId()"
    :label="$getLabel()"
    :label-sr-only="$isLabelHidden()"
    :helper-text="$getHelperText()"
    :hint="$getHint()"
    :hint-icon="$getHintIcon()"
    :required="$isRequired()"
    :state-path="$getStatePath()"
    class="-mt-3 filament-seo-slug-input-wrapper"
>
    <div
        x-data="{
            context: '{{ $getContext() }}', // edit or create
            state: $wire.entangle('{{ $getStatePath() }}'), // current slug value
            statePersisted: '', // slug value received from db
            stateInitial: '', // slug value before modification
            editing: false,
            modified: false,
            initModification: function() {
                this.stateInitial = this.state;

                if(!this.statePersisted) {
                    this.statePersisted = this.state;
                }

                this.editing = true;

                setTimeout(() => $refs.slugInput.focus(), 75);
                {{--$nextTick(() => $refs.slugInput.focus());--}}
            },
            submitModification: function() {
                if(!this.stateInitial) {
                    this.state = '';
                }
                else {
                    this.state = this.stateInitial;
                }

                $wire.set('{{ $getStatePath() }}', this.state)
                this.detectModification();
                this.editing = false;
           },
           cancelModification: function() {
                this.stateInitial = this.state;
                this.detectModification();
                this.editing = false;
           },
           resetModification: function() {
                this.stateInitial = this.statePersisted;
                this.detectModification();
           },
           detectModification: function() {
                this.modified = this.stateInitial !== this.statePersisted;
           },
        }"
        x-on:submit.document="modified = false"
    >
        <div
            {{ $attributes->merge($getExtraAttributes())->class(['flex gap-4 items-center justify-between group text-sm filament-forms-text-input-component']) }}
        >
            @if($getReadOnly())
                <span class="flex">
                    <span class="mr-1">{{ $getLabelPrefix() }}</span>
                    <span class="text-gray-400">{{ $getFullBaseUrl() }}</span>
                    <span class="text-gray-400 font-semibold">{{ $getState() }}</span>
                </span>

                @if($getSlugInputUrlVisitLinkVisible())
                    <x-filament::link
                            :href="$getRecordUrl()"
                            target="_blank"
                            size="sm"
                            icon="heroicon-m-arrow-top-right-on-square"
                            icon-position="after"
                        >
                        {{ $getVisitLinkLabel() }}
                    </x-filament::link>
                @endif
            @else
                <span
                     class="
                        @if(!$getState()) flex items-center gap-1 @endif
                    "
                >
                    <span>{{ $getLabelPrefix() }}</span>

                    <span
                        x-text="!editing ? '{{ $getFullBaseUrl() }}' : '{{ $getBasePath() }}'"
                        class="text-gray-400"
                    ></span>

                    <a
                        href="#"
                        role="button"
                        title="{{ trans('filament-title-with-slug::package.permalink_action_edit') }}"
                        x-on:click.prevent="initModification()"
                        x-show="!editing"
                        class="
                            cursor-pointer
                            font-semibold text-gray-400
                            inline-flex items-center justify-center
                            hover:underline hover:text-primary-500
                            dark:hover:text-primary-400
                            gap-1
                        "
                        :class="context !== 'create' && modified ? 'text-gray-600 bg-gray-100 dark:text-gray-400 dark:bg-gray-700 px-1 rounded-md' : ''"
                    >
                        <span class="mr-1">{{ $getState() }}</span>

                        <x-heroicon-m-pencil-square
                            stroke-width="2"
                            class="
                                h-4 w-4
                                text-primary-600 dark:text-primary-400
                            "
                        />

                        <span class="sr-only">{{ trans('filament-title-with-slug::package.permalink_action_edit') }}</span>
                    </a>

                    @if($getSlugLabelPostfix())
                        <span
                            x-show="!editing"
                            class="ml-0.5 text-gray-400"
                        >{{ $getSlugLabelPostfix() }}</span>
                    @endif

                    <span x-show="!editing && context !== 'create' && modified"> [{{ trans('filament-title-with-slug::package.permalink_status_changed') }}]</span>
                </span>

                <div
                    class="flex-1 mx-2"
                    x-show="editing"
                    style="display: none;"
                >
                    <div class="fi-input-wrp">
                        <div class="fi-input-wrp-content-ctn">
                            <input
                                type="text"
                                x-ref="slugInput"
                                x-model="stateInitial"
                                x-bind:disabled="!editing"
                                x-on:keydown.enter="submitModification()"
                                x-on:keydown.escape="cancelModification()"
                                {!! ($autocomplete = $getAutocomplete()) ? "autocomplete=\"{$autocomplete}\"" : null !!}
                                id="{{ $getId() }}"
                                {!! ($placeholder = $getPlaceholder()) ? "placeholder=\"{$placeholder}\"" : null !!}
                                {!! $isRequired() ? 'required' : null !!}
                                {{ $getExtraInputAttributeBag()->class([
                                    'fi-input text-sm font-semibold',
                                    'border-danger-600 ring-danger-600' => $errors->has($getStatePath())])
                                }}
                            />
                        </div>
                    </div>
                </div>

                <div
                    x-show="editing"
                    class="flex gap-2 items-center"
                    style="display: none;"
                >
                    <x-filament::button x-on:click.prevent="submitModification()">
                        {{ trans('filament-title-with-slug::package.permalink_action_ok') }}
                    </x-filament::button>

                    <x-filament::icon-button
                        icon="heroicon-o-arrow-path"
                        size="sm"
                        color="info"
                        x-show="context === 'edit' && modified"
                        x-on:click.prevent="resetModification()"
                        :label="trans('filament-title-with-slug::package.permalink_action_reset')"
                        :tooltip="trans('filament-title-with-slug::package.permalink_action_reset')"
                    />

                    <x-filament::icon-button
                        icon="heroicon-o-x-mark"
                        size="sm"
                        color="danger"
                        x-on:click.prevent="cancelModification()"
                        :label="trans('filament-title-with-slug::package.permalink_action_cancel')"
                        :tooltip="trans('filament-title-with-slug::package.permalink_action_cancel')"
                    />
                </div>

                <span
                    x-show="context === 'edit'"
                    class="flex items-center space-x-2"
                >
                    @if($getSlugInputUrlVisitLinkVisible())
                        <template x-if="!editing">
                            <x-filament::link
                                :href="$getRecordUrl()"
                                target="_blank"
                                size="sm"
                                icon="heroicon-m-arrow-top-right-on-square"
                                icon-position="after"
                            >
                                {{ $getVisitLinkLabel() }}
                            </x-filament::link>
                        </template>
                    @endif
            </span>
            @endif
        </div>
    </div>
</x-filament-forms::field-wrapper>
