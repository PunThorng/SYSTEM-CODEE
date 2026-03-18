<?php
declare(strict_types=1);
// home.php — Professional Restaurant Dashboard

// Check if being included in dashboard or accessed directly
$is_standalone = !defined('DASHBOARD_INCLUDED');

// Include database connection only if not already included
if ($is_standalone) {
    // Output HTML header for standalone access
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dashboard - Restaurant Management</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <?php
    require_once __DIR__ . '/../database/backend/db_connect.php';
} else {
    require_once __DIR__ . '/../database/backend/db_connect.php';
}

// Get current date info
$today = date('Y-m-d');

// Default values in case of errors
$receivedOrders   = 0;
$completeOrders   = 0;
$pendingOrders    = 0;
$cancelledOrders  = 0;
$processingOrders = 0;
$totalRevenue     = 0;
$todayOrders      = 0;
$todayRevenue     = 0;
$registeredUsers  = 0;
$activeUsers      = 0;
$foodMenuItems    = 0;

// Get order statistics
try {
    $orderStatsQuery = "SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as complete_orders,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
        SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
        COALESCE(SUM(total_price), 0) as total_revenue
        FROM orders";
    $orderStatsResult = $conn->query($orderStatsQuery);
    if ($orderStatsResult && $orderStats = $orderStatsResult->fetch_assoc()) {
        $receivedOrders   = (float)($orderStats['total_orders'] ?? 0);
        $completeOrders   = (float)($orderStats['complete_orders'] ?? 0);
        $pendingOrders    = (float)($orderStats['pending_orders'] ?? 0);
        $cancelledOrders  = (float)($orderStats['cancelled_orders'] ?? 0);
        $processingOrders = (float)($orderStats['processing_orders'] ?? 0);
        $totalRevenue     = (float)($orderStats['total_revenue'] ?? 0);
    }
} catch (Exception $e) {
    error_log("Order stats query error: " . $e->getMessage());
}

// Get today's order statistics
try {
    $todayOrderQuery = "SELECT 
        COUNT(*) as today_orders,
        COALESCE(SUM(total_price), 0) as today_revenue
        FROM orders WHERE DATE(order_date) = '$today'";
    $todayOrderResult = $conn->query($todayOrderQuery);
    if ($todayOrderResult && $todayOrderStats = $todayOrderResult->fetch_assoc()) {
        $todayOrders      = (float)($todayOrderStats['today_orders'] ?? 0);
        $todayRevenue     = (float)($todayOrderStats['today_revenue'] ?? 0);
    }
} catch (Exception $e) {
    error_log("Today order query error: " . $e->getMessage());
}

// Get user statistics
try {
    $userStatsQuery = "SELECT 
        COUNT(*) as registered_users,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users
        FROM registered_users";
    $userStatsResult = $conn->query($userStatsQuery);
    if ($userStatsResult && $userStats = $userStatsResult->fetch_assoc()) {
        $registeredUsers  = (float)($userStats['registered_users'] ?? 0);
        $activeUsers      = (float)($userStats['active_users'] ?? 0);
    }
} catch (Exception $e) {
    error_log("User stats query error: " . $e->getMessage());
}

// Get food/category statistics
try {
    $foodStatsQuery = "SELECT COUNT(*) as food_menu_items FROM category_food";
    $foodStatsResult = $conn->query($foodStatsQuery);
    if ($foodStatsResult && $foodStats = $foodStatsResult->fetch_assoc()) {
        $foodMenuItems    = (float)($foodStats['food_menu_items'] ?? 0);
    }
} catch (Exception $e) {
    error_log("Food stats query error: " . $e->getMessage());
}

// Calculate derived values
$dailySpecials    = 23;
$itemInStock      = 50;
$itemLowStock     = 10;
$moneyIn          = $totalRevenue;
$refunds          = 0;

$weeklyOrders = [165, 189, 142, 198, 225, 267, 245];
$weeklyDone   = [152, 176, 138, 185, 210, 248, 227];

$completionRate = round(($completeOrders / max($receivedOrders,1)) * 100);
$stockHealth    = round(($itemInStock / max($itemInStock + $itemLowStock,1)) * 100);
$activeRate     = round(($activeUsers / max($registeredUsers,1)) * 100);
$revenueHealth  = round(($moneyIn / max($moneyIn + $refunds,1)) * 100);
$specialRate    = round(($dailySpecials / max($foodMenuItems,1)) * 100);
$pendingRate    = round(($pendingOrders / max($receivedOrders,1)) * 100);

