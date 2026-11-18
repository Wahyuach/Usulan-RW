<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pemilihan Ketua RW</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f0f4f8;
      color: #1e293b;
      margin: 0;
      padding: 20px;
      text-align: center;
    }
    .container {
      max-width: 900px;
      margin: 0 auto;
    }
    h1 {
      color: #0f172a;
      margin-bottom: 5px;
    }
    p.subtitle {
      color: #64748b;
      margin-bottom: 30px;
    }
    .kandidat-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 40px;
    }
    .card {
      background: #ffffff;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
      border: 2px solid transparent;
      transition: transform 0.2s, border-color 0.2s;
    }
    .card:hover {
      transform: translateY(-4px);
      border-color: #2563eb;
    }
    .nomor-urut {
      background-color: #2563eb;
      color: white;
      width: 40px;
      height: 40px;
      line-height: 40px;
      border-radius: 50%;
      font-weight: bold;
      font-size: 1.2rem;
      margin: 0 auto 15px auto;
    }
    .foto-kandidat {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      background-color: #e2e8f0;
      margin-bottom: 15px;
      border: 3px solid #cbd5e1;
    }
    .nama-kandidat {
      font-size: 1.25rem;
      font-weight: bold;
      margin: 5px 0;
    }
    .alamat {
      font-size: 0.9rem;
      color: #475569;
      margin-bottom: 20px;
      min-height: 40px;
    }
    .btn-pilih {
      background-color: #16a34a;
      color: white;
      border: none;
      padding: 12px 24px;
      font-size: 1rem;
      font-weight: bold;
      border-radius: 8px;
      cursor: pointer;
      width: 100%;
    }
    .btn-pilih:hover {
      background-color: #15803d;
    }
    .panel-panitia {
      background: #ffffff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
      margin-top: 30px;
    }
    .hasil-box {
      display: none;
      margin-top: 20px;
      text-align: left;
    }
    .bar-container {
      background-color: #e2e8f0;
      border-radius: 8px;
      overflow: hidden;
      margin-bottom: 15px;
    }
    .bar {
      background-color: #2563eb;
      color: white;
      padding: 8px 12px;
      font-weight: bold;
      white-space: nowrap;
      transition: width 0.4s ease;
    }
    .btn-sekunder {
      background-color: #475569;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 6px;
      cursor: pointer;
      margin: 5px;
      font-size: 0.9rem;
    }
    .btn-danger {
      background-color: #dc2626;
    }
  </style>
</head>
<body>

<div class="container">
  <h1>USULAN CALON KETUA RW 08</h1>
  <p class="subtitle">Silakan klik tombol <strong>PILIH</strong> pada calon Ketua RW pilihan Anda</p>

  <div class="kandidat-grid" id="daftarKandidat"></div>

  <div class="panel-panitia">
    <strong>Panel Panitia</strong><br><br>
    <button class="btn-sekunder" onclick="toggleHasil()">Lihat / Sembunyikan Hasil Suara</button>
    <button class="btn-sekunder btn-danger" onclick="resetSuara()">Reset Semua Suara</button>

    <div class="hasil-box" id="boxHasil">
      <h3>Perolehan Suara Sementara (Total: <span id="totalSuara">0</span> Suara)</h3>
      <div id="grafikHasil"></div>
    </div>
  </div>
</div>

