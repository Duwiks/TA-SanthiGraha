<?php

namespace App\Http\Controllers;

use App\Models\PaymentGroup;
use App\Models\Project;
use App\Models\Category;
use Illuminate\Http\Request;

class PaymentGroupController extends Controller
{
    /**
     * Daftar semua Payment Group (admin only).
     * Mendukung filter: project_id, category_id, payment_status.
     */
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $query = PaymentGroup::with(['project', 'category'])
            ->withCount(['transactions as approved_count' => function ($q) {
                $q->where('status', 'approved');
            }])
            ->with(['transactions' => function ($q) {
                $q->where('status', 'approved')->orderByDesc('updated_at')->orderByDesc('id');
            }])
            ->orderByDesc('updated_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                  ->orWhereHas('project', function ($qProj) use ($search) {
                      $qProj->where('project_name', 'like', "%{$search}%")
                            ->orWhere('location', 'like', "%{$search}%");
                  })
                  ->orWhereHas('category', function ($qCat) use ($search) {
                      $qCat->where('category_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $groups   = $query->paginate(12)->withQueryString();
        $projects = Project::orderBy('project_name')->get();
        $categories = Category::orderBy('category_name')->get();

        return view('admin.payment-groups.index', compact('groups', 'projects', 'categories'));
    }

    /**
     * Detail satu Payment Group beserta daftar transaksi member-nya.
     */
    public function show($id)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $group = PaymentGroup::with([
            'project',
            'category',
            'transactions' => function ($q) {
                $q->with(['user:id,name', 'approver:id,name'])
                  ->orderByDesc('updated_at')
                  ->orderByDesc('id');
            },
        ])->findOrFail($id);

        return view('admin.payment-groups.show', compact('group'));
    }

    /**
     * Hapus satu Payment Group beserta seluruh transaksi di dalamnya.
     */
    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $group = PaymentGroup::with('transactions')->findOrFail($id);

        \Illuminate\Support\Facades\DB::transaction(function () use ($group) {
            foreach ($group->transactions as $trx) {
                if ($trx->receipt_photo && !\Illuminate\Support\Str::contains($trx->description ?? '', '[Nota Merah]') && \Illuminate\Support\Facades\Storage::disk('public')->exists($trx->receipt_photo)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($trx->receipt_photo);
                }
                $trx->delete();
            }
            $group->delete();
        });

        return redirect()->route('payment-groups.index')->with('success', 'Kelompok pembayaran berhasil dihapus.');
    }
}
