<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Task Manager</title>

  <script src="https://cdn.tailwindcss.com"></script>

  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

  <style>
    .task-dragging { opacity: 0.7; transform: scale(0.98); }
    .task-placeholder { border: 2px dashed #cbd5e1; }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">
  <div class="container mx-auto py-8 px-4">
    @yield('content')
  </div>

  @stack('scripts')
</body>
</html>
