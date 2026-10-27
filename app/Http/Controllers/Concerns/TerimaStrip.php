<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

/**
 * Aturan "semua field wajib diisi, tetapi boleh diisi tanda - jika datanya tidak ada".
 *
 * - Field teks biasa: "-" langsung lolos validasi dan disimpan apa adanya.
 * - Field bertipe (tanggal, angka, NIK, pilihan/enum): "-" tidak cocok dengan tipe kolomnya,
 *   jadi aturannya diganti "nullable" dan nilainya disimpan sebagai NULL.
 *   Di halaman detail, NULL sudah tampil sebagai "—".
 */
trait TerimaStrip
{
    /** True jika isian hanya berisi tanda "-" (artinya: data memang tidak ada). */
    protected function isStrip(Request $request, string $field): bool
    {
        $nilai = $request->input($field);

        return is_string($nilai) && trim($nilai) === '-';
    }

    /** Longgarkan aturan field bertipe yang diisi "-" supaya tidak ditolak oleh date/digits/in/integer. */
    protected function izinkanStrip(Request $request, array $rules, array $fieldBertipe): array
    {
        foreach ($fieldBertipe as $field) {
            if ($this->isStrip($request, $field)) {
                $rules[$field] = ['nullable'];
            }
        }

        return $rules;
    }

    /** Ubah "-" pada field bertipe menjadi NULL sebelum disimpan ke database. */
    protected function terapkanStrip(Request $request, array $validated, array $fieldBertipe): array
    {
        foreach ($fieldBertipe as $field) {
            if ($this->isStrip($request, $field)) {
                $validated[$field] = null;
            }
        }

        return $validated;
    }
}
