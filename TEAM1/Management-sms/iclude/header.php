<?php
declare(strict_types=1);
// navbar.php

$notification_count = 4; // Replace with dynamic DB count
$admin_name = "Admin RSRL4";
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
  :root {
    --nav-h: 56px;
    --sidebar-w: 260px;
    --blue-deep: #1a237e;
    --blue-mid: #283593;
    --blue-light: #3949ab;
    --white-10: rgba(255,255,255,0.10);
    --white-15: rgba(255,255,255,0.15);
    --white-20: rgba(255,255,255,0.20);
    --white-60: rgba(255,255,255,0.60);
    --white-85: rgba(255,255,255,0.85);
  }

  * { box-sizing: border-box; }

  body {
    margin: 0;
    font-family: 'Plus Jakarta Sans', sans-serif;
  }

  /* ═══════════════════════════
     NAVBAR
  ═══════════════════════════ */
  .top-navbar {
    position: fixed;
    margin-left: 10px;
    top: 0;
    left: var(--sidebar-w);
    right: 0;
    margin-top: 1px;
    border-radius: 3px;
    height: var(--nav-h);
    background: var(--s1);
    max-width: calc(100% - var(--sidebar-w) - 2px);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px 0 24px;
    z-index: 999;
    border-bottom: 1px solid rgba(68, 59, 59, 0.08);
    box-shadow: 0 4px 20px rgba(0,0,0,0.28);
  }

  /* ── Left ── */
  .nav-left { display: flex; align-items: center; gap: 10px; }

  .nav-page-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #fff;
    letter-spacing: 0.2px;
  }

  .nav-breadcrumb {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.70rem;
    color: var(--white-60);
    margin-top: 1px;
  }

  .nav-breadcrumb i { font-size: 0.62rem; }

  /* ── Right ── */
  .nav-right { display: flex; align-items: center; gap: 5px; }

  /* Icon button */
  .nav-btn {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 9px;
    background: var(--white-10);
    border: 1px solid var(--white-15);
    color: var(--white-85);
    font-size: 0.95rem;
    text-decoration: none;
    transition: background 0.18s, transform 0.15s;
  }

  .nav-btn:hover {
    background: var(--white-20);
    color: #fff;
    transform: translateY(-1px);
  }

  /* Notification count badge */
  .notif-badge {
    position: absolute;
    top: -3px;
    right: -3px;
    min-width: 16px;
    height: 16px;
    padding: 0 4px;
    background: #f44336;
    color: #fff;
    font-size: 0.58rem;
    font-weight: 700;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid var(--blue-deep);
  }

  /* Thin separator */
  .nav-sep {
    width: 1px;
    height: 24px;
    background: var(--white-15);
    margin: 0 4px;
  }

  /* Admin chip */
  .admin-chip {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 5px 13px 5px 6px;
    background: var(--white-10);
    border: 1px solid var(--white-15);
    border-radius: 40px;
    transition: background 0.18s;
  }

  .admin-chip:hover { background: var(--white-20); }

  .chip-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: var(--blue-light);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
    color: #fff;
    border: 1.5px solid var(--white-20);
    flex-shrink: 0;
  }

  .chip-name {
    font-size: 0.78rem;
    font-weight: 600;
    color: #fff;
    white-space: nowrap;
    line-height: 1.2;
  }

  .chip-role {
    font-size: 0.62rem;
    color: var(--white-60);
    line-height: 1.2;
  }

  /* Action buttons */
  .nav-action {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 9px;
    font-size: 0.79rem;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.18s, color 0.18s, transform 0.15s;
  }

  .nav-action:hover { transform: translateY(-1px); }

  .nav-action.register {
    background: var(--white-10);
    border: 1px solid var(--white-15);
    color: var(--white-85);
  }

  .nav-action.register:hover {
    background: var(--white-20);
    color: #fff;
  }

  .nav-action.logout {
    background: rgba(244,67,54,0.14);
    border: 1px solid rgba(244,67,54,0.28);
    color: #ef9a9a;
  }

  .nav-action.logout:hover {
    background: rgba(244,67,54,0.26);
    color: #fff;
  }

  /* Main content offset */
  .main-content {
    margin-left: var(--sidebar-w);
    margin-top: var(--nav-h);
    padding: 28px;
  }
</style>

<nav class="top-navbar">

  <!-- LEFT -->
  <div class="nav-left">
    <div>
      <div class="nav-page-title">Admin Dashboard</div>
    </div>
  </div>

  <!-- RIGHT -->
  <div class="nav-right">

    <!-- Notification bell -->
    <a href="notifications.php" class="nav-btn" title="Notifications">
      <i class="bi bi-bell"></i>
      <?php if ($notification_count > 0): ?>
        <span class="notif-badge"><?= $notification_count ?></span>
      <?php endif; ?>
    </a>

    <!-- Settings -->
    <a href="setting.php" class="nav-btn" title="Settings">
      <i class="bi bi-gear"></i>
    </a>

    <div class="nav-sep"></div>

    <!-- Admin chip -->
    <div class="admin-chip">
      <div class="chip-avatar">A</div>
      <div>
        <div class="chip-name"><?= htmlspecialchars($admin_name) ?></div>
        <div class="chip-role">Administrator</div>
      </div>
    </div>

    <div class="nav-sep"></div>

    <!-- Register -->
    <a href="../peges/users_register.php" class="nav-action register">
      <i class="bi bi-person-plus"></i> Register
    </a>

    <!-- Logout -->
    <a href="logout.php" class="nav-action logout">
      <i class="bi bi-box-arrow-right"></i> Logout
    </a>

  </div>
</nav>