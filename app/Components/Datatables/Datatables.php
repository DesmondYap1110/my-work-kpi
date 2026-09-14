<?php

namespace App\Components\Datatables;

use App\Components\Filters\Filter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Base class for the AJAX list classes under this namespace, following the
 * same pattern used in the MCS project: one {Entity}List class per module,
 * a shared generic AJAX endpoint (DatatablesController@listing), and a
 * table skeleton rendered server-side with its columns declared once
 * (getTableColumns()) and read back by the client via the data-cols
 * attribute, so column definitions never have to be duplicated in JS.
 */
abstract class Datatables
{
    const PAGINATION_NUMBER = 10;

    /**
     * Column key => header label, in display order. The key is also the
     * key each row array in listing() must use.
     *
     * @return array<string, string>
     */
    abstract public static function getTableColumns(): array;

    /**
     * Build the filtered/sorted/paginated Eloquent query for this request.
     * Subclasses build the base query via the matching App\Queries\*
     * class, then hand it to paginateFromRequest() below.
     */
    abstract public function filter(Request $request): LengthAwarePaginator;

    /**
     * Map the paginated result into the row arrays the table expects,
     * keyed exactly like getTableColumns().
     */
    abstract public function listing(Request $request, LengthAwarePaginator $result): array;

    /**
     * Whether only the system administrator may pull this list.
     *
     * Every table in the app is served by one AJAX endpoint that takes the
     * class name from the request, so a page being absent from someone's
     * sidebar does not put its rows out of reach - a hand-made POST naming
     * TeamList would still answer. Lists default to administrator-only and a
     * list that ordinary staff may read says so, rather than the other way
     * round: forgetting to mark a new list then closes it, instead of leaking
     * it.
     *
     * @see \App\Http\Controllers\DatatablesController
     */
    public static function adminOnly(): bool
    {
        return true;
    }

    /**
     * The filter fields this list offers, e.g. [new SelectFilter(...),
     * new DateFilter(...)]. Empty by default - only lists that actually
     * have a filter bar (Staff, Project, ProjectTask) override this.
     *
     * @return array<int, Filter>
     */
    public function filters(): array
    {
        return [];
    }

    /**
     * Opt a list into inline add/edit: rows become editable in place and a
     * blank "new" row is appended under the last page, instead of the user
     * going through a modal.
     *
     * Return a map of column key => field definition. Only columns listed
     * here become editable; everything else renders read-only.
     *
     *   ['team_name' => ['type' => 'text', 'required' => true, 'placeholder' => 'Team name']]
     *
     * Supported types: text, textarea, select (with 'options' => [value => label]).
     * Rows must also expose their raw values - see inlineValues().
     *
     * @return array<string, array<string, mixed>>
     */
    public function inlineFields(): array
    {
        return [];
    }

    /**
     * Where the inline row posts to. 'update' uses __id__ as the placeholder,
     * matching the convention already used by the edit modals.
     *
     * @return array{store?: string, update?: string}
     */
    public function inlineRoutes(): array
    {
        return [];
    }

    /**
     * Raw (unrendered) values for the editable columns of one row, so the
     * inline editor can populate its inputs. Listing rows carry this under
     * the _inline key; DataTables ignores keys it has no column for.
     *
     * @return array<string, mixed>
     */
    protected function inlineValues(array $values): array
    {
        return $values;
    }

    /**
     * Column keys rendered centre-aligned, matching the legacy tables where
     * dates, counts, status pills and the action buttons are centred while
     * free-text columns stay left-aligned. Subclasses add their own; the
     * action column is always centred.
     *
     * @return array<int, string>
     */
    public function centeredColumns(): array
    {
        return [];
    }

    /**
     * The column the table sorts by when it first opens, and which way -
     * [column key, 'asc'|'desc']. Null keeps DataTables' default: the first
     * column, ascending.
     *
     * @return array{0: string, 1: string}|null
     */
    public function defaultOrder(): ?array
    {
        return null;
    }

