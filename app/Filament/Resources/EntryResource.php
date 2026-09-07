<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EntryResource\Pages;
use App\Models\Entry;
use App\Models\Module;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EntryResource extends Resource
{
    protected static ?string $model = Entry::class;

    protected static ?string $modelLabel = 'registro';
    protected static ?string $pluralModelLabel = 'registros';

    // No mostramos el ítem por defecto: agregamos uno por cada módulo (abajo).
    protected static bool $shouldRegisterNavigation = false;

    /**
     * Un ítem de navegación por cada módulo creado.
     */
    public static function getNavigationItems(): array
    {
        if (! Schema::hasTable('modules')) {
            return [];
        }

        return Module::query()->orderBy('sort_order')->orderBy('name')->get()
            ->map(function (Module $module) {
                $url = static::getUrl('index') . '?module=' . $module->slug;

                return NavigationItem::make($module->pluralLabel())
                    ->icon($module->icon ?: 'heroicon-o-rectangle-stack')
                    ->group('Módulos')
                    ->url($url)
                    ->isActiveWhen(fn () => request()->routeIs(static::getRouteBaseName() . '.*')
                        && request('module') === $module->slug);
            })
            ->all();
    }

    protected static function moduleFrom($livewire): ?Module
    {
        if ($livewire && method_exists($livewire, 'currentModule')) {
            return $livewire->currentModule();
        }

        return null;
    }

    public static function form(Form $form): Form
    {
        $module = static::moduleFrom($form->getLivewire());

        $fieldComponents = [];
        if ($module) {
            foreach ($module->fieldList() as $field) {
                if ($component = static::buildField($field)) {
                    $fieldComponents[] = $component;
                }
            }
        }

        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Group::make(array_merge([
                    Forms\Components\TextInput::make('title')
                        ->label('Título / Nombre')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                            if ($operation === 'create') {
                                $set('slug', Str::slug($state));
                            }
                        }),
                ], $fieldComponents))->columnSpan(2),

                Forms\Components\Group::make([
                    Forms\Components\Section::make('Publicación')->schema([
                        Forms\Components\Toggle::make('is_published')->label('Publicado')->default(true),
                        Forms\Components\TextInput::make('slug')->label('Slug (URL)'),
                        Forms\Components\TextInput::make('sort_order')->label('Orden')->numeric()->default(0),
                    ]),
                ])->columnSpan(1),
            ]),
        ]);
    }

    protected static function buildField(array $field): ?Forms\Components\Component
    {
        $key = $field['key'] ?? null;
        if (! $key) {
            return null;
        }

        $name = 'data.' . $key;
        $label = $field['label'] ?? $key;
        $required = (bool) ($field['required'] ?? false);

        $component = match ($field['type'] ?? 'text') {
            'textarea' => Forms\Components\Textarea::make($name)->rows(4),
            'richtext' => Forms\Components\RichEditor::make($name),
            'email' => Forms\Components\TextInput::make($name)->email(),
            'number' => Forms\Components\TextInput::make($name)->numeric(),
            'boolean' => Forms\Components\Toggle::make($name),
            'date' => Forms\Components\DatePicker::make($name)->native(false),
            'select' => Forms\Components\Select::make($name)
                ->options(static::parseOptions($field['options'] ?? ''))->native(false),
            'image' => Forms\Components\FileUpload::make($name)->image()->maxSize(5120)->disk('public')->directory('modulos'),
            'gallery' => Forms\Components\FileUpload::make($name)->image()->multiple()->reorderable()
                ->maxSize(5120)->disk('public')->directory('modulos'),
            default => Forms\Components\TextInput::make($name),
        };

        return $component->label($label)->required($required);
    }

    protected static function parseOptions(string $options): array
    {
        return collect(explode(',', $options))
            ->map(fn ($o) => trim($o))
            ->filter()
            ->mapWithKeys(fn ($o) => [$o => $o])
            ->all();
    }

    /**
     * Opciones de un campo tipo "select" como lista (para el frontend).
     */
    public static function optionsFor(array $field): array
    {
        return array_values(static::parseOptions($field['options'] ?? ''));
    }

    public static function table(Table $table): Table
    {
        $module = static::moduleFrom($table->getLivewire());

        $columns = [
            Tables\Columns\TextColumn::make('title')->label('Título')->searchable()->sortable()->weight('bold'),
        ];

        if ($module) {
            foreach ($module->fieldList()->take(3) as $field) {
                if ($column = static::buildColumn($field)) {
                    $columns[] = $column;
                }
            }
        }

        $columns[] = Tables\Columns\IconColumn::make('is_published')->label('Publicado')->boolean();
        $columns[] = Tables\Columns\TextColumn::make('updated_at')->label('Actualizado')->since()->sortable();

        return $table
            ->columns($columns)
            ->modifyQueryUsing(fn (Builder $query) => $module ? $query->where('module_id', $module->id) : $query)
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function buildColumn(array $field): ?Tables\Columns\Column
    {
        $name = 'data.' . ($field['key'] ?? '');
        $label = $field['label'] ?? $field['key'] ?? '';

        return match ($field['type'] ?? 'text') {
            'image' => Tables\Columns\ImageColumn::make($name)->label($label)->disk('public')->height(40),
            'boolean' => Tables\Columns\IconColumn::make($name)->label($label)->boolean(),
            'richtext', 'gallery' => null,
            default => Tables\Columns\TextColumn::make($name)->label($label)->limit(40),
        };
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEntries::route('/'),
            'create' => Pages\CreateEntry::route('/create'),
            'edit' => Pages\EditEntry::route('/{record}/edit'),
        ];
    }
}
