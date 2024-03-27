@if ($crud->hasAccess('update'))
    <a
        href="{{ url($crud->route.'/'.$entry->getKey().'/impersonate') }}"
        class="btn btn-sm btn-link {{ auth()->user()->id == $entry->getKey() ? 'disabled': '' }}"
        data-toggle="tooltip" title="Impersonate" data-original-title="Tooltip"
    >
        <i class="la la-lg la-user-secret"></i>
    </a>
@endif
