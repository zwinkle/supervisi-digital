@include('admin.schools.partials.table', ['schools' => $schools])
<div class="mt-4">
    {{ $schools->links('vendor.pagination.tailwind') }}
</div>
