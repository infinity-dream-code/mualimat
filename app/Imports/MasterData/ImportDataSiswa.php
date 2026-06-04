<?php

namespace App\Imports\MasterData;

use App\Models\mst_kelas;
use App\Models\scctcust;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportDataSiswa implements WithMultipleSheets, ToCollection, WithHeadingRow
{
    public function sheets(): array
    {
        return [
            0 => $this,
        ];
    }

    public function collection(Collection $collection): void
    {
        $cacheKey = 'import_data_siswa';

        $requiredKeys = ['nama', 'unit', 'kelas', 'kelompok', 'angkatan'];

        $processedData = [];

        foreach ($collection as $row) {
            if ($row->filter()->isEmpty()) continue;

            $rowData = $row->toArray();

            if (count(array_intersect_key(array_flip($requiredKeys), $rowData)) !== count($requiredKeys)) {
                continue;
            }

            $rowData['unit'] = trim((string) ($rowData['unit'] ?? ''));
            $rowData['kelas'] = is_numeric($rowData['kelas'] ?? null)
                ? (string) (int) $rowData['kelas']
                : trim((string) ($rowData['kelas'] ?? ''));
            $rowData['kelompok'] = trim((string) ($rowData['kelompok'] ?? ''));

            $rowData['status'] = 1;
            $status_ket = null;

            $nis = isset($row['nis']) ? trim((string)$row['nis']) : null;
            $nodaftar = isset($row['nodaftar']) ? trim((string)$row['nodaftar']) : null;

            if (!$nis && !$nodaftar) {
                $rowData['status'] = 0;
                $status_ket = 'NIS &/ NODAFTAR tidak boleh kosong';
            }

            if ($nis && !is_numeric($nis)) {
                $rowData['status'] = 0;
                if (!empty($status_ket)) $status_ket .= ', ';
                $status_ket .= "NIS harus berupa angka";
            } elseif ($nis) {
                $rowData['nis'] = (string)$rowData['nis'];
                $checkData = scctcust::where('NOCUST', $rowData['nis'])->first();
                if ($checkData) {
                    $rowData['status'] = 2;
                    if (!empty($status_ket)) $status_ket .= ', ';
                    $status_ket .= "Siswa dengan NIS {$rowData['nis']} sudah ada, data akan diupdate";

                }
            }

            if ($nodaftar && !is_numeric($nodaftar)) {
                $rowData['status'] = 0;
                if (!empty($status_ket)) $status_ket .= ', ';
                $status_ket .= "NODAFTAR harus berupa angka";
            } elseif ($nodaftar) {
                $rowData['nodaftar'] = (string)$rowData['nodaftar'];
                $checkData = scctcust::where('NUM2ND', $rowData['nodaftar'])->first();
                if ($checkData) {
                    $rowData['status'] = 2;
                    if (!empty($status_ket)) $status_ket .= ', ';
                    $status_ket .= "Siswa dengan nodaftar {$rowData['nodaftar']} sudah ada, data akan diupdate";

                }
            }

            $rowData['ortu'] = trim((string) ($rowData['ortu'] ?? $rowData['genus'] ?? $rowData['ayah'] ?? '')) ?: null;

            $matchedKelas = mst_kelas::findForImport(
                $rowData['unit'],
                $rowData['kelas'],
                $rowData['kelompok'],
            );

            if (!$matchedKelas) {
                $rowData['status'] = 0;
                if (!empty($status_ket)) {
                    $status_ket .= ', ';
                }
                $status_ket .= sprintf(
                    'Kelas tidak ditemukan (Unit: %s, Kelas: %s, Kelompok: %s). Buat dulu di Master Kelas.',
                    $rowData['unit'],
                    $rowData['kelas'],
                    $rowData['kelompok'],
                );
            }


            $rowData['keterangan'] = $status_ket;
            $processedData[] = $rowData;
        }

        if (!empty($processedData)) {
            Cache::put($cacheKey, $processedData, now()->addMinutes(60));
        }
    }

    public function headingRow(): int
    {
        return 1;
    }
}
