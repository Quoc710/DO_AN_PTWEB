<?php

namespace App\Modules\Storefront\Controllers; // 🟢 Sửa lại Namespace theo cấu trúc thư mục Modules của bạn

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StorefrontController extends Controller
{
    // 1. Lấy danh sách sản phẩm và biến thể kèm cảnh báo cuộn lẻ
    public function getProducts(Request $request)
    {
        $products = DB::table('SAN_PHAM')
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

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    // 2. Kiểm tra quyền viết đánh giá (Chặn Review Ảo)
    public function checkReviewPermission(Request $request)
    {
        $maKH = $request->query('MaKH');

        $hasDeliveredOrder = DB::table('PHIEU_BAN')
            ->where('MaKH', $maKH)
            ->where('TrangThai', N'Đã giao')
            ->exists();

        return response()->json([
            'allow_review' => $hasDeliveredOrder,
            'message' => $hasDeliveredOrder ? 'Hợp lệ: Đã nhận hàng' : 'Bị khóa: Chưa có đơn hàng nào được giao thành công'
        ]);
    }

    // 3. Gửi đánh giá sản phẩm vào bảng DANH_GIA_KHACH_HANG
    public function storeReview(Request $request)
    {
        $request->validate([
            'MaKH' => 'required|integer',
            'SoSao' => 'required|integer|min:1|max:5',
            'NoiDung' => 'nullable|string'
        ]);

        // Kiểm tra bảo mật trước khi lưu đánh giá
        $checkOrder = DB::table('PHIEU_BAN')
            ->where('MaKH', $request->MaKH)
            ->where('TrangThai', N'Đã giao')
            ->exists();

        if (!$checkOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền đánh giá vì chưa mua hàng hoặc đơn chưa giao thành công!'
            ], 403);
        }

        $maDanhGia = DB::table('DANH_GIA_KHACH_HANG')->insertGetId([
            'MaKH' => $request->MaKH,
            'SoSao' => $request->SoSao,
            'NoiDung' => $request->NoiDung,
            'NgayDanhGia' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã gửi đánh giá thành công!',
            'MaDanhGia' => $maDanhGia
        ]);
    }
}