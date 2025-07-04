$(document).ready(function () {
    window.tableDUM = $('#jurnalDUM').DataTable({
        paging: false,
        searching: true,
        ordering: false,
        info: false,
        autoWidth: false,
        dom: 'Bfrtip',
        buttons: ['copyHtml5', 'excelHtml5', 'csvHtml5', 'pdfHtml5']
    });

    window.tableDUK = $('#jurnalDUK').DataTable({
        paging: false,
        searching: true,
        ordering: false,
        info: false,
        autoWidth: false,
        dom: 'Bfrtip',
        buttons: ['copyHtml5', 'excelHtml5', 'csvHtml5', 'pdfHtml5']
    });

    // Filter berdasarkan bulan
    $('#bulan').on('change', function () {
        var bulan = this.value;
        tableDUM.column(2).search(bulan, true, false).draw();
        tableDUK.column(2).search(bulan, true, false).draw();
        hitungTotalSaldo();
    });
});

function tambahDUM() {
    tambahBaris('#jurnalDUM', tableDUM);
}

function tambahDUK() {
    tambahBaris('#jurnalDUK', tableDUK);
}

function tambahBaris(tableId, table) {
    var bulanDipilih = $('#bulan').val();
    if (!bulanDipilih) {
        alert('Pilih bulan terlebih dahulu!');
        return;
    }
    var rowCount = table.rows().count() + 1;
    var rowData = [
        rowCount, '<input type="text" value="" />', bulanDipilih
    ];

    for (var i = 1; i <= 31; i++) {
        rowData.push('<input type="number" value="0" min="0" onchange="hitungTotal(this)" />');
    }

    rowData.push('<input type="number" value="0" readonly />');
    table.row.add(rowData).draw(false);
}

function hitungTotal(input) {
    var row = $(input).closest('tr');
    var total = 0;
    row.find('td:not(:last-child) input[type="number"]').each(function () {
        total += parseFloat($(this).val()) || 0;
    });
    row.find('td:last-child input').val(total);
    hitungTotalSaldo();
}
$(document).on('change', 'input[type="number"]', function () {
    console.log("🔥 Input berubah:", $(this).val());
    updateDatabase(this, 'DUM');
});
function hitungTotalSaldo() {
    var totalDUM = 0, totalDUK = 0;
    $('#jurnalDUM tbody tr').each(function () {
        totalDUM += parseFloat($(this).find('td:last-child input').val()) || 0;
    });
    $('#jurnalDUK tbody tr').each(function () {
        totalDUK += parseFloat($(this).find('td:last-child input').val()) || 0;
    });
    $('#totalDUM').val(totalDUM);
    $('#totalDUK').val(totalDUK);
}

function updateDatabase(input, table) {
    var row = $(input).closest('tr');
    var tanggal = row.find('.tanggal-input').val() || new Date().getDate(); // Ambil angka tanggal
    var bulan = $('#bulanSelect').val(); // Pastikan ini sesuai dengan dropdown bulan
    var uraian = row.find('.uraian-input').val() || "Tidak Ada";
    var kategori = row.find('.kategori-select').val() || "DUM"; // Pastikan ada kategori
    var jumlah = parseInt($(input).val()) || 0; // Pastikan jumlah dalam angka

    var requestData = {
        tanggal: tanggal,
        bulan: bulan, // Cek apakah ini perlu dikirim
        uraian: uraian,
        kategori: kategori,
        jumlah: jumlah
    };

    console.log("📤 Data yang dikirim ke server:", requestData);

    $.ajax({
        url: '/api/updateKas',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(requestData),
        success: function (response) {
            console.log("✅ Data berhasil diperbarui:", response);
        },
        error: function (xhr, status, error) {
            console.error("❌ Error:", error);
            console.log("📝 Response dari server:", xhr.responseText);
        }
    });
}



