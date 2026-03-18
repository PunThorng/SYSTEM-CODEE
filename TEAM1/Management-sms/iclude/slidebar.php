<?php
declare(strict_types=1);
// sidebar.php

// Detect current active page
$current_page = $_GET['page'] ?? 'home';
$current_file = basename($_SERVER['PHP_SELF']);

function isActive(string $page, string $current): string {
    return $page === $current ? 'active' : '';
}
function isFileActive(string $file, string $current): string {
    return $file === $current ? 'active' : '';
}
?>

<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
  :root {
    --sidebar-width: 260px;
    --sidebar-bg: #0d0f1a;
    --sidebar-accent: #3949ab;
    --sidebar-hover: rgba(255, 255, 255, 0.1);
    --sidebar-active-bg: rgba(255, 255, 255, 0.15);
    --sidebar-active-border: #667eea;
    --text-muted: rgba(255, 255, 255, 0.5);
    --transition: 0.2s ease;
  }

  body {
    margin: 0;
    font-family: 'Outfit', sans-serif;
  }

  /* ── Sidebar Shell ── */
  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    margin-left: 2px;
    width: var(--sidebar-width);
    height: 99.8vh;
    margin-top: 1px;
    background: var(--sidebar-bg);
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    overflow-x: hidden;
    border-radius: 3px 0px 0 3px;
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.2) transparent;
    z-index: 1000;
    box-shadow: 4px 0 20px rgba(0, 0, 0, 0.25);
  }

  .sidebar::-webkit-scrollbar { width: 4px; }
  .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }

  /* ── Profile Block ── */
  .sidebar-profile {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 28px 20px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    gap: 10px;
  }

  .sidebar-profile .avatar-wrap {
    position: relative;
    width: 64px;
    height: 64px;
  }

  .sidebar-profile .avatar-wrap img {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255, 255, 255, 0.35);
  }

  .sidebar-profile .online-dot {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 12px;
    height: 12px;
    background: #66bb6a;
    border-radius: 50%;
    border: 2px solid #1a237e;
  }

  .sidebar-profile h6 {
    margin: 0;
    font-size: 0.85rem;
    font-weight: 600;
    color: #fff;
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }

  .sidebar-profile span {
    font-size: 0.72rem;
    color: var(--text-muted);
    font-weight: 400;
  }

  /* ── Section Label ── */
  .nav-section-label {
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: var(--text-muted);
    padding: 18px 20px 6px;
  }

  /* ── Nav Links ── */
  .sidebar a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 20px;
    color: rgba(255, 255, 255, 0.80);
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    border-left: 3px solid transparent;
    transition: background var(--transition), color var(--transition), border-color var(--transition);
    position: relative;
  }

  .sidebar a i {
    font-size: 1rem;
    width: 20px;
    text-align: center;
    flex-shrink: 0;
  }

  .sidebar a:hover {
    background: var(--sidebar-hover);
    color: #fff;
  }

  .sidebar a.active {
    background: var(--sidebar-active-bg);
    color: #fff;
    border-left-color: var(--sidebar-active-border);
    font-weight: 600;
  }

  /* ── Badge ── */
  .nav-badge {
    margin-left: auto;
    background: rgba(255,255,255,0.18);
    color: #fff;
    font-size: 0.65rem;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 20px;
  }

  .nav-badge.new {
    background: #e53935;
  }

  /* ── Divider ── */
  .sidebar-divider {
    border: none;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    margin: 8px 20px;
  }

  /* ── Footer ── */
  .sidebar-footer {
    margin-top: auto;
    padding: 16px 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    font-size: 0.72rem;
    color: var(--text-muted);
    text-align: center;
  }

  /* ── Main content offset ── */
  .main-content {
    margin-left: var(--sidebar-width);
  }
</style>

<div class="sidebar">

  <!-- Profile -->
  <div class="sidebar-profile">
    <div class="avatar-wrap">
      <img src="../assets/images/chap.jpg" alt="Admin Logo">
      <span class="online-dot"></span>
    </div>
    <h6>Admin RSRL4</h6>
    <span>Restaurant System</span>
  </div>

  <!-- Main Navigation -->
  <div class="nav-section-label">Main</div>

  <a href="dashboard.php?page=home" class="<?= isActive('home', $current_page) ?>">
    <i class="bi bi-speedometer2"></i> Dashboard
  </a>

  <a href="dashboard.php?page=users" class="<?= isActive('users', $current_page) ?>">
    <i class="bi bi-people"></i> Users
  </a>

  <a href="dashboard.php?page=user_order" class="<?= isActive('user_order', $current_page) ?>">
    <i class="bi bi-cart3"></i> Users Order
    <span class="nav-badge new">New</span>
  </a>
  <a href="dashboard.php?page=category" class="<?= isActive('category', $current_page) ?>">
    <i class="bi bi-grid"></i> Category Food
  </a>

  <a href="dashboard.php?page=register" class="<?= isActive('register', $current_page) ?>">
    <i class="bi bi-person-plus"></i> Register Users
  </a>

  <hr class="sidebar-divider">

  <!-- Management -->
  <div class="nav-section-label">Management</div>

  <a href="sale.php" class="<?= isFileActive('sale.php', $current_file) ?>">
    <i class="bi bi-tags"></i> Sale
  </a>

  <a href="dashboard.php?page=total_order" class="<?= isActive('total_order', $current_page) ?>">
    <i class="bi bi-bag-check"></i> Total Order
  </a>

  <hr class="sidebar-divider">

  <!-- Reports & Settings -->
  <div class="nav-section-label">Analytics</div>

  <a href="report.php" class="<?= isFileActive('report.php', $current_file) ?>">
    <i class="bi bi-bar-chart-line"></i> Report
  </a>

  <a href="setting.php" class="<?= isFileActive('setting.php', $current_file) ?>">
    <i class="bi bi-gear"></i> Settings
  </a>

  <!-- Footer -->
  <div class="sidebar-footer">
    © <?= date('Y') ?> Restaurant System
  </div>

</div>