if ($is_standalone) {
    // Output CSS for standalone mode
    ?>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <style>
    :root {
      --bg:           #0d0f1a;
      --s1:           #13162a;
      --s2:           #181c30;
      --s3:           #1e2338;
      --border:       rgba(255,255,255,0.07);
      --border-h:     rgba(255,255,255,0.14);
      --indigo:       #4f6ef7;
      --indigo-s:     rgba(79,110,247,0.12);
      --emerald:      #10b981;
      --emerald-s:    rgba(16,185,129,0.12);
      --amber:        #f59e0b;
      --amber-s:      rgba(245,158,11,0.12);
      --rose:         #f43f5e;
      --rose-s:       rgba(244,63,94,0.12);
      --violet:       #a78bfa;
      --violet-s:     rgba(167,139,250,0.12);
      --cyan:         #06b6d4;
      --cyan-s:       rgba(6,182,212,0.12);
      --t1: #f1f5ff;
      --t2: #8892b0;
      --t3: #3d4563;
      --r: 14px;
      --font: 'Sora', sans-serif;
      --mono: 'JetBrains Mono', monospace;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { background: var(--bg); min-height: 100vh; font-family: var(--font); color: var(--t1); }
    .dash { margin-top: 50px; padding: 30px 32px 64px; }
    .kpi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 24px; }
    .kpi { background: var(--s1); border: 1px solid var(--border); border-radius: var(--r); padding: 14px 16px 12px; position: relative; overflow: hidden; cursor: pointer; transition: transform 0.2s; }
    .kpi:hover { transform: translateY(-2px); }
    .kpi::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; }
    .kpi.ind::before { background: var(--indigo); }
    .kpi.em::before { background: var(--emerald); }
    .kpi.am::before { background: var(--amber); }
    .kpi.vi::before { background: var(--violet); }
    .kpi.cy::before { background: var(--cyan); }
    .kpi.ro::before { background: var(--rose); }
    .kpi-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
    .kpi-ico { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.88rem; }
    .kpi-ico.ind { background: var(--indigo-s); color: var(--indigo); }
    .kpi-ico.em { background: var(--emerald-s); color: var(--emerald); }
    .kpi-ico.am { background: var(--amber-s); color: var(--amber); }
    .kpi-ico.vi { background: var(--violet-s); color: var(--violet); }
    .kpi-ico.cy { background: var(--cyan-s); color: var(--cyan); }
    .kpi-ico.ro { background: var(--rose-s); color: var(--rose); }
    .kpi-badge { font-size: 0.58rem; font-weight: 700; padding: 2px 7px; border-radius: 20px; }
    .kpi-badge.up { background: var(--emerald-s); color: var(--emerald); }
    .kpi-badge.warn { background: var(--amber-s); color: var(--amber); }
    .kpi-badge.down { background: var(--rose-s); color: var(--rose); }
    .kpi-num { font-size: 1.75rem; font-weight: 800; font-family: var(--mono); color: var(--t1); letter-spacing: -1px; line-height: 1; margin-bottom: 3px; }
    .kpi-lbl { font-size: 0.67rem; font-weight: 600; color: var(--t2); text-transform: uppercase; letter-spacing: 0.5px; }
    .kpi-bar-wrap { margin-top: 12px; padding-top: 10px; border-top: 1px solid var(--border); }
    .kpi-bar-row { display: flex; justify-content: space-between; font-size: 0.63rem; color: var(--t2); font-weight: 500; margin-bottom: 6px; }
    .kpi-bar-row span:last-child { font-weight: 700; font-family: var(--mono); color: var(--t1); font-size: 0.65rem; }
    .bar { height: 4px; background: var(--s3); border-radius: 10px; overflow: hidden; }
    .bar-fill { height: 100%; border-radius: 10px; }
    .slabel { font-size: 0.62rem; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; color: var(--t3); margin-bottom: 14px; display: flex; align-items: center; gap: 10px; }
    .slabel::after { content: ''; flex: 1; height: 1px; background: var(--border); }
    .charts-row { display: grid; grid-template-columns: 1fr 360px; gap: 14px; margin-bottom: 28px; }
    .chart-card { background: var(--s1); border: 1px solid var(--border); border-radius: var(--r); padding: 24px; }
    .chart-top { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 22px; }
    .chart-top h3 { font-size: 0.90rem; font-weight: 700; color: var(--t1); margin-bottom: 3px; }
    .chart-top p { font-size: 0.68rem; color: var(--t2); }
    .chart-legend { display: flex; gap: 14px; }
    .cleg { display: flex; align-items: center; gap: 6px; font-size: 0.68rem; font-weight: 600; color: var(--t2); }
    .cleg-line { width: 16px; height: 3px; border-radius: 4px; }
    .donut-wrap { position: relative; width: 170px; height: 170px; margin: 0 auto 20px; }
    .donut-center { position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); text-align: center; pointer-events: none; }
    .donut-center-num { font-size: 1.75rem; font-weight: 800; font-family: var(--mono); color: var(--t1); line-height: 1; }
    .donut-center-txt { font-size: 0.58rem; color: var(--t2); font-weight: 600; text-transform: uppercase; letter-spacing: 0.7px; }
    .donut-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 9px; }
    .donut-item { display: flex; align-items: center; gap: 9px; padding: 9px 11px; background: var(--s2); border: 1px solid var(--border); border-radius: 9px; }
    .ddot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .dname { font-size: 0.65rem; color: var(--t2); font-weight: 500; }
    .dval { font-size: 0.78rem; font-weight: 700; font-family: var(--mono); color: var(--t1); }
    .dash-hdr { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 30px; padding-bottom: 22px; border-bottom: 1px solid var(--border); }
    .dash-hdr h1 { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.5px; color: var(--t1); }
    .dash-hdr p { font-size: 0.75rem; color: var(--t2); margin-top: 4px; }
    </style>
    </head>
    <body>
    <div class="dash">
    <div class="dash-hdr">
        <div>
            <h1>Restaurant Overview</h1>
            <p><?= date('l, F j, Y') ?> &nbsp;·&nbsp; All systems operational</p>
        </div>
    </div>
    <?php
}

