<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');

        $query = Task::orderBy('order', 'asc')->orderBy('created_at', 'asc');

        if ($filter === 'completed') {
            $query->where('completed', true);
        } elseif ($filter === 'incomplete') {
            $query->where('completed', false);
        }

        $tasks = $query->get();

        return view('tasks.index', compact('tasks', 'filter'));
    }

    public function store(TaskRequest $request)
    {
        $data = $request->validated();
        $max = Task::max('order');
        $data['order'] = is_null($max) ? 1 : ($max + 1);
        $task = Task::create($data);

        return redirect()->route('tasks.index')->with('success', 'Task created.');
    }

    public function update(TaskRequest $request, Task $task)
    {
        $data = $request->validated();
        $task->update($data);

        return redirect()->route('tasks.index')->with('success', 'Task updated.');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task deleted.');
    }

    public function toggle(Task $task)
    {
        $task->update(['completed' => !$task->completed]);
        return redirect()->route('tasks.index');
    }

    public function reorder(Request $request): JsonResponse
    {
        $order = $request->input('order', []);
        if (!is_array($order)) {
            return response()->json(['error' => 'Invalid order array.'], 422);
        }

        foreach ($order as $index => $id) {
            Task::where('id', $id)->update(['order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }
}