<script>
  const kandidat = [
    {
      id: 1,
      nama: "Bpk. Dimas",
      alamat: "Blok F 15",
      foto: "https://placeholder.com"
    },
    {
      id: 2,
      nama: "Bpk. Abdul Rohman",
      alamat: "Blok F 12A",
      foto: "https://placeholder.com"
    },
    {
      id: 3,
      nama: "Bpk. Catur",
      alamat: "Blok G 1",
      foto: "https://placeholder.com"
    },
    {
      id: 4,
      nama: "Bpk. Miskan",
      alamat: "Blok C 37",
      foto: "https://placeholder.com"
    },
    {
      id: 5,
      nama: "Bpk. Iwan",
      alamat: "Blok C 06",
      foto: "https://placeholder.com"
    },
    {
      id: 6,
      nama: "Bpk. Winarno",
      alamat: "Blok C 15",
      foto: "https://placeholder.com"
    },
    {
      id: 7,
      nama: "Bpk. Joko Waluyo",
      alamat: "Blok D 24",
      foto: "https://placeholder.com"
    },
    {
      id: 8,
      nama: "Bpk. Budi",
      alamat: "Blok F 4",
      foto: "https://placeholder.com"
    },
    {
      id: 9,
      nama: "Bpk. Caswan",
      alamat: "Blok C 14",
      foto: "https://placeholder.com"
    },
    {
      id: "tidak-memilih",
      nama: "Tidak Memilih",
      alamat: "",
      foto: "https://placeholder.com",
      tidakMemilih: true
    },
    
  ];

  const PIN_PANITIA = "1234"; // Ganti PIN panitia di sini

  // Ambil data suara dari penyimpanan browser atau buat baru jika kosong
  let suara = JSON.parse(localStorage.getItem('voting_rw_data')) || {};
  kandidat.forEach(k => {
    if (suara[k.id] === undefined) suara[k.id] = 0;
  });

  // Render kartu kandidat ke layar
  function tampilkanKandidat() {
    const container = document.getElementById('daftarKandidat');
    container.innerHTML = '';
    kandidat.forEach(k => {
      container.innerHTML += `
        <div class="card">
          <div class="nomor-urut">${k.tidakMemilih ? "-" : k.id}</div>
          <img src="${k.foto}" alt="${k.nama}" class="foto-kandidat">
          <div class="nama-kandidat">${k.nama}</div>
          <div class="alamat">${k.alamat ? `Alamat: ${k.alamat}` : "&nbsp;"}</div>
          <button class="btn-pilih" onclick="coblos('${k.id}', '${k.nama}', ${k.tidakMemilih || false})">${k.tidakMemilih ? "TIDAK MEMILIH" : `PILIH CALON NO. ${k.id}`}</button>
        </div>
      `;
    });
  }

  // Fungsi saat warga menekan tombol Pilih
  function coblos(id, nama, tidakMemilih = false) {
    const pilihan = tidakMemilih ? "Tidak Memilih" : `Calon Nomor ${id}:\n${nama}`;
    const yakin = confirm(`Apakah Anda yakin memilih:\n${pilihan}?`);
    if (yakin) {
      suara[id]++;
      localStorage.setItem('voting_rw_data', JSON.stringify(suara));
      alert("Terima kasih! Suara Anda telah berhasil direkam.");
      updateGrafik();
    }
  }

  // Menampilkan atau menyembunyikan hasil (dilindungi PIN)
  function toggleHasil() {
    const box = document.getElementById('boxHasil');
    if (box.style.display === 'block') {
      box.style.display = 'none';
      return;
    }
    const pin = prompt("Masukkan PIN Panitia untuk melihat hasil:");
    if (pin === PIN_PANITIA) {
      updateGrafik();
      box.style.display = 'block';
    } else if (pin !== null) {
      alert("PIN salah!");
    }
  }

  // Memperbarui tampilan grafik perolehan suara
  function updateGrafik() {
    const grafik = document.getElementById('grafikHasil');
    const totalEl = document.getElementById('totalSuara');
    let total = 0;
    kandidat.forEach(k => total += suara[k.id]);
    totalEl.innerText = total;

    grafik.innerHTML = '';
    kandidat.forEach(k => {
      const jumlah = suara[k.id];
      const persen = total === 0 ? 0 : Math.round((jumlah / total) * 100);
      grafik.innerHTML += `
        <div style="margin-bottom: 5px;"><strong>${k.tidakMemilih ? k.nama : `No. ${k.id} - ${k.nama}`}</strong> (${jumlah} suara / ${persen}%)</div>
        <div class="bar-container">
          <div class="bar" style="width: ${Math.max(persen, 5)}%">${persen}%</div>
        </div>
      `;
    });
  }

  // Menghapus semua data suara (untuk memulai pemilihan baru)
  function resetSuara() {
    const pin = prompt("Masukkan PIN Panitia untuk MENGHAPUS semua suara:");
    if (pin === PIN_PANITIA) {
      if (confirm("PERINGATAN: Semua data suara akan kembali ke 0. Lanjutkan?")) {
        kandidat.forEach(k => suara[k.id] = 0);
        localStorage.setItem('voting_rw_data', JSON.stringify(suara));
        updateGrafik();
        alert("Data suara telah di-reset.");
      }
    } else if (pin !== null) {
      alert("PIN salah!");
    }
  }

  // Jalankan saat halaman dibuka
  tampilkanKandidat();
</script>

</body>
</html>
