<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    /**
     * Simpan rekening bank baru
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Akses Ditolak.');
        }

        $validated = $request->validate([
            'bank_code' => 'required|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_holder_name' => 'required|string|max:150',
            'no_whatsapp' => 'nullable|string|max:50',
            'is_default' => 'nullable|boolean',
            'notes' => 'nullable|string|max:255',
        ]);

        $isDefault = $request->boolean('is_default');

        // Jika diset default, reset default rekening lainnya
        if ($isDefault) {
            BankAccount::where('is_default', true)->update(['is_default' => false]);
        }

        // Jika ini rekening pertama, otomatis jadikan default
        if (BankAccount::count() === 0) {
            $isDefault = true;
        }

        $bankAccount = BankAccount::create([
            'user_id' => $user->id,
            'bank_code' => strtoupper($validated['bank_code']),
            'bank_name' => $validated['bank_name'] ?: strtoupper($validated['bank_code']),
            'account_number' => trim($validated['account_number']),
            'account_holder_name' => strtoupper(trim($validated['account_holder_name'])),
            'no_whatsapp' => $validated['no_whatsapp'] ? trim($validated['no_whatsapp']) : null,
            'is_default' => $isDefault,
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Rekening bank berhasil disimpan.',
                'data' => $bankAccount,
            ]);
        }

        return redirect()->back()->with('success', 'Rekening bank berhasil disimpan.');
    }

    /**
     * Perbarui rekening bank
     */
    public function update(Request $request, BankAccount $bankAccount)
    {
        $user = auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Akses Ditolak.');
        }

        $validated = $request->validate([
            'bank_code' => 'required|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_holder_name' => 'required|string|max:150',
            'no_whatsapp' => 'nullable|string|max:50',
            'is_default' => 'nullable|boolean',
            'notes' => 'nullable|string|max:255',
        ]);

        $isDefault = $request->boolean('is_default');

        if ($isDefault) {
            BankAccount::where('id', '!=', $bankAccount->id)->where('is_default', true)->update(['is_default' => false]);
        }

        $bankAccount->update([
            'bank_code' => strtoupper($validated['bank_code']),
            'bank_name' => $validated['bank_name'] ?: strtoupper($validated['bank_code']),
            'account_number' => trim($validated['account_number']),
            'account_holder_name' => strtoupper(trim($validated['account_holder_name'])),
            'no_whatsapp' => $validated['no_whatsapp'] ? trim($validated['no_whatsapp']) : null,
            'is_default' => $isDefault,
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Rekening bank berhasil diperbarui.',
                'data' => $bankAccount,
            ]);
        }

        return redirect()->back()->with('success', 'Rekening bank berhasil diperbarui.');
    }

    /**
     * Hapus rekening bank
     */
    public function destroy(BankAccount $bankAccount)
    {
        $user = auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Akses Ditolak.');
        }

        $bankAccount->delete();

        return redirect()->back()->with('success', 'Rekening bank berhasil dihapus.');
    }

    /**
     * Set sebagai rekening default
     */
    public function setDefault(BankAccount $bankAccount)
    {
        $user = auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Akses Ditolak.');
        }

        BankAccount::where('id', '!=', $bankAccount->id)->update(['is_default' => false]);
        $bankAccount->update(['is_default' => true]);

        return redirect()->back()->with('success', 'Rekening utama penarikan berhasil diatur.');
    }
}
