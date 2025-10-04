@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto bg-white p-6 rounded-xl shadow">
    <header class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">📝 Task Manager</h1>
        <div class="text-sm text-gray-500">
            <a href="{{ route('tasks.index', ['filter' => 'all']) }}" class="{{ $filter==='all' ? 'font-semibold underline' : '' }}">All</a>
            <span class="mx-2">|</span>
            <a href="{{ route('tasks.index', ['filter' => 'completed']) }}" class="{{ $filter==='completed' ? 'font-semibold underline' : '' }}">Completed</a>
            <span class="mx-2">|</span>
            <a href="{{ route('tasks.index', ['filter' => 'incomplete']) }}" class="{{ $filter==='incomplete' ? 'font-semibold underline' : '' }}">Incomplete</a>
        </div>
    </header>

    @if(session('success'))
    <div class="mb-4 text-green-700 bg-green-50 border border-green-100 px-4 py-2 rounded" id="alert">
        {{ session('success') }}
    </div>
    @endif

    <form action="{{ route('tasks.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-2 mb-4">
        @csrf
        <div class="md:col-span-1">
            <label class="sr-only">Title</label>
            <input name="title" value="{{ old('title') }}" placeholder="Task title"
                class="w-full border rounded p-2" />
            @error('title') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-1">
            <input name="description" value="{{ old('description') }}" placeholder="Short description (optional)"
                class="w-full border rounded p-2" />
            @error('description') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-1 flex items-center gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Add Task</button>
            <button type="button" id="clearForm" class="text-sm text-gray-600">Clear</button>
        </div>
    </form>

    <div>
        <ul id="taskList" class="space-y-3">
            @forelse($tasks as $task)
            <li data-id="{{ $task->id }}" draggable="true" class="bg-gray-50 p-3 rounded flex items-center justify-between shadow-sm">
                <div class="flex items-start gap-3 w-full">
                    <form action="{{ route('tasks.toggle', $task) }}" method="POST" class="flex-shrink-0">
                        @csrf
                        @method('PATCH')
                        <button type="submit" aria-label="Toggle complete">
                            @if($task->completed)
                            <span class="text-green-600">✅</span>
                            @else
                            <span class="text-gray-400">⭕</span>
                            @endif
                        </button>
                    </form>

                    <div class="flex-1">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-medium {{ $task->completed ? 'line-through text-gray-400' : '' }}">
                                    {{ $task->title }}
                                </h3>
                                @if($task->description)
                                <p class="text-sm text-gray-600">{{ $task->description }}</p>
                                @endif
                            </div>

                            <div class="flex-shrink-0 space-x-2">
                                <button class="editBtn text-sm px-2 py-1 border rounded" data-id="{{ $task->id }}" data-title="{{ e($task->title) }}" data-description="{{ e($task->description) }}">Edit</button>

                                <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this task?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm px-2 py-1 bg-red-600 text-white rounded">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
            @empty
            <li class="text-gray-500 p-4">No tasks yet.</li>
            @endforelse
        </ul>
    </div>
</div>

<div id="editModal" class="fixed inset-0 hidden items-center justify-center bg-black/40 z-50">
    <div class="bg-white rounded p-6 w-full max-w-lg">
        <h2 class="text-xl font-semibold mb-3">Edit task</h2>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="block text-sm">Title</label>
                <input name="title" id="editTitle" class="w-full border p-2 rounded" maxlength="255" />
                <p id="errTitle" class="text-red-600 text-sm mt-1 hidden"></p>
            </div>
            <div class="mb-3">
                <label class="block text-sm">Description</label>
                <textarea name="description" id="editDescription" class="w-full border p-2 rounded" maxlength="2000"></textarea>
                <p id="errDesc" class="text-red-600 text-sm mt-1 hidden"></p>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="cancelEdit" class="px-3 py-1">Cancel</button>
                <button type="submit" class="bg-yellow-500 px-4 py-1 rounded text-white">Save</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;

    const el = document.getElementById('taskList');
    const sortable = new Sortable(el, {
        animation: 150,
        ghostClass: 'task-placeholder',
        dragClass: 'task-dragging',
        onEnd: function(evt) {
            const ids = Array.from(el.children).map(li => li.dataset.id);
            axios.post("{{ route('tasks.reorder') }}", { order: ids })
                .then(resp => {})
                .catch(err => {
                    alert('Could not save order. Try again.');
                });
        }
    });

    const editModal = document.getElementById('editModal');
    const editForm = document.getElementById('editForm');
    const editTitle = document.getElementById('editTitle');
    const editDescription = document.getElementById('editDescription');
    const errTitle = document.getElementById('errTitle');
    const errDesc = document.getElementById('errDesc');

    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const title = btn.dataset.title;
            const description = btn.dataset.description;

            editTitle.value = title ?? '';
            editDescription.value = description ?? '';
            errTitle.classList.add('hidden');
            errDesc.classList.add('hidden');

            editForm.action = `/tasks/${id}`;
            editModal.classList.remove('hidden');
            editModal.classList.add('flex');
        });
    });

    document.getElementById('cancelEdit').addEventListener('click', () => {
        editModal.classList.add('hidden');
        editModal.classList.remove('flex');
    });

    editForm.addEventListener('submit', (e) => {
        errTitle.classList.add('hidden');
        errDesc.classList.add('hidden');
        let ok = true;

        const titleVal = editTitle.value.trim();
        const descVal = editDescription.value.trim();

        if (!titleVal) {
            errTitle.textContent = 'Title is required.';
            errTitle.classList.remove('hidden');
            ok = false;
        } else if (titleVal.length > 255) {
            errTitle.textContent = 'Title must be under 255 characters.';
            errTitle.classList.remove('hidden');
            ok = false;
        }

        if (descVal.length > 2000) {
            errDesc.textContent = 'Description is too long.';
            errDesc.classList.remove('hidden');
            ok = false;
        }

        if (!ok) e.preventDefault();
    });

    const addForm = document.querySelector('form[action="{{ route('tasks.store') }}"]');
    addForm.addEventListener('submit', (e) => {
        const titleInput = addForm.querySelector('input[name="title"]');
        const descInput = addForm.querySelector('input[name="description"]');
        let valid = true;

        addForm.querySelectorAll('.text-red-600').forEach(p => p.remove());

        if (!titleInput.value.trim()) {
            const error = document.createElement('p');
            error.className = 'text-red-600 text-sm mt-1';
            error.textContent = 'Title is required.';
            titleInput.insertAdjacentElement('afterend', error);
            valid = false;
        } else if (titleInput.value.length > 255) {
            const error = document.createElement('p');
            error.className = 'text-red-600 text-sm mt-1';
            error.textContent = 'Title must be under 255 characters.';
            titleInput.insertAdjacentElement('afterend', error);
            valid = false;
        }

        if (descInput.value.length > 2000) {
            const error = document.createElement('p');
            error.className = 'text-red-600 text-sm mt-1';
            error.textContent = 'Description is too long.';
            descInput.insertAdjacentElement('afterend', error);
            valid = false;
        }

        if (!valid) e.preventDefault();
    });

    document.getElementById('clearForm').addEventListener('click', () => {
        addForm.reset();
        addForm.querySelectorAll('.text-red-600').forEach(p => p.remove());
    });

    setTimeout(function() {
        const alert = document.getElementById('alert');
        if (alert) alert.style.display = 'none';
    }, 5000);
</script>

@endpush