<?php
require_once 'koneksi.php';
$id_tim = $_SESSION['id_tim']; 

if (isset($_POST['simpan_play'])) {
    $nama_s = mysqli_real_escape_string($koneksi, $_POST['nama_strategi']);
    $kat = $_POST['kategori'];
    $desc = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $nama_file = "";

    if (!empty($_POST['gambar_base64'])) {
        $img = $_POST['gambar_base64'];
        $img = str_replace('data:image/png;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        $nama_file = 'play_' . time() . '.png';
        file_put_contents('uploads/' . $nama_file, $data);
    } 
    else if (!empty($_FILES['gambar_skema']['name'])) {
        $nama_file = time() . '_' . $_FILES['gambar_skema']['name'];
        move_uploaded_file($_FILES['gambar_skema']['tmp_name'], 'uploads/' . $nama_file);
    }

    if ($nama_file != "") {
        mysqli_query($koneksi, "INSERT INTO playbook (id_tim, nama_strategi, kategori, deskripsi, url_gambar_skema) 
                                VALUES ('$id_tim', '$nama_s', '$kat', '$desc', '$nama_file')");
        echo "<script>alert('Playbook berhasil ditambahkan!'); window.location.href='index.php?menu=playbook';</script>";
    }
}

$result_play = mysqli_query($koneksi, "SELECT * FROM playbook WHERE id_tim = '$id_tim' ORDER BY id_play DESC");
?>

<div class="row g-4">
    <div class="col-lg-7 mb-4">
        <div class="card border-0 shadow-sm rounded-4 p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="fas fa-edit text-success me-2"></i>Flag Playmaker v3.1</h5>
                <span class="badge bg-dark text-warning border">Pro Mode</span>
            </div>
            
            <form method="POST" enctype="multipart/form-data" id="playbookForm">
                <div class="row g-2 mb-2">
                    <div class="col-8"><input type="text" name="nama_strategi" class="form-control" placeholder="Nama Play..." required></div>
                    <div class="col-4">
                        <select name="kategori" class="form-select">
                            <option value="Offense">Offense</option>
                            <option value="Defense">Defense</option>
                        </select>
                    </div>
                </div>

                <div class="card bg-light p-2 mb-2 border rounded-3">
                    <div class="d-flex flex-wrap gap-1 mb-2 border-bottom pb-2">
                        <button type="button" class="btn btn-sm btn-outline-primary fw-bold" onclick="setStamp('C', 'offense')">C</button>
                        <button type="button" class="btn btn-sm btn-outline-primary fw-bold" onclick="setStamp('QB', 'offense')">QB</button>
                        <button type="button" class="btn btn-sm btn-outline-primary fw-bold" onclick="setStamp('X', 'offense')">X</button>
                        <button type="button" class="btn btn-sm btn-outline-primary fw-bold" onclick="setStamp('Y', 'offense')">Y</button>
                        <button type="button" class="btn btn-sm btn-outline-primary fw-bold" onclick="setStamp('Z', 'offense')">Z</button>
                        <span class="border-end mx-1"></span>
                        <button type="button" class="btn btn-sm btn-outline-danger fw-bold" onclick="setStamp('B', 'defense')">B</button>
                        <button type="button" class="btn btn-sm btn-outline-danger fw-bold" onclick="setStamp('D', 'defense')">D</button>
                        <button type="button" class="btn btn-sm btn-outline-danger fw-bold" onclick="setStamp('S', 'defense')">S</button>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-dark active" id="tool-solid" onclick="setDrawMode('solid')"><i class="fas fa-arrow-right"></i> Route</button>
                            <button type="button" class="btn btn-outline-dark" id="tool-dashed" onclick="setDrawMode('dashed')"><i class="fas fa-ellipsis-h"></i> Motion</button>
                            <button type="button" class="btn btn-outline-danger" id="tool-eraser" onclick="setEraser()"><i class="fas fa-eraser"></i> Hapus</button>
                        </div>
                        <div class="d-flex gap-1 align-items-center">
                            <button type="button" class="btn btn-sm rounded-circle border shadow-sm" onclick="changeColor('white')" style="background: white; width:28px; height:28px;"></button>
                            <button type="button" class="btn btn-sm rounded-circle border shadow-sm" onclick="changeColor('#ffc107')" style="background: #ffc107; width:28px; height:28px;"></button>
                            <button type="button" class="btn btn-sm btn-link text-danger" onclick="clearCanvas()">Reset Lapangan</button>
                        </div>
                    </div>
                </div>

                <div class="border rounded-3 overflow-hidden shadow-sm mb-3" style="background-color: #1e6d3c;">
                    <canvas id="playBoard" width="700" height="450" style="cursor: crosshair; width: 100%; height: auto; touch-action: none;"></canvas>
                </div>
                
                <input type="hidden" name="gambar_base64" id="gambar_base64">
                <textarea name="deskripsi" class="form-control mb-3" rows="1" placeholder="Catatan tugas pemain..."></textarea>
                <button type="submit" name="simpan_play" class="btn btn-success btn-lg w-100 fw-bold rounded-pill" onclick="saveCanvasData()">Simpan Playbook</button>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="row g-2">
            <?php if(mysqli_num_rows($result_play) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result_play)): ?>
                    <div class="col-12 mb-2">
                        <div class="card shadow-sm border-0 rounded-4 overflow-hidden" style="transition:0.3s">
                            <div style="height: 140px; background: url('uploads/<?= $row['url_gambar_skema'] ?>') center/cover no-repeat; background-color: #1e6d3c; border-bottom: 2px solid #ffc107;"></div>
                            <div class="card-body p-2 px-3">
                                <span class="badge <?= $row['kategori'] == 'Offense' ? 'bg-primary' : 'bg-danger' ?> float-end"><?= $row['kategori'] ?></span>
                                <h6 class="fw-bold mb-0 text-dark"><?= $row['nama_strategi'] ?></h6>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const canvas = document.getElementById('playBoard');
    const ctx = canvas.getContext('2d');
    const FIELD_COLOR = "#1e6d3c";
    
    let isDrawing = false;
    let currentMode = 'draw'; 
    let currentLineType = 'solid';
    let currentColor = 'white';
    let currentStampLabel = '';
    let currentStampType = '';

    function setCanvasDefaults() {
        ctx.strokeStyle = currentColor; 
        // Kalau sedang menghapus, ukuran garis diperbesar biar gampang hapusnya
        ctx.lineWidth = (currentColor === FIELD_COLOR) ? 20 : 3; 
        ctx.lineCap = "round";
        ctx.lineJoin = "round";
        if(currentLineType === 'dashed' && currentColor !== FIELD_COLOR){
            ctx.setLineDash([12, 8]);
        } else {
            ctx.setLineDash([]); 
        }
    }

    function drawFieldTemplate() {
        ctx.fillStyle = FIELD_COLOR;
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.strokeStyle = "rgba(255, 255, 255, 0.3)";
        ctx.lineWidth = 1; ctx.setLineDash([]);
        const w = canvas.width; const h = canvas.height;
        for (let y = 0; y < h; y += 50) {
            ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(w, y); ctx.stroke();
            for (let x = 50; x < w; x += 50) { ctx.fillRect(x, y - 2, 2, 4); }
        }
        ctx.lineWidth = 2;
        ctx.beginPath(); ctx.moveTo(20, 0); ctx.lineTo(20, h); ctx.stroke();
        ctx.beginPath(); ctx.moveTo(w-20, 0); ctx.lineTo(w-20, h); ctx.stroke();
    }
    drawFieldTemplate();

    function getMousePos(canvas, evt) {
        const rect = canvas.getBoundingClientRect();
        let clientX = evt.clientX; let clientY = evt.clientY;
        if (evt.touches && evt.touches.length > 0) {
            clientX = evt.touches[0].clientX; clientY = evt.touches[0].clientY;
        }
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        return { x: (clientX - rect.left) * scaleX, y: (clientY - rect.top) * scaleY };
    }

    function drawStamp(x, y, text, type) {
        ctx.beginPath();
        ctx.arc(x, y, 16, 0, Math.PI * 2);
        ctx.fillStyle = (type === 'offense') ? "#0d6efd" : "#dc3545";
        ctx.fill();
        ctx.strokeStyle = "white";
        ctx.lineWidth = 2; ctx.setLineDash([]); ctx.stroke();
        ctx.fillStyle = "white";
        ctx.font = "bold 16px Arial"; ctx.textAlign = "center"; ctx.textBaseline = "middle";
        ctx.fillText(text, x, y);
    }

    function startPosition(e) {
        if(e.type.includes('touch')) e.preventDefault();
        const pos = getMousePos(canvas, e);
        if (currentMode === 'stamp') {
            drawStamp(pos.x, pos.y, currentStampLabel, currentStampType);
            return;
        }
        isDrawing = true;
        ctx.beginPath(); setCanvasDefaults(); ctx.moveTo(pos.x, pos.y);
    }

    function draw(e) {
        if (!isDrawing || currentMode === 'stamp') return;
        if(e.type.includes('touch')) e.preventDefault();
        const pos = getMousePos(canvas, e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
    }

    function endPosition() { isDrawing = false; ctx.beginPath(); }

    canvas.addEventListener('mousedown', startPosition);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', endPosition);
    canvas.addEventListener('touchstart', startPosition, {passive: false});
    canvas.addEventListener('touchmove', draw, {passive: false});
    canvas.addEventListener('touchend', endPosition);

    // --- KONTROL FITUR ---
    function setStamp(label, type) {
        currentMode = 'stamp';
        currentStampLabel = label; currentStampType = type;
        updateUI('stamp');
    }

    function setDrawMode(type) {
        currentMode = 'draw';
        currentLineType = type;
        // Pastikan warna bukan warna rumput saat pindah mode
        if(currentColor === FIELD_COLOR) currentColor = 'white';
        updateUI(type);
    }

    function setEraser() {
        currentMode = 'draw';
        currentColor = FIELD_COLOR; // Warna penghapus = warna rumput
        updateUI('eraser');
    }

    function changeColor(color) { 
        currentColor = color; 
        setDrawMode(currentLineType); 
    }

    function updateUI(activeTool) {
        // Reset semua tombol
        document.getElementById('tool-solid').className = "btn btn-outline-dark";
        document.getElementById('tool-dashed').className = "btn btn-outline-dark";
        document.getElementById('tool-eraser').className = "btn btn-outline-danger";

        if(activeTool === 'solid') document.getElementById('tool-solid').className = "btn btn-dark active";
        if(activeTool === 'dashed') document.getElementById('tool-dashed').className = "btn btn-dark active";
        if(activeTool === 'eraser') document.getElementById('tool-eraser').className = "btn btn-danger active";
    }
    
    function clearCanvas() { if(confirm("Hapus semua?")) { drawFieldTemplate(); } }
    function saveCanvasData() { document.getElementById('gambar_base64').value = canvas.toDataURL("image/png"); }
</script>