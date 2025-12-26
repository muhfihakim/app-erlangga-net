<div class="row">
    @if ($user->jabatan !== 'Teknisi')
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
                <div class="inner">
                    <!-- Menampilkan Total PO Bulan Ini -->
                    <h3>{{ $totalPoThisMonth }}</h3>

                    <p>Total PO Bulan Ini</p>
                </div>
                <a href="#" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
                <div class="inner">
                    <!-- Menampilkan Total PO Bulan Sebelumnya dan Persentase Perubahan -->
                    <h3>{{ $totalPoPreviousMonth }}
                        <!-- Menampilkan persentase perubahan di sini -->
                        <sup style="font-size: 20px;">
                            @if ($percentageChange > 0)
                                +{{ round($percentageChange, 1) }}% <i class="fas fa-arrow-up"></i>
                            @elseif ($percentageChange < 0)
                                {{ round($percentageChange, 1) }}% <i class="fas fa-arrow-down"></i>
                            @else
                                0%
                            @endif
                        </sup>
                    </h3>
                    <p>Total PO Bulan Sebelumnya</p>
                </div>
                <a href="#" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalAktivasiThisMonth }}</h3>
                    <p>Total Aktivasi Bulan Ini</p>
                </div>
                <a href="#" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $totalPendingThisMonth }}</h3>
                    <p>Total Pending Bulan Ini</p>
                </div>
                <a href="#" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    @endif
</div>
