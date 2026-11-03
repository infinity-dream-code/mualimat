<?php

namespace App\Support;

use App\Models\mst_kelas;
use App\Models\mst_sekolah;
use Illuminate\Support\Facades\Auth;

class UnitScope
{
    public static function current(): ?string
    {
        if (!Auth::check()) {
            return null;
        }

        $unit = trim((string) (Auth::user()->unit ?? ''));

        return $unit !== '' ? $unit : null;
    }

    public static function sekolahOptions(?string $unitScope = null)
    {
        $unitScope ??= self::current();

        return mst_sekolah::query()
            ->when($unitScope, fn ($q) => $q->whereRaw('TRIM(CODE01) = ?', [$unitScope]))
            ->get();
    }

    public static function kelasOptions(?string $unitScope = null)
    {
        $unitScope ??= self::current();

        return mst_kelas::query()
            ->when($unitScope, function ($q) use ($unitScope) {
                $desc = mst_sekolah::query()
                    ->whereRaw('TRIM(CODE01) = ?', [$unitScope])
                    ->value('DESC01');
                $q->where(function ($inner) use ($unitScope, $desc) {
                    $inner->whereRaw('TRIM(unit) = ?', [$unitScope]);
                    if ($desc) {
                        $inner->orWhereRaw('UPPER(TRIM(unit)) = ?', [strtoupper(trim((string) $desc))]);
                    }
                });
            })
            ->orderBy('unit')
            ->orderBy('jenjang')
            ->orderBy('kelas')
            ->get();
    }

    public static function apply($query, ?string $unitScope = null, string $column = 'scctcust.CODE01'): void
    {
        $unitScope ??= self::current();
        if (!$unitScope) {
            return;
        }

        $query->whereRaw('TRIM(CAST(' . $column . ' AS CHAR)) = ?', [$unitScope]);
    }

    /** Blade snippet: auto-select + disable other unit options */
    public static function bladeLockScript(?string $unitScope = null): string
    {
        $unitScope ??= self::current();
        if (!$unitScope) {
            return '';
        }

        $json = json_encode($unitScope);

        return <<<HTML
            <script>
            document.addEventListener('DOMContentLoaded', function () {
                const unitScope = {$json};
                const \$unitSelect = \$("[name='filter[unit]']");
                if (\$unitSelect.length && unitScope) {
                    \$unitSelect.val(unitScope).trigger('change');
                    \$unitSelect.find('option').each(function () {
                        if (\$(this).val() !== unitScope && \$(this).val() !== 'all') {
                            \$(this).prop('disabled', true);
                        }
                    });
                }
            });
            </script>
            HTML;
    }
}