if (!$is_standalone) {
?>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<?php } ?>

<style>
:root {
  --bg:           #0d0f1a;
  --s1:           #13162a;
  --s2:           #181c30;
  --s3:           #1e2338;
  --border:       rgba(255,255,255,0.07);
  --border-h:     rgba(255,255,255,0.14);

  --indigo:       #4f6ef7;
  --indigo-s:     rgba(79,110,247,0.12);
  --emerald:      #10b981;
  --emerald-s:    rgba(16,185,129,0.12);
  --amber:        #f59e0b;
  --amber-s:      rgba(245,158,11,0.12);
  --rose:         #f43f5e;
  --rose-s:       rgba(244,63,94,0.12);
  --violet:       #a78bfa;
  --violet-s:     rgba(167,139,250,0.12);
  --cyan:         #06b6d4;
  --cyan-s:       rgba(6,182,212,0.12);

  --t1: #f1f5ff;
  --t2: #8892b0;
  --t3: #3d4563;

  --r: 14px;
  --font: 'Sora', sans-serif;
  --mono: 'JetBrains Mono', monospace;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

.dash {
  margin-top: 50px;
  font-family: var(--font);
  background: var(--bg);
  min-height: 100vh;
  padding: 30px 32px 64px;
  color: var(--t1);
}

/* ── Header ── */
.dash-hdr {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  margin-bottom: 30px;
  padding-bottom: 22px;
  border-bottom: 1px solid var(--border);
}

.dash-hdr h1 {
  font-size: 1.5rem;
  font-weight: 800;
  letter-spacing: -0.5px;
  color: var(--t1);
}

.dash-hdr p {
  font-size: 0.75rem;
  color: var(--t2);
  margin-top: 4px;
}

.hdr-right { display: flex; align-items: center; gap: 10px; }

.live-pill {
  display: flex; align-items: center; gap: 7px;
  padding: 7px 14px;
  background: var(--s2);
  border: 1px solid var(--border);
  border-radius: 40px;
  font-size: 0.72rem;
  font-weight: 600;
  color: var(--t2);
}

.live-pill .dot {
  width: 7px; height: 7px;
  border-radius: 50%;
  background: var(--emerald);
  box-shadow: 0 0 7px var(--emerald);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%,100% { opacity:1; }
  50%      { opacity:0.4; }
}

.refresh-btn {
  display: flex; align-items: center; gap: 6px;
  padding: 8px 16px;
  background: var(--indigo);
  border: none;
  border-radius: 9px;
  font-size: 0.75rem;
  font-weight: 700;
  color: #fff;
  cursor: pointer;
  font-family: var(--font);
  transition: opacity .18s, transform .15s;
  letter-spacing: 0.2px;
}

.refresh-btn:hover { opacity: .85; transform: translateY(-1px); }

/* ── Section label ── */
.slabel {
  font-size: 0.62rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 2px;
  color: var(--t3);
  margin-bottom: 14px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.slabel::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--border);
}

/* ══ KPI GRID ══ */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  margin-bottom: 24px;
}

.kpi {
  background: var(--s1);
  border: 1px solid var(--border);
  border-radius: var(--r);
  padding: 14px 16px 12px;
  position: relative;
  overflow: hidden;
  transition: border-color .22s, transform .18s, box-shadow .22s;
  cursor: default;
}

.kpi:hover {
  border-color: var(--border-h);
  transform: translateY(-2px);
  box-shadow: 0 10px 28px rgba(0,0,0,.45);
}

.kpi::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 2px;
  border-radius: var(--r) var(--r) 0 0;
}

