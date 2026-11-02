<?php

namespace App\Modules\Storefront\Models;

use Illuminate\Support\Facades\DB;

class Review
{
    // Kiểm tra xem khách hàng đã có đơn hàng 'Đã giao' hay chưa (Chống review ảo)
    public static function checkUserDeliveredOrder($maKH)
    {
        return DB::table('PHIEU_BAN')
            ->where('MaKH', $maKH)
            ->where('TrangThai', N'Đã giao')
            ->exists();
    }

    // Lưu đánh giá mới vào bảng DANH_GIA_KHACH_HANG
    public static function createReview($data)
    {
        return DB::table('DANH_GIA_KHACH_HANG')->insertGetId([
            'MaKH' => $data['MaKH'],
            'SoSao' => $data['SoSao'],
            'NoiDung' => $data['NoiDung'] ?? null,
            'NgayDanhGia' => now()
        ]);
    }
}