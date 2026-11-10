<?php

namespace App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Support;

use Illuminate\Support\HtmlString;

class ModalDescriptions
{
    public static function initialStockSubmit(): HtmlString
    {
        return new HtmlString(
            'Initial stock hanya boleh dilakukan sekali setiap cabangnya dan hanya jika stok masih 0 dicabang tersebut. <br>' .
            'dan Demi Berjalannya kalkulasi stock dengan benar, ' .
            'Data yang disimpan TIDAK BISA DIUBAH kembali. <br>' .
            'Pastikan semua isian sudah benar.<br>'.
            'Dan jika ingin tambahan Stock, lanjutke menu purchase'
        );
    }
    public static function initialStockCancel(): HtmlString

    {
        return new HtmlString(
            'Initial Stock akan dihapus dan.<br>' .
            '<b>data akan di reset kembali.</b><br>'
        );
    }

    public static function stockAuditCancel(): HtmlString
    {
        return new HtmlString(
            'Stock Audit akan di cenceled dan.<br>' .
            '<b>Yakin dengan langkah ini?.</b><br>'
        );
    }

    public static function stockAuditPost(): HtmlString
    {
        return new HtmlString(
            'Yakin postting ke tabel Stock?. <br>' .
            'Stock tidak bisa diubah setelah di post, ' .
            'Pastikan semua isian sudah benar.'
        );
    }

    public static function mulaiAudit(): HtmlString
    {
        return new HtmlString(
            'Memulai stock opname, Mohon transaksi di hentikan sementara. <br>' .
            'dan Demi Berjalannya kalkulasi stock dengan benar, ' .
            'Data yang disimpan TIDAK BISA DIUBAH kembali. <br>' .
            'Pastikan semua isian sudah benar.<br>'.
            'Dan jika ingin tambahan Stock, lanjutke menu purchase'
        );
    }

    public static function selesaiAudit(): HtmlString
    {
        return new HtmlString(
            'Stock opname akan diselesaikan, dan data akan dikunci.<br>' .
            'pastikan semua isian sudah benar.<br>' .
            'Data yang disimpan TIDAK BISA DIUBAH kembali. <br>'
        );
    }

}