.kpi::after {
  content: '';
  position: absolute;
  top: -40px; right: -30px;
  width: 110px; height: 110px;
  border-radius: 50%;
  opacity: 0.05;
  pointer-events: none;
}

.kpi.ind::before { background: var(--indigo);  }  .kpi.ind::after  { background: var(--indigo); }
.kpi.em::before  { background: var(--emerald); }  .kpi.em::after   { background: var(--emerald); }
.kpi.am::before  { background: var(--amber);   }  .kpi.am::after   { background: var(--amber); }
.kpi.vi::before  { background: var(--violet);  }  .kpi.vi::after   { background: var(--violet); }
.kpi.cy::before  { background: var(--cyan);    }  .kpi.cy::after   { background: var(--cyan); }
.kpi.ro::before  { background: var(--rose);    }  .kpi.ro::after   { background: var(--rose); }

.kpi-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }

.kpi-ico {
  width: 32px; height: 32px;
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.88rem; flex-shrink: 0;
}

.kpi-ico.ind { background: var(--indigo-s);  color: var(--indigo);  }
.kpi-ico.em  { background: var(--emerald-s); color: var(--emerald); }
.kpi-ico.am  { background: var(--amber-s);   color: var(--amber);   }
.kpi-ico.vi  { background: var(--violet-s);  color: var(--violet);  }
.kpi-ico.cy  { background: var(--cyan-s);    color: var(--cyan);    }
.kpi-ico.ro  { background: var(--rose-s);    color: var(--rose);    }

.kpi-badge {
  font-size: 0.58rem;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 20px;
  display: flex; align-items: center; gap: 2px;
}

.kpi-badge.up   { background: var(--emerald-s); color: var(--emerald); }
.kpi-badge.down { background: var(--rose-s);    color: var(--rose); }
.kpi-badge.warn { background: var(--amber-s);   color: var(--amber); }

.kpi-num {
  font-size: 1.75rem;
  font-weight: 800;
  font-family: var(--mono);
  color: var(--t1);
  letter-spacing: -1px;
  line-height: 1;
  margin-bottom: 3px;
}

.kpi-lbl {
  font-size: 0.67rem;
  font-weight: 600;
  color: var(--t2);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.kpi-bar-wrap {
  margin-top: 12px;
  padding-top: 10px;
  border-top: 1px solid var(--border);
}

.kpi-bar-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.63rem;
  color: var(--t2);
  font-weight: 500;
  margin-bottom: 6px;
}

.kpi-bar-row span:last-child {
  font-weight: 700;
  font-family: var(--mono);
  color: var(--t1);
  font-size: 0.65rem;
}

.bar {
  height: 4px;
  background: var(--s3);
  border-radius: 10px;
  overflow: hidden;
}

.bar-fill {
  height: 100%;
  border-radius: 10px;
  transition: width 1.1s cubic-bezier(.34,1.56,.64,1);
}

/* ══ CHARTS ══ */
.charts-row {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 14px;
  margin-bottom: 28px;
}

.chart-card {
  background: var(--s1);
  border: 1px solid var(--border);
  border-radius: var(--r);
  padding: 24px;
  transition: border-color .22s;
}

.chart-card:hover { border-color: var(--border-h); }

.chart-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 22px;
}

.chart-top h3 {
  font-size: 0.90rem;
  font-weight: 700;
  color: var(--t1);
  margin-bottom: 3px;
}

.chart-top p { font-size: 0.68rem; color: var(--t2); }

.chart-legend { display: flex; gap: 14px; }

.cleg {
  display: flex; align-items: center; gap: 6px;
  font-size: 0.68rem; font-weight: 600; color: var(--t2);
}

.cleg-line { width: 16px; height: 3px; border-radius: 4px; }

/* Donut */
.donut-wrap {
  position: relative;
  width: 170px; height: 170px;
  margin: 0 auto 20px;
}

.donut-center {
  position: absolute;
  top: 50%; left: 50%;
  transform: translate(-50%,-50%);
  text-align: center;
  pointer-events: none;
}

.donut-center-num {
  font-size: 1.75rem;
  font-weight: 800;
  font-family: var(--mono);
  color: var(--t1);
  line-height: 1;
}

.donut-center-txt {
  font-size: 0.58rem;
  color: var(--t2);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.7px;
}

.donut-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 9px;
}

.donut-item {
  display: flex; align-items: center; gap: 9px;
  padding: 9px 11px;
  background: var(--s2);
  border: 1px solid var(--border);
  border-radius: 9px;
}

.ddot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.dname { font-size: 0.65rem; color: var(--t2); font-weight: 500; }
.dval  { font-size: 0.78rem; font-weight: 700; font-family: var(--mono); color: var(--t1); }

