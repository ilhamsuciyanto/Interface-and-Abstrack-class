<?php
require_once 'Pembayaran.php';
require_once 'Cetak.php';
require_once 'TransferBank.php';
require_once 'EWallet.php';
require_once 'QRIS.php';
require_once 'cod.php';
require_once 'Pajak.php';
require_once 'Diskon.php';

// ── Proses form ──────────────────────────────────────────────────────────────
$hasilProses = '';
$hasilStruk  = '';
$metodeLabel = '';
$adaDiskon   = false;
$pctDiskon   = 0;

if (isset($_POST['bayar'])) {
    $nominal = (float) $_POST['nominal'];
    $metode  = $_POST['metode'];

    // Instansiasi semua objek sekaligus (seperti pola index.php di gambar)
    $transfer = new TransferBank($nominal);
    $ewallet  = new EWallet($nominal);
    $qris     = new QRIS($nominal);
    $cod      = new COD($nominal);
    $pajak    = new Pajak($nominal);
    $diskon   = new Diskon($nominal);

    // Pilih objek sesuai metode yang dipilih
    switch ($metode) {
        case 'transfer':
            $transaksi   = $transfer;
            $metodeLabel = '🏦 Transfer Bank Konoha';
            break;
        case 'ewallet':
            $transaksi   = $ewallet;
            $metodeLabel = '🐸 Dompet Gama-chan';
            break;
        case 'qris':
            $transaksi   = $qris;
            $metodeLabel = '🔳 Segel QRIS';
            break;
        case 'cod':
            $transaksi   = $cod;
            $metodeLabel = '📦 COD Ninja';
            break;
        default:
            $transaksi   = $transfer;
            $metodeLabel = '🏦 Transfer Bank Konoha';
    }

    // Output prosesPembayaran() dan cetakStruk()
    $hasilProses = $transaksi->prosesPembayaran();
    $hasilStruk  = $transaksi->cetakStruk();

    // Pajak otomatis dihitung dari semua transaksi
    $pajakObj    = new Pajak($nominal);
    $hasilPajak  = $pajakObj->cetakStruk();

    // Diskon otomatis berdasarkan nominal
    $diskonObj   = new Diskon($nominal);
    $hasilDiskon = $diskonObj->cetakStruk();

    // Cek apakah dapat diskon (untuk animasi)
    if ($nominal >= 100000) {
        $adaDiskon = true;
        if ($nominal >= 1000000)     $pctDiskon = 20;
        elseif ($nominal >= 500000)  $pctDiskon = 10;
        else                         $pctDiskon = 5;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir Konoha: Sistem Pembayaran Ninja</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700;900&family=Rajdhani:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --orange: #FF6B00; --orange-glow: #FF8C00; --gold: #FFD700;
            --red: #CC0000;    --dark-red: #8B0000;    --leaf-green: #2ECC40;
            --navy: #0A0F1E;   --white: #F5F5F5;        --cream: #FFF8E1;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Rajdhani', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            padding: 24px 16px;
            background: var(--navy);
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed; inset: 0;
            background: url('https://images5.alphacoders.com/606/606259.jpg') center top / cover;
            filter: brightness(0.22) saturate(0.5) sepia(0.3);
            z-index: 0;
        }
        body::after {
            content: '';
            position: fixed; inset: 0;
            background:
                radial-gradient(ellipse at 50% 0%, rgba(180,0,0,.35) 0%, transparent 60%),
                linear-gradient(180deg, rgba(10,15,30,.3) 0%, rgba(10,15,30,.7) 100%);
            z-index: 1;
        }

        /* ── Particles ── */
        .particles { position: fixed; inset: 0; z-index: 2; pointer-events: none; }
        .particle  { position: absolute; border-radius: 50%; animation: floatP linear infinite; opacity: 0; }
        @keyframes floatP {
            0%   { bottom: -10px; opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: .6; }
            100% { bottom: 105vh; opacity: 0; transform: translateX(50px) scale(.4); }
        }

        /* ── Card ── */
        .ninja-cashier {
            position: relative; z-index: 10;
            width: 100%; max-width: 560px;
            background: rgba(10,15,30,.93);
            backdrop-filter: blur(14px);
            border-radius: 24px;
            padding: 38px 34px 34px;
            border: 1px solid rgba(255,215,0,.25);
            box-shadow: 0 0 40px rgba(255,107,0,.2), 0 0 80px rgba(200,0,0,.15), 0 30px 80px rgba(0,0,0,.8);
            animation: cardIn .8s cubic-bezier(.16,1,.3,1);
            align-self: flex-start;
        }
        @keyframes cardIn { from { opacity:0; transform:translateY(30px) scale(.97); } to { opacity:1; transform:translateY(0) scale(1); } }

        .corner { position:absolute; width:22px; height:22px; border-color:var(--gold); border-style:solid; opacity:.7; }
        .ctl { top:12px; left:12px;   border-width:2px 0 0 2px; border-radius:4px 0 0 0; }
        .ctr { top:12px; right:12px;  border-width:2px 2px 0 0; border-radius:0 4px 0 0; }
        .cbl { bottom:12px; left:12px; border-width:0 0 2px 2px; border-radius:0 0 0 4px; }
        .cbr { bottom:12px; right:12px; border-width:0 2px 2px 0; border-radius:0 0 4px 0; }

        /* ── Header ── */
        .logo { display:block; width:60px; height:60px; margin:0 auto 12px; animation:pulse 3s ease-in-out infinite; }
        @keyframes pulse {
            0%,100% { filter:drop-shadow(0 0 10px rgba(255,107,0,.7)); }
            50%      { filter:drop-shadow(0 0 22px rgba(255,215,0,1)); transform:scale(1.06); }
        }
        h1 { font-family:'Cinzel Decorative',serif; font-size:clamp(20px,4.5vw,28px); font-weight:900; color:var(--white);
             text-shadow:0 0 20px var(--orange),2px 2px 0 var(--dark-red); letter-spacing:3px; text-align:center; }
        h1 span { color:var(--orange); }
        .subtitle { font-size:10px; letter-spacing:5px; color:rgba(255,215,0,.42); text-transform:uppercase; text-align:center; margin-top:5px; }
        .divider  { display:flex; align-items:center; gap:10px; margin:18px 0; color:var(--gold); font-size:12px; }
        .divider::before,.divider::after { content:''; flex:1; height:1px; background:linear-gradient(to right,transparent,var(--gold),transparent); }

        /* ── Form ── */
        .form-group { display:flex; flex-direction:column; gap:8px; margin-bottom:16px; }
        label { font-size:11px; font-weight:700; letter-spacing:3px; text-transform:uppercase; color:var(--gold); }
        .iw  { position:relative; }
        .ico { position:absolute; left:13px; top:50%; transform:translateY(-50%); font-size:16px; pointer-events:none; }
        input[type=number], select {
            width:100%; padding:13px 14px 13px 42px;
            background:rgba(255,255,255,.05);
            border:1.5px solid rgba(255,215,0,.3); border-radius:12px;
            color:var(--white); font-family:'Rajdhani',sans-serif; font-size:15px; font-weight:600;
            transition:all .25s; appearance:none;
        }
        input[type=number]::placeholder { color:rgba(255,255,255,.3); }
        input[type=number]:focus, select:focus {
            outline:none; border-color:var(--orange);
            background:rgba(255,107,0,.08);
            box-shadow:0 0 0 3px rgba(255,107,0,.15);
        }
        select option { background:#1a2335; color:var(--white); font-weight:600; }
        .sel-wrap { position:relative; }
        .sel-wrap::after { content:'▼'; position:absolute; right:14px; top:50%; transform:translateY(-50%); font-size:10px; color:var(--gold); pointer-events:none; }

        /* ── Button ── */
        .btn-jutsu {
            width:100%; padding:15px; margin-top:8px;
            background:linear-gradient(135deg, var(--red), var(--orange), var(--gold));
            background-size:200%; border:none; border-radius:14px;
            color:var(--white); font-family:'Cinzel Decorative',serif; font-size:13px; font-weight:700;
            letter-spacing:2px; cursor:pointer; transition:all .3s;
            box-shadow:0 4px 20px rgba(255,107,0,.5);
            animation:btnG 4s ease infinite;
        }
        @keyframes btnG { 0%,100%{background-position:0% 50%;} 50%{background-position:100% 50%;} }
        .btn-jutsu:hover  { transform:translateY(-2px); box-shadow:0 8px 30px rgba(255,107,0,.7); }
        .btn-jutsu:active { transform:translateY(2px); }

        /* ── Receipt ── */
        .receipt {
            margin-top:24px;
            background:linear-gradient(135deg, rgba(255,248,225,.97), rgba(255,240,200,.99));
            border:2px solid var(--gold); border-radius:14px;
            color:#2d1500; font-family:'Courier New',monospace;
            position:relative; overflow:hidden;
            box-shadow:0 8px 30px rgba(0,0,0,.5);
            animation:rcIn .5s cubic-bezier(.16,1,.3,1);
        }
        @keyframes rcIn { from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);} }
        .receipt::before { content:'忍'; position:absolute; top:14px; right:18px; font-size:38px; color:rgba(204,0,0,.1); font-weight:900; }
        .rc-head  { padding:16px 20px 10px; border-bottom:1px dashed rgba(139,0,0,.3); text-align:center; }
        .rc-title { font-family:'Cinzel Decorative',serif; font-size:10px; letter-spacing:4px; text-transform:uppercase; color:var(--dark-red); margin-bottom:8px; }
        .badge    { display:inline-block; background:var(--red); color:#fff; font-size:9px; letter-spacing:2px; padding:3px 12px; border-radius:20px; font-family:'Rajdhani',sans-serif; font-weight:700; }
        .rc-body  { padding:14px 20px; }
        .rc-section { margin-bottom:14px; }
        .rc-section-title { font-size:9px; letter-spacing:3px; text-transform:uppercase; color:var(--dark-red); margin-bottom:6px; font-weight:700; }
        .rc-row   { display:flex; justify-content:space-between; padding:3px 0; font-size:12px; border-bottom:1px dotted rgba(139,0,0,.12); }
        .rc-row:last-child { border-bottom:none; }
        .rc-row .lbl { color:#5a2d00; }
        .rc-row .val { font-weight:700; color:#2d1500; font-size:11px; word-break:break-all; text-align:right; max-width:60%; }
        .rc-sep   { border:none; border-top:1px dashed rgba(139,0,0,.35); margin:10px 0; }
        .diskon-chip  { display:inline-block; background:rgba(0,120,60,.15); border:1px solid rgba(0,120,60,.35); color:#004d20; font-size:9px; padding:1px 7px; border-radius:10px; margin-left:5px; font-weight:700; }
        .pajak-chip   { display:inline-block; background:rgba(180,80,0,.12); border:1px solid rgba(180,80,0,.3); color:#7a3a00; font-size:9px; padding:1px 7px; border-radius:10px; margin-left:5px; font-weight:700; }
        .rc-foot  { padding:10px 20px 14px; border-top:1px dashed rgba(139,0,0,.3); text-align:center; font-size:10px; color:#8B4513; letter-spacing:2px; }

        /* ── Rasengan Overlay ── */
        .overlay { position:fixed; inset:0; z-index:999; display:none; align-items:center; justify-content:center; cursor:pointer; }
        .overlay.show { display:flex; }
        .ov-bg { position:absolute; inset:0; background:rgba(0,0,40,.86); }
        .rasengan-wrap { position:relative; z-index:2; display:flex; flex-direction:column; align-items:center; gap:14px; animation:rsIn .4s cubic-bezier(.16,1,.3,1); }
        @keyframes rsIn { from{transform:scale(0);}to{transform:scale(1);} }
        .rasengan { width:200px; height:200px; border-radius:50%; position:relative; display:flex; align-items:center; justify-content:center; }
        .rring { position:absolute; border-radius:50%; border:3px solid rgba(100,210,255,.75); animation:spin linear infinite; }
        .rring:nth-child(1){width:200px;height:200px;animation-duration:1.4s;}
        .rring:nth-child(2){width:155px;height:155px;animation-duration:1.1s;animation-direction:reverse;}
        .rring:nth-child(3){width:110px;height:110px;animation-duration:.85s;}
        @keyframes spin { from{transform:rotate(0deg);}to{transform:rotate(360deg);} }
        .rcore { width:72px;height:72px;border-radius:50%;background:radial-gradient(circle,#fff 0%,rgba(100,210,255,1) 40%,rgba(0,100,255,.5) 100%);animation:cPulse .5s ease-in-out infinite alternate;z-index:2; }
        @keyframes cPulse { from{transform:scale(1);}to{transform:scale(1.2);} }
        .sparks { position:absolute; inset:0; pointer-events:none; }
        .spark  { position:absolute; width:4px; height:4px; border-radius:50%; background:#00d4ff; top:50%; left:50%; animation:sparkFly ease-out infinite; }
        @keyframes sparkFly { 0%{transform:translate(0,0) scale(1);opacity:1;}100%{transform:translate(var(--tx),var(--ty)) scale(0);opacity:0;} }
        .ov-pct  { font-family:'Cinzel Decorative',serif; font-size:20px; color:#fff; text-shadow:0 0 24px #00bfff; letter-spacing:2px; text-align:center; }
        .ov-sub  { font-size:12px; color:rgba(100,210,255,.8); letter-spacing:3px; text-transform:uppercase; text-align:center; font-family:'Rajdhani',sans-serif; }
        .ov-yell { font-family:'Cinzel Decorative',serif; font-size:17px; color:var(--gold); text-shadow:0 0 16px var(--orange); text-align:center; animation:yPulse .6s ease-in-out infinite alternate; }
        @keyframes yPulse { from{transform:scale(1);}to{transform:scale(1.08);} }
        .tap-hint { position:absolute; bottom:20px; font-size:10px; color:rgba(255,255,255,.3); letter-spacing:2px; z-index:2; font-family:'Rajdhani',sans-serif; }

        /* ── Footer ── */
        .card-footer { margin-top:22px; text-align:center; font-size:10px; letter-spacing:2px; color:rgba(255,215,0,.25); text-transform:uppercase; }
    </style>
</head>
<body>

<!-- Particles -->
<div class="particles" id="particles"></div>

<div class="ninja-cashier">
    <div class="corner ctl"></div><div class="corner ctr"></div>
    <div class="corner cbl"></div><div class="corner cbr"></div>

    <!-- Header -->
    <div style="text-align:center;margin-bottom:24px;">
        <svg class="logo" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <defs><radialGradient id="lg" cx="50%" cy="50%" r="50%"><stop offset="0%" stop-color="#FFD700"/><stop offset="100%" stop-color="#FF6B00"/></radialGradient></defs>
            <circle cx="50" cy="50" r="46" fill="none" stroke="url(#lg)" stroke-width="3"/>
            <path d="M50 15C50 15,72 20,80 38C88 56,76 72,58 74C58 74,62 80,50 88C38 80,42 74,42 74C24 72,12 56,20 38C28 20,50 15,50 15Z" fill="url(#lg)" opacity=".9"/>
            <path d="M50 28C50 28,64 33,68 45C72 57,65 67,56 68L50 78L44 68C35 67,28 57,32 45C36 33,50 28,50 28Z" fill="#0A0F1E" opacity=".7"/>
            <circle cx="50" cy="50" r="6" fill="url(#lg)"/>
        </svg>
        <p style="font-size:10px;letter-spacing:6px;color:rgba(255,215,0,.42);text-transform:uppercase;margin-bottom:5px;">木ノ葉隠れの里 • 忍者財務局</p>
        <h1>KASIR <span>KONOHA</span></h1>
        <p class="subtitle">Sistem Pembayaran Ninja</p>
    </div>

    <div class="divider">⚡ MISI PEMBAYARAN ⚡</div>

    <!-- Form POST -->
    <form method="POST" action="">
        <div class="form-group">
            <label for="nominal">Nominal Ryo (両)</label>
            <div class="iw">
                <span class="ico">🍥</span>
                <input type="number" id="nominal" name="nominal"
                       placeholder="Masukkan jumlah Ryo..." required min="1"
                       value="<?= isset($_POST['nominal']) ? htmlspecialchars($_POST['nominal']) : '' ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="metode">Teknik Pembayaran</label>
            <div class="iw sel-wrap">
                <span class="ico">📜</span>
                <select id="metode" name="metode" required>
                    <option value="" disabled <?= !isset($_POST['metode']) ? 'selected' : '' ?>>— Pilih Jutsu Pembayaran —</option>
                    <option value="transfer" <?= (isset($_POST['metode']) && $_POST['metode']==='transfer') ? 'selected' : '' ?>>🏦  Transfer Bank Konoha</option>
                    <option value="ewallet"  <?= (isset($_POST['metode']) && $_POST['metode']==='ewallet')  ? 'selected' : '' ?>>🐸  Dompet Gama-chan</option>
                    <option value="qris"     <?= (isset($_POST['metode']) && $_POST['metode']==='qris')     ? 'selected' : '' ?>>🔳  Segel QRIS</option>
                    <option value="cod"      <?= (isset($_POST['metode']) && $_POST['metode']==='cod')      ? 'selected' : '' ?>>📦  COD Ninja</option>
                </select>
            </div>
        </div>

        <button type="submit" name="bayar" class="btn-jutsu">⚔️ Proses Misi Pembayaran</button>
    </form>

    <!-- ── Receipt Output ── -->
    <?php if (isset($_POST['bayar'])): ?>
    <div class="receipt">
        <div class="rc-head">
            <p class="rc-title">📋 Laporan Misi Pembayaran</p>
            <span class="badge"><?= $metodeLabel ?></span>
        </div>
        <div class="rc-body">

            <!-- SECTION 1: Pembayaran Utama -->
            <div class="rc-section">
                <p class="rc-section-title">⚔️ Transaksi Utama</p>
                <div class="rc-row">
                    <span class="lbl">Proses Pembayaran</span>
                    <span class="val"><?= htmlspecialchars($hasilProses) ?></span>
                </div>
                <div class="rc-row">
                    <span class="lbl">Struk</span>
                    <span class="val"><?= htmlspecialchars($hasilStruk) ?></span>
                </div>
            </div>

            <hr class="rc-sep">

            <!-- SECTION 2: Diskon Otomatis -->
            <?php
            $diskonObj2  = new Diskon((float)$_POST['nominal']);
            $prosesDiskon = $diskonObj2->prosesPembayaran();
            $strukDiskon  = $diskonObj2->cetakStruk();
            ?>
            <div class="rc-section">
                <p class="rc-section-title">⚡ Diskon Otomatis <span class="diskon-chip">AUTO</span></p>
                <div class="rc-row">
                    <span class="lbl">Kalkulasi</span>
                    <span class="val"><?= htmlspecialchars($prosesDiskon) ?></span>
                </div>
                <div class="rc-row">
                    <span class="lbl">Struk Diskon</span>
                    <span class="val"><?= htmlspecialchars($strukDiskon) ?></span>
                </div>
            </div>

            <hr class="rc-sep">

            <!-- SECTION 3: Pajak Otomatis -->
            <?php
            $pajakObj2   = new Pajak((float)$_POST['nominal']);
            $prosesPajak = $pajakObj2->prosesPembayaran();
            $strukPajak  = $pajakObj2->cetakStruk();
            ?>
            <div class="rc-section">
                <p class="rc-section-title">📜 Pajak PPN 11% <span class="pajak-chip">AUTO</span></p>
                <div class="rc-row">
                    <span class="lbl">Kalkulasi</span>
                    <span class="val"><?= htmlspecialchars($prosesPajak) ?></span>
                </div>
                <div class="rc-row">
                    <span class="lbl">Struk Pajak</span>
                    <span class="val"><?= htmlspecialchars($strukPajak) ?></span>
                </div>
            </div>

        </div>
        <div class="rc-foot">木ノ葉 • Transaksi Sah • 忍</div>
    </div>
    <?php endif; ?>

    <div class="card-footer">木ノ葉 &bull; Konoha Financial Division &bull; 忍</div>
</div>

<!-- Rasengan Overlay (muncul jika dapat diskon) -->
<?php if ($adaDiskon): ?>
<div class="overlay show" id="overlay" onclick="document.getElementById('overlay').classList.remove('show')">
    <div class="ov-bg"></div>
    <div class="rasengan-wrap">
        <div class="ov-pct">DISKON <?= $pctDiskon ?>% AKTIF!</div>
        <div class="rasengan">
            <div class="rring"></div><div class="rring"></div><div class="rring"></div>
            <div class="sparks" id="sparks"></div>
            <div class="rcore"></div>
        </div>
        <div class="ov-sub">
            <?php
            if ($pctDiskon === 20)     echo 'Kage-level — Shadow Clone Discount!';
            elseif ($pctDiskon === 10) echo 'Jonin-level — Jonin Special Discount!';
            else                       echo 'Chunin-level — Chunin Discount!';
            ?>
        </div>
        <div class="ov-yell">DATTEBAYO! DAPAT DISKON!</div>
    </div>
    <div class="tap-hint">— TAP UNTUK LANJUT —</div>
</div>
<?php endif; ?>

<script>
// Particles
(function(){
    const pts=document.getElementById('particles');
    [{s:3,c:'#FF6B00',l:'10%',d:8,dl:0},{s:2,c:'#FFD700',l:'22%',d:12,dl:1},
     {s:4,c:'#CC0000',l:'38%',d:10,dl:2},{s:2,c:'#FF8C00',l:'52%',d:9,dl:.5},
     {s:3,c:'#FFD700',l:'66%',d:11,dl:3},{s:5,c:'#2ECC40',l:'80%',d:13,dl:4},
     {s:2,c:'#FF6B00',l:'88%',d:7,dl:1.5},{s:3,c:'#CC0000',l:'30%',d:10,dl:.2}
    ].forEach(p=>{
        const el=document.createElement('div');el.className='particle';
        el.style.cssText=`width:${p.s}px;height:${p.s}px;background:${p.c};left:${p.l};animation-duration:${p.d}s;animation-delay:${p.dl}s;`;
        pts.appendChild(el);
    });
})();

// Rasengan sparks
(function(){
    const sparks=document.getElementById('sparks');
    if(!sparks) return;
    for(let i=0;i<18;i++){
        const s=document.createElement('div');s.className='spark';
        const angle=(i/18)*Math.PI*2;
        const dist=70+Math.random()*40;
        s.style.cssText=`--tx:${Math.cos(angle)*dist}px;--ty:${Math.sin(angle)*dist}px;animation-delay:${Math.random()*.6}s;animation-duration:${1+Math.random()*.8}s;`;
        sparks.appendChild(s);
    }
    // Auto-close setelah 3.5 detik
    setTimeout(()=>{
        const ov=document.getElementById('overlay');
        if(ov) ov.classList.remove('show');
    }, 3500);
})();
</script>
</body>
</html>