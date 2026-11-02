<?php

namespace App\Modules\Storefront\Models;

use Illuminate\Support\Facades\DB;

class Product
{
    // Lấy danh sách sản phẩm kết hợp biến thể và cảnh báo cuộn lẻ
    public static function getAllWithRolls()
    {
        return DB::table('SAN_PHAM')
            ->join('BIEN_THE_SP', 'SAN_PHAM.MaSP', '=', 'BIEN_THE_SP.MaSP')
            ->leftJoin('CUON_VAI', 'BIEN_THE_SP.MaBienThe', '=', 'CUON_VAI.MaBienThe')
            ->select(
                'SAN_PHAM.MaSP',
                'SAN_PHAM.TenSP',
                'SAN_PHAM.NhomSP',
                'BIEN_THE_SP.MaBienThe',
                'BIEN_THE_SP.ChatLieuSize',
                'BIEN_THE_SP.MauSac',
                'CUON_VAI.DoDaiConLai as DoDaiLienMachToiDa'
            )
            ->get();
    }
}