    /**
     * $extraParams are static, page-scoped values (e.g. a parent record's
     * id) that every AJAX request for this table must carry - stamped as a
     * data-extra JSON attribute and merged client-side, for list pages
     * that are always scoped to one parent (e.g. "objectives of KPI #5")
     * rather than driven purely by a visible filter form.
     */
    public static function buildHTML(string $class, array $extraParams = []): string
    {
        $object = app("\\App\\Components\\Datatables\\{$class}");
        $columns = $object::getTableColumns();
        $dataCols = implode('|', array_keys($columns));
        $dataExtra = e(json_encode($extraParams));

        $order = $object->defaultOrder();
        $dataOrder = $order ? e(json_encode([[array_search($order[0], array_keys($columns), true), $order[1]]])) : '';

        $centered = array_unique([...$object->centeredColumns(), 'action']);
        $dataCentered = implode('|', $centered);

        $headers = collect($columns)
            ->map(fn ($label, $key) => in_array($key, $centered, true)
                ? "<th class=\"text-center\">{$label}</th>"
                : "<th>{$label}</th>")
            ->implode('');

        $inlineFields = $object->inlineFields();
        $inlineAttributes = '';

        if ($inlineFields) {
            $inlineAttributes = ' data-inline-fields=\''.e(json_encode($inlineFields)).'\''
                .' data-inline-routes=\''.e(json_encode($object->inlineRoutes())).'\'';
        }

        $table = '<table class="table table-bordered nowrap table-striped align-middle ajax-datatable w-100" data-class="'.$class.'" data-cols="'.$dataCols.'" data-centered="'.$dataCentered.'" data-page-length="'.static::PAGINATION_NUMBER.'" data-extra=\''.$dataExtra.'\''.($dataOrder ? ' data-order=\''.$dataOrder.'\'' : '').$inlineAttributes.'>'
            ."<thead><tr>{$headers}</tr></thead><tbody></tbody></table>";

        return '<div id="tb-box" class="general-box"><div id="table-padding"><div id="table-div">'.$table.'</div></div></div>';
    }

    /**
     * Renders the filter bar for a list class from its filters(), in the
     * same #tb-border-line / #filter-btn-div box the legacy app used.
     * Submitting/resetting this form is wired up client-side (see
     * public/js/datatables.js's .js-datatable-filter handling) to reload
     * the matching AJAX table rather than navigate.
     */
    public static function buildHTMLFilter(string $class): string
    {
        $object = app("\\App\\Components\\Datatables\\{$class}");
        $filters = $object->filters();

        if (empty($filters)) {
            return '';
        }

        // Two per row on a tablet (an iPad, sidebar open, has ~730px), four on a
        // wide screen - a quarter of a tablet was too narrow for the fields
        // and pushed Filter/Reset onto two lines, or off the page.
        $fields = collect($filters)
            ->map(fn (Filter $filter) => '<div class="col-md-6 col-xl-3">'.$filter->getHTML().'</div>')
            ->implode('');

        return '<div id="tb-box" class="general-box mb-3">'
            .'<div id="table-padding">'
            .'<form class="row g-2 align-items-end js-datatable-filter" data-for="'.e($class).'">'
            .$fields
            .'<div class="col-md-6 col-xl-3 datatable-filter-actions" id="filter-btn-div">'
            .'<button type="submit" id="general-btn" class="btn1"><i class="ri-filter-3-line"></i>Filter</button>'
            .'<button type="reset" id="general-btn" class="btn2"><i class="ri-refresh-line"></i>Reset</button>'
            .'</div>'
            .'</form>'
            .'</div>'
            .'</div>';
    }

    /**
     * Applies the DataTables "order" param (falling back to the query's
     * own default order when the client hasn't picked a column yet) and
     * pages using DataTables' start/length pair, translated to Laravel's
     * page-number pagination.
     *
     * $sortableMap lets a subclass override which display column maps to
     * which real query column - pass null for a display column that is
     * relation-derived (e.g. "position_name" via a belongsTo) and so can't
     * be sorted without a join; omitted keys default to sorting by their
     * own name.
     */
    protected function paginateFromRequest(Builder $query, Request $request, array $sortableMap = []): LengthAwarePaginator
    {
        $columns = array_keys(static::getTableColumns());
        $order = (array) $request->input('order', []);

        if (! empty($order) && isset($order[0]['column'])) {
            $displayColumn = $columns[$order[0]['column']] ?? null;
            $columnName = $displayColumn && ! array_key_exists($displayColumn, $sortableMap)
                ? $displayColumn
                : ($sortableMap[$displayColumn] ?? null);

            if ($columnName && $displayColumn !== 'action') {
                $query->reorder($columnName, $order[0]['dir'] === 'asc' ? 'asc' : 'desc');
            }
        }

        $length = max(1, (int) $request->input('length', static::PAGINATION_NUMBER));
        $page = (int) floor($request->integer('start') / $length) + 1;

        return $query->paginate($length, ['*'], 'page', $page);
    }

