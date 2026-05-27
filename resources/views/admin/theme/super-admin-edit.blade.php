@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Gestion du Thème - SuperAdmin</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Formulaire de modification du thème -->
        <div class="lg:col-span-2">
            <form action="{{ route('admin.theme.update') }}" method="POST" class="bg-white rounded-lg shadow-md p-6">
                @csrf
                
                <div class="mb-6">
                    <h2 class="text-lg font-semibold mb-4">Sélection des utilisateurs</h2>
                    <div class="space-y-2 max-h-60 overflow-y-auto">
                        @foreach($userGroups as $adminId => $users)
                            <div class="border rounded p-3">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" class="user-group-checkbox" 
                                           data-group="{{ $adminId }}">
                                    <span class="font-semibold">
                                        {{ $adminId ? ($users->first()->admin->name ?? 'Admin Inconnu') : 'Utilisateurs sans admin' }}
                                    </span>
                                </label>
                                <div class="ml-6 mt-2 space-y-1">
                                    @foreach($users as $user)
                                        <label class="flex items-center space-x-2">
                                            <input type="checkbox" name="user_ids[]" 
                                                   value="{{ $user->id }}" 
                                                   class="user-checkbox user-group-{{ $adminId }}"
                                                   {{ $user->id == auth()->id() ? 'checked' : '' }}>
                                            <span>{{ $user->name }} ({{ $user->email }})</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Les champs de couleurs restent les mêmes -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Couleur de la navbar
                        </label>
                        <input type="color" name="navbar_bg" value="#{{ $colors['navbar_bg'] }}" 
                               class="w-full h-10 rounded border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Couleur du footer
                        </label>
                        <input type="color" name="footer_bg" value="#{{ $colors['footer_bg'] }}" 
                               class="w-full h-10 rounded border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Couleur du texte principal
                        </label>
                        <input type="color" name="primary_text" value="#{{ $colors['primary_text'] }}" 
                               class="w-full h-10 rounded border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Couleur de survol
                        </label>
                        <input type="color" name="hover_color" value="#{{ $colors['hover_color'] }}" 
                               class="w-full h-10 rounded border-gray-300">
                    </div>
                </div>

                <div class="flex space-x-4">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Mettre à jour le thème
                    </button>
                    <button type="button" onclick="selectAllUsers()" class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700">
                        Sélectionner tous
                    </button>
                </div>
            </form>
        </div>

        <!-- Aperçu et actions -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-4">Actions rapides</h3>
                <form action="{{ route('admin.theme.reset') }}" method="POST" class="mb-4">
                    @csrf
                    <input type="hidden" name="user_ids" id="reset_user_ids">
                    <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        Réinitialiser le thème
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function selectAllUsers() {
    document.querySelectorAll('.user-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
}

// Gestion des groupes d'utilisateurs
document.querySelectorAll('.user-group-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const groupId = this.dataset.group;
        document.querySelectorAll('.user-group-' + groupId).forEach(userCheckbox => {
            userCheckbox.checked = this.checked;
        });
    });
});

// Mettre à jour les IDs pour la réinitialisation
document.querySelector('form[action="{{ route("admin.theme.reset") }}"]').addEventListener('submit', function() {
    const selectedUsers = Array.from(document.querySelectorAll('.user-checkbox:checked'))
        .map(checkbox => checkbox.value);
    document.getElementById('reset_user_ids').value = selectedUsers.join(',');
});
</script>
@endsection