/* ══ QUICK ROW ══ */
.quick-grid {
  display: grid;
  grid-template-columns: repeat(4,1fr);
  gap: 14px;
  margin-bottom: 28px;
}

.qcard {
  background: var(--s1);
  border: 1px solid var(--border);
  border-radius: var(--r);
  padding: 18px 20px;
  display: flex; align-items: center; gap: 14px;
  transition: border-color .2s, transform .18s;
}

.qcard:hover { border-color: var(--border-h); transform: translateY(-2px); }

.qico {
  width: 46px; height: 46px;
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.2rem; flex-shrink: 0;
}

.qlbl { font-size: 0.68rem; font-weight: 600; color: var(--t2); text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
.qval { font-size: 1.4rem; font-weight: 800; font-family: var(--mono); color: var(--t1); line-height: 1.1; }
.qsub { font-size: 0.63rem; color: var(--t2); margin-top: 2px; }

/* ══ FOOD ══ */
.food-grid {
  display: grid;
  grid-template-columns: repeat(4,1fr);
  gap: 14px;
}

.fcard {
  background: var(--s1);
  border: 1px solid var(--border);
  border-radius: var(--r);
  overflow: hidden;
  transition: border-color .22s, transform .2s, box-shadow .22s;
}

.fcard:hover {
  border-color: var(--border-h);
  transform: translateY(-3px);
  box-shadow: 0 16px 40px rgba(0,0,0,.5);
}

.fimg {
  width: 100%; aspect-ratio: 4/3;
  overflow: hidden;
  background: var(--s3);
  position: relative;
}

.fimg img {
  width:100%; height:100%; object-fit:cover;
  transition: transform .4s ease;
}

.fcard:hover .fimg img { transform: scale(1.07); }

.fimg-overlay {
  position: absolute;
  bottom:0; left:0; right:0;
  height: 55%;
  background: linear-gradient(to top, rgba(13,15,26,.9), transparent);
}

.fbadge {
  position: absolute;
  top: 10px; right: 10px;
  padding: 3px 9px;
  border-radius: 20px;
  font-size: 0.58rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.fbadge.hot   { background: var(--rose);    color:#fff; }
.fbadge.best  { background: var(--indigo);  color:#fff; }
.fbadge.daily { background: var(--amber);   color:#0d0f1a; }
.fbadge.fit   { background: var(--emerald); color:#0d0f1a; }

.fbody { padding: 14px 16px; }
.fname { font-size: 0.83rem; font-weight: 700; color: var(--t1); margin-bottom: 3px; }
.fmeta { font-size: 0.65rem; color: var(--t2); display:flex; align-items:center; gap:5px; }
.fmeta-dot { width:3px; height:3px; border-radius:50%; background: var(--t3); }

/* ══ ANIMATIONS ══ */
@keyframes fadeUp {
  from { opacity:0; transform:translateY(16px); }
  to   { opacity:1; transform:translateY(0); }
}

.kpi   { animation: fadeUp .42s ease both; }
.chart-card { animation: fadeUp .42s ease both; }
.qcard { animation: fadeUp .38s ease both; }
.fcard { animation: fadeUp .38s ease both; }

.kpi:nth-child(1)   { animation-delay:.04s; }
.kpi:nth-child(2)   { animation-delay:.08s; }
.kpi:nth-child(3)   { animation-delay:.12s; }
.kpi:nth-child(4)   { animation-delay:.16s; }
.kpi:nth-child(5)   { animation-delay:.20s; }
.kpi:nth-child(6)   { animation-delay:.24s; }
.qcard:nth-child(1) { animation-delay:.05s; }
.qcard:nth-child(2) { animation-delay:.10s; }
.qcard:nth-child(3) { animation-delay:.15s; }
.qcard:nth-child(4) { animation-delay:.20s; }
.fcard:nth-child(1) { animation-delay:.06s; }
.fcard:nth-child(2) { animation-delay:.12s; }
.fcard:nth-child(3) { animation-delay:.18s; }
.fcard:nth-child(4) { animation-delay:.24s; }
</style>

<div class="dash">

  <!-- Header -->
  <div class="dash-hdr">
    <div>
      <h1>Restaurant Overview</h1>
      <p><?= date('l, F j, Y') ?> &nbsp;·&nbsp; All systems operational</p>
    </div>
    <div class="hdr-right">
      <div class="live-pill"><span class="dot"></span> Live Data</div>
      <button class="refresh-btn" onclick="location.reload()">
        <i class="bi bi-arrow-clockwise"></i> Refresh
      </button>
    </div>
  </div>

  <!-- KPI Cards -->
  <div class="slabel">Key Performance Indicators</div>
  <div class="kpi-grid">

    <div class="kpi ind" onclick="window.location.href='<?= $is_standalone ? '../iclude/dashboard.php?page=total_order' : '?page=total_order' ?>'">
      <div class="kpi-head">
        <div class="kpi-ico ind"><i class="bi bi-cart3"></i></div>
        <span class="kpi-badge up"><i class="bi bi-arrow-up-short"></i>+12%</span>
      </div>
      <div class="kpi-num"><?= number_format($receivedOrders) ?></div>
      <div class="kpi-lbl">Total Orders</div>
      <div class="kpi-bar-wrap">
        <div class="kpi-bar-row"><span>Completion rate</span><span><?= $completionRate ?>%</span></div>
        <div class="bar"><div class="bar-fill" style="width:<?= $completionRate ?>%;background:var(--indigo)"></div></div>
      </div>
    </div>

    <div class="kpi em" onclick="window.location.href='<?= $is_standalone ? '../iclude/dashboard.php?page=total_order' : '?page=total_order' ?>'">
      <div class="kpi-head">
        <div class="kpi-ico em"><i class="bi bi-currency-dollar"></i></div>
        <span class="kpi-badge up"><i class="bi bi-arrow-up-short"></i>+8.3%</span>
      </div>
      <div class="kpi-num">$<?= number_format($totalRevenue, 2) ?></div>
      <div class="kpi-lbl">Total Revenue</div>
      <div class="kpi-bar-wrap">
        <div class="kpi-bar-row"><span>Today's revenue</span><span>$<?= number_format($todayRevenue, 2) ?></span></div>
        <div class="bar"><div class="bar-fill" style="width:<?= $revenueHealth ?>%;background:var(--emerald)"></div></div>
      </div>
    </div>

    <div class="kpi am" onclick="window.location.href='<?= $is_standalone ? '../iclude/dashboard.php?page=category' : '?page=category' ?>'">
      <div class="kpi-head">
        <div class="kpi-ico am"><i class="bi bi-grid"></i></div>
        <span class="kpi-badge warn"><i class="bi bi-exclamation"></i><?= $itemLowStock ?> low</span>
      </div>
      <div class="kpi-num"><?= number_format($foodMenuItems) ?></div>
      <div class="kpi-lbl">Categories</div>
      <div class="kpi-bar-wrap">
        <div class="kpi-bar-row"><span>Stock health</span><span><?= $stockHealth ?>%</span></div>
        <div class="bar"><div class="bar-fill" style="width:<?= $stockHealth ?>%;background:var(--amber)"></div></div>
      </div>
    </div>

    <div class="kpi vi" onclick="window.location.href='<?= $is_standalone ? '../iclude/dashboard.php?page=register' : '?page=register' ?>'">
      <div class="kpi-head">
        <div class="kpi-ico vi"><i class="bi bi-person-plus"></i></div>
        <span class="kpi-badge up"><i class="bi bi-arrow-up-short"></i>+5%</span>
      </div>
      <div class="kpi-num"><?= number_format($registeredUsers) ?></div>
      <div class="kpi-lbl">User Register</div>
      <div class="kpi-bar-wrap">
        <div class="kpi-bar-row"><span>Currently active</span><span><?= number_format($activeUsers) ?></span></div>
        <div class="bar"><div class="bar-fill" style="width:<?= $activeRate ?>%;background:var(--violet)"></div></div>
      </div>
    </div>

    <div class="kpi cy" onclick="window.location.href='<?= $is_standalone ? '../iclude/dashboard.php?page=category' : '?page=category' ?>'">
      <div class="kpi-head">
        <div class="kpi-ico cy"><i class="bi bi-journal-richtext"></i></div>
        <span class="kpi-badge up"><i class="bi bi-arrow-up-short"></i>+3</span>
      </div>
      <div class="kpi-num"><?= number_format($foodMenuItems) ?></div>
      <div class="kpi-lbl">Menu Items</div>
      <div class="kpi-bar-wrap">
        <div class="kpi-bar-row"><span>Daily specials</span><span><?= $dailySpecials ?></span></div>
        <div class="bar"><div class="bar-fill" style="width:<?= $specialRate ?>%;background:var(--cyan)"></div></div>
      </div>
    </div>

    <div class="kpi ro" onclick="window.location.href='<?= $is_standalone ? '../iclude/dashboard.php?page=user_order' : '?page=user_order' ?>'">
      <div class="kpi-head">
        <div class="kpi-ico ro"><i class="bi bi-hourglass-split"></i></div>
        <span class="kpi-badge down"><i class="bi bi-arrow-down-short"></i>Needs attention</span>
      </div>
      <div class="kpi-num"><?= number_format($pendingOrders) ?></div>
      <div class="kpi-lbl">Pending Orders</div>
      <div class="kpi-bar-wrap">
        <div class="kpi-bar-row"><span>% of total orders</span><span><?= $pendingRate ?>%</span></div>
        <div class="bar"><div class="bar-fill" style="width:<?= $pendingRate ?>%;background:var(--rose)"></div></div>
      </div>
    </div>

  </div>

  <!-- Charts -->
  <div class="slabel">Analytics</div>
  <div class="charts-row">

    <div class="chart-card">
      <div class="chart-top">
        <div>
          <h3>Weekly Order Trends</h3>
          <p>Total orders vs completed — last 7 days</p>
        </div>
        <div class="chart-legend">
          <div class="cleg"><div class="cleg-line" style="background:var(--indigo)"></div>Total</div>
          <div class="cleg"><div class="cleg-line" style="background:var(--emerald)"></div>Completed</div>
        </div>
      </div>
      <div style="position:relative;height:280px;">
        <canvas id="lineChart"></canvas>
      </div>
    </div>

    <div class="chart-card" style="display:flex;flex-direction:column;justify-content:center;">
      <div class="chart-top">
        <div><h3>Order Status</h3><p>Live breakdown by state</p></div>
      </div>
      <div class="donut-wrap">
        <canvas id="donutChart"></canvas>
        <div class="donut-center">
          <div class="donut-center-num"><?= $receivedOrders ?></div>
          <div class="donut-center-txt">Total</div>
        </div>
      </div>
      <div class="donut-grid">
        <div class="donut-item">
          <div class="ddot" style="background:var(--emerald)"></div>
          <div><div class="dname">Completed</div><div class="dval"><?= $completeOrders ?></div></div>
        </div>
        <div class="donut-item">
          <div class="ddot" style="background:var(--amber)"></div>
          <div><div class="dname">Pending</div><div class="dval"><?= $pendingOrders ?></div></div>
        </div>
        <div class="donut-item">
          <div class="ddot" style="background:var(--rose)"></div>
          <div><div class="dname">Cancelled</div><div class="dval"><?= $cancelledOrders ?></div></div>
        </div>
        <div class="donut-item">
          <div class="ddot" style="background:var(--indigo)"></div>
          <div><div class="dname">Processing</div><div class="dval"><?= $processingOrders ?></div></div>
        </div>
      </div>
    </div>

  </div>

  <!-- Quick Stats -->
  <div class="slabel">At A Glance</div>
  <div class="quick-grid">
    <div class="qcard" onclick="window.location.href='?page=total_order'" style="cursor:pointer;" title="View Completed Orders">
      <div class="qico" style="background:var(--emerald-s);color:var(--emerald)"><i class="bi bi-check2-circle"></i></div>
      <div>
        <div class="qlbl">Completed Today</div>
        <div class="qval"><?= number_format($completeOrders) ?></div>
        <div class="qsub"><?= $completionRate ?>% completion rate</div>
      </div>
    </div>
    <div class="qcard" onclick="window.location.href='?page=user_order'" style="cursor:pointer;" title="View Cancelled Orders">
      <div class="qico" style="background:var(--rose-s);color:var(--rose)"><i class="bi bi-x-circle"></i></div>
      <div>
        <div class="qlbl">Cancelled</div>
        <div class="qval"><?= number_format($cancelledOrders) ?></div>
        <div class="qsub"><?= round($cancelledOrders/max($receivedOrders,1)*100) ?>% of total orders</div>
      </div>
    </div>
    <div class="qcard" onclick="window.location.href='?page=user_order'" style="cursor:pointer;" title="View Processing Orders">
      <div class="qico" style="background:var(--indigo-s);color:var(--indigo)"><i class="bi bi-cpu"></i></div>
      <div>
        <div class="qlbl">Processing</div>
        <div class="qval"><?= number_format($processingOrders) ?></div>
        <div class="qsub">In kitchen right now</div>
      </div>
    </div>
    <div class="qcard" onclick="window.location.href='?page=category'" style="cursor:pointer;" title="View Low Stock Items">
      <div class="qico" style="background:var(--amber-s);color:var(--amber)"><i class="bi bi-exclamation-triangle"></i></div>
      <div>
        <div class="qlbl">Low Stock Alert</div>
        <div class="qval" style="color:var(--amber)"><?= number_format($itemLowStock) ?></div>
        <div class="qsub">Items need restocking</div>
      </div>
    </div>
  </div>

  <!-- Featured Food -->
  <div class="slabel">Featured Menu Items</div>
  <div class="food-grid">

    <div class="fcard">
      <div class="fimg">
        <img src="../assets/images/pizza.jpg" alt="Pizza">
        <div class="fimg-overlay"></div>
        <span class="fbadge hot">🔥 Hot</span>
      </div>
      <div class="fbody">
        <div class="fname">Margherita Pizza</div>
        <div class="fmeta">Italian <div class="fmeta-dot"></div> Popular</div>
      </div>
    </div>

    <div class="fcard">
      <div class="fimg">
        <img src="../assets/images/buger.jpg" alt="Burger">
        <div class="fimg-overlay"></div>
        <span class="fbadge best">★ Best Seller</span>
      </div>
      <div class="fbody">
        <div class="fname">Classic Burger</div>
        <div class="fmeta">American <div class="fmeta-dot"></div> #1 Pick</div>
      </div>
    </div>

    <div class="fcard">
      <div class="fimg">
        <img src="../assets/images/man.jpg" alt="Chef Special">
        <div class="fimg-overlay"></div>
        <span class="fbadge daily">Today Only</span>
      </div>
      <div class="fbody">
        <div class="fname">Chef's Special</div>
        <div class="fmeta">Daily <div class="fmeta-dot"></div> Limited</div>
      </div>
    </div>

    <div class="fcard">
      <div class="fimg">
        <img src="../assets/images/rice.jpg" alt="Rice Bowl">
        <div class="fimg-overlay"></div>
        <span class="fbadge fit">Healthy</span>
      </div>
      <div class="fbody">
        <div class="fname">Steamed Rice Bowl</div>
        <div class="fmeta">Asian <div class="fmeta-dot"></div> 320 kcal</div>
      </div>
    </div>

  </div>

</div>

<script>
Chart.defaults.color = '#8892b0';
Chart.defaults.font.family = 'Sora, sans-serif';
Chart.defaults.font.size = 11;

// Line Chart
const lctx = document.getElementById('lineChart').getContext('2d');
const g1 = lctx.createLinearGradient(0,0,0,280);
g1.addColorStop(0,'rgba(79,110,247,.22)'); g1.addColorStop(1,'rgba(79,110,247,0)');
const g2 = lctx.createLinearGradient(0,0,0,280);
g2.addColorStop(0,'rgba(16,185,129,.18)'); g2.addColorStop(1,'rgba(16,185,129,0)');

new Chart(lctx, {
  type: 'line',
  data: {
    labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
    datasets: [
      {
        label: 'Total Orders',
        data: <?= json_encode($weeklyOrders) ?>,
        borderColor:'#4f6ef7', backgroundColor: g1,
        borderWidth:2.5, fill:true, tension:0.42,
        pointBackgroundColor:'#4f6ef7', pointBorderColor:'#13162a',
        pointBorderWidth:2.5, pointRadius:5, pointHoverRadius:7
      },
      {
        label: 'Completed',
        data: <?= json_encode($weeklyDone) ?>,
        borderColor:'#10b981', backgroundColor: g2,
        borderWidth:2.5, fill:true, tension:0.42,
        pointBackgroundColor:'#10b981', pointBorderColor:'#13162a',
        pointBorderWidth:2.5, pointRadius:5, pointHoverRadius:7
      }
    ]
  },
  options: {
    responsive:true, maintainAspectRatio:false,
    interaction:{ mode:'index', intersect:false },
    plugins: {
      legend:{ display:false },
      tooltip:{
        backgroundColor:'#1e2338',
        borderColor:'rgba(255,255,255,.08)', borderWidth:1,
        padding:14, cornerRadius:10,
        titleFont:{ weight:'700', size:12 },
        bodyFont:{ size:11 }
      }
    },
    scales: {
      y: { beginAtZero:false, grid:{ color:'rgba(255,255,255,.04)', drawBorder:false }, ticks:{ color:'#4a5568', padding:8 }, border:{ display:false } },
      x: { grid:{ display:false }, ticks:{ color:'#4a5568', padding:6, font:{ weight:'600' } }, border:{ display:false } }
    }
  }
});

// Donut Chart
const dctx = document.getElementById('donutChart').getContext('2d');
new Chart(dctx, {
  type: 'doughnut',
  data: {
    labels:['Completed','Pending','Cancelled','Processing'],
    datasets:[{
      data:[<?= $completeOrders ?>,<?= $pendingOrders ?>,<?= $cancelledOrders ?>,<?= $processingOrders ?>],
      backgroundColor:['#10b981','#f59e0b','#f43f5e','#4f6ef7'],
      borderColor:'#13162a', borderWidth:3, hoverOffset:10
    }]
  },
  options: {
    responsive:true, maintainAspectRatio:true, cutout:'72%',
    plugins: {
      legend:{ display:false },
      tooltip:{
        backgroundColor:'#1e2338',
        borderColor:'rgba(255,255,255,.08)', borderWidth:1,
        padding:12, cornerRadius:10,
        titleFont:{ weight:'700' }
      }
    }
  }
});
</script>

<?php if ($is_standalone) { ?>
</div>
</body>
</html>
<?php } ?>