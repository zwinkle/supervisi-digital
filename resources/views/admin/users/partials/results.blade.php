@include('admin.users.partials.table', ['users' => $users])
<div class="mt-4">
    {{ $users->links('vendor.pagination.tailwind') }}
</div>
