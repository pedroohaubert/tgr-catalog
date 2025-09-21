<form wire:submit.prevent="submit" class="flex items-center gap-2 max-w-xl w-full">
    <input
        wire:model.defer="query"
        type="search"
        placeholder="Buscar produtos..."
        class="block w-64 rounded-md border-gray-300 shadow-sm focus:border-gray-700 focus:ring-gray-700 text-sm"
    />
    <label class="inline-flex items-center gap-1.5 text-xs text-gray-500">
        <input type="checkbox" wire:model="showInactive" class="w-3 h-3 rounded border-gray-300 text-gray-800 focus:ring-gray-700" />
        Inativos
    </label>
    <button type="submit" class="inline-flex items-center px-3 py-2 rounded-md bg-black text-white text-sm hover:bg-gray-800">
        Buscar
    </button>
</form>