    /**
     * The response envelope every list class returns from listing():
     * DataTables' modern server-side protocol (draw/recordsTotal/
     * recordsFiltered/data).
     */
    protected function respond(Request $request, LengthAwarePaginator $result, array $rows): array
    {
        return [
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $result->total(),
            'recordsFiltered' => $result->total(),
            'data' => $rows,
        ];
    }

    /**
     * Row-action button helpers shared by every list class, rendering the
     * theme's circular icon buttons instead of hand-rolled Bootstrap
     * buttons in each list class. The colour variant is carried on the id
     * (#tb-ac-btn-N), not the class, because that is how the theme's
     * stylesheet targets it.
     */
    /**
     * A text link inside a cell, for making the record's own name clickable
     * instead of spending an action button on it. Uses the theme's #tb-link
     * styling (colour lives on the id, as with the tb-ac-btn-* buttons).
     */
    protected function tbTextLink(string $url, string $label, string $tooltip = ''): string
    {
        $title = $tooltip !== '' ? ' title="'.e($tooltip).'"' : '';

        return '<a href="'.$url.'" id="tb-link"'.$title.'>'.e($label).'</a>';
    }

    protected function tbLink(string $url, string $icon, string $colorId, string $tooltip): string
    {
        return '<a href="'.$url.'" class="tb-ac-btn" id="'.$colorId.'" title="'.e($tooltip).'"><i class="'.$icon.'"></i></a>';
    }

    protected function tbButton(string $icon, string $colorId, string $tooltip, array $dataAttrs, string $extraClass = ''): string
    {
        $attrs = collect($dataAttrs)->map(fn ($value, $key) => 'data-'.$key.'="'.e($value).'"')->implode(' ');

        return '<button type="button" class="tb-ac-btn '.$extraClass.'" id="'.$colorId.'" title="'.e($tooltip).'" '.$attrs.'><i class="'.$icon.'"></i></button>';
    }

    protected function tbForm(string $url, string $method, string $icon, string $colorId, string $tooltip, string $extraClass = ''): string
    {
        return '<form action="'.$url.'" method="POST" class="d-inline '.$extraClass.'">'
            .csrf_field().($method !== 'POST' ? method_field($method) : '')
            .'<button type="submit" class="tb-ac-btn" id="'.$colorId.'" title="'.e($tooltip).'"><i class="'.$icon.'"></i></button></form>';
    }

    protected function tbDeleteForm(string $url): string
    {
        return $this->tbForm($url, 'DELETE', 'ri-delete-bin-6-line', 'tb-ac-btn-2', 'Delete', 'js-confirm-delete');
    }

    /**
     * Whether the person looking at this table is the system administrator.
     *
     * Lists open to everyone use it to leave out the columns, filters and
     * buttons that only an administrator has any use for. Static so that
     * getTableColumns(), which renders the header, can ask it too.
     */
    protected static function viewerIsAdmin(): bool
    {
        return (bool) \Illuminate\Support\Facades\Auth::user()?->isAdmin();
    }

    /**
     * Long text cut to fit a cell, with the whole of it shown on hover.
     *
     * The full text only goes on the element when something was actually cut,
     * so short values do not pop up a copy of what is already on screen. See
     * public/js/modules/text-peek.js.
     */
    protected function tbTruncated(?string $text, int $limit, string $empty = '-'): string
    {
        $text = trim((string) $text);

        if ($text === '') {
            return $empty;
        }

        $short = \Illuminate\Support\Str::limit($text, $limit);

        if ($short === $text) {
            return e($text);
        }

        return '<span class="text-peek" tabindex="0" data-full-text="'.e($text).'">'.e($short).'</span>';
    }

    /**
     * A status pill. The colour lives on the id, as the theme expects:
     * 1 green, 2 red, 3 orange, 4 blue, 5 pink.
     */
    protected function tbStatus(string $label, int $colorId): string
    {
        return '<span class="tb-status" id="tb-status-'.$colorId.'">'.e($label).'</span>';
    }

    protected function tbStatusToggle(string $url, bool $active, string $activeLabel = 'Active', string $inactiveLabel = 'Inactive'): string
    {
        $statusId = $active ? 1 : 2;

        return '<form action="'.$url.'" method="POST" class="d-inline">'
            .csrf_field().method_field('PATCH')
            .'<button type="submit" class="tb-status" id="tb-status-'.$statusId.'" style="border:none;">'.($active ? $activeLabel : $inactiveLabel).'</button></form>';
    }
}
