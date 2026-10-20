<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\House;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class SurveyorManagementController extends Controller
{
    /**
     * Daftar seluruh surveyor & ringkasan kinerja.
     */
    public function index(Request $request)
    {
        $query = User::whereHas('roles', function ($q) {
            $q->where('slug', 'surveyor');
        })->withCount([
            'houses as total_houses_count',
            'houses as draft_houses_count' => function ($q) {
                $q->where('status', 'draft');
            },
            'houses as submitted_houses_count' => function ($q) {
                $q->where('status', 'submitted');
            },
            'houses as verified_houses_count' => function ($q) {
                $q->where('status', 'verified');
            },
            'houses as published_houses_count' => function ($q) {
                $q->where('status', 'published');
            },
            'houses as revision_houses_count' => function ($q) {
                $q->where('status', 'revision');
            },
        ])->latest('id');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->input('status') !== null && $request->input('status') !== '') {
            $query->where('is_active', filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN));
        }

        $surveyors = $query->paginate(15)->withQueryString();

        // Stats Global Surveyor
        $totalSurveyors = User::whereHas('roles', fn($q) => $q->where('slug', 'surveyor'))->count();
        $activeSurveyors = User::whereHas('roles', fn($q) => $q->where('slug', 'surveyor'))->where('is_active', true)->count();

        return view('admin.surveyors.index', compact('surveyors', 'totalSurveyors', 'activeSurveyors'));
    }

    /**
     * Form tambah surveyor baru.
     */
    public function create()
    {
        return view('admin.surveyors.create');
    }

    /**
     * Form tambah surveyor banyak sekaligus (Bulk Create).
     */
    public function bulkCreate()
    {
        return view('admin.surveyors.bulk-create');
    }

    /**
     * Download template CSV untuk bulk import surveyor.
     */
    public function downloadCsvTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_surveyor_rtlh.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, ['nama', 'email', 'password']);
            fputcsv($file, ['Ahmad Subagja', 'ahmad.surveyor@cirebon-rtlh.test', 'password123']);
            fputcsv($file, ['Budi Santoso', 'budi.surveyor@cirebon-rtlh.test', 'password123']);
            fputcsv($file, ['Citra Dewi', 'citra.surveyor@cirebon-rtlh.test', 'password123']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Simpan surveyor banyak sekaligus dari Teks Baris atau CSV.
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'default_password' => ['nullable', 'string', 'min:6'],
            'csv_file' => ['nullable', 'file', 'mimes:csv,txt', 'max:2048'],
            'bulk_text' => ['nullable', 'string'],
        ]);

        $defaultPassword = $request->input('default_password') ?: 'password123';
        $rows = [];

        // 1. Proses dari File CSV jika diunggah
        if ($request->hasFile('csv_file')) {
            $path = $request->file('csv_file')->getRealPath();
            if (($handle = fopen($path, 'r')) !== false) {
                // Read potential BOM
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }

                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    if (empty($data) || (count($data) === 1 && $data[0] === null)) {
                        continue;
                    }

                    $name = trim($data[0] ?? '');
                    $email = trim($data[1] ?? '');
                    $password = trim($data[2] ?? '');

                    // Skip header row if present
                    if (strtolower($name) === 'nama' || strtolower($name) === 'name' || strtolower($email) === 'email') {
                        continue;
                    }

                    if (!empty($name) && !empty($email)) {
                        $rows[] = [
                            'name' => $name,
                            'email' => $email,
                            'password' => !empty($password) ? $password : $defaultPassword,
                        ];
                    }
                }
                fclose($handle);
            }
        }

        // 2. Proses dari Teks Baris (Textarea)
        if ($request->filled('bulk_text')) {
            $lines = explode("\n", $request->input('bulk_text'));
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $cols = array_map('trim', explode(',', $line));
                $name = $cols[0] ?? '';
                $email = $cols[1] ?? '';
                $password = $cols[2] ?? '';

                if (strtolower($name) === 'nama' || strtolower($name) === 'name' || strtolower($email) === 'email') {
                    continue;
                }

                if (!empty($name) && !empty($email)) {
                    $rows[] = [
                        'name' => $name,
                        'email' => $email,
                        'password' => !empty($password) ? $password : $defaultPassword,
                    ];
                }
            }
        }

        if (empty($rows)) {
            return back()->with('err', 'Tidak ada data surveyor valid yang ditemukan. Masukkan teks baris atau unggah file CSV.')->withInput();
        }

        $surveyorRole = Role::where('slug', 'surveyor')->first();
        $createdCount = 0;
        $skippedCount = 0;

        foreach ($rows as $row) {
            // Validasi email sintaks & keunikan
            if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                $skippedCount++;
                continue;
            }

            if (User::where('email', $row['email'])->exists()) {
                $skippedCount++;
                continue;
            }

            $user = User::create([
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => Hash::make($row['password']),
                'is_active' => true,
            ]);

            if ($surveyorRole) {
                $user->roles()->sync([$surveyorRole->id]);
            }

            $createdCount++;

            AuditLogger::log(
                $request->user(),
                'BULK_CREATE_SURVEYOR',
                'User',
                $user->id,
                null,
                $user->toArray()
            );
        }

        $msg = "Berhasil menambahkan {$createdCount} surveyor baru secara masal.";
        if ($skippedCount > 0) {
            $msg .= " ({$skippedCount} data dilewati karena email tidak valid atau sudah terdaftar).";
        }

        return redirect()->route('admin.surveyors.index')->with('ok', $msg);
    }

    /**
     * Simpan surveyor baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $surveyorRole = Role::where('slug', 'surveyor')->first();
        if ($surveyorRole) {
            $user->roles()->sync([$surveyorRole->id]);
        }

        if ($request->boolean('is_admin')) {
            $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
            $user->roles()->syncWithoutDetaching([$adminRole->id]);
        }

        AuditLogger::log(
            $request->user(),
            'CREATE_SURVEYOR',
            'User',
            $user->id,
            null,
            $user->toArray()
        );

        return redirect()
            ->route('admin.surveyors.index')
            ->with('ok', "Surveyor {$user->name} (ID: #{$user->id}) berhasil ditambahkan.");
    }

    /**
     * Detail surveyor & log history pekerjaannya.
     */
    public function show(User $surveyor, Request $request)
    {
        $housesQuery = House::with('region')
            ->where('created_by', $surveyor->id)
            ->latest('id');

        if ($request->filled('house_status')) {
            $housesQuery->where('status', $request->input('house_status'));
        }

        $houses = $housesQuery->paginate(10, ['*'], 'houses_page')->withQueryString();

        $logs = AuditLog::where('user_id', $surveyor->id)
            ->latest('id')
            ->paginate(15, ['*'], 'logs_page')
            ->withQueryString();

        $stats = [
            'total' => House::where('created_by', $surveyor->id)->count(),
            'draft' => House::where('created_by', $surveyor->id)->where('status', 'draft')->count(),
            'submitted' => House::where('created_by', $surveyor->id)->where('status', 'submitted')->count(),
            'verified' => House::where('created_by', $surveyor->id)->where('status', 'verified')->count(),
            'published' => House::where('created_by', $surveyor->id)->where('status', 'published')->count(),
            'revision' => House::where('created_by', $surveyor->id)->where('status', 'revision')->count(),
        ];

        return view('admin.surveyors.show', compact('surveyor', 'houses', 'logs', 'stats'));
    }

    /**
     * Form edit surveyor.
     */
    public function edit(User $surveyor)
    {
        return view('admin.surveyors.edit', compact('surveyor'));
    }

    /**
     * Update data surveyor.
     */
    public function update(Request $request, User $surveyor)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($surveyor->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldData = $surveyor->toArray();

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $request->boolean('is_active'),
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $surveyor->update($updateData);

        if ($request->has('role_settings_submitted')) {
            $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
            if ($request->boolean('is_admin')) {
                $surveyor->roles()->syncWithoutDetaching([$adminRole->id]);
            } else {
                if ($surveyor->id !== auth()->id()) {
                    $surveyor->roles()->detach($adminRole->id);
                }
            }
        }

        AuditLogger::log(
            $request->user(),
            'UPDATE_SURVEYOR',
            'User',
            $surveyor->id,
            $oldData,
            $surveyor->fresh()->toArray()
        );

        return redirect()
            ->route('admin.surveyors.show', $surveyor)
            ->with('ok', "Data surveyor {$surveyor->name} berhasil diperbarui.");
    }

    /**
     * Toggle status aktif / non-aktif surveyor.
     */
    public function toggleStatus(Request $request, User $surveyor)
    {
        $oldData = $surveyor->toArray();
        $surveyor->is_active = !$surveyor->is_active;
        $surveyor->save();

        $statusLabel = $surveyor->is_active ? 'diaktifkan' : 'dinonaktifkan';

        AuditLogger::log(
            $request->user(),
            'TOGGLE_SURVEYOR_STATUS',
            'User',
            $surveyor->id,
            $oldData,
            $surveyor->fresh()->toArray()
        );

        return back()->with('ok', "Akun surveyor {$surveyor->name} telah {$statusLabel}.");
    }

    /**
     * Promosi / cabut hak akses Administrator dari akun surveyor.
     */
    public function toggleAdmin(Request $request, User $surveyor)
    {
        // Pencegahan lockout: Admin tidak boleh mencabut hak admin akunnya sendiri
        if ($surveyor->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat mencabut hak akses Administrator dari akun Anda sendiri.');
        }

        $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
        $oldData = $surveyor->toArray();
        $isAlreadyAdmin = $surveyor->hasRole('admin');

        if ($isAlreadyAdmin) {
            $surveyor->roles()->detach($adminRole->id);
            $action = 'DEMOTE_FROM_ADMIN';
            $message = "Hak akses Administrator untuk {$surveyor->name} berhasil dicabut. Akun kembali menjadi Surveyor biasa.";
        } else {
            $surveyor->roles()->syncWithoutDetaching([$adminRole->id]);
            $action = 'PROMOTE_TO_ADMIN';
            $message = "Akses Administrator berhasil diberikan kepada {$surveyor->name}. Akun ini sekarang memiliki kendali penuh setara Admin.";
        }

        AuditLogger::log(
            $request->user(),
            $action,
            'User',
            $surveyor->id,
            $oldData,
            $surveyor->fresh()->toArray()
        );

        return back()->with('ok', $message);
    }
}
