<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('project_name', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%");
        }

        $projects = $query->orderBy('created_at', 'desc')->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.projects.index', compact('projects'));
    }

    public function create()
    {
        return view('admin.projects.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_name' => 'required|string|max:150|unique:projects',
            'location' => 'nullable|string|max:150',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ], [
            'project_name.required' => 'Nama Proyek wajib diisi.',
            'project_name.unique' => 'Nama Proyek sudah ada.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak valid.',
        ]);

        Project::create([
            'project_name' => $request->project_name,
            'location' => $request->location,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'aktif',
        ]);

        return redirect()->route('projects.index')->with('success', 'Proyek berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $project = Project::findOrFail($id);

        // Proteksi backend: project selesai tidak bisa diedit
        if ($project->is_finished) {
            return redirect()
                ->route('projects.index')
                ->with('error', 'Proyek sudah selesai dan tidak dapat diedit.');
        }

        return view('admin.projects.form', compact('project'));
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        // Proteksi backend: project selesai tidak bisa diupdate
        if ($project->is_finished) {
            return redirect()
                ->route('projects.index')
                ->with('error', 'Proyek sudah selesai dan tidak dapat diedit.');
        }

        $request->validate([
            'project_name' => 'required|string|max:150|unique:projects,project_name,' . $id,
            'location' => 'nullable|string|max:150',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ], [
            'project_name.required' => 'Nama Proyek wajib diisi.',
            'project_name.unique' => 'Nama Proyek sudah ada.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak valid.',
        ]);

        $project->update([
            'project_name' => $request->project_name,
            'location' => $request->location,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->route('projects.index')->with('success', 'Proyek berhasil diupdate!');
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);

        // Proteksi backend: proyek yang sudah selesai tidak dapat dihapus
        if ($project->is_finished) {
            return redirect()
                ->route('projects.index')
                ->with('error', 'Proyek sudah selesai dan tidak dapat dihapus.');
        }

        // Cek apakah proyek masih digunakan
        if ($project->transactions()->exists()) {
            return redirect()
                ->route('projects.index')
                ->with('error', 'Proyek tidak dapat dihapus karena masih digunakan pada transaksi.');
        }

        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Proyek berhasil dihapus!');
    }

    /**
     * Tandai project sebagai selesai (admin action).
     * Setelah ini, project tidak bisa diperpanjang atau diedit.
     */
    public function complete($id)
    {
        $project = Project::findOrFail($id);

        if ($project->is_finished) {
            return redirect()
                ->route('projects.index')
                ->with('error', 'Proyek sudah berstatus selesai.');
        }

        $project->update(['status' => 'selesai']);

        return redirect()
            ->route('projects.index')
            ->with('success', "Proyek \"{$project->project_name}\" telah ditandai selesai.");
    }

    /**
     * Perpanjang deadline project (hanya untuk project yang belum selesai).
     */
    public function extend(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        // Proteksi backend: tidak bisa perpanjang project yang sudah selesai
        if ($project->is_finished) {
            return redirect()
                ->route('projects.index')
                ->with('error', 'Proyek sudah selesai dan tidak dapat diperpanjang.');
        }

        $request->validate([
            'new_end_date' => 'required|date|after:today',
        ], [
            'new_end_date.required' => 'Tanggal deadline baru wajib diisi.',
            'new_end_date.after'    => 'Tanggal deadline baru harus setelah hari ini.',
        ]);

        $project->update(['end_date' => $request->new_end_date]);

        return redirect()
            ->route('projects.index')
            ->with('success', "Deadline proyek \"{$project->project_name}\" berhasil diperpanjang.");
    }
}
