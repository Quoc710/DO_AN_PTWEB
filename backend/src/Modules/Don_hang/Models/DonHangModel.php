<?php

namespace App\Modules\Don_hang\Models;

class DonHangModel
{
    public int $maPhieuBan;
    public int $maKH;
    public string $ngayBan;
    public float $tongTien;

    public function __construct(array $data = [])
    {
        $this->maPhieuBan = (int)($data['MaPhieuBan'] ?? 0);
        $this->maKH = (int)($data['MaKH'] ?? 0);
        $this->ngayBan = (string)($data['NgayBan'] ?? '');
        $this->tongTien = (float)($data['TongTien'] ?? 0.0);
    }
}
